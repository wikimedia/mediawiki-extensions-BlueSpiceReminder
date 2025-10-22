<?php

namespace BlueSpice\Reminder\HookHandler\SkinTemplateNavigation;

use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\MediaWikiServices;
use SkinTemplate;

class AddReminderEntry implements SkinTemplateNavigation__UniversalHook {

	/**
	 * @param SkinTemplate $sktemplate
	 * @return bool
	 */
	protected function skipProcessing( SkinTemplate $sktemplate ) {
		$user = $sktemplate->getUser();
		if ( !$user->isRegistered() ) {
			return true;
		}
		$title = $sktemplate->getTitle();
		if ( !$title->exists() || $title->isSpecialPage() ) {
			return true;
		}
		if ( !MediaWikiServices::getInstance()->getPermissionManager()
			->userCan( 'read', $user, $title )
		) {
			return true;
		}
		return false;
	}

	/**
	 * // phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
	 * @inheritDoc
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		if ( $this->skipProcessing( $sktemplate ) ) {
			return;
		}

		$links['actions']['reminderCreate'] = [
			"class" => '',
			"text" => $sktemplate->msg( 'bs-reminder-actionmenuentry-setreminder-label' )->text(),
			"href" => "#",
			"bs-group" => "hidden",
			'position' => 30,
		];
	}
}
