<?php

namespace BlueSpice\Reminder\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

class AddToPersonal extends ChameleonSkinTemplateOutputPageBeforeExec {
	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( !$this->getServices()->getSpecialPageFactory()->exists( 'Reminder' ) ) {
			return true;
		}
		return !$this->skin->getUser()->isLoggedIn();
	}

	protected function doProcess() {
		$reminder = $this->getServices()->getSpecialPageFactory()->getPage(
			'Reminder'
		);
		if ( !$reminder ) {
			return true;
		}

		$this->template->data['personal_urls']['my_reminder'] = [
			'id' => 'pt-reminder',
			'text' => $this->msg( 'bs-reminder-menu_entry-show' )->plain(),
			'href' => $reminder->getPageTitle()->getLocalURL() . '/'
				. $this->skin->getUser()->getName(),
			'active' => true
		];
	}
}
