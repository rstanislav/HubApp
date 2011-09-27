<?php
class UnsortedFiles extends Hub {
	function GetUnsortedFiles() {
		$Drives = Drives::GetDrivesFromDB();
		
		if(is_array($Drives)) {
			$UnsortedFiles = array();
			foreach($Drives AS $Drive) {
				$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveRoot'] : $Drive['DriveLetter'];
				$Files[$DriveRoot] = glob($DriveRoot.'/Unsorted/*');
				
				if(sizeof($Files[$DriveRoot])) {
					$UnsortedFiles[$DriveRoot] = $Files[$DriveRoot];
				}
			}
			
			if(sizeof($UnsortedFiles)) {
				return $UnsortedFiles;
			}
		}
	}
	
	function RenameFile($Folder, $From, $To) {
		if(is_file($Folder.$From)) {
			if(rename($Folder.$From, $Folder.$To)) {
				echo $To;
				Hub::AddLog(EVENT.'Unsorted Files', 'Success', 'Renamed "'.$Folder.$From.'" to "'.$Folder.$To.'"');
			}
			else {
				Hub::AddLog(EVENT.'Unsorted Files', 'Failure', 'Failed to rename "'.$Folder.$From.'" to "'.$Folder.$To.'"');
			}
		}
	}
	
	function MoveFile($From, $To) {
		if(is_file($From)) {
			if(rename($From, $To)) {
				Hub::AddLog(EVENT.'Unsorted Files', 'Success', 'Moved "'.$From.'" to "'.$To.'"');
			}
			else {
				Hub::AddLog(EVENT.'Unsorted Files', 'Failure', 'Failed to move "'.$From.'" to "'.$To.'"');
			}
		}
	}
	
	function DeleteFile($File) {
		if(is_dir($File)) {
			if(Drives::RecursiveDirRemove($File)) {
				Hub::AddLog(EVENT.'Unsorted Files', 'Success', 'Deleted "'.$File.'"');
			}
			else {
				echo 'Error';
				Hub::AddLog(EVENT.'Unsorted Files', 'Failure', 'Failed to delete "'.$File.'"');
			}
		}
		else {
			if(is_file($File)) {
				if(unlink($File)) {
					Hub::AddLog(EVENT.'Unsorted Files', 'Success', 'Deleted "'.$File.'"');
				}
				else {
					echo 'Error';
					Hub::AddLog(EVENT.'Unsorted Files', 'Failure', 'Failed to delete "'.$File.'"');
				}
			}
		}
	}
}
?>