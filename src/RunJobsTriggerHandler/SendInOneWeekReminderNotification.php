<?php

namespace BlueSpice\Reminder\RunJobsTriggerHandler;

use BlueSpice\Reminder\Event\ReminderInOneWeek;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\INotificationEvent;
use Title;

class SendInOneWeekReminderNotification extends SendNotificationBase {
	protected $queryConds = [
		'rem_date = CURDATE() + INTERVAL 7 DAY'
	];

	/**
	 * @param UserIdentity $user
	 * @param Title $title
	 * @param string $comment
	 * @return INotificationEvent
	 */
	protected function getEvent( UserIdentity $user, Title $title, string $comment ): INotificationEvent {
		return new ReminderInOneWeek( $user, $title, $comment );
	}
}
