<?php

namespace BlueSpice\Reminder;

use MediaWiki\Config\Config;

abstract class Reminder implements IReminder {
	public function getType(): string {
		return $this->type;
	}

	protected function __construct(
		protected readonly string $type,
		protected readonly Config $config,
	) {
	}

	public static function factory( string $type, Config $config ): IReminder {
		return new static( $type, $config );
	}
}
