<?php

namespace BlueSpice\Reminder\Hook\PageContentSaveComplete;

use BlueSpice\Hook\PageContentSaveComplete;
use Exception;
use Hooks;

/**
 * Hook after Article is saved, sets up the reminder if user chose so
 */
class SetReminderOnCreate extends PageContentSaveComplete {
	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( $this->isMinor || !$this->revision ) {
			return true;
		}
		if ( !$this->status->isOK() || $this->status->hasMessage( 'edit-no-change' ) ) {
			// ugly. we need to check the status object for the no edit warning,
			// cause on this point in the code it ist - unfortunaltey -
			// impossible to find out, if this edit changed something.
			// '$article->getLatest()' is always the same as
			// '$this->revision->getId()'. '$baseRevId' is always 'false' #5240
			return true;
		}
		$title = $this->wikipage->getTitle();
		if ( !$title || !$title->exists() ) {
			return true;
		}
		if ( !$title->isNewPage() ) {
			return true;
		}
		if ( !$this->user->getOption( 'bs-reminder-oncreate' ) ) {
			return true;
		}
		if ( in_array( $title->getNamespace(), $this->getNSBlacklist() ) ) {
			return true;
		}

		return false;
	}

	protected function doProcess() {
		$sDefaultPeriod = $this->user->getOption( 'bs-reminder-period' );
		$iDate = strtotime( "+$sDefaultPeriod days" );
		$sFormattedFieldValue = date( 'Y-m-d', $iDate );

		$conn = $this->getServices()->getDBLoadBalancer()->getConnection( DB_MASTER );
		$res = $conn->insert(
			'bs_reminder',
			[
				'rem_user_id' => $this->user->getId(),
				'rem_page_id' => $this->wikipage->getId(),
				'rem_date' => $sFormattedFieldValue
		] );
		if ( !$res ) {
			$this->status->error( 'bs-reminder-create-error' );
			return true;
		}
		$remID = $conn->insertId();
		try {
			Hooks::run( 'BsReminderOnSave', [
				[],
				$remID,
				$this->wikipage->getId(),
				$this->user->getId()
			] );
		} catch ( Exception $e ) {
			$this->status->error( $e->getMessage() );
		}
		return true;
	}

	/**
	 *
	 * @return int[]
	 */
	private function getNSBlacklist() {
		return explode( '|', $this->user->getOption( 'bs-reminder-forns' ) );
	}

}
