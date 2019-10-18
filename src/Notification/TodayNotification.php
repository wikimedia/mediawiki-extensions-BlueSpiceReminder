<?php

namespace BlueSpice\Reminder\Notification;

use BlueSpice\BaseNotification;

class TodayNotification extends BaseNotification {
	public function __construct( \User $agent, $title = null, $comment = '' ) {
		$params = [
			'comment' => $comment,
			'notifyAgent' => true
		];

		$this->audience[] = $agent->getId();

		parent::__construct( 'notification-bs-reminder-today', $agent, $title, $params );
	}
}
