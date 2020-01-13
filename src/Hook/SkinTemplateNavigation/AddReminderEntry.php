<?php

namespace BlueSpice\Reminder\Hook\SkinTemplateNavigation;

use BlueSpice\Hook\SkinTemplateNavigation;

class AddReminderEntry extends SkinTemplateNavigation {
	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( !$this->sktemplate->getUser()->isLoggedIn() ) {
			return true;
		}
		if ( !$this->sktemplate->getTitle()->exists()
			|| $this->sktemplate->getTitle()->isSpecialPage() ) {
			return true;
		}
		if ( !$this->sktemplate->getTitle()->userCan( 'read' ) ) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$this->links['actions']['reminderCreate'] = [
			"class" => '',
			"text" => $this->msg( 'bs-reminder-menu_entry-create' )->plain(),
			"href" => "#",
			"bs-group" => "hidden"
		];
		return true;
	}

}
