<?php

namespace BlueSpice\Reminder\Hook\SkinTemplateOutputPageBeforeExec;

use BlueSpice\Hook\SkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;
use BlueSpice\Reminder\Panel\Flyout;

class AddFlyout extends SkinTemplateOutputPageBeforeExec {
	protected function skipProcessing() {
		$title = $this->skin->getTitle();
		if( $this->skin->getUser()->isAnon() ) {
			return true;
		}
		if( !$title instanceof \Title || !$title->exists() || $title->isSpecialPage() ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$this->mergeSkinDataArray(
			SkinData::PAGE_DOCUMENTS_PANEL,
			[
				'reminder' => [
					'position' => 30,
					'callback' => function( $sktemplate ) {
						return new Flyout( $sktemplate );
					}
				]
			]
		);

		$this->appendSkinDataArray( SkinData::EDIT_MENU_BLACKLIST, 'reminderCreate' );

		return true;
	}
}
