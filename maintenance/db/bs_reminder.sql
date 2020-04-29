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
  `rem_id`              INT(10) NOT NULL AUTO_INCREMENT ,
  `rem_user_id`         INT(10) NOT NULL ,
  `rem_page_id`         INT(10) NOT NULL ,
  `rem_date`            DATE NOT NULL ,
  `rem_comment`         VARBINARY(255) ,
  `rem_is_repeating`    TINYINT(1) NOT NULL DEFAULT '0' ,
  `rem_repeat_date_end` VARCHAR (14) NOT NULL DEFAULT '' ,
  `rem_repeat_config`   BLOB NOT NULL ,

  PRIMARY KEY (`rem_id`) ,
  INDEX `rem_user_id_idx` (`rem_user_id` ASC) ,
  INDEX `rem_page_id_idx` (`rem_page_id` ASC) ,
  INDEX `rem_user_page_idx` (`rem_user_id` ASC, `rem_page_id` ASC) ,
  INDEX `rem_date_idx` (`rem_date` ASC)
) /*$wgDBTableOptions*/;