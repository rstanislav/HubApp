<?php
error_reporting(E_ALL);

ini_set('display_errors',     0); 
ini_set('log_errors',         1);
ini_set('error_log',          realpath(dirname(__FILE__)).'/tmp/schedule_error.log');
ini_set('max_execution_time', (60 * 60 * 5));

session_start();
require_once realpath(dirname(__FILE__)).'/resources/config.php';
require_once realpath(dirname(__FILE__)).'/libraries/libraries.php';

$HubObj->CheckForDBUpgrade();

$Settings = $HubObj->Settings;

if($Settings['SettingHubKillSwitch'] || $HubObj->CheckLock()) {
	die();
}
else {
	$HubObj->Lock();
}

$UTorrentObj->Connect();
$SeriesObj->ConnectTheTVDB();

// Check for existing active drive and that all required folders are present
$DrivesObj->CheckActiveDrive();

// Backup XBMC Files
if(date('G') > 4 && date('G') < 6) {
	//
}

// Update RSS Feeds
$RSSObj->Update();

// Download torrents corresponding with new episodes and/or wishlist items
$RSSObj->DownloadWantedTorrents();

// Remove finished torrents from uTorrent
if(is_object($UTorrentObj->UTorrentAPI)) {
	$UTorrentObj->DeleteFinishedTorrents();
}

// Extract and/or move completed downloads across all drives
$ExtractFilesObj->ExtractAndMoveAllFiles();

// Check previous log entries and update library if new content is available
$LogActivity = $HubObj->PDO->query('SELECT LogDate AS NewContent FROM Log WHERE LogAction = "update" ORDER BY LogDate DESC LIMIT 1')->fetch();
$XBMCActivity = $HubObj->PDO->query('SELECT LogDate AS LastUpdate FROM Log WHERE LogType = "Success" AND LogEvent LIKE "%XBMC" AND (LogText LIKE "Updated XBMC Library%") ORDER BY LogDate DESC LIMIT 1')->fetch();

if($LogActivity['NewContent'] > $XBMCActivity['LastUpdate']) {
	$XBMCObj->Connect();
	if(is_object($XBMCObj->XBMCRPC)) {
		$XBMCObj->ScanForContent();
		// $XBMCObj->Notification('Hub', 'Adding new content');
			
		$HubObj->AddLog(EVENT.'XBMC', 'Success', 'Updated XBMC Library');
	}
}

$FolderRebuild = $HubObj->PDO->query('SELECT Value AS Last FROM Hub WHERE Setting = "LastFolderRebuild"')->fetch();
$SerieRefresh  = $HubObj->PDO->query('SELECT Value AS Last FROM Hub WHERE Setting = "LastSerieRefresh"')->fetch();
$SerieRebuild  = $HubObj->PDO->query('SELECT Value AS Last FROM Hub WHERE Setting = "LastSerieRebuild"')->fetch();

$LatestUpdate = max($FolderRebuild['Last'], $SerieRefresh['Last'], $SerieRebuild['Last']);
if((date('G') > 3 && date('G') < 7) || (time() - $LatestUpdate) >= (60 * 60 * 24 * 2)) {
	if(date('dmy', $FolderRebuild['Last']) != date('dmy')) {
		$SeriesObj->RebuildFolders();
	}

	if(date('dmy', $SerieRefresh['Last']) != date('dmy')) {
		if(is_object($SeriesObj->TheTVDBAPI)) {
			$SeriesObj->RefreshAllSeries();
		}
	}

	if(date('dmy', $SerieRebuild['Last']) != date('dmy')) {
		if(is_object($SeriesObj->TheTVDBAPI)) {
			$SeriesObj->RebuildEpisodes();
		}
	}
}

$HubObj->Unlock();
?>