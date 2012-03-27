<?php
class RSS extends Hub {
	function CheckTLRSS() {
	}
	
	function StripCData($Str) { 
		preg_match_all('/<!\[cdata\[(.*?)\]\]>/is', $Str, $Matches);
		
		return str_replace($Matches[0], $Matches[1], $Str); 
	}
	
	function GetQualityRank($Str) {
		$Str         = str_replace('.', ' ', $Str);
		$Str         = str_replace('_', ' ', $Str);
		$Str         = str_replace('-', ' ', $Str);
		$QualityRank = 0;
		$Words       = array_unique(explode(' ', $Str));
		foreach($Words AS $Word) {
			switch(strtolower($Word)) {
				case '1080p':    $QualityRank += 60000; break;
				case '1080i':    $QualityRank += 50000; break;
				case '810p':     $QualityRank += 40000; break;
				case '720p':     $QualityRank += 30000; break;
				case '540p':     $QualityRank += 20000; break;
				case '480p':     $QualityRank += 10000; break;
				
				case 'bluray':
				case 'brrip':
				case 'bdrip':    $QualityRank += 6000;  break;
				case 'dvdrip':
				case 'hdtv':
				case 'pdtv':
				case 'hdtvrip':  $QualityRank += 5000;  break;
				case 'dvdscr':   $QualityRank += 3000;  break;
				case 'ts':
				case 'telesync':
				case 'cam':      $QualityRank -= 100000; break;
				
				case 'proper':
				case 'repack':   $QualityRank += 100;   break;
				
				case 'truehd7':
				case 'truehd5':
				case 'truehd':   $QualityRank += 30;    break;
				case 'dts':      $QualityRank += 20;    break;
				case 'ac3':      $QualityRank += 10;    break;
				
				case 'x264':     $QualityRank += 20;    break;
				case 'xvid':     $QualityRank += 10;    break;
			}
		}
		
		return $QualityRank;
	}
	
	function DownloadWantedTorrents() {
		$TorrentsPrep = $this->PDO->prepare('SELECT Torrents.*, RSS.RSSTitle FROM Torrents, RSS WHERE TorrentDate > :Date AND RSSID = RSSKey ORDER BY TorrentDate');
		$TorrentsPrep->execute(array(':Date' => strtotime('-10 days')));
				
		$DownloadArr = array();
		if($TorrentsPrep->rowCount()) {
			$Torrents = $TorrentsPrep->fetchAll();
			foreach($Torrents AS $Torrent) {
				$TorrentTitle = substr($Torrent['TorrentURI'], (strrpos($Torrent['TorrentURI'], '/') + 1));
				$Parsed = RSS::ParseRelease($Torrent['TorrentTitle']);
				
				if(is_array($Parsed) && $Parsed['Type'] == 'TV') {
					if(!preg_match("/\bgerman\b|\bhebsub\b|\bhebrew\b|\bsample\b|\bsubs\b/i", $Torrent['TorrentTitle'])) {
						$SerieTitle = $Parsed['Title'];
						
						$EpisodePrep = $this->PDO->prepare('SELECT Series.*, Episodes.* FROM Series, Episodes WHERE Episodes.SeriesKey = Series.SerieID AND (Series.SerieTitle = :Title OR Series.SerieTitleAlt = :Title) AND Episodes.EpisodeSeason = :Season AND Episodes.EpisodeEpisode = :Episode GROUP BY Series.SerieTitle');
						$EpisodePrep->execute(array(':Title'   => $Parsed['Title'],
													':Season'  => $Parsed['Episodes'][0][0],
													':Episode' => $Parsed['Episodes'][0][1]));
						
						$Episodes = $EpisodePrep->fetchAll();
						foreach($Episodes AS $Episode) {
							$SerieTitle = $Parsed['Title'];
							$DownloadArrID = $SerieTitle.'-'.$Parsed['Episodes'][0][0].$Parsed['Episodes'][0][1];
							
							$NewQuality = RSS::GetQualityRank($Torrent['TorrentTitle']);
							if($NewQuality >= Hub::GetSetting('MinimumDownloadQuality') && $NewQuality <= Hub::GetSetting('MaximumDownloadQuality')) {
								if(!isset($DownloadArr[$DownloadArrID])) {
									$DownloadArr[$DownloadArrID] = array();
								}
										
								if(isset($DownloadArr[$DownloadArrID][0])) {
									$OldQuality = RSS::GetQualityRank($DownloadArr[$DownloadArrID][0]);
								
									if($NewQuality > $OldQuality && $NewQuality >= Hub::GetSetting('MinimumDownloadQuality')) {
										$DownloadArr[$DownloadArrID][0] = $Torrent['TorrentURI'];
										$DownloadArr[$DownloadArrID][1] = $Episode['EpisodeID'];
										$DownloadArr[$DownloadArrID][2] = $Torrent['TorrentID'];
									}
								}
								else {
									$DownloadArr[$DownloadArrID][0] = $Torrent['TorrentURI'];
									$DownloadArr[$DownloadArrID][1] = $Episode['EpisodeID'];
									$DownloadArr[$DownloadArrID][2] = $Torrent['TorrentID'];
								}
								
								if($Episode['EpisodeFile']) {
									$OldQuality = RSS::GetQualityRank($Episode['EpisodeFile']);
								
									if($NewQuality > $OldQuality && $NewQuality >= Hub::GetSetting('MinimumDownloadQuality')) {
										if(is_file($Episode['EpisodeFile'])) {
											if(unlink($Episode['EpisodeFile'])) {
												Hub::AddLog(EVENT.'Series', 'Success', 'Deleted "'.$Episode['EpisodeFile'].'" in favour of "'.$TorrentTitle.'"', 0, 'clean');
												Hub::NotifyUsers('FileHigherQuality', 'Series', 'Deleted "'.$Episode['EpisodeFile'].'" in favour of "'.$TorrentTitle.'"');
											
												$UpdateEpisodePrep = $this->PDO->prepare('UPDATE Episodes SET EpisodeFile = "", TorrentKey = "" WHERE EpisodeFile = :File');
												$UpdateEpisodePrep->execute(array(':File' => $Episode['EpisodeFile']));
												
												$DownloadArr[$DownloadArrID][0] = $Torrent['TorrentURI'];
												$DownloadArr[$DownloadArrID][1] = $Episode['EpisodeID'];
												$DownloadArr[$DownloadArrID][2] = $Torrent['TorrentID'];
											}
											else {
												Hub::AddLog(EVENT.'Series', 'Failure', 'Tried to delete "'.$Episode['EpisodeFile'].'" in favour of "'.$TorrentTitle.'"');
											}
										}
									}
									else {
										unset($DownloadArr[$DownloadArrID]);
									}
								}
								else if($Episode['TorrentKey']) {
									$Torrents = $this->PDO->query('SELECT TorrentTitle AS Title FROM Torrents WHERE TorrentID = '.$Episode['TorrentKey'])->fetch();
								
									$OldQuality = RSS::GetQualityRank($Torrents['Title']);
									if($NewQuality > $OldQuality && $NewQuality >= Hub::GetSetting('MinimumDownloadQuality')) {
										$DownloadArr[$DownloadArrID][0] = $Torrent['TorrentURI'];
										$DownloadArr[$DownloadArrID][1] = $Episode['EpisodeID'];
										$DownloadArr[$DownloadArrID][2] = $Torrent['TorrentID'];
									}
									else {
										unset($DownloadArr[$DownloadArrID]);
									}
								}
							}
						}
					}
				}
				else if(is_array($Parsed) && $Parsed['Type'] == 'Movie') {
					if(!stristr($Torrent['TorrentTitle'], 'hebrew') && !stristr($Torrent['TorrentTitle'], 'hebsub')) {
						$WishlistPrep = $this->PDO->prepare('SELECT * FROM Wishlist WHERE WishlistTitle = :Title AND WishlistYear = :Year');
						$WishlistPrep->execute(array(':Title' => $Parsed['Title'],
												 	 ':Year'  => $Parsed['Year']));
												 
						if($WishlistPrep->rowCount()) {
							$Wishlists = $WishlistPrep->fetchAll();
						
							foreach($Wishlists AS $Wishlist) {
								$NewQuality = RSS::GetQualityRank($Torrent['TorrentTitle']);
								if($NewQuality >= Hub::GetSetting('MinimumDownloadQuality') && $NewQuality <= Hub::GetSetting('MaximumDownloadQuality')) {
									if(!isset($DownloadArr[$Parsed['Title']])) {
										$DownloadArr[$Parsed['Title']] = array();
									}
								
									if(isset($DownloadArr[$Parsed['Title']][0])) {
										$OldQuality = RSS::GetQualityRank($DownloadArr[$Parsed['Title']][0]);
							
										if($NewQuality > $OldQuality && $NewQuality >= Hub::GetSetting('MinimumDownloadQuality')) {
											$DownloadArr[$Parsed['Title']][0] = $Torrent['TorrentURI'];
											$DownloadArr[$Parsed['Title']][1] = 99999999;
											$DownloadArr[$Parsed['Title']][2] = $Torrent['TorrentID'];
											$DownloadArr[$Parsed['Title']][3] = $Parsed['Title'];
										}
									}
									else {
										$DownloadArr[$Parsed['Title']][0] = $Torrent['TorrentURI'];
										$DownloadArr[$Parsed['Title']][1] = 99999999;
										$DownloadArr[$Parsed['Title']][2] = $Torrent['TorrentID'];
										$DownloadArr[$Parsed['Title']][3] = $Parsed['Title'];
									}
						
									if($Wishlist['WishlistFile']) {
										$OldQuality = RSS::GetQualityRank($Wishlist['WishlistFile']);
									
										if($NewQuality > $OldQuality && $NewQuality >= Hub::GetSetting('MinimumDownloadQuality')) {
											if(is_file($Wishlist['WishlistFile'])) {
												if(unlink($Wishlist['WishlistFile'])) {
													Hub::AddLog(EVENT.'Wishlist', 'Success', 'Deleted "'.$Wishlist['WishlistFile'].'"  in favour of "'.$TorrentTitle.'"', 0, 'clean');
													Hub::NotifyUsers('FileHigherQuality', 'Wishlist', 'Deleted "'.$Wishlist['WishlistFile'].'"  in favour of "'.$TorrentTitle.'"');
											
													$WishlistUpdatePrep = $this->PDO->prepare('UPDATE Wishlist SET WishlistFile = "" WHERE WishlistFile = :File');
													$WishlistUpdatePrep->execute(array(':File' => $Wishlist['WishlistFile']));
												
													$DownloadArr[$Parsed['Title']][0] = $Torrent['TorrentURI'];
													$DownloadArr[$Parsed['Title']][1] = 99999999;
													$DownloadArr[$Parsed['Title']][2] = $Torrent['TorrentID'];
													$DownloadArr[$Parsed['Title']][3] = $Parsed['Title'];
												}
												else {
													Hub::AddLog(EVENT.'Wishlist', 'Failure', 'Tried to delete "'.$Wishlist['WishlistFile'].'" in favour of "'.$TorrentTitle.'"');
												}
											}
										}
										else {
											unset($DownloadArr[$Parsed['Title']]);
										}
									}
									else if($Wishlist['TorrentKey']) {
										$Torrents = $this->PDO->query('SELECT TorrentTitle AS Title FROM Torrents WHERE TorrentID = '.$Wishlist['TorrentKey'])->fetch();
								
										$OldQuality = RSS::GetQualityRank($Torrents['Title']);
										if($NewQuality > $OldQuality && $NewQuality >= Hub::GetSetting('MinimumDownloadQuality')) {
											$DownloadArr[$Parsed['Title']][0] = $Torrent['TorrentURI'];
											$DownloadArr[$Parsed['Title']][1] = 99999999;
											$DownloadArr[$Parsed['Title']][2] = $Torrent['TorrentID'];
											$DownloadArr[$Parsed['Title']][3] = $Parsed['Title'];
										}
										else {
											unset($DownloadArr[$Parsed['Title']]);
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
			UTorrent::Connect();
			UTorrent::DownloadTorrents($DownloadArr);
		}
		else {
			return FALSE;
		}
	}
	
	function Update() {
		$RSSFeeds = $this->GetRSSFeeds();
		
		if(is_array($RSSFeeds)) {
			$NewItems = 0;
			foreach($RSSFeeds AS $RSSFeed) {
				$Update = $this->PDO->query('SELECT TorrentPubDate AS Last FROM Torrents WHERE RSSKey = "'.$RSSFeed['RSSID'].'" ORDER BY TorrentPubDate DESC LIMIT 1')->fetch();
				
				$RSSFile = @file_get_contents($RSSFeed['RSSFeed']);
							
				if($RSSFile) {
					$XML = new SimpleXMLElement($RSSFile);
					
					foreach($XML->channel->item as $Item) {
						$Item->pubDate = strtotime($Item->pubDate);
							
						if($Item->pubDate > $Update['Last']) {
							$RSSPrep = $this->PDO->prepare('INSERT INTO Torrents (TorrentID, TorrentDate, TorrentPubDate, TorrentURI, TorrentTitle, TorrentCategory, RSSKey) VALUES (:TorrentID, :TorrentDate, :TorrentPubDate, :TorrentURI, :TorrentTitle, :TorrentCategory, :RSSID)');
							$RSSPrep->execute(array(':TorrentID'       => NULL,
													':TorrentDate'     => time(),
													':TorrentPubDate'  => $Item->pubDate,
													':TorrentURI'      => $this->StripCData($Item->link),
													':TorrentTitle'    => $this->StripCData($Item->title),
													':TorrentCategory' => $Item->category,
													':RSSID'           => $RSSFeed['RSSID']));
							$NewItems++;
						}
					}
				}
			}
			
			if($NewItems) {
				Hub::AddLog(EVENT.'RSS', 'Success', 'Added '.$NewItems.' torrents spread across '.sizeof($RSSFeeds).' RSS feeds');
				Hub::NotifyUsers('NewRSSTorrents', 'RSS', 'Added '.$NewItems.' torrents spread across '.sizeof($RSSFeeds).' RSS feeds');
			}
		}
	}
	
	function GetRSSFeeds() {
		$RSSPrep = $this->PDO->prepare('SELECT * FROM RSS');
		$RSSPrep->execute();
		
		if($RSSPrep->rowCount()) {
			return $RSSPrep->fetchAll();
		}
		else {
			return FALSE;
		}
	}
	
	function GetRSSFeed($RSSTitle) {
		$RSSPrep = $this->PDO->prepare('SELECT * FROM RSS WHERE RSSTitle = :Title');
		$RSSPrep->execute(array(':Title' => $RSSTitle));
		
		if($RSSPrep->rowCount()) {
			return $RSSPrep->fetch();
		}
		else {
			return FALSE;
		}
	}
	
	function GetCategories($RSSKey) {
		$CatPrep = $this->PDO->prepare('SELECT DISTINCT TorrentCategory FROM Torrents WHERE RSSKey = :RSSKey ORDER BY TorrentCategory');
		$CatPrep->execute(array(':RSSKey' => $RSSKey));
		
		if($CatPrep->rowCount()) {
			return $CatPrep->fetchAll();
		}
		else {
			return FALSE;
		}
	}
	
	function GetTorrentByID($TorrentID) {
		$TorrentPrep = $this->PDO->prepare('SELECT * FROM Torrents WHERE TorrentID = :ID');
		$TorrentPrep->execute(array(':ID' => $TorrentID));
		
		if($TorrentPrep->rowCount()) {
			return $TorrentPrep->fetch();
		}
		else {
			return FALSE;
		}
	}
	
	function TorrentIsDownloaded($TorrentID) {
		$TorrentPrep = $this->PDO->prepare('SELECT TorrentKey FROM Episodes WHERE Episodes.TorrentKey = :TorrentID');
		$TorrentPrep->execute(array(':TorrentID' => $TorrentID));
		
		if($TorrentPrep->rowCount()) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}

	function RSSFeedAdd() { // $_POST
		$AddError = FALSE;
		foreach($_POST AS $PostKey => $PostValue) {
			if(!filter_has_var(INPUT_POST, 'RSSTitle') || !filter_has_var(INPUT_POST, 'RSSFeed')) {
				$AddError = TRUE;
			}
		}
		
		if(!$AddError) {
			$RSSFeedAddPrep = $this->PDO->prepare('INSERT INTO RSS (RSSID, RSSDate, RSSTitle, RSSFeed) VALUES (NULL, :Date, :Title, :Feed)');
			$RSSFeedAddPrep->execute(array(':Date'  => time(),
										   ':Title' => $_POST['RSSTitle'],
										   ':Feed'  => $_POST['RSSFeed']));
		}
		else {
			echo 'You have to fill in all the fields';
		}
	}
	
	function RSSFeedEdit() { // $_POST
		if(filter_has_var(INPUT_POST, 'id') && filter_has_var(INPUT_POST, 'value')) {
			if(!empty($_POST['id']) || !empty($_POST['value'])) {
				list($EditID, $EditField) = explode('-|-', $_POST['id']);
			
				$RSSFeedFromDB = self::GetRSSFeedByID($EditID);
			
				if($RSSFeedFromDB) {
					$RSSFeedEdit = array_replace($RSSFeedFromDB, array($EditField => $_POST['value']));
					
					$RSSFeedEditPrep = $this->PDO->prepare('UPDATE RSS SET '.$EditField.' = :EditValue WHERE RSSID = :EditID');
					$RSSFeedEditPrep->execute(array(':EditValue' => $_POST['value'],
													':EditID'    => $EditID));
						
					echo $_POST['value'];
				}
			}
		}
	}
	
	function RSSFeedDelete() {
		if(filter_has_var(INPUT_GET, 'RSSID')) {
			$RSSFeed = $this->PDO->query('SELECT RSSTitle FROM RSS WHERE RSSID = "'.$_GET['RSSID'].'"')->fetch();
			
			$RSSFeedDeletePrep = $this->PDO->prepare('DELETE FROM RSS WHERE RSSID = :ID');
			$RSSFeedDeletePrep->execute(array(':ID' => $_GET['RSSID']));
			
			$RSSTorrentsDeletePrep = $this->PDO->prepare('DELETE FROM Torrents WHERE RSSKey = :ID');
			$RSSTorrentsDeletePrep->execute(array(':ID' => $_GET['RSSID']));
			
			Hub::AddLog(EVENT.'RSS', 'Success', 'Deleted feed "'.$RSSFeed['RSSTitle'].'"');
		}
	}
	
	function GetRSSFeedByID($RSSID) {
		$RSSPrep = $this->PDO->prepare('SELECT * FROM RSS WHERE RSSID = :ID');
		$RSSPrep->execute(array(':ID' => $RSSID));
		
		if($RSSPrep->rowCount()) {
			return $RSSPrep->fetch();
		}
		else {
			return FALSE;
		}
	}
	
	function GetTorrents($Category = '', $RSSKey) {
		if($Category == 'undefined' || empty($Category)) {
			$TorrentPrep = $this->PDO->prepare('SELECT * FROM Torrents WHERE RSSKey = :RSSKey ORDER BY TorrentPubDate DESC LIMIT 100');
			$TorrentPrep->execute(array(':RSSKey' => $RSSKey));
		}
		else if(is_array($Category)) {
			$Categories = '"'.join('","', $Category).'"';
			$TorrentPrep = $this->PDO->query('SELECT * FROM Torrents WHERE RSSKey = "'.$RSSKey.'" AND TorrentCategory IN ('.$Categories.') ORDER BY TorrentPubDate DESC LIMIT 100')->fetchAll();
			
			if(sizeof($TorrentPrep)) {
				return $TorrentPrep;
			}
			else {
				return FALSE;
			}
		}
		else {
			$TorrentPrep = $this->PDO->prepare('SELECT * FROM Torrents WHERE RSSKey = :RSSKey AND TorrentCategory = :Category ORDER BY TorrentPubDate DESC LIMIT 100');
			$TorrentPrep->execute(array(':Category' => urldecode($Category),
										':RSSKey'   => $RSSKey));
		}
		
		if($TorrentPrep->rowCount()) {
			return $TorrentPrep->fetchAll();
		}
		else {
			return FALSE;
		}
	}
	
	function SearchTitle($Search) {
		$SearchPrep = $this->PDO->prepare('SELECT Torrents.*, RSS.RSSTitle FROM Torrents, RSS WHERE TorrentTitle LIKE :Search AND TorrentTitle NOT LIKE :ExcludeSearch AND RSS.RSSID = Torrents.RSSKey AND Torrents.IsBroken != 1 ORDER BY TorrentDate DESC');
		$SearchPrep->execute(array(':Search'        => urldecode($Search).'%',
								   ':ExcludeSearch' => '%hebsub%'));
		
		if($SearchPrep->rowCount()) {
			return $SearchPrep->fetchAll();
		}
		else {
			return FALSE;
		}
	}
	
	function CreateSearchLink($Query, $Type) {
		$DeadLink = '<img src="images/icons/search_dark.png" title="Add a search URI in the settings to enable searching" />';
		switch($Type) {
			case 'movie':
				if(Hub::GetSetting('SearchURIMovies')) {
					$Link = Hub::GetSetting('SearchURIMovies');
				}
			break;
			
			case 'tv':
				if(Hub::GetSetting('SearchURITVSeries')) {
					$Link = Hub::GetSetting('SearchURITVSeries');
				}
			break;
		}
		
		if(isset($Link)) {
			return '<a href="'.str_replace('{QUERY}', urlencode($Query), $Link).'" target="_blank"><img src="images/icons/search.png" title="Search for \''.$Query.'\'" /></a>';
		}
		else {
			return $DeadLink;
		}
	}
	
	function SetTorrentAsBroken($ID) {
		$TorrentPrep = $this->PDO->prepare('UPDATE Torrents SET IsBroken = 1 WHERE TorrentID = :ID');
		$TorrentPrep->execute(array(':ID' => $ID));
	}
	
	function TorrentDownload($ID) {
		$Torrent = $this->PDO->query('SELECT TorrentID, TorrentURI, TorrentTitle FROM Torrents WHERE TorrentID = '.$ID.' AND IsBroken != 1')->fetch();
		
		if($Torrent['TorrentURI']) {
			UTorrent::Connect();
			$TorrentData = @file_get_contents($Torrent['TorrentURI']);
			$File        = urlencode(substr($Torrent['TorrentURI'], (strrpos($Torrent['TorrentURI'], '/') + 1)));
			
			if(is_array($http_response_header)) {
				if(array_key_exists(0, $http_response_header) && $http_response_header[0] != 'HTTP/1.1 200 OK') {
					Hub::AddLog(EVENT.'uTorrent', 'Failure', 'Tried to download "'.$File.'" but server returned "'.$http_response_header[0].'"');
					
					self::SetTorrentAsBroken($Torrent['TorrentID']);
					
					return FALSE;
				}
			}
			
			if(is_object($this->UTorrentAPI)) {
				if(RSS::BDecode($TorrentData)) {
					UTorrent::TorrentAdd(urldecode($Torrent['TorrentURI']));
				
					Hub::AddLog(EVENT.'uTorrent', 'Success', 'Downloaded "'.urldecode($Torrent['TorrentTitle']).'"');
					
					return TRUE;
				}
				else {
					Hub::AddLog(EVENT.'uTorrent', 'Failure', 'Tried to add "'.urldecode($Torrent['TorrentTitle']).'", but it is not a valid torrent file');
					
					self::SetTorrentAsBroken($Torrent['TorrentID']);
					
					return FALSE;
				}
			}
			else {
				if(RSS::BDecode($TorrentData)) {
					if(!is_file(Hub::GetSetting('UTorrentWatchFolder').'/'.$File)) {
						if(file_put_contents(Hub::GetSetting('UTorrentWatchFolder').'/'.$File, $TorrentData)) {
							Hub::AddLog(EVENT.'Watch Folder', 'Success', 'Downloaded "'.urldecode($File).'"');
							Hub::NotifyUsers('TorrentDownloadManual', 'Watch Folder', 'Downloaded "'.urldecode($File).'"');
						
							return TRUE;
						}
						else {
							Hub::AddLog(EVENT.'Watch Folder', 'Failure', 'Failed to download "'.urldecode($File).'"');
						
							return FALSE;
						}
					}
				}
				else {
					Hub::AddLog(EVENT.'uTorrent', 'Failure', 'Tried to add "'.urldecode($Torrent['TorrentTitle']).'", but it is not a valid torrent file');
					
					self::SetTorrentAsBroken($Torrent['TorrentID']);
					
					return FALSE;
				}
			}
		}
		else {
			return FALSE;
		}
	}
	
	function ParseRelease($Release) {
		$Search  = array(' ', '_', '(', ')', '.-.', '[', ']', '{', '}');
		$Replace = array('.', '.', '',  '',  '.',   '',  '',  '',  '');
		$Release = str_replace($Search, $Replace, $Release);
		
		$SerieRegEx    = '/(.*?)\.?((?:(?:s[0-9]{1,2})?[.-]?e[0-9]{1,2}|[0-9]{1,2}x[0-9]{1,2})(?:[.-]?(?:s?[0-9]{1,2})?[xe]?[0-9]{1,2})*)\.(.*)/i';
		$MovieRegEx    = '/([A-z0-9 \&._\-:\\pL]+)([0-9]{4})(.*)/';
		$TalkShowRegEx = '/([A-z0-9 \&._\-:]+)([0-9]{4}).([0-9]{2}).([0-9]{2})([. ])/';
		
		if(preg_match($SerieRegEx, $Release, $Match)) {
			$ReleaseTitle      = str_replace('.', ' ', str_replace('_', ' ', trim($Match[1])));
	  		$ReleaseEpisodeStr = trim($Match[2]);
	  		$ReleaseQuality    = str_replace('.', ' ', str_replace('_', ' ', $Match[3]));
	  		$ReleaseEpisodes   = array();
	  
	  		preg_match_all('/\G[.-]?(?:s?([0-9]{1,2}+))?[.-]?[xe]?([0-9]{1,3})/i', $ReleaseEpisodeStr, $Matches, PREG_SET_ORDER);
	  	
	  		$ReleaseSeason = 'NA';
	  		foreach($Matches as $Match) {
				if(isset($Match[1]) && strlen($Match[1]) > 0) {
		  			$ReleaseSeason = (int) $Match[1];
				}
			
				if($ReleaseSeason != 72) {
					$Episodes[] = (int) $Match[2];
				}
	  		}
	  		
	  		if(sizeof($Episodes) > 1) {
	  			for($i = min($Episodes); $i <= max($Episodes); $i++) {
	  				$ReleaseEpisodes[] = array($ReleaseSeason, $i);
	  			}
	  		}
	  		else {
	  			$ReleaseEpisodes[] = array($ReleaseSeason, $Episodes[0]);
	  		}
	  			  		
	  		if(!empty($ReleaseTitle)) {
	  			return array('Type'     => 'TV',
	  					 	 'Title'    => $ReleaseTitle,
						 	 'Episodes' => $ReleaseEpisodes,
						 	 'Quality'  => $ReleaseQuality);
			}
			else {
				return FALSE;
			}
		}
		else if(preg_match($TalkShowRegEx, $Release, $Match)) {
			return array('Type'  => 'Talk Show',
						 'Title' => trim(str_replace($Replace, $Search, $Match[1])),
						 'Year'  => $Match[2]);
		}
		else if(preg_match($MovieRegEx, $Release, $Match)) {
			return array('Type'  => 'Movie',
						 'Title' => trim(str_replace($Replace, $Search, $Match[1])),
						 'Year'  => trim($Match[2]));
		}
		else {
			return FALSE;
		}
	}
	
	function BDecode($Str) {
		$Pos = 0;
		
		return RSS::BDecodeRecursive($Str, $Pos);
	}
	
	function BDecodeRecursive($Str, &$Pos) {
		$StrLength = strlen($Str);
		if(($Pos < 0) || ($Pos >= $StrLength)) {
			return NULL;
		}
		else if($Str{$Pos} == 'i') {
			$Pos++;
			$NumLength = strspn($Str, '-0123456789', $Pos);
			$StrPos    = $Pos;
			$Pos      += $NumLength;
			
			if(($Pos >= $StrLength) || ($Str{$Pos} != 'e')) {
				return NULL;
			}
			else {
				$Pos++;
				
				return intval(substr($Str, $StrPos, $NumLength));
			}
		}
		else if($Str{$Pos} == 'd') {
			$Pos++;
			$ReturnValue = array();
			while($Pos < $StrLength) {
				if($Str{$Pos} == 'e') {
					$Pos++;
					
					return $ReturnValue;
				}
				else {
					$Key = RSS::BDecodeRecursive($Str, $Pos);
					if($Key == NULL) {
						return NULL;
					}
					else {
						$Val = RSS::BDecodeRecursive($Str, $Pos);
						if($Val == NULL) {
							return NULL;
						}
						else if(!is_array($Key)) {
							$ReturnValue[$Key] = $Val;
						}
					}
				}
			}
		
			return NULL;
		}
		else if($Str{$Pos} == 'l') {
			$Pos++;
			$ReturnValue = array();
			while($Pos < $StrLength) {
				if($Str{$Pos} == 'e') {
					$Pos++;
					
					return $ReturnValue;
				}
				else {
					$Val = RSS::BDecodeRecursive($Str, $Pos);
					if($Val == NULL) {
						return NULL;
					}
					else {
						$ReturnValue[] = $Val;
					}
				}
			}
			
			return NULL;
		}
		else {
			$NumLength = strspn($Str, '0123456789', $Pos);
			$StrPos    = $Pos;
			$Pos      += $NumLength;
			if(($Pos >= $StrLength) || ($Str{$Pos} != ':')) {
				return NULL;
			}
			else {
				$ValLength = intval(substr($Str, $StrPos, $NumLength));
				$Pos++;
				$Val = substr($Str, $Pos, $ValLength);
				if(strlen($Val) != $ValLength) {
					return NULL;
				}
				else {
					$Pos += $ValLength;
					
					return $Val;
				}
			}
		}
	}
	
	function GetBadge($RSSID) {
		$Feed = $this->PDO->query('SELECT * FROM RSS WHERE RSSID = '.$RSSID)->fetch();
		
		$LastActivity = Hub::GetActivity('page=RSS&Feed='.$Feed['RSSTitle'].'&Category=undefined');
		$TorrentPrep = $this->PDO->prepare('SELECT * FROM Torrents WHERE TorrentDate > :LastActivity AND RSSKey = :RSSID');
		$TorrentPrep->execute(array(':LastActivity' => $LastActivity,
									':RSSID'        => $RSSID));
		
		$TorrentNewSize = $TorrentPrep->rowCount();
		
		$TorrentQuery = $this->PDO->query('SELECT COUNT(TorrentID) AS TorrentSize FROM Torrents WHERE RSSKey = '.$RSSID)->fetch();
		$TorrentSize = $TorrentQuery['TorrentSize'];
		
		if($TorrentSize >= 1000) {
			$KSize = str_replace('000', '', round($TorrentSize, -3)).'K';
		}
		else {
			$KSize = $TorrentSize;
		}
		
		if($TorrentNewSize > 0 && $TorrentSize > 0) {
			echo '<span class="badge dual rightbadge blue-badge">'.$KSize.'</span><span class="badge dual leftbadge red-badge">'.$TorrentNewSize.'</span>';
		}
		else if($TorrentSize >= 0) {
			echo '<span class="badge single blue-badge">'.$KSize.'</span>';
		}
	}
}
?>