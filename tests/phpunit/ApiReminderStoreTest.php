<?php
/**
 * Class ApiReminderStoreTest
 * @group Broken
 * @group medium
 * @group API
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceReminder
 *
 * @covers ApiReminderStore
 */
class ApiReminderStoreTest extends ApiTestCase {

	protected function setUp(): void {
		parent::setUp();
		$this->tablesUsed[] = 'bs_reminder';
	}

	protected function getTokens() {
		return $this->getTokenList( self::$users['sysop'] );
	}

	public function testApiReminderClassExists() {
		$this->assertTrue(
			class_exists( 'ApiReminderStore' ),
			'Class ApiReminderStore does not exist'
		);
	}

	public function testApiReminderClassHasPublicMethods() {
		$this->assertTrue(
			method_exists( 'ApiReminderStore', 'makeData' )
		);
	}

	public function testMakeData_catchAnonUser() {
		$aData = $this->doApiRequest( [
			'action' => 'bs-reminder-store',
			'token' => '+\\'
		],  null, false, new User );
		$this->assertTrue( isset( $aData[0]['total'] ), 'API did not report total number of results' );
		$this->assertSame( 0, $aData[0]['total'] );
	}

	/**
	 * @dataProvider provideGetDetailsForReminderData
	 */
	public function testMakeData_getReminders( $aTaskData, $aExpected ) {
		$this->addTestReminderToDb();
		$aData = $this->doApiRequestWithToken( [
			'action' => 'bs-reminder-store'
		],  null, null );
		// reset option?
		$this->assertTrue( isset( $aData[0]['total'] ), 'API did not report total number of results' );
		$this->assertSame( 1, $aData[0]['total'] );
	}

	public function provideGetDetailsForReminderData() {
		$workingParams = [
			'onlyArticleId' => [
				'taskData' => [
					'articleId' => 1
				],
				// Careful here: the first item is a key which identifies the testuser in self::$users
				'expected' => [
					'userId' => 'sysop',
					'id' => '3',
					'date' => '2016-11-25',
					'articleId' => '1',
					'comment' => 'Testing Reminder'
				]
			]
		];
		return $workingParams;
	}

	/**
	 * Adds one reminder entry to the database for testing
	 */
	private function addTestReminderToDb() {
		$this->db->insert(
			'bs_reminder',
			[
				'rem_id' => 3,
				'rem_user_id' => static::$users[ 'sysop' ]->getUser()->getId(),
				'rem_page_id' => 1,
				'rem_date' => '2016-11-25',
				'rem_comment' => 'Testing Reminder'
			]
		);
	}
}
