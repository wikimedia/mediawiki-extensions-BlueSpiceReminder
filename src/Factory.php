<?php

namespace BlueSpice\Reminder;

use BlueSpice\ExtensionAttributeBasedRegistry;
use MediaWiki\Config\Config;

class Factory {

	/**
	 *
	 * @var ExtensionAttributeBasedRegistry
	 */
	protected $registry = null;

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @param ExtensionAttributeBasedRegistry $registry
	 * @param Config $config
	 */
	public function __construct( ExtensionAttributeBasedRegistry $registry, Config $config ) {
		$this->registry = $registry;
		$this->config = $config;
	}

	/**
	 *
	 * @param string $type
	 * @return IReminder|null
	 */
	public function newFromType( $type ) {
		if ( empty( $type ) ) {
			$type = 'page';
		}
		$callback = $this->registry->getValue( $type, '' );
		if ( !is_callable( $callback ) ) {
			return null;
		}
		$instance = call_user_func_array( $callback, [ $type, $this->config ] );
		return $instance;
	}

	/**
	 *
	 * @param string $type
	 * @return bool
	 */
	public function isRegisteredType( $type ) {
		if ( empty( $type ) ) {
			$type = 'page';
		}
		return in_array( $type, $this->getRegisteredTypes() );
	}

	/**
	 *
	 * @return string[]
	 */
	public function getRegisteredTypes() {
		return array_merge( $this->registry->getAllKeys(), [ '' ] );
	}
}
