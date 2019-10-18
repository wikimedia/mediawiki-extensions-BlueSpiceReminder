<?php

namespace BlueSpice\Reminder\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;
use BlueSpice\Html\FormField\NamespaceMultiselect;

class AddDisableReminderForNS extends GetPreferences {
	protected function doProcess() {
		$this->preferences['bs-reminder-forns'] = [
			'class' => NamespaceMultiselect::class,
			'label-message' => 'bs-reminder-pref-DisableReminderForNS',
			'section' => 'editing/reminder',
			NamespaceMultiselect::OPTION_BLACKLIST => [
				NS_MEDIAWIKI, NS_MEDIAWIKI_TALK
			]
		];
		return true;
	}
}
