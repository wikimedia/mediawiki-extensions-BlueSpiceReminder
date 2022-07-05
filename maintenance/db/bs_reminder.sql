-- Database definition for Reminder
--
-- Part of BlueSpice MediaWiki
--
-- @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-network.hk>
-- @version    $Id: review.sql 5929 2012-07-25 13:03:19Z rvogel $
-- @package    BlueSpice_Extensions
-- @subpackage Reminder
-- @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
-- @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
-- @filesource

CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/bs_reminder (
  `rem_id`              int unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `rem_user_id`         INT(10) NOT NULL ,
  `rem_page_id`         INT(10) NOT NULL ,
  `rem_date`            DATE NOT NULL ,
  `rem_comment`         VARBINARY(255) ,
  `rem_is_repeating`    TINYINT(1) NOT NULL DEFAULT '0' ,
  `rem_repeat_date_end` VARCHAR (14) NOT NULL DEFAULT '' ,
  `rem_repeat_config`   BLOB NOT NULL ,
  `rem_type`            varchar(255) NOT NULL DEFAULT ''
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/`rem_user_id_idx` ON /*_*/bs_reminder (`rem_user_id` ASC);
CREATE INDEX /*i*/`rem_page_id_idx` ON /*_*/bs_reminder(`rem_page_id` ASC);
CREATE INDEX /*i*/`rem_user_page_idx` ON /*_*/bs_reminder(`rem_user_id` ASC, `rem_page_id` ASC);
CREATE INDEX /*i*/`rem_date_idx` ON /*_*/bs_reminder(`rem_date` ASC);