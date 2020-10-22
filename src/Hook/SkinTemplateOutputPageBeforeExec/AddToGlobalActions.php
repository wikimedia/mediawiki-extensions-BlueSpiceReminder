<?php

namespace BlueSpice\Reminder\Hook\SkinTemplateOutputPageBeforeExec;

use BlueSpice\Hook\SkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddToGlobalActions extends SkinTemplateOutputPageBeforeExec {
	protected function doProcess() {
		$oSpecialReminder = \SpecialPageFactory::getPage( 'Reminder' );

		if ( !$oSpecialReminder ) {
			return true;
		}

		if ( !$this->getContext()
			->getUser()
			->isAllowed( $oSpecialReminder->getRestriction() )
		) {
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