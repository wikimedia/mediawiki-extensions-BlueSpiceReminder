<?php

namespace BlueSpice\Reminder\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

class AddSetReminderOnCreate extends GetPreferences {
	protected function doProcess() {
		$this->preferences['bs-reminder-oncreate'] = [
			'type' => 'check',
			'section' => 'editing/reminder',
			'label-message' => 'bs-reminder-pref-SetReminderOnCreate',
		];
		return true;
	}
}
