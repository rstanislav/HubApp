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
	    foreach(new RecursiveIteratorIterator(new IgnorantRecursiveDirectoryIterator($Directory)) AS $File) { 
	        $DirSize += self::GetFileSize($File); 
	    }
	    
	    return $DirSize; 
	} 
	
	function GetFiles() {
		$Drives = Drives::GetDrives();
		
		$CompletedFiles = array();
		if(is_array($Drives)) {	
			foreach($Drives AS $Drive) {
				$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveShare'] : $Drive['DriveMount'];
				$Files = Hub::RecursiveDirSearch($DriveRoot.'/Completed');
				
				foreach($Files AS $File) {
					$FileInfo = pathinfo($File);
					switch($FileInfo['extension']) {
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
							
							if($UniqueRarFile && !$this->SeedingFileCopied($File)) {
								if(!preg_match("/\bsubs\b|\bsubpack\b|\bsubfix\b|\bsubtitles\b|\bsub\b|\bsubtitle\b|\btrailer\b|\btrailers\b/i", $File)) {
									$CompletedFiles['Extract'][] = $File.'--||--'.$Drive['DriveID'];
								}
							}
						break;
						
						case 'mp4':
						case 'mkv':
						case 'avi':
							if($this->GetFileSize($File) >= (1024 * 1024 * 150) && !$this->SeedingFileCopied($File)) {
								$CompletedFiles['Move'][] = $File.'--||--'.$Drive['DriveID'];
							}
						break;
					}
				}
			}
		}
		
		return $CompletedFiles;
	}
	
	function SeedingFileCopied($File) {
		$SeedingFilePrep = $this->PDO->prepare('SELECT SeedingID FROM Seeding WHERE SeedingFile = :FileBase OR SeedingFile = :File');
		$SeedingFilePrep->execute(array(':FileBase' => pathinfo($File, PATHINFO_BASENAME),
		                                ':File'     => $File));
		                             
		if($SeedingFilePrep->rowCount()) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
	function SeedingFileCopyToDB($File) {
		$SeedingFilePrep = $this->PDO->prepare('INSERT INTO Seeding (SeedingID, SeedingDate, SeedingFile) VALUES (null, :Date, :File)');
		$SeedingFilePrep->execute(array(':Date' => time(),
		                                ':File' => $File));
	}
	
	function ExtractFile($File, $DriveID) {
		$FileInfo = pathinfo($File);
		$FileInfo['foldername'] = substr($FileInfo['dirname'], (strrpos($FileInfo['dirname'], '/') + 1));
		
		$Drive = Drives::GetDriveByID($DriveID);
		$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveShare'] : $Drive['DriveMount'];
		
		if(!empty($FileInfo['foldername'])) {
			exec('"'.realpath(dirname(__FILE__).'/../').'/resources/unrar/UnRAR.exe" e "'.str_replace('/', '\\', $File).'" "'.str_replace('/', '\\', $FileInfo['dirname']).'/"', $RarOutput, $RarReturn);
			
			if($RarReturn == 0) {
				$ExtractedFile = $RarOutput[(sizeof($RarOutput) - 2)];
				$ExtractedFile = str_replace('...         ', '', $ExtractedFile);
				$ExtractedFile = substr($ExtractedFile, 0, strpos($ExtractedFile, '  '));
				
				$MoveFileReturn = self::MoveFile($FileInfo['dirname'].'/'.$ExtractedFile, $DriveID, pathinfo($File, PATHINFO_BASENAME));
				
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
	
	function MoveFile($File, $DriveID, $ExtractedFrom = '') {
		$FileInfo = pathinfo($File);
		$FileInfo['foldername'] = substr($FileInfo['dirname'], (strrpos($FileInfo['dirname'], '/') + 1));
		
		UTorrent::Connect();
		$FileInTorrent = (empty($ExtractedFrom)) ? $File : $ExtractedFrom;
		$SeedingFile   = UTorrent::CheckTorrentForFile($FileInTorrent);
		
		if($SeedingFile && empty($ExtractedFrom)) {
			$MoveAction    = 'copy';
			$MoveText      = 'Copied';
		}
		else {
			$MoveAction    = 'rename';
			$MoveText      = 'Moved';
		}
		
		$Drive = Drives::GetDriveByID($DriveID);
		$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveShare'] : $Drive['DriveMount'];
		
		if(in_array(strtoupper($FileInfo['foldername']), array('CD1', 'CD2', 'CD3'))) {
			$NewFileName = str_replace($DriveRoot.'/Completed/', '', $FileInfo['dirname']);
			$NewFileName = str_replace('/', '-', $NewFileName);
			$NewFileName = $NewFileName.'.'.$FileInfo['extension'];
		}
		else {
			if(RSS::ParseRelease($FileInfo['basename'])) {
				if(RSS::GetQualityRank($FileInfo['foldername']) > RSS::GetQualityRank($FileInfo['basename'])) {
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

		if(is_file($FileInfo['dirname'].'/'.$FileInfo['basename']) && $MoveAction($FileInfo['dirname'].'/'.$FileInfo['basename'], $NewFolder.'/'.$NewFileName)) {
			$AddLogEntry = '';
				
			if(!str_replace($DriveRoot.'/Completed', '', $FileInfo['dirname'])) {
				$OldLocation = $FileInfo['basename'];
			}
			else {
				$OldLocation = str_replace($DriveRoot.'/Completed/', '', $FileInfo['dirname']).'/'.$FileInfo['basename'];
			}
			
			if(!$SeedingFile) {
				if($FileInfo['foldername'] != 'Completed') {
					$Files = Hub::RecursiveDirSearch($FileInfo['dirname']);
					$FilesNo = 0;
					foreach($Files AS $File) {
						if(!preg_match("/\bsubs\b|\bsubpack\b|\bsubfix\b|\bsubtitles\b|\bsub\b|\bsubtitle\b|\btrailer\b|\btrailers\b|\bsample\b/i", $File) && self::GetFileSize($File) > (1024 * 1024 * 100)) {
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
					else {
						$NewLocation = $DriveRoot.'/Unsorted/'.str_replace($DriveRoot.'/Completed/', '', $FileInfo['dirname']);
						if(rename($FileInfo['dirname'], $NewLocation)) {
							$AddLogEntry = ' and moved "'.$FileInfo['dirname'].'" to "'.$DriveRoot.'/Unsorted/" because it has files worth keeping';
						}
					}
				}
			}
			else {
				$this->SeedingFileCopyToDB($FileInTorrent);
				
				$AddLogEntry = ' and kept the original files for seeding purposes';
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
							                                  
							Hub::NotifyUsers('NewLibraryEpisode', 'XBMC/Series', '"'.$SerieTitle.' '.$ParsedEpisode[0].'x'.$ParsedEpisode[1].'" is now available on "'.$DriveRoot.'"');
						}
					}
				}
			}
			else if($ParsedFile['Type'] == 'Movie') {
				$WishlistUpdatePrep = $this->PDO->prepare('UPDATE Wishlist SET WishlistFile = :File, WishlistDownloadDate = :Date, WishlistFileGone = 0 WHERE WishlistTitle = :Title AND WishlistYear = :Year');
				$WishlistUpdatePrep->execute(array(':File'  => $NewFolder.'/'.$NewFileName,
				                                   ':Date'  => time(),
				                                   ':Title' => $ParsedFile['Title'],
				                                   ':Year'  => $ParsedFile['Year']));
				                                   
				Hub::NotifyUsers('NewLibraryMovie', 'XBMC/Movies', '"'.$ParsedFile['Title'].' ('.$ParsedFile['Year'].')" is now available on "'.$DriveRoot.'"');
			}
			
			$LogEntry = $MoveText.' "'.$FileInfo['dirname'].'/'.$FileInfo['basename'].'" to "'.$NewFolder.'/'.$NewFileName.'"'.$AddLogEntry;
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
					list($File, $DriveID) = explode('--||--', $File);
					
					self::ExtractFile($File, $DriveID);
				}
			}
			
			if(array_key_exists('Move', $Files)) {
				foreach($Files['Move'] AS $File) {
					list($File, $DriveID) = explode('--||--', $File);
					
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
		$Drives = Drives::GetDrives();
		
		if(is_array($Drives)) {
			$FoldersDeleted = $FoldersSizeDeleted = 0;
			foreach($Drives AS $Drive) {
				$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveShare'] : $Drive['DriveMount'];
				$CompletedContents = new RecursiveIteratorIterator(new IgnorantRecursiveDirectoryIterator($DriveRoot.'/Completed'), RecursiveIteratorIterator::SELF_FIRST);
				
				foreach($CompletedContents AS $Name => $Object){
				    if(is_dir($Name)) {
				    	try {
				    		$DirSize = self::GetDirectorySize($Name);
				    	}
				    	catch(UnexpectedValueException $e) {
				    		break;
				    	}
				    	
				    	if($DirSize <= (1024 * 1024 * 100)) {
				    		@Drives::RecursiveDirRemove($Name);
				    		
				    		$FoldersDeleted++;
				    		$FoldersSizeDeleted += $DirSize;
				    	}
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