<?php

namespace BlueSpice\Reminder\Event;

use Message;

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
	 * @return Message|null
	 */
	public function getLinksIntroMessage(): ?Message {
		return Message::newFromKey( 'ext-notifications-notification-generic-links-intro' );
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'bs-reminder-one-week';
	}
}
