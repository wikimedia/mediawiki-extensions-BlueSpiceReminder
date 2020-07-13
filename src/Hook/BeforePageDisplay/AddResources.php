<?php

namespace BlueSpice\Reminder\Hook\BeforePageDisplay;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$defaultPeriod = $this->out->getUser()->getOption( 'bs-reminder-period' );
		$this->out->addJsConfigVars(
			"DefaultReminderPeriod",
			strtotime( "+$defaultPeriod days" )
		);
		$types = $this->getServices()->getService( 'BSReminderFactory' )->getRegisteredTypes();
		$remRegistry = [];
		foreach ( $types as $type ) {
			$rem = $this->getServices()->getService( 'BSReminderFactory' )->newFromType( $type );
			if ( !$rem ) {
				continue;
			}
			$remRegistry[$type] = (object)[
				'type' => $rem->getType(),
				'LabelMsgKey' => $rem->getLabelMsgKey(),
				'LabelMsg' => $this->msg( $rem->getLabelMsgKey() )->plain(),
			];
		}
		$this->out->addJsConfigVars(
			"bsgReminderRegisteredTypes",
			$remRegistry
		);
		if ( $this->out->getUser()->isAllowed( "remindereditall" ) ) {
			$this->out->addJsConfigVars( "BSReminderShowUserColumn", true );
		}
		$this->out->addModuleStyles( 'ext.bluespice.reminder' );
		$this->out->addModules( 'ext.bluespice.reminder' );

		return true;
	}

}
