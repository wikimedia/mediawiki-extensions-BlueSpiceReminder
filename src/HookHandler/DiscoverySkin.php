<?php

namespace BlueSpice\Reminder\HookHandler;

use BlueSpice\Discovery\Hook\BlueSpiceDiscoveryTemplateDataProviderAfterInit;
use BlueSpice\Discovery\ITemplateDataProvider;
use BlueSpice\Reminder\GlobalActionsManager;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class DiscoverySkin implements
	MWStakeCommonUIRegisterSkinSlotComponents,
	BlueSpiceDiscoveryTemplateDataProviderAfterInit
{

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			'GlobalActionsManager',
			[
				'special-bluespice-reminder' => [
					'factory' => static function () {
						return new GlobalActionsManager();
					}
				]
			]
		);
	}

	/**
	 *
	 * @param ITemplateDataProvider $registry
	 * @return void
	 */
	public function onBlueSpiceDiscoveryTemplateDataProviderAfterInit( $registry ): void {
		$registry->unregister( 'toolbox', 'ca-reminderCreate' );
		$registry->register( 'actions_secondary', 'ca-reminderCreate' );
	}
}
