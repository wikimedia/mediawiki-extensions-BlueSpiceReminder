<?php

use MediaWiki\MediaWikiServices;

return [
	'BSRepeatingReminderDateCalculator' => function ( MediaWikiServices $services ) {
		return new \BlueSpice\Reminder\RepeatingReminderDateCalculator();
	},
];
