<?php
class Drives extends Hub {
	function CheckActiveDrive() {
		$Settings = Hub::GetSettings();
		
		self::GetActiveDrive();
		UTorrent::Connect();
		
		if($this->ActiveDrive) {
			$Drive = self::GetDriveByID($this->ActiveDrive);
			
			if(is_array($Drive)) {
				$FreeSpace  = self::GetFreeSpace($Drive['DriveRoot'],  TRUE);
				$TotalSpace = self::GetTotalSpace($Drive['DriveRoot'], TRUE);
				if(self::GetFreeSpacePercentage($FreeSpace, $TotalSpace) <= $Settings['SettingHubMinimumActiveDiskPercentage']) {
					$Drives = Drives::GetDrives();
					
					if(is_array($Drives)) {
						foreach($Drives AS $Drive) {
							if(self::GetFreeSpacePercentage($FreeSpace, $TotalSpace) > $Settings['SettingHubMinimumActiveDiskPercentage']) {
								self::SetActiveDrive($Drive['DriveID']);
								
								break;
							}
						}
					}
				}
				
				$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveLetter'] : $Drive['DriveRoot'];
				
				if(is_dir($DriveRoot.'/Downloads')) {
					UTorrent::SetSetting('dir_active_download', $DriveRoot.'/Downloads');
				}
				else {
					die(Hub::AddLog(EVENT.'uTorrent', 'Failure', 'Incomplete Downloads folder: "'.$DriveRoot.'/Downloads" does not exist'));
				}
				
				if(is_dir($DriveRoot.'/Completed')) {
					UTorrent::SetSetting('dir_completed_download', $DriveRoot.'/Completed');
				}
				else {
					die(Hub::AddLog(EVENT.'uTorrent', 'Failure', 'Completed Downloads folder: "'.$DriveRoot.'/Completed" does not exist'));
				}
				
				if($Settings['SettingUTorrentWatchFolder'] && is_dir($Settings['SettingUTorrentWatchFolder'])) {
					UTorrent::SetSetting('dir_autoload', $Settings['SettingUTorrentWatchFolder']);
				}
				else {
					die(Hub::AddLog(EVENT.'uTorrent', 'Failure', 'Watch folder: "'.$Settings['SettingUTorrentWatchFolder'].'" does not exist'));
				}
			}
			else {
				die(Hub::AddLog(EVENT.'Drives', 'Failure', 'Unable to get drives from database'));
			}
		}
		else {
			die(Hub::AddLog(EVENT.'Drives', 'Failure', 'No active drive has been set'));
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
	
	function GetFreeSpace($DriveRoot, $AsBytes = FALSE) {
		if($AsBytes) {
			return disk_free_space($DriveRoot);
		}
		else {
			return $this->BytesToHuman(disk_free_space($DriveRoot));
		}
	}
	
	function GetTotalSpace($DriveRoot, $AsBytes = FALSE) {
		if($AsBytes) {
			return disk_total_space($DriveRoot);
		}
		else {
			return $this->BytesToHuman(disk_total_space($DriveRoot));
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
			
			Hub::AddLog(EVENT.'File System', 'Success', 'Set "'.$Drive['DriveRoot'].'" as active drive');
			
			if($Drive['DriveNetwork']) {
				$DriveRoot = $Drive['DriveLetter'];
			}
			else {
				$DriveRoot = $Drive['DriveRoot'];
			}
			
			$this->ActiveDrive = $Drive['DriveID'];
			
			if(!is_dir($DriveRoot.'/Downloads')) {
				mkdir($DriveRoot.'/Downloads');
			}
			
			if(!is_dir($DriveRoot.'/Completed')) {
				mkdir($DriveRoot.'/Completed');
			}
			
			UTorrent::SetSetting('dir_active_download',    $DriveRoot.'/Downloads');
			UTorrent::SetSetting('dir_completed_download', $DriveRoot.'/Completed');
			
			Hub::AddLog(EVENT.'uTorrent', 'Success', 'Set "'.$Drive['DriveRoot'].'" as active drive');
		}
	}
	
	function RemoveDrive($DriveID) {
	}
	
	function AddDrive($DriveRoot) {
	}
}
?>