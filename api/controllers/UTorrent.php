<?php
/**
 * //@protected
**/
class UTorrent {
	private $PDO;
	private $UTorrent = null;
	
	function __construct() {
		$this->PDO = DB::Get();
	}
	
	function Connect() {
		if(!is_object($this->UTorrent)) {
			require_once APP_PATH.'/api/libraries/api.utorrent.php';
			
			$UTorrent = array('Host' => GetSetting('UTorrentIP'),
							  'Port' => GetSetting('UTorrentPort'),
							  'User' => GetSetting('UTorrentUsername'),
							  'Pass' => GetSetting('UTorrentPassword'));
	
			if(!empty($UTorrent['Host'])) {
				if(!empty($UTorrent['Port'])) {
					if(!empty($UTorrent['User'])) {
						if(!empty($UTorrent['Pass'])) {
							$this->UTorrent = new UTorrentAPI($UTorrent['Host'], 
														      $UTorrent['User'],
														      $UTorrent['Pass'],
														      $UTorrent['Port']);
							
							if(!$this->UTorrent->Token) {
								throw new RestException(503, 'Unable to connect to uTorrent');
							}
						}
						else {
							throw new RestException(401, 'uTorrent API password is wrong/missing');
						}
					}
					else {
						throw new RestException(401, 'uTorrent API username is wrong/missing');
					}
				}
				else {
					throw new RestException(401, 'uTorrent API port is wrong/missing');
				}
			}
			else {
				throw new RestException(401, 'uTorrent API hostname is wrong/missing');
			}
		}
	}
	
	/**
	 * @url GET /
	**/
	function TorrentsAll() {
		$this->Connect();
		
		$Torrents = $this->UTorrent->getTorrents();
		if($Torrents) {
			$TorrentsArr = array();
			foreach($Torrents AS $Torrent) {
				if($Torrent[UTORRENT_TORRENT_STATUS] == 128) {
					$Torrent['Status'] = 'Stopped';
				}
				if($Torrent[UTORRENT_TORRENT_STATUS] & 2) {
					$Torrent['Status'] = 'Checking';
				}
				if($Torrent[UTORRENT_TORRENT_STATUS] & 16) {
					$Torrent['Status'] = 'Error';
				}
				if($Torrent[UTORRENT_TORRENT_STATUS] & 32) {
					$Torrent['Status'] = 'Paused (F)';
				}
				if($Torrent[UTORRENT_TORRENT_STATUS] == 233) {
					$Torrent['Status'] = 'Paused';
				}
				if($Torrent[UTORRENT_TORRENT_STATUS] == 136) {
					$Torrent['Status'] = ($Torrent[UTORRENT_TORRENT_PROGRESS] == 1000) ? 'Finished' : 'Stopped';
				}
				if($Torrent[UTORRENT_TORRENT_STATUS] == 137) {
					$Torrent['Status'] = ($Torrent[UTORRENT_TORRENT_PROGRESS] == 1000) ? 'Seeding (F)' : 'Downloading (F)';
				}
				if($Torrent[UTORRENT_TORRENT_STATUS] == 200) {
					$Torrent['Status'] = ($Torrent[UTORRENT_TORRENT_PROGRESS] == 1000) ? 'Queued Seed' : 'Queued';
				}
				if($Torrent[UTORRENT_TORRENT_STATUS] == 201) {
					$Torrent['Status'] = ($Torrent[UTORRENT_TORRENT_PROGRESS] == 1000) ? 'Seeding' : 'Downloading';
				}
			
				$Torrent['TimeRemaining'] = ($Torrent[UTORRENT_TORRENT_DOWNSPEED] > (20 * 1024) && $Torrent['Status'] == 'Downloading') ? ConvertSeconds(($Torrent[UTORRENT_TORRENT_SIZE] - $Torrent[UTORRENT_TORRENT_DOWNLOADED]) / $Torrent[UTORRENT_TORRENT_DOWNSPEED]) : '∞';
				
				$Torrent['StatusCode'] = $Torrent[UTORRENT_TORRENT_STATUS];
				$Torrent['Hash'] = $Torrent[UTORRENT_TORRENT_HASH];
				$Torrent['Uploaded'] = $Torrent[UTORRENT_TORRENT_UPLOADED];
				$Torrent['Ratio'] = $Torrent[UTORRENT_TORRENT_RATIO];
				$Torrent['UpSpeedInBytes'] = $Torrent[UTORRENT_TORRENT_UPSPEED];
				$Torrent['DownSpeedInBytes'] = $Torrent[UTORRENT_TORRENT_DOWNSPEED];
				$Torrent['UpSpeed'] = BytesToHuman($Torrent[UTORRENT_TORRENT_UPSPEED]).'/s';
				$Torrent['DownSpeed'] = BytesToHuman($Torrent[UTORRENT_TORRENT_DOWNSPEED]).'/s';
				$Torrent['ETA'] = $Torrent[UTORRENT_TORRENT_ETA];
				$Torrent['Label'] = $Torrent[UTORRENT_TORRENT_LABEL];
				$Torrent['PeersConnected'] = $Torrent[UTORRENT_TORRENT_PEERS_CONNECTED];
				$Torrent['PeersSwarm'] = $Torrent[UTORRENT_TORRENT_PEERS_SWARM];
				$Torrent['SeedsConnected'] = $Torrent[UTORRENT_TORRENT_SEEDS_CONNECTED];
				$Torrent['SeedsSwarm'] = $Torrent[UTORRENT_TORRENT_SEEDS_SWARM];
				$Torrent['Availability'] = $Torrent[UTORRENT_TORRENT_AVAILABILITY];
				$Torrent['QueuePosition'] = $Torrent[UTORRENT_TORRENT_QUEUE_POSITION];
				$Torrent['RemainingInBytes'] = $Torrent[UTORRENT_TORRENT_REMAINING];
				$Torrent['Remaining'] = BytesToHuman($Torrent[UTORRENT_TORRENT_REMAINING]);
				$Torrent['Name'] = $Torrent[UTORRENT_TORRENT_NAME];
				$Torrent['SizeInBytes'] = $Torrent[UTORRENT_TORRENT_SIZE];
				$Torrent['Size'] = BytesToHuman($Torrent[UTORRENT_TORRENT_SIZE]);
				$Torrent['DownloadedInBytes'] = $Torrent[UTORRENT_TORRENT_DOWNLOADED];
				$Torrent['Downloaded'] = BytesToHuman($Torrent[UTORRENT_TORRENT_DOWNLOADED]);
				$Torrent['Progress'] = ($Torrent[UTORRENT_TORRENT_PROGRESS] / 10).'%';
				
				unset($Torrent[UTORRENT_TORRENT_HASH]);
				unset($Torrent[UTORRENT_TORRENT_STATUS]);
				unset($Torrent[UTORRENT_TORRENT_UPLOADED]);
				unset($Torrent[UTORRENT_TORRENT_RATIO]);
				unset($Torrent[UTORRENT_TORRENT_UPSPEED]);
				unset($Torrent[UTORRENT_TORRENT_DOWNSPEED]);
				unset($Torrent[UTORRENT_TORRENT_ETA]);
				unset($Torrent[UTORRENT_TORRENT_LABEL]);
				unset($Torrent[UTORRENT_TORRENT_PEERS_CONNECTED]);
				unset($Torrent[UTORRENT_TORRENT_PEERS_SWARM]);
				unset($Torrent[UTORRENT_TORRENT_SEEDS_CONNECTED]);
				unset($Torrent[UTORRENT_TORRENT_SEEDS_SWARM]);
				unset($Torrent[UTORRENT_TORRENT_AVAILABILITY]);
				unset($Torrent[UTORRENT_TORRENT_QUEUE_POSITION]);
				unset($Torrent[UTORRENT_TORRENT_REMAINING]);
				unset($Torrent[UTORRENT_TORRENT_NAME]);
				unset($Torrent[UTORRENT_TORRENT_SIZE]);
				unset($Torrent[UTORRENT_TORRENT_DOWNLOADED]);
				unset($Torrent[UTORRENT_TORRENT_DOWNSPEED]);
				unset($Torrent[UTORRENT_TORRENT_PROGRESS]);
				
				$TorrentsArr[] = $Torrent;
			}
			
			return $TorrentsArr;
		}
		else {
			throw new RestException(404, 'Did not find any torrents matching your criteria');
		}
	}
	
	/**
	 * @url GET /start/:HashOrAll
	**/
	function StartTorrent($HashOrAll) {
		$this->Connect();
		
		$LogEntry = '';
		try {
			$Torrents = $this->TorrentsAll();
			
			foreach($Torrents AS $Torrent) {
				if(strtolower($HashOrAll) == 'all') {
					$this->UTorrent->torrentStart($Torrent['Hash']);
					
					$LogEntry = 'Started all torrents';
				}
				else {
					if($Torrent['Hash'] == $HashOrAll) {
						$this->UTorrent->torrentStart($HashOrAll);
						
						$LogEntry = 'Started torrent "'.$Torrent['Name'].'"';
					}
				}
			}
		}
		catch(RestException $e) {
		}
		
		if(strlen($LogEntry)) {
			AddLog(EVENT.'uTorrent', 'Success', $LogEntry);
		}
		
		throw new RestException(200, $LogEntry);
	}
	
	/**
	 * @url GET /pause/:HashOrAll
	**/
	function PauseTorrent($HashOrAll) {
		$this->Connect();
		
		$LogEntry = '';
		try {
			$Torrents = $this->TorrentsAll();
			foreach($Torrents AS $Torrent) {
				if(strtolower($HashOrAll) == 'all') {
					$this->UTorrent->torrentPause($Torrent['Hash']);
					
					$LogEntry = 'Paused all torrents';
				}
				else {
					if($Torrent['Hash'] == $HashOrAll) {
						$this->UTorrent->torrentPause($HashOrAll);
						
						$LogEntry = 'Paused torrent "'.$Torrent['Name'].'"';
					}
				}
			}
		}
		catch(RestException $e) {
		}
		
		if(strlen($LogEntry)) {
			AddLog(EVENT.'uTorrent', 'Success', $LogEntry);
		}
		
		throw new RestException(200, $LogEntry);
	}
	
	/**
	 * @url GET /stop/:HashOrAll
	**/
	function StopTorrent($HashOrAll) {
		$this->Connect();
		
		$LogEntry = '';
		try {
			$Torrents = $this->TorrentsAll();
			foreach($Torrents AS $Torrent) {
				if(strtolower($HashOrAll) == 'all') {
					$this->UTorrent->torrentStop($Torrent['Hash']);
					
					$LogEntry = 'Stopped all torrents';
				}
				else {
					if($Torrent['Hash'] == $HashOrAll) {
						$this->UTorrent->torrentStop($HashOrAll);
						
						$LogEntry = 'Stopped torrent "'.$Torrent['Name'].'"';
					}
				}
			}
		}
		catch(RestException $e) {
		}
		
		if(strlen($LogEntry)) {
			AddLog(EVENT.'uTorrent', 'Success', $LogEntry);
		}
		
		throw new RestException(200, $LogEntry);
	}
	
	/**
	 * @url GET /add/:Torrent
	**/
	function AddTorrent($Torrent) {
		$this->Connect();
		try {
			$this->UTorrent->torrentAdd($Torrent);
		}
		catch(RestException $e) {
			throw new RestException(400, 'AddTorrent: '.$e->getMessage());
		}
	}
	
	/**
	 * @url GET /remove/finished
	**/
	function RemoveFinishedTorrents() {
		$this->Connect();
		
		$LogEntry = '';
		try {
			$Torrents = $this->UTorrent->getTorrents();
			$RemovedTorrents = $RemovedTorrentsSize = 0;
			if(is_array($Torrents)) {
				foreach($Torrents AS $Torrent) {
					if($Torrent[UTORRENT_TORRENT_PROGRESS] == 1000 && $Torrent[UTORRENT_TORRENT_STATUS] == 136) {
						$RemovedTorrents++;
						$RemovedTorrentsSize += $Torrent[UTORRENT_TORRENT_SIZE];
				
						$this->RemoveTorrent(FALSE, $Torrent[UTORRENT_TORRENT_HASH]);
					}
				}
			}
		
			
		}
		catch(RestException $e) {
		}
		
		$LogEntry = 'Removed '.$RemovedTorrents.' torrents totaling '.BytesToHuman($RemovedTorrentsSize);
		if($RemovedTorrents) {
			AddLog(EVENT.'uTorrent', 'Success', $LogEntry);
			throw new RestException(200, $LogEntry);
		}
		else {
			throw new RestException(404);
		}
	}
	
	/**
	 * @url GET /remove/:Data/:HashOrAll
	 * @url GET /remove/:HashOrAll
	**/
	function RemoveTorrent($Data = FALSE, $HashOrAll) {
		$this->Connect();
		
		$LogEntry = '';
		$LogDataEntry = ($Data) ? ' along with all data' : '';
		
		try {
			$Torrents = $this->UTorrent->getTorrents();
			$RemovedTorrents = $RemovedTorrentsSize = 0;
			foreach($Torrents AS $Torrent) {
				if(strtolower($HashOrAll) == 'all') {
					$RemovedTorrents++;
					$RemovedTorrentsSize += $Torrent[UTORRENT_TORRENT_SIZE];
					
					if($Torrent[UTORRENT_TORRENT_PROGRESS] != 1000) {
						$this->UTorrent->torrentRemove($Torrent[UTORRENT_TORRENT_HASH], TRUE);
					}
					else {
						$this->UTorrent->torrentRemove($Torrent[UTORRENT_TORRENT_HASH], $Data);
					}
				}
				else {
					if($Torrent[UTORRENT_TORRENT_HASH] == $HashOrAll) {
						$this->UTorrent->torrentRemove($HashOrAll);
						
						$LogEntry = 'Removed torrent "'.$Torrent[UTORRENT_TORRENT_NAME].'"';
					}
				}
			}
		}
		catch(RestException $e) {
		}
		
		if(strlen($LogEntry)) {
			AddLog(EVENT.'uTorrent', 'Success', $LogEntry.$LogDataEntry);
		}
		
		throw new RestException(200, $LogEntry.$LogDataEntry);
	}
	
	/**
	 * @url GET /speed
	**/
	function GetUTorrentSpeedSettings() {
		if($this->GetSetting('max_ul_rate') == GetSetting('UTorrentDefaultUpSpeed') && $this->GetSetting('max_dl_rate') == GetSetting('UTorrentDefaultDownSpeed')) {
			return 'normal';
		}
		else if($this->GetSetting('max_ul_rate') == GetSetting('UTorrentDefinedUpSpeed') && $this->GetSetting('max_dl_rate') == GetSetting('UTorrentDefinedDownSpeed')) {
			return 'limited';
		}
		else {
			throw new RestException(404);
		}
	}
	
	/**
	 * @url GET /speedtoggle
	**/
	function ToggleUTorrentSpeedSettings() {
		if($this->GetSetting('max_ul_rate') == GetSetting('UTorrentDefaultUpSpeed') && $this->GetSetting('max_dl_rate') == GetSetting('UTorrentDefaultDownSpeed')) {
			$this->SetSetting('max_ul_rate', GetSetting('UTorrentDefinedUpSpeed'));
			$this->SetSetting('max_dl_rate', GetSetting('UTorrentDefinedDownSpeed'));
			
			$LogEntry = 'Set speed settings to "defined"';
		}
		else {
			$this->SetSetting('max_ul_rate', GetSetting('UTorrentDefaultUpSpeed'));
			$this->SetSetting('max_dl_rate', GetSetting('UTorrentDefaultDownSpeed'));
			
			$LogEntry = 'Set speed settings to "default"';
		}
		
		AddLog(EVENT.'uTorrent', 'Success', $LogEntry);
		throw new RestException(200, $LogEntry);
	}
	
	function DownloadTorrents($DownloadArr) {
		$Torrents = $this->UTorrent->getTorrents();
		
		foreach($DownloadArr AS $Serie => $TorrentURI) {
			$TorrentTitle      = substr($TorrentURI[0], (strrpos($TorrentURI[0], '/') + 1));
			$TorrentData       = @file_get_contents($TorrentURI[0]);
			$Parsed            = ParseRelease($TorrentTitle);
			
			if(sizeof($Torrents)) {
				foreach($Torrents AS $Torrent) {
					$TorrentInfo = ParseRelease($Torrent[UTORRENT_TORRENT_NAME]);
				
					if(is_array($Parsed) && $Parsed['Type'] == 'TV' && $TorrentInfo['Title'] == $Parsed['Title']) {
						if($TorrentInfo['Episodes'][0][0] == $Parsed['Episodes'][0][0] && $TorrentInfo['Episodes'][0][1] == $Parsed['Episodes'][0][1]) {
							$OldQuality = GetQualityRank($Torrent[UTORRENT_TORRENT_NAME]);
							$NewQuality = GetQualityRank($TorrentTitle);
							
							if($NewQuality > $OldQuality) {
								$this->RemoveTorrent(TRUE, $Torrent[UTORRENT_TORRENT_HASH]);
								
								AddLog(EVENT.'uTorrent', 'Success', 'Removed "'.$Torrent[UTORRENT_TORRENT_NAME].'" in favour of "'.$TorrentTitle.'"');
							}
							else {
								$TorrentURI = FALSE;
							}
						}
					}
				}
			}
		
			if($TorrentURI) {
				$this->UTorrent->torrentAdd($TorrentURI[0]);
				if($TorrentURI[1] != 99999999) {
					if(!empty($TorrentURI[2]) && !empty($TorrentURI[1])) {
						$EpisodePrep = $this->PDO->prepare('UPDATE
						                                    	Episodes
						                                    SET
						                                    	TorrentKey = :TorrentKey
						                                    WHERE
						                                    	ID = :ID');
						                                    	
						$EpisodePrep->execute(array(':TorrentKey' => $TorrentURI[2],
													':ID'         => $TorrentURI[1]));
						
						AddLog(EVENT.'Series', 'Success', 'Downloaded "'.$TorrentTitle.'"');
					}
				}
				else {
					$WishlistPrep = $this->PDO->prepare('UPDATE
					                                     	Wishlist
					                                     Set
					                                     	Date = :Date,
					                                     	TorrentKey = :TorrentKey
					                                     WHERE
					                                     	Title = :Title');
					                                     	
					$WishlistPrep->execute(array(':Date'       => time(),
												 ':TorrentKey' => $TorrentURI[2],
												 ':Title'      => $TorrentURI[3]));
					
					AddLog(EVENT.'Wishlist', 'Success', 'Downloaded "'.$TorrentTitle.'" from Wishlist');
				}
			}
		}
	}
	
	/**
	 * @url GET /:Hash
	**/
	function GetFiles($Hash) {
		$this->Connect();
		
		$Files = $this->UTorrent->getFiles($Hash);
		
		if($Files) {
			return $Files;
		}
		else {
			throw new RestException(404, 'Did not find any torrents with Hash "'.$Hash.'"');
		}
	}
	
	/**
	 * Internal functions
	**/
	function SetSetting($Setting, $Value) {
		return $this->UTorrent->setSetting($Setting, $Value);
	}
	
	function GetSetting($Setting) {
		$this->Connect();
		
		$Settings = $this->GetSettings();
		
		if(is_array($Settings)) {
			foreach($Settings AS $SettingArr) {
				if($SettingArr[0] == $Setting) {
					return $SettingArr[2];
				}
			}
		}
	}
	
	function GetSettings() {
		return $this->UTorrent->getSettings();
	}
}
?>