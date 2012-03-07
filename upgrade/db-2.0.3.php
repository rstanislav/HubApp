<?php
$sql = array();
$sql[0] = "ALTER TABLE  `drives` CHANGE  `DriveRoot`  `DriveShare` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
$sql[1] = "ALTER TABLE  `drives` ADD  `DriveUser` VARCHAR( 255 ) NOT NULL AFTER  `DriveShare` , ADD  `DrivePass` VARCHAR( 255 ) NOT NULL AFTER  `DriveUser`";
$sql[2] = "ALTER TABLE  `drives` CHANGE  `DriveLetter`  `DriveMount` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
$sql[3] = "ALTER TABLE `drives` MODIFY COLUMN `DriveNetwork` INT(1) NOT NULL AFTER `DriveActive`;"
$sql[4] = "ALTER TABLE `settings` ADD `SettingHubLocalHostname` VARCHAR(255)  NOT NULL  DEFAULT ''  AFTER `SettingUTorrentDefinedDownSpeed`;"
?>