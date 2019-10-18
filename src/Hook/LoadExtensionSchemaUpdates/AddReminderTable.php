<?php

namespace BlueSpice\Reminder\Hook\LoadExtensionSchemaUpdates;

class AddReminderTable extends \BlueSpice\Hook\LoadExtensionSchemaUpdates {

	protected function doProcess() {
		$dir = $this->getExtensionPath();

		$this->updater->addExtensionTable(
			'bs_reminder',
			"$dir/maintenance/db/bs_reminder.sql"
		);

		// BS 2.23.3: Rename remind_date => reminder_date
		$this->updater->modifyExtensionField(
			'bs_reminder',
			'remind_date',
			"$dir/bs_reminder.patch.modify_remind_date.sql"
		);

		// BS 2.23.3: prefix columns in reminder
		$this->updater->modifyExtensionField(
			'bs_reminder',
			'id',
			"$dir/bs_reminder.patch.add_col_prefix.sql"
		);

		// BS 2.23.3: add comment field
		$this->updater->addExtensionField(
			'bs_reminder',
			'rem_comment',
			"$dir/bs_reminder.patch.add_comment_col.sql"
		);
	}

	protected function getExtensionPath() {
		return dirname( dirname( dirname( __DIR__ ) ) );
	}

}
