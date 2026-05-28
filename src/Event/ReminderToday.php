<?php

namespace BlueSpice\Reminder\Event;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Page\PageIdentity;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\BotAgent;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;
use MWStake\MediaWiki\Component\Events\TitleEvent;

class ReminderToday extends TitleEvent {

	public function __construct(
		protected readonly UserIdentity $targetUser,
		PageIdentity $title,
		protected readonly string $comment,
	) {
		parent::__construct( new BotAgent(), $title );
	}

	/**
	 * @return Message
	 */
	public function getKeyMessage(): Message {
		return Message::newFromKey( 'bs-reminder-event-today-desc' );
	}

	/**
	 * @inheritDoc
	 */
	public function getMessage( IChannel $forChannel ): Message {
		$msgKey = $this->getMessageKey();
		if ( $this->comment ) {
			return Message::newFromKey( $msgKey . '-with-comment' )->params(
				$this->getTitle()->getFullURL(),
				$this->getTitleDisplayText(),
				$this->comment
			);
		}
		return Message::newFromKey( $msgKey )->params(
			$this->getTitle()->getFullURL(),
			$this->getTitleDisplayText()
		);
	}

	/**
	 * @return string
	 */
	public function getMessageKey(): string {
		return 'bs-reminder-event-today';
	}

	/**
	 * @inheritDoc
	 */
	public function getLinksIntroMessage( IChannel $forChannel ): ?Message {
		return Message::newFromKey( 'bs-reminder-notification-generic-links-intro' );
	}

	/**
	 * @return UserIdentity[]|null
	 */
	public function getPresetSubscribers(): ?array {
		return [ $this->targetUser ];
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'bs-reminder-today';
	}

	/**
	 * @param UserIdentity $agent
	 * @param MediaWikiServices $services
	 * @param array $extra
	 * @return array
	 */
	public static function getArgsForTesting(
		UserIdentity $agent, MediaWikiServices $services, array $extra = []
	): array {
		return [
			$extra['targetUser'] ?? $services->getUserFactory()->newFromName( 'WikiSysop' ),
			$extra['title'] ?? $services->getTitleFactory()->newMainPage(),
			'dummy comment'
		];
	}
}
