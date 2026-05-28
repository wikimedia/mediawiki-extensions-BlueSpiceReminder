<?php

namespace BlueSpice\Reminder\Activity;

use DateTime;
use MediaWiki\Extension\Workflows\Activity\ExecutionStatus;
use MediaWiki\Extension\Workflows\Activity\GenericActivity;
use MediaWiki\Extension\Workflows\Definition\ITask;
use MediaWiki\Extension\Workflows\Exception\WorkflowExecutionException;
use MediaWiki\Extension\Workflows\WorkflowContext;
use MediaWiki\Message\Message;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\User;
use MediaWiki\User\UserFactory;
use Wikimedia\Rdbms\ILoadBalancer;

class SetReminderActivity extends GenericActivity {
	/** @var Title */
	private $title;
	/** @var User */
	private $user;
	/** @var string */
	private $date;
	/** @var string */
	private $comment;

	public function __construct(
		private readonly TitleFactory $titleFactory,
		private readonly UserFactory $userFactory,
		private readonly ILoadBalancer $loadBalancer,
		ITask $task,
	) {
		parent::__construct( $task );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $data, WorkflowContext $context ): ExecutionStatus {
		$this->processData( $data );

		$db = $this->loadBalancer->getConnection( DB_PRIMARY );
		$res = $db->insert(
			'bs_reminder',
			[
				'rem_user_id' => $this->user->getId(),
				'rem_page_id' => $this->title->getArticleID(),
				'rem_date' => $this->date,
				'rem_comment' => addslashes( $this->comment ),
				'rem_type' => 'page',
			],
			__METHOD__
		);

		if ( !$res ) {
			throw new WorkflowExecutionException(
				Message::newFromKey( 'bs-reminder-save-error-unkown' )->text(),
				$this->getTask()
			);
		}

		return new ExecutionStatus( self::STATUS_COMPLETE, [
			'reminderId' => $db->insertId(),
		] );
	}

	/**
	 * @param array $data
	 */
	private function processData( $data ) {
		$this->title = $this->titleFactory->newFromText( $data['page'] );
		if ( !( $this->title instanceof Title ) || !$this->title->exists() ) {
			// Previous validation is assumed
			throw new WorkflowExecutionException(
				Message::newFromKey( 'workflows-error-generic' )->text(),
				$this->getTask()
			);
		}

		$this->user = $this->userFactory->newFromName( $data['username'] );
		if ( !( $this->user instanceof User ) || !$this->user->isRegistered() ) {
			// Previous validation is assumed
			throw new WorkflowExecutionException(
				Message::newFromKey( 'workflows-error-generic' )->text(),
				$this->getTask()
			);
		}

		$this->date = $this->parseDate( $data['date'] );
		$this->comment = $data['comment'] ?? '';
	}

	/**
	 * @param string $date
	 * @return string Properly formatted date
	 * @throws WorkflowExecutionException
	 */
	private function parseDate( $date ) {
		try {
			$date = new DateTime( $date );
		} catch ( \Exception $ex ) {
			throw new WorkflowExecutionException( $ex->getMessage(), $this->task );
		}

		return $date->format( 'Y-m-d' );
	}
}
