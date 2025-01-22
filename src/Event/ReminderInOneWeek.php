<?php

namespace BlueSpice\Reminder\Event;

use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;

class ReminderInOneWeek extends ReminderToday {

	/**
	 * @return Message
	 */
	public function getKeyMessage(): Message {
		return Message::newFromKey( 'bs-reminder-event-one-week-desc' );
	}

	/**
	 * @return string
	 */
	public function getMessageKey(): string {
		return 'bs-reminder-event-one-week';
	}

	/**
	 * @param IChannel $forChannel
	 * @return Message|null
	 */
	public function getLinksIntroMessage( IChannel $forChannel ): ?Message {
		return Message::newFromKey( 'ext-notifyme-notification-generic-links-intro' );
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'bs-reminder-one-week';
	}
}
