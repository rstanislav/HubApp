<?php
class ExtractFiles extends Hub {
	function GetFileSize($File) {
		return filesize($File);
	}
	
	function GetDirectorySize($Directory) { 
	    $DirSize = 0; 
	    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($Directory)) as $File){ 
	        $DirSize += self::GetFileSize($File); 
	    }
	    
	    return $DirSize; 
	} 
	
	function GetFiles() {
		$Drives = Drives::GetDrives();
		
		$CompletedFiles = array();
		if(is_array($Drives)) {
			foreach($Drives AS $Drive) {
				$Files = Hub::RecursiveGlob($Drive['DriveRoot'].'/Completed', "{*.mp4,*.mkv,*.avi,*.rar}", GLOB_BRACE);
				
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
									$CompletedFiles['Extract'][] = $File;
								}
							}
						break;
						
						case 'mp4':
						case 'mkv':
						case 'avi':
							if($this->GetFileSize($File) >= (1024 * 1024 * 150)) {
								$CompletedFiles['Move'][] = $File;
							}
						break;
					}
				}
			}
		}
		
		return $CompletedFiles;
	}
	
	function ExtractFile($File) {
		$FileInfo = pathinfo($File);
		$FileInfo['foldername'] = substr($FileInfo['dirname'], (strrpos($FileInfo['dirname'], '/') + 1));
		$FileInfo['drive']      = substr($FileInfo['dirname'], 0, (strpos($FileInfo['dirname'], '/')));
		
		if(!empty($FileInfo['foldername'])) {
			exec('"'.realpath(dirname(__FILE__).'/../').'/resources/unrar/UnRAR.exe" e "'.$File.'" "'.$FileInfo['dirname'].'/"', $RarOutput, $RarReturn);
			
			if($RarReturn == 0) {
				$ExtractedFile = $RarOutput[(sizeof($RarOutput) - 2)];
				$ExtractedFile = str_replace('...         ', '', $ExtractedFile);
				$ExtractedFile = substr($ExtractedFile, 0, strpos($ExtractedFile, '  '));
				
				$MoveFileReturn = self::MoveFile($FileInfo['dirname'].'/'.$ExtractedFile);
				
				return $MoveFileReturn;
			}
			else {
				if(@rename($FileInfo['dirname'], $FileInfo['drive'].'/Unsorted/'.$FileInfo['foldername'])) {
					Hub::AddLog(EVENT.'File System', 'Success', 'Moved broken download "'.$FileInfo['dirname'].'" to "'.$FileInfo['drive'].'/Unsorted/'.$FileInfo['foldername'].'"');
				}
				else {
					Hub::AddLog(EVENT.'File System', 'Failure', 'Failed to move broken download "'.$FileInfo['dirname'].'" to "'.$FileInfo['drive'].'/Unsorted/'.$FileInfo['foldername'].'"');
				}
				
				return FALSE;
			}
		}
	}
	
	function MoveFile($File) {
		$FileInfo = pathinfo($File);
		$FileInfo['foldername'] = substr($FileInfo['dirname'], (strrpos($FileInfo['dirname'], '/') + 1));
		$FileInfo['drive']      = substr($FileInfo['dirname'], 0, (strpos($FileInfo['dirname'], '/')));
		
		if(in_array(strtoupper($FileInfo['foldername']), array('CD1', 'CD2', 'CD3'))) {
			$NewFileName = str_replace($FileInfo['drive'].'/Completed/', '', $FileInfo['dirname']);
			$NewFileName = str_replace('/', '-', $NewFileName);
			$NewFileName = $NewFileName.'.'.$FileInfo['extension'];
		}
		else {
			if(RSS::ParseRelease($FileInfo['basename'])) {
				$NewFileName = $FileInfo['basename'];
			}
			else {
				$NewFileName = $FileInfo['foldername'].'.'.$FileInfo['extension'];
			}
		}
		
		if($FileInfo['foldername'] == 'Completed') {
			$NewFileName = $FileInfo['basename'];
		}
		
		$ParsedFile = RSS::ParseRelease($NewFileName);
		
		$NewFolder = $FileInfo['drive'].'/Unsorted';
		if($ParsedFile['Type'] == 'TV') {
			$Serie = $this->PDO->query('SELECT SerieTitle, SerieTitleAlt, SerieID FROM Series WHERE SerieTitle = "'.$ParsedFile['Title'].'" OR SerieTitleAlt = "'.$ParsedFile['Title'].'"')->fetch();
			
			if(!empty($Serie['SerieTitle']) && is_dir($FileInfo['drive'].'/Media/TV/'.$Serie['SerieTitle'])) {
				$NewFolder = $FileInfo['drive'].'/Media/TV/'.$Serie['SerieTitle'];
			}
			else if(!empty($Serie['SerieTitleAlt']) && is_dir($FileInfo['drive'].'/Media/TV/'.$Serie['SerieTitleAlt'])) {
				$NewFolder = $FileInfo['drive'].'/Media/TV/'.$Serie['SerieTitleAlt'];
			}
		}
		else if($ParsedFile['Type'] == 'Movie') {
			if(is_dir($FileInfo['drive'].'/Media/Movies')) {
				$NewFolder = $FileInfo['drive'].'/Media/Movies';
			}
		}
		else {
			if(is_dir($FileInfo['drive'])) {
				$NewFolder = $FileInfo['drive'].'/Unsorted';
			}
		}
		
		if(rename($FileInfo['dirname'].'/'.$FileInfo['basename'], $NewFolder.'/'.$NewFileName)) {
			$AddLogEntry = '';
				
			if(!str_replace($FileInfo['drive'].'/Completed', '', $FileInfo['dirname'])) {
				$OldLocation = $FileInfo['basename'];
			}
			else {
				$OldLocation = str_replace($FileInfo['drive'].'/Completed/', '', $FileInfo['dirname']).'/'.$FileInfo['basename'];
			}
			
			if($FileInfo['foldername'] != 'Completed') {
				if(@Drives::RecursiveDirRemove($FileInfo['dirname'])) {
					$AddLogEntry = ' and deleted "'.$FileInfo['dirname'].'"';
				}
				else {
					$AddLogEntry = ' but failed to delete "'.$FileInfo['dirname'].'"';
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
						}
					}
				}
			}
			else if($ParsedFile['Type'] == 'Movie') {
				$WishlistUpdatePrep = $this->PDO->prepare('UPDATE Wishlist SET WishlistFile = :File WHERE WishlistTitle = :Title AND WishlistYear = :Year');
				$WishlistUpdatePrep->execute(array(':File'  => $NewFolder.'/'.$NewFileName,
				                                   ':Title' => $ParsedFile['Title'],
				                                   ':Year'  => $ParsedFile['Year']));
			}
			
			$LogEntry = 'Moved "'.$FileInfo['dirname'].'/'.$FileInfo['basename'].'" to "'.$NewFolder.'/'.$NewFileName.'"'.$AddLogEntry;
			if($NewFolder != $FileInfo['drive'].'/Unsorted') {
				Hub::AddLog(EVENT.'Extract Files', 'Success', $LogEntry, 0, 'update');
				
				return $LogEntry;
			}
			else {
				Hub::AddLog(EVENT.'Extract Files', 'Success', $LogEntry);
				
				return $LogEntry;
			}
		}
		else {
			$LogEntry = 'Failed to move '.str_replace($FileInfo['drive'].'/Completed/', '', $FileInfo['dirname']).'/'.$FileInfo['basename'].' to '.$NewFolder.'/'.$NewFileName;
			Hub::AddLog(EVENT.'File System', 'Failure', $LogEntry);
			
			return $LogEntry;
		}
	}
	
	function ExtractAndMoveAllFiles() {
		$Files = self::GetFiles();
		if(is_array($Files) && sizeof($Files)) {
			if(array_key_exists('Extract', $Files)) {
				foreach($Files['Extract'] AS $File) {
					self::ExtractFile($File);
				}
			}
			
			if(array_key_exists('Move', $Files)) {
				foreach($Files['Move'] AS $File) {
					self::MoveFile($File);
				}
			}
		}
		
		self::CleanDownloadsFolder();
	}
	
	function CleanDownloadsFolder() {
		$Drives = Drives::GetDrives();
		
		if(is_array($Drives)) {
			$FoldersDeleted = $FoldersSizeDeleted = 0;
			foreach($Drives AS $Drive) {
				$CompletedContents = array_filter(glob($Drive['DriveRoot'].'/Completed/*'), 'is_dir');
			
				foreach($CompletedContents AS $Complete) {
					$DirSize = $this->GetDirectorySize($Complete);
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