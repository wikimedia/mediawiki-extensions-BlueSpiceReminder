<?php

namespace BlueSpice\Reminder\RunJobsTriggerHandler;

use BlueSpice\Reminder\Notification\OneWeekNotification;

class SendInOneWeekReminderNotification extends SendNotificationBase {
	protected $queryConds = [
		'rem_date = CURDATE() + INTERVAL 7 DAY'
	];

	protected $notificationClass = OneWeekNotification::class;
}
