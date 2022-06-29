<?php

namespace BlueSpice\Reminder\Activity;

use DateTime;
use MediaWiki\Extension\Workflows\Activity\ExecutionStatus;
use MediaWiki\Extension\Workflows\Activity\GenericActivity;
use MediaWiki\Extension\Workflows\Definition\ITask;
use MediaWiki\Extension\Workflows\Exception\WorkflowExecutionException;
use MediaWiki\Extension\Workflows\WorkflowContext;
use MediaWiki\User\UserFactory;
use Message;
use Title;
use TitleFactory;
use User;
use Wikimedia\Rdbms\ILoadBalancer;

class SetReminderActivity extends GenericActivity {
	/** @var TitleFactory */
	private $titleFactory;
	/** @var UserFactory */
	private $userFactory;
	/** @var ILoadBalancer */
	private $loadBalancer;
	/** @var Title */
	private $title;
	/** @var User */
	private $user;
	/** @var string */
	private $date;
	/** @var string */
	private $comment;

	/**
	 * @param TitleFactory $titleFactory
	 * @param UserFactory $userFactory
	 * @param ILoadBalancer $lb
	 * @param ITask $task
	 */
	public function __construct(
		TitleFactory $titleFactory, UserFactory $userFactory, ILoadBalancer $lb, ITask $task
	) {
		parent::__construct( $task );
		$this->titleFactory = $titleFactory;
		$this->userFactory = $userFactory;
		$this->loadBalancer = $lb;
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
