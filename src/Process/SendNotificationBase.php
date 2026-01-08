<?php

namespace BlueSpice\Reminder\Process;

use BlueSpice\Reminder\RepeatingReminderDateCalculator;
use DateTime;
use Exception;
use MediaWiki\Json\FormatJson;
use MediaWiki\Title\Title;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\INotificationEvent;
use MWStake\MediaWiki\Component\Events\Notifier;
use MWStake\MediaWiki\Component\ProcessManager\IProcessStep;
use Wikimedia\Rdbms\ILoadBalancer;

abstract class SendNotificationBase implements IProcessStep {

	/** @var array */
	protected $queryConds = [];

	/** @var bool */
	protected $doUpdateRepeatingRemindersDate = false;

	/**
	 * @param ILoadBalancer $loadBalancer
	 * @param UserFactory $userFactory
	 * @param Notifier $notifier
	 * @param RepeatingReminderDateCalculator $reminderService
	 */
	public function __construct(
		private readonly ILoadBalancer $loadBalancer,
		private readonly UserFactory $userFactory,
		private readonly Notifier $notifier,
		private readonly RepeatingReminderDateCalculator $reminderService
	) {
	}

	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function execute( $data = [] ): array {
		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		$res = $dbr->select(
			'bs_reminder',
			'*',
			$this->queryConds,
			__METHOD__
		);

		$repeatingReminders = [];

		if ( $res && $res->numRows() ) {
			foreach ( $res as $row ) {
				$user = $this->userFactory->newFromId( $row->rem_user_id );
				$title = Title::newFromID( $row->rem_page_id );
				$comment = $row->rem_comment;
				if ( $user && $title ) {
					$event = $this->getEvent( $user, $title, $comment );
					$this->notifier->emit( $event );

					if ( $this->doUpdateRepeatingRemindersDate === true && (int)$row->rem_is_repeating === 1 ) {
						$repeatingReminders[] = $row;
					}
				}
			}
		}

		if ( $this->doUpdateRepeatingRemindersDate ) {
			$this->updateRepeatingRemindersDate( $repeatingReminders );
		}

		return [];
	}

	/**
	 * @param UserIdentity $user
	 * @param Title $title
	 * @param string $comment
	 * @return INotificationEvent
	 */
	abstract protected function getEvent( UserIdentity $user, Title $title, string $comment ): INotificationEvent;

	/**
	 * @param array $repeatingReminders
	 */
	protected function updateRepeatingRemindersDate( array $repeatingReminders ): void {
		if ( count( $repeatingReminders ) > 0 ) {
			foreach ( $repeatingReminders as $reminder ) {
				$currentDate = new DateTime();
				$nextReminderDate = $this->reminderService->getNextReminderDateFromGivenDate(
					$currentDate,
					FormatJson::decode( $reminder->rem_repeat_config )
				);

				if ( $nextReminderDate === false ) {
					wfDebugLog(
						'BS::Reminder',
						'Could not calculate next reminder date from '
						. $reminder->rem_repeat_config
					);
					continue;
				}

				$repeatDateEnd = DateTime::createFromFormat( 'YmdHis', $reminder->rem_repeat_date_end );

				if ( $repeatDateEnd->format( 'Y-m-d' ) >= $nextReminderDate->format( 'Y-m-d' ) ) {
					$dbw = $this->loadBalancer->getConnection( DB_PRIMARY );
					$dbw->update(
						'bs_reminder',
						[ 'rem_date' => $nextReminderDate->format( 'Y-m-d' ) ],
						[ 'rem_id' => $reminder->rem_id ],
						__METHOD__
					);
				}
			}
		}
	}

}
