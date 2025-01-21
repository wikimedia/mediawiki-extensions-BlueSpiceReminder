<?php

namespace MediaWiki\Extension\Workflows\Tests\Activity;

use BlueSpice\Reminder\Activity\SetReminderActivity;
use MediaWiki\Extension\Workflows\Definition\DefinitionContext;
use MediaWiki\Extension\Workflows\Definition\Element\Task;
use MediaWiki\Extension\Workflows\WorkflowContext;
use MediaWiki\Extension\Workflows\WorkflowContextMutable;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\User;
use MediaWiki\User\UserFactory;
use MediaWikiIntegrationTestCase;
use Wikimedia\Rdbms\Database;
use Wikimedia\Rdbms\LoadBalancer;

/**
 * @covers \BlueSpice\Reminder\Activity\SetReminderActivity
 * @group Database
 */
class SetReminderActivityTest extends MediaWikiIntegrationTestCase {

	/**
	 *
	 * @param array $data
	 * @param array $expectedDbData
	 *
	 * @covers \BlueSpice\Reminder\Activity\SetReminderActivity::execute
	 * @dataProvider provideCompleteItemData
	 *
	 */
	public function testCompleteItem( $data, $expectedDbData ) {
		$titleFactoryMock = $this->createMock( TitleFactory::class );
		$titleMock = $this->createMock( Title::class );
		$titleMock->method( 'getArticleId' )->willReturn( 1 );
		$titleMock->method( 'exists' )->willReturn( true );
		$titleFactoryMock->method( 'newFromText' )->willReturn( $titleMock );
		$userFactoryMock = $this->createMock( UserFactory::class );
		$user = $this->createMock( User::class );
		$user->method( 'getName' )->willReturn( 'Testuser' );
		$user->method( 'getId' )->willReturn( 2 );
		$user->method( 'isRegistered' )->willReturn( true );
		$userFactoryMock->method( 'newFromName' )->willReturn( $user );

		$connectionMock = $this->createMock( Database::class );
		$connectionMock->method( 'insert' )->willReturnCallback(
			function ( $table, $data, $method ) use ( $expectedDbData ) {
				$this->assertArrayEquals( $data, $expectedDbData );
				return true;
			}
		);
		$dbLoadBalancerMock = $this->createMock( LoadBalancer::class );
		$dbLoadBalancerMock->method( 'getConnection' )->willReturn( $connectionMock );

		$task = new Task( 'Test_Id', 'setReminder', [], [], 'task' );
		$activity = new SetReminderActivity( $titleFactoryMock, $userFactoryMock, $dbLoadBalancerMock, $task );

		$definitionContext = new DefinitionContext( [] );
		$mutableContext = new WorkflowContextMutable( $titleFactoryMock );
		$mutableContext->setDefinitionContext( $definitionContext );
		$workflowContext = new WorkflowContext( $mutableContext );
		$activity->execute( $data, $workflowContext );
	}

	/**
	 *
	 * @return array
	 */
	public function provideCompleteItemData() {
		return [
			'test1' => [
				'data' => [
					'page' => 'Dummy page',
					'content' => "My page content",
					'mode' => 'append',
					'username' => 'Testuser',
					'date' => '2022-06-01',
					'comment' => 'Dummy reminder'
				],
				'expected' => [
					'rem_user_id' => 2,
					'rem_page_id' => 1,
					'rem_date' => '2022-06-01',
					'rem_comment' => 'Dummy reminder',
					'rem_type' => 'page',
				]
			]
		];
	}
}
