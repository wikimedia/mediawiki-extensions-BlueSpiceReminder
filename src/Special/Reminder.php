<?php

namespace BlueSpice\Reminder\Special;

use MediaWiki\Html\Html;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\SpecialPage\SpecialPage;

class Reminder extends SpecialPage {

	/** @var PermissionManager */
	protected $permissionManager;

	/**
	 * @param PermissionManager $permissionManager
	 */
	public function __construct( PermissionManager $permissionManager ) {
		parent::__construct( 'Reminder', 'read' );
		$this->permissionManager = $permissionManager;
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		$out = $this->getOutput();
		$username = '';
		$page = '';

		$requestParams = $this->getRequest()->getQueryValues();
		if ( isset( $requestParams['user'] ) ) {
			$username = $requestParams['user'];
		}
		if ( isset( $requestParams['page'] ) ) {
			$page = $requestParams['page'];
		}

		if ( !$this->userCanEditAll() ) {
			$username = $this->getUser()->getName();
		}

		if ( $username === $this->getUser()->getName() ) {
			$out->setPageTitle( $this->msg( 'bs-reminder-special-myreminder-heading' )->plain() );
		}

		$this->getOutput()->addJsConfigVars(
			'BSReminderUsername',
			$username
		);

		$this->getOutput()->addJsConfigVars(
			'BSReminderPage',
			$page
		);

		$out->addModules( [ 'ext.bluespice.reminder.specialReminder' ] );
		$out->addHTML( Html::element( 'div', [ 'id' => 'bs-reminder-special-reminder-container' ] ) );
	}

	private function userCanEditAll(): bool {
		return $this->permissionManager->userHasRight(
			$this->getUser(),
			'remindereditall'
		);
	}
}
