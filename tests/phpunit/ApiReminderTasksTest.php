<?php /** @noinspection LongInheritanceChainInspection */
/**
 * Class ApiReminderTasksTest
 * @group Broken
 * @group medium
 * @group API
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceReminder
 *
 * @covers ApiReminderTasks
 *
 * IDEAS
 * - create a standard test set, eg. isAnon, all task parameters set etc.
 * - convention: catch for unknown parameters
 *
 * FINDINGS
 * - for API tests:
 *   - use ApiTestCase
 *   - @group must be medium or large
 *
 * QUESTIONS
 * - how to test anon usage
 * - how to make non destructive
 *
 * TODO
 * - check results of working examples
 *
 */
class ApiReminderTasksTest extends ApiTestCase {

	protected function setUp(): void {
		parent::setUp();
		$this->tablesUsed[] = 'bs_reminder';
	}

	protected function getTokens() {
		return $this->getTokenList( self::$users['sysop'] );
	}

	public function testApiReminderClassExists() {
		$this->assertTrue(
			class_exists( 'ApiReminderTasks' ),
			'Class ApiReminderTasks does not exist'
		);
	}

	public function testApiReminderClassHasPublicMethods() {
		$this->assertTrue(
			method_exists( 'ApiReminderTasks', 'task_getDetailsForReminder' )
		);
		$this->assertTrue(
			method_exists( 'ApiReminderTasks', 'task_saveReminder' )
		);
		$this->assertTrue(
			method_exists( 'ApiReminderTasks', 'task_deleteReminder' )
		);
	}

	public function testSaveReminder_catchAnonUser() {
		// You could also retrieve the edit toke for an anonymous user via Api.
		// This seems unneccessary right now, but might be useful in the future.
		// So leaving this code here for reference.
		// self::$users['anonymous'] = new stdClass();
		// self::$users['anonymous']->user = new User;
		// $tokens = $this->getTokenList( self::$users['anonymous'] ); // returns   'edittoken' => '+\\',
		$data = $this->doApiRequest( [
			'action' => 'bs-reminder-tasks',
			'task' => 'saveReminder',
			'taskData' => [],
			'token' => '+\\'
		],  null, false, new User );
		$this->assertTrue( isset( $data[0]['errors']['permissionError'] ) );
	}

	/**
	 * @dataProvider provideFalseSaveReminderData
	 */
	public function testSaveReminder_catchFalseRequests( $aRequest, $sExpectedError ) {
		$data = $this->doApiRequestWithToken( [
			'action' => 'bs-reminder-tasks',
			'task' => 'saveReminder',
			'taskData' => json_encode( $aRequest )
		],  null, null );
		$this->assertTrue( isset( $data[0]['errors'][$sExpectedError] ) );
	}

	public static function provideFalseSaveReminderData() {
		$falseParams = [
			'undefinedArticleId' => [
				'taskData' => [],
				'expectedError' => 'unknown-page'
			],
			'unknownArticleId' => [
				'taskData' => [
					'articleId' => 0
				],
				'expectedError' => 'unknown-page'
			],
			'hackingArticleId' => [
				'taskData' => [
					'articleId' => 'any string'
				],
				'expectedError' => 'unknown-page'
			],
			// undefined comment should result in empty comment
/*            'undefinedComment' => [
				'taskData' => [
					'articleId' => 1,
					'userName' => 'UnknownUser'
				],
				'expectedError' => 'undefined-comment'
			],*/
			// user adds reminder to self
/*            'undefinedUser' => [
				'taskData' => [
					'articleId' => 1
				],
				'expectedError' => 'unknown-user'
			],*/
			'unknownUser' => [
				'taskData' => [
					'articleId' => 1,
					'userName' => 'UnknownUser'
				],
				'expectedError' => 'unknown-user'
			],
			'hackingUser' => [
				'taskData' => [
					'articleId' => 1,
					'userName' => 'any string'
				],
				'expectedError' => 'unknown-user'
			],
			// undefined target user should result in logged in user being the target user
/*            'undefinedTargetUser' => [
				'taskData' => [
					'articleId' => 1,
					'comment' => 'Testing reminder'
				],
				'expectedError' => 'unknown-user'
			],*/
			'unknownTargetUser' => [
				'taskData' => [
					'articleId' => 1,
					'comment' => 'Testing reminder',
					'userName' => 'UnknownUser'
				],
				'expectedError' => 'unknown-user'
			],
			'hackingTargetUser' => [
				'taskData' => [
					'articleId' => 1,
					'comment' => 'Testing reminder',
					'userName' => 'any string'
				],
				'expectedError' => 'unknown-user'
			],
			// undefined reminder id results in new reminder
/*            'undefinedReminderId' => [
				'taskData' => [
					'articleId' => 1
				],
				'expectedError' => 'unknown-user'
			],*/
			'unknownReminderId' => [
				'taskData' => [
					'articleId' => 1,
					'id' => -1
				],
				'expectedError' => 'noactions'
			],
			'hackingReminderId' => [
				'taskData' => [
					'articleId' => 1,
					'id' => 'any string'
				],
				'expectedError' => 'noactions'
			],
		];
		return $falseParams;
	}

	public function testSaveReminder_checksPermissionReminderEditAll() {
		$GLOBALS['wgGroupPermissions']['sysop']['remindereditall'] = false;
		$data = $this->doApiRequestWithToken( [
			'action' => 'bs-reminder-tasks',
			'task' => 'saveReminder',
			'taskData' => json_encode( [
				'articleId' => 1,
				'userName' => 'Apitestuser'
			] )
		],  null, null );
		$GLOBALS['wgGroupPermissions']['sysop']['remindereditall'] = true;
		$this->assertTrue( isset( $data[0]['errors']['user'] ) );
	}

	public function testSaveReminder_checksSaveHookFailure() {
		$originalHookList = isset( $GLOBALS['wgHooks']['BsReminderOnSave'] )
			? $GLOBALS['wgHooks']['BsReminderOnSave']
			: [];
		$GLOBALS['wgHooks']['BsReminderOnSave'][]
			= 'ApiReminderTasksTest::onBsReminderOnSave';
		$data = $this->doApiRequestWithToken( [
			'action' => 'bs-reminder-tasks',
			'task' => 'saveReminder',
			'taskData' => json_encode( [
				'articleId' => 1,
				'userName' => 'Apitestuser'
			] )
		],  null, null );
		$GLOBALS['wgHooks']['BsReminderOnSave'] = $originalHookList;
		$this->assertTrue( isset( $data[0]['errors']['createerror'] ) );
	}

	public function testSaveReminder_checksUpdateHookFailure() {
		$originalHookList = isset( $GLOBALS['wgHooks']['BsReminderOnUpdate'] )
			? $GLOBALS['wgHooks']['BsReminderOnUpdate']
			: [];
		$GLOBALS['wgHooks']['BsReminderOnUpdate'][]
			= 'ApiReminderTasksTest::onBsReminderOnUpdate';
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
		$data = $this->doApiRequestWithToken( [
			'action' => 'bs-reminder-tasks',
			'task' => 'saveReminder',
			'taskData' => json_encode( [
				'articleId' => 1,
				'comment' => 'Testing Edit Reminder',
				'userName' => 'Apitestsysop',
				'id' => 3
			] )
		],  null, null );
		$GLOBALS['wgHooks']['BsReminderOnUpdate'] = $originalHookList;
		$this->assertTrue( isset( $data[0]['errors']['createerror'] ) );
	}

	public function testSaveReminder_checksDbExistenceCheckFailure() {
		$this->db->query(
			'ALTER TABLE `'
			. $this->dbPrefix()
			. 'bs_reminder` CHANGE  `rem_id` `rem_id_disabled` INT(10) NOT NULL AUTO_INCREMENT;'
		);
		$data = $this->doApiRequestWithToken( [
			'action' => 'bs-reminder-tasks',
			'task' => 'saveReminder',
			'taskData' => json_encode( [
				'articleId' => 1,
				'comment' => 'Testing Edit Reminder',
				'userName' => 'Apitestsysop',
				'id' => 3
			] )
		],  null, null );
		$this->db->query(
			'ALTER TABLE `'
			. $this->dbPrefix()
			. 'bs_reminder` CHANGE  `rem_id_disabled` `rem_id` INT(10) NOT NULL AUTO_INCREMENT;'
		);
		$this->assertTrue( isset( $data[0]['errors']['noactions'] ) );
	}

	public function testSaveReminder_checksDbInsertFailure() {
		$this->db->query(
			'ALTER TABLE `'
			. $this->dbPrefix()
			. 'bs_reminder` CHANGE  `rem_user_id` `rem_user_id_disabled` INT(10) NOT NULL;'
		);
		$data = $this->doApiRequestWithToken( [
			'action' => 'bs-reminder-tasks',
			'task' => 'saveReminder',
			'taskData' => json_encode( [
				'articleId' => 1,
				'comment' => 'Testing Edit Reminder',
				'userName' => 'Apitestsysop'
			] )
		],  null, null );
		$this->db->query(
			'ALTER TABLE `'
			. $this->dbPrefix()
			. 'bs_reminder` CHANGE  `rem_user_id_disabled` `rem_user_id` INT(10) NOT NULL;'
		);
		$this->assertTrue( isset( $data[0]['errors']['createerror'] ) );
	}

	public function testSaveReminder_checksDbUpdateFailure() {
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
		$this->db->query(
			'ALTER TABLE `'
			. $this->dbPrefix()
			. 'bs_reminder` CHANGE  `rem_user_id` `rem_user_id_disabled` INT(10) NOT NULL;'
		);
		$data = $this->doApiRequestWithToken( [
			'action' => 'bs-reminder-tasks',
			'task' => 'saveReminder',
			'taskData' => json_encode( [
				'articleId' => 1,
				'comment' => 'Testing Edit Reminder',
				'userName' => 'Apitestsysop',
				'id' => 3
			] )
		],  null, null );
		$this->db->query(
			'ALTER TABLE `'
			. $this->dbPrefix()
			. 'bs_reminder` CHANGE  `rem_user_id_disabled` `rem_user_id` INT(10) NOT NULL;'
		);
		$this->assertTrue( isset( $data[0]['errors']['updateerror'] ) );
	}

	/**
	 * @dataProvider provideCreateReminderData
	 */
	public function testSaveReminder_createReminder( $aTaskData, $aExpected ) {
		$data = $this->doApiRequestWithToken( [
			'action' => 'bs-reminder-tasks',
			'task' => 'saveReminder',
			'taskData' => json_encode( $aTaskData )
		],  null, null );
		$this->assertTrue(
			isset( $data[0]['success'] ),
			'API did not report status "success"'
		);
		$this->assertTrue(
			isset( $data[0]['payload']['id'] ),
			'API returned no reminder ID'
		);
		$iReminderId = $data[0]['payload']['id'];
		$this->assertSelect(
			'bs_reminder',
			[ 'rem_user_id', 'rem_page_id', 'rem_comment' ],
			[ 'rem_id' => $iReminderId ],
			[ [
				static::$users[ $aExpected[0] ]->getUser()->getId(),
				$aExpected[1],
				$aExpected[2]
			] ]
		);
	}

	public function provideCreateReminderData() {
		// TODO: test date
		$workingParams = [
			'onlyArticleId' => [
				'taskData' => [
					'articleId' => 1
				],
				// Careful here: the first item is a key which identifies the testuser
				// in self::$users
				'expected' => [ 'sysop', '1', '' ]
			],
			'articleIdAndUser' => [
				'taskData' => [
					'articleId' => 1,
					'userName' => 'Apitestuser'
				],
				'expected' => [ 'uploader', '1', '' ]
			],
			'articleIdAndComment' => [
				'taskData' => [
					'articleId' => 1,
					'comment' => 'Testing Reminder'
				],
				'expected' => [ 'sysop', '1', 'Testing Reminder' ]
			],
			'articleIdAndUserAndComment' => [
				'taskData' => [
					'articleId' => 1,
					'comment' => 'Testing Reminder',
					'userName' => 'Apitestuser'
				],
				'expected' => [ 'uploader', '1', 'Testing Reminder' ]
			]
		];
		return $workingParams;
	}

	/**
	 * @dataProvider provideEditReminderData
	 */
	public function testSaveReminder_editReminder( $aTaskData, $aExpected ) {
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
		$data = $this->doApiRequestWithToken( [
			'action' => 'bs-reminder-tasks',
			'task' => 'saveReminder',
			'taskData' => json_encode( $aTaskData )
		],  null, null );
		$this->assertTrue(
			isset( $data[0]['success'] ),
			'API did not report status "success"'
		);
		$this->assertTrue(
			isset( $data[0]['payload']['id'] ),
			'API returned no reminder ID'
		);
		$iReminderId = $data[0]['payload']['id'];
		$this->assertSelect(
			'bs_reminder',
			[ 'rem_user_id', 'rem_page_id', 'rem_comment' ],
			[ 'rem_id' => $iReminderId ],
			[ [
				static::$users[ $aExpected[0] ]->getUser()->getId(),
				$aExpected[1],
				$aExpected[2]
			] ]
		);
	}

	public function provideEditReminderData() {
		$workingParams = [
			'articleIdAndUserAndCommentAndReminderId' => [
				'taskData' => [
					'articleId' => 1,
					'comment' => 'Testing Edit Reminder',
					'userName' => 'Apitestsysop',
					'id' => 3
				],
				'expected' => [ 'sysop', '1', 'Testing Edit Reminder' ]
			]
		];
		return $workingParams;
	}

	public function testDeleteReminder_catchAnonUser() {
		$data = $this->doApiRequest( [
			'action' => 'bs-reminder-tasks',
			'task' => 'deleteReminder',
			'taskData' => [],
			'token' => '+\\'
		],  null, false, new User );
		$this->assertTrue( isset( $data[0]['errors']['permissionError'] ) );
	}

	/**
	 * @dataProvider provideFalseDeleteReminderData
	 */
	public function testDeleteReminder_catchFalseRequests( $aRequest, $sExpectedError ) {
		$data = $this->doApiRequestWithToken( [
			'action' => 'bs-reminder-tasks',
			'task' => 'deleteReminder',
			'taskData' => json_encode( $aRequest )
		],  null, null );
		$this->assertTrue( isset( $data[0]['errors'][$sExpectedError] ) );
	}

	public function testDeleteReminder_checksPermissionReminderEditAll() {
		$GLOBALS['wgGroupPermissions']['sysop']['remindereditall'] = false;
		$data = $this->doApiRequestWithToken( [
			'action' => 'bs-reminder-tasks',
			'task' => 'deleteReminder',
			'taskData' => json_encode( [
				'reminderId' => 1,
				'userName' => 'Apitestuser'
			] )
		],  null, null );
		$GLOBALS['wgGroupPermissions']['sysop']['remindereditall'] = true;
		$this->assertTrue( isset( $data[0]['errors']['reminderId'] ) );
	}

	public function testDeleteReminder_checksDeleteHookFailure() {
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
		$originalHookList = isset( $GLOBALS['wgHooks']['BsReminderDeleteReminder'] )
			? $GLOBALS['wgHooks']['BsReminderDeleteReminder']
			: [];
		$GLOBALS['wgHooks']['BsReminderDeleteReminder'][]
			= 'ApiReminderTasksTest::onBsReminderDeleteReminder';
		$data = $this->doApiRequestWithToken( [
			'action' => 'bs-reminder-tasks',
			'task' => 'deleteReminder',
			'taskData' => json_encode( [
				'reminderId' => 3
			] )
		],  null, null );
		$GLOBALS['wgHooks']['BsReminderDeleteReminder'] = $originalHookList;
		$this->assertTrue( isset( $data[0]['errors']['deletehookerror'] ) );
	}

	public function testDeleteReminder_checksDbDeleteFailure() {
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
		$this->db->query(
			'ALTER TABLE `'
			. $this->dbPrefix()
			. 'bs_reminder` CHANGE `rem_id` `rem_id_disabled` INT(10) NOT NULL AUTO_INCREMENT;'
		);
		$data = $this->doApiRequestWithToken( [
			'action' => 'bs-reminder-tasks',
			'task' => 'deleteReminder',
			'taskData' => json_encode( [ 'reminderId' => 3 ] )
		],  null, null );
		$this->db->query(
			'ALTER TABLE `'
			. $this->dbPrefix()
			. 'bs_reminder` CHANGE  `rem_id_disabled` `rem_id` INT(10) NOT NULL AUTO_INCREMENT;'
		);
		$this->assertTrue( isset( $data[0]['errors']['saving'] ) );
	}

	/**
	 * @dataProvider provideDeleteReminderData
	 */
	public function testDeleteReminder_deleteReminder( $aTaskData ) {
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
		$data = $this->doApiRequestWithToken( [
			'action' => 'bs-reminder-tasks',
			'task' => 'deleteReminder',
			'taskData' => json_encode( $aTaskData )
		],  null, null );
		$this->assertTrue(
			isset( $data[0]['success'] ),
			'API did not report status "success"'
		);
		$this->assertTrue(
			isset( $data[0]['payload']['id'] ),
			'API returned no reminder ID'
		);
		$iReminderId = $data[0]['payload']['id'];
		$this->assertSelect(
			'bs_reminder',
			[ 'rem_user_id', 'rem_page_id', 'rem_comment' ],
			[ 'rem_id' => $iReminderId ],
			[]
		);
	}

	public function testGetDetailsForReminder_catchAnonUser() {
		$data = $this->doApiRequest( [
			'action' => 'bs-reminder-tasks',
			'task' => 'getDetailsForReminder',
			'taskData' => [],
			'token' => '+\\'
		],  null, false, new User );
		$this->assertTrue( isset( $data[0]['errors']['permissionError'] ) );
	}

	/**
	 * @dataProvider provideFalseGetDetailsForReminderData
	 */
	public function testGetDetailsForReminder_catchFalseRequests( $aRequest, $sExpectedError ) {
		$data = $this->doApiRequestWithToken( [
			'action' => 'bs-reminder-tasks',
			'task' => 'getDetailsForReminder',
			'taskData' => json_encode( $aRequest )
		],  null, null );
		$this->assertTrue( isset( $data[0]['errors'][$sExpectedError] ) );
	}

	public function testGetDetailsForReminder_checksDbReadFailure() {
		$this->addTestReminderToDb();
		$this->db->query(
			'ALTER TABLE `'
			. $this->dbPrefix()
			. 'bs_reminder` CHANGE `rem_id` `rem_id_disabled` INT(10) NOT NULL AUTO_INCREMENT;'
		);
		$data = $this->doApiRequestWithToken( [
			'action' => 'bs-reminder-tasks',
			'task' => 'getDetailsForReminder',
			'taskData' => json_encode( [ 'articleId' => 1 ] )
		],  null, null );
		$this->db->query(
			'ALTER TABLE `'
			. $this->dbPrefix()
			. 'bs_reminder` CHANGE  `rem_id_disabled` `rem_id` INT(10) NOT NULL AUTO_INCREMENT;'
		);
		$this->assertTrue( isset( $data[0]['errors']['reminderId'] ) );
	}

	public static function provideFalseGetDetailsForReminderData() {
		$falseParams = [
			'undefinedArticleId' => [
				'taskData' => [],
				'expectedError' => 'reminderId'
			]
		];
		return $falseParams;
	}

	public function testGetDetailsForReminder_checksPermissionReminderEditAll() {
		$this->addTestReminderToDb();
		$GLOBALS['wgGroupPermissions']['user']['remindereditall'] = false;
		$data = $this->doApiRequestWithToken( [
			'action' => 'bs-reminder-tasks',
			'task' => 'getDetailsForReminder',
			'taskData' => json_encode( [
				'articleId' => 1
			] )
		],  null, static::$users[ 'uploader' ]->getUser() );
		$GLOBALS['wgGroupPermissions']['user']['remindereditall'] = true;
		$this->assertFalse( isset( $data[0]['payload']['id'] ) );
	}

	/**
	 * @dataProvider provideGetDetailsForReminderData
	 */
	public function testGetDetailsForReminder_getReminder( $aTaskData, $aExpected ) {
		$this->addTestReminderToDb();
		$aData = $this->doApiRequestWithToken( [
			'action' => 'bs-reminder-tasks',
			'task' => 'getDetailsForReminder',
			'taskData' => json_encode( $aTaskData )
		],  null, null );

		$this->assertTrue(
			isset( $aData[0]['success'] ),
			'API did not report status "success"'
		);
		$this->assertTrue(
			isset( $aData[0]['payload'] ),
			'API did not return payload data'
		);

		$aPayload = $aData[0]['payload'];
		$this->assertTrue(
			isset( $aPayload['id'] ),
			'Result does not contain id field'
		);
		$this->assertTrue(
			isset( $aPayload['date'] ),
			'Result does not contain date field'
		);
		$this->assertTrue(
			isset( $aPayload['userId'] ),
			'Result does not contain userId field'
		);
		$this->assertTrue(
			isset( $aPayload['articleId'] ),
			'Result does not contain articleId field'
		);
		$this->assertTrue(
			isset( $aPayload['comment'] ),
			'Result does not contain comment field'
		);

		$this->assertEquals(
			$aPayload['id'],
			$aExpected['id']
		);
		$this->assertEquals(
			$aPayload['date'],
			$aExpected['date']
		);
		$this->assertEquals(
			$aPayload['userId'],
			static::$users[ $aExpected['userId'] ]->getUser()->getId()
		);
		$this->assertEquals(
			$aPayload['articleId'],
			$aExpected['articleId']
		);
		$this->assertEquals(
			$aPayload['comment'],
			$aExpected['comment']
		);
	}

	public function provideGetDetailsForReminderData() {
		$workingParams = [
			'onlyArticleId' => [
				'taskData' => [
					'articleId' => 1
				],
				// Careful here: the first item is a key which identifies the testuser
				// in self::$users
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

	public function provideDeleteReminderData() {
		$workingParams = [
			'articleIdAndUserAndCommentAndReminderId' => [
				'taskData' => [
					'reminderId' => 3
				]
			]
		];
		return $workingParams;
	}

	public function provideFalseDeleteReminderData() {
		$falseParams = [
			'missingReminderId' => [
				'taskData' => [],
				'expectedError' => 'reminderId'
			]
		];
		return $falseParams;
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

	public static function onBsReminderOnSave( $oTaskData, $iReminderId, $iArticleId, $iUserId ) {
		throw new MWException();
	}

	public static function onBsReminderOnUpdate( $oTaskData, $iReminderId ) {
		throw new MWException();
	}

	public static function onBsReminderDeleteReminder( $oTaskData, &$oResult ) {
		$oResult->success = false;
		$oResult->message = $oResult->errors['deletehookerror'] = 'test';
		return true;
	}
}
