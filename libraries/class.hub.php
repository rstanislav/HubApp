<?php
require_once APP_PATH.'/libraries/api.thetvdb.php';
require_once APP_PATH.'/libraries/api.boxcar.php';

class Hub {
	const HubVersion   = '2.4.5.3';
	const MinDBVersion = '2.0.3';
	
	public $PDO;
	
	public $TheTVDBAPI;
	
	public $Error;
	
	public $Settings;
	
	public $CurrentZone;
	
	public $ActiveDrive;
	
	public $User;
	
	public $BoxcarAPI;
	
	function __construct() {
		$ReqExts = array('gd', 'pdo', 'curl', 'SimpleXML', 'mysql', 'json', 'pdo_mysql');
		$ExtError = FALSE;
		foreach($ReqExts AS $ReqExt) {
			if(!extension_loaded($ReqExt)) {
				echo 'Required extension: "'.$ReqExt.'" is not loaded.<br />';
				$ExtError = TRUE;
			}
		}
		
		if($ExtError) { die('Modify your php.ini to include the required extensions'); }
		
		try {
		    $this->PDO = new PDO(DB_TYPE.':host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
		    $this->PDO->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		    $this->PDO->setAttribute(PDO::ATTR_CASE,               PDO::CASE_NATURAL);
		    $this->PDO->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
		    $this->PDO->setAttribute(PDO::ATTR_ORACLE_NULLS,       PDO::NULL_EMPTY_STRING);
		}
		catch(PDOException $e) {
			die('Could not connect to database: '.$e->getMessage());
		}
		
		User::CheckStatus();
		
		$this->BoxcarAPI = new BoxcarAPI();
	}
	
	function CheckHub() {
		self::GetSettings();
		Drives::GetActiveDrive();
		Zones::GetCurrentZone();
		RSS::CheckTLRSS();
	}
	
	function CheckForDBUpgrade() {
		$DB = $this->PDO->query('SELECT Value AS CurrentDBVersion FROM Hub WHERE Setting = "CurrentDBVersion"')->fetch();
		
		if(str_replace('.', '', $DB['CurrentDBVersion']) < str_replace('.', '', self::MinDBVersion)) {
			foreach(glob('upgrade/db-*.php') AS $File) {
				$NewDBVersion = str_replace('.php', '', str_replace('upgrade/db-', '', $File));
				
				if(str_replace('.', '', $NewDBVersion) > str_replace('.', '', $DB['CurrentDBVersion'])) {
					$sql = '';
			    	include_once $File;
			    	
			    	$IsUpgraded = FALSE;
			    	if(is_array($sql)) {
			    		foreach($sql AS $SQLUpgrade) {
			    			$UpgradePrep = $this->PDO->prepare($SQLUpgrade);
			    			$UpgradePrep->execute();
			    			
			    			$IsUpgraded = TRUE;
			    		}
			    	}
			    	else if(is_string($sql)) {
			    		$UpgradePrep = $this->PDO->prepare($sql);
			    		$UpgradePrep->execute();
			    		
			    		$IsUpgraded = TRUE;
			    	}
			    	
			    	if($IsUpgraded) {
			    		$DBUpgradePrep = $this->PDO->prepare('UPDATE Hub SET Value = :NewVersion WHERE Setting = "CurrentDBVersion"');
			    		$DBUpgradePrep->execute(array(':NewVersion' => $NewDBVersion));
			    		
			    		Hub::AddLog(EVENT.'Database', 'Success', 'Upgraded database to "'.$NewDBVersion.'"');
			    	}
			    }
			    else {
			    	unlink($File);
			    }
			}
		}
	}
	
	function ShowError() {
		echo '
		<div id="error" style="">
		 <img src="images/alerts/confirm.png" />
		 <div class="error-head">Error!</div>
		  
		  '.implode('<br />', $this->Error).'<br /><br />
		  
		  <a id="settingsbutton" class="button regular"><span class="inner"><span class="label" style="min-width:50px;" nowrap="">Settings</span></span></a>
		 </div>
		</div>'."\n";
	}
	
	function GetLogs() {
		$LogPrep = $this->PDO->prepare('SELECT * FROM Log ORDER BY LogID DESC LIMIT 75');
		$LogPrep->execute();
		
		if($LogPrep->rowCount()) {
			return $LogPrep->fetchAll();
		}
		else {
			return FALSE;
		}
	}
	
	function NotifyUsers($NotificationAction, $Category, $Text) {
		$UserNotificationPrep = $this->PDO->prepare('SELECT UserNotifications.UserKey, User.UserEMail, Notifications.NotificationID FROM UserNotifications, User, Notifications WHERE User.UserID = UserNotifications.UserKey AND Notifications.NotificationAction = :Action AND User.UserEMail != "" AND UserNotifications.NotificationKey = NotificationID');
		$UserNotificationPrep->execute(array(':Action' => $NotificationAction));
		
		if($UserNotificationPrep->rowCount()) {
			foreach($UserNotificationPrep->fetchAll() AS $Notification) {
				$this->BoxcarAPI->Notify($Notification['UserEMail'], $Category, $Text);
			}
		}
		else {
			return FALSE;
		}
	}
	
	function RecursiveDirSearch($Directory, $Extensions = null) {
		$Iterator = new IgnorantRecursiveDirectoryIterator($Directory);
		$Extensions = (!is_array($Extensions)) ? array('mp4','mkv','avi','rar') : $Extensions;
		
		$Files = array();
		foreach(new RecursiveIteratorIterator($Iterator, RecursiveIteratorIterator::SELF_FIRST) AS $Object) {
			if($Object->isFile()) {
				$File = str_replace('\\', '/', $Object->__toString());
				$FileInfo = pathinfo($File);
			
				if(array_key_exists('extension', $FileInfo) && in_array($FileInfo['extension'], $Extensions)) {
					$Files[] = $File;
				}
			}
		}
		
		return $Files;
	}
	
	function AddLog($LogEvent, $LogType, $LogText, $LogError = FALSE, $LogAction = '') {
		$LogError = (is_array($LogError)) ? implode("\n", $LogError) : $LogError;
		
		$LogPrep = $this->PDO->prepare('INSERT INTO Log (LogID, LogDate, LogEvent, LogType, LogError, LogText, LogAction) VALUES (NULL, :LogDate, :LogEvent, :LogType, :LogError, :LogText, :LogAction)');
		$LogPrep->execute(array(':LogDate'   => time(),
		                        ':LogEvent'  => $LogEvent,
		                        ':LogType'   => $LogType,
		                        ':LogError'  => $LogError,
		                        ':LogText'   => $LogText,
		                        ':LogAction' => $LogAction));
	}
	
	function BytesToHuman($Bytes) {
		$Types = array('B', 'KB', 'MB', 'GB', 'TB');
		
		for($i = 0; $Bytes >= 1024 && $i < (count($Types) - 1); $Bytes /= 1024, $i++);
		
		return(round($Bytes, 2).' '.$Types[$i]);
	}
	
	function LogActivity($Page) {
		$URL = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		
		if($this->GetActivity($Page)) {
			$ActivityPrep = $this->PDO->prepare('UPDATE Activity SET ActivityDate = :ActivityDate WHERE ActivityUser = :ActivityUser AND ActivityURL = :ActivityURL');
			$ActivityPrep->execute(array(':ActivityDate' => time(),
			                             ':ActivityUser' => $this->User,
			                             ':ActivityURL'  => $Page));
		}
		else {
			$ActivityPrep = $this->PDO->prepare('INSERT INTO Activity (ActivityID, ActivityDate, ActivityUser, ActivityURL) VALUES (:ActivityID, :ActivityDate, :ActivityUser, :ActivityURL)');
			$ActivityPrep->execute(array(':ActivityID'   => NULL,
		                             	 ':ActivityDate' => time(),
		                             	 ':ActivityUser' => $this->User,
		                             	 ':ActivityURL'  => $Page));
		}
	}
	
	function GetActivity($Page) {
		$URL = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		
		$ActivityPrep = $this->PDO->prepare('SELECT ActivityDate FROM Activity WHERE ActivityUser = :ActivityUser AND ActivityURL = :ActivityURL');
		$ActivityPrep->execute(array(':ActivityUser' => $this->User,
		                             ':ActivityURL'  => $Page));
		                             
		if($ActivityPrep->rowCount()) {
			$Activity = $ActivityPrep->fetch();
			
			return $Activity['ActivityDate'];
		}
		else {
			return 0;
		}
	}
	
	function Lock() {
		$LockPrep = $this->PDO->prepare('UPDATE Hub SET Value = :Time WHERE Setting = "IsLocked"');
		$LockPrep->execute(array(':Time' => time()));
	}
	
	function CheckLock() {
		$Lock = $this->PDO->query('SELECT Value AS IsLocked FROM Hub WHERE Setting = "IsLocked"')->fetch();
		
		if($Lock['IsLocked'] > strtotime('-4 hours')) {
			return TRUE;
		}
		else if($Lock['IsLocked'] != 0 && $Lock['IsLocked'] < strtotime('-4 hours')) {
			Hub::Unlock();
			Hub::AddLog(EVENT.'Hub', 'Success', 'Lock was removed due to 4 hour timeout');
			
			return FALSE;
		}
		else {
			return FALSE;
		}
	}
	
	function Unlock() {
		$LockPrep = $this->PDO->prepare('UPDATE Hub SET Value = 0 WHERE Setting = "IsLocked"');
		$LockPrep->execute();
	}
	
	function SaveSettings($Section = 'Hub') {
		switch($Section) {
			case 'Hub':
				$HubBackup     = (isset($_POST['SettingHubBackup']))     ? 1 : 0;
				$HubKillSwitch = (isset($_POST['SettingHubKillSwitch'])) ? 1 : 0;
				
				$EditSettingsPrep = $this->PDO->prepare('UPDATE Settings SET SettingHubLocalHostname = :LocalHostname, SettingHubLocalIP = :LocalIP, SettingHubMinimumActiveDiskFreeSpaceInGB = :MinActiveDiskFreeSpaceInGB, SettingHubMinimumDownloadQuality = :MinDownloadQuality, SettingHubMaximumDownloadQuality = :MaxDownloadQuality, SettingHubBackup = :HubBackup, SettingHubTheTVDBAPIKey = :TheTVDBAPIKey, SettingHubKillSwitch = :HubKillSwitch, SettingHubSearchURITVSeries = :SearchURITVSeries, SettingHubSearchURIMovies = :SearchURIMovies');
				$EditSettingsPrep->execute(array(':LocalHostname'              => $_POST['SettingHubLocalHostname'],
				                                 ':LocalIP'                    => implode('.', $_POST['SettingHubLocalIP']),
				                                 ':MinActiveDiskFreeSpaceInGB' => $_POST['SettingHubMinimumActiveDiskFreeSpaceInGB'],
				                                 ':MinDownloadQuality'         => $_POST['SettingHubMinimumDownloadQuality'],
				                                 ':MaxDownloadQuality'         => $_POST['SettingHubMaximumDownloadQuality'],
				                                 ':HubBackup'                  => $HubBackup,
				                                 ':TheTVDBAPIKey'              => $_POST['SettingHubTheTVDBAPIKey'],
				                                 ':HubKillSwitch'              => $HubKillSwitch,
				                                 ':SearchURITVSeries'          => $_POST['SettingHubSearchURITVSeries'],
				                                 ':SearchURIMovies'            => $_POST['SettingHubSearchURIMovies']));
			break;
			
			case 'Notifications':
				$UserInfo = User::GetUser($_COOKIE['HubUser']);
				
				$ClearNotificationsPrep = $this->PDO->prepare('DELETE FROM UserNotifications WHERE UserKey = :UserID');
				$ClearNotificationsPrep->execute(array(':UserID' => $UserInfo['UserID']));
				
				Hub::d($_POST);
				
				if(is_array($_POST['Notification'])) {
					foreach($_POST['Notification'] AS $NotificationID => $Notification) {
						$AddNotificationPrep = $this->PDO->prepare('INSERT INTO UserNotifications (UserKey, NotificationKey) VALUES (:UserID, :NotificationID)');
						$AddNotificationPrep->execute(array(':UserID'         => $UserInfo['UserID'],
						                                    ':NotificationID' => $NotificationID));
					}
				}
			break;
			
			case 'XBMC':
				$EditSettingsPrep = $this->PDO->prepare('UPDATE Settings SET SettingXBMCLogFile = :XBMCLogFile, SettingXBMCSourcesFile = :XBMCSourcesFile, SettingXBMCRSSFile = :XBMCRSSFile, SettingXBMCDatabaseFolder = :XBMCDatabaseFolder');
				$EditSettingsPrep->execute(array(':XBMCLogFile'        => $_POST['SettingXBMCLogFile'],
				                                 ':XBMCSourcesFile'    => $_POST['SettingXBMCSourcesFile'],
				                                 ':XBMCRSSFile'        => $_POST['SettingXBMCRSSFile'],
				                                 ':XBMCDatabaseFolder' => $_POST['SettingXBMCDatabaseFolder']));
			break;
			
			case 'UTorrent':
				$EditSettingsPrep = $this->PDO->prepare('UPDATE Settings SET SettingUTorrentHostname = :Hostname, SettingUTorrentPort = :Port, SettingUTorrentUsername = :Username, SettingUTorrentPassword = :Password, SettingUTorrentWatchFolder = :WatchFolder, SettingUTorrentDefaultUpSpeed = :DefaultUpSpeed, SettingUTorrentDefaultDownSpeed = :DefaultDownSpeed, SettingUTorrentDefinedUpSpeed = :DefinedUpSpeed, SettingUTorrentDefinedDownSpeed = :DefinedDownSpeed');
				$EditSettingsPrep->execute(array(':Hostname'         => implode('.', $_POST['SettingUTorrentHostname']),
				                                 ':Port'             => $_POST['SettingUTorrentPort'],
				                                 ':Username'         => $_POST['SettingUTorrentUsername'],
				                                 ':Password'         => $_POST['SettingUTorrentPassword'],
				                                 ':WatchFolder'      => $_POST['SettingUTorrentWatchFolder'],
				                                 ':DefaultUpSpeed'   => $_POST['SettingUTorrentDefaultUpSpeed'],
				                                 ':DefaultDownSpeed' => $_POST['SettingUTorrentDefaultDownSpeed'],
				                                 ':DefinedUpSpeed'   => $_POST['SettingUTorrentDefinedUpSpeed'],
				                                 ':DefinedDownSpeed' => $_POST['SettingUTorrentDefinedDownSpeed']));
			break;
		}
	}
	
	function ConvertSeconds($Seconds, $TimeFormat = TRUE) {
		$CSeconds   = ($Seconds % 60);
		$Remaining  = intval($Seconds / 60);
		$CMinutes   = ($Remaining % 60);
		$Remaining  = intval($Remaining / 60);
		$CHours     = ($Remaining % 24);
		$CDays      = intval($Remaining / 24);
	
		if($TimeFormat) {
			return sprintf("%02d:%02d:%02d:%02d", $CDays, $CHours, $CMinutes, $CSeconds);
		}
		else {
			if($CDays) {
				return sprintf("%01dd %02dh %02dm ", $CDays, $CHours, $CMinutes);
			}
			else if($CHours) {
				return sprintf("%02dh %02dm ", $CHours, $CMinutes);
			}
			else {
				return sprintf("%02dm ", $CMinutes);
			}
		}
	}
	
	function ConcatFilePath($Path) {
		if(strstr($Path, 'stack')) {
			$Path = str_replace('stack://', '', $Path);
			
			$FileArr = explode(',', $Path);
			
			$ConcatFileArr = array();
			foreach($FileArr AS $File) {
				$ConcatFileArr[] = trim(Hub::ConcatFilePath($File));
			}
			
			return $ConcatFileArr;
		}
		else {
			$Path = str_replace('smb:', '', $Path);
			$Path = str_replace('\\', '/', $Path);
			$First = strpos($Path, 'Media');
			$Last = strrpos($Path, '/');
		
			$First = substr($Path, 0, $First);
			$Last = substr($Path, $Last, strlen($Path));
		
			return $First.' … '.$Last;
		}
	}
	
	function GetMySQLDumpLocation() {
		$Locations = shell_exec('where /r c:\\ mysqldump.exe');
		$Locations = explode("\n", trim($Locations));
		
		if(sizeof($Locations) > 1) {
			$LatestVersion  = 0;
			$LatestVersionLocation = '';
			foreach($Locations AS $Location) {
				preg_match('/[0-9]+\.[0-9]+\.[0-9]+/i', $Location, $Matches);
				
				if(is_array($Matches)) {
					foreach($Matches AS $Match) {
						if(str_replace('.', '', $Match) > $LatestVersion) {
							$LatestVersion  = $Match;
							$LatestVersionLocation = $Location;
						}
					}
				}
			}
			
			if($LatestVersionLocation != '' && is_file($LatestVersionLocation)) {
				return $LatestVersionLocation;
			}
			else {
				// echo 'Unable to determine latest version or "'.$LatestVersionLocation.'" does not exist';
				
				return FALSE;
			}
		}
		else if(sizeof($Locations) == 1) {
			if(is_file($Locations[0])) {
				return $Locations[0];
			}
			else {
				// echo '"'.$Locations[0].'" does not exist';
				
				return FALSE;
			}
		}
		else {
			// echo 'Not found';
			
			return FALSE;
		}
	}
	
	function BackupDatabase($User = '', $Pass = '', $Database, $BackupLocation = '') {
		if(empty($BackupLocation)) {
			$BackupLocation = Hub::GetSetting('BackupFolder');
		}
		
		$BackupLocation = str_replace('\\', '/', $BackupLocation);
		
		if(!is_dir($BackupLocation)) {
			return FALSE;
		}
		
		$BackupFile  = $Database.'-database-'.date('d-m-Y').'.sql';
		
		if(is_file($BackupLocation.'/'.$BackupFile) || is_file($BackupLocation.'/'.$BackupFile.'.gz')) {
			return FALSE;
		}
		
		$UserStr = (empty($User)) ? '' : ' -u '.$User;
		$PassStr = (empty($Pass)) ? '' : ' -p '.$Pass;
		
		$MySQLDump = Hub::GetMySQLDumpLocation();
		if($MySQLDump) {
			$CmdResponse = shell_exec($MySQLDump.$UserStr.$PassStr.' '.$Database.' > '.$BackupLocation.'/'.$BackupFile);
			
			if(!trim($CmdResponse)) {
				$RegularHandle    = fopen($BackupLocation.'/'.$BackupFile, 'rb');
				$CompressedHandle = fopen($BackupLocation.'/'.$BackupFile.'.gz', 'w');
				
				$CompressError = FALSE;
				while(!feof($RegularHandle)) {
					$CompressedData = gzencode(fread($RegularHandle, 8192), 9);
					if(!fwrite($CompressedHandle, $CompressedData)) {
						$CompressError = TRUE;
						
						break;
					}
				}
				
				fclose($RegularHandle);
				fclose($CompressedHandle);
				
				if($CompressError) {
					unlink($BackupLocation.'/'.$BackupFile.'.gz');
				}
				else {
					unlink($BackupLocation.'/'.$BackupFile);
					$BackupFile = $BackupFile.'.gz';
				}
				
				Hub::AddLog(EVENT.'Backup', 'Success', 'Backed up "'.$Database.'" database to "'.$BackupLocation.'/'.$BackupFile.'"');
				
				return TRUE;
			}
			else {
				Hub::AddLog(EVENT.'Backup', 'Failure', 'Failed to backup "'.$Database.'" database to "'.$BackupLocation.'/'.$BackupFile.'"');
				
				return FALSE;
			}
		}
		else {
			return FALSE;
		}
	}
	
	function ZipDirectory($Directory, $ZipFile) {
	    if(!extension_loaded('zip') || !file_exists($Directory)) {
	        return FALSE;
	    }
	
	    $ZipObj = new ZipArchive();
	    if(!$ZipObj->open($ZipFile, ZIPARCHIVE::CREATE)) {
	        return FALSE;
	    }
	
	    $Directory = str_replace('\\', '/', realpath($Directory));
		if(is_dir($Directory)) {
	        $Files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($Directory), RecursiveIteratorIterator::SELF_FIRST);
			$FileCount = 0;
			foreach($Files AS $File) {
	            $File = str_replace('\\', '/', realpath($File));
	
	            if(is_dir($File)) {
	                $ZipObj->addEmptyDir(str_replace($Directory.'/', '', $File.'/'));
	            }
	            else if(is_file($File)) {
	            	$ZipObj->addFile($File, str_replace($Directory.'/', '', $File));
	            }
	            
	            if($FileCount++ == 500) { 
	            	$ZipObj->close(); 
	                if($ZipObj = new ZipArchive()) { 
	                	$ZipObj->open($ZipFile); 
	                    $FileCount = 0; 
	                } 
	        	}
	        }
	    }
	    else if(is_file($Directory)) {
	        $ZipObj->addFile($Directory, str_replace($Directory.'/', '', $File));
	    }
	
	    return $ZipObj->close();
	}
	
	function GetSetting($Setting) {
		$SettingsPrep = $this->PDO->prepare('SELECT * FROM Hub WHERE Setting = :Setting');
		$SettingsPrep->execute(array(':Setting' => $Setting));
		
		if($SettingsPrep->rowCount()) {
			$this->Settings = $SettingsPrep->fetch();
		}
		
		return $this->Settings;
	}
	
	function GetRandomID() {
		return base_convert(rand(10e16, 10e20), 10, 36);
	}
	
	function d($Arr) {
		echo '<pre>'; print_r($Arr); echo '</pre>';
	} 

	function __destruct() {
		$this->PDO = null;
	}
}

class IgnorantRecursiveDirectoryIterator extends RecursiveDirectoryIterator {
    function getChildren() {
        try {
            return parent::getChildren();
        }
        catch(UnexpectedValueException $e) {
            return new RecursiveArrayIterator(array());
        }
    }
}

class HubDirectoryIterator extends DirectoryIterator {
	function getSize() {
		return ExtractFiles::GetFileSize($this->current()->getPath().'/'.$this->current()->getBasename());
	}
}
?>