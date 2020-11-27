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

use BlueSpice\Reminder\Factory;
use MediaWiki\MediaWikiServices;

/**
 * Main class for Reminder extension
 * @package BlueSpice_pro
 * @subpackage Reminder
 */
class Reminder extends BsExtensionMW {

	/**
	 *
	 * @return Factory
	 */
	private function getFactory() {
		return MediaWikiServices::getInstance()->getService( 'BSReminderFactory' );
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
	 * @return array
	 */
	public function getReminders( User $oUser, $iOffset = 0, $iLimit = 25, $sSortField = 'rem_date',
		$sSortDirection = 'ASC', $iDate = 0, \User $requestedUser = null ) {
		$aData = [
			'results' => [],
			'total' => 0
		];
		$pm = MediaWikiServices::getInstance()->getPermissionManager();
		if ( !$pm->userHasRight( $oUser, 'read' ) || $oUser->isAnon() ) {
			return $aData;
		}
		if ( empty( $this->getFactory()->getRegisteredTypes() ) ) {
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
			"{$sTblPrfx}bs_reminder.rem_repeat_date_end",
			"{$sTblPrfx}bs_reminder.rem_type"
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
		MediaWikiServices::getInstance()->getHookContainer()->run(
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

		$isAllowed = MediaWikiServices::getInstance()->getPermissionManager()->userHasRight(
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
					'rem_repeat_date_end' => $row->rem_repeat_date_end,
					'rem_type' => $row->rem_type,
				];
				MediaWikiServices::getInstance()->getHookContainer()->run(
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
