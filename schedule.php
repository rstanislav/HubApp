<?php
ini_set('max_execution_time', (60 * 60 * 5));

session_start();
require_once realpath(dirname(__FILE__)).'/resources/config.php';
require_once realpath(dirname(__FILE__)).'/libraries/libraries.php';

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
$UTorrentObj->DeleteFinishedTorrents();

if((date('G') > 3 && date('G') < 7)) {
	$FolderRebuild = $HubObj->PDO->query('SELECT Value AS Last FROM Hub WHERE Setting = "LastFolderRebuild"')->fetch();
	
	if((time() - $FolderRebuild['Last']) >= (60 * 60 * 24)) {
		// Rebuild serie folders across all drives
		$SeriesObj->RebuildFolders();
		
		$RebuildPrep = $HubObj->PDO->prepare('UPDATE Hub SET Value = :Time WHERE Setting = "LastFolderRebuild"');
		$RebuildPrep->execute(array(':Time' => time()));
	}
	
	$SerieRefresh = $HubObj->PDO->query('SELECT Value AS Last FROM Hub WHERE Setting = "LastSerieRefresh"')->fetch();
	
	if((time() - $SerieRefresh['Last']) >= (60 * 60 * 24)) {
		// Refresh database series data
		$SeriesObj->RefreshAllSeries();
		
		$RefreshPrep = $HubObj->PDO->prepare('UPDATE Hub SET Value = :Time WHERE Setting = "LastSerieRefresh"');
		$RefreshPrep->execute(array(':Time' => time()));
	}
	
	$SerieRebuild= $HubObj->PDO->query('SELECT Value AS Last FROM Hub WHERE Setting = "LastSerieRebuild"')->fetch();
	
	if((time() - $SerieRefresh['Last']) >= (60 * 60 * 24)) {
		// Rebuild database episodes data
		$SeriesObj->RebuildEpisodes();
		
		$RebuildPrep = $HubObj->PDO->prepare('UPDATE Hub SET Value = :Time WHERE Setting = "LastSerieRebuild"');
		$RebuildPrep->execute(array(':Time' => time()));
	}
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

$HubObj->Unlock();
?>