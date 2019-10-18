<?php

class ApiReminderStore extends BSApiExtJSStoreBase {

	public function __construct($mainModule, $moduleName, $modulePrefix = '') {
		parent::__construct($mainModule, $moduleName, $modulePrefix);
	}

	protected function makeData( $sQuery = '' ) {
		$oUser = RequestContext::getMain()->getUser();
		if ( $oUser->isAnon() ) {
			return array();
		}

		$requestedUser = null;
		if ( !empty( $sQuery ) ) {
			$requestedUser = \User::newFromName( $sQuery );
			if ( !$requestedUser || $requestedUser->isAnon() ) {
				$requestedUser = null;
			}
		}

		// remindereditall permission is checked in getReminders
		$aReminders = BsExtensionManager::getExtension( 'BlueSpiceReminder' )->getReminders(
			$oUser,
			null,
			null,
			'rem_date',
			'DESC',
			0,
			$requestedUser
		);

		$aOutput = array();

		foreach ( $aReminders['results'] as $aReminder ) {
			$oReminder = (object) $aReminder;
			$aOutput[] = $oReminder;
		}

		return $aOutput;
	}

	public function isReadMode() {
		return true;
	}

	protected function makeMetaData( $sQuery = '' ) {
		$oUser = RequestContext::getMain()->getUser();
		if ( $oUser->isAnon() ) {
			return array();
		}

		$aMetadata = array(
			'idProperty' => 'id',
			'root' => 'results',
			'totalProperty' => 'total',
			'successProperty' => 'success',
			'fields' => array(
				array( 'name' => 'page_title' ),
				array( 'name' => 'page_link' ),
				array( 'name' => 'user_name' ),
				array ( 'name' => 'rem_date' ),
				array ( 'name' => 'article_id' ),
				array ( 'name' => 'rem_comment' )
			),
			'sortInfo' => array(
				'field' => 'rem_date',
				'direction' => 'DESC'
			)
		);

		if ( $oUser->isAllowed( "remindereditall" ) ) {
			$aMetadata['columns'][] = array (
				'header' => wfMessage( 'bs-reminder-header-username' )->plain(),
				'dataIndex' => 'user_name',
				'render' => 'raw',
				'sortable' => true
			);
		}
		$aMetadata['columns'][] = array (
			'header' => wfMessage( 'bs-reminder-header-pagename' )->plain(),
			'dataIndex' => 'page_title',
			'render' => 'page',
			'sortable' => true
		);
		$aMetadata['columns'][] = array (
			'header' => wfMessage( 'bs-reminder-header-date' )->plain(),
			'dataIndex' => 'rem_date',
			'render' => 'date',
			'sortable' => true
		);
		$aMetadata['columns'][] = array (
			'header' => wfMessage( 'bs-reminder-header-comment')->plain(),
			'dataIndex' => 'rem_comment',
			'render' => 'comment',
			'sortable' => false
		);

		\Hooks::run( 'BsReminderBuildOverviewMetadata', array( &$aMetadata ) );

		return $aMetadata;
	}
}
