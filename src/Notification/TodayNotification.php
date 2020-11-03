<?php

namespace BlueSpice\Reminder\Notification;

use BlueSpice\BaseNotification;

class TodayNotification extends BaseNotification {
	/**
	 *
	 * @param \User $agent
	 * @param \Title|null $title
	 * @param string $comment
	 */
	public function __construct( \User $agent, $title = null, $comment = '' ) {
		$params = [
			'comment' => $comment,
			'notifyAgent' => true
		];
		parent::__construct( 'notification-bs-reminder-today', $agent, $title, $params );
		$this->addAffectedUsers( [ $agent->getId() ] );
	}
}
