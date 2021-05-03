<?php

namespace BlueSpice\Reminder\RunJobsTriggerHandler;

use BlueSpice\RunJobsTriggerHandler;
use DateTime;
use FormatJson;
use MediaWiki\MediaWikiServices;

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

	/**
	 * @var bool
	 */
	protected $doUpdateRepeatingRemindersDate = false;

	protected function doRun() {
		$status = \Status::newGood();

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'bs_reminder',
			'*',
			$this->queryConds
		);

		$repeatingReminders = [];

		if ( $res && $res->numRows() ) {
			foreach ( $res as $row ) {
				$user = \User::newFromId( $row->rem_user_id );
				$title = \Title::newFromID( $row->rem_page_id );
				$comment = $row->rem_comment;
				if ( $user && $title ) {
					$notification = new $this->notificationClass( $user, $title, $comment );
					$this->notifier->notify( $notification );

					if ( $this->doUpdateRepeatingRemindersDate === true && (int)$row->rem_is_repeating === 1 ) {
						$repeatingReminders[] = $row;
					}
				}
			}
		}

		if ( $this->doUpdateRepeatingRemindersDate ) {
			$this->updateRepeatingRemindersDate( $repeatingReminders );
		}

		return $status;
	}

	/**
	 * @param array $repeatingReminders
	 */
	protected function updateRepeatingRemindersDate( array $repeatingReminders ) {
		if ( count( $repeatingReminders ) > 0 ) {
			foreach ( $repeatingReminders as $reminder ) {
				$currentDate = new DateTime();
				$nextReminderDate = MediaWikiServices::getInstance()
					->getService( 'BSRepeatingReminderDateCalculator' )
					->getNextReminderDateFromGivenDate(
						$currentDate,
						FormatJson::decode( $reminder->rem_repeat_config )
					);

				$repeatDateEnd = DateTime::createFromFormat( 'YmdHis', $reminder->rem_repeat_date_end );

				if ( $repeatDateEnd->format( 'Y-m-d' ) >= $nextReminderDate->format( 'Y-m-d' ) ) {
					$dbw = $this->loadBalancer->getConnection( DB_PRIMARY );
					$dbw->update(
						'bs_reminder',
						[ 'rem_date' => $nextReminderDate->format( 'Y-m-d' ) ],
						[ 'rem_id' => $reminder->rem_id ],
						__METHOD__
					);
				}
			}
		}
	}

}
