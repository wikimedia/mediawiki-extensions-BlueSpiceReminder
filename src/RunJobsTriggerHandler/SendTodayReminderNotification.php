<?php

namespace BlueSpice\Reminder\RunJobsTriggerHandler;

use BlueSpice\Reminder\Event\ReminderToday;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\INotificationEvent;
use Title;

class SendTodayReminderNotification extends SendNotificationBase {
	/** @var string[] */
	protected $queryConds = [
		'rem_date = CURDATE()'
	];

	/**
	 * @param UserIdentity $user
	 * @param Title $title
	 * @param string $comment
	 * @return INotificationEvent
	 */
	protected function getEvent( UserIdentity $user, Title $title, string $comment ): INotificationEvent {
		return new ReminderToday( $user, $title, $comment );
	}

	/** @var bool */
	protected $doUpdateRepeatingRemindersDate = true;
}
