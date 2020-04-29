<?php

/**
 * Reminder Extension for BlueSpice
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit https://bluespice.com
 *
 *
 * @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-network.hk>
 * @author     Tobias Weichart <weichart@hallowelt.com>
 * @version    2.23.1
 * @package    BlueSpice_pro
 * @subpackage Reminder
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

/**
 * Main class for Reminder extension
 * @package BlueSpice_pro
 * @subpackage Reminder
 */
class Reminder extends BsExtensionMW {

	/**
	 * Init Method of Reminder class
	 */
	protected function initExt() {
		// register extension hooks
		$this->setHook( 'ArticleInsertComplete' );
		$this->setHook( 'SkinTemplateOutputPageBeforeExec' );
		$this->setHook( 'BSUserManagerAfterDeleteUser' );
		$this->setHook( 'EchoGetDefaultNotifiedUsers' );
	}

	/**
	 * Creates Reminder link in personal urls and icon in personal info
	 * @param SkinTemplate &$sktemplate
	 * @param BaseTemplate &$tpl
	 * @return bool Always true to keep hook running
	 */
	public function onSkinTemplateOutputPageBeforeExec( &$sktemplate, &$tpl ) {
		$oSpecialPageReminder = SpecialPage::getTitleFor( 'Reminder' );
		$oUser = RequestContext::getMain()->getUser();
		$oTitle = RequestContext::getMain()->getTitle();
		if ( $oTitle === null || !$oUser->isLoggedIn() ) {
			return true;
		}

		$tpl->data['personal_urls']['my_reminder'] = [
			'id' => 'pt-reminder',
			'text' => wfMessage( 'bs-reminder-menu_entry-show' )->plain(),
			'href' => $oSpecialPageReminder->getLocalURL() . '/' . $oUser->getName(),
			'active' => true
		];

		$iTotal = 0;
		$sFormattedFieldValue = date( "Y-m-d", time() );
		$aData = $this->getReminders( $oUser, 0, 0, 'rem_date', 'DESC', $sFormattedFieldValue );
		if ( !empty( $aData['total'] ) ) {
			$iTotal = (int)$aData['total'];
		}

		if ( $iTotal > 0 ) {
			$tpl->data['bs_personal_info'][15] = [
				'id' => 'pi-reminder',
				'text' => $iTotal,
				'href' => $oSpecialPageReminder->getLocalURL() . '/' . $oUser->getName(),
				'class' => 'icon-flag',
				'active' => true
			];
		}

		return true;
	}

	/**
	 * Hook after Article is saved, sets up the reminder if user chose so
	 * @param WikiPage $oWikiPage
	 * @param User $oUser
	 * @param string $sText
	 * @param string $sSummary
	 * @param bool $bMinorEdit
	 * @param bool $bWatchThis
	 * @param null $nSectionanchor
	 * @param int &$iFlags
	 * @param Revision $oRevision
	 * @return bool
	 */
	public function onArticleInsertComplete( $oWikiPage, $oUser, $sText, $sSummary,
		$bMinorEdit, $bWatchThis, $nSectionanchor, &$iFlags, $oRevision ) {
		if ( $this->getUser()->getOption( 'bs-reminder-oncreate' ) ) {
			$iPageNS = $oWikiPage->getTitle()->getNamespace();
			$aDisabledNamespaces = explode(
				'|',
				$this->getUser()->getOption( 'bs-reminder-forns' )
			);
			if ( in_array( $iPageNS, $aDisabledNamespaces ) ) {
				return true;
			}

			$iPageId = $oWikiPage->getId();
			$iUserId = $oUser->getId();

			$sDefaultPeriod = $this->getUser()->getOption( 'bs-reminder-period' );
			$iDate = strtotime( "+$sDefaultPeriod days" );
			$sFormattedFieldValue = date( 'Y-m-d', $iDate );

			$dbw = wfGetDB( DB_MASTER );
			$res = $dbw->insert(
				'bs_reminder',
				[
					'rem_user_id' => $iUserId,
					'rem_page_id' => $iPageId,
					'rem_date' => $sFormattedFieldValue
			] );

			if ( !$res ) {
				return wfMessage( 'bs-reminder-create-error' )->plain();
			}

			$iReminderId = $dbw->insertId();

			try {
				Hooks::run( 'BsReminderOnSave', [ [], $iReminderId, $iPageId, $iUserId ] );
			} catch ( Exception $e ) {
				return true;
			}
		}

		return true;
	}

	/**
	 *
	 * @param User $oUser
	 * @param int $iOffset
	 * @param int $iLimit
	 * @param string $sSortField
	 * @param string $sSortDirection
	 * @param string $iDate
	 * @param \User|null $requestedUser
	 * @return int
	 */
	public function getReminders( User $oUser, $iOffset = 0, $iLimit = 25, $sSortField = 'rem_date',
		$sSortDirection = 'ASC', $iDate = 0, \User $requestedUser = null ) {
		$aData = [
			'results' => [],
			'total' => 0
		];
		if ( BsCore::checkAccessAdmission( 'read' ) === false || $oUser->isAnon() ) {
			return $aData;
		}
		$dbr = wfGetDB( DB_REPLICA );
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
			"{$sTblPrfx}bs_reminder.rem_repeat_date_end"
		];
		$aConditions = [];
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
		Hooks::run(
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

		if ( $oUser->isAllowed( 'remindereditall' ) ) {
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

		$baseurl = \SpecialPage::getTitleFor( 'Reminder' )->getLocalURL() . '/';

		if ( $res ) {
			foreach ( $res as $row ) {
				$oTitle = Title::newFromID( $row->rem_page_id );
				$aResultSet = [
					'id' => $row->rem_id,
					'user_name' => $row->user_name,
					'user_page' => $row->user_name,
					'page_title' => $oTitle->getPrefixedText(),
					'page_link' => $oTitle->getLocalURL(),
					'reminder_date' => $row->rem_date,
					'article_id' => $row->rem_page_id,
					'rem_comment' => $row->rem_comment,
					'rem_is_repeating' => $row->rem_is_repeating,
					'rem_repeat_date_end' => $row->rem_repeat_date_end
				];
				Hooks::run( 'BsReminderBuildOverviewResultSet', [ $this, &$aResultSet, $row ] );
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

	/**
	 * Delete all personal reminders when a user is deleted
	 * @param UserManager $oSender
	 * @param User $oUser User that was deleted
	 * @param \Status &$aAnswer
	 * @return bool Always true to keep Hook running
	 */
	public function onBSUserManagerAfterDeleteUser( $oSender, $oUser, &$aAnswer ) {
		$dbw = wfGetDB( DB_MASTER );
		$res = $dbw->delete( 'bs_reminder',
			[ 'rem_user_id' => $oUser->getId() ]
		);
		return true;
	}

	/**
	 * Handler for EchoGetDefaultNotifiedUsers hook.
	 * @param EchoEvent $event EchoEvent to get implicitly subscribed users for
	 * @param array &$users Array to append implicitly subscribed users to.
	 * @return bool true in all cases
	 */
	public function onEchoGetDefaultNotifiedUsers( $event, &$users ) {
		switch ( $event->getType() ) {
			case 'notification-bs-reminder-today':
			case 'notification-bs-reminder-one-week':
				$extra = $event->getExtra();
				if ( !$extra || !isset( $extra['recipient-id'] ) ) {
					break;
				}
				$recipientId = $extra['recipient-id'];
				$users[$recipientId] = User::newFromId( $recipientId );
				break;
		}
		return true;
	}
}
