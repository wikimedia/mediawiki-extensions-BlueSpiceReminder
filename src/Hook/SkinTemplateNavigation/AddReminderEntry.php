<?php

namespace BlueSpice\Reminder\Hook\SkinTemplateNavigation;

use BlueSpice\Hook\SkinTemplateNavigation;

class AddReminderEntry extends SkinTemplateNavigation {
	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		$user = $this->sktemplate->getUser();
		if ( !$user->isLoggedIn() ) {
			return true;
		}
		$title = $this->sktemplate->getTitle();
		if ( !$title->exists() || $title->isSpecialPage() ) {
			return true;
		}
		if ( !\MediaWiki\MediaWikiServices::getInstance()
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
