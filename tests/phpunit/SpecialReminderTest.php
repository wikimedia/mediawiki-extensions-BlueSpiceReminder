<?php

namespace BlueSpice\Reminder\Tests;

use SpecialPageTestBase;
use SpecialReminder;

/**
 * Class SpecialReminderTest
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceReminder
 * @covers SpecialReminder
 */
class SpecialReminderTest extends SpecialPageTestBase {

	protected function setUp(): void {
		parent::setUp();
		$this->tablesUsed[] = 'bs_reminder';
	}

	protected function newSpecialPage() {
		return new SpecialReminder();
	}

	public function testSpecialReminderClassExists() {
		$this->assertTrue(
			class_exists( 'SpecialReminder' ),
			'Class SpecialReminder does not exist'
		);
	}

	public function testSpecialReminder_hasGridElement() {
		list( $html, ) = $this->executeSpecialPage();
		$this->assertStringContainsString( 'bs-reminder-overview-grid', $html );
	}

	public function testSpecialReminder_hasBacklink() {
		list( $html, $response ) = $this->executeSpecialPage( "TestBacklink" );
		$this->assertStringContainsString( 'bs-reminder-overview-grid', $html );
	}
}
