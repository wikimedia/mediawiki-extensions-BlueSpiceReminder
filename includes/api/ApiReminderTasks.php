<?php

use BlueSpice\Api\Response\Standard;
use BlueSpice\Reminder\Factory;

class ApiReminderTasks extends BSApiTasksBase {
	protected $aTasks = [ 'deleteReminder', 'saveReminder' ];

	/**
	 *
	 * @return Factory
	 */
	protected function getFactory() {
		return $this->getServices()->getService( 'BSReminderFactory' );
	}

	/**
	 *
	 * @param \stdClass $oTaskData
	 * @param array $aParams
	 * @return Standard
	 */
	public function task_deleteReminder( $oTaskData, $aParams ) {
		$oResult = $this->makeStandardReturn();
		$oUser = $this->getUser();
		if ( $oUser->isAnon() ) {
			$oResult->message = $oResult->errors[]
				= wfMessage( 'bs-permissionerror' )->plain();
			return $oResult;
		}

		$iReminderId = false;
		if ( isset( $oTaskData->reminderId ) ) {
			$iReminderId = (int)$oTaskData->reminderId;
		}

		if ( !$iReminderId ) {
			$oResult->message = $oResult->errors[] =
				wfMessage( 'bs-reminder-error-valid-reminder' )->text();
			return $oResult;
		}

		// check if user has right to delete reminders for other users
		$aConds = [ 'rem_id' => $iReminderId ];
		$isAllowed = $this->getServices()->getPermissionManager()->userHasRight(
			$oUser,
			'remindereditall'
		);
		if ( !$isAllowed ) {
			$aConds['rem_user_id'] = $oUser->getId();
		}

		$dbr = wfGetDB( DB_REPLICA );
		try {
			$res = $dbr->select(
				'bs_reminder',
				'rem_id, rem_user_id',
				$aConds,
				__METHOD__
			);
		} catch ( DBError $e ) {
			$res = false;
			$oResult->message = $oResult->errors[] =
				wfMessage( 'bs-reminder-delete-error-unkown' )->text();
			return $oResult;
		}

		if ( !$res || !$res->valid() || $res->numRows() < 1 ) {
			$oResult->message = $oResult->errors[]
				= wfMessage( 'bs-reminder-error-owner-reminder' )->plain();
			return $oResult;
		}

		$oResult->success = true;

		$this->getServices()->getHookContainer()->run( 'BsReminderDeleteReminder', [
			$iReminderId,
			&$oResult
		] );
		if ( !$oResult->success ) {
			return $oResult;
		}

		try {
			$dbw = wfGetDB( DB_PRIMARY );
			$dbw->delete(
				'bs_reminder',
				$aConds,
				__METHOD__
			);
		} catch ( DBError $e ) {
			// @codeCoverageIgnoreStart
			// This code block is only reached in the unlikely case that a db connection is
			// present in the check query above and not present here a few lines down.
			// This case cannot be reproduced in unit tests, but it is ok not to check.
			// Thus ignoring in code coverage
			$oResult->success = false;
			$oResult->message = $oResult->errors[] =
				wfMessage( 'bs-reminder-delete-error-unkown' )->text();
			wfDebugLog(
				'BS::Reminder',
				'SpecialReminder::ApiReminderManage::deleteReminder: ' . $dbw->lastQuery()
			);
			return $oResult;
			// @codeCoverageIgnoreEnd
		}
		$oResult->message = wfMessage( 'bs-reminder-delete-success' )->plain();
		$oResult->payload = [ 'id' => $iReminderId ];

		return $oResult;
	}

	/**
	 *
	 * @param \stdClass $oTaskData
	 * @param array $aParams
	 * @return Standard
	 */
	public function task_saveReminder( $oTaskData, $aParams ) {
		$oResult = $this->makeStandardReturn();
		$oUser = $this->getUser();
		$sComment = '';
		$bIsUpdate = false;
		if ( $oUser->isAnon() ) {
			$oResult->message = $oResult->errors[]
				= wfMessage( 'bs-permissionerror' )->plain();
			return $oResult;
		}
		$type = !empty( $oTaskData->type ) ? $oTaskData->type : '';
		if ( !$this->getFactory()->isRegisteredType( $type ) ) {
			$oResult->message = $oResult->errors[]
				= $this->msg( 'bs-reminder-invalid-type' )->plain();
			return $oResult;
		}

		$iReminderId = false;
		$dbr = wfGetDB( DB_REPLICA );
		// this is normally the case when clicking the reminder on a normal page
		// (not the overview specialpage) or the edit button on the specialpage
		// and data needs to be prefilled
		if ( !empty( $oTaskData->id ) ) {
			try {
				$res = $dbr->select(
					'bs_reminder',
					'rem_page_id',
					[
						'rem_id' => (int)$oTaskData->id
					],
					__METHOD__
				);
			} catch ( DBError $e ) {
				$res = false;
			}
			if ( !$res ) {
				$oResult->message = $oResult->errors[] =
					wfMessage( 'bs-reminder-create-no-actions' )->text();
				return $oResult;
			}
			$row = $res->fetchRow();
			if ( empty( $row['rem_page_id'] ) ) {
				$oResult->message = $oResult->errors[] =
					wfMessage( 'bs-reminder-create-no-actions' )->text();
				return $oResult;
			}
			$bIsUpdate = true;
			$iReminderId = (int)$oTaskData->id;
		}
		if ( property_exists( $oTaskData, 'articleId' ) ) {
			$title = Title::newFromID( $oTaskData->articleId ?? 0 );
		} else {
			$title = Title::newFromText( $oTaskData->page );
		}
		if ( !$title instanceof Title || !$title->exists() ) {
			$oResult->message = $oResult->errors[] =
				wfMessage( 'bs-reminder-unknown-page-msg' )->text();
			return $oResult;
		}

		$sFormattedFieldValue = $oTaskData->date;

		$iUserId = $oUser->getId();

		$sComment = strip_tags( empty( $oTaskData->comment ) ? '' : (string)$oTaskData->comment );

		if ( isset( $oTaskData->userName ) && $oTaskData->userName != '' ) {
			$iTargetUserId = User::newFromName( $oTaskData->userName )->getId();

			if ( !$iTargetUserId > 0 ) {
				$oResult->message = $oResult->errors[] =
					wfMessage( 'bs-reminder-unknown-user-msg' )->text();
				return $oResult;
			}

			$isAllowed = $this->getServices()->getPermissionManager()->userHasRight(
				$oUser,
				'remindereditall'
			);
			if ( !$isAllowed && $iUserId !== $iTargetUserId ) {
				$oResult->message = $oResult->errors[] =
					wfMessage( 'bs-reminder-other-user-not-allowed' )->text();
				return $oResult;
			}

			$iUserId = $iTargetUserId;

		}

		$aData = [
			'rem_user_id' => $iUserId,
			'rem_page_id' => $title->getArticleID(),
			'rem_date' => $sFormattedFieldValue,
			'rem_comment' => addslashes( $sComment ),
			'rem_type' => $type,
		];

		if ( isset( $oTaskData->isRepeating ) && $oTaskData->isRepeating === true
			&& !empty( $oTaskData->repeatConfig ) ) {
			$endDate = new DateTime( $oTaskData->repeatConfig->repeatDateEnd );
			$aData['rem_repeat_date_end'] = $endDate->format( 'YmdHis' );

			$startReminderDate = DateTime::createFromFormat( 'Y-m-d', $aData['rem_date'] );
			$startReminderDate = $this->getServices()
				->getService( 'BSRepeatingReminderDateCalculator' )
				->getStartDate( $startReminderDate, $oTaskData->repeatConfig );
			$startReminderDate = $startReminderDate->format( 'YmdHis' );

			if ( $startReminderDate > $aData['rem_repeat_date_end'] ) {
				$oResult->message = $oResult->errors[] =
					$this->msg( 'bs-reminder-start-date-greater-end-date' )->text();
				return $oResult;
			}
			$aData['rem_date'] = $startReminderDate;
			$aData['rem_is_repeating'] = true;
			$aData['rem_repeat_config'] = FormatJson::encode( $oTaskData->repeatConfig );
		}

		$dbw = wfGetDB( DB_PRIMARY );
		if ( !$iReminderId ) {
			try {
				$res = $dbw->insert( 'bs_reminder', $aData, __METHOD__ );
			} catch ( DBError $e ) {
				$res = false;
			}
			if ( !$res ) {
				$oResult->message = $oResult->errors[] =
					wfMessage( 'bs-reminder-create-error' )->text();
				return $oResult;
			}

			$iReminderId = $dbw->insertId();

			try {
				$this->getServices()->getHookContainer()->run( 'BsReminderOnSave', [
					$oTaskData,
					$iReminderId,
					$oTaskData->articleId, $iUserId
				] );
			} catch ( Exception $e ) {
				$oResult->message = $oResult->errors[] =
					$e->getMessage();
				return $oResult;
			}
		} else {
			try {
				$res = $dbw->update(
					'bs_reminder',
					$aData,
					[ 'rem_id' => $iReminderId ],
					__METHOD__
				);
			} catch ( DBError $e ) {
				$res = false;
			}
			if ( !$res ) {
				$oResult->message = $oResult->errors[] =
					wfMessage( 'bs-reminder-update-error' )->text();
				return $oResult;
			}

			try {
				$this->getServices()->getHookContainer()->run( 'BsReminderOnUpdate', [
					$oTaskData,
					$iReminderId
				] );
			} catch ( Exception $e ) {
				$oResult->message = $oResult->errors[] =
					$e->getMessage();
				return $oResult;
			}
		}

		$oResult->success = true;
		$oResult->payload = [ 'id' => $iReminderId ];
		if ( $bIsUpdate ) {
			$oResult->message = wfMessage( "bs-reminder-update-success" )->plain();
		} else {
			$oResult->message = wfMessage( "bs-reminder-save-success" )->plain();
		}

		return $oResult;
	}

	/**
	 *
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'deleteReminder' => [ 'read' ],
			'saveReminder' => [ 'read' ]
		];
	}
}
