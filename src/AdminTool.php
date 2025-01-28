<?php

namespace BlueSpice\Reminder;

use BlueSpice\IAdminTool;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\SpecialPage\SpecialPage;

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
		$specialReminder = MediaWikiServices::getInstance()
			->getSpecialPageFactory()
			->getPage( 'Reminder' );
		if ( !$specialReminder ) {
			return [];
		}
		return [
			$specialReminder->getRestriction()
		];
	}

}
