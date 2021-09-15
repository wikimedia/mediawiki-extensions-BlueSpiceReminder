<?php

namespace BlueSpice\Reminder\Hook\UserMergeAccountFields;

use BlueSpice\DistributionConnector\Hook\UserMergeAccountFields;

class MergeReminderDBFields extends UserMergeAccountFields {

	protected function doProcess() {
		$this->updateFields[] = [ 'bs_reminder', 'rem_user_id' ];
	}

}
