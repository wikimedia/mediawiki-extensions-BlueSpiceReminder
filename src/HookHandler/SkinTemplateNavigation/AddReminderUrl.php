<?php

namespace BlueSpice\Reminder\HookHandler\SkinTemplateNavigation;

use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\MediaWikiServices;

class AddReminderUrl implements SkinTemplateNavigation__UniversalHook {

	/**
	 * // phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
	 * @inheritDoc
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		$user = $sktemplate->getUser();
		if ( !$user->isRegistered() ) {
			return;
		}

		$reminder = MediaWikiServices::getInstance()->getSpecialPageFactory()
			->getPage( 'Reminder' );
		if ( !$reminder ) {
			return;
		}

		$links['user-menu']['my_reminder'] = [
			'id' => 'pt-my_reminder',
			'href' => $reminder->getPageTitle()->getLocalURL() . '/'
			. $user->getName(),
			'text' => $sktemplate->msg( 'bs-reminder-menu_entry-show' )->plain(),
			'position' => 50,
			'data' => [ 'attentionindicator' => 'reminder' ],
		];
	}
}
