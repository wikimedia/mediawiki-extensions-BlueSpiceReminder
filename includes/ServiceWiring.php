<?php

use BlueSpice\ExtensionAttributeBasedRegistry;
use BlueSpice\Reminder\Factory;
use MediaWiki\MediaWikiServices;

return [
	'BSRepeatingReminderDateCalculator' => function ( MediaWikiServices $services ) {
		return new \BlueSpice\Reminder\RepeatingReminderDateCalculator();
	},
	'BSReminderFactory' => function ( MediaWikiServices $services ) {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceReminderRegistry'
		);
		return new Factory(
			$registry,
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},
];
