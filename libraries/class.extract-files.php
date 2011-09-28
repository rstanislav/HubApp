<?php
class ExtractFiles extends Hub {
	function GetFileSize($File) {
		clearstatcache();
		
		$IntegerMax  = 4294967295; // 2147483647+2147483647+1;
		$FileSize    = filesize($File);
		$FilePointer = fopen($File, 'r');
		fseek($FilePointer, 0, SEEK_END);
		
		if(ftell($FilePointer) == 0) {
			$FileSize += $IntegerMax;
		}
		
		fclose($FilePointer);
		
		if($FileSize < 0) {
			$FileSize += $IntegerMax;
		}
		
		return $FileSize;
	}
	
	function GetDirectorySize($Directory) { 
	    $DirSize = 0; 
	    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($Directory)) as $File){ 
	        $DirSize += self::GetFileSize($File); 
	    }
	    
	    return $DirSize; 
	} 
	
	function GetFiles() {
		$Drives = Drives::GetDrivesFromDB();
		
		$CompletedFiles = array();
		if(is_array($Drives)) {
			foreach($Drives AS $Drive) {
				$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveRoot'] : $Drive['DriveLetter'];
				$Files = Hub::RecursiveGlob($DriveRoot.'/Completed', "{*.mp4,*.mkv,*.avi,*.rar}", GLOB_BRACE);
				
				foreach($Files AS $File) {
					$FileExt = pathinfo($File, PATHINFO_EXTENSION);
					
					switch($FileExt) {
						case 'rar':
							$UniqueRarFile = FALSE;
						
							preg_match('/([^.]+)\.([^.]+)$/', $File, $FileMatches);
						
							if(!stristr($FileMatches[1], 'part')) {
								$UniqueRarFile = TRUE;
							}
							if($FileMatches[1] == 'part1') {
								$UniqueRarFile = TRUE;
							}
							if($FileMatches[1] == 'part01') {
								$UniqueRarFile = TRUE;
							}
							if($FileMatches[1] == 'part001') {
								$UniqueRarFile = TRUE;
							}
							if($FileMatches[1] == 'part0001') {
								$UniqueRarFile = TRUE;
							}
							
							if($UniqueRarFile) {
								if(!preg_match("/\bsubs\b|\bsubpack\b|\bsubfix\b|\bsubtitles\b|\bsub\b|\bsubtitle\b|\btrailer\b|\btrailers\b/i", $File)) {
									$CompletedFiles['Extract'][] = $File.','.$Drive['DriveID'];
								}
							}
						break;
						
						case 'mp4':
						case 'mkv':
						case 'avi':
							if($this->GetFileSize($File) >= (1024 * 1024 * 150)) {
								$CompletedFiles['Move'][] = $File.','.$Drive['DriveID'];
							}
						break;
					}
				}
			}
		}
		
		return $CompletedFiles;
	}
	
	function ExtractFile($File, $DriveID) {
		$FileInfo = pathinfo($File);
		$FileInfo['foldername'] = substr($FileInfo['dirname'], (strrpos($FileInfo['dirname'], '/') + 1));
		
		$Drive = Drives::GetDriveByID($DriveID);
		$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveRoot'] : $Drive['DriveLetter'];
		
		if(!empty($FileInfo['foldername'])) {
			exec('"'.realpath(dirname(__FILE__).'/../').'/resources/unrar/UnRAR.exe" e "'.str_replace('/', '\\', $File).'" "'.str_replace('/', '\\', $FileInfo['dirname']).'/"', $RarOutput, $RarReturn);
			
			if($RarReturn == 0) {
				$ExtractedFile = $RarOutput[(sizeof($RarOutput) - 2)];
				$ExtractedFile = str_replace('...         ', '', $ExtractedFile);
				$ExtractedFile = substr($ExtractedFile, 0, strpos($ExtractedFile, '  '));
				
				$MoveFileReturn = self::MoveFile($FileInfo['dirname'].'/'.$ExtractedFile, $DriveID);
				
				return $MoveFileReturn;
			}
			else {
				if(@rename($FileInfo['dirname'], $DriveRoot.'/Unsorted/'.$FileInfo['foldername'])) {
					Hub::AddLog(EVENT.'File System', 'Success', 'Moved broken download "'.$FileInfo['dirname'].'" to "'.$DriveRoot.'/Unsorted/'.$FileInfo['foldername'].'"');
				}
				else {
					Hub::AddLog(EVENT.'File System', 'Failure', 'Failed to move broken download "'.$FileInfo['dirname'].'" to "'.$DriveRoot.'/Unsorted/'.$FileInfo['foldername'].'"');
				}
				
				return FALSE;
			}
		}
	}
	
	function MoveFile($File, $DriveID) {
		$FileInfo = pathinfo($File);
		$FileInfo['foldername'] = substr($FileInfo['dirname'], (strrpos($FileInfo['dirname'], '/') + 1));
		
		$Drive = Drives::GetDriveByID($DriveID);
		$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveRoot'] : $Drive['DriveLetter'];
		
		if(in_array(strtoupper($FileInfo['foldername']), array('CD1', 'CD2', 'CD3'))) {
			$NewFileName = str_replace($DriveRoot.'/Completed/', '', $FileInfo['dirname']);
			$NewFileName = str_replace('/', '-', $NewFileName);
			$NewFileName = $NewFileName.'.'.$FileInfo['extension'];
		}
		else {
			if(RSS::ParseRelease($FileInfo['basename'])) {
				if(RSS::GetQualityRank($FileInfo['foldername']) >= RSS::GetQualityRank($FileInfo['basename'])) {
					$NewFileName = $FileInfo['foldername'].'.'.$FileInfo['extension'];
				}
				else {
					$NewFileName = $FileInfo['basename'];
				}
			}
			else {
				if(RSS::ParseRelease($FileInfo['foldername'])) {
					$NewFileName = $FileInfo['foldername'].'.'.$FileInfo['extension'];
				}
				else {
					$NewFileName = $FileInfo['filename'].'-'.mt_rand().'.'.$FileInfo['extension'];
				}
			}
		}
		
		if($FileInfo['foldername'] == 'Completed') {
			$NewFileName = $FileInfo['basename'];
		}
		
		$ParsedFile = RSS::ParseRelease($NewFileName);
		
		$NewFolder = $DriveRoot.'/Unsorted';
		if($ParsedFile['Type'] == 'TV') {
			$Serie = $this->PDO->query('SELECT SerieTitle, SerieTitleAlt, SerieID FROM Series WHERE SerieTitle = "'.$ParsedFile['Title'].'" OR SerieTitleAlt = "'.$ParsedFile['Title'].'"')->fetch();
			
			$SerieTitle = (!empty($Serie['SerieTitleAlt'])) ? $Serie['SerieTitleAlt'] : $Serie['SerieTitle'];
			
			if(!empty($Serie['SerieTitle']) && is_dir($DriveRoot.'/Media/TV/'.$Serie['SerieTitle'])) {
				$NewFolder = $DriveRoot.'/Media/TV/'.$Serie['SerieTitle'];
			}
			else if(!empty($Serie['SerieTitleAlt']) && is_dir($DriveRoot.'/Media/TV/'.$Serie['SerieTitleAlt'])) {
				$NewFolder = $DriveRoot.'/Media/TV/'.$Serie['SerieTitleAlt'];
			}
		}
		else if($ParsedFile['Type'] == 'Movie') {
			if(is_dir($DriveRoot.'/Media/Movies')) {
				$NewFolder = $DriveRoot.'/Media/Movies';
			}
		}
		else {
			if(is_dir($DriveRoot.'/Unsorted')) {
				$NewFolder = $DriveRoot.'/Unsorted';
			}
		}
		
		if(is_file($NewFolder.'/'.$NewFileName)) {
			$NewFileInfo = pathinfo($NewFileName);
			$NewFileName = $NewFileInfo['filename'].'-DUPE-'.mt_rand().'.'.$NewFileInfo['extension'];
		}

		if(rename($FileInfo['dirname'].'/'.$FileInfo['basename'], $NewFolder.'/'.$NewFileName)) {
			$AddLogEntry = '';
				
			if(!str_replace($DriveRoot.'/Completed', '', $FileInfo['dirname'])) {
				$OldLocation = $FileInfo['basename'];
			}
			else {
				$OldLocation = str_replace($DriveRoot.'/Completed/', '', $FileInfo['dirname']).'/'.$FileInfo['basename'];
			}
			
			if($FileInfo['foldername'] != 'Completed') {
				$Files = Hub::RecursiveGlob($FileInfo['dirname'], "{*.mp4,*.mkv,*.avi}", GLOB_BRACE);
				$FilesNo = 0;
				foreach($Files AS $File) {
					if(self::GetFileSize($File) > (1024 * 1024 * 100)) {
						$FilesNo++;
					}
				}
				
				if(!$FilesNo) {
					if(@Drives::RecursiveDirRemove($FileInfo['dirname'])) {
						$AddLogEntry = ' and deleted "'.$FileInfo['dirname'].'"';
					}
					else {
						$AddLogEntry = ' but failed to delete "'.$FileInfo['dirname'].'"';
					}
				}
			}
			
			if($ParsedFile['Type'] == 'TV') {
				if(is_file($NewFolder.'/'.$NewFileName)) {
					if($Serie['SerieID']) {
						foreach($ParsedFile['Episodes'] AS $ParsedEpisode) {
							$EpisodeUpdatePrep = $this->PDO->prepare('UPDATE Episodes SET EpisodeFile = :File WHERE SeriesKey = :SerieID AND EpisodeSeason = :Season AND EpisodeEpisode = :Episode');
							$EpisodeUpdatePrep->execute(array(':File'    => $NewFolder.'/'.$NewFileName,
							                                  ':SerieID' => $Serie['SerieID'],
							                                  ':Season'  => $ParsedEpisode[0],
							                                  ':Episode' => $ParsedEpisode[1]));
							                                  
							Hub::NotifyUsers('NewLibraryEpisode', 'XBMC/Series', '"'.$SerieTitle.' s'.sprintf('%02s', $ParsedEpisode[0]).'e'.sprintf('%02s', $ParsedEpisode[1]).'" is now available on "'.$DriveRoot.'"');
						}
					}
				}
			}
			else if($ParsedFile['Type'] == 'Movie') {
				$WishlistUpdatePrep = $this->PDO->prepare('UPDATE Wishlist SET WishlistFile = :File WHERE WishlistTitle = :Title AND WishlistYear = :Year');
				$WishlistUpdatePrep->execute(array(':File'  => $NewFolder.'/'.$NewFileName,
				                                   ':Title' => $ParsedFile['Title'],
				                                   ':Year'  => $ParsedFile['Year']));
				                                   
				Hub::NotifyUsers('NewLibraryMovie', 'XBMC/Movies', '"'.$ParsedFile['Title'].' ('.$ParsedFile['Year'].')" is now available on "'.$DriveRoot.'"');
			}
			
			$LogEntry = 'Moved "'.$FileInfo['dirname'].'/'.$FileInfo['basename'].'" to "'.$NewFolder.'/'.$NewFileName.'"'.$AddLogEntry;
			if($NewFolder != $DriveRoot.'/Unsorted') {
				Hub::AddLog(EVENT.'Extract Files', 'Success', $LogEntry, 0, 'update');
				
				return $LogEntry;
			}
			else {
				Hub::AddLog(EVENT.'Extract Files', 'Success', $LogEntry);
				
				return $LogEntry;
			}
		}
		else {
			$LogEntry = 'Failed to move '.str_replace($DriveRoot.'/Completed/', '', $FileInfo['dirname']).'/'.$FileInfo['basename'].' to '.$NewFolder.'/'.$NewFileName;
			Hub::AddLog(EVENT.'File System', 'Failure', $LogEntry);
			
			return $LogEntry;
		}
	}
	
	function ExtractAndMoveAllFiles() {
		if(!strlen(EVENT)) {
			if(Hub::CheckLock()) {
				return FALSE;
			}
			else {
				Hub::Lock();
			}
		}
		
		$Files = self::GetFiles();
		if(is_array($Files) && sizeof($Files)) {
			if(array_key_exists('Extract', $Files)) {
				foreach($Files['Extract'] AS $File) {
					list($File, $DriveID) = explode(',', $File);
					
					self::ExtractFile($File, $DriveID);
				}
			}
			
			if(array_key_exists('Move', $Files)) {
				foreach($Files['Move'] AS $File) {
					list($File, $DriveID) = explode(',', $File);
					
					self::MoveFile($File, $DriveID);
				}
			}
		}
		
		self::CleanDownloadsFolder();
			
		if(!strlen(EVENT)) {
			Hub::Unlock();
		}
	}
	
	function CleanDownloadsFolder() {
		$Drives = Drives::GetDrivesFromDB();
		
		if(is_array($Drives)) {
			$FoldersDeleted = $FoldersSizeDeleted = 0;
			foreach($Drives AS $Drive) {
				$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveRoot'] : $Drive['DriveLetter'];
				$CompletedContents = array_filter(glob($DriveRoot.'/Completed/*'), 'is_dir');
			
				foreach($CompletedContents AS $Complete) {
					$DirSize = self::GetDirectorySize($Complete);
					if($DirSize <= (1024 * 1024 * 100)) {
						@Drives::RecursiveDirRemove($Complete);
						
						$FoldersDeleted++;
						$FoldersSizeDeleted += $DirSize;
					}
				}
			}
		}
		
		if($FoldersDeleted) {
			Hub::AddLog(EVENT.'File System', 'Success', 'Cleaned Downloads directory. Deleted '.$FoldersDeleted.' folders totaling '.Hub::BytesToHuman($FoldersSizeDeleted));
		} 
	}
}
?>