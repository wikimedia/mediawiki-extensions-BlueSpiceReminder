<?php

class ApiReminderStore extends BSApiExtJSStoreBase {

	/**
	 *
	 * @param string $mainModule
	 * @param string $moduleName
	 * @param string $modulePrefix
	 */
	public function __construct( $mainModule, $moduleName, $modulePrefix = '' ) {
		parent::__construct( $mainModule, $moduleName, $modulePrefix );
	}

	/**
	 *
	 * @param string $sQuery
	 * @return array
	 */
	protected function makeData( $sQuery = '' ) {
		$oUser = RequestContext::getMain()->getUser();
		if ( $oUser->isAnon() ) {
			return [];
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

		$aOutput = [];

		foreach ( $aReminders['results'] as $aReminder ) {
			$oReminder = (object)$aReminder;
			$aOutput[] = $oReminder;
		}

		return $aOutput;
	}

	/**
	 *
	 * @return bool
	 */
	public function isReadMode() {
		return true;
	}

	/**
	 *
	 * @param string $sQuery
	 * @return array
	 */
	protected function makeMetaData( $sQuery = '' ) {
		$oUser = RequestContext::getMain()->getUser();
		if ( $oUser->isAnon() ) {
			return [];
		}

		$aMetadata = [
			'idProperty' => 'id',
			'root' => 'results',
			'totalProperty' => 'total',
			'successProperty' => 'success',
			'fields' => [
				[ 'name' => 'page_title' ],
				[ 'name' => 'page_link' ],
				[ 'name' => 'user_name' ],
				[ 'name' => 'rem_date' ],
				[ 'name' => 'article_id' ],
				[ 'name' => 'rem_comment' ]
			],
			'sortInfo' => [
				'field' => 'rem_date',
				'direction' => 'DESC'
			]
		];

		if ( $oUser->isAllowed( "remindereditall" ) ) {
			$aMetadata['columns'][] = [
				'header' => wfMessage( 'bs-reminder-header-username' )->plain(),
				'dataIndex' => 'user_name',
				'render' => 'raw',
				'sortable' => true
			];
		}
		$aMetadata['columns'][] = [
			'header' => wfMessage( 'bs-reminder-header-pagename' )->plain(),
			'dataIndex' => 'page_title',
			'render' => 'page',
			'sortable' => true
		];
		$aMetadata['columns'][] = [
			'header' => wfMessage( 'bs-reminder-header-date' )->plain(),
			'dataIndex' => 'rem_date',
			'render' => 'date',
			'sortable' => true
		];
		$aMetadata['columns'][] = [
			'header' => wfMessage( 'bs-reminder-header-comment' )->plain(),
			'dataIndex' => 'rem_comment',
			'render' => 'comment',
			'sortable' => false
		];

		\Hooks::run( 'BsReminderBuildOverviewMetadata', [ &$aMetadata ] );

		return $aMetadata;
	}
}
