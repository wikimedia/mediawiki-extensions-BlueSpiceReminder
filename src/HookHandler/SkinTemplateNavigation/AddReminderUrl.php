<?php

namespace BlueSpice\Reminder\HookHandler\SkinTemplateNavigation;

use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\MediaWikiServices;
use RequestContext;

class AddReminderUrl implements SkinTemplateNavigation__UniversalHook {

	/**
	 * // phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
	 * @inheritDoc
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		$user = RequestContext::getMain()->getUser();
		if ( !$user->isRegistered() ) {
			return;
		}

		$reminder = MediaWikiServices::getInstance()->getSpecialPageFactory()
			->getPage( 'MyReminder' );
		if ( !$reminder ) {
			return;
		}

		$links['user-menu']['my_reminder'] = [
			'id' => 'pt-my_reminder',
			'href' => $reminder->getPageTitle()->getLocalURL(),
			'text' => $sktemplate->msg( 'bs-reminder-menu_entry-show' )->plain(),
			'position' => 50,
			'data' => [ 'attentionindicator' => 'reminder' ],
		];
	}
}
