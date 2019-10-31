<?php

namespace BlueSpice\Reminder\Panel;

use BlueSpice\Calumma\Panel\BasePanel;
use BlueSpice\Calumma\IFlyout;

class Flyout extends BasePanel implements IFlyout {

	/**
	 * @return \Message
	 */
	public function getFlyoutTitleMessage() {
		return wfMessage( 'bs-reminder-nav-link-title-reminder' );
	}

	/**
	 * @return \Message
	 */
	public function getFlyoutIntroMessage() {
		return wfMessage( 'bs-reminder-flyout-intro' );
	}

	/**
	 * @return \Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-reminder-flyout-title' );
	}

	/**
	 * @return string
	 */
	public function getBody() {
		return '';
	}

	/**
	 *
	 * @return string
	 */
	public function getTriggerCallbackFunctionName() {
		return 'bs.reminder.flyoutCallback';
	}

	/**
	 *
	 * @return array
	 */
	public function getTriggerRLDependencies() {
		return [ 'ext.bluespice.reminder.flyout' ];
	}
}
