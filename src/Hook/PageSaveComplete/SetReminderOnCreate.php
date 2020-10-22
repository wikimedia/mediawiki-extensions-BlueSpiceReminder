<?php

namespace BlueSpice\Reminder\Hook\PageSaveComplete;

use BlueSpice\Hook\PageSaveComplete;
use Exception;
use Hooks;

/**
 * Hook after Article is saved, sets up the reminder if user chose so
 */
class SetReminderOnCreate extends PageSaveComplete {
	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( ( $this->flags & EDIT_MINOR ) || !$this->revisionRecord ) {
			return true;
		}
		if ( $this->editResult->isNullEdit() ) {
			return true;
		}
		$title = $this->wikiPage->getTitle();
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

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$sDefaultPeriod = $this->user->getOption( 'bs-reminder-period' );
		$iDate = strtotime( "+$sDefaultPeriod days" );
		$sFormattedFieldValue = date( 'Y-m-d', $iDate );

		$conn = $this->getServices()->getDBLoadBalancer()->getConnection( DB_MASTER );
		$res = $conn->insert(
			'bs_reminder',
			[
				'rem_user_id' => $this->user->getId(),
				'rem_page_id' => $this->wikiPage->getId(),
				'rem_date' => $sFormattedFieldValue
		] );
		if ( !$res ) {
			return true;
		}
		$remID = $conn->insertId();
		try {
			Hooks::run( 'BsReminderOnSave', [
				[],
				$remID,
				$this->wikiPage->getId(),
				$this->user->getId()
			] );
		} catch ( Exception $e ) {
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
