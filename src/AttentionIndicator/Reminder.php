<?php

namespace BlueSpice\Reminder\AttentionIndicator;

use BlueSpice\Discovery\AttentionIndicator;
use BlueSpice\Discovery\IAttentionIndicator;
use MediaWiki\Config\Config;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\User;
use Wikimedia\Rdbms\LoadBalancer;

class Reminder extends AttentionIndicator {

	public function __construct(
		string $key,
		Config $config,
		User $user,
		protected readonly LoadBalancer $lb,
		protected readonly TitleFactory $titleFactory,
	) {
		parent::__construct( $key, $config, $user );
	}

	/**
	 * @param string $key
	 * @param Config $config
	 * @param User $user
	 * @param MediaWikiServices $services
	 * @param LoadBalancer|null $lb
	 * @param TitleFactory|null $titleFactory
	 * @return IAttentionIndicator
	 */
	public static function factory(
		string $key, Config $config, User $user, MediaWikiServices $services,
		?LoadBalancer $lb = null, ?TitleFactory $titleFactory = null
	) {
		if ( !$lb ) {
			$lb = $services->getDBLoadBalancer();
		}
		if ( !$titleFactory ) {
			$titleFactory = $services->getTitleFactory();
		}
		return new static(
			$key,
			$config,
			$user,
			$lb,
			$titleFactory
		);
	}

	/**
	 * @return int
	 */
	protected function doIndicationCount(): int {
		$count = 0;
		$res = $this->lb->getConnection( DB_REPLICA )->select(
			'bs_reminder',
			[ 'rem_page_id' ],
			[ 'rem_user_id' => $this->user->getId(), "rem_date <= NOW()" ],
			__METHOD__
		);
		foreach ( $res as $row ) {
			$title = $this->titleFactory->newFromID( $row->rem_page_id );
			if ( !$title || !$title->exists() ) {
				continue;
			}
			$count++;
		}
		return $count;
	}

}
