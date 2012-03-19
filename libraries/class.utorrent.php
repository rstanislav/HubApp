<?php
class UTorrent extends Hub {
	public $UTorrentAPI;
	
	function Connect() {
		require_once APP_PATH.'/libraries/api.utorrent.php';
		
		$UTorrent = array('Host' => Hub::GetSetting('UTorrentIP'),
						  'Port' => Hub::GetSetting('UTorrentPort'),
						  'User' => Hub::GetSetting('UTorrentUsername'),
						  'Pass' => Hub::GetSetting('UTorrentPassword'));

		if(!empty($UTorrent['Host'])) {
			if(!empty($UTorrent['Port'])) {
				if(!empty($UTorrent['User'])) {
					if(!empty($UTorrent['Pass'])) {
						$this->UTorrentAPI = new UTorrentAPI($UTorrent['Host'], 
															 $UTorrent['User'],
															 $UTorrent['Pass'],
															 $UTorrent['Port']);
						
						if(!$this->UTorrentAPI->Token) {
							$this->Error[] = 'Unable to connect to uTorrent';
						}
					}
					else {
						$this->Error[] = 'uTorrent API password is wrong/missing';
					}
				}
				else {
					$this->Error[] = 'uTorrent API username is wrong/missing';
				}
			}
			else {
				$this->Error[] = 'uTorrent API port is wrong/missing';
			}
		}
		else {
			$this->Error[] = 'uTorrent API hostname is wrong/missing';
		}
	}
	
	function DownloadTorrents($DownloadArr) {
		foreach($DownloadArr AS $Serie => $TorrentURI) {
			$TorrentTitle      = substr($TorrentURI[0], (strrpos($TorrentURI[0], '/') + 1));
			$TorrentData       = @file_get_contents($TorrentURI[0]);
			
			if(is_array($http_response_header)) {
				if(array_key_exists(0, $http_response_header) && $http_response_header[0] != 'HTTP/1.1 200 OK') {
					Hub::AddLog(EVENT.'uTorrent', 'Failure', 'Tried to download "'.$TorrentTitle.'" but server returned "'.$http_response_header[0].'"');
					
					return FALSE;
				}
			}
			
			if(RSS::BDecode($TorrentData)) {
				$Parsed = RSS::ParseRelease($TorrentTitle);
				$Torrents = self::GetTorrents();
					
				foreach($Torrents AS $Torrent) {
					$TorrentInfo = RSS::ParseRelease($Torrent[UTORRENT_TORRENT_NAME]);
				
					if(is_array($Parsed) && $Parsed['Type'] == 'TV' && $TorrentInfo['Title'] == $Parsed['Title']) {
						if($TorrentInfo['Episodes'][0][0] == $Parsed['Episodes'][0][0] && $TorrentInfo['Episodes'][0][1] == $Parsed['Episodes'][0][1]) {
							$OldQuality = RSS::GetQualityRank($Torrent[UTORRENT_TORRENT_NAME]);
							$NewQuality = RSS::GetQualityRank($TorrentTitle);
							
							if($NewQuality > $OldQuality) {
								self::TorrentDeleteData($Torrent[UTORRENT_TORRENT_HASH]);
								
								Hub::AddLog(EVENT.'uTorrent', 'Success', 'Removed "'.$Torrent[UTORRENT_TORRENT_NAME].'" in favour of "'.$TorrentTitle.'"');
								Hub::NotifyUsers('FileHigherQuality', 'uTorrent', 'Removed "'.$Torrent[UTORRENT_TORRENT_NAME].'" in favour of "'.$TorrentTitle.'"');
							}
							else {
								$TorrentURI = FALSE;
							}
						}
					}
				}
			
				if($TorrentURI) {
					self::TorrentAdd($TorrentURI[0]);
					
					if($TorrentURI[1] != 99999999) {
						if(!empty($TorrentURI[2]) && !empty($TorrentURI[1])) {
							$EpisodePrep = $this->PDO->prepare('UPDATE Episodes SET TorrentKey = :TorrentKey WHERE EpisodeID = :EpisodeID');
							$EpisodePrep->execute(array(':TorrentKey' => $TorrentURI[2],
														':EpisodeID'  => $TorrentURI[1]));
							
							Hub::AddLog(EVENT.'Series', 'Success', 'Downloaded "'.$TorrentTitle.'"');
							Hub::NotifyUsers('NewUTorrentEpisode', 'uTorrent/Series', 'Downloaded "'.$TorrentTitle.'"');
						}
					}
					else {
						$WishlistPrep = $this->PDO->prepare('UPDATE Wishlist Set WishlistDownloadDate = :Date, TorrentKey = :TorrentKey WHERE WishlistTitle = :WishlistTitle');
						$WishlistPrep->execute(array(':Date'          => time(),
													 ':TorrentKey'    => $TorrentURI[2],
													 ':WishlistTitle' => $TorrentURI[3]));
						
						Hub::AddLog(EVENT.'Wishlist', 'Success', 'Downloaded "'.$TorrentTitle.'" from Wishlist');
						Hub::NotifyUsers('NewUTorrentWish', 'uTorrent/Wishlist', 'Downloaded "'.$TorrentTitle.'" from Wishlist');
					}
				}
			}
			else {
				Hub::AddLog(EVENT.'uTorrent', 'Failure', 'Tried to download "'.$TorrentTitle.'" but it is not a valid torrent file');
				
				return FALSE;
			}
		}
	}
	
	function DeleteFinishedTorrents() {
		$Torrents = self::GetTorrents();
		
		$RemovedTorrents = $RemovedTorrentsSize = 0;
		if(is_array($Torrents)) {
			foreach($Torrents AS $Torrent) {
				if($Torrent[UTORRENT_TORRENT_PROGRESS] == 1000 && $Torrent[UTORRENT_TORRENT_STATUS] == 136) {
					$RemovedTorrents++;
					$RemovedTorrentsSize += $Torrent[UTORRENT_TORRENT_SIZE];
			
					self::TorrentDelete($Torrent[UTORRENT_TORRENT_HASH]);
				}
			}
		}
	
		if($RemovedTorrents) {
			Hub::AddLog(EVENT.'uTorrent', 'Success', 'Removed '.$RemovedTorrents.' torrents totaling '.Hub::BytesToHuman($RemovedTorrentsSize));
			Hub::NotifyUsers('FinishedTorrentsRemoved', 'uTorrent', 'Removed '.$RemovedTorrents.' torrents totaling '.Hub::BytesToHuman($RemovedTorrentsSize));
		}
	}
	
	function CheckTorrentForFile($FileCheck) {
		if(is_object($this->UTorrentAPI)) {
			$Torrents = UTorrent::GetTorrents();
			
			if(is_array($Torrents)) {
				foreach($Torrents AS $Torrent) {
					$SeedStatuses = array(137, 200, 201); // Seeding (F), Queued Seed, Seeding
					if(in_array($Torrent[UTORRENT_TORRENT_STATUS], $SeedStatuses)) {
						$Files = UTorrent::GetFiles($Torrent[0]);
					
						foreach($Files AS $File) {
							if(is_array($File)) {
								foreach($File AS $FileTmp) {
									if(($FileTmp[0] == $FileCheck) || (pathinfo($FileTmp[0], PATHINFO_BASENAME) == pathinfo($FileCheck, PATHINFO_BASENAME))) {
										return TRUE;
									}
								}
							}
						}
					}
				}
			}
		}
		
		return FALSE;
	}
	
	function GetSettings() {
		if(is_object($this->UTorrentAPI)) {
			return $this->UTorrentAPI->getSettings();
		}
		else {
			return FALSE;
		}
	}
	
	function GetSetting($Setting) {
		$Settings = $this->GetSettings();
		
		if(is_array($Settings)) {
			foreach($Settings AS $SettingArr) {
				if($SettingArr[0] == $Setting) {
					return $SettingArr[2];
				}
			}
		}
	}
	
	function SetSetting($Setting, $Value) {
		return $this->UTorrentAPI->setSetting($Setting, $Value);
	}
	
	function TorrentAdd($URI) {
		return $this->UTorrentAPI->torrentAdd($URI);
	}
	
	function GetTorrents() {
		return $this->UTorrentAPI->getTorrents();
	}
	
	function GetFiles($Hash) {
		return $this->UTorrentAPI->getFiles($Hash);
	}
	
	function TorrentStartAll() {
		$Torrents = self::GetTorrents();
		
		foreach($Torrents AS $Torrent) {
			self::TorrentStart($Torrent[UTORRENT_TORRENT_HASH]);
		}
	}
	
	function TorrentStart($Hash) {
		return $this->UTorrentAPI->torrentStart($Hash);
	}
	
	function TorrentStopAll() {
		$Torrents = self::GetTorrents();
		
		foreach($Torrents AS $Torrent) {
			self::TorrentStop($Torrent[UTORRENT_TORRENT_HASH]);
		}
	}
	
	function TorrentStop($Hash) {
		return $this->UTorrentAPI->torrentStop($Hash);
	}
	
	function TorrentPauseAll() {
		$Torrents = self::GetTorrents();
		
		foreach($Torrents AS $Torrent) {
			self::TorrentPause($Torrent[UTORRENT_TORRENT_HASH]);
		}
	}
	
	function TorrentPause($Hash) {
		return $this->UTorrentAPI->torrentPause($Hash);
	}
	
	function TorrentRemoveAll() {
		$Torrents = self::GetTorrents();
		
		$RemovedTorrents = $RemovedTorrentsSize = 0;
		foreach($Torrents AS $Torrent) {
			$RemovedTorrents++;
			$RemovedTorrentsSize += $Torrent[UTORRENT_TORRENT_SIZE];
			
			if($Torrent[UTORRENT_TORRENT_PROGRESS] != 1000) {
				self::TorrentDeleteData($Torrent[UTORRENT_TORRENT_HASH]);
			}
			else {
				self::TorrentDelete($Torrent[UTORRENT_TORRENT_HASH]);
			}
		}
	
		if($RemovedTorrents) {
			Hub::AddLog(EVENT.'uTorrent', 'Success', 'Removed '.$RemovedTorrents.' torrents totaling '.Hub::BytesToHuman($RemovedTorrentsSize));
		}
	}
	
	function TorrentDelete($Hash) {
		return $this->UTorrentAPI->torrentRemove($Hash);
	}
	
	function TorrentDeleteData($Hash) {
		return $this->UTorrentAPI->torrentRemove($Hash, TRUE);
	}
	
	function GetBadge() {
		if(!$this->UTorrentAPI->Token) {
			echo '<span class="badge single red-badge"><img style="margin-top:2px" src="images/icons/offline.png" /></span>';
		}
		else {
			$Torrents = self::GetTorrents();
			
			if(is_array($Torrents) && sizeof($Torrents)) {
				$TorrentSize = sizeof($Torrents);
		
				$TorrentFinishedSize = 0;
				foreach($Torrents AS $Torrent) {
					if($Torrent[UTORRENT_TORRENT_PROGRESS] == 1000) {
						$TorrentFinishedSize++;
						$TorrentSize--;
					}
				}
		
				if($TorrentFinishedSize > 0 && $TorrentSize == 0) {
					echo '<span class="badge single red-badge">'.$TorrentFinishedSize.'</span>';
				}
				else if($TorrentFinishedSize > 0 && $TorrentSize > 0) {
					echo '<span class="badge dual rightbadge blue-badge">'.$TorrentSize.'</span><span class="badge dual leftbadge red-badge">'.$TorrentFinishedSize.'</span>';
				}
				else if($TorrentSize > 0) {
					echo '<span class="badge single blue-badge">'.$TorrentSize.'</span>';
				}
			}
		}
	}
}
?>