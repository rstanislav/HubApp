<?php
error_reporting(E_ALL);

ini_set('display_errors',     1); 
ini_set('log_errors',         1);
ini_set('error_log',          realpath(dirname(__FILE__)).'/tmp/schedule_error.log');
ini_set('max_execution_time', (60 * 60 * 5));

session_start();
require_once realpath(dirname(__FILE__)).'/resources/config.php';
require_once realpath(dirname(__FILE__)).'/api/resources/DB.php';
require_once realpath(dirname(__FILE__)).'/api/resources/functions.php';
require_once realpath(dirname(__FILE__)).'/resources/api.hub.php';

$DBUpgrade = json_decode($Hub->Request('/hub/upgrade'));

$LockStatus = json_decode($Hub->Request('/hub/lockstatus'));

if(is_object($LockStatus) && property_exists($LockStatus, 'error')) {
	if($LockStatus->error->code == 200) {
		$Hub->Request('/hub/lock');
	}
	else {
		die('locked');
	}
}

// Check for existing active drive and that all required folders are present
$ActiveDrive = json_decode($Hub->Request('/drives/active/check'));
if(is_object($ActiveDrive) && property_exists($ActiveDrive, 'error')) {
	if($ActiveDrive->error->code != 200) {
		die();
	}
}

// Update RSS Feeds
$Hub->Request('/rss/refresh');

// Download torrents corresponding with new episodes and/or wishlist items
$Hub->Request('/rss/download');

// Remove finished torrents from uTorrent
$Hub->Request('/utorrent/remove/finished');

// Delete backup files older than x days as defined in Hub settings
$Hub->Request('/hub/backup/clean');

// Extract and/or move completed downloads across all drives
$CompletedFiles = json_decode($Hub->Request('/drives/files/completed'));

if(is_object($CompletedFiles) && !property_exists($CompletedFiles, 'error')) {
	if(is_object($CompletedFiles) && property_exists($CompletedFiles, 'Move')) {
		foreach($CompletedFiles->Move AS $Move) {
			$Hub->Request('/drives/files/move', 'POST', array('File' => $Move));
		}
	}
	
	if(is_object($CompletedFiles) && property_exists($CompletedFiles, 'Extract')) {
		foreach($CompletedFiles->Extract AS $Extract) {
			$Hub->Request('/drives/files/extract', 'POST', array('File' => $Extract));
		}
	}
}

// Check previous log entries and update library if new content is available
$Hub->Request('/xbmc/library/newcontentscan');

// Cache movie covers locally
$Hub->Request('/xbmc/movies/cachecovers');

$FolderRebuild   = GetSetting('LastFolderRebuild');
$SerieRefresh    = GetSetting('LastSerieRefresh');
$SerieRebuild    = GetSetting('LastSerieRebuild');
$WishlistUpdate  = GetSetting('LastWishlistUpdate');
$MoviesUpdate    = GetSetting('LastMoviesUpdate');
$WishlistRefresh = GetSetting('LastWishlistRefresh');
$Backup          = time();//GetSetting('LastBackup');

$LatestUpdate = min($FolderRebuild, $SerieRefresh, $SerieRebuild, $WishlistUpdate, $MoviesUpdate, $WishlistRefresh, $Backup);
if((date('G') >= 4 && date('G') <= 6) || (time() - $LatestUpdate) >= (60 * 60 * 24 * 2)) {
	if(date('dmy', $FolderRebuild) != date('dmy')) {
		$Hub->Request('/series/rebuild/folders');
	}

	if(date('dmy', $SerieRefresh) != date('dmy')) {
		$Hub->Request('/series/refresh/all');
	}

	if(date('dmy', $SerieRebuild) != date('dmy')) {
		$Hub->Request('/series/rebuild/episodes');
	}
	
	if(GetSetting('ShareMovies')) {
		if(date('dmy', $MoviesUpdate) != date('dmy')) {
			$Hub->Request('/xbmc/update/shared');
		}
	}
	
	if(GetSetting('ShareWishlist')) {
		if(date('dmy', $WishlistUpdate) != date('dmy')) {
			$Hub->Request('/wishlist/update/shared');
		}
	}
	
	if(date('dmy', $WishlistRefresh) != date('dmy')) {
		$Hub->Request('/wishlist/refresh');
	}
}

/*
$LatestUpdate = min($FolderRebuild, $SerieRefresh, $SerieRebuild, $WishlistUpdate, $MoviesUpdate, $WishlistRefresh, $Backup);
if((date('G') >= 4 && date('G') <= 6) || (time() - $LatestUpdate) >= (60 * 60 * 24 * 2)) {
	if(date('dmy', $Backup) != date('dmy')) {
		if(is_dir($HubObj->GetSetting('BackupFolder'))) {
			if($HubObj->GetSetting('BackupHubFiles')) {
				if(!is_file($HubObj->GetSetting('BackupFolder').'/hub-files-'.date('d-m-Y').'.zip')) {
					if($HubObj->ZipDirectory(APP_PATH, $HubObj->GetSetting('BackupFolder').'/hub-files-'.date('d-m-Y').'.zip')) {
						$HubObj->AddLog(EVENT.'Backup', 'Success', 'Backed up Hub files to "'.$HubObj->GetSetting('BackupFolder').'/hub-files-'.date('d-m-Y').'.zip"');
					}
				}
			}
		
			if($HubObj->GetSetting('BackupHubDatabase')) {
				$HubObj->BackupDatabase(DB_USER, DB_PASS, DB_NAME, $HubObj->GetSetting('BackupFolder'));
			}
		
			if($HubObj->GetSetting('BackupXBMCFiles')) {
				if(is_dir($HubObj->GetSetting('XBMCDataFolder'))) {
					if(!is_file($HubObj->GetSetting('BackupFolder').'/xbmc-files-'.date('d-m-Y').'.zip')) {
						if($HubObj->ZipDirectory($HubObj->GetSetting('XBMCDataFolder'), $HubObj->GetSetting('BackupFolder').'/xbmc-files-'.date('d-m-Y').'.zip')) {
							$HubObj->AddLog(EVENT.'Backup', 'Success', 'Backed up XBMC files to "'.$HubObj->GetSetting('BackupFolder').'/xbmc-files-'.date('d-m-Y').'.zip"');
						}
					}
				}
			}
		
			if($HubObj->GetSetting('BackupXBMCDatabase')) {
				$XBMCDatabases = $XBMCObj->FindDatabaseNames();
				if(is_array($XBMCDatabases)) {
					foreach($XBMCDatabases AS $XBMCDatabase) {
						$HubObj->BackupDatabase(DB_USER, DB_PASS, $XBMCDatabase, $HubObj->GetSetting('BackupFolder'));
					}
				}
			}
		}
	}
}
*/

$Unlock = json_decode($Hub->Request('/hub/unlock'));

if(is_object($Unlock) && property_exists($Unlock, 'error')) {
	if($Unlock->error->code != 200) {
		AddLog(EVENT.'Hub', 'Failure', 'Failed to remove lock');
	}
}
?>