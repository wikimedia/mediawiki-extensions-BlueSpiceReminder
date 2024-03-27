<?php

use BlueSpice\Reminder\Factory;

class ApiReminderStore extends BSApiExtJSStoreBase {

	/**
	 *
	 * @return Factory
	 */
	protected function getFactory() {
		return $this->services->getService( 'BSReminderFactory' );
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
			$requestedUser = $this->services->getUserFactory()->newFromName( $sQuery );
			if ( !$requestedUser || $requestedUser->isAnon() ) {
				$requestedUser = null;
			}
		}

		// remindereditall permission is checked in getReminders
		$aReminders = $this->getReminders(
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
				[ 'name' => 'rem_repeat_date_end_raw' ],
				[ 'name' => 'rem_repeat_config' ],
				[ 'name' => 'rem_type' ],
				[ 'name' => 'type_display' ],
			],
			'sortInfo' => [
				'field' => 'rem_date',
				'direction' => 'DESC'
			]
		];

		$isAllowed = $this->services->getPermissionManager()->userHasRight(
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

		$this->services->getHookContainer()->run( 'BsReminderBuildOverviewMetadata', [
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

	/**
	 *
	 * @param User $oUser
	 * @param int $iOffset
	 * @param int $iLimit
	 * @param string $sSortField
	 * @param string $sSortDirection
	 * @param string $iDate
	 * @param User|null $requestedUser
	 * @return array
	 */
	protected function getReminders( User $oUser, $iOffset = 0, $iLimit = 25, $sSortField = 'rem_date',
		$sSortDirection = 'ASC', $iDate = 0, User $requestedUser = null ) {
		$aData = [
			'results' => [],
			'total' => 0
		];
		$pm = $this->services->getPermissionManager();
		if ( !$pm->userHasRight( $oUser, 'read' ) || $oUser->isAnon() ) {
			return $aData;
		}
		if ( empty( $this->getFactory()->getRegisteredTypes() ) ) {
			return $aData;
		}
		$dbr = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$sTblPrfx = $dbr->tablePrefix();

		switch ( $sSortField ) {
			case 'rem_date':
				$sSortField = "{$sTblPrfx}bs_reminder.rem_date";
				break;
			case 'user_name':
				$sSortField = "{$sTblPrfx}user.user_name";
				break;
			case 'page_title':
				$sSortField = "{$sTblPrfx}page.page_title";
				break;
		}

		$aTables = [
			'bs_reminder', 'user', 'page'
		];
		$aFields = [
			"{$sTblPrfx}bs_reminder.rem_id",
			"{$sTblPrfx}bs_reminder.rem_page_id",
			"{$sTblPrfx}bs_reminder.rem_date",
			"{$sTblPrfx}user.user_name",
			"{$sTblPrfx}page.page_title",
			"{$sTblPrfx}bs_reminder.rem_comment",
			"{$sTblPrfx}bs_reminder.rem_is_repeating",
			"{$sTblPrfx}bs_reminder.rem_repeat_date_end",
			"{$sTblPrfx}bs_reminder.rem_type",
			"{$sTblPrfx}bs_reminder.rem_repeat_config"
		];
		$aConditions = [
			"{$sTblPrfx}bs_reminder.rem_type" => $this->getFactory()->getRegisteredTypes()
		];
		$aOptions = [
			'ORDER BY' => "{$sSortField} {$sSortDirection}",
			'GROUP BY' => "{$sTblPrfx}bs_reminder.rem_id",
			'SORT BY' => "{$sTblPrfx}bs_reminder.rem_date DESC"
		];

		if ( !empty( $iOffset ) ) {
			$aOptions['OFFSET'] = $iOffset;
		}

		if ( !empty( $iLimit ) ) {
			$aOptions['LIMIT'] = $iLimit;
		}

		$aJoinConditions = [
			"user" => [ 'JOIN', "{$sTblPrfx}bs_reminder.rem_user_id = {$sTblPrfx}user.user_id" ],
			"page" => [ 'JOIN', "{$sTblPrfx}bs_reminder.rem_page_id = {$sTblPrfx}page.page_id" ]
		];

		// give other extensions the opportunity to modify the query
		$this->services->getHookContainer()->run(
			'BsReminderBeforeBuildOverviewQuery',
			[
				$this,
				&$aTables,
				&$aFields,
				&$aConditions,
				&$aOptions,
				&$aJoinConditions,
				&$sSortField,
				&$sSortDirection
			]
		);

		$isAllowed = $this->services->getPermissionManager()->userHasRight(
			$oUser,
			'remindereditall'
		);
		if ( $isAllowed ) {
			if ( $requestedUser && !$requestedUser->isAnon() ) {
				$aConditions["{$sTblPrfx}bs_reminder.rem_user_id"] = $requestedUser->getId();
			}
		} else {
			$aConditions["{$sTblPrfx}bs_reminder.rem_user_id"] = $oUser->getId();
		}
		if ( $iDate !== 0 ) {
			$aConditions[] = "{$sTblPrfx}bs_reminder.rem_date <= '" . $iDate . "'";
		}

		$res = $dbr->select(
			$aTables, $aFields, $aConditions, __METHOD__, $aOptions, $aJoinConditions
		);

		$baseurl = SpecialPage::getTitleFor( 'Reminder' )->getLocalURL() . '/';

		if ( $res ) {
			foreach ( $res as $row ) {
				$oTitle = Title::newFromID( $row->rem_page_id );
				$userpage = Title::makeTitle( NS_USER, $row->user_name );
				$aResultSet = [
					'id' => $row->rem_id,
					'user_name' => $row->user_name,
					'user_page' => $userpage->getLocalURL(),
					'page_title' => $oTitle->getPrefixedText(),
					'page_link' => $oTitle->getLocalURL(),
					'reminder_date' => $row->rem_date,
					'article_id' => $row->rem_page_id,
					'rem_comment' => $row->rem_comment,
					'rem_is_repeating' => $row->rem_is_repeating,
					'rem_repeat_date_end' => $row->rem_repeat_date_end,
					'rem_type' => $row->rem_type,
					'rem_repeat_config' => $row->rem_repeat_config
				];
				$this->services->getHookContainer()->run(
					'BsReminderBuildOverviewResultSet',
					[
						$this,
						&$aResultSet,
						$row
					]
				);
				$aData['results'][] = $aResultSet;
			}
		}

		unset( $aOptions['LIMIT'], $aOptions['OFFSET'] );
		$res = $dbr->select(
			$aTables,
			"COUNT({$sTblPrfx}bs_reminder.rem_id) AS total",
			$aConditions,
			__METHOD__,
			[],
			$aJoinConditions
		);
		if ( $res ) {
			$row = $res->fetchRow();
			$aData['total'] = $row['total'];
		}

		return $aData;
	}
}
