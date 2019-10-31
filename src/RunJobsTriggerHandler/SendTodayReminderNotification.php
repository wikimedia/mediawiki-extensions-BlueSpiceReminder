<?php

namespace BlueSpice\Reminder\RunJobsTriggerHandler;

use BlueSpice\Reminder\Notification\TodayNotification;

class SendTodayReminderNotification extends SendNotificationBase {
	protected $queryConds = [
		'rem_date = CURDATE()'
	];

	protected $notificationClass = TodayNotification::class;
}
