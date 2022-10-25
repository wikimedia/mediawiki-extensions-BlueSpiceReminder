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
			->getPage( 'Reminder' );
		$links['my_reminder'] = [
			'href' => $reminder->getPageTitle()->getLocalURL() . '/'
			. $user->getName(),
			'text' => $sktemplate->msg( 'bs-reminder-menu_entry-show' )->plain(),
			'position' => 50,
			'data' => [ 'attentionindicator' => 'reminder' ],
		];
	}
}
