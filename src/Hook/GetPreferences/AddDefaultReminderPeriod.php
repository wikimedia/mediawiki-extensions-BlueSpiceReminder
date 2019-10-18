<?php

namespace BlueSpice\Reminder\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

class AddDefaultReminderPeriod extends GetPreferences {
	protected function doProcess() {
		$this->preferences['bs-reminder-period'] = array(
			'type' => 'int',
			'label-message' => 'bs-reminder-pref-DefaultReminderPeriod',
			'section' => 'editing/reminder',
		);
		return true;
	}
}
