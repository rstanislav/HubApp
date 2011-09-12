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
	
	function GetFreeSpace($DriveLetter, $AsBytes = FALSE) {
		if($AsBytes) {
			return disk_free_space($DriveLetter);
		}
		else {
			return $this->BytesToHuman(disk_free_space($DriveLetter));
		}
	}
	
	function GetTotalSpace($DriveLetter, $AsBytes = FALSE) {
		if($AsBytes) {
			return disk_total_space($DriveLetter);
		}
		else {
			return $this->BytesToHuman(disk_total_space($DriveLetter));
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
	}
	
	function AddDrive($DriveRoot) {
	}
}
?>