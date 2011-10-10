<?php
class Drives extends Hub {
	function CheckActiveDrive() {
		$Settings = Hub::GetSettings();
		
		self::GetActiveDrive();
		UTorrent::Connect();
		
		if($this->ActiveDrive) {
			$Drive = self::GetDriveByID($this->ActiveDrive);
			
			if(is_array($Drive)) {
				$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveRoot'] : $Drive['DriveLetter'];
				
				$FreeSpace  = self::GetFreeSpace($DriveRoot,  TRUE);
				$TotalSpace = self::GetTotalSpace($DriveRoot, TRUE);
				if(self::GetFreeSpacePercentage($FreeSpace, $TotalSpace) <= $Settings['SettingHubMinimumActiveDiskPercentage']) {
					self::DetermineNewActiveDrive();
				}
				else {
					if(is_dir($DriveRoot.'/Downloads')) {
						UTorrent::SetSetting('dir_active_download', $Drive['DriveLetter'].'/Downloads');
					}
					else {
						die(Hub::AddLog(EVENT.'uTorrent', 'Failure', 'Incomplete Downloads folder: "'.$Drive['DriveLetter'].'/Downloads" does not exist'));
					}
					
					if(is_dir($DriveRoot.'/Completed')) {
						UTorrent::SetSetting('dir_completed_download', $Drive['DriveLetter'].'/Completed');
					}
					else {
						die(Hub::AddLog(EVENT.'uTorrent', 'Failure', 'Completed Downloads folder: "'.$Drive['DriveLetter'].'/Completed" does not exist'));
					}
					
					if($Settings['SettingUTorrentWatchFolder'] && is_dir($Settings['SettingUTorrentWatchFolder'])) {
						UTorrent::SetSetting('dir_autoload', $Settings['SettingUTorrentWatchFolder']);
					}
					else {
						die(Hub::AddLog(EVENT.'uTorrent', 'Failure', 'Watch folder: "'.$Settings['SettingUTorrentWatchFolder'].'" does not exist'));
					}
				}
			}
			else {
				self::DetermineNewActiveDrive();
			}
		}
		else {
			die(Hub::AddLog(EVENT.'Drives', 'Failure', 'No active drive has been set'));
		}
	}
	
	function DetermineNewActiveDrive() {
		$Settings = Hub::GetSettings();
		$Drives = Drives::GetDrivesFromDB();
		
		if(is_array($Drives)) {
			foreach($Drives AS $Drive) {
				$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveRoot'] : $Drive['DriveLetter'];
				
				$FreeSpace  = self::GetFreeSpace($DriveRoot,  TRUE);
				$TotalSpace = self::GetTotalSpace($DriveRoot, TRUE);
				
				if(self::GetFreeSpacePercentage($FreeSpace, $TotalSpace) > $Settings['SettingHubMinimumActiveDiskPercentage']) {
					self::SetActiveDrive($Drive['DriveID']);
					
					break;
				}
			}
		}
	}
	
	function GetActiveDrive() {
		$ActiveDrive = $this->PDO->query('SELECT * FROM Drives WHERE DriveActive = 1')->fetch();
		
		if(sizeof($ActiveDrive)) {
			$this->ActiveDrive = $ActiveDrive['DriveID'];
		}
		else {
			$this->Error[] = 'No active drive is set';
		}
	}
	
	function GetUnusedDriveLetters() {
		$Drives = array();
		for($ASCII = 68; $ASCII <= 90; $ASCII++) {
			$DriveLetter = chr($ASCII).':';
		
			if(!is_dir($DriveLetter) && !self::GetDriveByLetter($DriveLetter)) {
				$Drives[] = $DriveLetter;
			}
		}
		
		return $Drives;
	}
	
	function GetDrives() {
		$Drives = array();
		for($ASCII = 68; $ASCII <= 90; $ASCII++) {
			$DriveLetter = chr($ASCII).':';
		
			if(is_dir($DriveLetter)) {
				$SpaceFree  = @disk_free_space($DriveLetter);
				$SpaceTotal = @disk_total_space($DriveLetter);
				
				if($SpaceFree != 0 && $SpaceTotal > ((1024 * 1024 * 1024) * 100)) {
					$Drives[] = $DriveLetter;
				}
			}
		}
		
		return $Drives;
	}
	
	function GetDriveByLetter($DriveLetter) {
		$DrivePrep = $this->PDO->prepare('SELECT * FROM Drives WHERE DriveLetter = :Letter');
		$DrivePrep->execute(array(':Letter' => $DriveLetter));
		
		if($DrivePrep->rowCount()) {
			return $DrivePrep->fetch();
		}
		else {
			return FALSE;
		}
	}
	
	function GetDrivesFromDB() {
		$DrivePrep = $this->PDO->prepare('SELECT * FROM Drives ORDER BY DriveDate');
		$DrivePrep->execute();
		
		if($DrivePrep->rowCount()) {
			return $DrivePrep->fetchAll();
		}
		else {
			return FALSE;
		}
	}
	
	function GetDrivesNetwork() {
		$DrivePrep = $this->PDO->prepare('SELECT * FROM Drives WHERE DriveNetwork = 1 ORDER BY DriveDate');
		$DrivePrep->execute();
		
		if($DrivePrep->rowCount()) {
			return $DrivePrep->fetchAll();
		}
		else {
			return FALSE;
		}
	}
	
	function RecursiveDirRemove($Directory) { 
		if(is_dir($Directory)) { 
			$Objects = scandir($Directory); 
			
			foreach($Objects AS $Object) { 
				if($Object != '.' && $Object != '..') { 
	        		if(filetype($Directory.'/'.$Object) == 'dir') {
	        			self::RecursiveDirRemove($Directory.'/'.$Object); 
	        		}
	        		else {
	        			unlink($Directory.'/'.$Object);
	        		}
	       		} 
	     	} 
	     
	     	reset($Objects);
	     	
	     	return rmdir($Directory); 
	   	}
	}
	
	function GetDriveByID($DriveID) {
		$DrivePrep = $this->PDO->prepare('SELECT * FROM Drives WHERE DriveID = :ID');
		$DrivePrep->execute(array(':ID' => $DriveID));
		
		if($DrivePrep->rowCount()) {
			return $DrivePrep->fetch();
		}
		else {
			return FALSE;
		}
	}
	
	function GetFreeSpace($DriveLetter, $AsBytes = FALSE) {
		if($AsBytes) {
			return @disk_free_space($DriveLetter);
		}
		else {
			return $this->BytesToHuman(@disk_free_space($DriveLetter));
		}
	}
	
	function GetTotalSpace($DriveLetter, $AsBytes = FALSE) {
		if($AsBytes) {
			return @disk_total_space($DriveLetter);
		}
		else {
			return $this->BytesToHuman(@disk_total_space($DriveLetter));
		}
	}
	
	function GetFreeSpacePercentage($FreeSpace, $TotalSpace) {
		$PercentageFree = $FreeSpace ? round($FreeSpace / $TotalSpace, 2) * 100 : 0;
		
		return $PercentageFree;
	}
	
	function SetActiveDrive($DriveID) {
		$UpdateDrivePrep = $this->PDO->prepare('UPDATE Drives SET DriveActive = 0');
		$UpdateDrivePrep->execute();
		
		$UpdateDrivePrep = $this->PDO->prepare('UPDATE Drives SET DriveActive = 1 WHERE DriveID = :ID');
		$UpdateDrivePrep->execute(array(':ID' => $DriveID));
		
		$DrivePrep = $this->PDO->prepare('SELECT * FROM Drives WHERE DriveActive = 1');
		$DrivePrep->execute();
		
		if($DrivePrep->rowCount()) {
			$Drive = $DrivePrep->fetch();
			UTorrent::Connect();
			
			$DriveRoot     = ($Drive['DriveNetwork']) ? $Drive['DriveRoot']                                : $Drive['DriveLetter'];
			$DriveRootText = ($Drive['DriveNetwork']) ? $Drive['DriveRoot'].' ('.$Drive['DriveLetter'].')' : $Drive['DriveLetter'];
			
			$this->ActiveDrive = $Drive['DriveID'];
			Hub::AddLog(EVENT.'File System', 'Success', 'Set "'.$DriveRootText.'" as active drive');
			Hub::NotifyUsers('NewActiveDrive', 'File System', 'Set "'.$DriveRootText.'" as active drive');
			
			if(!is_dir($DriveRoot.'/Downloads')) {
				mkdir($DriveRoot.'/Downloads');
			}
			
			if(!is_dir($DriveRoot.'/Completed')) {
				mkdir($DriveRoot.'/Completed');
			}
			
			UTorrent::SetSetting('dir_active_download',    $Drive['DriveLetter'].'/Downloads');
			UTorrent::SetSetting('dir_completed_download', $Drive['DriveLetter'].'/Completed');
			
			Hub::AddLog(EVENT.'uTorrent', 'Success', 'Set "'.$DriveRootText.'" as active drive');
		}
	}
	
	function RemoveDrive($DriveID) {
		$Settings = Hub::GetSettings();
		
		$DrivePrep = $this->PDO->prepare('SELECT * FROM Drives WHERE DriveID = :ID');
		$DrivePrep->execute(array(':ID' => $DriveID));
		
		$Drive = $DrivePrep->fetch();
		$DriveRoot = ($Drive['DriveNetwork']) ? 'smb:'.$Drive['DriveRoot'] : $Drive['DriveLetter'];
		
		if(!empty($DriveRoot)) {
			$DriveDeletePrep = $this->PDO->prepare('DELETE FROM Drives WHERE DriveID = :ID');
			$DriveDeletePrep->execute(array(':ID' => $DriveID));
		
			Hub::AddLog(EVENT.'File System', 'Success', 'Deleted "'.$Drive['DriveLetter'].'" from database');
		
			if($Drive['DriveActive']) {
				self::DetermineNewActiveDrive();
			}
			
			if(is_file($Settings['SettingXBMCSourcesFile'])) {
				$DocObj = new DOMDocument();
				$DocObj->load($Settings['SettingXBMCSourcesFile']);
		
				$Sources = $DocObj->getElementsByTagName('source');
				$PathArr = array();
				
				$LogPaths = array();
				foreach($Sources AS $Source) {
					$Names = $Source->getElementsByTagName('name');
					$Name  = $Names->item(0)->nodeValue;
					
					$DriveChk = $DriveRoot.'/Media/'.$Name.'/';
					
					$Paths = $Source->getElementsByTagName('path');
					foreach($Paths AS $Path) {
						if($Path->nodeValue == $DriveChk) {
							$Source->removeChild($Path);
							
							$LogPaths[] = $DriveChk;
						}
					}
				}
			
				$DocObj->save($Settings['SettingXBMCSourcesFile']);
				
				if(sizeof($LogPaths)) {
					Hub::AddLog(EVENT.'XBMC', 'Success', 'Removed "'.implode(', ', $LogPaths).'" from Sources.xml');
				}
			}
			else {
				echo $Settings['SettingXBMCSourcesFile'].' does not exist.';
			}
		}
	}
	
	function AddDrive($DriveLetter, $DriveRoot = '') {
		$Settings = Hub::GetSettings();
		
		if(!$Settings['SettingXBMCSourcesFile'] || !is_file($Settings['SettingXBMCSourcesFile'])) {
			die('no such file');
		}
		
		$Drive = ($DriveRoot) ? $DriveRoot : $DriveLetter;
		
		$RequiredFolders = array('Completed', 
		                         'Downloads', 
		                         'Media', 
		                         'Media/Misc', 
		                         'Media/Movies', 
		                         'Media/TV', 
		                         'Unsorted');
		
		if(isset($Drive) && !empty($Drive)) {
			if(is_dir($Drive)) {
				$LogFolders = array();
				foreach($RequiredFolders AS $RequiredFolder) {
					if(!is_dir($Drive.'/'.$RequiredFolder)) {
						if(mkdir($Drive.'/'.$RequiredFolder)) {
							$LogFolders[] = $RequiredFolder;
						}
					}
				}
				
				if(sizeof($LogFolders)) {
					Hub::AddLog(EVENT.'Drives', 'Success', 'Created folders: "'.implode(', ', $LogFolders).'" on "'.$Drive.'"');
				}
			}
		}
		
		$DriveNetwork = (!empty($DriveRoot)) ? 1 : 0;
		
		$DriveAddPrep = $this->PDO->prepare('INSERT INTO Drives (DriveID, DriveDate, DriveRoot, DriveNetwork, DriveLetter, DriveActive) VALUES (NULL, :Date, :Root, :Network, :Letter, :Active)');
		$DriveAddPrep->execute(array(':Date'    => time(),
		                             ':Root'    => $DriveRoot,
		                             ':Network' => $DriveNetwork,
		                             ':Letter'  => $DriveLetter,
		                             ':Active'  => 0));
		
		Hub::AddLog(EVENT.'Drives', 'Success', 'Added "'.$DriveLetter.'" to the database');
		
		if(is_file($Settings['SettingXBMCSourcesFile'])) {
			$DriveRoot = ($DriveNetwork) ? 'smb:'.$DriveRoot : $DriveLetter;
			
			$DocObj = new DOMDocument();
			$DocObj->load($Settings['SettingXBMCSourcesFile']);
		
			$Sources = $DocObj->getElementsByTagName('source');
			$PathArr = array();
			$LogSources = array();
			foreach($Sources AS $Source) {
				$Names = $Source->getElementsByTagName('name');
				$Name  = $Names->item(0)->nodeValue;
			
				$Paths = $Source->getElementsByTagName('path');
				foreach($Paths AS $Path) {
					$PathArr[$Name][] = $Path->nodeValue;
				}
				
				if(@!in_array($DriveRoot.'/'.'Media'.'/'.$Name.'/', @$PathArr[$Name])) {
					$PathElement = $DocObj->createElement('path', $DriveRoot.'/'.'Media'.'/'.$Name.'/');
					$Source->appendChild($PathElement);
			
					$PathAttr = $DocObj->createAttribute('pathversion');
					$PathElement->appendChild($PathAttr);
				
					$PathAttrText = $DocObj->createTextNode('1');
					$PathAttr->appendChild($PathAttrText);
					
					$LogSources[] = $DriveRoot.'/'.'Media'.'/'.$Name.'/';
				}
			}
			
			$DocObj->save($Settings['SettingXBMCSourcesFile']);
			
			if(sizeof($LogSources)) {
				Hub::AddLog(EVENT.'XBMC', 'Success', 'Added "'.implode(', ', $LogSources).'" to Sources.xml');
			}
			
			XBMC::Connect();
			if(is_object($this->XBMCRPC)) {
				XBMC::ScanForContent();
				
				Hub::AddLog(EVENT.'XBMC', 'Success', 'Updated XBMC Library');
			}
		}
		else {
			echo $Settings['SettingXBMCSourcesFile'].' does not exist.';
		}
	}
}
?>