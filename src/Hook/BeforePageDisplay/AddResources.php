<?php

namespace BlueSpice\Reminder\Hook\BeforePageDisplay;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$defaultPeriod = $this->out->getUser()->getOption( 'bs-reminder-period' );
		$this->out->addJsConfigVars(
			"DefaultReminderPeriod",
			strtotime( "+$defaultPeriod days" )
		);
		if ( $this->out->getUser()->isAllowed( "remindereditall" ) ) {
			$this->out->addJsConfigVars( "BSReminderShowUserColumn", true );
		}
		$this->out->addModuleStyles( 'ext.bluespice.reminder' );
		$this->out->addModules( 'ext.bluespice.reminder' );

		return true;
	}

}
