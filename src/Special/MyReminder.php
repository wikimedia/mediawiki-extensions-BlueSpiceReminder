<?php

namespace BlueSpice\Reminder\Special;

use Html;
use SpecialPage;

class MyReminder extends SpecialPage {

	public function __construct() {
		parent::__construct( 'MyReminder', 'read' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		$out = $this->getOutput();
		$out->setPageTitle( $this->msg( 'bs-reminder-special-myreminder-heading' )->plain() );
		$out->addModules( [ 'ext.bluespice.reminder.specialMyReminder' ] );
		$out->addHTML( Html::element( 'div', [ 'id' => 'bs-reminder-special-myreminder-container' ] ) );
	}
}
