<?php
$IsUpgraded = TRUE;
$SettingsPrep = $this->PDO->prepare('SELECT * FROM Settings');
$SettingsPrep->execute();

if($SettingsPrep->rowCount()) {
	$Settings = $SettingsPrep->fetchAll();
	
	$this->d($Settings);
	$this->PDO->query('INSERT INTO Hub (Setting, Value) VALUES
	("LocalHostname",            "'.$Settings[0]['SettingHubLocalHostname'].'"),
	("LocalIP",                  "'.$Settings[0]['SettingHubLocalIP'].'"),
	("BackupFolder",             ""),
	("BackupHubFiles",           "0"),
	("BackupHubDatabase",        "0"),
	("BackupXBMCFiles",          "0"),
	("BackupXBMCDatabase",       "0"),
	("MinimumDiskSpaceRequired", "'.$Settings[0]['SettingHubMinimumActiveDiskFreeSpaceInGB'].'"),
	("MinimumDownloadQuality",   "'.$Settings[0]['SettingHubMinimumDownloadQuality'].'"),
	("MaximumDownloadQuality",   "'.$Settings[0]['SettingHubMaximumDownloadQuality'].'"),
	("TheTVDBAPIKey",            "'.$Settings[0]['SettingHubTheTVDBAPIKey'].'"),
	("KillSwitch",               "'.$Settings[0]['SettingHubKillSwitch'].'"),
	("SearchURITVSeries",        "'.$Settings[0]['SettingHubSearchURITVSeries'].'"),
	("SearchURIMovies",          "'.$Settings[0]['SettingHubSearchURIMovies'].'"),
	("XBMCDataFolder",           "'.str_replace('\\', '/', $Settings[0]['SettingXBMCDatabaseFolder']).'"),
	("UTorrentIP",               "'.$Settings[0]['SettingUTorrentHostname'].'"),
	("UTorrentPort",             "'.$Settings[0]['SettingUTorrentPort'].'"),
	("UTorrentUsername",         "'.$Settings[0]['SettingUTorrentUsername'].'"),
	("UTorrentPassword",         "'.$Settings[0]['SettingUTorrentPassword'].'"),
	("UTorrentWatchFolder",      "'.str_replace('\\', '/', $Settings[0]['SettingUTorrentWatchFolder']).'"),
	("UTorrentDefaultUpSpeed",   "'.$Settings[0]['SettingUTorrentDefaultUpSpeed'].'"),
	("UTorrentDefaultDownSpeed", "'.$Settings[0]['SettingUTorrentDefaultDownSpeed'].'"),
	("UTorrentDefinedUpSpeed",   "'.$Settings[0]['SettingUTorrentDefinedUpSpeed'].'"),
	("UTorrentDefinedDownSpeed", "'.$Settings[0]['SettingUTorrentDefinedDownSpeed'].'"),
	("ShareMovies",              "0"),
	("ShareWishlist",            "0"),
	("LastWishlistUpdate",       "0"),
	("LastMoviesUpdate",         "0"),
	("LastWishlistRefresh",      "0"),
	("LastBackup",               "0")');
	
	$this->PDO->query('DROP TABLE Settings');
}
?>