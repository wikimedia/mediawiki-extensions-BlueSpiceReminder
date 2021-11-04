<?php

namespace BlueSpice\Reminder\Hook\PersonalUrls;

use BlueSpice\Hook\PersonalUrls;

class AddReminderUrl extends PersonalUrls {

	protected function skipProcessing() {
		$user = $this->getContext()->getUser();
		return !$user->isRegistered();
	}

	protected function doProcess() {
		$user = $this->getContext()->getUser();
		$reminder = $this->getServices()->getSpecialPageFactory()->getPage(
			'Reminder'
		);
		$this->personal_urls['my_reminder'] = [
			'href' => $reminder->getPageTitle()->getLocalURL() . '/'
			. $user->getName(),
			'text' => $this->msg( 'bs-reminder-menu_entry-show' )->plain(),
			'position' => 80,
		];

		return true;
	}

}
