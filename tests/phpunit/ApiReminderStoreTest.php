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

	protected function setUp() {
		parent::setUp();
		$this->tablesUsed[] = 'bs_reminder';
		$this->doLogin();
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

	/**
	 * @throws Exception
	 */
	public function testMakeData_catchAnonUser() {
		$aData = $this->doApiRequest( [
			'action' => 'bs-reminder-store',
			'token' => '+\\'
		],  null, false, new User );
		$this->assertTrue( isset( $aData[0]['total'] ), 'API did not report total number of results' );
		$this->assertEquals( $aData[0]['total'], 0 );
	}

	/**
	 * @param $aTaskData
	 * @param $aExpected
	 * @internal param $aRequest
	 * @internal param $sExpectedError
	 * @dataProvider provideGetDetailsForReminderData
	 */
	public function testMakeData_getReminders( $aTaskData, $aExpected ) {
		self::$users['sysop']->getUser()->setOption( "MW::Reminder::ShowAllReminders", true );
		$this->addTestReminderToDb();
		$aData = $this->doApiRequestWithToken( [
			'action' => 'bs-reminder-store'
		],  null, null );
		// reset option?
		$this->assertTrue( isset( $aData[0]['total'] ), 'API did not report total number of results' );
		$this->assertEquals( $aData[0]['total'], 1 );
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