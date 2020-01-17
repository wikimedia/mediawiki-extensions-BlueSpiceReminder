<?php

namespace BlueSpice\Reminder\Notification;

use BlueSpice\NotificationManager;
use BlueSpice\Reminder\Notification\PresentationModel\Today;
use BlueSpice\Reminder\Notification\PresentationModel\OneWeek;

class Registrator {
	/**
	 *
	 * @param NotificationManager $manager
	 */
	public static function registerNotifications( NotificationManager $manager ) {
		$notifier = $manager->getNotifier();

		$notifier->registerNotificationCategory( 'notification-bs-reminder-cat', [
			'tooltip' => 'echo-pref-tooltip-bs-reminder-cat'
		] );

		$manager->registerNotification(
			'notification-bs-reminder-today',
			[
				'category' => 'notification-bs-reminder-cat',
				'presentation-model' => Today::class
			]
		);

		$manager->registerNotification(
			'notification-bs-reminder-one-week',
			[
				'category' => 'notification-bs-reminder-cat',
				'presentation-model' => OneWeek::class
			]
		);
	}
}
