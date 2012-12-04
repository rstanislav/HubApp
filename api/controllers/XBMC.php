<?php
/**
 * //@protected
**/
class XBMC {
	private $PDO;
	private $XBMC = null;
	
	function __construct() {
		$this->PDO = DB::Get();
	}
	
	function Connect($Zone = 'default') {
		if(!is_object($this->XBMC)) {
			$Zone = $this->GetZoneData($Zone);
			
			require_once APP_PATH.'/api/libraries/xbmc-rpc/rpc/HTTPClient.php';
			try {
				$this->XBMC = new XBMC_RPC_HTTPClient($Zone['XBMCUser'].':'.$Zone['XBMCPassword'].'@'.$Zone['XBMCHost'].':'.$Zone['XBMCPort']);
			}
			catch(XBMC_RPC_ConnectionException $e) {
				throw new RestException(503, 'XBMC: '.$e->getMessage());
			}
		}
	}
	
	function GetZoneData($Zone = 'default') {
		try {
			if($Zone == 'default') {
				$ZonePrep = $this->PDO->prepare('SELECT
			                                 	 	*
			                                 	 FROM
			                                  	 	Zones
			                                 	 WHERE
			                                 	 	IsDefault = 1');
			
			}
			else {
				$ZonePrep = $this->PDO->prepare('SELECT
				                             	 	*
				                             	 FROM
				                              	 	Zones
				                             	 WHERE
				                             	 	Name = :Name');
			}
			
			$ZonePrep->execute(array(':Name' => $Zone));
			
			if($ZonePrep->rowCount()) {
				return $ZonePrep->fetch();
			}
			else {
				throw new RestException(404, 'Unable to find any zones in the database matching your criteria');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
	}
	
	/**
	 * @url GET /movies
	**/
	function MoviesAll() {
		$this->Connect();
		
		try {
			$MoviesTmp = $this->XBMC->VideoLibrary->GetMovies(array('properties' => array('genre', 'director', 'trailer', 'tagline', 'plot', 'plotoutline',
			                                                                        'title', 'originaltitle', 'lastplayed', 'file', 'runtime', 'year',
			                                                                        'playcount', 'rating', 'thumbnail', 'imdbnumber')))['movies'];
			
			osort($MoviesTmp, 'label');
			
			$DrivesObj = new Drives;
			$Movies = array();
			foreach($MoviesTmp AS $Movie) {
				if(is_file(APP_PATH.'/posters/movies/'.$Movie['imdbnumber'].'-small.jpg')) {
					$Movie['poster']      = 'posters/movies/'.$Movie['imdbnumber'].'.jpg';
					$Movie['postersmall'] = 'posters/movies/'.$Movie['imdbnumber'].'-small.jpg';
				}
				else {
					$Movie['poster']      = 'images/poster-unavailable.png';
					$Movie['postersmall'] = 'images/poster-unavailable.png';
				}
				
				if(strstr($Movie['file'], 'stack')) {
					$Path = str_replace('stack://', '', $Movie['file']);
					
					$Movie['files'] = explode(', ', $Path);
					
					$LocalFileArr = array();
					foreach($Movie['files'] AS $File) {
						$LocalFileArr[] = array('network' => $File,
						                        'local'   => $DrivesObj->GetLocalLocation($File));
					}
					
					$Movie['files'] = $LocalFileArr;
				}
				
				$Movie['filelocal'] = $DrivesObj->GetLocalLocation($Movie['file']);
				
				$Movies[] = $Movie;
			}
			
			return $Movies;
		}
		catch(XBMC_RPC_Exception $e) {
			throw new RestException(503, 'XBMC: '.$e->getMessage());
		}
	}
	
	/**
	 * @url GET /movies/recent
	 * @url GET /movies/recent/:Num
	**/
	function GetRecentlyAddedMovies($Num = 33) {
		$this->Connect();
		
		if(!is_numeric($Num)) {
			throw new RestException(412, 'Parameter must be a numeric value');
		}
		
		try {
			$MoviesTmp = $this->XBMC->VideoLibrary->GetRecentlyAddedMovies(array('limits'     => array('start' => 0, 'end' => (int) $Num),
				                                                           'properties' => array('genre', 'trailer', 'tagline', 'plot', 'plotoutline', 'title',
					                                                                             'originaltitle', 'file', 'runtime', 'year', 'rating', 'playcount', 'thumbnail', 'imdbnumber')))['movies'];
			
			$DrivesObj = new Drives;
			$Movies = array();
			foreach($MoviesTmp AS $Movie) {
				if(is_file(APP_PATH.'/posters/movies/'.$Movie['imdbnumber'].'-small.jpg')) {
					$Movie['poster']      = 'posters/movies/'.$Movie['imdbnumber'].'.jpg';
					$Movie['postersmall'] = 'posters/movies/'.$Movie['imdbnumber'].'-small.jpg';
				}
				else {
					$Movie['poster']      = 'images/poster-unavailable.png';
					$Movie['postersmall'] = 'images/poster-unavailable.png';
				}
				
				if(strstr($Movie['file'], 'stack')) {
					$Path = str_replace('stack://', '', $Movie['file']);
					
					$Movie['files'] = explode(', ', $Path);
					
					$LocalFileArr = array();
					foreach($Movie['files'] AS $File) {
						$LocalFileArr[] = array('network' => $File,
						                        'local'   => $DrivesObj->GetLocalLocation($File));
					}
					
					$Movie['files'] = $LocalFileArr;
				}
				
				$Movie['filelocal'] = $DrivesObj->GetLocalLocation($Movie['file']);
				
				$Movies[] = $Movie;
			}
			
			return $Movies;
		}
		catch(XBMC_RPC_Exception $e) {
			throw new RestException(503, 'XBMC: '.$e->getMessage());
		}
	}
	
	function GetImage($Image) {
		try {
			$Zone = $this->GetZoneData('default');
			$Image = $this->XBMC->Files->PrepareDownload(array('path' => $Image));
			
			return 'http://'.$Zone['XBMCUser'].':'.$Zone['XBMCPassword'].'@'.$Zone['XBMCHost'].':'.$Zone['XBMCPort'].'/'.$Image['details']['path'];
		}
		catch(XBMC_RPC_Exception $e) {
			die($e->getMessage());
		}
	}
	
	/**
	 * @url GET /movies/cachecovers
	**/
	function CacheCovers($ForceNew = FALSE) {
		$this->Connect();
		
		try {
			$Movies = $this->MoviesAll();
		}
		catch(RestException $e) {
		}
		
		$CoverCount = 0;
		if(is_array($Movies)) {
			foreach($Movies AS $Movie) {
				if(!is_file(APP_PATH.'/posters/movies/'.$Movie['imdbnumber'].'.jpg') || $ForceNew) {
					if(array_key_exists('thumbnail', $Movie)) {
						$Cover = file_get_contents($this->GetImage($Movie['thumbnail']));
						if(strlen($Cover)) {
							$CoverFile = APP_PATH.'/posters/movies/'.$Movie['imdbnumber'].'.jpg';
							if($FileHandle = fopen($CoverFile, 'w')) {
								if(fwrite($FileHandle, $Cover) !== FALSE) {
									MakeThumbnail(APP_PATH.'/posters/movies/'.$Movie['imdbnumber'].'.jpg', APP_PATH.'/posters/movies/'.$Movie['imdbnumber'].'-small.jpg', 150, 221);
									
									$CoverCount++;
								}
								
								fclose($FileHandle);
							}
						}
					}
				}
			}
		}
		
		$LogEntry = '';
		if($CoverCount) {
			$LogEntry = 'Cached '.$CoverCount.' movie posters';
			AddLog(EVENT.'Movies', 'Success', $LogEntry);
		}
		
		throw new RestException(200, $LogEntry);
	}
	
	/**
	 * @url GET /movies/:ID
	**/
	function GetMovieByID($ID) {
		$this->Connect();
		
		if(!is_numeric($ID)) {
			throw new RestException(412, 'Parameter must be a numeric value');
		}
		
		try {
			return $this->XBMC->VideoLibrary->GetMovieDetails(array('movieid'    => (int) $ID,
				                                                    'properties' => array('title', 'genre', 'year', 'rating', 'director', 'trailer', 'tagline',
				                                                                          'plot', 'plotoutline', 'originaltitle', 'lastplayed', 'playcount',
				                                                                          'writer', 'studio', 'mpaa', 'cast', 'country', 'imdbnumber', 'premiered',
				                                                                          'productioncode', 'runtime', 'set', 'showlink', 'streamdetails', 'top250',
				                                                                          'votes', 'fanart', 'thumbnail', 'file', 'sorttitle', 'resume', 'setid')));
		}
		catch(XBMC_RPC_Exception $e) {
			throw new RestException(503, 'XBMC: '.$e->getMessage());
		}
	}
	
	/**
	 * @url GET /zones
	**/
	function ZonesAll() {
		try {
			$ZonePrep = $this->PDO->prepare('SELECT
			                                 	*
			                                 FROM
			                                  	Zones
			                                 ORDER BY
			                                  	Name');
			                                  	
			$ZonePrep->execute();
			
			if($ZonePrep->rowCount()) {
				return $ZonePrep->fetchAll();
			}
			else {
				throw new RestException(404, 'Unable to find any zones in the database matching your criteria');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
	}
	
	/**
	 * @url GET /players/active
	**/
	function GetActivePlayers() {
		$this->Connect();
		
		try {
			$ActivePlayer = $this->XBMC->Player->GetActivePlayers();
		
			if(sizeof($ActivePlayer) && $ActivePlayer[0]['type'] == 'video') {
				$ItemInfo = $this->XBMC->Player->GetItem(array('playerid' => 1, 'properties' => array('tvshowid', 'duration', 'mpaa', 'writer', 'plotoutline', 'votes', 'year', 'rating', 'season', 'imdbnumber', 'studio', 'showlink', 'showtitle', 'episode', 'country', 'premiered', 'originaltitle', 'cast', 'firstaired', 'tagline', 'top250', 'trailer', 'plot', 'file')));
				
				$PlayerInfo = $this->XBMC->Player->GetProperties(array('playerid' => 1, 'properties' => array('speed', 'subtitleenabled', 'percentage', 'currentaudiostream', 'currentsubtitle', 'audiostreams', 'position', 'subtitles', 'totaltime', 'time')));
				
				$PlayerInfo['time']['formatted'] = sprintf('%02s:%02s:%02s', $PlayerInfo['time']['hours'], $PlayerInfo['time']['minutes'], $PlayerInfo['time']['seconds']);
				$PlayerInfo['totaltime']['formatted'] = sprintf('%02s:%02s:%02s', $PlayerInfo['totaltime']['hours'], $PlayerInfo['totaltime']['minutes'], $PlayerInfo['totaltime']['seconds']);
				$PlayerInfo['status'] = ($PlayerInfo['speed']) ? 'Playing' : 'Paused';
				
				if($ItemInfo['item']['type'] == 'episode') {
					$SeriesObj = new Series;
					$Serie = $SeriesObj->GetSerieByTitle($ItemInfo['item']['showtitle']);
					
					if(is_array($Serie)) {
						if(is_file(APP_PATH.'/'.$Serie[0]['PosterSmall'])) {
							$ItemInfo['item']['postersmall'] = $Serie[0]['PosterSmall'];
						}
				
						if(is_file(APP_PATH.'/'.$Serie[0]['Poster'])) {
							$ItemInfo['item']['poster'] = $Serie[0]['Poster'];
						}
					}
				}
				else if($ItemInfo['item']['type'] == 'movie') {
					$ItemInfo['item']['tagline'] = ($ItemInfo['item']['tagline']) ? $ItemInfo['item']['tagline'] : 'NA';
					$ItemInfo['item']['country'] = ($ItemInfo['item']['country']) ? $ItemInfo['item']['country'] : 'NA';
					$ItemInfo['item']['imdburi'] = ($ItemInfo['item']['imdbnumber']) ? 'http://www.imdb.com/title/'.$ItemInfo['item']['imdbnumber'] : '';
					$PlayerInfo['currentsubtitle'] = (is_array($PlayerInfo['currentsubtitle']) && array_key_exists('name', $PlayerInfo['currentsubtitle'])) ? $PlayerInfo['currentsubtitle']['name'] : 'NA';
					$ItemInfo['item']['year'] = ($ItemInfo['item']['year']) ? ' ('.$ItemInfo['item']['year'].')' : '';
					$ItemInfo['item']['studio'] = ($ItemInfo['item']['studio']) ? $ItemInfo['item']['studio'] : 'NA';
					$ItemInfo['item']['mpaa'] = ($ItemInfo['item']['mpaa']) ? $ItemInfo['item']['mpaa'] : 'NA';
					$ItemInfo['item']['plot'] = ($ItemInfo['item']['plot']) ? nl2br($ItemInfo['item']['plot']) : 'NA';
					
					if(array_key_exists('id', $ItemInfo['item']) && is_file(APP_PATH.'/posters/movies/'.$ItemInfo['item']['imdbnumber'].'-small.jpg')) {
						$ItemInfo['item']['poster'] = 'posters/movies/'.$ItemInfo['item']['imdbnumber'].'-small.jpg';
					}
					else {
						$ItemInfo['item']['poster'] = 'images/poster-unavailable.png';
					}
						
					if($ItemInfo['item']['label'] == $ItemInfo['item']['originaltitle']) {
						$ItemInfo['item']['formatted'] = $ItemInfo['item']['label'];
					}
					else {
						if($ItemInfo['item']['originaltitle']) {
							$ItemInfo['item']['formatted'] = $ItemInfo['item']['label'].' ('.$ItemInfo['item']['originaltitle'].')';
						}
						else {
							$ItemInfo['item']['formatted'] = $ItemInfo['item']['label'];
						}
					}
				}
				
				return array_merge($ItemInfo, $PlayerInfo);
			}
			else {
				throw new RestException(404, 'No active players');
			}
		}
		catch(XBMC_RPC_Exception $e) {
			throw new RestException(503, 'XBMC: '.$e->getMessage());
		}
	}
	
	/**
	 * @url GET /library/newcontentscan
	**/
	function NewContentLibraryScan() {
		$LogActivity = $this->PDO->query('SELECT Date AS NewContent FROM Log WHERE Action = "update" ORDER BY Date DESC LIMIT 1')->fetch();
		$XBMCActivity = $this->PDO->query('SELECT Date AS LastUpdate FROM Log WHERE Type = "Success" AND Event LIKE "%XBMC" AND (Text LIKE "Updated XBMC Library%") ORDER BY Date DESC LIMIT 1')->fetch();
		
		if($LogActivity['NewContent'] > $XBMCActivity['LastUpdate']) {
			$this->Connect();
			
			try {
				$ActivePlayer = $this->XBMC->Player->GetActivePlayers();
			
				if(!sizeof($ActivePlayer)) {
					try {
						$this->LibraryUpdate();
					}
					catch(XBMC_RPC_Exception $e){
						throw new RestException(503, 'XBMC: '.$e->getMessage());
					}
					
					$LogEntry = 'Updated XBMC Library';
					AddLog(EVENT.'XBMC', 'Success', $LogEntry);
					
					throw new RestException(200, $LogEntry);
				}
				else {
					throw new RestException(400, 'Not allowed to start library scan when XBMC is in playback mode');
				}
			}
			catch(XBMC_RPC_Exception $e){
				throw new RestException(503, 'XBMC: '.$e->getMessage());
			}
		}
	}
	
	/**
	 * @url GET /library/clean
	**/
	function LibraryClean() {
		$this->Connect();
		
		try {
			$this->XBMC->VideoLibrary->Clean();
		}
		catch(XBMC_RPC_Exception $e) {
			throw new RestException(503, 'XBMC: '.$e->getMessage());
		}
		
		throw new RestException(200, 'Started cleaning of XBMC library');
	}
	
	/**
	 * @url GET /library/update
	**/
	function LibraryUpdate() {
		$this->Connect();

		try {
			$this->XBMC->VideoLibrary->Scan();
		}
		catch(XBMC_RPC_Exception $e) {
			throw new RestException(503, 'XBMC: '.$e->getMessage());
		}
		
		throw new RestException(200, 'Updated XBMC library');
	}
	
	/**
	 * @url GET /log
	 * @url GET /log/:Lines
	**/
	function GetLog($Lines = 100) {
		if(!is_numeric($Lines)) {
			throw new RestException(412, 'Parameter must be a numeric value');
		}
		
		$LogFile = GetSetting('XBMCDataFolder').'/xbmc.log';
		if(!is_file($LogFile)) {
			throw new RestException(404, 'File "'.$LogFile.'" does not exist');
		}
		else {
			$LogFile = array_reverse(file($LogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
		
			$LogArr = array();
			$Line = 0;
			foreach($LogFile AS $LogLine) {
				list($Time, $T, $M) = explode(' ', $LogLine);
				$Text = str_replace($Time.' '.$T.' '.$M.' ', '', $LogLine);
			
				if(!empty($ToTime) && $Time == $ToTime) {
					break;
				}
				
				$LogArr[] = array($Time, $T, $M, $Text);
				
				if($Line++ == $Lines) {
					break;
				}
			}
			
			return $LogArr;
		}
	}
	
	/**
	 * @url GET /:ZoneName/play
	 * @url POST /:ZoneName/play
	 * @url GET /:ZoneName/pause
	**/
	function Play($ZoneName, $File = '') {
		$this->Connect($ZoneName);
		
		if($File) {
			$File = str_replace('smb:', '', urldecode($File));
			
			$DrivesObj = new Drives;
			$NetworkFile = $DrivesObj->GetNetworkLocation($File);
			$LocalFile   = $DrivesObj->GetLocalLocation($File);
			
			if(is_file($LocalFile) || is_file($NetworkFile)) {
				try {
					$NetworkFile = 'smb:'.$NetworkFile;
					
					$this->XBMC->Player->Open(array('item' => array('file' => $NetworkFile))); // USE NETWORK FILE
				}
				catch(XBMC_RPC_Exception $e) {
					throw new RestException(503, 'XBMC: '.$e->getMessage());
				}
			}
			else {
				throw new RestException(404, $LocalFile.' does not exist');
			}
		}
		else {
			$this->XBMC->Player->PlayPause(array('playerid' => 1));
		}
		
		if($File) {
			$ReturnStr = 'Started playback of "'.$File.'"';
		}
		else {
			$ReturnStr = 'Toggled playback mode';
		}
		
		throw new RestException(200, $ReturnStr);
	}
	
	/**
	 * @url GET /:ZoneName/stop
	**/
	function Stop($ZoneName, $File = '') {
		$this->Connect($ZoneName);
		
		try {
			$this->XBMC->Player->Stop(array('playerid' => 1));
		}
		catch(XBMC_RPC_Exception $e) {
			throw new RestException(503, 'XBMC: '.$e->getMessage());
		}
		
		throw new RestException(200);
	}
	
	/**
	 * @url POST /zones
	**/
	function AddZone($Name, $Host, $Port, $User, $Password) {
		if(empty($Name) || empty($Host) || empty($Port) || empty($User) || empty($Password)) {
			throw new RestException(412, 'Invalid request. Required parameters are "Name", "Host", "Port", "User", "Password"');
		}
		
		$this->CheckConnection($User, $Password, $Host, $Port);
		
		try {
			$ZoneAddPrep = $this->PDO->prepare('INSERT INTO
													Zones
														(Date,
														Name,
														XBMCHost,
														XBMCPort,
														XBMCUser,
														XBMCPassword)
													VALUES
														(:Date,
														:Name,
														:XBMCHost,
														:XBMCPort,
														:XBMCUser,
														:XBMCPassword)');
														
			$ZoneAddPrep->execute(array(':Date'         => time(),
			                            ':Name'         => $Name,
			                            ':XBMCHost'     => $Host,
			                            ':XBMCPort'     => $Port,
			                            ':XBMCUser'     => $User,
			                            ':XBMCPassword' => $Password));
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = 'Added zone "'.$Name.'" to the database';
		
		AddLog(EVENT.'XBMC', 'Success', $LogEntry);
		throw new RestException(201, $LogEntry);
	}
	
	/**
	 * @url GET /update/shared
	**/
	function UpdateSharedMovies() {
		$AllMovies = $this->MoviesAll();
		
		$Movies = array();
		foreach($AllMovies AS $Movie) {
			if(!trim($Movie['label'])) {
				$Title = trim(str_replace('The ', '', trim($Movie['originaltitle'])));
			}
			else {
				$Title = trim(str_replace('The ', '', trim($Movie['label'])));
			}
			
			$Movies[$Title][] = $Movie;
		}
		
		ksort($Movies);
		
		$MovieShare = '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
		
		<html> 
		<head>
		 <meta http-equiv="Content-type" content="text/html; charset=utf-8" /> 
		 <title>Hub &raquo; Share &raquo; Movies</title> 
		 <link type="text/css" rel="stylesheet" href="../css/stylesheet.css" />
		</head>
		
		<body>
		
		<div id="maincontent">
		
		<div class="head">Movies <small><small><small>updated: '.date('d.m.Y H:i').'</small></small></small></div>
		<table width="100%" class="nostyle">
		 <tr>'."\n";
		
		$i = 1;
		foreach($Movies AS $Movie) {
			if(is_file(APP_PATH.'/posters/thumbnails/movie-'.$Movie[0]['movieid'].'.jpg')) {
				$Thumbnail = '../posters/thumbnails/movie-'.$Movie[0]['movieid'].'.jpg';
			}
			else {
				$Thumbnail = '../images/poster-unavailable.png';
			}
			
			if(!empty($Movie[0]['trailer'])) {
				if(strstr($Movie[0]['trailer'], 'plugin.video.youtube')) {
					$MovieTrailerLink = '<a href="http://youtube.com/watch?v='.str_replace('plugin://plugin.video.youtube/?action=play_video&videoid=', '', $Movie[0]['trailer']).'" target="_blank" title="'.$Movie[0]['label'].' ('.$Movie[0]['year'].') Trailer"><img  src="../images/icons/youtube.png" /></a>';
				}
				else if(strstr($Movie[0]['trailer'], 'http://playlist.yahoo.com')) {
					$MovieTrailerLink = '<a href="'.$Movie[0]['trailer'].'" target="_blank" title="'.$Movie[0]['label'].' ('.$Movie[0]['year'].') Trailer"><img  src="../images/icons/yahoo.png" /></a>';
				}
			}
			else {
				$MovieTrailerLink = '<a href="http://youtube.com/results?search_query='.urlencode($Movie[0]['label'].' '.$Movie[0]['year'].' trailer').'" target="_blank" title="Search for trailer on YouTube"><img  src="../images/icons/youtube.png" /></a>';
			}
			
			$Watched = ($Movie[0]['playcount']) ? '<div class="cover-watched">watched</div>' : '';
			
			$MoviePoster = '
			 <div id="Cover-'.$Movie[0]['movieid'].'" class="cover">
			  <img class="poster" width="150" height="250" src="../'.$Movie[0]['postersmall'].'" />
			  '.$Watched.'
			 </div>';
			 
			$MovieTitle = (empty($Movie[0]['label'])) ? $Movie[0]['originaltitle'] : $Movie[0]['label'];
			$MovieShare .= '
			<td style="text-align: center;">
			 <div style="height:310px;">
			  <div style="width: 151px; height: 250px; margin: 0 auto;">'.$MoviePoster.'</div><br />
			  <strong>'.$MovieTitle.' ('.$Movie[0]['year'].') '.$MovieTrailerLink.'</strong>
			 </div>
			</td>'."\n";
			
			if($i++ % 6 == 0) {
				$MovieShare .= '
				</tr>
				<tr>'."\n";
			}
		}
		$MovieShare .= '</table></div>'."\n";
		
		file_put_contents(APP_PATH.'/share/movies.html', $MovieShare);
		
		try {
			$UpdatePrep = $this->PDO->prepare('UPDATE
			                                   	Hub
			                                   SET
			                                   	Value = :Time
			                                   WHERE
			                                   	Setting = "LastMoviesUpdate"');
			                                   	
			$UpdatePrep->execute(array(':Time' => time()));
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = 'Updated "/share/movies.html"';
		AddLog(EVENT.'Public Sharing', 'Success', $LogEntry);
		
		throw new RestException(200, $LogEntry);
	}
	
	/**
	 * @url DELETE /zones/:ID
	**/
	function DeleteZone($ID) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		try {	
			$ZoneDeletePrep = $this->PDO->prepare('DELETE FROM
													Zones
												   WHERE
												   	ID = :ID');
												   	
			$ZoneDeletePrep->execute(array(':ID' => $ID));
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = 'Deleted zone with ID "'.$ID.'"';
		
		AddLog(EVENT.'XBMC', 'Success', $LogEntry);
		throw new RestException(200, $LogEntry);
	}
	
	/**
	 * @url GET /zones/default/:ID
	**/
	function SetDefaultZone($ID) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		try {
			$ZonePrep = $this->PDO->prepare('SELECT
												Name
											 FROM
											 	Zones
											 WHERE
											 	ID = :ID');
			
			$ZonePrep->execute(array(':ID' => $ID));
			
			if($ZonePrep->rowCount()) {
				$ZoneName = $ZonePrep->fetch()['Name'];
				
				try {
					$this->PDO->query('UPDATE
					                   	Zones
					                   SET
					                   	IsDefault = 0');
				}
				catch(PDOException $e) {
					throw new RestException(400, 'MySQL: '.$e->getMessage());
				}
				
				try {
					$ZonePrep = $this->PDO->prepare('UPDATE
					                                 	Zones
					                                 SET
					                                 	IsDefault = 1
					                                 WHERE
					                                 	Name = :Name');
					
					$ZonePrep->execute(array(':Name' => $ZoneName));
				}
				catch(PDOException $e) {
					throw new RestException(400, 'MySQL: '.$e->getMessage());
				}
			}
			else {
				throw new RestException(404, 'Did not find any zones in the database matching your criteria');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = 'Set "'.$ZoneName.'" as default zone';
		
		AddLog(EVENT.'XBMC', 'Success', $LogEntry);
		throw new RestException(200, $LogEntry);
	}
	
	/**
	 * @url POST /zones/update/:ID
	**/
	function UpdateZone($ID) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		$AcceptedParameters = array('Name',
		                            'XBMCHost',
		                            'XBMCPort',
		                            'XBMCUser',
		                            'XBMCPassword');
		
		if(!sizeof($_POST)) {
			throw new RestException(412, 'Invalid request. Accepted parameters are "'.implode(', ', $AcceptedParameters).'"');
		}
		
		$UpdateQuery = 'UPDATE Zones SET ';
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
			$ZonePrep = $this->PDO->prepare($UpdateQuery);
			
			$ZonePrep->execute($PrepArr);
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = 'Updated zone with ID "'.$ID.'"';
		
		AddLog(EVENT.'XBMC', 'Success', $LogEntry);
		throw new RestException(200, $LogEntry);
	}
	
	/**
	 * Internal functions
	**/
	function CheckConnection($User, $Pass, $Host, $Port) {
		require_once APP_PATH.'/api/libraries/xbmc-rpc/rpc/HTTPClient.php';
		
		try {
			$TempConnection = new XBMC_RPC_HTTPClient($User.':'.$Pass.'@'.$Host.':'.$Port);
			
			if(is_object($TempConnection)) {
				unset($TempConnection);
			}
		}
		catch(XBMC_RPC_ConnectionException $e) {
			throw new RestException(412, 'XBMC: '.$e->getMessage());
		}
	}
}
?>