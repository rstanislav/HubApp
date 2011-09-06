<?php
class XBMC extends Hub {
	public $XBMCRPC;
	
	function Connect() {
		$Zone = Zones::GetZoneByName(Zones::GetCurrentZone());
		
		if(is_array($Zone)) {
			require_once APP_PATH.'/libraries/xbmc-rpc/HTTPClient.php';
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
	
	function PlayFile($File) {
		if(is_file($File)) {
			try {
				return $this->XBMCRPC->XBMC->Play(array('file' => $File));
			}
			catch(XBMC_RPC_Exception $e) {
				die($e->getMessage());
			}
		}
	}
	
	function ScanForContent() {
		try {
			return $this->XBMCRPC->VideoLibrary->ScanForContent();
		}
		catch(XBMC_RPC_Exception $e) {
			die($e->getMessage());
		}
	}
	
	function CleanLibrary() {
		// Not implemented in the XBMC JSON RPC yet
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
		        'start' => 0,
		        'end' => 24, 
		        'fields' => array(
		            'genre', 'director', 'trailer', 'tagline', 'plot', 'plotoutline', 'title',
		            'originaltitle', 'lastplayed', 'showtitle', 'firstaired', 'duration', 'season',
		            'episode', 'runtime', 'year', 'playcount', 'rating'
		        )));
		}
		catch(XBMC_RPC_Exception $e) {
		    die($e->getMessage());
		}
	}
	
	function GetMovies() {
		try {
		    return $this->XBMCRPC->VideoLibrary->GetMovies(array(
		        'start' => 0,
		        'fields' => array(
		            'genre', 'director', 'trailer', 'tagline', 'plot', 'plotoutline', 'title',
		            'originaltitle', 'lastplayed', 'showtitle', 'firstaired', 'duration', 'season',
		            'episode', 'runtime', 'year', 'playcount', 'rating'
		        )));
		}
		catch(XBMC_RPC_Exception $e) {
		    die($e->getMessage());
		}
	}
	
	function GetImage($Image) {
		try {
		    $Zone = Zones::GetZoneByName(Zones::GetCurrentZone());
		    $Image = $this->XBMCRPC->Files->Download($Image);
		    
		    return 'http://'.$Zone['ZoneXBMCUsername'].':'.$Zone['ZoneXBMCPassword'].'@'.$Zone['ZoneXBMCHost'].':'.$Zone['ZoneXBMCPort'].'/'.$Image['path'];
		}
		catch(XBMC_RPC_Exception $e) {
		    die($e->getMessage());
		}
	}
	
	function MakeRequest($One, $Two, $Params = '') {
		try {
			$Response = $this->XBMCRPC->$One->$Two($Params);
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
		}else {
		    foreach ($response['methods'] as $command => $commandData) {
		        printf(
		            '<p><strong>%s</strong><br />%s</p>',
		            $command,
		            isset($commandData['description']) ? $commandData['description'] : ''
		        );
		    }
		}
	}
	
	function GetLogFile($ToTime = '', $Lines = 50) {
		$Settings = Hub::GetSettings();
		
		if(is_file($Settings['SettingXBMCLogFile'])) {
			$LogFile = array_reverse(file($Settings['SettingXBMCLogFile'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
		
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