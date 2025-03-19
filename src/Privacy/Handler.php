<?php

namespace BlueSpice\Reminder\Privacy;

use BlueSpice\Privacy\IPrivacyHandler;
use BlueSpice\Privacy\Module\Transparency;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use Wikimedia\Rdbms\IDatabase;

class Handler implements IPrivacyHandler {
	/** @var IDatabase */
	protected $db;

	/**
	 *
	 * @param IDatabase $db
	 */
	public function __construct( IDatabase $db ) {
		$this->db = $db;
	}

	/**
	 *
	 * @param string $oldUsername
	 * @param string $newUsername
	 * @return Status
	 */
	public function anonymize( $oldUsername, $newUsername ) {
		return Status::newGood();
	}

	/**
	 *
	 * @param User $userToDelete
	 * @param User $deletedUser
	 * @return Status
	 */
	public function delete( User $userToDelete, User $deletedUser ) {
		$this->db->delete(
			'bs_reminder',
			[ 'rem_user_id' => $userToDelete->getId() ],
			__METHOD__
		);

		return Status::newGood();
	}

	/**
	 *
	 * @param array $types
	 * @param string $format
	 * @param User $user
	 * @return Status
	 */
	public function exportData( array $types, $format, User $user ) {
		if ( !in_array( Transparency::DATA_TYPE_WORKING, $types ) ) {
			return Status::newGood( [] );
		}

		$res = $this->db->select(
			'bs_reminder',
			'*',
			[ 'rem_user_id' => $user->getId() ],
			__METHOD__
		);

		$data = [];
		foreach ( $res as $row ) {
			$title = Title::newFromID( $row->rem_page_id );
			if ( !$title ) {
				continue;
			}

			$data[] = wfMessage(
				'bs-reminder-privacy-transparency-working-reminder',
				$title->getPrefixedText(),
				$row->rem_date,
				$row->rem_comment ?: '-'
			)->plain();
		}

		return Status::newGood( [
			Transparency::DATA_TYPE_WORKING => $data
		] );
	}
}
