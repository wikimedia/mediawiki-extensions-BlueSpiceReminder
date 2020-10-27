<?php

namespace BlueSpice\Reminder\Notification\PresentationModel;

use BlueSpice\EchoConnector\EchoEventPresentationModel;

class Today extends EchoEventPresentationModel {
	/**
	 *
	 * @return array
	 */
	public function getHeaderMessageContent() {
		$headerKey = 'notification-bs-reminder-today-subject';
		$headerParams = [ 'title', 'comment' ];

		return [
			'key' => $headerKey,
			'params' => $headerParams
		];
	}

	/**
	 * Gets appropriate message key and params for
	 * web notification message
	 *
	 * @return array
	 */
	public function getBodyMessageContent() {
		$bodyKey = 'notification-bs-reminder-today-web-body';
		$bodyParams = [ 'title', 'comment' ];

		if ( $this->distributionType == 'email' ) {
			$bodyKey = 'notification-bs-reminder-today-email-body';
			$bodyParams = [ 'title', 'comment' ];
		}

		return [
			'key' => $bodyKey,
			'params' => $bodyParams
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getIcon() {
		return 'reminder';
	}
}
