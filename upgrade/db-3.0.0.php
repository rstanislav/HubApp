<?php
$sql = array();
$sql[] = "ALTER TABLE  `activity` CHANGE  `ActivityID`  `ID` SMALLINT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
CHANGE  `ActivityDate`  `Date` INT( 10 ) UNSIGNED NOT NULL ,
CHANGE  `ActivityUser`  `User` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `ActivityURL`  `URL` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
$sql[] = "ALTER TABLE  `drives` CHANGE  `DriveID`  `ID` SMALLINT( 4 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
CHANGE  `DriveDate`  `Date` INT( 10 ) UNSIGNED NOT NULL ,
CHANGE  `DriveShare`  `Share` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `DriveUser`  `User` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `DrivePass`  `Password` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `DriveMount`  `Mount` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `DriveActive`  `IsActive` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0',
CHANGE  `DriveNetwork`  `IsNetwork` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0'";
$sql[] = "ALTER TABLE `episodes` CHANGE `EpisodeID` `ID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT, CHANGE `EpisodeDate` `Date` INT(10) UNSIGNED NOT NULL, CHANGE `EpisodeListingNo` `ListingNo` TINYINT(1) UNSIGNED NOT NULL, CHANGE `EpisodeSeason` `Season` TINYINT(2) UNSIGNED NOT NULL, CHANGE `EpisodeEpisode` `Episode` SMALLINT(4) UNSIGNED NOT NULL, CHANGE `EpisodeTitle` `Title` TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `EpisodePlot` `Plot` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `EpisodeRating` `Rating` VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `EpisodeRatingCount` `RatingCount` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `EpisodeBanner` `Banner` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `EpisodeAirDate` `AirDate` INT(10) UNSIGNED NOT NULL, CHANGE `SeriesKey` `SeriesKey` INT(10) UNSIGNED NOT NULL, CHANGE `EpisodeFile` `File` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `TorrentKey` `TorrentKey` INT(12) UNSIGNED NOT NULL, CHANGE `EpisodeTheTVDBID` `TheTVDBID` INT(10) UNSIGNED NOT NULL";
$sql[] = "ALTER TABLE  `log` CHANGE  `LogID`  `ID` SMALLINT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
CHANGE  `LogDate`  `Date` INT( 10 ) UNSIGNED NOT NULL ,
CHANGE  `LogEvent`  `Event` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `LogType`  `Type` ENUM(  '',  'Failure',  'Success' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '',
CHANGE  `LogError`  `Error` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `LogText`  `Text` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `LogAction`  `Action` ENUM(  '',  'clean',  'update' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  ''";
$sql[] = "ALTER TABLE  `notifications` CHANGE  `NotificationID`  `ID` TINYINT( 2 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
CHANGE  `NotificationDate`  `Date` INT( 10 ) UNSIGNED NOT NULL ,
CHANGE  `NotificationText`  `Text` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `NotificationAction`  `Action` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
$sql[] = "ALTER TABLE  `permissions` CHANGE  `PermissionID`  `ID` TINYINT( 2 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
CHANGE  `PermissionDate`  `Date` INT( 10 ) UNSIGNED NOT NULL ,
CHANGE  `PermissionAction`  `Action` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `PermissionText`  `Text` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `PermissionValue`  `Value` SMALLINT( 20 ) UNSIGNED NOT NULL";
$sql[] = "ALTER TABLE  `rss` CHANGE  `RSSID`  `ID` TINYINT( 4 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
CHANGE  `RSSDate`  `Date` INT( 10 ) UNSIGNED NOT NULL ,
CHANGE  `RSSTitle`  `Title` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `RSSFeed`  `Feed` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
$sql[] = "DROP TABLE seeding";
$sql[] = "ALTER TABLE `series` CHANGE `SerieID` `ID` INT(4) UNSIGNED NOT NULL AUTO_INCREMENT, CHANGE `SerieDate` `Date` INT(10) UNSIGNED NOT NULL, CHANGE `SerieTitle` `Title` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `SeriePlot` `Plot` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `SerieContentRating` `ContentRating` VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `SerieIMDBID` `IMDBID` VARCHAR(14) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `SerieRating` `Rating` VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `SerieRatingCount` `RatingCount` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `SerieBanner` `Banner` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `SerieFanArt` `FanArt` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `SeriePoster` `Poster` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `SerieFirstAired` `FirstAired` INT(10) UNSIGNED NOT NULL, CHANGE `SerieAirDay` `AirDay` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `SerieAirTime` `AirTime` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `SerieRuntime` `Runtime` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `SerieNetwork` `Network` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `SerieStatus` `Status` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `SerieGenre` `Genre` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `SerieTheTVDBID` `TheTVDBID` MEDIUMINT(6) UNSIGNED NOT NULL, CHANGE `SerieTitleAlt` `TitleAlt` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
$sql[] = "ALTER TABLE  `torrents` CHANGE  `TorrentID`  `ID` MEDIUMINT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
CHANGE  `TorrentDate`  `Date` INT( 10 ) UNSIGNED NOT NULL ,
CHANGE  `TorrentPubDate`  `PubDate` INT( 10 ) UNSIGNED NOT NULL ,
CHANGE  `TorrentURI`  `URI` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `TorrentTitle`  `Title` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `TorrentSize`  `Size` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `TorrentCategory`  `Category` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `RSSKey`  `RSSKey` TINYINT( 4 ) UNSIGNED NOT NULL ,
CHANGE  `IsBroken`  `IsBroken` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0'";
$sql[] = "ALTER TABLE  `user` CHANGE  `UserID`  `ID` TINYINT( 3 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
CHANGE  `UserDate`  `Date` INT( 10 ) UNSIGNED NOT NULL ,
CHANGE  `UserName`  `Name` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `UserPassword`  `Password` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `UserEMail`  `EMail` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `UserGroupKey`  `UserGroupKey` TINYINT( 3 ) UNSIGNED NOT NULL";
$sql[] = "ALTER TABLE  `usergroups` CHANGE  `UserGroupID`  `ID` TINYINT( 3 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
CHANGE  `UserGroupDate`  `Date` INT( 10 ) UNSIGNED NOT NULL ,
CHANGE  `UserGroupName`  `Name` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
$sql[] = "ALTER TABLE  `wishlist` CHANGE  `WishlistID`  `ID` TINYINT( 4 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
CHANGE  `WishlistDate`  `Date` INT( 10 ) NOT NULL ,
CHANGE  `WishlistTitle`  `Title` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `WishlistYear`  `Year` SMALLINT( 4 ) UNSIGNED NOT NULL ,
CHANGE  `WishlistDownloadDate`  `DownloadDate` INT( 10 ) UNSIGNED NOT NULL ,
CHANGE  `WishlistFile`  `File` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `WishlistFileGone`  `IsFileGone` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0',
CHANGE  `TorrentKey`  `TorrentKey` MEDIUMINT( 10 ) UNSIGNED NOT NULL";
$sql[] = "ALTER TABLE  `zones` CHANGE  `ZoneID`  `ID` TINYINT( 4 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
CHANGE  `ZoneDate`  `Date` INT( 10 ) UNSIGNED NOT NULL ,
CHANGE  `ZoneName`  `Name` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `ZoneDefault`  `IsDefault` TINYINT( 1 ) UNSIGNED NOT NULL ,
CHANGE  `ZoneXBMCHost`  `XBMCHost` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `ZoneXBMCPort`  `XBMCPort` SMALLINT( 6 ) UNSIGNED NOT NULL ,
CHANGE  `ZoneXBMCUsername`  `XBMCUser` VARCHAR( 15 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `ZoneXBMCPassword`  `XBMCPassword` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
?>