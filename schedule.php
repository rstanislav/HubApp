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

if($HubObj->GetSetting('KillSwitch') || $HubObj->CheckLock()) {
	die();
}
else {
	$HubObj->Lock();
}

$UTorrentObj->Connect();
$SeriesObj->ConnectTheTVDB();
$XBMCObj->Connect('default');

// Check for existing active drive and that all required folders are present
$DrivesObj->CheckActiveDrive();

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
	if(is_object($XBMCObj->XBMCRPC)) {
		$ActivePlayer = $XBMCObj->MakeRequest('Player', 'GetActivePlayers');
	
		if(!sizeof($ActivePlayer)) {
			$XBMCObj->ScanForContent();
			
			$HubObj->AddLog(EVENT.'XBMC', 'Success', 'Updated XBMC Library');
		}
	}
}

if(is_object($XBMCObj->XBMCRPC)) {
	// Cache movie covers locally
	$XBMCObj->CacheCovers();
}

$FolderRebuild   = $HubObj->GetSetting('LastFolderRebuild');
$SerieRefresh    = $HubObj->GetSetting('LastSerieRefresh');
$SerieRebuild    = $HubObj->GetSetting('LastSerieRebuild');
$WishlistUpdate  = $HubObj->GetSetting('LastWishlistUpdate');
$MoviesUpdate    = $HubObj->GetSetting('LastMoviesUpdate');
$WishlistRefresh = $HubObj->GetSetting('LastWishlistRefresh');
$Backup          = $HubObj->GetSetting('LastBackup');

$LatestUpdate = min($FolderRebuild, $SerieRefresh, $SerieRebuild, $WishlistUpdate, $MoviesUpdate, $WishlistRefresh, $Backup);
if((date('G') >= 4 && date('G') <= 6) || (time() - $LatestUpdate) >= (60 * 60 * 24 * 2)) {
	if(date('dmy', $FolderRebuild) != date('dmy')) {
		$SeriesObj->RebuildFolders();
	}

	if(date('dmy', $SerieRefresh) != date('dmy')) {
		if(is_object($SeriesObj->TheTVDBAPI)) {
			$SeriesObj->RefreshAllSeries();
		}
	}

	if(date('dmy', $SerieRebuild) != date('dmy')) {
		if(is_object($SeriesObj->TheTVDBAPI)) {
			$SeriesObj->RebuildEpisodes();
		}
	}
	
	if($HubObj->GetSetting('ShareMovies')) {
		if(date('dmy', $MoviesUpdate) != date('dmy')) {
			if(is_object($XBMCObj->XBMCRPC)) {
				$Movies = $XBMCObj->GetMovies();
				
				if(is_array($Movies)) {
					$ShareObj->UpdateMovies($Movies);
				}
			}
		}
	}
	
	if($HubObj->GetSetting('ShareWishlist')) {
		if(date('dmy', $WishlistUpdate) != date('dmy')) {
			$ShareObj->UpdateWishlist();
		}
	}
	
	if(date('dmy', $WishlistRefresh) != date('dmy')) {
		$WishlistObj->WishlistRefresh();
	}
	
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

$HubObj->Unlock();
?>