<?php
/**
 * //@protected
**/
class Series {
	private $PDO;
	private $TheTVDB = null;
	
	function __construct() {
		$this->PDO = DB::Get();
	}
	
	function Connect() {
		if(!is_object($this->TheTVDB)) {
			require_once APP_PATH.'/api/libraries/api.thetvdb.php';
			
			$TheTVDBAPIKey = GetSetting('TheTVDBAPIKey');
				
			try {
				$this->TheTVDB = new TheTVDBAPI($TheTVDBAPIKey);
				
				$this->TheTVDB->SetPreviousUpdateTime(time()); // Timestamp
				$this->TheTVDB->SetLanguage('en');
			}
			catch(Exception $e) {
				throw new RestException(400, 'Unable to connect to TheTVDB API: '.$e->getMessage());
			}
		}
	}
	
	/**
	 * @url GET /search/:SearchStr
	**/
	function SearchTheTVDB($SearchStr) {
		$this->Connect();
		
		try {
			$Series = $this->TheTVDB->GetSeries($SearchStr);
		}
		catch(Exception $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		if(is_array($Series) && sizeof($Series)) {
			return json_decode(json_encode($Series), 1);
		}
		else {
			throw new RestException(404, 'Did not find any TV series matching your criteria "'.$SearchStr.'"');
		}
	}
	
	/**
	 * @url GET /add/:TheTVDBID
	**/
	function AddSerie($TheTVDBID) {
		$this->Connect();
		
		try {
			$Serie = $this->PDO->query('SELECT
			                            	ID
			                            FROM
			                            	Series
			                            WHERE
			                            	TheTVDBID = "'.$TheTVDBID.'"')->fetch();
			
			if($Serie) {
				throw new RestException(417, 'A serie already exists in the database with this TheTVDBID: "'.$TheTVDBID.'"');
			}
		}
		catch(PDOException $e) {
			throw new RestException();
		}
		
		try {
			$SeriesInfo = $this->TheTVDB->GetSeriesInfo($TheTVDBID);
		}
		catch(Exception $e) {
			throw new RestException(400, 'TheTVDB: '.$e->getMessage());
		}
		
		$LogEntry = '';
		
		$EpisodesAdded = 0;
		foreach($SeriesInfo AS $Serie) {
			if($Serie->SeriesName) {
				$SerieTitle        = $Serie->SeriesName;
				$Serie->SeriesName = str_replace(array(':', '\'', '(', ')', '*'), '', $Serie->SeriesName);
				$Serie->Genre      = str_replace('|', ', ', trim($Serie->Genre, '|'));
			
				$DrivesObj = new Drives;
				$Drives = $DrivesObj->DrivesAll();
				if(is_array($Drives)) {
					foreach($Drives AS $Drive) {
						$DriveRoot = ($Drive['IsNetwork']) ? $Drive['Share'] : $Drive['Mount'];
						
						if(!is_dir($DriveRoot.'/Media/TV/'.$Serie->SeriesName)) {
							if(!mkdir($DriveRoot.'/Media/TV/'.$Serie->SeriesName)) {
								$LogEntry .= 'Unable to create "'.$DriveRoot.'/Media/TV/'.$Serie->SeriesName.'"'."\n";
							}
						}
					}
				}
				else {
					$LogEntry .= 'Unable to get drive data from the database while adding "'.$Serie->SeriesName.'"'."\n";
				}
				
				try {
					$SerieAddPrep = $this->PDO->prepare('INSERT INTO
					                                     	Series
					                                     		(ID,
					                                     		Date,
					                                     		Title,
					                                     		Plot,
					                                     		ContentRating,
					                                     		IMDBID,
					                                     		Rating,
					                                     		RatingCount,
					                                     		Banner,
					                                     		FanArt,
					                                     		Poster,
					                                     		FirstAired,
					                                     		AirDay,
					                                     		AirTime,
					                                     		Runtime,
					                                     		Network,
					                                     		Status,
					                                     		Genre,
					                                     		TheTVDBID)
					                                     	VALUES
					             	                            (NULL,
					             	                         	:Date,
					             	                         	:Title,
					             	                         	:Plot,
					             	                         	:ContentRating,
					             	                         	:IMDBID,
					             	                         	:Rating,
					             	                         	:RatingCount,
					             	                         	:Banner,
					             	                         	:FanArt,
					             	                         	:Poster,
					             	                         	:FirstAired,
					             	                         	:AirDay,
					             	                         	:AirTime,
					             	                         	:Runtime,
					             	                         	:Network,
					             	                         	:Status,
					             	                         	:Genre,
					             	                         	:TheTVDBID)');
					             	                         	
					$SerieAddPrep->execute(array(':Date'          => time(),
												 ':Title'         => $Serie->SeriesName,
												 ':Plot'          => $Serie->Overview,
												 ':ContentRating' => $Serie->ContentRating,
												 ':IMDBID'        => $Serie->IMDB_ID,
												 ':Rating'        => $Serie->Rating,
												 ':RatingCount'   => $Serie->RatingCount,
												 ':Banner'        => $Serie->banner,
												 ':FanArt'        => $Serie->fanart,
												 ':Poster'        => $Serie->poster,
												 ':FirstAired'    => strtotime($Serie->FirstAired),
												 ':AirDay'        => $Serie->Airs_DayOfWeek,
												 ':AirTime'       => $Serie->Airs_Time,
												 ':Runtime'       => $Serie->Runtime.' minutes',
												 ':Network'       => $Serie->Network,
												 ':Status'        => $Serie->Status,
												 ':Genre'         => $Serie->Genre,
												 ':TheTVDBID'     => $Serie->id));
				}
				catch(PDOException $e) {
					throw new RestException(400, 'MySQL: '.$e->getMessage());
				}
				
				$EpisodeAirTime = $Serie->Airs_Time;
				$TimeZoneOffset = ($Serie->Network == 'BBC Two') ? '+1 hour' : '+6 hours';
			}
			else {
				try {
					$SerieAdded = $this->PDO->query('SELECT
					                                 	ID
					                                 FROM
					                                 	Series
					                                 WHERE
					                                 	TheTVDBID = "'.$Serie->seriesid.'"')->fetch();
				}
				catch(PDOException $e) {
					throw new RestException(400, 'MySQL: '.$e->getMessage());
				}
				
				$SerieID = $SerieAdded['ID'];
				$Date = date('d.m.Y', strtotime($Serie->FirstAired));
				$Time = date('H:i',   strtotime($EpisodeAirTime));
				$EpisodeAirDate =     strtotime($TimeZoneOffset, strtotime($Date.' '.$Time));
				$this->DownloadPoster($SerieID);
				
				try {
					$EpisodeAddPrep = $this->PDO->prepare('INSERT INTO
					                                       	Episodes
					                                       		(ID,
					                                       		Date,
					                                       		Season,
					                                       		Episode,
					                                       		Title,
					                                       		Plot,
					                                       		Rating,
					                                       		RatingCount,
					                                       		Banner,
					                                       		AirDate,
					                                       		SeriesKey,
					                                       		TheTVDBID)
					                                       	VALUES
					                                       		(NULL,
					                                       		:Date,
					                                       		:Season,
					                                       		:Episode,
					                                       		:Title,
					                                       		:Plot,
					                                       		:Rating,
					                                       		:RatingCount,
					                                       		:Banner,
					                                       		:AirDate,
					                                       		:SeriesKey,
					                                       		:EpisodeTheTVDBID)');
				
					$EpisodeAddPrep->execute(array(':Date'             => time(),
												   ':Season'           => $Serie->SeasonNumber,
												   ':Episode'          => $Serie->EpisodeNumber,
												   ':Title'            => $Serie->EpisodeName,
												   ':Plot'             => $Serie->Overview,
												   ':Rating'           => $Serie->Rating,
												   ':RatingCount'      => $Serie->RatingCount,
												   ':Banner'           => $Serie->filename,
												   ':AirDate'          => $EpisodeAirDate,
												   ':SeriesKey'        => $SerieID,
												   ':EpisodeTheTVDBID' => $Serie->id));
				}
				catch(PDOException $e) {
					throw new RestException(400, 'MySQL: '.$e->getMessage());
				}
				
				$EpisodesAdded++;
			}
		}
		
		$LogEntry .= 'Added "'.$SerieTitle.'" with '.$EpisodesAdded.' episodes'."\n";
		
		AddLog(EVENT.'Series', 'Success', $LogEntry);
		throw new RestException(201, $LogEntry);
	}
	
	/**
	 * @url GET /
	**/
	function SeriesAll() {
		try {
			$SeriePrep = $this->PDO->prepare('SELECT
			                                  	*
			                                  FROM
			                                  	Series
			                                  ORDER BY
			                                  	Title');
			                                  	
			$SeriePrep->execute();
			$SerieRes = $SeriePrep->fetchAll();
			
			if(sizeof($SerieRes)) {
				$Data = array();
				foreach($SerieRes AS $SerieRow) {
					if(strlen($SerieRow['Poster'])) {
						$FileInfo = pathinfo($SerieRow['Poster']);
						
						$SerieRow['Poster']      = 'posters/series/'.$FileInfo['filename'].'.'.$FileInfo['extension'];
						$SerieRow['PosterSmall'] = 'posters/series/'.$FileInfo['filename'].'-small.'.$FileInfo['extension'];
					}
					
					$EpisodesPrep = $this->PDO->prepare('SELECT
					                                     	COUNT(ID)
					                                     AS
					                                     	EpisodeCount
					                                     FROM
					                                     	Episodes
					                                     WHERE
					                                     	SeriesKey = :ID
					                                     AND
					                                     	Season != 0
					                                     AND
					                                     	File != ""');
					                                     	
					$EpisodesPrep->execute(array(':ID' => $SerieRow['ID']));
					
					$SerieRow['EpisodeCount'] = $EpisodesPrep->fetch()['EpisodeCount'];
					
					$Data[] = $SerieRow;
				}
				
				return $Data;
			}
			else {
				throw new RestException(404, 'Did not find any TV series in the database matching your critera');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
	}
	
	/**
	 * @url GET /recent
	 * @url GET /recent/:Days
	**/
	function GetRecentEpisodes($Days = 5) {
		if(!is_numeric($Days)) {
			throw new RestException(412, 'Days must be a numeric value');
		}
		
		try {
			$SchedulePrep = $this->PDO->prepare('SELECT
													Series.ID,
			                                     	Series.Title,
			                                     	Series.AirTime,
			                                     	Series.Poster,
			                                     	Episodes.Season,
			                                     	Episodes.Episode,
			                                     	Episodes.Title AS EpisodeTitle,
			                                     	Episodes.AirDate,
			                                     	Episodes.File,
			                                     	Episodes.TorrentKey,
			                                     	Episodes.ID AS EpisodeID
			                                     FROM
			                                     	Series,
			                                     	Episodes
			                                     WHERE
			                                     	Episodes.SeriesKey = Series.ID
			                                     AND
			                                     	Episodes.AirDate <= :CurrentTime
			                                     AND
			                                     	Episodes.AirDate >= :DayOffset
			                                     AND
			                                     	Episodes.Season != 0
			                                     AND
			                                     	Episodes.Episode != 0
			                                     ORDER BY
			                                     	Episodes.AirDate 
			                                     DESC');
			                                     
			$SchedulePrep->execute(array(':CurrentTime' => time(),
									     ':DayOffset'   => strtotime('-'.$Days.' days')));
			$ScheduleRes = $SchedulePrep->fetchAll();
			
			if(sizeof($ScheduleRes)) {
				$Data = array();
				foreach($ScheduleRes AS $Row) {
					if(strlen($Row['Poster'])) {
						$FileInfo = pathinfo($Row['Poster']);
						
						$Row['Poster']      = 'posters/series/'.$FileInfo['filename'].'.'.$FileInfo['extension'];
						$Row['PosterSmall'] = 'posters/series/'.$FileInfo['filename'].'-small.'.$FileInfo['extension'];
					}
					
					$Row['Season']  = sprintf('%02s', $Row['Season']);
					$Row['Episode'] = sprintf('%02s', $Row['Episode']);
					
					if($Row['File'] && is_file($Row['File'])) {
						$Row['Status'] = 'Available';
					}
					else if($Row['TorrentKey']) {
						$Row['Status'] = 'Downloaded';
					}
					else {
						$RSSObj = new RSS;
						$SearchStr = $Row['Title'].' s'.$Row['Season'].'e'.$Row['Episode'];
						
						try {
							$Torrents = $RSSObj->SearchTorrents($SearchStr);
						}
						catch(RestException $e) {
							$Torrents = '';
						}
						
						if(is_array($Torrents)) {
							$Row['Torrents'] = $Torrents;
							
							if(sizeof($Torrents) > 1) {
								$Row['Status'] = 'Torrents';
							}
							else {
								$Row['Status'] = 'Torrent';
							}
						}
						else {
							$Row['Status'] = '';
						}
					}
					                   
					$Data[] = $Row;
				}
				
				return $Data;
			}
			else {
				throw new RestException(404, 'Did not find any episodes in the database matching your criteria');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
	}
	
	/**
	 * @url DELETE /:ID
	**/
	function DeleteSerie($ID) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		try {
			$Serie = $this->PDO->query('SELECT
			                            	Title,
			                            	TitleAlt
			                            FROM
			                            	Series
			                            WHERE
			                            	ID = "'.$ID.'"')->fetch();
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$DrivesObj = new Drives;
		$Drives = $DrivesObj->DrivesAll();
		
		if(strlen($Serie['Title'])) {
			$LogEntry = '';
			
			foreach($Drives AS $Drive) {
				$DriveRoot = ($Drive['IsNetwork']) ? $Drive['Share'] : $Drive['Mount'];
				
				if(@!RecursiveDirRemove($DriveRoot.'/Media/TV/'.$Serie['Title'])) {
					$LogEntry .= 'Failed to delete "'.$DriveRoot.'/Media/TV/'.$Serie['Title'].'"'."\n";
				}
				
				if(strlen($Serie['TitleAlt'])) {
					if(@!RecursiveDirRemove($DriveRoot.'/Media/TV/'.$Serie['TitleAlt'])) {
						$LogEntry .= 'Failed to delete "'.$DriveRoot.'/Media/TV/'.$Serie['TitleAlt'].'"'."\n";
					}
				}
			}
			
			try {
				$SerieDeletePrep = $this->PDO->prepare('DELETE FROM Series WHERE ID = :ID');
				$SerieDeletePrep->execute(array(':ID' => $ID));
			}
			catch(PDOException $e) {
				throw new RestException(400, 'MySQL: '.$e->getMessage());
			}
			
			try {
				$EpisodesDeletePrep = $this->PDO->prepare('DELETE FROM Episodes WHERE SeriesKey = :ID');
				$EpisodesDeletePrep->execute(array(':ID' => $ID));
			}
			catch(PDOException $e) {
				throw new RestException(400, 'MySQL: '.$e->getMessage());
			}
			
			$LogEntry .= 'Deleted "'.$Serie['Title'].'" from the database'."\n";
			
			AddLog(EVENT.'Series', 'Success', $LogEntry);
			throw new RestException(200, $LogEntry);
		}
		else {
			throw new RestException(404, 'Did not find any TV serie in the database matching your criteria');
		}
	}
	
	/**
	 * @url POST /update/:ID
	**/
	function UpdateSerie($ID) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		$AcceptedParameters = array('Date',
		                            'Title',
		                            'Plot',
		                            'ContentRating',
		                            'IMDBID',
		                            'Rating',
		                            'RatingCount',
		                            'Banner',
		                            'FanArt',
		                            'Poster',
		                            'FirstAired',
		                            'AirDay',
		                            'AirTime',
		                            'Runtime',
		                            'Network',
		                            'Status',
		                            'Genre',
		                            'TheTVDBID',
		                            'TitleAlt');
		
		if(!sizeof($_POST)) {
			throw new RestException(412, 'Invalid request. Accepted parameters are "'.implode(', ', $AcceptedParameters).'"');
		}
		
		$UpdateQuery = 'UPDATE Series SET ';
		$PrepArr = array();
		$i = 0;
		foreach($_POST AS $Key => $Value) {
			if(!in_array($Key, $AcceptedParameters)) {
				throw new RestException(412, 'Invalid request. Accepted parameters are "'.implode(', ', $AcceptedParameters).'"');
			}
			
			$UpdateQuery .= ' '.$Key.' = :'.$Key;
			$PrepArr[':'.$Key] = $Value;
			
			if(++$i != sizeof($_POST)) {
				$UpdateQuery .= ', ';
			}
			else {
				$UpdateQuery .= ' WHERE ID = :ID';
				$PrepArr[':ID'] = $ID;
			}
		}
		
		try {
			$SeriePrep = $this->PDO->prepare($UpdateQuery);
			
			$SeriePrep->execute($PrepArr);
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = 'Updated TV series with the ID "'.$ID.'"';
		
		AddLog(EVENT.'Series', 'Success', $LogEntry);
		throw new RestException(200, $LogEntry);
	}
	
	/**
	 * @url GET /upcoming
	**/
	function UpcomingEpisodes() {
		try {
			$SchedulePrep = $this->PDO->prepare('SELECT
													S.ID,
			                                     	S.Title AS Title,
			                                     	S.Poster AS Poster,
			                                     	E.Season AS Season,
			                                     	E.Episode AS Episode,
			                                     	E.File,
			                                     	E.TorrentKey,
			                                     	E.AirDate,
			                                     	E.Title AS EpisodeTitle,
			                                     	E.ID AS EpisodeID
			                                     FROM
			                                     	Series S
			                                     JOIN
			                                     	(SELECT
			                                     		SeriesKey,
			                                     		MIN(Episodes.AirDate) MinDate
			                                     	FROM
			                                     		Episodes
				                                 	WHERE
				                                 		Episodes.AirDate > :CurrentTime
				                                 	AND
				                                 		Episodes.Season != 0
				                                 	AND
				                                 		Episodes.Episode != 0
				                                 	GROUP BY
				                                 		SeriesKey)
				                                 	M
				                                    ON
				                                    	M.SeriesKey = S.ID
			                                        JOIN
			                                        	Episodes E
			                                        ON
			                                        	E.SeriesKey = S.ID
			                                        AND
			                                        	E.AirDate = M.MinDate
			                                        ORDER BY
			                                        	E.AirDate');
			                                        	
			$SchedulePrep->execute(array(':CurrentTime' => time()));
			$ScheduleRes = $SchedulePrep->fetchAll();
			
			if(sizeof($ScheduleRes)) {
				$Data = array();
				foreach($ScheduleRes AS $Row) {
					if(strlen($Row['Poster'])) {
						$FileInfo = pathinfo($Row['Poster']);
						
						$Row['Poster']      = 'posters/series/'.$FileInfo['filename'].'.'.$FileInfo['extension'];
						$Row['PosterSmall'] = 'posters/series/'.$FileInfo['filename'].'-small.'.$FileInfo['extension'];
					}
					
					$Row['Season']  = sprintf('%02s', $Row['Season']);
					$Row['Episode'] = sprintf('%02s', $Row['Episode']);
					
					if($Row['File'] && is_file($Row['File'])) {
						$Row['Status'] = 'Available';
					}
					else if($Row['TorrentKey']) {
						$Row['Status'] = 'Downloaded';
					}
					else {
						$RSSObj = new RSS;
						$SearchStr = $Row['Title'].' s'.$Row['Season'].'e'.$Row['Episode'];
						
						try {
							$Torrents = $RSSObj->SearchTorrents($SearchStr);
						}
						catch(RestException $e) {
							$Torrents = '';
						}
						
						if(is_array($Torrents)) {
							$Row['Torrents'] = $Torrents;
							
							if(sizeof($Torrents) > 1) {
								$Row['Status'] = 'Torrents';
							}
							else {
								$Row['Status'] = 'Torrent';
							}
						}
						else {
							$Row['Status'] = '';
						}
					}
					                   
					$Data[] = $Row;
				}
				
				return $Data;
			}
			else {
				throw new RestException(404, 'Did not find any episodes in the database matching your criteria');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
	}
	
	/**
	 * @url DELETE /episodes/:ID
	**/
	function DeleteEpisode($ID) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		try {
			$EpisodePrep = $this->PDO->prepare('SELECT
			                                    	File
			                                    FROM
			                                    	Episodes
			                                    WHERE
			                                    	ID = :ID');
			                                    	
			$EpisodePrep->execute(array(':ID' => $ID));
			
			$Episode = $EpisodePrep->fetch();
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		if(empty($Episode['File'])) {
			throw new RestException(404, 'Did not find any episode in the database matching your criteria');
		}
		
		if(unlink($Episode['File'])) {
			try {
				$EpisodeDeletePrep = $this->PDO->prepare('UPDATE
															Episodes
														  SET
														  	File = ""
														  WHERE
														  	ID = :ID');
				
				$EpisodeDeletePrep->execute(array(':ID' => $ID));
			}
			catch(PDOException $e) {
				throw new RestException(400, 'MySQL: '.$e->getMessage());
			}
			
			$LogEntry = 'Deleted "'.$Episode['File'].'"';
			
			AddLog(EVENT.'Series', 'Success', $LogEntry);
			throw new RestException(200, $LogEntry);
		}
		else {
			throw new RestException(400, 'Failed to delete "'.$Episode['File'].'"');
		}
	}
	
	/**
	 * @url GET /episodes/duplicates
	**/
	function FindDuplicateEpisodes() {
		//
	}
	
	/**
	 * @url GET /rebuild/folders
	**/
	function RebuildFolders() {
		$DrivesObj = new Drives;
		
		try {
			$Drives = $DrivesObj->DrivesAll();
			$Series = $this->SeriesAll();
		}
		catch(RestException $e) {
		}
		
		if(is_array($Drives) && is_array($Series)) {
			$RebuiltFolders = 0;
			foreach($Drives AS $Drive) {
				$DriveRoot = ($Drive['IsNetwork']) ? $Drive['Share'] : $Drive['Mount'];
				
				foreach($Series AS $Serie) {
					
					if(strlen($Serie['TitleAlt'])) {
						$Folder = $DriveRoot.'/Media/TV/'.$Serie['TitleAlt'];
					}
					else {
						$Folder = $DriveRoot.'/Media/TV/'.$Serie['Title'];
					}
					
					if(!is_dir($Folder)) {
						if(@mkdir($Folder)) {
							$RebuiltFolders++;
						}
					}
				}
			}
		}
		
		try {
			$RebuildPrep = $this->PDO->prepare('UPDATE Hub SET Value = :Time WHERE Setting = "LastFolderRebuild"');
			$RebuildPrep->execute(array(':Time' => time()));
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = '';
		if($RebuiltFolders) {
			$LogEntry = 'Created '.$RebuiltFolders.' missing folders divided over '.sizeof($Series).' series.';
			AddLog(EVENT.'Series', 'Success', $LogEntry);
		}
		
		throw new RestException(200, $LogEntry);
	}
	
	/**
	 * @url GET /rebuild/episodes
	**/
	function RebuildEpisodes() {
		$RebuildTimeStart = time();
		$EpisodesRebuilt = 0;
		
		$DrivesObj = new Drives;
		
		try {
			$Drives = $DrivesObj->DrivesAll();
		}
		catch(RestException $e) {
		}
		
		if(is_array($Drives)) {
			try {
				$EpisodesPrep = $this->PDO->prepare('UPDATE
				                                     	Episodes
				                                     SET
				                                     	File = ""');
				                                     	
				$EpisodesPrep->execute();
			}
			catch(PDOException $e) {
				throw new RestException(400, 'MySQL: '.$e->getMessage());
			}
			
			foreach($Drives AS $Drive) {
				$DriveRoot = ($Drive['IsNetwork']) ? $Drive['Share'] : $Drive['Mount'];
				$SeriesInfoArr = $this->GetSeriesInfo($DriveRoot, TRUE);
				
				foreach($SeriesInfoArr AS $SerieTitle => $Series) {
					foreach($Series['Episodes'] AS $Episode) {
						$Slash        = strrpos($Episode, '/');
						$Location     = substr($Episode, 0, ($Slash + 1));
						$FileLength   = (strlen($Episode) - $Slash);
						$File         = substr($Episode, ($Slash + 1), $FileLength);
							
						$ParsedInfo = ParseRelease($File);
						
						if($ParsedInfo) {
							try {
								$Serie = $this->PDO->query('SELECT
								                            	Series.ID
								                            FROM
								                            	Series
								                            WHERE
								                            	Title = "'.$SerieTitle.'"
								                            OR
								                            	TitleAlt = "'.$SerieTitle.'"')->fetch();
							}
							catch(PDOException $e) {
								throw new RestException(400, 'MySQL: '.$e->getMessage());
							}
							
							if(strlen($Serie['ID'])) {
								if(is_file($Location.$File)) {
									if(array_key_exists('Episodes', $ParsedInfo)) {
										foreach($ParsedInfo['Episodes'] AS $Episodes) {
											try {
												$EpisodeUpdatePrep = $this->PDO->prepare('UPDATE
												                                          	Episodes
												                                          SET
												                                          	File = :File
												                                          WHERE
												                                          	SeriesKey = :SeriesKey
												                                          AND
												                                          	Season = :Season
												                                          AND
												                                          	Episode = :Episode');
												                                          	
												$EpisodeUpdatePrep->execute(array(':File'      => $Location.$File,
																			  	  ':SeriesKey' => $Serie['ID'],
																				  ':Season'    => $Episodes[0],
																				  ':Episode'   => $Episodes[1]));
											}
											catch(PDOException $e) {
												throw new RestException(400, 'MySQL: '.$e->getMessage());
											}
										
											$EpisodesRebuilt++;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
		try {
			$RebuildPrep = $this->PDO->prepare('UPDATE Hub SET Value = :Time WHERE Setting = "LastSerieRebuild"');
			$RebuildPrep->execute(array(':Time' => time()));
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = 'Rebuilt '.$EpisodesRebuilt.' episodes divided over '.sizeof($this->SeriesAll()).' series.';
		AddLog(EVENT.'Series', 'Success', $LogEntry);
		throw new RestException(200, $LogEntry);
	}
	
	function GetSeriesInfo($DriveRoot = '', $EpisodeLocations = FALSE) {
		$SeriesArr = $this->GetSeriesDirectories($DriveRoot);
		$SeriesDirectoriesArr = $SeriesArr;
		
		sort($SeriesArr);
		$SeriesArr = array_unique($SeriesArr);
	
		$SeriesInfoArr = array();
		foreach($SeriesArr AS $Serie) {
			$Serie = substr($Serie, (strrpos($Serie, '/') + 1));
			
			foreach($SeriesDirectoriesArr AS $SerieDirectory) {
				if($Serie == substr($SerieDirectory, (strrpos($SerieDirectory, '/') + 1))) {
					$SerieEpisodes = RecursiveDirSearch($SerieDirectory);
					@$SeriesInfoArr[$Serie]['TotalEpisodes'] += sizeof($SerieEpisodes);
					
					if($EpisodeLocations) {
						$SeriesInfoArr[$Serie]['Episodes'] = (@is_array($SeriesInfoArr[$Serie]['Episodes'])) ? $SeriesInfoArr[$Serie]['Episodes'] : array();
						$SeriesInfoArr[$Serie]['Episodes'] = array_merge($SeriesInfoArr[$Serie]['Episodes'], $SerieEpisodes);
					}
					
					$SeriesInfoArr[$Serie][] = $SerieDirectory;
				}
			}
		}
		
		return $SeriesInfoArr;
	}
	
	function GetSeriesDirectories($DriveRoot = '') {
		$SeriesDirArr = array();
		
		if($DriveRoot) {
			if(is_dir($DriveRoot.'/Media/TV/')) {
				$SeriesDir = array_filter(glob($DriveRoot.'/Media/TV/*'), 'is_dir');
				$SeriesDirArr = array_merge($SeriesDirArr, $SeriesDir);
			}
		}
		else {
			$Drives = Drives::GetDrives();
			if(is_array($Drives)) {
				foreach($Drives AS $Drive) {
					$DriveRoot = ($Drive['IsNetwork']) ? $Drive['Share'] : $Drive['Mount'];
					
					if(is_dir($DriveRoot.'/Media/TV/')) {
						$SeriesDir = array_filter(glob($DriveRoot.'/Media/TV/*'), 'is_dir');
						$SeriesDirArr = array_merge($SeriesDirArr, $SeriesDir);
					}
				}
			}
		}
		
		return $SeriesDirArr;
	}
	
	/**
	 * @url GET /refresh/all
	**/
	function RefreshAllSeries() {
		$Series = $this->SeriesAll();
		
		foreach($Series AS $Serie) {
			try {
				$this->RefreshSerieByID($Serie['ID']);
			}
			catch(RestException $e) {
				//echo $e->getCode().' '.$e->getMessage().'<br />';
			}
		}
		
		$LogEntry = 'Refreshed '.sizeof($Series).' series';
		AddLog(EVENT.'Series', 'Success', $LogEntry);
		
		try {
			$RefreshPrep = $this->PDO->prepare('UPDATE
			                                    	Hub
			                                    SET
			                                    	Value = :Time
			                                    WHERE
			                                    	Setting = "LastSerieRefresh"');
			                                    	
			$RefreshPrep->execute(array(':Time' => time()));
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		throw new RestException(200, $LogEntry);
	}
	
	/**
	 * @url GET /refresh/:ID
	**/
	function RefreshSerieByID($ID = FALSE) {
		$this->Connect();
		
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		$SeriesUpdated = $EpisodesAdded = $EpisodesUpdated = 0;
		try {
			$this->TheTVDB->SetPreviousUpdateTime(0);
			$this->TheTVDB->SetLanguage('en');
		}
		catch(Exception $e) {
			throw new RestException(400, 'TheTVDB: '.$e->getMessage());
		}
		
		try {
			$SeriePrep = $this->PDO->prepare('SELECT
			                                    ID,
			                                  	Title,
			                                  	TheTVDBID
			                                  FROM
			                                  	Series
			                                  WHERE
			                                  	ID = :ID');
			                                  	
			$SeriePrep->execute(array(':ID' => $ID));
			
			if($SeriePrep->rowCount()) {
				$Series = $SeriePrep->fetchAll();
			}
			else {
				throw new RestException(404, 'Did not find any TV serie in the database matching your criteria');
			}
		}
		catch(PDOException $e) {
			throw new RestException(412, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = '';
		foreach($Series AS $Serie) {
			try {
				$SeriesInfo = $this->TheTVDB->GetSeriesInfo($Serie['TheTVDBID']);
			}
			catch(Exception $e) {
				throw new RestException(400, 'TheTVDB: '.$e->getMessage());
			}
				
			foreach($SeriesInfo AS $SerieInfo) {
				if($SerieInfo->SeriesName) {
					$SerieInfo->SeriesName = str_replace(array(':', '\'', '(', ')', '*'), '', $SerieInfo->SeriesName);
					$SerieInfo->Genre      = str_replace('|', ', ', trim($SerieInfo->Genre, '|'));
				
					try {
						$SerieUpdatePrep = $this->PDO->prepare('UPDATE
						                                        	Series
						                                        SET
						                                        	Title         = :Title,
						                                        	Plot          = :Plot,
						                                        	ContentRating = :ContentRating,
						                                        	IMDBID        = :IMDBID,
						                                        	Rating        = :Rating,
						                                        	RatingCount   = :RatingCount,
						                                        	Banner        = :Banner,
						                                        	FanArt        = :FanArt,
						                                        	Poster        = :Poster,
						                                        	FirstAired    = :FirstAired,
						                                        	AirDay        = :AirDay,
						                                        	AirTime       = :AirTime,
						                                        	Runtime       = :Runtime,
						                                        	Network       = :Network,
						                                        	Status        = :Status,
						                                        	Genre         = :Genre
						                                        WHERE
						                                        	TheTVDBID     = :TheTVDBID');
					
						$SerieUpdatePrep->execute(array(':Title'         => $SerieInfo->SeriesName,
														':Plot'          => $SerieInfo->Overview,
														':ContentRating' => $SerieInfo->ContentRating,
														':IMDBID'        => $SerieInfo->IMDB_ID,
														':Rating'        => $SerieInfo->Rating,
														':RatingCount'   => $SerieInfo->RatingCount,
														':Banner'        => $SerieInfo->banner,
														':FanArt'        => $SerieInfo->fanart,
														':Poster'        => $SerieInfo->poster,
														':FirstAired'    => strtotime($SerieInfo->FirstAired),
														':AirDay'        => $SerieInfo->Airs_DayOfWeek,
														':AirTime'       => $SerieInfo->Airs_Time,
														':Runtime'       => $SerieInfo->Runtime,
														':Network'       => $SerieInfo->Network,
														':Status'        => $SerieInfo->Status,
														':Genre'         => $SerieInfo->Genre,
														':TheTVDBID'     => $Serie['TheTVDBID']));
					}
					catch(PDOException $e) {
						throw new RestException(412, 'MySQL: '.$e->getMessage());
					}
	
					$EpisodeAirTime = $SerieInfo->Airs_Time;
					$TimeZoneOffset = ($SerieInfo->Network == 'BBC Two') ? '+1 hour' : '+6 hours';
					$SeriesUpdated++;
				}
				else {
					$Episode = $this->PDO->query('SELECT TheTVDBID FROM Episodes WHERE TheTVDBID = "'.$SerieInfo->id.'"')->fetch();
				
					if(strlen($Episode['TheTVDBID'])) {
						$Date = date('d.m.Y', strtotime($SerieInfo->FirstAired));
						$Time = date('H:i',   strtotime($EpisodeAirTime));
						$EpisodeAirDate =     strtotime($TimeZoneOffset, strtotime($Date.' '.$Time));
						
						try {
							$EpisodeUpdatePrep = $this->PDO->prepare('UPDATE
																	  	Episodes
																	  SET
																	  	Season      = :Season,
																	  	Episode     = :Episode,
																	  	Title       = :Title,
																	  	Plot        = :Plot,
																	  	Rating      = :Rating,
																	  	RatingCount = :RatingCount,
																	  	Banner      = :Banner,
																	  	AirDate     = :AirDate
																	  WHERE
																	  	TheTVDBID   = :TheTVDBID');
																	  	
							$EpisodeUpdatePrep->execute(array(':Season'      => $SerieInfo->SeasonNumber,
															  ':Episode'     => $SerieInfo->EpisodeNumber,
															  ':Title'       => $SerieInfo->EpisodeName,
															  ':Plot'        => $SerieInfo->Overview,
															  ':Rating'      => $SerieInfo->Rating,
															  ':RatingCount' => $SerieInfo->RatingCount,
															  ':Banner'      => $SerieInfo->filename,
															  ':AirDate'     => $EpisodeAirDate,
															  ':TheTVDBID'   => $SerieInfo->id));
						}
						catch(PDOException $e) {
							throw new RestException(412, 'MySQL: '.$e->getMessage());
						}
		
						$EpisodesUpdated++;
					}
					else {
						$Date = date('d.m.Y', strtotime($SerieInfo->FirstAired));
						$Time = date('H:i',   strtotime($EpisodeAirTime));
						$EpisodeAirDate =     strtotime($TimeZoneOffset, strtotime($Date.' '.$Time));
		
						try {
							$EpisodeAddPrep = $this->PDO->prepare('INSERT INTO
																   	Episodes
																   		(ID,
																   		Date,
																   		Season,
																   		Episode,
																   		Title,
																   		Plot,
																   		Rating,
																   		RatingCount,
																   		Banner,
																   		AirDate,
																   		SeriesKey,
																   		TheTVDBID)
																   	VALUES
																   		(NULL,
																   		:Date,
																   		:Season,
																   		:Episode,
																   		:Title,
																   		:Plot,
																   		:Rating,
																   		:RatingCount,
																   		:Banner,
																   		:AirDate,
																   		:SeriesKey,
																   		:EpisodeTheTVDBID)');
						
							$EpisodeAddPrep->execute(array(':Date'             => time(),
														   ':Season'           => $SerieInfo->SeasonNumber,
														   ':Episode'          => $SerieInfo->EpisodeNumber,
														   ':Title'            => $SerieInfo->EpisodeName,
														   ':Plot'             => $SerieInfo->Overview,
														   ':Rating'           => $SerieInfo->Rating,
														   ':RatingCount'      => $SerieInfo->RatingCount,
														   ':Banner'           => $SerieInfo->filename,
														   ':AirDate'          => $EpisodeAirDate,
														   ':SeriesKey'        => $Serie['ID'],
														   ':EpisodeTheTVDBID' => $SerieInfo->id));
						}
						catch(PDOException $e) {
							throw new RestException(412, 'MySQL: '.$e->getMessage());
						}
		
						$EpisodesAdded++;
					}
				}
			}
		}	
	
		if(($SeriesUpdated + $EpisodesAdded + $EpisodesUpdated) > 0) {
			$LogEntry .= 'Refreshed "'.$Serie['Title'].'". Updated '.$EpisodesUpdated.' and added '.$EpisodesAdded.' episodes.'."\n";
			
			$LogEntry .= $this->DownloadPoster($Serie['ID']);
			
			AddLog(EVENT.'Series', 'Success', $LogEntry);
			throw new RestException(200, $LogEntry);
		}
		else {
			throw new RestException(304);
		}
	}
	
	/**
	 * @url GET /:ID/episodes
	**/
	function GetEpisodes($ID) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		try {
			$EpisodePrep = $this->PDO->prepare('SELECT
			                                    	Series.ID,
			                                    	Episodes.*
			                                    FROM
			                                    	Series,
			                                    	Episodes
			                                    WHERE
			                                    	Series.ID = :ID
			                                    AND
			                                    	Episodes.SeriesKey = Series.ID
			                                    AND
			                                    	Episodes.AirDate > 200000
			                                    AND
			                                    	Episodes.Season != 0
			                                    ORDER BY
			                                    	Episodes.AirDate
			                                    DESC');
			                                  	
			$EpisodePrep->execute(array(':ID' => $ID));
			$EpisodeRes = $EpisodePrep->fetchAll();
			
			if(sizeof($EpisodeRes)) {
				$Data = array();
				foreach($EpisodeRes AS $Row) {
					$Row['Season']  = sprintf('%02s', $Row['Season']);
					$Row['Episode'] = sprintf('%02s', $Row['Episode']);
					
					if($Row['File'] && is_file($Row['File'])) {
						$Row['Status'] = 'Available';
					}
					else if($Row['TorrentKey']) {
						$Row['Status'] = 'Downloaded';
					}
					else if($Row['AirDate'] > time()) {
						$Row['Status'] = 'Upcoming';
					}
					else {
						$RSSObj = new RSS;
						$SearchStr = $Row['Title'].' s'.$Row['Season'].'e'.$Row['Episode'];
						
						try {
							$Torrents = $RSSObj->SearchTorrents($SearchStr);
						}
						catch(RestException $e) {
							$Torrents = '';
						}
						
						if(is_array($Torrents)) {
							$Row['Torrents'] = $Torrents;
							
							if(sizeof($Torrents) > 1) {
								$Row['Status'] = 'Torrents';
							}
							else {
								$Row['Status'] = 'Torrent';
							}
						}
						else {
							$Row['Status'] = '';
						}
					}
					                   
					$Data[] = $Row;
				}
				
				return $Data;
			}
			else {
				throw new RestException(404, 'Did not find any episodes in the database matching your criteria');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
	}
	
	/**
	 * @url GET /:ID
	**/
	function GetSerieByID($ID) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		try {
			$SeriePrep = $this->PDO->prepare('SELECT
			                                  	*
			                                  FROM
			                                  	Series
			                                  WHERE
			                                  	ID = :ID');
			                                  	
			$SeriePrep->execute(array(':ID' => $ID));
			$SerieRes = $SeriePrep->fetchAll();
			
			if(sizeof($SerieRes)) {
				$Data = array();
				foreach($SerieRes AS $SerieRow) {
					if(strlen($SerieRow['Poster'])) {
						$FileInfo = pathinfo($SerieRow['Poster']);
						
						$SerieRow['Poster']      = 'posters/series/'.$FileInfo['filename'].'.'.$FileInfo['extension'];
						$SerieRow['PosterSmall'] = 'posters/series/'.$FileInfo['filename'].'-small.'.$FileInfo['extension'];
					}
					
					$Data[] = $SerieRow;
				}
				
				return $Data;
			}
			else {
				throw new RestException(404, 'Did not find any TV serie in the database matching your criteria');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
	}

	function GetSerieByTitle($Title) {
		try {
			$SeriePrep = $this->PDO->prepare('SELECT
			                                  	*
			                                  FROM
			                                  	Series
			                                  WHERE
			                                  	Title = :Title
			                                  OR
			                                  	TitleAlt = :Title');
			                                  	
			$SeriePrep->execute(array(':Title' => $Title));
			$SerieRes = $SeriePrep->fetchAll();
			
			if(sizeof($SerieRes)) {
				$Data = array();
				foreach($SerieRes AS $SerieRow) {
					if(strlen($SerieRow['Poster'])) {
						$FileInfo = pathinfo($SerieRow['Poster']);
						
						$SerieRow['Poster']      = 'posters/series/'.$FileInfo['filename'].'.'.$FileInfo['extension'];
						$SerieRow['PosterSmall'] = 'posters/series/'.$FileInfo['filename'].'-small.'.$FileInfo['extension'];
					}
					
					$Data[] = $SerieRow;
				}
				
				return $Data;
			}
			else {
				throw new RestException(404, 'Did not find any TV serie in the database matching your criteria');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
	}
	
	/**
	 * @url DELETE :ID/season/:Num1
	 * @url DELETE :ID/season/:Num1/:Num2
	**/
	function DeleteSeasonFiles($ID, $Num1, $Num2 = NULL) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
	
		if(!is_numeric($Num1)) {
			throw new RestException(412, 'Num1 must be a numeric value');
		}
		
		if(is_null($Num2)) {
			$Seasons = $Num1;
		}
		else {
			$Seasons = array();
			for($i = $Num1; $i <= $Num2; $i++) {
				$Seasons[] = $i;
			}
			
			$Seasons = implode(', ', $Seasons);
		}
		
		try {
			$EpisodesPrep = $this->PDO->prepare('SELECT
													ID,
			                                     	File
			                                     FROM
			                                     	Episodes
			                                     WHERE
			                                     	SeriesKey = :ID
			                                     AND
			                                     	File != ""
			                                     AND
			                                     	Episodes.Season IN ('.$Seasons.')');
			
			$EpisodesPrep->execute(array(':ID' => $ID));
			                             
			$Episodes = $EpisodesPrep->fetchAll();
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$EpisodesDeleted = 0;
		$LogEntry = '';
		foreach($Episodes AS $Episode) {
			try {
				$EpisodeFileDeletePrep = $this->PDO->prepare('UPDATE
				                                              	Episodes
				                                              SET
				                                              	File = ""
				                                              WHERE
				                                              	ID = :ID');
		
				$EpisodeFileDeletePrep->execute(array(':ID' => $Episode['ID']));
			}
			catch(PDOException $e) {
				throw new RestException(400, 'MySQL: '.$e->getMessage());
			}
			
			if(is_file($Episode['File'])) {
				if(unlink($Episode['File'])) {
					$EpisodesDeleted++;
				}
				else {
					$LogEntry .= 'Failed to delete episode file "'.$Episode['File'].'" of serie with ID "'.$ID.'"'."\n";
				}
			}
		}
		
		if($EpisodesDeleted) {
			$LogEntry = 'Deleted '.$EpisodesDeleted.' episode files from season '.$Seasons.' of serie with ID "'.$ID.'"';
		}
		
		if(strlen($LogEntry)) {
			AddLog(EVENT.'Series', 'Success', $LogEntry);
			throw new RestException(200, $LogEntry);
		}
		else {
			throw new RestException(404, 'Did not find any episodes in the database matching your criteria');
		}
	}
	
	/**
	 * Internal functions
	**/
	function DownloadPoster($SerieID, $ForceNew = FALSE) {
		$Serie = $this->PDO->query('SELECT
		                            	Title,
		                            	Poster
		                            FROM
		                            	Series
		                            WHERE
		                            	ID = '.$SerieID)->fetch();
		
		if(!is_file(APP_PATH.'/'.$Serie['Poster'])) {
			$FileInfo = pathinfo($this->TheTVDB->GetPoster($Serie['Poster']));
			
			if(isset($FileInfo['extension'])) {
				if(!is_file(APP_PATH.'/posters/series/'.$FileInfo['filename'].'.'.$FileInfo['extension']) || $ForceNew) {
					$ch = curl_init($this->TheTVDB->GetPoster($Serie['Poster']));
					$fp = fopen(APP_PATH.'/tmp/'.$FileInfo['filename'].'.'.$FileInfo['extension'], 'wb');
					curl_setopt($ch, CURLOPT_FILE, $fp);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_exec($ch);
					curl_close($ch);
					fclose($fp);
				
					MakeThumbnail(APP_PATH.'/tmp/'.$FileInfo['filename'].'.'.$FileInfo['extension'], APP_PATH.'/posters/series/'.$FileInfo['filename'].'-small.'.$FileInfo['extension'],150,221);
					rename(APP_PATH.'/tmp/'.$FileInfo['filename'].'.'.$FileInfo['extension'], APP_PATH.'/posters/series/'.$FileInfo['filename'].'.'.$FileInfo['extension']);
				
					return 'Downloaded a new poster for "'.$Serie['Title'].'"'."\n";
				}
			}
		}
		
		return FALSE;
	}
}
?>