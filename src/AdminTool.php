<?php

namespace BlueSpice\Reminder;

use BlueSpice\IAdminTool;
use Message;
use SpecialPage;
use SpecialPageFactory;

class AdminTool implements IAdminTool {

	/**
	 *
	 * @return string
	 */
	public function getURL() {
		$tool = SpecialPage::getTitleFor( 'Reminder' );
		return $tool->getLocalURL();
	}

	/**
	 *
	 * @return Message
	 */
	public function getDescription() {
		return Message::newFromKey( 'bs-reminder-extension-description' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getName() {
		return Message::newFromKey( 'specialreminder' );
	}

	/**
	 *
	 * @return array
	 */
	public function getClasses() {
		return [
			'bs-admin-link icon-flag'
		];
	}

	/**
	 *
	 * @return array
	 */
	public function getDataAttributes() {
		return [];
	}

	/**
	 *
	 * @return array
	 */
	public function getPermissions() {
		$specialReminder = SpecialPageFactory::getPage( 'Reminder' );
		return [
			$specialReminder->getRestriction()
		];
	}

}
