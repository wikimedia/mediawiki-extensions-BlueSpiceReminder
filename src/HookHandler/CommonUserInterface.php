<?php

namespace BlueSpice\Reminder\HookHandler;

use BlueSpice\Reminder\GlobalActionsManager;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class CommonUserInterface implements MWStakeCommonUIRegisterSkinSlotComponents {

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

}
