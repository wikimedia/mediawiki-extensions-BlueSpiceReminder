<?php

namespace BlueSpice\Reminder\Notification;

use BlueSpice\BaseNotification;

class OneWeekNotification extends BaseNotification {
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
		parent::__construct( 'notification-bs-reminder-one-week', $agent, $title, $params );
		$this->addAffectedUsers( [ $agent->getId() ] );
	}
}
