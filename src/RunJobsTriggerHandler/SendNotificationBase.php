<?php

namespace BlueSpice\Reminder\RunJobsTriggerHandler;

use BlueSpice\RunJobsTriggerHandler;
use DateTime;
use FormatJson;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\INotificationEvent;

abstract class SendNotificationBase extends RunJobsTriggerHandler {
	/**
	 *
	 * @var array
	 */
	protected $queryConds = [];

	/**
	 * @var bool
	 */
	protected $doUpdateRepeatingRemindersDate = false;

	protected function doRun() {
		$status = Status::newGood();

		$dbr = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$res = $dbr->select(
			'bs_reminder',
			'*',
			$this->queryConds
		);

		$repeatingReminders = [];

		if ( $res && $res->numRows() ) {
			$userFactory = $this->services->getUserFactory();
			foreach ( $res as $row ) {
				$user = $userFactory->newFromId( $row->rem_user_id );
				$title = Title::newFromID( $row->rem_page_id );
				$comment = $row->rem_comment;
				if ( $user && $title ) {
					$event = $this->getEvent( $user, $title, $comment );
					$this->services->getService( 'MWStake.Notifier' )->emit( $event );

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
	 * @param UserIdentity $user
	 * @param Title $title
	 * @param string $comment
	 * @return INotificationEvent
	 */
	abstract protected function getEvent( UserIdentity $user, Title $title, string $comment ): INotificationEvent;

	/**
	 * @param array $repeatingReminders
	 */
	protected function updateRepeatingRemindersDate( array $repeatingReminders ) {
		if ( count( $repeatingReminders ) > 0 ) {
			$reminderService = $this->services->getService( 'BSRepeatingReminderDateCalculator' );
			foreach ( $repeatingReminders as $reminder ) {
				$currentDate = new DateTime();
				$nextReminderDate = $reminderService->getNextReminderDateFromGivenDate(
					$currentDate,
					FormatJson::decode( $reminder->rem_repeat_config )
				);

				if ( $nextReminderDate === false ) {
					wfDebugLog(
						'BS::Reminder',
						'Could not calculate next reminder date from '
						. $reminder->rem_repeat_config
					);
					continue;
				}

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
