<?php
class XBMC extends Hub {
	public $XBMCRPC;
	
	function Connect($Zone = '') {
		if($Zone == 'default') {
			$Zone = Zones::GetZoneByName(Zones::GetDefaultZone());
		}
		else {
			$Zone = Zones::GetZoneByName(Zones::GetCurrentZone());
		}
		
		if(is_array($Zone)) {
			require_once APP_PATH.'/libraries/xbmc-rpc/rpc/HTTPClient.php';
			try {
				$this->XBMCRPC = new XBMC_RPC_HTTPClient($Zone['ZoneXBMCUsername'].':'.$Zone['ZoneXBMCPassword'].'@'.$Zone['ZoneXBMCHost'].':'.$Zone['ZoneXBMCPort']);
			}
			catch(XBMC_RPC_ConnectionException $e) {
				$this->Error[] = $e->getMessage();
			}
		}
		else {
			$this->Error[] = 'Unable to get XBMC API credentials';
		}
	}
	
	function CheckConnection($User, $Pass, $Host, $Port) {
		require_once APP_PATH.'/libraries/xbmc-rpc/rpc/HTTPClient.php';
		try {
			$TempConnection = new XBMC_RPC_HTTPClient($User.':'.$Pass.'@'.$Host.':'.$Port);
			
			if(is_object($TempConnection)) {
				unset($TempConnection);
				
				return TRUE;
			}
		}
		catch(XBMC_RPC_ConnectionException $e) {
			die($e->getMessage());
		}
	}
	
	function PlayFile($File) {
		$File = str_replace('smb:', '', $File);
		$NetworkFile = Drives::GetNetworkLocation($File);
		$LocalFile   = Drives::GetLocalLocation($File);
		
		if(is_file($LocalFile) || is_file($NetworkFile)) {
			try {
				$NetworkFile = 'smb:'.$NetworkFile;
				
 				return $this->XBMCRPC->Player->Open(array('item' => array('file' => $NetworkFile))); // USE NETWORK FILE
			}
			catch(XBMC_RPC_Exception $e) {
				die($e->getMessage());
			}
		}
		else {
			echo 'No such file: '.$LocalFile;
		}
	}
	
	function PlayPause($PlayerID) {
		try {
			return $this->XBMCRPC->Player->PlayPause(array('playerid' => 1));
		}
		catch(XBMC_RPC_Exception $e) {
			die($e->getMessage());
		}
	}
	
	function PlayStop($PlayerID) {
		try {
			return $this->XBMCRPC->Player->Stop(array('playerid' => 1));
		}
		catch(XBMC_RPC_Exception $e) {
			die($e->getMessage());
		}
	}
	
	function ScanForContent() {
		try {
			return $this->XBMCRPC->VideoLibrary->Scan();
		}
		catch(XBMC_RPC_Exception $e) {
			die($e->getMessage());
		}
	}
	
	function CleanLibrary() {
		try {
			return $this->XBMCRPC->VideoLibrary->Clean();
		}
		catch(XBMC_RPC_Exception $e) {
			die($e->getMessage());
		}
	}
	
	function Notification($Sender, $Message) {
		try {
			return $this->XBMCRPC->JSONRPC->NotifyAll(array('sender' => $Sender, 'message' => $Message));
		}
		catch(XBMC_RPC_Exception $e) {
			die($e->getMessage());
		}
	}
	
	function GetRecentlyAddedEpisodes() {
		try {
			return $this->XBMCRPC->VideoLibrary->GetRecentlyAddedEpisodes();
		}
		catch(XBMC_RPC_Exception $e) {
			die($e->getMessage());
		}
	}
	
	function GetRecentlyAddedMovies() {
		try {
			return $this->XBMCRPC->VideoLibrary->GetRecentlyAddedMovies(array(
				'limits' => array('start' => 0, 'end' => 33),
				'properties' => array(
					'genre', 'trailer', 'tagline', 'plot', 'plotoutline', 'title',
					'originaltitle', 'file', 'runtime', 'year', 'rating', 'playcount', 'thumbnail'
				)));
		}
		catch(XBMC_RPC_Exception $e) {
			die($e->getMessage());
		}
	}
	
	function GetMovies() {
		try {
			return $this->XBMCRPC->VideoLibrary->GetMovies(array(
				'properties' => array(
					'genre', 'director', 'trailer', 'tagline', 'plot', 'plotoutline', 'title',
					'originaltitle', 'lastplayed', 'file', 'runtime', 'year', 'playcount', 'rating', 'thumbnail'
				)));
		}
		catch(XBMC_RPC_Exception $e) {
			die($e->getMessage());
		}
	}
	
	function GetMovieDetails($ID) {
		try {
			return $this->XBMCRPC->VideoLibrary->GetMovieDetails(array(
				'movieid' => (int) $ID,
				'properties' => array('title', 'genre', 'year', 'rating', 'director', 'trailer', 'tagline', 'plot', 'plotoutline', 'originaltitle', 'lastplayed', 'playcount', 'writer', 'studio', 'mpaa', 'cast', 'country', 'imdbnumber', 'premiered', 'productioncode', 'runtime', 'set', 'showlink', 'streamdetails', 'top250', 'votes', 'fanart', 'thumbnail', 'file', 'sorttitle', 'resume', 'setid')
			));
		}
		catch(XBMC_RPC_Exception $e) {
			die($e->getMessage());
		}
	}
	
	function GetImage($Image) {
		try {
			$Zone = Zones::GetZoneByName(Zones::GetCurrentZone());
			$Image = $this->XBMCRPC->Files->PrepareDownload(array('path' => $Image));
			
			return 'http://'.$Zone['ZoneXBMCUsername'].':'.$Zone['ZoneXBMCPassword'].'@'.$Zone['ZoneXBMCHost'].':'.$Zone['ZoneXBMCPort'].'/'.$Image['details']['path'];
		}
		catch(XBMC_RPC_Exception $e) {
			die($e->getMessage());
		}
	}
	
	function CacheCovers($ForceNew = FALSE) {
		$Movies = self::GetMovies();
		$CoverCount = 0;
		if(is_array($Movies)) {
			foreach($Movies['movies'] AS $Movie) {
				if(!is_file(APP_PATH.'/posters/movie-'.$Movie['movieid'].'.jpg') || $ForceNew) {
					if(array_key_exists('thumbnail', $Movie)) {
						$Cover = file_get_contents(self::GetImage($Movie['thumbnail']));
						if(strlen($Cover)) {
							$CoverFile = APP_PATH.'/posters/movie-'.$Movie['movieid'].'.jpg';
							if($FileHandle = fopen($CoverFile, 'w')) {
								if(fwrite($FileHandle, $Cover) !== FALSE) {
									Series::MakeThumbnail(APP_PATH.'/posters/movie-'.$Movie['movieid'].'.jpg', APP_PATH.'/posters/thumbnails/movie-'.$Movie['movieid'].'.jpg', 150, 221);
									
									$CoverCount++;
								}
								
								fclose($FileHandle);
							}
						}
					}
				}
			}
		}
		
		if($CoverCount) {
			Hub::AddLog(EVENT.'Movies', 'Success', 'Cached '.$CoverCount.' movie posters');
		}
	}
	
	function MakeRequest($One, $Two, $Params = '') {
		try {
			if(empty($Params)) {
				$Response = $this->XBMCRPC->$One->$Two();
			}
			else {
				$Response = $this->XBMCRPC->$One->$Two($Params);
			}
		}
		catch(XBMC_RPC_Exception $e) {
			die($e->getMessage());
		}
		
		return $Response;
	}
	
	function GetCommands() {
		try {
			$response = $this->XBMCRPC->JSONRPC->Introspect();
		}
		catch(XBMC_RPC_Exception $e) {
			die($e->getMessage());
		}
		
		print '<p>The following commands are available according to XBMC:</p>';
		if($this->XBMCRPC->isLegacy()) {
			foreach($response['commands'] as $command) {
				printf('<p><strong>%s</strong><br />%s</p>', $command['command'], $command['description']);
			}
		}
		else {
			$i = 0;
			foreach ($response['methods'] as $command => $commandData) {
				$description = isset($commandData['description']) ? $commandData['description'] : '';
				
				$color = sizeof($commandData['params']) ? 'red' : 'black';
				echo '
				<div id="command-'.$i.'" style="color: '.$color.'">
				 <strong>'.$command.'</strong><br />
				 '.$description.'<br />
				</div>'."\n";
				
				if(sizeof($commandData['params'])) {
					echo '<div style="display:none" id="data-'.$i.'">'."\n";
					Hub::d($commandData['params']);
					echo '</div><br />';
				}
				else {
					echo '<br />';
				}
				
				$i++;
			}
		}
	}
	
	function FindDatabaseNames() {
		$DeterminedDatabases = array();
		
		if(is_dir(Hub::GetSetting('XBMCDataFolder'))) {
			$Files = glob(Hub::GetSetting('XBMCDataFolder').'/userdata/Database/*.db');
			
			if(is_array($Files)) {
				$Databases = $this->PDO->query('SHOW DATABASES')->fetchAll();
				
				foreach($Files AS $File) {
					if(stristr($File, 'videos') || stristr($File, 'music')) {
						preg_match('/[0-9]+/', basename($File), $Matches);
						
						if(is_array($Matches)) {
							if(sizeof($Matches) == 1) {
								if(is_array($Databases)) {
									foreach($Databases AS $Database) {
										if(strrpos($Database['Database'], $Matches[0]) !== FALSE) {
											$DeterminedDatabases[] = $Database['Database'];
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
		if(sizeof($DeterminedDatabases)) {
			return $DeterminedDatabases;
		}
		else {
			return FALSE;
		}
	}
	
	function GetLogFile($ToTime = '', $Lines = 50) {
		if(is_file(Hub::GetSetting('XBMCDataFolder').'/xbmc.log')) {
			$LogFile = array_reverse(file(Hub::GetSetting('XBMCDataFolder').'/xbmc.log', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
		
			$LogArr = array();
			$Line = 0;
			foreach($LogFile AS $LogLine) {
				list($Time, $T, $M) = explode(' ', $LogLine);
				$Text = str_replace($Time.' '.$T.' '.$M.' ', '', $LogLine);
			
				if(!empty($ToTime) && $Time == $ToTime) {
					break;
				}
				echo '
				<tr>
				 <td rel="time" style="text-align:center;">'.$Time.'</td>
				 <td style="text-align:center;width: 60px">'.$T.'</td>
				 <td style="text-align:center;width: 60px">'.$M.'</td>
				 <td>'.$Text.'</td>
				</tr>'."\n";
				if($Line++ == $Lines) {
					break;
				}
			}
		}
	}
}
?>