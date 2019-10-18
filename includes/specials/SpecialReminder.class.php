<?php

/**
 * Renders the Reminder special page.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-network.hk>
 * @version    $Id: SpecialReview.class.php 9608 2013-06-05 10:39:04Z sulbricht $
 * @package    BlueSpice_Extensions
 * @subpackage Reminder
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

use BlueSpice\Special\ManagerBase;

/**
 * Reminder special page that renders the Reminder dialogues and lists
 * @package BlueSpice_Extensions
 * @subpackage Reminder
 */
class SpecialReminder extends ManagerBase {

	/**
	 * Constructor of SpecialReview class
	 */
	public function __construct() {
		parent::__construct( 'Reminder', 'read' );
	}

	/**
	 * Renders special page output.
	 * @param string $param Name of the article, who's review should be edited, or user whos review should be displayed.
	 * @return bool Allow other hooked methods to be executed. always true.
	 */
	public function execute( $param ) {
		parent::execute( $param );

		$this->showRemindersOverview( $param );
		return true;
	}

	protected function showRemindersOverview( $username = '' ) {
		$this->getOutput()->addJsConfigVars(
			'BSReminderUsername',
			empty( $username ) ? false : $username
		);

	}

	/**
	 * @return string ID of the HTML element being added
	 */
	protected function getId() {
		return 'bs-reminder-overview-grid';
	}

	/**
	 * @return array
	 */
	protected function getModules() {
		return [
			'ext.bluespice.reminder.special'
		];
	}
}
