<?php
$sql = array();
$sql[] = "ALTER TABLE  `torrents` ADD  `IsBroken` INT( 1 ) NOT NULL DEFAULT  '0'";
$sql[] = "ALTER TABLE  `activity` CHANGE  `ActivityID`  `ActivityID` INT( 10 ) NOT NULL AUTO_INCREMENT ,
CHANGE  `ActivityDate`  `ActivityDate` INT( 10 ) NOT NULL ,
CHANGE  `ActivityUser`  `ActivityUser` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `ActivityURL`  `ActivityURL` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
$sql[] = "ALTER TABLE `episodes` CHANGE `EpisodeID` `EpisodeID` INT(10) NOT NULL AUTO_INCREMENT, CHANGE `EpisodeDate` `EpisodeDate` INT(10) NOT NULL, CHANGE `EpisodeListingNo` `EpisodeListingNo` INT(10) NOT NULL, CHANGE `EpisodeSeason` `EpisodeSeason` INT(2) NOT NULL, CHANGE `EpisodeEpisode` `EpisodeEpisode` INT(4) NOT NULL, CHANGE `EpisodeTitle` `EpisodeTitle` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `EpisodePlot` `EpisodePlot` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `EpisodeRating` `EpisodeRating` VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `EpisodeRatingCount` `EpisodeRatingCount` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `EpisodeBanner` `EpisodeBanner` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `EpisodeAirDate` `EpisodeAirDate` INT(10) NOT NULL, CHANGE `SeriesKey` `SeriesKey` INT(4) NOT NULL, CHANGE `EpisodeFile` `EpisodeFile` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `TorrentKey` `TorrentKey` INT(12) NOT NULL, CHANGE `EpisodeTheTVDBID` `EpisodeTheTVDBID` INT(10) NOT NULL";
$sql[] = "ALTER TABLE  `log` CHANGE  `LogID`  `LogID` INT( 10 ) NOT NULL AUTO_INCREMENT ,
CHANGE  `LogDate`  `LogDate` INT( 10 ) NOT NULL ,
CHANGE  `LogEvent`  `LogEvent` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `LogType`  `LogType` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `LogError`  `LogError` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `LogText`  `LogText` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `LogAction`  `LogAction` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
$sql[] = "ALTER TABLE  `notifications` CHANGE  `NotificationID`  `NotificationID` INT( 10 ) NOT NULL AUTO_INCREMENT ,
CHANGE  `NotificationDate`  `NotificationDate` INT( 10 ) NOT NULL ,
CHANGE  `NotificationText`  `NotificationText` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `NotificationAction`  `NotificationAction` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
$sql[] = "ALTER TABLE  `permissions` CHANGE  `PermissionAction`  `PermissionAction` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `PermissionText`  `PermissionText` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
$sql[] = "ALTER TABLE  `rss` CHANGE  `RSSTitle`  `RSSTitle` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `RSSFeed`  `RSSFeed` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
$sql[] = "ALTER TABLE  `seeding` CHANGE  `SeedingFile`  `SeedingFile` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
$sql[] = "ALTER TABLE  `series` CHANGE  `SerieTitle`  `SerieTitle` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `SeriePlot`  `SeriePlot` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `SerieAirDay`  `SerieAirDay` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `SerieAirTime`  `SerieAirTime` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `SerieRuntime`  `SerieRuntime` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `SerieNetwork`  `SerieNetwork` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `SerieStatus`  `SerieStatus` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `SerieGenre`  `SerieGenre` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
$sql[] = "ALTER TABLE  `torrents` CHANGE  `TorrentURI`  `TorrentURI` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `TorrentTitle`  `TorrentTitle` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `TorrentSize`  `TorrentSize` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `TorrentCategory`  `TorrentCategory` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
$sql[] = "ALTER TABLE  `user` CHANGE  `UserName`  `UserName` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `UserPassword`  `UserPassword` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `UserEMail`  `UserEMail` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
$sql[] = "ALTER TABLE  `usergroups` CHANGE  `UserGroupName`  `UserGroupName` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
$sql[] = "ALTER TABLE  `zones` CHANGE  `ZoneName`  `ZoneName` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `ZoneXBMCHost`  `ZoneXBMCHost` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `ZoneXBMCUsername`  `ZoneXBMCUsername` VARCHAR( 15 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `ZoneXBMCPassword`  `ZoneXBMCPassword` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";

$sql[] = "ALTER TABLE `activity` CHANGE `ActivityID` `ActivityID` SMALLINT(10)  UNSIGNED  NOT NULL  AUTO_INCREMENT;";
$sql[] = "ALTER TABLE `activity` CHANGE `ActivityDate` `ActivityDate` INT(10)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `drives` CHANGE `DriveID` `DriveID` SMALLINT(4)  UNSIGNED  NOT NULL  AUTO_INCREMENT;";
$sql[] = "ALTER TABLE `drives` CHANGE `DriveDate` `DriveDate` INT(10)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `drives` CHANGE `DriveActive` `DriveActive` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0';";
$sql[] = "ALTER TABLE `drives` CHANGE `DriveNetwork` `DriveNetwork` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0';";
$sql[] = "ALTER TABLE `episodes` CHANGE `EpisodeID` `EpisodeID` SMALLINT(10)  UNSIGNED  NOT NULL  AUTO_INCREMENT;";
$sql[] = "ALTER TABLE `episodes` CHANGE `EpisodeDate` `EpisodeDate` INT(10)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `episodes` CHANGE `EpisodeListingNo` `EpisodeListingNo` TINYINT(1)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `episodes` CHANGE `EpisodeSeason` `EpisodeSeason` TINYINT(2)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `episodes` CHANGE `EpisodeEpisode` `EpisodeEpisode` SMALLINT(4)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `episodes` CHANGE `EpisodeTitle` `EpisodeTitle` TINYTEXT  NOT NULL;";
$sql[] = "ALTER TABLE `episodes` CHANGE `EpisodeAirDate` `EpisodeAirDate` INT(10)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `episodes` CHANGE `SeriesKey` `SeriesKey` TINYINT(4)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `episodes` CHANGE `EpisodeTheTVDBID` `EpisodeTheTVDBID` INT(10)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `episodes` CHANGE `TorrentKey` `TorrentKey` INT(12)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `log` CHANGE `LogID` `LogID` SMALLINT(10)  UNSIGNED  NOT NULL  AUTO_INCREMENT;";
$sql[] = "ALTER TABLE `log` CHANGE `LogDate` `LogDate` INT(10)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `log` CHANGE `LogType` `LogType` ENUM(255)  NOT NULL  DEFAULT '';";
$sql[] = "ALTER TABLE `log` CHANGE `LogAction` `LogAction` ENUM('', 'clean', 'update')  NOT NULL  DEFAULT '';";
$sql[] = "ALTER TABLE `log` CHANGE `LogType` `LogType` ENUM('', 'Failure', 'Success')  NOT NULL  DEFAULT '';";
$sql[] = "ALTER TABLE `notifications` CHANGE `NotificationID` `NotificationID` TINYINT(2)  UNSIGNED  NOT NULL  AUTO_INCREMENT;";
$sql[] = "ALTER TABLE `notifications` CHANGE `NotificationDate` `NotificationDate` INT(10)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `permissions` CHANGE `PermissionID` `PermissionID` TINYINT(2)  UNSIGNED  NOT NULL  AUTO_INCREMENT;";
$sql[] = "ALTER TABLE `permissions` CHANGE `PermissionDate` `PermissionDate` INT(10)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `permissions` CHANGE `PermissionValue` `PermissionValue` SMALLINT(20)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `rss` CHANGE `RSSID` `RSSID` TINYINT(4)  UNSIGNED  NOT NULL  AUTO_INCREMENT;";
$sql[] = "ALTER TABLE `rss` CHANGE `RSSDate` `RSSDate` INT(10)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `seeding` CHANGE `SeedingID` `SeedingID` SMALLINT(4)  UNSIGNED  NOT NULL  AUTO_INCREMENT;";
$sql[] = "ALTER TABLE `seeding` CHANGE `SeedingDate` `SeedingDate` INT(10)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `series` CHANGE `SerieID` `SerieID` TINYINT(4)  UNSIGNED  NOT NULL  AUTO_INCREMENT;";
$sql[] = "ALTER TABLE `series` CHANGE `SerieDate` `SerieDate` INT(10)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `series` CHANGE `SerieFirstAired` `SerieFirstAired` INT(10)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `series` CHANGE `SerieTheTVDBID` `SerieTheTVDBID` MEDIUMINT(6)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `torrents` CHANGE `TorrentID` `TorrentID` MEDIUMINT(10)  UNSIGNED  NOT NULL  AUTO_INCREMENT;";
$sql[] = "ALTER TABLE `torrents` CHANGE `TorrentDate` `TorrentDate` INT(10)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `torrents` CHANGE `TorrentPubDate` `TorrentPubDate` INT(10)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `torrents` CHANGE `RSSKey` `RSSKey` TINYINT(4)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `torrents` CHANGE `IsBroken` `IsBroken` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0';";
$sql[] = "ALTER TABLE `user` CHANGE `UserID` `UserID` TINYINT(3)  UNSIGNED  NOT NULL  AUTO_INCREMENT;";
$sql[] = "ALTER TABLE `user` CHANGE `UserDate` `UserDate` INT(10)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `user` CHANGE `UserGroupKey` `UserGroupKey` TINYINT(3)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `usergrouppermissions` CHANGE `UserGroupKey` `UserGroupKey` TINYINT(3)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `usergrouppermissions` CHANGE `PermissionKey` `PermissionKey` TINYINT(2)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `usergroups` CHANGE `UserGroupID` `UserGroupID` TINYINT(3)  UNSIGNED  NOT NULL  AUTO_INCREMENT;";
$sql[] = "ALTER TABLE `usergroups` CHANGE `UserGroupDate` `UserGroupDate` INT(10)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `usernotifications` CHANGE `UserKey` `UserKey` TINYINT(3)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `usernotifications` CHANGE `NotificationKey` `NotificationKey` TINYINT(3)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `wishlist` CHANGE `WishlistID` `WishlistID` TINYINT(4)  UNSIGNED  NOT NULL  AUTO_INCREMENT;";
$sql[] = "ALTER TABLE `wishlist` CHANGE `WishlistYear` `WishlistYear` SMALLINT(4)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `wishlist` CHANGE `WishlistDownloadDate` `WishlistDownloadDate` INT(10)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `wishlist` CHANGE `WishlistFileGone` `WishlistFileGone` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0';";
$sql[] = "ALTER TABLE `wishlist` CHANGE `TorrentKey` `TorrentKey` MEDIUMINT(10)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `zones` CHANGE `ZoneID` `ZoneID` TINYINT(4)  UNSIGNED  NOT NULL  AUTO_INCREMENT;";
$sql[] = "ALTER TABLE `zones` CHANGE `ZoneDate` `ZoneDate` INT(10)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `zones` CHANGE `ZoneDefault` `ZoneDefault` TINYINT(1)  UNSIGNED  NOT NULL;";
$sql[] = "ALTER TABLE `zones` CHANGE `ZoneXBMCPort` `ZoneXBMCPort` SMALLINT(6)  UNSIGNED  NOT NULL;";
?>