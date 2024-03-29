-- This file is automatically generated using maintenance/generateSchemaSql.php.
-- Source: extensions/BlueSpiceReminder/maintenance/db/sql/bs_reminder.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
CREATE TABLE bs_reminder (
  rem_id SERIAL NOT NULL,
  rem_user_id INT NOT NULL,
  rem_page_id INT NOT NULL,
  rem_date TIMESTAMPTZ NOT NULL,
  rem_comment TEXT NOT NULL,
  rem_is_repeating SMALLINT DEFAULT 0 NOT NULL,
  rem_repeat_date_end VARCHAR(14) DEFAULT '' NOT NULL,
  rem_repeat_config TEXT NOT NULL,
  rem_type VARCHAR(255) DEFAULT '' NOT NULL,
  PRIMARY KEY(rem_id)
);

CREATE INDEX rem_user_id_idx ON bs_reminder (rem_user_id);

CREATE INDEX rem_page_id_idx ON bs_reminder (rem_page_id);

CREATE INDEX rem_user_page_idx ON bs_reminder (rem_user_id, rem_page_id);

CREATE INDEX rem_date_idx ON bs_reminder (rem_date);
