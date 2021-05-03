<?php

use BlueSpice\ExtensionAttributeBasedRegistry;
use BlueSpice\Reminder\Factory;
use MediaWiki\MediaWikiServices;

return [
	'BSRepeatingReminderDateCalculator' => static function ( MediaWikiServices $services ) {
		return new \BlueSpice\Reminder\RepeatingReminderDateCalculator();
	},
	'BSReminderFactory' => static function ( MediaWikiServices $services ) {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceReminderRegistry'
		);
		return new Factory(
			$registry,
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},
];
