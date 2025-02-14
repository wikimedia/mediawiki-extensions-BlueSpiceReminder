<?php

namespace BlueSpice\Reminder\Tests;

use BlueSpice\Reminder\Special\Reminder;
use SpecialPageTestBase;

/**
 * Class SpecialReminderTest
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceReminder
 * @covers \BlueSpice\Reminder\Special\Reminder
 */
class SpecialReminderTest extends SpecialPageTestBase {

	protected function newSpecialPage() {
		$permissionManager = $this->getServiceContainer()->getPermissionManager();
		return new Reminder( $permissionManager );
	}

	public function testSpecialReminderClassExists() {
		$this->assertTrue(
			class_exists( 'Reminder' ),
			'Class Reminder does not exist'
		);
	}

	public function testSpecialReminder_hasGridElement() {
		[ $html, ] = $this->executeSpecialPage();
		$this->assertStringContainsString( 'bs-reminder-special-reminder-container', $html );
	}
}
