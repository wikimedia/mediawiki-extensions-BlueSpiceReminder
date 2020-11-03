<?php

namespace BlueSpice\Reminder\Hook\BSUserManagerAfterDeleteUser;

use BlueSpice\UserManager\Hook\BSUserManagerAfterDeleteUser;

class DeleteReminders extends BSUserManagerAfterDeleteUser {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( !$this->status->isOK() ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		try {
			$this->getServices()->getDBLoadBalancer()->getConnection( DB_MASTER )->delete(
				'bs_reminder',
				[ 'rem_user_id' => $this->user->getId() ],
				__METHOD__
			);
		} catch ( Exception $e ) {
			$this->status->fatal( $e->getMessage() );
		}
	}

}
