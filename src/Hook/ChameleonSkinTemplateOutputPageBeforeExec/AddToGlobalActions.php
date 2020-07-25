<?php

namespace BlueSpice\Reminder\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddToGlobalActions extends ChameleonSkinTemplateOutputPageBeforeExec {
	protected function doProcess() {
		$oSpecialReminder = \MediaWiki\MediaWikiServices::getInstance()
			->getSpecialPageFactory()
			->getPage( 'Reminder' );

		if ( !$oSpecialReminder ) {
			return true;
		}

		$isAllowed = $this->getServices()->getPermissionManager()->userHasRight(
			$this->getContext()->getUser(),
			$oSpecialReminder->getRestriction()
		);
		if ( !$isAllowed ) {
			return true;
		}

		$this->mergeSkinDataArray(
			SkinData::GLOBAL_ACTIONS,
			[
				'bs-reminder' => [
					'href' => $oSpecialReminder->getPageTitle()->getFullURL(),
					'text' => $oSpecialReminder->getDescription(),
					'title' => $oSpecialReminder->getPageTitle(),
					'iconClass' => ' icon-flag ',
					'position' => 800,
					'data-permissions' => 'read'
				]
			]
		);

		return true;
	}
}
