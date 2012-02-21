<?php
$sql = array();
$sql[0] = 'ALTER TABLE  `settings` CHANGE  `SettingHubMinimumActiveDiskPercentage`  `SettingHubMinimumActiveDiskFreeSpaceInGB` INT( 2 ) NOT NULL DEFAULT  \'5\'';
$sql[1] = 'UPDATE  `hub`.`settings` SET  `SettingHubMinimumActiveDiskFreeSpaceInGB` =  \'5\' WHERE  `settings`.`SettingID` =1;'
);
?>