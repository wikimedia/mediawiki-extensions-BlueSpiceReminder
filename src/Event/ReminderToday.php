<?php

namespace BlueSpice\Reminder\Event;

use MediaWiki\MediaWikiServices;
use MediaWiki\Page\PageIdentity;
use MediaWiki\User\UserIdentity;
use Message;
use MWStake\MediaWiki\Component\Events\BotAgent;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;
use MWStake\MediaWiki\Component\Events\TitleEvent;

class ReminderToday extends TitleEvent {

	/** @var UserIdentity */
	protected $targetUser;

	/** @var string */
	protected $comment;

	/**
	 * @param UserIdentity $target
	 * @param PageIdentity $title
	 * @param string $comment
	 */
	public function __construct( UserIdentity $target, PageIdentity $title, string $comment ) {
		parent::__construct( new BotAgent(), $title );
		$this->targetUser = $target;
		$this->comment = $comment;
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
		return Message::newFromKey( 'ext-notifyme-notification-generic-links-intro' );
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
