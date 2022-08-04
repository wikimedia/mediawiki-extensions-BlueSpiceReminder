<?php

namespace BlueSpice\Reminder\Hook\LoadExtensionSchemaUpdates;

class AddReminderTable extends \BlueSpice\Hook\LoadExtensionSchemaUpdates {

	protected function doProcess() {
		$dbType = $this->updater->getDB()->getType();
		$dir = $this->getExtensionPath();

		$this->updater->addExtensionTable(
			'bs_reminder',
			"$dir/maintenance/db/sql/$dbType/bs_reminder-generated.sql"
		);

		if ( $dbType == 'mysql' ) {
			// BS 2.23.3: Rename remind_date => reminder_date
			$this->updater->modifyExtensionField(
				'bs_reminder',
				'remind_date',
				"$dir/maintenance/db/bs_reminder.patch.modify_remind_date.sql"
			);

			// BS 2.23.3: prefix columns in reminder
			$this->updater->modifyExtensionField(
				'bs_reminder',
				'id',
				"$dir/maintenance/db/bs_reminder.patch.add_col_prefix.sql"
			);

			// BS 2.23.3: add comment field
			$this->updater->addExtensionField(
				'bs_reminder',
				'rem_comment',
				"$dir/maintenance/db/bs_reminder.patch.add_comment_col.sql"
			);

			$this->updater->addExtensionField(
				'bs_reminder',
				'rem_is_repeating',
				"$dir/maintenance/db/bs_reminder.patch.add_is_repeating_col.sql"
			);

			$this->updater->addExtensionField(
				'bs_reminder',
				'rem_repeat_date_end',
				"$dir/maintenance/db/bs_reminder.patch.add_repeat_date_end_col.sql"
			);

			$this->updater->addExtensionField(
				'bs_reminder',
				'rem_repeat_config',
				"$dir/maintenance/db/bs_reminder.patch.add_repeat_config_col.sql"
			);

			$this->updater->addExtensionField(
				'bs_reminder',
				'rem_type',
				"$dir/maintenance/db/bs_reminder.patch.add_type_col.sql"
			);
		}
	}

	/**
	 *
	 * @return string
	 */
	protected function getExtensionPath() {
		return dirname( dirname( dirname( __DIR__ ) ) );
	}

}
