<?php

use BlueSpice\Reminder\Factory;
use MediaWiki\Context\RequestContext;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

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
				'header' => wfMessage( 'bs-reminder-header-username' )->text(),
				'dataIndex' => 'user_name',
				'render' => 'raw',
				'sortable' => true
			];
		}
		$aMetadata['columns'][] = [
			'header' => wfMessage( 'bs-reminder-header-pagename' )->text(),
			'dataIndex' => 'page_title',
			'render' => 'page',
			'sortable' => true
		];
		$aMetadata['columns'][] = [
			'header' => wfMessage( 'bs-reminder-header-date' )->text(),
			'dataIndex' => 'rem_date',
			'render' => 'date',
			'sortable' => true
		];
		$aMetadata['columns'][] = [
			'header' => wfMessage( 'bs-reminder-header-comment' )->text(),
			'dataIndex' => 'rem_comment',
			'render' => 'comment',
			'sortable' => false
		];
		$aMetadata['columns'][] = [
			'header' => wfMessage( 'bs-reminder-header-type' )->text(),
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
	 * @param User $user
	 * @param int $offset
	 * @param int $limit
	 * @param string $sortField
	 * @param string $sortDirection
	 * @param string $date
	 * @param User|null $requestedUser
	 * @return array
	 */
	protected function getReminders(
		User $user, $offset = 0, $limit = 25, $sortField = 'rem_date',
		$sortDirection = 'ASC', $date = 0, ?User $requestedUser = null
	) {
		$data = [
			'results' => [],
			'total' => 0
		];
		$pm = $this->services->getPermissionManager();
		if ( !$pm->userHasRight( $user, 'read' ) || $user->isAnon() ) {
			return $data;
		}
		if ( empty( $this->getFactory()->getRegisteredTypes() ) ) {
			return $data;
		}
		$dbr = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA );

		switch ( $sortField ) {
			case 'rem_date':
				$sortField = "bs_reminder.rem_date";
				break;
			case 'user_name':
				$sortField = "u.user_name";
				break;
			case 'page_title':
				$sortField = "page.page_title";
				break;
		}

		$tables = [
			'bs_reminder'
		];
		$fields = [
			"bs_reminder.rem_id",
			"bs_reminder.rem_page_id",
			"bs_reminder.rem_date",
			"u.user_name",
			"page.page_title",
			"bs_reminder.rem_comment",
			"bs_reminder.rem_is_repeating",
			"bs_reminder.rem_repeat_date_end",
			"bs_reminder.rem_type",
			"bs_reminder.rem_repeat_config"
		];
		$conditions = [
			"bs_reminder.rem_type" => $this->getFactory()->getRegisteredTypes()
		];
		$options = [
			'ORDER BY' => "{$sortField} {$sortDirection}",
			'GROUP BY' => "bs_reminder.rem_id",
			'SORT BY' => "bs_reminder.rem_date DESC"
		];

		if ( !empty( $offset ) ) {
			$options['OFFSET'] = $offset;
		}

		if ( !empty( $limit ) ) {
			$options['LIMIT'] = $limit;
		}

		$joinConditions = [
			'user' => [ 'u', 'bs_reminder.rem_user_id = u.user_id' ],
			'page' => [ 'page', 'bs_reminder.rem_page_id = page.page_id' ]
		];

		// give other extensions the opportunity to modify the query
		$this->services->getHookContainer()->run(
			'BsReminderBeforeBuildOverviewQuery',
			[
				$this,
				&$tables,
				&$fields,
				&$conditions,
				&$options,
				&$joinConditions,
				&$sortField,
				&$sortDirection
			]
		);

		$isAllowed = $this->services->getPermissionManager()->userHasRight(
			$user,
			'remindereditall'
		);
		if ( $isAllowed ) {
			if ( $requestedUser && !$requestedUser->isAnon() ) {
				$conditions["bs_reminder.rem_user_id"] = $requestedUser->getId();
			}
		} else {
			$conditions["bs_reminder.rem_user_id"] = $user->getId();
		}
		if ( $date !== 0 ) {
			$conditions[] = "bs_reminder.rem_date <= '" . $date . "'";
		}

		$query = $dbr->newSelectQueryBuilder()
			->tables( $tables )
			->fields( $fields )
			->where( $conditions )
			->caller( __METHOD__ )
			->options( $options );

		foreach ( $joinConditions as $table => $info ) {
			[ $alias, $cond ] = $info;
			$query->join( $table, $alias, $cond );
		}

		$res = $query->fetchResultSet();

		if ( $res ) {
			foreach ( $res as $row ) {
				$oTitle = Title::newFromID( $row->rem_page_id );
				$userpage = Title::makeTitle( NS_USER, $row->user_name );
				$user = $this->services->getUserFactory()->newFromName( $row->user_name );
				$username = !empty( $user->getRealName() ) ? $user->getRealName() : $user->getName();
				$aResultSet = [
					'id' => $row->rem_id,
					'user_name' => $user->getName(),
					'user_real_name' => $username,
					'user_page' => $userpage->getLocalURL(),
					'page_title' => $oTitle->getPrefixedText(),
					'page_link' => $oTitle->getLocalURL(),
					'reminder_date' => $row->rem_date,
					'article_id' => $row->rem_page_id,
					'rem_comment' => $row->rem_comment,
					'rem_is_repeating' => $row->rem_is_repeating === '1',
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
				$data['results'][] = $aResultSet;
			}
		}

		unset( $options['LIMIT'], $options['OFFSET'] );
		$res = $dbr->select(
			$tables,
			"COUNT(bs_reminder.rem_id) AS total",
			$conditions,
			__METHOD__,
			[],
			$joinConditions
		);
		if ( $res ) {
			$row = $res->fetchRow();
			$data['total'] = $row['total'];
		}

		return $data;
	}
}
