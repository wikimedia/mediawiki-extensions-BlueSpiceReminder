<?php

namespace BlueSpice\Reminder\RunJobsTriggerHandler;

use BlueSpice\RunJobsTriggerHandler;

class SendNotificationBase extends RunJobsTriggerHandler {
	/**
	 *
	 * @var array
	 */
	protected $queryConds = [];

	/**
	 *
	 * @var string
	 */
	protected $notificationClass;

	protected function doRun() {
		$status = \Status::newGood();

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'bs_reminder',
			'*',
			$this->queryConds
		);

		if ( $res && $res->numRows() ) {
			foreach ( $res as $row ) {
				$user = \User::newFromId( $row->rem_user_id );
				$title = \Title::newFromID( $row->rem_page_id );
				$comment = $row->rem_comment;

				if ( $user && $title ) {
					$titleText = $title->getPrefixedText();
					$notification = new $this->notificationClass( $user, $title, $comment );

					$this->notifier->notify( $notification );
				}
			}
		}

		return $status;
	}

}
