<?php

use BlueSpice\Reminder\Factory;
use MediaWiki\MediaWikiServices;

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
	 * @return Factory
	 */
	protected function getFactory() {
		return $this->getServices()->getService( 'BSReminderFactory' );
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
		$em = MediaWikiServices::getInstance()->getService( 'BSExtensionFactory' );
		$aReminders = $em->getExtension( 'BlueSpiceReminder' )->getReminders(
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
			$aReminder['rem_repeat_date_end'] = RequestContext::getMain()->getLanguage()
				->userTimeAndDate( $aReminder['rem_repeat_date_end'], $oUser );
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
				[ 'name' => 'rem_comment' ],
				[ 'name' => 'rem_is_repeating' ],
				[ 'name' => 'rem_repeat_date_end' ],
				[ 'name' => 'rem_type' ],
				[ 'name' => 'type_display' ],
			],
			'sortInfo' => [
				'field' => 'rem_date',
				'direction' => 'DESC'
			]
		];

		$isAllowed = $this->getServices()->getPermissionManager()->userHasRight(
			$oUser,
			'remindereditall'
		);
		if ( $isAllowed ) {
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
		$aMetadata['columns'][] = [
			'header' => wfMessage( 'bs-reminder-header-type' )->plain(),
			'dataIndex' => 'rem_type',
			'render' => 'type',
			'sortable' => false
		];

		$this->getServices()->getHookContainer()->run( 'BsReminderBuildOverviewMetadata', [
			&$aMetadata
		] );

		return $aMetadata;
	}

	/**
	 * Performs list filtering based on given filter of type array on a dataset
	 * @param \stdClass $oFilter
	 * @param \stdClass $aDataSet
	 * @return bool true if filter applies, false if not
	 */
	public function filterList( $oFilter, $aDataSet ) {
		if ( $oFilter->field !== 'rem_type' ) {
			return parent::filterList( $oFilter, $aDataSet );
		}
		if ( !is_array( $oFilter->value ) ) {
			 $oFilter->value = [ $oFilter->value ];
		}
		if ( in_array( 'page', $oFilter->value ) ) {
			$oFilter->value[] = '';
		}
		$aFieldValues = $aDataSet->{$oFilter->field};
		$aFilterValues = $oFilter->value;
		return in_array( $aFieldValues, $aFilterValues );
	}
}
