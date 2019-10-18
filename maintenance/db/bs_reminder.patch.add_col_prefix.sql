ALTER TABLE /*$wgDBprefix*/bs_reminder CHANGE COLUMN `id` `rem_id` INT(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE /*$wgDBprefix*/bs_reminder CHANGE COLUMN `user_id` `rem_user_id` INT(10) NOT NULL;
ALTER TABLE /*$wgDBprefix*/bs_reminder CHANGE COLUMN `page_id` `rem_page_id` INT(10) NOT NULL;
ALTER TABLE /*$wgDBprefix*/bs_reminder CHANGE COLUMN `reminder_date` `rem_date` DATE NOT NULL;