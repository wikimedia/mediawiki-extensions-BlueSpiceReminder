<?php

namespace BlueSpice\Reminder\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

class AddDisableReminderForNS extends GetPreferences {
	protected function doProcess() {
		$excludeNS = [
			NS_MEDIAWIKI,
			NS_MEDIAWIKI_TALK
		];

		$namespaces = $this->getContext()->getLanguage()->getNamespaces();
		foreach ( $namespaces as $namespaceId => $namespace ) {
			if ( in_array( $namespaceId, $excludeNS ) || $namespaceId < 0 ) {
				continue;
			}

			if ( $namespaceId === NS_MAIN ) {
				$namespace = wfMessage( 'bs-ns_main' )->text();
			}

			$namespaceValues[$namespace] = $namespaceId;
		}
		$this->preferences['bs-reminder-forns'] = [
			'type' => 'multiselect',
			'label-message' => 'bs-reminder-pref-DisableReminderForNS',
			'section' => 'editing/reminder',
			'options' => $namespaceValues
		];
		return true;
	}
}
