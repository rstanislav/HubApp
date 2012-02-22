<?php
session_start();
require_once './resources/config.php';
require_once './libraries/libraries.php';

$Page = (filter_has_var(INPUT_GET, 'page')) ? $_GET['page'] : '';

if($UserObj->LoggedIn) {
	$LastActivity = $HubObj->GetActivity(urldecode($_SERVER['QUERY_STRING']));
	$HubObj->LogActivity(urldecode($_SERVER['QUERY_STRING']));
}

$ErrorFreePages = array('Settings', 'ZonesDropdown', 'Drives', 'ZoneChange');
if($HubObj->Error && !in_array($Page, $ErrorFreePages)) {
	$HubObj->ShowError();
}
else {
	switch($Page) {
		case 'WishlistRefresh':
			$WishlistObj->WishlistRefresh();
		break;
		
		case 'XBMCPlayPause':
			if(filter_has_var(INPUT_GET, 'PlayerID')) {
				$XBMCObj->Connect();
				if(is_object($XBMCObj->XBMCRPC)) {
					$XBMCObj->PlayPause($_GET['PlayerID']);
				}
			}
		break;
		
		case 'Upload':
			$Settings = $HubObj->GetSettings();
			
			if(is_dir($Settings['SettingUTorrentWatchFolder']) && $UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentDownload')) {
				require_once './libraries/valums.file-uploader.php';
			
				$UploaderObj = new qqFileUploader();
				$UploadResult = $UploaderObj->handleUpload($Settings['SettingUTorrentWatchFolder'].'/');
				
				if(array_key_exists('success', $UploadResult)) {
					if($UploadResult['success']) {
						$HubObj->AddLog(EVENT.'uTorrent', 'Success', 'Uploaded "'.$_GET['qqfile'].'" to Watch Folder');
					}
				}
			}
		break;
	
		case 'Profile':
			require_once './pages/Profile.php';
		break;
		
		case 'ProfileSave':
			$UserObj->ProfileSave();
		break;
		
		case 'UserAdd':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'UserAdd')) {
				$UserObj->UserAdd();
			}
		break;
		
		case 'DeleteEpisode':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'SerieDeleteEpisode')) {
				if(filter_has_var(INPUT_GET, 'EpisodeID')) {
					$SeriesObj->DeleteEpisode($_GET['EpisodeID']);
				}
			}
		break;
		
		case 'XBMCLibraryUpdate':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'XBMCLibraryUpdate')) {
				$XBMCObj->Connect();
				if(is_object($XBMCObj->XBMCRPC)) {
					$XBMCObj->ScanForContent();
					
					$HubObj->AddLog(EVENT.'XBMC', 'Success', 'Updated XBMC Library');
				}
			}
		break;
		
		case 'XBMCLibraryClean':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'XBMCLibraryClean')) {
				$XBMCObj->Connect();
				if(is_object($XBMCObj->XBMCRPC)) {
					$XBMCObj->CleanLibrary();
					
					$HubObj->AddLog(EVENT.'XBMC', 'Success', 'Cleaned XBMC Library');
				}
			}
		break;
		
		case 'TorrentStartAll':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentStart')) {
				$UTorrentObj->Connect();
				
				if(is_object($UTorrentObj->UTorrentAPI)) {
					$UTorrentObj->TorrentStartAll();
				}
			}
		break;
		
		case 'TorrentPauseAll':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentPause')) {
				$UTorrentObj->Connect();
				
				if(is_object($UTorrentObj->UTorrentAPI)) {
					$UTorrentObj->TorrentPauseAll();
				}
			}
		break;
		
		case 'TorrentStopAll':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentStop')) {
				$UTorrentObj->Connect();
				
				if(is_object($UTorrentObj->UTorrentAPI)) {
					$UTorrentObj->TorrentStopAll();
				}
			}
		break;
		
		case 'TorrentRemove':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentDelete')) {
				$UTorrentObj->Connect();
				
				if(is_object($UTorrentObj->UTorrentAPI)) {
					if(filter_has_var(INPUT_GET, 'All')) {
						$UTorrentObj->TorrentRemoveAll();
					}
					else if(filter_has_var(INPUT_GET, 'Finished')) {
						$UTorrentObj->DeleteFinishedTorrents();
					}
				}
			}
		break;
			
		case 'FilePlay':
			$XBMCObj->Connect();
			$XBMCObj->PlayFile($_GET['File']);
		break;
		
		case 'Unlock':
			$HubObj->Unlock();
			$HubObj->AddLog(EVENT.'Hub', 'Success', 'User "'.$UserObj->User.'" forced Hub unlock');
		break;
		
		case 'LockStatus':
			$Settings = $HubObj->GetSettings();
			
			if($Settings['SettingHubKillSwitch']) {
				echo '<a><img src="images/icons/lock_break.png" /></a>';
			}
			else {
				if($HubObj->CheckLock()) {
					echo '<a><img src="images/icons/lock.png" /></a>';
				}
			}
		break;
		
		case 'TorrentSpeedSetting':
			$UTorrentObj->Connect();
			if(is_object($UTorrentObj->UTorrentAPI)) {
				$Settings = $HubObj->GetSettings();
				
				if($UTorrentObj->GetSetting('max_ul_rate') == $Settings['SettingUTorrentDefaultUpSpeed'] && $UTorrentObj->GetSetting('max_dl_rate') == $Settings['SettingUTorrentDefaultDownSpeed']) {
					echo '<a><img src="images/icons/turtle_dark.png" title="Enable uTorrent speed limiter ('.$Settings['SettingUTorrentDefinedDownSpeed'].'/'.$Settings['SettingUTorrentDefinedUpSpeed'].' KiB/s)" /></a>';
				}
				else if($UTorrentObj->GetSetting('max_ul_rate') == $Settings['SettingUTorrentDefinedUpSpeed'] && $UTorrentObj->GetSetting('max_dl_rate') == $Settings['SettingUTorrentDefinedDownSpeed']) {
					echo '<a><img src="images/icons/turtle_red.png" title="Disable uTorrent speed limiter ('.$Settings['SettingUTorrentDefaultDownSpeed'].'/'.$Settings['SettingUTorrentDefaultUpSpeed'].' KiB/s)" /></a>';
				}
				else {
					echo '<a><img src="images/icons/turtle_blue.png" title="Check your uTorrent speed settings!" /></a>';
				}
			}
		break;
		
		case 'TorrentSpeedSettingToggle':
			$UTorrentObj->Connect();
			if(is_object($UTorrentObj->UTorrentAPI)) {
				$Settings = $HubObj->GetSettings();
				
				if($UTorrentObj->GetSetting('max_ul_rate') == $Settings['SettingUTorrentDefaultUpSpeed']) {
					$UTorrentObj->SetSetting('max_ul_rate', $Settings['SettingUTorrentDefinedUpSpeed']);
				}
				else {
					$UTorrentObj->SetSetting('max_ul_rate', $Settings['SettingUTorrentDefaultUpSpeed']);
				}
			
				if($UTorrentObj->GetSetting('max_dl_rate') == $Settings['SettingUTorrentDefaultDownSpeed']) {
					$UTorrentObj->SetSetting('max_dl_rate', $Settings['SettingUTorrentDefinedDownSpeed']);
				}
				else {
					$UTorrentObj->SetSetting('max_dl_rate', $Settings['SettingUTorrentDefaultDownSpeed']);
				}
			}
		break;
		
		case 'DriveActive':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'DriveActive')) {
				if(filter_has_var(INPUT_GET, 'DriveID')) {
					$DrivesObj->SetActiveDrive($_GET['DriveID']);
				}
			}
			else {
				$_SESSION['Error'] = 'You are not allowed to set active drive';
			}
		break;
		
		case 'DriveAdd':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'DriveAdd')) {
				if(filter_has_var(INPUT_GET, 'DriveLetter')) {
					$DrivesObj->AddDrive($_GET['DriveLetter']);
				}
			}
			else {
				$_SESSION['Error'] = 'You are not allowed to add drives';
			}
		break;
		
		case 'DriveNetworkAdd':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'DriveAdd')) {
				$AddError = FALSE;
				foreach($_POST AS $PostKey => $PostValue) {
					if(!filter_has_var(INPUT_POST, $PostKey) || empty($PostValue)) {
						$AddError = TRUE;
					}
				}
				
				if(!$AddError) {
					$DrivesObj->AddDrive($_POST['DriveNetworkLetter'], '//'.$_POST['DriveNetworkComputer'].'/'.$_POST['DriveNetworkShare']);
				}
				else {
					echo 'You have to fill in all the fields';
				}
			}
			else {
				$_SESSION['Error'] = 'You are not allowed to add drives';
			}
		break;
		
		case 'DriveRemove':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'DriveRemove')) {
				if(filter_has_var(INPUT_GET, 'DriveID')) {
					$DrivesObj->RemoveDrive($_GET['DriveID']);
				}
			}
			else {
				$_SESSION['Error'] = 'You are not allowed to remove drives';
			}
		break;
		
		case 'ExtractFile':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ExtractFiles')) {
				if(filter_has_var(INPUT_GET, 'File') && filter_has_var(INPUT_GET, 'DriveID')) {
					$ExtractFileReturn = $ExtractFilesObj->ExtractFile(urldecode($_GET['File']), $_GET['DriveID']);
					echo $ExtractFileReturn;
				}
			}
			else {
				$_SESSION['Error'] = 'You are not allowed to extract files';
			}
		break;
		
		case 'MoveFile':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ExtractFiles')) {
				if(filter_has_var(INPUT_GET, 'File') && filter_has_var(INPUT_GET, 'DriveID')) {
					$MoveFileReturn = $ExtractFilesObj->MoveFile(urldecode($_GET['File']), $_GET['DriveID']);
					echo $MoveFileReturn;
				}
			}
			else {
				$_SESSION['Error'] = 'You are not allowed to extract files';
			}
		break;
		
		/*
		* Main
		*/
		case 'PastSchedule':
			include_once './pages/Default_PastSchedule.php';
		break;
		
		case 'FutureSchedule':
			include_once './pages/Default_FutureSchedule.php';
		break;
		
		case 'UnavailableSchedule':
			include_once './pages/Default_UnavailableSchedule.php';
		break;
		
		case 'DownloadMultiple':
			include_once './pages/Default_MultipleDownloads.php';
		break;
		
		case 'Users':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewUsers')) {
				include_once './pages/Users.php';
			
				$UserPage = ($_GET['UserPage'] != 'undefined') ? $_GET['UserPage'] : 'Users';
			
				include_once './pages/Users_'.$UserPage.'.php';
			}
			else {
				include_once './pages/NoAccess.php';
			}
		break;
		
		case 'Statistics':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewStatistics')) {
				include_once './pages/Statistics.php';
			}
			else {
				include_once './pages/NoAccess.php';
			}
		break;
		
		case 'GroupPermissions':
			$DeleteGroupPermissionsPrep = $HubObj->PDO->prepare('DELETE FROM UserGroupPermissions WHERE UserGroupKey = :UserGroupID');
			$DeleteGroupPermissionsPrep->execute(array(':UserGroupID' => $_POST['GroupID']));
			
			foreach($_POST['Permission'] AS $PermissionID => $CheckedValue) {
				$AddGroupPermissionPrep = $HubObj->PDO->prepare('INSERT INTO UserGroupPermissions (UserGroupKey, PermissionKey) VALUES (:UserGroupID, :PermissionID)');
				$AddGroupPermissionPrep->execute(array(':UserGroupID' => $_POST['GroupID'],
					                                   ':PermissionID' => $PermissionID));
			}
			
			header('Location: '.$_SERVER['HTTP_REFERER'].'#!/Users/Groups/');
		break;
		
		/*
		* Drives
		*/
		case 'Drives':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewDrives')) {
				include_once './pages/Drives.php';
			}
			else {
				include_once './pages/NoAccess.php';
			}
		break;
		
		/*
		* Series
		*/
		case 'Series':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewSeries')) {
				include_once './pages/Series.php';
			}
			else {
				include_once './pages/NoAccess.php';
			}
		break;
		
		case 'SerieAdd':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'SerieAdd')) {
				$SeriesObj->ConnectTheTVDB();
				$SeriesObj->AddSerie($_GET['TheTVDBID']);
				
				if(filter_has_var(INPUT_GET, 'WithEpisodes')) {
					if($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentDownload')) {
						$EpisodeTorrents = $SeriesObj->GetSerieEpisodeTorrents($_GET['TheTVDBID']);
					
						if(is_array($EpisodeTorrents)) {
							$UTorrentObj->Connect();
							$UTorrentObj->DownloadTorrents($EpisodeTorrents);
						}
					}
					else {
						$_SESSION['Error'] = 'You are not permitted to download torrents';
					}
				}
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to add series';
			}
		break;
		
		case 'SerieRefresh':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'SerieRefresh')) {
				if($SeriesObj->ConnectTheTVDB()) {
					$SeriesObj->RefreshSerie($_GET['SerieID']);
				}
				else {
					$HubObj->AddLog(EVENT.'Series', 'Failure', 'Unable to connect to TheTVDB.com');
				}
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to refresh series';
			}
		break;
		
		case 'SerieRefreshAll':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'SerieRefreshAll')) {
				$SeriesObj->ConnectTheTVDB();
				$SeriesObj->RefreshAllSeries();
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to refresh all series';
			}
		break;
		
		case 'EpisodesRebuild':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'SerieRebuild')) {
				$SeriesObj->RebuildEpisodes();
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to rebuild serie episodes';
			}
		break;
		
		case 'FoldersRebuild':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'SerieRebuildFolders')) {
				$SeriesObj->RebuildFolders();
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to rebuild serie folders';
			}
		break;
		
		case 'SerieSpelling':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'SerieAddSpelling')) {
				if(filter_has_var(INPUT_GET, 'SerieID') && filter_has_var(INPUT_GET, 'Spelling')) {
					$SeriesObj->AddSpelling($_GET['SerieID'], $_GET['Spelling']);
				}
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to add an alternate title to a serie';
			}
		break;
		
		case 'SerieDelete':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'SerieDelete')) {
				$SeriesObj->DeleteSerie($_GET['SerieID']);
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to delete a serie';
			}
		break;
		
		/*
		* Movies
		*/
		case 'Movies':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewMovies')) {
				include_once './pages/Movies.php';
			}
			else {
				include_once './pages/NoAccess.php';
			}
		break;
		
		/*
		* Wishlist
		*/
		case 'Wishlist':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewWishlist')) {
				include_once './pages/Wishlist.php';
			}
			else {
				include_once './pages/NoAccess.php';
			}
		break;
		
		/*
		* Unsorted Files
		*/
		case 'UnsortedFiles':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewUnsortedFiles')) {
				include_once './pages/UnsortedFiles.php';
			}
			else {
				include_once './pages/NoAccess.php';
			}
		break;
		
		/*
		* Extract Files
		*/
		case 'ExtractFiles':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewExtractFiles')) {
				include_once './pages/ExtractFiles.php';
			}
			else {
				include_once './pages/NoAccess.php';
			}
		break;
		
		/*
		* Hub Log
		*/
		case 'HubLog':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewHubLog')) {
				include_once './pages/HubLog.php';
			}
			else {
				include_once './pages/NoAccess.php';
			}
		break;
		
		/*
		* XBMC Control Panel
		*/
		case 'XBMCControlPanel':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewXBMCCP')) {
				include_once './pages/XBMCControlPanel.php';
			}
			else {
				include_once './pages/NoAccess.php';
			}
		break;
		
		/*
		* XBMC Zones
		*/
		case 'XBMCZones':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewXBMCZones')) {
				include_once './pages/XBMCZones.php';
			}
			else {
				include_once './pages/NoAccess.php';
			}
		break;
		
		case 'ZoneAdd':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ZoneAdd')) {
				$ZonesObj->ZoneAdd();
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to add zones';
			}
		break;
		
		case 'ZoneEdit':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ZoneEdit')) {
				$ZonesObj->ZoneEdit();
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to edit zones';
			}
		break;
		
		case 'ZoneDelete':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ZoneDelete')) {
				$ZonesObj->ZoneDelete();
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to delete a zone';
			}
		break;
		
		case 'ZoneChange':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ZoneSwitch')) {
				$ZonesObj->ZoneChange($_GET['Zone']);
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to switch zones';
			}
		break;
		
		case 'PermissionEdit':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'PermissionEdit')) {
				$UserObj->PermissionEdit();
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to edit permissions';
			}
		break;
		
		case 'PermissionAdd':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'PermissionAdd')) {
				$UserObj->PermissionAdd();
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to add permissions';
			}
		break;
		
		case 'SaveSettings':
			$HubObj->SaveSettings($_POST['SettingSection']);
		break;
		
		case 'WishlistAdd':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'WishlistAdd')) {
				$WishlistObj->WishlistAdd();
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to add to the wishlist';
			}
		break;
		
		case 'WishlistEdit':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'WishlistEdit')) {
				$WishlistObj->WishlistEdit();
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to edit wishlist items';
			}
		break;
		
		case 'WishlistDelete':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'WishlistDelete')) {
				$WishlistObj->WishlistDelete($_GET['WishlistID']);
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to delete wishlist items';
			}
		break;
		
		/*
		* XBMC Screenshots
		*/
		case 'XBMCScreenshots':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewXBMCScreenshots')) {
				include_once './pages/XBMCScreenshots.php';
			}
			else {
				include_once './pages/NoAccess.php';
			}
		break;
		
		/*
		* XBMC Log
		*/
		case 'XBMCLog':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewXBMCLog')) {
				include_once './pages/XBMCLog.php';
			}
			else {
				include_once './pages/NoAccess.php';
			}
		break;
		
		/*
		* UTorrent Control Panel
		*/
		case 'UTorrentControlPanel':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewUTorrentCP')) {
				include_once './pages/UTorrentControlPanel.php';
			}
			else {
				include_once './pages/NoAccess.php';
			}
		break;
		
		case 'UTorrentCP':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewUTorrentCP')) {
				include_once './pages/UTorrentControlPanel_Default.php';
			}
			else {
				include_once './pages/NoAccess.php';
			}
		break;
		
		case 'TorrentDownload':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentDownload')) {
				if(filter_has_var(INPUT_GET, 'TorrentID')) {
					if($RSSObj->TorrentDownload($_GET['TorrentID'])) {
						if(filter_has_var(INPUT_GET, 'EpisodeID')) {
							$TorrentEpisodePrep = $HubObj->PDO->prepare('UPDATE Episodes SET TorrentKey = :TorrentKey WHERE EpisodeID = :EpisodeID');
							$TorrentEpisodePrep->execute(array(':TorrentKey' => $_GET['TorrentID'],
							                                   ':EpisodeID'  => $_GET['EpisodeID']));
						}
					}
				}
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to download torrents';
			}
		break;
		
		case 'TorrentStart':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentStart')) {
				if(filter_has_var(INPUT_GET, 'TorrentHash')) {
					$UTorrentObj->Connect();
					$UTorrentObj->TorrentStart($_GET['TorrentHash']);
				}
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to start torrents';
			}
		break;
		
		case 'TorrentStop':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentStop')) {
				if(filter_has_var(INPUT_GET, 'TorrentHash')) {
					$UTorrentObj->Connect();
					$UTorrentObj->TorrentStop($_GET['TorrentHash']);
				}
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to stop torrents';
			}
		break;
		
		case 'TorrentPause':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentPause')) {
				if(filter_has_var(INPUT_GET, 'TorrentHash')) {
					$UTorrentObj->Connect();
					$UTorrentObj->TorrentPause($_GET['TorrentHash']);
				}
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to pause torrents';
			}
		break;
		
		case 'TorrentDelete':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentDelete')) {
				if(filter_has_var(INPUT_GET, 'TorrentHash')) {
					$UTorrentObj->Connect();
					$UTorrentObj->TorrentDelete($_GET['TorrentHash']);
				}
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to delete torrents';
			}
		break;
		
		case 'TorrentDeleteData':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentDeleteData')) {
				if(filter_has_var(INPUT_GET, 'TorrentHash')) {
					$UTorrentObj->Connect();
					$UTorrentObj->TorrentDeleteData($_GET['TorrentHash']);
				}
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to delete torrents with data';
			}
		break;
		
		/*
		* RSS
		*/
		case 'RSSCP':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewRSSCP')) {
				include_once './pages/RSS_Control_Panel.php';
			}
			else {
				include_once './pages/NoAccess.php';
			}
		break;
		
		case 'UnsortedFileRename':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'UnsortedFilesRename')) {
				if(filter_has_var(INPUT_POST, 'value')) {
					if(filter_has_var(INPUT_POST, 'FilePath')) {
						if(filter_has_var(INPUT_POST, 'DefaultFile')) {
							$UnsortedFilesObj->RenameFile($_POST['FilePath'], $_POST['DefaultFile'], $_POST['value']);
						}
					}
				}
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to edit unsorted files';
			}
		break;
		
		case 'UnsortedFileMove':
			print_r($_POST);
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'UnsortedFilesMove')) {
				if(filter_has_var(INPUT_POST, 'TVFolder')) {
					$NewFolder = $_POST['TVFolder'].'/';
				}
				else {
					$NewFolder = $_POST['ContentFolder'].'/';
				}
				
				$UnsortedFilesObj->MoveFile($_POST['UnsortedFilePath'].$_POST['UnsortedFile'], $NewFolder.$_POST['UnsortedFile']);
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to move unsorted files';
			}
		break;
		
		case 'UnsortedFileDelete':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'UnsortedFilesDelete')) {
				$UnsortedFilesObj->DeleteFile($_GET['File']);
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to delete unsorted files';
			}
		break;
		
		case 'RSS':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewRSSFeed')) {
				include_once './pages/RSS.php';
			}
			else {
				include_once './pages/NoAccess.php';
			}
		break;
		
		case 'DeleteUser':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'UserDelete')) {
				if(filter_has_var(INPUT_GET, 'UserID')) {
					$UserObj->UserDelete($_GET['UserID']);
				}
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to delete users';
			}
		break;
		
		case 'DeleteUserGroup':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'UserGroupDelete')) {
				if(filter_has_var(INPUT_GET, 'UserGroupID')) {
					$UserObj->UserGroupDelete($_GET['UserGroupID']);
				}
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to delete user groups';
			}
		break;
		
		case 'RSSFeedAdd':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'RSSFeedAdd')) {
				$RSSObj->RSSFeedAdd();
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to add RSS feeds';
			}
		break;
		
		case 'RSSFeedEdit':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'RSSFeedEdit')) {
				$RSSObj->RSSFeedEdit();
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to edit RSS feeds';
			}
		break;
		
		case 'RSSFeedDelete':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'RSSFeedDelete')) {
				$RSSObj->RSSFeedDelete($_GET['RSSID']);
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to delete RSS feeds';
			}
		break;
		
		case 'RSSCategories':
			setcookie('TorrentCategories', join(',', $_GET['TorrentCategories']), (time() + (3600 * 24 * 31)));
		break;
		
		case 'RSSUpdate':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'RSSUpdate')) {
				$RSSObj->Update();
			}
			else {
				$_SESSION['Error'] = 'You are not permitted to update RSS feeds';
			}
		break;
		
		/*
		* Hub General
		*/
		case 'Login':
			$User = (filter_has_var(INPUT_POST, 'HubUser')) ? $_POST['HubUser'] : '';
			$Pass = (filter_has_var(INPUT_POST, 'HubPass')) ? $_POST['HubPass'] : '';
			
			if($User && $Pass) {
				if(!$UserObj->Login($User, $Pass)) {
					$_SESSION['LoginError'] = 'Username and password did not match';
					
					header('Location: '.$_SERVER['HTTP_REFERER']);
				}
			}
			else if(!$User || !$Pass) {
				$_SESSION['LoginError'] = 'You have to enter both fields, ya dumbass...';
				
				header('Location: '.$_SERVER['HTTP_REFERER']);
			}
		break;
		
		case 'Logout':
			$UserObj->Logout();
			header('Location: '.$_SERVER['HTTP_REFERER']);
		break;
		
		case 'ForgotPassword':
			$User  = (filter_has_var(INPUT_POST, 'HubUser')) ? $_POST['HubUser'] : '';
			$EMail = (filter_has_var(INPUT_POST, 'HubEMail')) ? $_POST['HubEMail'] : '';
			
			if($User || $EMail) {
				$UserObj->ResetPassword($User, $EMail);
			}
			
			//header('Location: '.$_SERVER['HTTP_REFERER'].'#!/Password/');
		break;
		
		case 'Settings':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewSettings')) {
				include_once './pages/Settings.php';
				
				$SettingPage = ($_GET['SettingPage'] != 'undefined') ? $_GET['SettingPage'] : 'Hub';
				
				include_once './pages/Settings_'.$SettingPage.'.php';
			}
			else {
				include_once './pages/NoAccess.php';
			}
		break;
		
		case 'Search':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'Search')) {
				include_once './pages/Search.php';
			}
			else {
				include_once './pages/NoAccess.php';
			}
		break;
		
		case 'Help':
			include_once './pages/Help.php';
		break;
		
		case 'UserGroupEdit':
			if($UserObj->CheckPermission($UserObj->UserGroupID, 'UserGroupEdit')) {
				include_once './pages/Users_Groups_Edit.php';
			}
			else {
				include_once './pages/NoAccess.php';
			}	
		break;
		
		case 'Badge':
			$Badge = (filter_has_var(INPUT_GET, 'Badge')) ? $_GET['Badge'] : '';
			
			switch($Badge) {
				case 'Wishlist':
					$WishlistObj->GetBadge();
				break;
				
				case 'UTorrent':
					$UTorrentObj->Connect();
					$UTorrentObj->GetBadge();
				break;
				
				case 'RSS':
					$RSSObj->GetBadge($_GET['ID']);
				break;
				
				default:
				break;
			}
		break;
		
		default:
			include_once './pages/default.php';
		break;
	}
}
?>