<?php

use BlueSpice\ExtensionAttributeBasedRegistry;
use BlueSpice\Reminder\Factory;
use MediaWiki\MediaWikiServices;

// PHP unit does not understand code coverage for this file
// as the @covers annotation cannot cover a specific file
// This is fully tested in ServiceWiringTest.php
// @codeCoverageIgnoreStart

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

// @codeCoverageIgnoreEnd
