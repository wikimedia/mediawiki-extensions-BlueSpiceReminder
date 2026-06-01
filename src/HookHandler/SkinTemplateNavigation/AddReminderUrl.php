<?php

namespace BlueSpice\Reminder\HookHandler\SkinTemplateNavigation;

use MediaWiki\Context\RequestContext;
use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\SpecialPage\SpecialPageFactory;

class AddReminderUrl implements SkinTemplateNavigation__UniversalHook {

	public function __construct(
		private readonly SpecialPageFactory $specialPageFactory,
	) {
	}

	/**
	 * // phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
	 * @inheritDoc
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		$user = RequestContext::getMain()->getUser();
		if ( !$user->isRegistered() ) {
			return;
		}

		$reminder = $this->specialPageFactory->getPage( 'Reminder' );
		if ( !$reminder ) {
			return;
		}

		$links['user-menu']['my_reminder'] = [
			'id' => 'pt-my_reminder',
			'href' => $reminder->getPageTitle()->getLocalURL( "user={$user->getName()}" ),
			'text' => $sktemplate->msg( 'bs-reminder-menu_entry-show' )->text(),
			'position' => 50,
			'data' => [ 'attentionindicator' => 'reminder' ],
		];
	}
}
