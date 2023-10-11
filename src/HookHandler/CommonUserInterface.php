<?php

namespace BlueSpice\Reminder\HookHandler;

use BlueSpice\Reminder\GlobalActionsEditing;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class CommonUserInterface implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			'GlobalActionsEditing',
			[
				'special-bluespice-reminder' => [
					'factory' => static function () {
						return new GlobalActionsEditing();
					}
				]
			]
		);
	}

}
