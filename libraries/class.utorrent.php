<?php
class UTorrent extends Hub {
	public $UTorrentAPI;
	
	function Connect() {
		require_once APP_PATH.'/libraries/api.utorrent.php';
		
		$Settings = Hub::GetSettings();
		if(array_key_exists('SettingUTorrentHostname', $Settings)) {
			if(array_key_exists('SettingUTorrentPort', $Settings)) {
				if(array_key_exists('SettingUTorrentUsername', $Settings)) {
					if(array_key_exists('SettingUTorrentPassword', $Settings)) {
						$this->UTorrentAPI = new UTorrentAPI($Settings['SettingUTorrentHostname'], 
						                                     $Settings['SettingUTorrentUsername'],
						                                     $Settings['SettingUTorrentPassword'],
						                                     $Settings['SettingUTorrentPort']);
						
						if(!$this->UTorrentAPI) {
							$this->Error[] = 'Unable to get uTorrent token';
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
			$TorrentTitle = substr($TorrentURI[0], (strrpos($TorrentURI[0], '/') + 1));
			
			$Parsed = RSS::ParseRelease($TorrentTitle);
			$Torrents = self::GetTorrents();
				
			foreach($Torrents AS $Torrent) {
				$TorrentInfo = RSS::ParseRelease($Torrent[UTORRENT_TORRENT_NAME]);
			
				if($TorrentInfo['Title'] == $Parsed['Title']) {
					if($TorrentInfo['Episodes'][0][0] == $Parsed['Episodes'][0][0] && $TorrentInfo['Episodes'][0][1] == $Parsed['Episodes'][0][1]) {
						$OldQuality = RSS::GetQualityRank($Torrent[UTORRENT_TORRENT_NAME]);
						$NewQuality = RSS::GetQualityRank($TorrentTitle);
						
						if($NewQuality > $OldQuality) {
							self::TorrentDeleteData($Torrent[UTORRENT_TORRENT_HASH]);
							
							Hub::AddLog(EVENT.'uTorrent', 'Success', 'Removed "'.$Torrent[UTORRENT_TORRENT_NAME].'" in favour of "'.$TorrentTitle.'"');
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
					}
				}
				else {
					$WishlistPrep = $this->PDO->prepare('UPDATE Wishlist Set WishlistDownloadDate = :Date, TorrentKey = :TorrentKey WHERE WishlistTitle = :WishlistTitle');
					$WishlistPrep->execute(array(':Date'          => time(),
					                             ':TorrentKey'    => $TorrentURI[2],
					                             ':WishlistTitle' => $TorrentURI[3]));
					
					Hub::AddLog(EVENT.'Wishlist', 'Success', 'Downloaded "'.$TorrentTitle.'" from Wishlist');
				}
			}
		}
	}
	
	function DeleteFinishedTorrents() {
		$Torrents = self::GetTorrents();
		
		$RemovedTorrents = $RemovedTorrentsSize = 0;
		foreach($Torrents AS $Torrent) {
			if($Torrent[UTORRENT_TORRENT_PROGRESS] == 1000) {
				$RemovedTorrents++;
				$RemovedTorrentsSize += $Torrent[UTORRENT_TORRENT_SIZE];
			
				self::TorrentDelete($Torrent[UTORRENT_TORRENT_HASH]);
			}
		}
	
		if($RemovedTorrents) {
			Hub::AddLog(EVENT.'uTorrent', 'Success', 'Removed '.$RemovedTorrents.' torrents totaling '.Hub::BytesToHuman($RemovedTorrentsSize));
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
		
		foreach($Torrents AS $Torrent) {
			if($Torrent[UTORRENT_TORRENT_PROGRESS] != 1000) {
				self::TorrentDeleteData($Torrent[UTORRENT_TORRENT_HASH]);
			}
			else {
				self::TorrentDelete($Torrent[UTORRENT_TORRENT_HASH]);
			}
		}
	}
	
	function TorrentDelete($Hash) {
		return $this->UTorrentAPI->torrentRemove($Hash);
	}
	
	function TorrentDeleteData($Hash) {
		return $this->UTorrentAPI->torrentRemove($Hash, TRUE);
	}
	
	function GetBadge() {
		if(!$this->UTorrentAPI) {
			echo '<span class="badge single blue">!</span>';
		}
		else {
			$Torrents = $this->GetTorrents();
			$TorrentSize = sizeof($Torrents);
		
			$TorrentFinishedSize = 0;
			foreach($Torrents AS $Torrent) {
				if($Torrent[UTORRENT_TORRENT_PROGRESS] == 1000) {
					$TorrentFinishedSize++;
					$TorrentSize--;
				}
			}
		
			if($TorrentFinishedSize > 0 && $TorrentSize == 0) {
				echo '<span class="badge single red">'.$TorrentFinishedSize.'</span>';
			}
			else if($TorrentFinishedSize > 0 && $TorrentSize > 0) {
				echo '<span class="badge dual rightbadge blue">'.$TorrentSize.'</span><span class="badge dual leftbadge red">'.$TorrentFinishedSize.'</span>';
			}
			else if($TorrentSize > 0) {
				echo '<span class="badge single blue">'.$TorrentSize.'</span>';
			}
		}
	}
}
?>