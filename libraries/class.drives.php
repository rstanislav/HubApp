<?php
class Drives extends Hub {
	function CheckActiveDrive() {
		self::GetActiveDrive();
		UTorrent::Connect();
		
		if($this->ActiveDrive) {
			$Drive = self::GetDriveByID($this->ActiveDrive);
			
			if(is_array($Drive)) {
				$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveShare'] : $Drive['DriveMount'];
				
				$FreeSpace  = self::GetFreeSpace($DriveRoot,  TRUE);
				$TotalSpace = self::GetTotalSpace($DriveRoot, TRUE);
				if(($FreeSpace / 1024 / 1024 / 1024) <= Hub::GetSetting('MinimumDiskSpaceRequired')) {
					self::DetermineNewActiveDrive();
				}
				else {
					if(is_dir($DriveRoot.'/Downloads')) {
						UTorrent::SetSetting('dir_active_download', $Drive['DriveMount'].'/Downloads');
					}
					else {
						die(Hub::AddLog(EVENT.'uTorrent', 'Failure', 'Incomplete Downloads folder: "'.$Drive['DriveMount'].'/Downloads" does not exist'));
					}
					
					if(is_dir($DriveRoot.'/Completed')) {
						UTorrent::SetSetting('dir_completed_download', $Drive['DriveMount'].'/Completed');
					}
					else {
						die(Hub::AddLog(EVENT.'uTorrent', 'Failure', 'Completed Downloads folder: "'.$Drive['DriveMount'].'/Completed" does not exist'));
					}
					
					if(is_dir(Hub::GetSetting('UTorrentWatchFolder'))) {
						UTorrent::SetSetting('dir_autoload', Hub::GetSetting('UTorrentWatchFolder'));
					}
					else {
						die(Hub::AddLog(EVENT.'uTorrent', 'Failure', 'Watch folder: "'.Hub::GetSetting('UTorrentWatchFolder').'" does not exist'));
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
	
	function DriveShareCredentials($DriveShare, $User = '', $Pass = '') {
		$DriveShare = str_replace('\\', '/', $DriveShare);
		
		if(!empty($User) && !empty($Pass)) {
			$DriveShare = str_replace('//', '//'.$User.':'.$Pass.'@', $DriveShare);
		}
		
		return $DriveShare;
	}
	
	function GetNetworkLocation($Location) {
		if(strpos($Location, '/') != 0) {
			$Drive = $this->PDO->query('SELECT * FROM Drives WHERE DriveMount LIKE "'.substr($Location, 0, strpos($Location, '/')).'%" LIMIT 1')->fetch();
	
			if(is_array($Drive)) {
				return str_replace($Drive['DriveMount'], self::DriveShareCredentials($Drive['DriveShare'], $Drive['DriveUser'], $Drive['DrivePass']), $Location);
			}
			else {
				return $Location;
			}
		}
		else {
			return $Location;
		}
	}
	
	function GetLocalLocation($Location) {
		$Location = str_replace('smb:', '', $Location);
		
		if(strpos($Location, '/') === 0) {
			$Location = preg_replace('/[A-z0-9-]+\:[A-z0-9-]+@/', '', $Location);
			
			$Drives = Drives::GetDrives();
			
			if(is_array($Drives)) {
				foreach($Drives AS $Drive) {
					if(strstr($Location, $Drive['DriveShare'])) {
						return str_replace($Drive['DriveShare'], $Drive['DriveMount'], $Location);
					}
				}
			}
			else {
				return $Location;
			}
		}
		else {
			return $Location;
		}
	}
	
	function GetDriveByMount($DriveMount) {
		$DrivePrep = $this->PDO->prepare('SELECT * FROM Drives WHERE DriveMount = :Mount');
		$DrivePrep->execute(array(':Mount' => $DriveMount));
	
		if($DrivePrep->rowCount()) {
			return $DrivePrep->fetch();
		}
		else {
			return FALSE;
		}
	}
	
	function DetermineNewActiveDrive() {
		$Drives = Drives::GetDrives();
		
		if(is_array($Drives)) {
			foreach($Drives AS $Drive) {
				$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveShare'] : $Drive['DriveMount'];
				
				$FreeSpace  = self::GetFreeSpace($DriveRoot,  TRUE);
				$TotalSpace = self::GetTotalSpace($DriveRoot, TRUE);
				if(($FreeSpace / 1024 / 1024 / 1024) > Hub::GetSetting('MinimumDiskSpaceRequired')) {
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
	
	function GetDrives() {
		$DrivePrep = $this->PDO->prepare('SELECT * FROM Drives ORDER BY DriveDate');
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
	
	function RecursiveDirFileAdd($Directory, $FileToAdd) { 
		if(is_dir($Directory)) {
			touch($Directory.'/'.$FileToAdd);
			$Objects = scandir($Directory); 
			
			foreach($Objects AS $Object) { 
				if($Object != '.' && $Object != '..') { 
	        		if(filetype($Directory.'/'.$Object) == 'dir') {
	        			self::RecursiveDirFileAdd($Directory.'/'.$Object, $FileToAdd); 
	        		}
	       		} 
	     	} 
	     
	     	reset($Objects);
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
	
	function GetFreeSpace($DriveID, $AsBytes = FALSE) {
		$Drive = self::GetDriveByID($DriveID);
		$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveShare'] : $Drive['DriveMount'];
		$FreeSpace = @disk_free_space($DriveRoot);
		
		if($AsBytes) {
			return $FreeSpace;
		}
		else {
			return $this->BytesToHuman($FreeSpace);
		}
	}
	
	function GetTotalSpace($DriveID, $AsBytes = FALSE) {
		$Drive = self::GetDriveByID($DriveID);
		$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveShare'] : $Drive['DriveMount'];
		$TotalSpace = @disk_total_space($DriveRoot);
		
		if($AsBytes) {
			return $TotalSpace;
		}
		else {
			return $this->BytesToHuman($TotalSpace);
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
			$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveShare'] : $Drive['DriveMount'];
			
			$this->ActiveDrive = $Drive['DriveID'];
			Hub::AddLog(EVENT.'File System', 'Success', 'Set "'.$Drive['DriveShare'].' ('.$Drive['DriveMount'].')" as active drive');
			Hub::NotifyUsers('NewActiveDrive', 'File System', 'Set "'.$Drive['DriveShare'].' ('.$Drive['DriveMount'].')" as active drive');
			
			if(!is_dir($DriveRoot.'/Downloads')) {
				mkdir($DriveRoot.'/Downloads');
			}
			
			if(!is_dir($DriveRoot.'/Completed')) {
				mkdir($DriveRoot.'/Completed');
			}
			
			UTorrent::SetSetting('dir_active_download',    $Drive['DriveMount'].'/Downloads');
			UTorrent::SetSetting('dir_completed_download', $Drive['DriveMount'].'/Completed');
			
			Hub::AddLog(EVENT.'uTorrent', 'Success', 'Set "'.$Drive['DriveShare'].' ('.$Drive['DriveMount'].')" as active drive');
		}
	}
	
	function RemoveDrive($DriveID) {
		$DrivePrep = $this->PDO->prepare('SELECT * FROM Drives WHERE DriveID = :ID');
		$DrivePrep->execute(array(':ID' => $DriveID));
		
		$Drive = $DrivePrep->fetch();
		$DriveShareCred = self::DriveShareCredentials($Drive['DriveShare'], $Drive['DriveUser'], $Drive['DrivePass']);
	
		if($Drive['DriveActive']) {
			self::DetermineNewActiveDrive();
		}
		
		if(is_file(Hub::GetSetting('XBMCDataFolder').'/userdata/sources.xml')) {
			$DocObj = new DOMDocument();
			$DocObj->load(Hub::GetSetting('XBMCDataFolder').'/userdata/sources.xml');
	
			$Sources = $DocObj->getElementsByTagName('source');
			$PathArr = array();
			
			$LogPaths = array();
			foreach($Sources AS $Source) {
				$Names = $Source->getElementsByTagName('name');
				$Name  = $Names->item(0)->nodeValue;
				
				$DriveChk = 'smb:'.$DriveShareCred.'/Media/'.$Name.'/';
				
				$Paths = $Source->getElementsByTagName('path');
				foreach($Paths AS $Path) {
					if($Path->nodeValue == $DriveChk) {
						$Source->removeChild($Path);
						
						$LogPaths[] = 'smb:'.$Drive['DriveShare'].'/Media/'.$Name.'/';
					}
				}
			}
		
			$DocObj->save(Hub::GetSetting('XBMCDataFolder').'/userdata/sources.xml');
			
			if(sizeof($LogPaths)) {
				Hub::AddLog(EVENT.'XBMC', 'Success', 'Removed "'.implode(', ', $LogPaths).'" from '.Hub::GetSetting('XBMCDataFolder').'/userdata/sources.xml');
			}
		}
		else {
			echo Hub::GetSetting('XBMCDataFolder').'/userdata/sources.xml does not exist.';
		}
		
		$DriveDeletePrep = $this->PDO->prepare('DELETE FROM Drives WHERE DriveID = :ID');
		$DriveDeletePrep->execute(array(':ID' => $DriveID));
		
		Hub::AddLog(EVENT.'File System', 'Success', 'Deleted "'.$Drive['DriveShare'].' ('.$Drive['DriveMount'].')" from database');
	}
	
	function AddDrive($DriveShare, $DriveUser, $DrivePass, $DriveMount, $DriveNetwork = 0) {
		if(!is_file(Hub::GetSetting('XBMCDataFolder').'/userdata/sources.xml')) {
			die('File does not exist: '.Hub::GetSetting('XBMCDataFolder').'/userdata/sources.xml');
		}
		
		$DriveRoot = ($DriveNetwork) ? $DriveShare : $DriveMount;
		
		$RequiredFolders = array('Completed', 
		                         'Downloads', 
		                         'Media', 
		                         'Media/Misc', 
		                         'Media/Movies', 
		                         'Media/TV', 
		                         'Unsorted');
		
		if(!empty($DriveRoot) && is_dir($DriveRoot)) {
			$LogFolders = array();
			foreach($RequiredFolders AS $RequiredFolder) {
				if(!is_dir($DriveRoot.'/'.$RequiredFolder)) {
					if(mkdir($DriveRoot.'/'.$RequiredFolder)) {
						$LogFolders[] = $RequiredFolder;
					}
				}
			}
			
			if(sizeof($LogFolders)) {
				Hub::AddLog(EVENT.'Drives', 'Success', 'Created folders: "'.implode(', ', $LogFolders).'" on "'.$DriveShare.' ('.$DriveMount.')"');
			}
		}
		
		$DriveAddPrep = $this->PDO->prepare('INSERT INTO Drives (DriveID, DriveDate, DriveShare, DriveUser, DrivePass, DriveMount, DriveActive, DriveNetwork) VALUES (NULL, :Date, :Share, :User, :Pass, :Mount, :Active, :Network)');
		$DriveAddPrep->execute(array(':Date'    => time(),
		                             ':Share'   => $DriveShare,
		                             ':User'    => $DriveUser,
		                             ':Pass'    => $DrivePass,
		                             ':Mount'   => $DriveMount,
		                             ':Active'  => 0,
		                             ':Network' => $DriveNetwork));
		
		Hub::AddLog(EVENT.'Drives', 'Success', 'Added "'.$DriveShare.' ('.$DriveMount.')" to the database');
		$DriveShareCred = self::DriveShareCredentials($DriveShare, $DriveUser, $DrivePass);
		
		if(is_file(Hub::GetSetting('XBMCDataFolder').'/userdata/sources.xml')) {
			$DocObj = new DOMDocument();
			$DocObj->load(Hub::GetSetting('XBMCDataFolder').'/userdata/sources.xml');
		
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
				
				if(@!in_array('smb:'.$DriveShareCred.'/'.'Media'.'/'.$Name.'/', @$PathArr[$Name])) {
					$PathElement = $DocObj->createElement('path', 'smb:'.$DriveShareCred.'/'.'Media'.'/'.$Name.'/');
					$Source->appendChild($PathElement);
			
					$PathAttr = $DocObj->createAttribute('pathversion');
					$PathElement->appendChild($PathAttr);
				
					$PathAttrText = $DocObj->createTextNode('1');
					$PathAttr->appendChild($PathAttrText);
					
					$LogSources[] = 'smb:'.$DriveShare.'/'.'Media'.'/'.$Name.'/';
				}
			}
			
			$DocObj->save(Hub::GetSetting('XBMCDataFolder').'/userdata/sources.xml');
			
			if(sizeof($LogSources)) {
				Hub::AddLog(EVENT.'XBMC', 'Success', 'Added "'.implode(', ', $LogSources).'" to '.Hub::GetSetting('XBMCDataFolder').'/userdata/sources.xml');
			}
			
			XBMC::Connect('default');
			if(is_object($this->XBMCRPC)) {
				XBMC::ScanForContent();
				
				Hub::AddLog(EVENT.'XBMC', 'Success', 'Updated XBMC Library');
			}
		}
		else {
			echo Hub::GetSetting('XBMCDataFolder').'/userdata/sources.xml'.' does not exist.';
		}
	}
}
?>