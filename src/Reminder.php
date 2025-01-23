<?php

namespace BlueSpice\Reminder;

use MediaWiki\Config\Config;

abstract class Reminder implements IReminder {
	/**
	 *
	 * @var string
	 */
	protected $type = '';

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 *
	 * @param string $type
	 * @param Config $config
	 */
	protected function __construct( $type, Config $config ) {
		$this->type = $type;
		$this->config = $config;
	}

	/**
	 *
	 * @param string $type
	 * @param Config $config
	 * @return IReminder
	 */
	public static function factory( $type, Config $config ) {
		return new static( $type, $config );
	}
}
