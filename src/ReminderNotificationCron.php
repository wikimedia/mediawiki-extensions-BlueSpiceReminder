<?php

namespace BlueSpice\Reminder;

use BlueSpice\Reminder\Process\SendInOneWeekReminderNotification;
use BlueSpice\Reminder\Process\SendTodayReminderNotification;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\ProcessManager\ManagedProcess;
use MWStake\MediaWiki\Component\WikiCron\WikiCronManager;

class ReminderNotificationCron {

	/**
	 * @return void
	 */
	public static function register() {
		if ( defined( 'MW_PHPUNIT_TEST' ) || defined( 'MW_QUIBBLE_CI' ) ) {
			return;
		}

		/** @var WikiCronManager $cronManager */
		$cronManager = MediaWikiServices::getInstance()->getService( 'MWStake.WikiCronManager' );

		// Interval: Daily at 01:00
		$cronManager->registerCron( 'bs-reminder-send-daily', '0 1 * * *', new ManagedProcess( [
			'send-daily' => [
				'class' => SendTodayReminderNotification::class,
				'services' => [
					'DBLoadBalancer',
					'UserFactory',
					'MWStake.Notifier',
					'BSRepeatingReminderDateCalculator',
				],
			]
		] ) );

		// Interval: Daily at 01:00
		$cronManager->registerCron( 'bs-reminder-send-weekly', '0 1 * * *', new ManagedProcess( [
			'send-weekly' => [
				'class' => SendInOneWeekReminderNotification::class,
				'services' => [
					'DBLoadBalancer',
					'UserFactory',
					'MWStake.Notifier',
					'BSRepeatingReminderDateCalculator',
				],
			]
		] ) );
	}
}
