<?php

namespace BlueSpice\Reminder\Hook\SkinTemplateNavigationUniversal;

use BlueSpice\Hook\SkinTemplateNavigationUniversal;

class AddReminderEntry extends SkinTemplateNavigationUniversal {
	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		$user = $this->sktemplate->getUser();
		if ( !$user->isRegistered() ) {
			return true;
		}
		$title = $this->sktemplate->getTitle();
		if ( !$title->exists() || $title->isSpecialPage() ) {
			return true;
		}
		if ( !$this->getServices()
			->getPermissionManager()
			->userCan( 'read', $user, $title )
		) {
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
