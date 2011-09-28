<?php
class Series extends Hub {
	public $TheTVDBAPI;
	
	function ConnectTheTVDB() {
		require_once APP_PATH.'/libraries/api.thetvdb.php';
		
		$Settings = Hub::GetSettings();
		if(array_key_exists('SettingHubTheTVDBAPIKey', $Settings)) {
			$this->TheTVDBAPI = new TheTVDBAPI($Settings['SettingHubTheTVDBAPIKey']);
			
			if(!is_object($this->TheTVDBAPI)) {
				$this->Error[] = 'Unable to connect to TheTVDB API';
				
				return FALSE;
			}
			else {
				$this->TheTVDBAPI->SetPreviousUpdateTime(time()); // Timestamp
				$this->TheTVDBAPI->SetLanguage('en');
			}
		}
		else {
			$this->Error[] = 'No API key available for TheTVDB';
		}
		
		return TRUE;
	}
	
	function SerieExists($SerieTitle) {
		$SeriePrep = $this->PDO->prepare('SELECT SerieTitle FROM Series WHERE (SerieTitle = :Title OR SerieTitleAlt = :Title)');
		$SeriePrep->execute(array(':Title' => $SerieTitle));
		
		if($SeriePrep->rowCount()) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
	function GetPastSchedule($Days) {
		$SchedulePrep = $this->PDO->prepare('SELECT Series.*, Episodes.* FROM Series, Episodes WHERE Episodes.SeriesKey = Series.SerieID AND EpisodeAirDate <= :CurrentTime AND EpisodeAirDate >= :DayOffset AND EpisodeSeason != 0 ORDER BY EpisodeAirDate DESC');
		$SchedulePrep->execute(array(':CurrentTime' => time(),
		                          ':DayOffset'   => strtotime('-'.$Days.' days')));
		
		if($SchedulePrep->rowCount()) {
			return $SchedulePrep->fetchAll();
		}
		else {
			return FALSE;
		}
	}
	
	function GetFutureSchedule() {
		$SchedulePrep = $this->PDO->prepare('SELECT S.*,E.* FROM Series S
		JOIN (SELECT SeriesKey, MIN(EpisodeAirDate) MinDate FROM Episodes
		    WHERE EpisodeAirDate > :CurrentTime AND EpisodeSeason != 0 GROUP BY SeriesKey) M
		    ON M.SeriesKey = S.SerieID
		JOIN Episodes E ON E.SeriesKey = S.SerieID AND E.EpisodeAirDate = M.MinDate ORDER BY E.EpisodeAirDate');
		$SchedulePrep->execute(array(':CurrentTime' => time()));
		
		if($SchedulePrep->rowCount()) {
			return $SchedulePrep->fetchAll();
		}
		else {
			return FALSE;
		}
	}
	
	function GetSeries() {
		$SeriePrep = $this->PDO->prepare('SELECT * FROM Series ORDER BY SerieTitle');
		$SeriePrep->execute();
		
		if($SeriePrep->rowCount()) {
			return $SeriePrep->fetchAll();
		}
		else {
			return FALSE;
		}
	}
	
	function GetEpisodeCount($SerieID) {
		$Serie = $this->PDO->query('SELECT COUNT(EpisodeID) AS Episodes FROM Episodes WHERE SeriesKey = '.$SerieID.' AND EpisodeFile != ""')->fetch();
	
		return $Serie['Episodes'];
	}
	
	function SearchTitle($Search) {
		$Series = $this->TheTVDBAPI->GetSeries($Search); // $SearchStr, $Language = 'en'
		
		if(sizeof($Series)) {
			return $Series;
		}
		else {
			return FALSE;
		}
	}
	
	function GetSerieByTitle($Title) {
		$SeriePrep = $this->PDO->prepare('SELECT * FROM Series WHERE SerieTitle = :SerieTitle');
		$SeriePrep->execute(array(':SerieTitle' => $Title));
		
		if($SeriePrep->rowCount()) {
			return $SeriePrep->fetchAll();
		}
		else {
			return FALSE;
		}
	}
	
	function GetSeasons($SerieID) {
		$SeasonPrep = $this->PDO->prepare('SELECT Series.SerieID, Episodes.* FROM Series, Episodes WHERE SerieID = :SerieID AND Episodes.SeriesKey = Series.SerieID AND Episodes.EpisodeSeason != 0 AND Episodes.EpisodeAirDate > 200000 ORDER BY Episodes.EpisodeAirDate DESC');
		$SeasonPrep->execute(array(':SerieID' => $SerieID));
				
		if($SeasonPrep->rowCount()) {
			return $SeasonPrep->fetchAll();
		}
		else {
			return FALSE;
		}
	}
	
	function GetEmptyEpisodes($SerieID) {
		$EpisodePrep = $this->PDO->prepare('SELECT * FROM Episodes WHERE SeriesKey = :SerieID AND EpisodeFile = "" ORDER BY EpisodeAirDate DESC');
		$EpisodePrep->execute(array(':SerieID' => $SerieID));
		
		if($EpisodePrep->rowCount()) {
			return $EpisodePrep->fetchAll();
		}
		else {
			return FALSE;
		}
	}
	
	function AddSpelling($SerieID, $Spelling) {
		$Serie = $this->PDO->query('SELECT SerieTitle FROM Series WHERE SerieID = "'.$SerieID.'"')->fetch();
		
		if($Serie['SerieTitle']) {
			$SpellingPrep = $this->PDO->prepare('UPDATE Series SET SerieTitleAlt = :Spelling WHERE SerieID = :ID');
			$SpellingPrep->execute(array(':Spelling' => $Spelling,
		                                 ':ID'       => $SerieID));
		                             
			Hub::AddLog(EVENT.'Series', 'Success', 'Added "'.$Spelling.'" as an alternate title for "'.$Serie['SerieTitle'].'"');
		}
	}
	
	function AddSerie($TheTVDBID) {
		try {
			$this->TheTVDBAPI->SetPreviousUpdateTime(time());
			$this->TheTVDBAPI->SetLanguage('en');
		
			$SeriesInfo = $this->TheTVDBAPI->GetSeriesInfo($TheTVDBID);
			$EpisodesAdded = 0;
			foreach($SeriesInfo AS $Serie) {
				if($Serie->SeriesName) {
					$SerieTitle = $Serie->SeriesName;
					$Serie->SeriesName = str_replace(array(':', '\'', '(', ')', '*'), '', $Serie->SeriesName);
					$Serie->Genre      = str_replace('|', ', ', trim($Serie->Genre, '|'));
				
					$Drives = Drives::GetDrivesFromDB();
					if(is_array($Drives)) {
						foreach($Drives AS $Drive) {
							$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveRoot'] : $Drive['DriveLetter'];
							
							if(!is_dir($DriveRoot.'/Media/TV/'.$Serie->SeriesName)) {
								if(!mkdir($DriveRoot.'/Media/TV/'.$Serie->SeriesName)) {
									Hub::AddLog(EVENT.'Drives', 'Failure', 'Unable to create "'.$DriveRoot.'/Media/TV/'.$Serie->SeriesName.'"');
								}
							}
						}
					}
					else {
						Hub::AddLog(EVENT.'Drives', 'Failure', 'Unable to get drive data from the database while adding "'.$Serie->SeriesName.'"');
					}
					
					$SerieAddPrep = $this->PDO->prepare('INSERT INTO Series (SerieID, SerieDate, SerieTitle, SeriePlot, SerieContentRating, SerieIMDBID, SerieRating, SerieRatingCount, SerieBanner, SerieFanArt, SeriePoster, SerieFirstAired, SerieAirDay, SerieAirTime, SerieRuntime, SerieNetwork, SerieStatus, SerieGenre, SerieTheTVDBID) VALUES (NULL, :Date, :Title, :Plot, :ContentRating, :IMDBID, :Rating, :RatingCount, :Banner, :FanArt, :Poster, :FirstAired, :AirDay, :AirTime, :Runtime, :Network, :Status, :Genre, :TheTVDBID)');
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
					
					$EpisodeAirTime = $Serie->Airs_Time;
					$TimeZoneOffset = ($Serie->Network == 'BBC Two') ? '+1 hour' : '+6 hours';
				}
				else {
					$SerieAdded = $this->PDO->query('SELECT SerieID FROM Series WHERE SerieTheTVDBID = "'.$Serie->seriesid.'"')->fetch();
					$SerieID = $SerieAdded['SerieID'];
					
					$this->DownloadPoster($SerieID);
					
					$Date = date('d.m.Y', strtotime($Serie->FirstAired));
					$Time = date('H:i',   strtotime($EpisodeAirTime));
					$EpisodeAirDate =     strtotime($TimeZoneOffset, strtotime($Date.' '.$Time));
	
					$EpisodeAddPrep = $this->PDO->prepare('INSERT INTO Episodes (EpisodeID, EpisodeDate, EpisodeSeason, EpisodeEpisode, EpisodeTitle, EpisodePlot, EpisodeRating, EpisodeRatingCount, EpisodeBanner, EpisodeAirDate, SeriesKey, EpisodeTheTVDBID) VALUES (NULL, :Date, :Season, :Episode, :Title, :Plot, :Rating, :RatingCount, :Banner, :AirDate, :SeriesKey, :EpisodeTheTVDBID)');
				
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
	
					$EpisodesAdded++;
				}
			}
		
			Hub::AddLog(EVENT.'Series', 'Success', 'Added "'.$SerieTitle.'" with '.$EpisodesAdded.' episodes');
		}
		catch(Exception $e) {
			echo '<strong>Exception:</strong> '.$e->getMessage().' in '.$e->getTraceAsString();
		}
	}
	
	function GetSerieEpisodeTorrents($TheTVDBID) {
		$Settings = Hub::GetSettings();
		$Serie = $this->PDO->query('SELECT SerieID, SerieTitle FROM Series WHERE SerieTheTVDBID = "'.$TheTVDBID.'"')->fetch();
		
		$DownloadArr = array();
		if(strlen($Serie['SerieID'])) {
			$Episodes = self::GetEmptyEpisodes($Serie['SerieID']);
			
			if(is_array($Episodes)) {
				foreach($Episodes AS $Episode) {
					$SearchFile = $Serie['SerieTitle'].' s'.sprintf('%02s', $Episode['EpisodeSeason']).'e'.sprintf('%02s', $Episode['EpisodeEpisode']);
					$Torrents = RSS::SearchTitle($SearchFile);
					
					if(is_array($Torrents)) {
						foreach($Torrents AS $Torrent) {
							$Parsed = RSS::ParseRelease($Torrent['TorrentTitle']);
						
							if(is_array($Parsed) && $Parsed['Type'] == 'TV') {
								if(!stristr($Torrent['TorrentTitle'], 'german') && !stristr($Torrent['TorrentTitle'], 'hebsub')) {
									$SerieTitle = $Parsed['Title'];
									
									foreach($Parsed['Episodes'] AS $SerieEp) {
										$NewQuality = RSS::GetQualityRank($Torrent['TorrentTitle']);
										if($NewQuality >= $Settings['SettingHubMinimumDownloadQuality'] && $NewQuality <= $Settings['SettingHubMaximumDownloadQuality']) {
											if(!isset($DownloadArr[$SerieTitle.'-'.$SerieEp[0].$SerieEp[1]])) {
												$DownloadArr[$SerieTitle.'-'.$SerieEp[0].$SerieEp[1]] = array();
											}
											
											if(isset($DownloadArr[$SerieTitle.'-'.$SerieEp[0].$SerieEp[1]][0])) {
												$OldQuality = RSS::GetQualityRank($DownloadArr[$SerieTitle.'-'.$SerieEp[0].$SerieEp[1]][0]);
									
												if($NewQuality > $OldQuality && $NewQuality >= $Settings['SettingHubMinimumDownloadQuality']) {
													$DownloadArr[$SerieTitle.'-'.$SerieEp[0].$SerieEp[1]][0] = $Torrent['TorrentURI'];
													$DownloadArr[$SerieTitle.'-'.$SerieEp[0].$SerieEp[1]][1] = $Episode['EpisodeID'];
													$DownloadArr[$SerieTitle.'-'.$SerieEp[0].$SerieEp[1]][2] = $Torrent['TorrentID'];
												}
											}
											else {
												$DownloadArr[$SerieTitle.'-'.$SerieEp[0].$SerieEp[1]][] = $Torrent['TorrentURI'];
												$DownloadArr[$SerieTitle.'-'.$SerieEp[0].$SerieEp[1]][] = $Episode['EpisodeID'];
												$DownloadArr[$SerieTitle.'-'.$SerieEp[0].$SerieEp[1]][] = $Torrent['TorrentID'];
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
		if(sizeof($DownloadArr)) {
			return $DownloadArr;
		}
		else {
			return FALSE;
		}
	}
	
	function DeleteSerie($SerieID) {
		$Drives = Drives::GetDrivesFromDB();
		
		if(is_array($Drives)) {
			$Serie = $this->PDO->query('SELECT SerieTitle, SerieTitleAlt FROM Series WHERE SerieID = "'.$SerieID.'"')->fetch();
			
			if(strlen($Serie['SerieTitle'])) {
				foreach($Drives AS $Drive) {
					$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveRoot'] : $Drive['DriveLetter'];
					
					Drives::RecursiveDirRemove($DriveRoot.'/Media/TV/'.$Serie['SerieTitle']);
					
					if(strlen($Serie['SerieTitleAlt'])) {
						Drives::RecursiveDirRemove($DriveRoot.'/Media/TV/'.$Serie['SerieTitleAlt']);
					}
				}
			}
			
			
			$SerieDeletePrep = $this->PDO->prepare('DELETE FROM Series WHERE SerieID = :ID');
			$SerieDeletePrep->execute(array(':ID' => $SerieID));
			
			$EpisodesDeletePrep = $this->PDO->prepare('DELETE FROM Episodes WHERE SeriesKey = :ID');
			$EpisodesDeletePrep->execute(array(':ID' => $SerieID));
			
			Hub::AddLog(EVENT.'Series', 'Success', 'Deleted "'.$Serie['SerieTitle'].'" from the database', 0, 'clean');
		}
		else {
			Hub::AddLog(EVENT.'Drives', 'Failure', 'Unable to get drive data from the database while deleting "'.$Serie['SerieTitle'].'"');
		}
	}
	
	function MakeThumbnail($src, $dst, $width, $height, $crop=0) {
		if(!list($w, $h) = getimagesize($src)) return "Unsupported picture type!";
	
		$type = strtolower(substr(strrchr($src,"."),1));
		if($type == 'jpeg') $type = 'jpg';

		switch($type){
			case 'bmp': $img = imagecreatefromwbmp($src); break;
			case 'gif': $img = imagecreatefromgif($src); break;
			case 'jpg': $img = imagecreatefromjpeg($src); break;
			case 'png': $img = imagecreatefrompng($src); break;
			default : return "Unsupported picture type!";
		}
	
		// resize
		if($crop){
			if($w < $width or $h < $height) return "Picture is too small!";
			$ratio = max($width/$w, $height/$h);
			$h = $height / $ratio;
			$x = ($w - $width / $ratio) / 2;
			$w = $width / $ratio;
		}
		else{
			if($w < $width and $h < $height) return "Picture is too small!";
			$ratio = min($width/$w, $height/$h);
			$width = $w * $ratio;
			$height = $h * $ratio;
			$x = 0;
		}
	
		$new = imagecreatetruecolor($width, $height);
	
		// preserve transparency
		if($type == "gif" or $type == "png"){
			imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
			imagealphablending($new, false);
			imagesavealpha($new, true);
		}
	
		imagecopyresampled($new, $img, 0, 0, $x, 0, $width, $height, $w, $h);
	
		switch($type){
			case 'bmp': imagewbmp($new, $dst); break;
			case 'gif': imagegif($new, $dst); break;
			case 'jpg': imagejpeg($new, $dst); break;
			case 'png': imagepng($new, $dst); break;
		}
		
		return true;
	}
	
	function DownloadPoster($SerieID, $ForceNew = FALSE) {
		$Serie = $this->PDO->query('SELECT SerieTitle, SeriePoster AS Poster FROM Series WHERE SerieID = '.$SerieID)->fetch();
		$FileInfo = pathinfo($this->TheTVDBAPI->GetPoster($Serie['Poster']));
		
		if(isset($FileInfo['extension'])) {
			if(!is_file(APP_PATH.'/posters/'.$FileInfo['filename'].'.'.$FileInfo['extension']) || $ForceNew) {
				$ch = curl_init($this->TheTVDBAPI->GetPoster($Serie['Poster']));
				$fp = fopen(APP_PATH.'/tmp/'.$FileInfo['filename'].'.'.$FileInfo['extension'], 'wb');
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_exec($ch);
				curl_close($ch);
				fclose($fp);
			
				$this->MakeThumbnail(APP_PATH.'/tmp/'.$FileInfo['filename'].'.'.$FileInfo['extension'], APP_PATH.'/posters/thumbnails/'.$FileInfo['filename'].'.'.$FileInfo['extension'],150,221);
			
				Hub::AddLog(EVENT.'Series', 'Success', 'Downloaded a new poster for "'.$Serie['SerieTitle'].'"');
			
				rename(APP_PATH.'/tmp/'.$FileInfo['filename'].'.'.$FileInfo['extension'], APP_PATH.'/posters/'.$FileInfo['filename'].'.'.$FileInfo['extension']);
			}
		}
	}
	
	function RefreshSerie($SerieID, $SingleSerie = TRUE) {
		$SeriesUpdated = $EpisodesAdded = $EpisodesUpdated = 0;
		try {
			$this->TheTVDBAPI->SetPreviousUpdateTime(0);
			$this->TheTVDBAPI->SetLanguage('en');
		
			$Serie = $this->PDO->query('SELECT SerieTitle, SerieTheTVDBID FROM Series WHERE SerieID = '.$SerieID)->fetch();
			$SerieTheTVDBID = $Serie['SerieTheTVDBID'];
			$SerieTitle     = $Serie['SerieTitle'];
			
			$this->DownloadPoster($SerieID);
			
			$SeriesInfo = $this->TheTVDBAPI->GetSeriesInfo($SerieTheTVDBID);
			
			if($SeriesInfo) {
				foreach($SeriesInfo AS $Serie) {
					if($Serie->SeriesName) {
						$Serie->SeriesName = str_replace(array(':', '\'', '(', ')', '*'), '', $Serie->SeriesName);
						$Serie->Genre      = str_replace('|', ', ', trim($Serie->Genre, '|'));
					
						$SerieUpdatePrep = $this->PDO->prepare('UPDATE Series SET SerieTitle = :Title, SeriePlot = :Plot, SerieContentRating = :ContentRating, SerieIMDBID = :IMDBID, SerieRating = :Rating, SerieRatingCount = :RatingCount, SerieBanner = :Banner, SerieFanArt = :FanArt, SeriePoster = :Poster, SerieFirstAired = :FirstAired, SerieAirDay = :AirDay, SerieAirTime = :AirTime, SerieRuntime = :Runtime, SerieNetwork = :Network, SerieStatus = :Status, SerieGenre = :Genre WHERE SerieTheTVDBID = :TheTVDBID');
					
						$SerieUpdatePrep->execute(array(':Title'         => $Serie->SeriesName,
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
					                                    ':Runtime'       => $Serie->Runtime,
					                                    ':Network'       => $Serie->Network,
					                                    ':Status'        => $Serie->Status,
					                                    ':Genre'         => $Serie->Genre,
					                                    ':TheTVDBID'     => $SerieTheTVDBID));
		
						$EpisodeAirTime = $Serie->Airs_Time;
						$TimeZoneOffset = ($Serie->Network == 'BBC Two') ? '+1 hour' : '+6 hours';
						$SeriesUpdated++;
					}
					else {
						$Episode = $this->PDO->query('SELECT EpisodeTheTVDBID FROM Episodes WHERE EpisodeTheTVDBID = "'.$Serie->id.'"')->fetch();
					
						if(strlen($Episode['EpisodeTheTVDBID'])) {
							$Date = date('d.m.Y', strtotime($Serie->FirstAired));
							$Time = date('H:i',   strtotime($EpisodeAirTime));
							$EpisodeAirDate =     strtotime($TimeZoneOffset, strtotime($Date.' '.$Time));
			
							$EpisodeUpdatePrep = $this->PDO->prepare('UPDATE Episodes SET EpisodeSeason = :Season, EpisodeEpisode = :Episode, EpisodeTitle = :Title, EpisodePlot = :Plot, EpisodeRating = :Rating, EpisodeRatingCount = :RatingCount, EpisodeBanner = :Banner, EpisodeAirDate = :AirDate WHERE EpisodeTheTVDBID = :TheTVDBID');
							$EpisodeUpdatePrep->execute(array(':Season'      => $Serie->SeasonNumber,
						                                      ':Episode'     => $Serie->EpisodeNumber,
						                                      ':Title'       => $Serie->EpisodeName,
						                                      ':Plot'        => $Serie->Overview,
						                                      ':Rating'      => $Serie->Rating,
						                                      ':RatingCount' => $Serie->RatingCount,
						                                      ':Banner'      => $Serie->filename,
						                                      ':AirDate'     => $EpisodeAirDate,
						                                      ':TheTVDBID'   => $Serie->id));
			
						    $EpisodesUpdated++;
						}
						else {
							$Date = date('d.m.Y', strtotime($Serie->FirstAired));
							$Time = date('H:i',   strtotime($EpisodeAirTime));
							$EpisodeAirDate =     strtotime($TimeZoneOffset, strtotime($Date.' '.$Time));
			
							$EpisodeAddPrep = $this->PDO->prepare('INSERT INTO Episodes (EpisodeID, EpisodeDate, EpisodeSeason, EpisodeEpisode, EpisodeTitle, EpisodePlot, EpisodeRating, EpisodeRatingCount, EpisodeBanner, EpisodeAirDate, SeriesKey, EpisodeTheTVDBID) VALUES (NULL, :Date, :Season, :Episode, :Title, :Plot, :Rating, :RatingCount, :Banner, :AirDate, :SeriesKey, :EpisodeTheTVDBID)');
						
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
			
							$EpisodesAdded++;
						}
					}
				}
			}
		}
		catch(Exception $e) {
			echo '<strong>Exception:</strong> '.$e->getMessage().' in '.$e->getTraceAsString();
		}
	
		if($SingleSerie) {
			if(($SeriesUpdated + $EpisodesAdded + $EpisodesUpdated) > 0) {
				Hub::AddLog(EVENT.'Series', 'Success', 'Updated '.$EpisodesUpdated.' and added '.$EpisodesAdded.' episodes to "'.$SerieTitle.'".');
			}
		}
		else {
			return $SeriesUpdated.'-'.$EpisodesAdded.'-'.$EpisodesUpdated;
		}
	}
	
	function RefreshAllSeries() {
		if(!strlen(EVENT)) {
			if(Hub::CheckLock()) {
				return FALSE;
			}
			else {
				Hub::Lock();
			}
		}
		
		$Series = $this->GetSeries();
		
		$SeriesUpdated = $EpisodesAdded = $EpisodesUpdated = 0;
		foreach($Series AS $Serie) {
			$RefreshStat = explode('-', $this->RefreshSerie($Serie['SerieID'], FALSE));
			
			$SeriesUpdated   += $RefreshStat[0];
			$EpisodesAdded   += $RefreshStat[1];
			$EpisodesUpdated += $RefreshStat[2];
		}
		
		if(($SeriesUpdated + $EpisodesAdded + $EpisodesUpdated) > 0) {
			Hub::AddLog(EVENT.'Series', 'Success', 'Refreshed '.$SeriesUpdated.' series. Updated '.$EpisodesUpdated.' and added '.$EpisodesAdded.' episodes.');
			Hub::NotifyUsers('SerieDataRefresh', 'Series', 'Refreshed '.$SeriesUpdated.' series. Updated '.$EpisodesUpdated.' and added '.$EpisodesAdded.' episodes.');
		}
		
		$RefreshPrep = $this->PDO->prepare('UPDATE Hub SET Value = :Time WHERE Setting = "LastSerieRefresh"');
		$RefreshPrep->execute(array(':Time' => time()));
		
		if(!strlen(EVENT)) {
			Hub::Unlock();
		}
	}
	
	function GetSeriesDirectories($DriveRoot = '') {
		$SeriesDirArr = array();
		
		if($DriveRoot) {
			if(is_dir($DriveRoot.'/Media/TV/')) {
				$SeriesDir = glob($DriveRoot.'/Media/TV/*');
				$SeriesDirArr = array_merge($SeriesDirArr, $SeriesDir);
			}
		}
		else {
			$Drives = Drives::GetDrivesFromDB();
			if(is_array($Drives)) {
				foreach($Drives AS $Drive) {
					$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveRoot'] : $Drive['DriveLetter'];
					
					if(is_dir($DriveRoot.'/Media/TV/')) {
						$SeriesDir = glob($DriveRoot.'/Media/TV/*');
						$SeriesDirArr = array_merge($SeriesDirArr, $SeriesDir);
					}
				}
			}
		}
		
		return $SeriesDirArr;
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
					$SerieEpisodes = Hub::RecursiveGlob($SerieDirectory, '{*.mp4,*.avi,*.mkv}', GLOB_BRACE);
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
	
	function RebuildEpisodes() {
		if(!strlen(EVENT)) {
			if(Hub::CheckLock()) {
				return FALSE;
			}
			else {
				Hub::Lock();
			}
		}
			
		$RebuildTimeStart = time();
		$EpisodesRebuilt = 0;
		$Drives = Drives::GetDrivesFromDB();
		if(is_array($Drives)) {
			$EpisodesPrep = $this->PDO->prepare('UPDATE Episodes SET EpisodeFile = ""');
			$EpisodesPrep->execute();
			
			foreach($Drives AS $Drive) {
				$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveRoot'] : $Drive['DriveLetter'];
				$SeriesInfoArr = $this->GetSeriesInfo($DriveRoot, TRUE);
		
				foreach($SeriesInfoArr AS $SerieTitle => $Series) {
					foreach($Series['Episodes'] AS $Episode) {
						$Slash        = strrpos($Episode, '/');
						$Location     = substr($Episode, 0, ($Slash + 1));
						$FileLength   = (strlen($Episode) - $Slash);
						$File         = substr($Episode, ($Slash + 1), $FileLength);
							
						$ParsedInfo = RSS::ParseRelease($File);
						
						if($ParsedInfo) {
							$Serie = $this->PDO->query('SELECT Series.SerieID FROM Series WHERE SerieTitle = "'.$SerieTitle.'" OR SerieTitleAlt = "'.$SerieTitle.'"')->fetch();
							
							if(strlen($Serie['SerieID'])) {
								if(is_file($Location.$File)) {
									if(array_key_exists('Episodes', $ParsedInfo)) {
										foreach($ParsedInfo['Episodes'] AS $Episodes) {
											$EpisodeUpdatePrep = $this->PDO->prepare('UPDATE Episodes SET EpisodeFile = :EpisodeFile WHERE SeriesKey = :SeriesKey AND EpisodeSeason = :Season AND EpisodeEpisode = :Episode');
											$EpisodeUpdatePrep->execute(array(':EpisodeFile' => $Location.$File,
										                                  	  ':SeriesKey'   => $Serie['SerieID'],
										                                      ':Season'      => $Episodes[0],
										                                      ':Episode'     => $Episodes[1]));
										
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
		
		$RebuildPrep = $this->PDO->prepare('UPDATE Hub SET Value = :Time WHERE Setting = "LastSerieRebuild"');
		$RebuildPrep->execute(array(':Time' => time()));
		
		if(!strlen(EVENT)) {
			Hub::Unlock();
		}
		
		Hub::AddLog(EVENT.'Series', 'Success', 'Rebuilt '.$EpisodesRebuilt.' episodes divided over '.sizeof($this->GetSeries()).' series.');
		Hub::NotifyUsers('EpisodeDataRebuilt', 'Series', 'Rebuilt '.$EpisodesRebuilt.' episodes divided over '.sizeof($this->GetSeries()).' series.');
	}
	
	function DeleteEpisode($ID) {
		$Episode = $this->PDO->query('SELECT EpisodeFile FROM Episodes WHERE EpisodeID = "'.$ID.'"')->fetch();
		
		if($Episode['EpisodeFile']) {
			$EpisodePrep = $this->PDO->prepare('UPDATE Episodes SET EpisodeFile = "" AND TorrentKey = "" WHERE EpisodeID = :ID');
			$EpisodePrep->execute(array(':ID' => $ID));
		                             
			Hub::AddLog(EVENT.'Series', 'Success', 'Deleted "'.$Episode['EpisodeFile'].'"');
		}
	}	
	
	function RebuildFolders() {
		if(!strlen(EVENT)) {
			if(Hub::CheckLock()) {
				return FALSE;
			}
			else {
				Hub::Lock();
			}
		}
		
		$Drives = Drives::GetDrivesFromDB();
		$Series = $this->GetSeries();
		if(is_array($Drives) && is_array($Series)) {
			$RebuiltFolders = 0;
			foreach($Drives AS $Drive) {
				$DriveRoot = ($Drive['DriveNetwork']) ? $Drive['DriveRoot'] : $Drive['DriveLetter'];
				
				foreach($Series AS $Serie) {
					$Folder = $DriveRoot.'/Media/TV/'.$Serie['SerieTitle'];
					if(!is_dir($Folder)) {
						if(@mkdir($Folder)) {
							$RebuiltFolders++;
						}
					}
				}
			}
			
			if($RebuiltFolders) {
				Hub::AddLog(EVENT.'Series', 'Success', 'Created '.$RebuiltFolders.' missing folders divided over '.sizeof($Series).' series.');
			}
		}
		
		$RebuildPrep = $this->PDO->prepare('UPDATE Hub SET Value = :Time WHERE Setting = "LastFolderRebuild"');
		$RebuildPrep->execute(array(':Time' => time()));
		
		if(!strlen(EVENT)) {
			Hub::Unlock();
		}
	}
}
?>