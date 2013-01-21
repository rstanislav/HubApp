<?php
/**
 * //@protected
**/
class Drives {
	private $PDO;
	
	function __construct() {
		$this->PDO = DB::Get();
	}
	
	/**
	 * @url GET /
	**/
	function DrivesAll() {
		$DrivePrep = $this->PDO->prepare('SELECT
		                                  	*
		                                  FROM
		                                  	Drives
		                                  ORDER BY
		                                  	Date');
		$DrivePrep->execute();
		$DriveRes = $DrivePrep->fetchAll();
		
		if(sizeof($DriveRes)) {
			$Data = array();
			foreach($DriveRes AS $DriveRow) {
				$FreeSpaceBytes  = $this->GetFreeSpace($DriveRow['ID'], TRUE);
				$TotalSpaceBytes = $this->GetTotalSpace($DriveRow['ID'], TRUE);
				$FreeSpace       = $this->GetFreeSpace($DriveRow['ID'], FALSE);
				$TotalSpace      = $this->GetTotalSpace($DriveRow['ID'], FALSE);
				
				$DriveRow['FreeSpaceInBytes'] = $FreeSpaceBytes;
				$DriveRow['TotalSpaceInBytes'] = $TotalSpaceBytes;
				$DriveRow['FreeSpace'] = $FreeSpace;
				$DriveRow['TotalSpace'] = $TotalSpace;
				
				$Data[] = $DriveRow;
			}
			
			return $Data;
		}
		else {
			throw new RestException(404, 'Did not find any drives in the database');
		}
	}
	
	/**
	 * @url GET /files/completed
	**/
	function GetCompletedFiles() {
		$Drives = $this->DrivesAll();
		
		$CompletedFiles = array();
		foreach($Drives AS $Drive) {
			$DriveRoot = ($Drive['IsNetwork']) ? $Drive['Share'] : $Drive['Mount'];
			$Files = RecursiveDirSearch($DriveRoot.'/Completed');
			
			foreach($Files AS $File) {
				$FileInfo = pathinfo($File);
				switch($FileInfo['extension']) {
					case 'rar':
						$UniqueRarFile = FALSE;
					
						preg_match('/([^.]+)\.([^.]+)$/', $File, $FileMatches);
					
						if(!stristr($FileMatches[1], '.part')) {
							$UniqueRarFile = TRUE;
						}
						if(!stristr($FileMatches[1], ' part')) {
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
					case 'mpg':
					case 'mpeg':
					case 'mkv':
					case 'avi':
					case 'wmv':
						if(GetFileSize($File) >= (1024 * 1024 * 150)) {
							$CompletedFiles['Move'][] = $File;
						}
					break;
				}
			}
		}
		
		if(!sizeof($CompletedFiles)) {
			throw new RestException(404, 'Did not find any files matching your criteria');
		}
		else {
			return $CompletedFiles;
		}
	}

	/**
	 * @url POST /files/move
	**/
	function MoveFile() {
		if(!filter_has_var(INPUT_POST, 'File')) {
			throw new RestException(412, 'You need to specify a file ("File")');
		}
		
		$File = $_POST['File'];
		
		if(!is_file($File)) {
			throw new RestException(404, 'File "'.$File.'" does not exist');
		}
		
		$Drive = substr($File, 0, strpos($File, '/'));
		
		$ParsedInfo = ParseRelease(basename(dirname($File)));
			
		if(!$ParsedInfo) {
			$ParsedInfo = ParseRelease(basename($File));
						
			if(!$ParsedInfo) {
				$ParsedInfo = ParseRelease(basename(dirname($File)));
							
				if(!$ParsedInfo) {
					$ParsedInfo = ParseRelease(basename(dirname(dirname($File))));
							
					if(!$ParsedInfo) {
						$ParsedInfo = ParseRelease(basename(dirname(dirname(dirname($File)))));

						if(!$ParsedInfo) {
							$ParsedInfo = ParseRelease(basename(dirname(dirname(dirname(dirname($File))))));
									
							if(!$ParsedInfo) {
								$ParsedInfo = ParseRelease(basename(dirname(dirname(dirname(dirname(dirname($File)))))));
							}
						}
					}
				}
			}
		}
		
		if(filter_has_var(INPUT_POST, 'NewLocation')) {
			$NewLocation = $_POST['NewLocation'];
			
			if(strpos($NewLocation, '/') != strlen($NewLocation)) {
				$NewLocation = $NewLocation.'/';
			}
			
			if(!is_dir($NewLocation)) {
				throw new RestException(412, 'Directory "'.$NewLocation.'" does not exist');
			}
		}
		else {
			if($ParsedInfo) {
				switch($ParsedInfo['Type']) {
					case 'TV':
						$Serie = $this->PDO->query('SELECT
						                            	Title,
						                            	TitleAlt,
						                            	ID
						                            FROM
						                            	Series
						                            WHERE
						                            	Title = "'.$ParsedInfo['Title'].'"
						                            OR
						                            	TitleAlt = "'.$ParsedInfo['Title'].'"')->fetch();
									
						if(is_array($Serie)) {
							$NewLocation = $Drive.'/Media/TV/'.$Serie['Title'].'/';
							
							if(!is_dir($NewLocation)) {
								$NewLocation = $Drive.'/Media/TV/'.$Serie['TitleAlt'].'/';
										
								if(!is_dir($NewLocation)) {
									$NewLocation = $Drive.'/Unsorted/';
								}
							}
						}
						else {
							$NewLocation = $Drive.'/Unsorted/';
						}
									
						$NewFile = $ParsedInfo['Title'].'.S'.sprintf('%02s', $ParsedInfo['Episodes'][0][0]).'E'.sprintf('%02s', $ParsedInfo['Episodes'][0][1]);
									
						if(sizeof($ParsedInfo['Episodes']) > 1) {
							for($i = 1; $i < sizeof($ParsedInfo['Episodes']); $i++) {
								$NewFile .= '-E'.$ParsedInfo['Episodes'][$i][1];
							}
						}
									
						$NewFile .= '.'.$ParsedInfo['Quality'].'.'.pathinfo($File, PATHINFO_EXTENSION);
									
						foreach($ParsedInfo['Episodes'] AS $ParsedEpisode) {
							try {
								$EpisodeUpdatePrep = $this->PDO->prepare('UPDATE
										                              	  	Episodes
										                                  SET
										                                    File = :File
										                                  WHERE
										                                    SeriesKey = :SerieID
										                                  AND
										                                    Season = :Season
										                                  AND
										                                    Episode = :Episode');
										                                          	
								$EpisodeUpdatePrep->execute(array(':File'    => $NewLocation.$NewFile,
											                      ':SerieID' => $Serie['ID'],
															      ':Season'  => $ParsedEpisode[0],
															      ':Episode' => $ParsedEpisode[1]));
							}
							catch(PDOException $e) {
								throw new RestException(400, 'MySQL: '.$e->getMessage());
							}
						}
					break;
					
					case 'Movie':
						$NewLocation = $Drive.'/Media/Movies/';
						$NewFile     = $ParsedInfo['Title'].'.'.$ParsedInfo['Year'].'.'.$ParsedInfo['Quality'].'.'.pathinfo($File, PATHINFO_EXTENSION);
						
						try {
							$WishlistUpdatePrep = $this->PDO->prepare('UPDATE
									                              	   	Wishlist
									                                   SET
									                                   	DownloadDate = :Date,
									                                   	IsFileGone = 0,
									                                    File = :File
									                                   WHERE
									                                    Title = :Title
									                                   AND
									                                    Year = :Year');
									                                          	
							$WishlistUpdatePrep->execute(array(':Date'  => time(),
							                                   ':File'  => $NewLocation.str_replace(' ', '.', $NewFile),
										                       ':Title' => $ParsedInfo['Title'],
														       ':Year'  => $ParsedInfo['Year']));
						}
						catch(PDOException $e) {
							throw new RestException(400, 'MySQL: '.$e->getMessage());
						}
					break;
					
					default:
						$NewLocation = $Drive.'/Unsorted/';
						$NewFile     = basename($File);
				}
			}
			else {
				$NewLocation = $Drive.'/Unsorted/';
				$NewFile     = basename($File);
			}
		}
		
		$NewFile = str_replace(' ', '.', $NewFile);
		
		if(filter_has_var(INPUT_POST, 'debug')) {
			throw new RestException(200, $File.' -> '.$NewLocation.$NewFile);
		}
		else {
			if(@rename($File, $NewLocation.$NewFile)) {
				$LogEntry = 'Moved "'.$File.'" to "'.$NewLocation.$NewFile.'"';
				
				if($NewLocation != $Drive.'/Unsorted') {
					AddLog(EVENT.'Drives', 'Success', $LogEntry, 0, 'update');
				}
				else {
					AddLog(EVENT.'Drives', 'Success', $LogEntry);
				}
				
				throw new RestException(200, $LogEntry);
			}
			else {
				$LogEntry = 'Failed to move "'.$File.'" to "'.$NewLocation.$NewFile.'"';
				
				AddLog(EVENT.'Drives', 'Failure', $LogEntry);
				throw new RestException(400, $LogEntry);
			}
		}
	}

	/**
	 * @url POST /files/extract
	**/
	function ExtractFile() {
		if(!filter_has_var(INPUT_POST, 'File')) {
			throw new RestException(412, 'You need to specify a file ("File")');
		}
		
		$File = $_POST['File'];
		
		if(!is_file($File)) {
			throw new RestException(404, 'File "'.$File.'" does not exist');
		}
		
		$Drive   = substr($File, 0, strpos($File, '/'));
		
		if(class_exists('RarArchive')) {
			$RARFile = RarArchive::open($File);
		}
		else {
			throw new RestException(500, 'RarArchive (php_rar.dll) is not loaded');
		}
		
		if($RARFile === FALSE) {
			throw new RestException(400, 'Unable to open "'.$File.'"');
		}
					
		foreach($RARFile->getEntries() AS $Entry) {
			$ParentDirectory = basename(dirname($File));
			
			if(preg_match('/CD([0-9]+)/i', $ParentDirectory, $Matches)) {
				$ParentDirectory = basename(dirname(dirname($File))).'-'.$Matches[0];
			}
			
			$ParsedInfo = ParseRelease($ParentDirectory);
			
			if(!$ParsedInfo) {
				$ParsedInfo = ParseRelease($Entry->getName());
						
				if(!$ParsedInfo) {
					$ParsedInfo = ParseRelease(basename(dirname($File)));
							
					if(!$ParsedInfo) {
						$ParsedInfo = ParseRelease(basename(dirname(dirname($File))));
							
						if(!$ParsedInfo) {
							$ParsedInfo = ParseRelease(basename(dirname(dirname(dirname($File)))));

							if(!$ParsedInfo) {
								$ParsedInfo = ParseRelease(basename(dirname(dirname(dirname(dirname($File))))));
									
								if(!$ParsedInfo) {
									$ParsedInfo = ParseRelease(basename(dirname(dirname(dirname(dirname(dirname($File)))))));
								}
							}
						}
					}
				}
			}
						
			if($ParsedInfo) {
				switch($ParsedInfo['Type']) {
					case 'TV':		
						$Serie = $this->PDO->query('SELECT
						                            	Title,
						                            	TitleAlt,
						                            	ID
						                            FROM
						                            	Series
						                            WHERE
						                            	Title = "'.$ParsedInfo['Title'].'"
						                            OR
						                            	TitleAlt = "'.$ParsedInfo['Title'].'"')->fetch();
									
						if(is_array($Serie)) {
							$NewLocation = $Drive.'/Media/TV/'.$Serie['Title'].'/';
							
							if(!is_dir($NewLocation)) {
								$NewLocation = $Drive.'/Media/TV/'.$Serie['TitleAlt'].'/';
										
								if(!is_dir($NewLocation)) {
									$NewLocation = $Drive.'/Unsorted/';
								}
							}
						}
						else {
							$NewLocation = $Drive.'/Unsorted/';
						}
									
						$NewFile = $ParsedInfo['Title'].'.S'.sprintf('%02s', $ParsedInfo['Episodes'][0][0]).'E'.sprintf('%02s', $ParsedInfo['Episodes'][0][1]);
									
						if(sizeof($ParsedInfo['Episodes']) > 1) {
							for($i = 1; $i < sizeof($ParsedInfo['Episodes']); $i++) {
								$NewFile .= '-E'.$ParsedInfo['Episodes'][$i][1];
							}
						}
									
						$NewFile .= '.'.$ParsedInfo['Quality'].'.'.pathinfo($Entry->getName(), PATHINFO_EXTENSION);
									
						foreach($ParsedInfo['Episodes'] AS $ParsedEpisode) {
							try {
								$EpisodeUpdatePrep = $this->PDO->prepare('UPDATE
										                              	  	Episodes
										                                  SET
										                                    File = :File
										                                  WHERE
										                                    SeriesKey = :SerieID
										                                  AND
										                                    Season = :Season
										                                  AND
										                                    Episode = :Episode');
										                                          	
								$EpisodeUpdatePrep->execute(array(':File'    => $NewLocation.str_replace(' ', '.', $NewFile),
											                      ':SerieID' => $Serie['ID'],
															      ':Season'  => $ParsedEpisode[0],
															      ':Episode' => $ParsedEpisode[1]));
							}
							catch(PDOException $e) {
								throw new RestException(400, (isset($_GET['debug'])) ? 'MySQL: '.$e->getMessage() : 'MySQL');
							}
						}
					break;
								
					case 'Movie':
						$NewLocation = $Drive.'/Media/Movies/';
						$NewFile     = $ParsedInfo['Title'].'.'.$ParsedInfo['Year'].'.'.$ParsedInfo['Quality'].'.'.pathinfo($Entry->getName(), PATHINFO_EXTENSION);
						
						try {
							$WishlistUpdatePrep = $this->PDO->prepare('UPDATE
									                              	   	Wishlist
									                                   SET
									                                   	DownloadDate = :Date,
									                                   	IsFileGone = 0,
									                                    File = :File
									                                   WHERE
									                                    Title = :Title
									                                   AND
									                                    Year = :Year');
									                                          	
							$WishlistUpdatePrep->execute(array(':Date'  => time(),
							                                  ':File'  => $NewLocation.str_replace(' ', '.', $NewFile),
										                      ':Title' => $ParsedInfo['Title'],
														      ':Year'  => $ParsedInfo['Year']));
						}
						catch(PDOException $e) {
							throw new RestException(400, 'MySQL: '.$e->getMessage());
						}
					break;
								
					default:
						$NewLocation = $Drive.'/Unsorted/';
						$NewFile     = $Entry->getName();
				}
							
				$NewFile = str_replace(' ', '.', $NewFile);
			}
			else {
				$NewLocation = $Drive.'/Unsorted/';
				$NewFile   = $Entry->getName();
			}
			
			if(filter_has_var(INPUT_POST, 'debug')) {
				$RARFile->close();
				
				throw new RestException(200, $File.' -> '.$NewLocation.$NewFile);
			}
			else {
				if($Entry->extract(FALSE, $NewLocation.$NewFile)) {
					$RARFile->close();
					
					$LogEntry = 'Extracted "'.$File.'" to "'.$NewLocation.$NewFile.'"';
					if(@RecursiveDirRemove(dirname($File))) {
						$LogEntry .= ' and deleted "'.dirname($File).'"';
					}
					
					if($NewLocation != $Drive.'/Unsorted') {
						AddLog(EVENT.'Drives', 'Success', $LogEntry, 0, 'update');
					}
					else {
						AddLog(EVENT.'Drives', 'Success', $LogEntry);
					}
					
					throw new RestException(200, $LogEntry);
				}
				else {
					$RARFile->close();
					$LogEntry = 'Failed to extract "'.$File.'" to "'.$NewLocation.$NewFile.'"';
					
					AddLog(EVENT.'Drives', 'Failure', $LogEntry);
					throw new RestException(400, $LogEntry);
				}
			}
		}
	}
	
	/**
	 * @url GET /clean
	**/
	function CleanDownloadsFolder() {
		$Drives = $this->DrivesAll();
		
		$FoldersDeleted = $FoldersSizeDeleted = 0;
		$LogEntry = '';
		foreach($Drives AS $Drive) {
			$DriveRoot = ($Drive['IsNetwork']) ? $Drive['Share'] : $Drive['Mount'];
			$CompletedContents = new RecursiveIteratorIterator(new IgnorantRecursiveDirectoryIterator($DriveRoot.'/Completed'), RecursiveIteratorIterator::SELF_FIRST);
			
			foreach($CompletedContents AS $Name => $Object){
				if(is_dir($Name)) {
					try {
						$DirSize = GetDirectorySize($Name);
					}
					catch(UnexpectedValueException $e) {
						break;
					}
					
					if($DirSize <= (1024 * 1024 * 100)) {
						if(@RecursiveDirRemove($Name)) {
							$FoldersDeleted++;
							$FoldersSizeDeleted += $DirSize;
						}
					}
					else {
						$Name = str_replace('\\', '/', $Name);
						
						$NewLocation = $DriveRoot.'/Unsorted/'.str_replace($DriveRoot.'/Completed/', '', $Name);
						if(@rename($Name, $NewLocation)) {
							$LogEntry .= 'Moved "'.$Name.'" to "'.$DriveRoot.'/Unsorted/" because it has files worth keeping'."\n";
						}
					}
				}
			}
		}
		
		if($FoldersDeleted) {
			$LogEntry = $LogEntry.'Cleaned Downloads directory. Deleted '.$FoldersDeleted.' folders totaling '.BytesToHuman($FoldersSizeDeleted);
			
			AddLog(EVENT.'Drives', 'Success', $LogEntry);
			throw new RestException(200, $LogEntry);
		}
	}
	
	/**
	 * @url POST /check
	**/
	function CheckDrive() {
		echo $_POST['IsNetwork'];
		
		$RequiredParams = array('Share', 'User', 'Pass', 'Mount', 'IsNetwork');
		
		$PostErr = FALSE;
		foreach($RequiredParams AS $Param) {
			if(!filter_has_var(INPUT_POST, $Param) || empty($_POST[$Param])) {
				$PostErr = TRUE;
			}
		}
		
		if($PostErr) {
			throw new RestException(412, 'Required parameters are "'.implode(', ', $RequiredParams).'"');
		}
	}
	
	/**
	 * @url POST /
	**/
	function AddDrive($Share, $User, $Password, $Mount, $IsNetwork) {
		if(empty($Share) || empty($User) || empty($Password) || empty($Mount)) {
			throw new RestException(412, 'Invalid request. Required parameters are "Share", "User", "Password", "Mount"');
		}
		
		$LogEntry = '';
		
		$XBMCDataFolder = GetSetting('XBMCDataFolder');
		if(!is_file($XBMCDataFolder.'/userdata/sources.xml')) {
			throw new RestException(404, 'File "'.$XBMCDataFolder.'/userdata/sources.xml" does not exist');
		}
		
		$DriveRoot = ($IsNetwork) ? $Share : $Mount;
		
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
				$LogEntry .= 'Created folders "'.implode(', ', $LogFolders).'" on "'.$Share.' ('.$Mount.')"'."\n";
			}
		}
		
		try {
			$DriveAddPrep = $this->PDO->prepare('INSERT INTO
			                                     	Drives
			                                     		(ID,
			                                     		Date,
			                                     		Share,
			                                     		User,
			                                     		Password,
			                                     		Mount,
			                                     		IsActive,
			                                     		IsNetwork)
			                                     	VALUES
			                                     		(NULL,
			                                     		:Date,
			                                     		:Share,
			                                     		:User,
			                                     		:Password,
			                                     		:Mount,
			                                     		:Active,
			                                     		:Network)');
			                                     		
			$DriveAddPrep->execute(array(':Date'     => time(),
										 ':Share'    => $Share,
										 ':User'     => $User,
										 ':Password' => $Password,
										 ':Mount'    => $Mount,
										 ':Active'   => 0,
										 ':Network'  => $IsNetwork));
		}
		catch(PDOException $e) {
			throw new RestException(412, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry .= 'Added "'.$Share.' ('.$Mount.')" to the database'."\n";
		
		$DriveShareCred = str_replace('\\', '/', $Share);
		if(!empty($User) && !empty($Password)) {
			$DriveShareCred = str_replace('//', '//'.$User.':'.$Password.'@', $Share);
		}
		
		$DocObj = new DOMDocument();
		$DocObj->load($XBMCDataFolder.'/userdata/sources.xml');
	
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
				
				$LogSources[] = 'smb:'.$Share.'/'.'Media'.'/'.$Name.'/';
			}
		}
		
		$DocObj->save($XBMCDataFolder.'/userdata/sources.xml');
		
		if(sizeof($LogSources)) {
			$LogEntry .= 'Added "'.implode(', ', $LogSources).'" to '.$XBMCDataFolder.'/userdata/sources.xml'."\n";
		}
		
		$XBMCUpdateErr = FALSE;
		try {
			$XBMCObj = new XBMC;
			$XBMCObj->Connect();
			$XBMCObj->LibraryUpdate();
		}
		catch(XBMC_RPC_Exception $e) {
			$LogEntry .= 'Failed to update XBMC library: '.$e->getMessage()."\n";
			$XBMCUpdateErr = TRUE;
		}
		catch(RestException $e) {
		}
		
		if(!$XBMCUpdateErr) {
			$LogEntry .= 'Updated XBMC library';
		}
		
		AddLog(EVENT.'Drives', 'Success', $LogEntry);
		throw new RestException(201, $LogEntry);
	}
	
	/**
	 * @url DELETE /:ID
	**/
	function DeleteDrive($ID) {
		try {
			$DrivePrep = $this->PDO->prepare('SELECT
												*
											FROM
												Drives
											WHERE
												ID = :ID');
												
			$DrivePrep->execute(array(':ID' => $ID));
			
			if($DrivePrep->rowCount()) {
				$Drive = $DrivePrep->fetch();
			}
			else {
				throw new RestException(404);
			}
		}
		catch(PDOException $e) {
			throw new RestException(412, 'MySQL: '.$e->getMessage());
		}
		
		$DriveShareCred = str_replace('\\', '/', $Drive['Share']);
		if(!empty($Drive['User']) && !empty($Drive['Password'])) {
			$DriveShareCred = str_replace('//', '//'.$Drive['User'].':'.$Drive['Password'].'@', $Drive['Share']);
		}
	
		if($Drive['IsActive']) {
			$this->DetermineNewActiveDrive();
		}
		
		$XBMCDataFolder = GetSetting('XBMCDataFolder');
		if(!is_file($XBMCDataFolder.'/userdata/sources.xml')) {
			throw new RestException(404, $XBMCDataFolder.'/userdata/sources.xml does not exist');
		}
			
		$DocObj = new DOMDocument();
		$DocObj->load($XBMCDataFolder.'/userdata/sources.xml');

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
					
					$LogPaths[] = 'smb:'.$Drive['Share'].'/Media/'.$Name.'/';
				}
			}
		}
	
		$DocObj->save($XBMCDataFolder.'/userdata/sources.xml');
		
		if(sizeof($LogPaths)) {
			AddLog(EVENT.'XBMC', 'Success', 'Removed "'.implode(', ', $LogPaths).'" from '.$XBMCDataFolder.'/userdata/sources.xml');
		}
		
		try {
			$DriveDeletePrep = $this->PDO->prepare('DELETE FROM Drives WHERE ID = :ID');
			$DriveDeletePrep->execute(array(':ID' => $ID));
		}
		catch(PDOException $e) {
			throw new RestException(412, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = 'Deleted "'.$Drive['Share'].' ('.$Drive['Mount'].')" from database';
		
		AddLog(EVENT.'Drives', 'Success', $LogEntry);
		throw new RestException(200, $LogEntry);
	}
	
	/**
	 * @url GET /active
	**/
	function GetActiveDrive() {
		try {
			$DrivePrep = $this->PDO->prepare('SELECT
												*
											  FROM
											  	Drives
											  WHERE
											  	IsActive = 1');
											  	
			$DrivePrep->execute();
			$DriveRow = $DrivePrep->fetch();
			
			if(sizeof($DriveRow)) {
				return $DriveRow;
			}
			else {
				throw new RestException(404, 'Did not find a drive in the database marked as active');
			}
		}
		catch(PDOException $e) {
			throw new RestException(412, 'MySQL: '.$e->getMessage());
		}
	}
	
	/**
	 * @url GET /active/check
	**/
	function CheckActiveDrive() {
		try {
			$Drive = $this->GetActiveDrive();
		}
		catch(RestException $e) {
			switch($e->getCode()) {
				case 404:
					$this->DetermineNewActiveDrive();
				break;
				
				default:
					throw new RestException();
			}
		}
		
		$UTorrentObj = new UTorrent;
		$UTorrentObj->Connect();
		
		$DriveRoot = ($Drive['IsNetwork']) ? $Drive['Share'] : $Drive['Mount'];
		
		$FreeSpace  = $this->GetFreeSpace($Drive['ID'],  TRUE);
		$TotalSpace = $this->GetTotalSpace($Drive['ID'], TRUE);
		
		if(($FreeSpace / 1024 / 1024 / 1024) <= GetSetting('MinimumDiskSpaceRequired')) {
			$this->DetermineNewActiveDrive();
		}
		else {
			if(is_dir($DriveRoot.'/Downloads')) {
				$UTorrentObj->SetSetting('dir_active_download', $Drive['Mount'].'/Downloads');
			}
			else {
				if(@!mkdir($DriveRoot.'/Downloads')) {
					$LogEntry = 'Incomplete Downloads folder: "'.$Drive['Mount'].'/Downloads" does not exist and could not be created';
					AddLog(EVENT.'Drives', 'Failure', $LogEntry);
					
					throw new RestException(400, $LogEntry);
				}
				else {
					AddLog(EVENT.'Drives', 'Success', 'Created "Incomplete Downloads" folder: "'.$Drive['Mount'].'/Downloads"');
				}
			}
			
			if(is_dir($DriveRoot.'/Completed')) {
				$UTorrentObj->SetSetting('dir_completed_download', $Drive['Mount'].'/Completed');
			}
			else {
				if(@!mkdir($DriveRoot.'/Completed')) {
					$LogEntry = 'Completed Downloads folder: "'.$Drive['Mount'].'/Completed" does not exist and could not be created';
					AddLog(EVENT.'uTorrent', 'Failure', $LogEntry);
					
					throw new RestException(400, $LogEntry);
				}
				else {
					AddLog(EVENT.'Drives', 'Success', 'Created "Completed Downloads" folder: "'.$Drive['Mount'].'/Completed"');
				}
			}
			
			$UTorrentWatchFolder = GetSetting('UTorrentWatchFolder');
			if(is_dir($UTorrentWatchFolder)) {
				$UTorrentObj->SetSetting('dir_autoload', $UTorrentWatchFolder);
			}
			else {
				if(@!mkdir($UTorrentWatchFolder)) {
					$LogEntry = 'uTorrent Watch folder: "'.$UTorrentWatchFolder.'" does not exist and could not be created';
					AddLog(EVENT.'Drives', 'Failure', $LogEntry);
					
					throw new RestException(400, $LogEntry);
				}
				else {
					AddLog(EVENT.'Drives', 'Success', 'Created "uTorrent Watch folder" folder: "'.$UTorrentWatchFolder.'"');
				}
			}
		}
		
		throw new RestException(200, '"'.$DriveRoot.'" is active drive');
	}
	
	/**
	 * @url GET /active/:ID
	**/
	function SetActiveDrive($ID) {
		try {
			$UpdateDrivePrep = $this->PDO->prepare('UPDATE
			                                        	Drives
			                                        SET
			                                        	IsActive = 0');
			                                        	
			$UpdateDrivePrep->execute();
		}
		catch(PDOException $e) {
			throw new RestException(412, 'MySQL: '.$e->getMessage());
		}
		
		try {
			$UpdateDrivePrep = $this->PDO->prepare('UPDATE
			                                        	Drives
			                                        SET
			                                        	IsActive = 1
			                                        WHERE
			                                        	ID = :ID');
			                                        	
			$UpdateDrivePrep->execute(array(':ID' => $ID));
		}
		catch(PDOException $e) {
			throw new RestException(412, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = '';
		try {
			$DrivePrep = $this->PDO->prepare('SELECT
				                              	*
				                              FROM
				                              	Drives
				                              WHERE
				                              	IsActive = 1');
				                              	
			$DrivePrep->execute();
			$Drive = $DrivePrep->fetch();
			
			if(sizeof($Drive)) {
				$UTorrentObj = new UTorrent;
				$UTorrentObj->Connect();
				
				$DriveRoot = ($Drive['IsNetwork']) ? $Drive['Share'] : $Drive['Mount'];
				
				$this->ActiveDrive = $Drive['ID'];
				
				if(!is_dir($DriveRoot.'/Downloads')) {
					if(@!mkdir($DriveRoot.'/Downloads')) {
						throw new RestException(400, 'Unable to create directory "'.$DriveRoot.'/Downloads"');
					}
				}
				
				if(!is_dir($DriveRoot.'/Completed')) {
					if(@!mkdir($DriveRoot.'/Completed')) {
						throw new RestException(400, 'Unable to create directory "'.$DriveRoot.'/Completed"');
					}
				}
				
				$UTorrentObj->SetSetting('dir_active_download',    $Drive['Mount'].'/Downloads');
				$UTorrentObj->SetSetting('dir_completed_download', $Drive['Mount'].'/Completed');
				
				$LogEntry .= 'Set "'.$Drive['Share'].' ('.$Drive['Mount'].')" as active drive'."\n";
				
				AddLog(EVENT.'Drives', 'Success', $LogEntry);
			}
			else {
				throw new RestException(404, 'Did not find any drives in the database flagged as "default"');
			}
		}
		catch(PDOException $e) {
			throw new RestException(412, 'MySQL: '.$e->getMessage());
		}
	}
	
	/**
	 * @url GET /:ID/:FolderPath
	**/
	function GetFolderContents($ID, $FolderPath = '') {
		echo $ID.' '.$FolderPath;
		echo urlencode('Media/TV');
	}
	
	/**
	 * Internal functions
	**/
	function GetDriveByID($ID) {
		try {
			$DrivePrep = $this->PDO->prepare('SELECT
												*
											  FROM
											  	Drives
											  WHERE
											  	ID = :ID');
											  	
			$DrivePrep->execute(array(':ID' => $ID));
			
			if($DrivePrep->rowCount()) {
				return $DrivePrep->fetch();
			}
			else {
				throw new RestException(404, 'Did not find a drive in the database with the ID "'.$ID.'"');
			}
		}
		catch(PDOException $e) {
			throw new RestException(412, 'MySQL: '.$e->getMessage());
		}
	}
	
	function GetMovieFiles() {
		$Drives = $this->DrivesAll();
		
		$MovieFiles = array();
		foreach($Drives AS $Drive) {
			$DriveRoot = ($Drive['IsNetwork']) ? $Drive['Share'] : $Drive['Mount'];
			$Files[$DriveRoot] = RecursiveDirSearch($DriveRoot.'/Media/Movies/');
			
			if(sizeof($Files[$DriveRoot])) {
				$MovieFiles = array_merge((array)$MovieFiles, (array)$Files[$DriveRoot]);
			}
		}
		
		if(sizeof($MovieFiles)) {
			return $MovieFiles;
		}
		else {
			throw new RestException(404, 'Did not find any movie files on your drives');
		}
	}
	
	function DetermineNewActiveDrive() {
		$Drives = $this->DrivesAll();
		
		foreach($Drives AS $Drive) {
			$FreeSpace  = $this->GetFreeSpace($Drive['ID'],  TRUE);
			$TotalSpace = $this->GetTotalSpace($Drive['ID'], TRUE);
			
			if(($FreeSpace / 1024 / 1024 / 1024) > GetSetting('MinimumDiskSpaceRequired')) {
				$this->SetActiveDrive($Drive['ID']);
				
				break;
			}
		}
	}
	
	function GetFreeSpace($DriveID, $AsBytes = FALSE) {
		$Drive = $this->GetDriveByID($DriveID);
		$DriveRoot = ($Drive['IsNetwork']) ? $Drive['Share'] : $Drive['Mount'];
		$FreeSpace = @disk_free_space($DriveRoot);
		
		if($AsBytes) {
			return $FreeSpace;
		}
		else {
			return BytesToHuman($FreeSpace);
		}
	}
	
	function GetTotalSpace($DriveID, $AsBytes = FALSE) {
		$Drive = $this->GetDriveByID($DriveID);
		$DriveRoot = ($Drive['IsNetwork']) ? $Drive['Share'] : $Drive['Mount'];
		$TotalSpace = @disk_total_space($DriveRoot);
		
		if($AsBytes) {
			return $TotalSpace;
		}
		else {
			return BytesToHuman($TotalSpace);
		}
	}
	
	function GetNetworkLocation($Location) {
		if(strpos($Location, '/') != 0) {
			$Drive = $this->PDO->query('SELECT * FROM Drives WHERE Mount LIKE "'.substr($Location, 0, strpos($Location, '/')).'%" LIMIT 1')->fetch();
	
			if(is_array($Drive)) {
				return str_replace($Drive['Mount'], $this->DriveShareCredentials($Drive['Share'], $Drive['User'], $Drive['Password']), $Location);
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
			
			$Drives = $this->DrivesAll();
			
			if(is_array($Drives)) {
				foreach($Drives AS $Drive) {
					if(strstr($Location, $Drive['Share'])) {
						return str_replace($Drive['Share'], $Drive['Mount'], $Location);
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
	
	function DriveShareCredentials($Share, $User = '', $Pass = '') {
		$Share = str_replace('\\', '/', $Share);
		
		if(!empty($User) && !empty($Pass)) {
			$Share = str_replace('//', '//'.$User.':'.$Pass.'@', $Share);
		}
		
		return $Share;
	}
}
?>