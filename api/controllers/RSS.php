<?php
/**
 * //@protected
**/
class RSS {
	private $PDO;
	
	function __construct() {
		$this->PDO = DB::Get();
	}
	
	/**
	 * @url GET /search/:SearchStr
	**/
	function SearchTorrents($SearchStr) {
		try {
			$SearchPrep = $this->PDO->prepare('SELECT
			                                   	Torrents.ID,
			                                   	Torrents.PubDate AS Date,
			                                   	Torrents.Category,
			                                   	Torrents.URI,
			                                   	Torrents.Title,
			                                   	RSS.Title AS Feed
			                                   FROM
			                                   	Torrents,
			                                   	RSS
			                                   WHERE
			                                   	Torrents.Title
			                                   LIKE
			                                   	:Search
			                                   AND
			                                   	Torrents.Title
			                                   NOT LIKE
			                                   	:ExcludeSearch
			                                   AND
			                                   	RSS.ID = Torrents.RSSKey
			                                   ORDER BY
			                                   	Torrents.Date
			                                   DESC');
			
			$SearchPrep->execute(array(':Search'        => urldecode($SearchStr).'%',
									   ':ExcludeSearch' => '%hebsub%'));
			$SearchRes = $SearchPrep->fetchAll();
			
			if(sizeof($SearchRes)) {
				$Data = array();
				foreach($SearchRes AS $Row) {
					$TorrentQuality = GetQualityRank($Row['Title']);
					if($TorrentQuality > GetSetting('MaximumDownloadQuality')) {
						$Row['Quality'] = 2;
					}
					else if($TorrentQuality >= GetSetting('MinimumDownloadQuality') && $TorrentQuality <= GetSetting('MaximumDownloadQuality')) {
						$Row['Quality'] = 1;
					}
					else if($TorrentQuality < GetSetting('MinimumDownloadQuality')) {
						$Row['Quality'] = 0;
					}
					
					$Data[] = $Row;
				}
					
				return $Data;
			}
			else {
				throw new RestException(404, 'Did not find any torrents in the database matching your criteria');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
	}
	
	/**
	 * @url GET /
	**/
	function RSSAll() {
		try {
			$RSSPrep = $this->PDO->prepare('SELECT
			                                	*
			                                FROM
			                                  	RSS
			                                ORDER BY
			                                  	Title');
			                                  	
			$RSSPrep->execute();
			
			if($RSSPrep->rowCount()) {
				return $RSSPrep->fetchAll();
			}
			else {
				throw new RestException(404, 'Did not find any RSS feeds in the database matching your criteria');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
	}
	
	/**
	 * @url POST /
	**/
	function AddRSS($Title, $Feed) {
		if(empty($Title) || empty($Feed)) {
			throw new RestException(412, 'Invalid request. Required parameters are "Title", "Feed"');
		}
		
		try {
			$RSSAddPrep = $this->PDO->prepare('INSERT INTO
											   	RSS
													(Date,
													Title,
													Feed)
												VALUES
													(:Date,
													:Title,
													:Feed)');
														
			$RSSAddPrep->execute(array(':Date'  => time(),
			                           ':Title' => $Title,
			                           ':Feed'  => $Feed));
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = 'Added "'.$Title.'" to RSS';
		
		AddLog(EVENT.'RSS', 'Success', $LogEntry);
		throw new RestException(201, $LogEntry);
	}
	
	/**
	 * @url DELETE /:ID
	**/
	function DeleteRSS($ID) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		try {	
			$RSSDeletePrep = $this->PDO->prepare('DELETE FROM
												  	RSS
												  WHERE
												   	ID = :ID');
												   	
			$RSSDeletePrep->execute(array(':ID' => $ID));
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = 'Deleted RSS feed with the ID "'.$ID.'" from the database';
		
		AddLog(EVENT.'RSS', 'Success', $LogEntry);
		throw new RestException(200, $LogEntry);
	}
	
	/**
	 * @url POST /update/:ID
	**/
	function UpdateRSS($ID) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		$AcceptedParameters = array('Title',
		                            'Feed');
		
		if(!sizeof($_POST)) {
			throw new RestException(412, 'Invalid request. Accepted parameters are "'.implode(', ', $AcceptedParameters).'"');
		}
		
		$UpdateQuery = 'UPDATE RSS SET ';
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
				$UpdateQuery .= ' WHERE RSSID = :ID';
				$PrepArr[':ID'] = $ID;
			}
		}
		
		try {
			$RSSPrep = $this->PDO->prepare($UpdateQuery);
			$RSSPrep->execute($PrepArr);
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = 'Updated RSS feed with the ID "'.$ID.'" in the database';
		
		AddLog(EVENT.'RSS', 'Success', $LogEntry);
		throw new RestException(200, $LogEntry);
	}
	
	/**
	 * @url GET /download/:TorrentID
	 * @url GET /download/:TorrentID/:EpisodeID
	**/
	function DownloadTorrent($TorrentID, $EpisodeID) {
		if(!is_numeric($TorrentID)) {
			throw new RestException(412, 'TorrentID must be a numeric value');
		}
	
		if(!empty($EpisodeID) && !is_numeric($EpisodeID)) {
			throw new RestException(412, 'EpisodeID must be a numeric value');
		}
		
		try {
			$TorrentPrep = $this->PDO->prepare('SELECT
													Torrents.URI,
													Torrents.Title,
													RSS.Title AS Feed
												FROM
													Torrents,
													RSS
												WHERE
													Torrents.ID = :TorrentID
												AND
													RSS.ID = Torrents.RSSKey');
			
			$TorrentPrep->execute(array(':TorrentID' => $TorrentID));
			$Torrent = $TorrentPrep->fetch();
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		if(is_array($Torrent)) {
			try {
				$UTorrentObj = new UTorrent;
				$UTorrentObj->Connect();
				$UTorrentObj->AddTorrent($Torrent['URI']);
			}
			catch(RestException $e) {
				throw new RestException(400, 'DownloadTorrent: '.$e->getMessage());
			}
			
			if(!empty($EpisodeID)) {
				try {
					$EpisodePrep = $this->PDO->prepare('UPDATE
															Episodes
														SET
														  	TorrentKey = :TorrentID
														WHERE
														  	ID = :EpisodeID');
														  	
					$EpisodePrep->execute(array(':TorrentID' => $TorrentID,
												':EpisodeID' => $EpisodeID));
				}
				catch(PDOException $e) {
					throw new RestException(400, 'MySQL: '.$e->getMessage());
				}
			}
			
			$LogEntry = 'Downloaded torrent "'.$Torrent['Title'].'" from "'.$Torrent['Feed'].'"';
			AddLog(EVENT.'RSS/uTorrent', 'Success', $LogEntry);
			throw new RestException(200, $LogEntry);
		}
		else {
			throw new RestException(404, 'Did not find any torrents matching your criteria');
		}
	}
	
	/**
	 * @url GET /download
	**/
	function DownloadWantedTorrents() {
		try {
			$TorrentsPrep = $this->PDO->prepare('SELECT
			                                     	Torrents.*,
			                                     	RSS.Title AS RSSTitle
			                                     FROM
			                                     	Torrents,
			                                     	RSS
			                                     WHERE
			                                     	Torrents.Date > :Date
			                                     AND
			                                     	RSS.ID = Torrents.RSSKey
			                                     ORDER BY
			                                     	Torrents.Date');
			                                     	
			$TorrentsPrep->execute(array(':Date' => strtotime('-10 days')));
			$Torrents = $TorrentsPrep->fetchAll();
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
				
		$DownloadArr = array();
		if(sizeof($Torrents)) {
			foreach($Torrents AS $Torrent) {
				$TorrentTitle = substr($Torrent['URI'], (strrpos($Torrent['URI'], '/') + 1));
				$Parsed       = ParseRelease($Torrent['Title']);
				
				if(is_array($Parsed) && $Parsed['Type'] == 'TV') {
					if(!preg_match("/\bgerman\b|\bhebsub\b|\bhebrew\b|\bsample\b|\bsubs\b/i", $Torrent['Title'])) {
						$SerieTitle = $Parsed['Title'];
						
						try {
							$EpisodePrep = $this->PDO->prepare('SELECT
							                                    	Series.Title AS SerieTitle,
							                                    	Series.TitleAlt AS SerieTitleAlt,
							                                    	Episodes.*
							                                    FROM
							                                    	Series,
							                                    	Episodes
							                                    WHERE
							                                    	Episodes.SeriesKey = Series.ID
							                                    AND
							                                    	(Series.Title = :Title
							                                    		OR
							                                    	Series.TitleAlt = :Title)
							                                    AND
							                                    	Episodes.Season = :Season
							                                    AND
							                                    	Episodes.Episode = :Episode
							                                    GROUP BY
							                                    	Series.Title');
							                                    	
							$EpisodePrep->execute(array(':Title'   => $Parsed['Title'],
														':Season'  => $Parsed['Episodes'][0][0],
														':Episode' => $Parsed['Episodes'][0][1]));
							$Episodes = $EpisodePrep->fetchAll();
						}
						catch(PDOException $e) {
							throw new RestException(400, 'MySQL: '.$e->getMessage());
						}
						
						if(sizeof($Episodes)) {
							foreach($Episodes AS $Episode) {
								$SerieTitle = $Parsed['Title'];
								$DownloadArrID = $SerieTitle.'-'.$Parsed['Episodes'][0][0].$Parsed['Episodes'][0][1];
								
								$NewQuality = GetQualityRank($Torrent['Title']);
								if($NewQuality >= GetSetting('MinimumDownloadQuality') && $NewQuality <= GetSetting('MaximumDownloadQuality')) {
									if(!isset($DownloadArr[$DownloadArrID])) {
										$DownloadArr[$DownloadArrID] = array();
									}
											
									if(isset($DownloadArr[$DownloadArrID][0])) {
										$OldQuality = GetQualityRank($DownloadArr[$DownloadArrID][0]);
									
										if($NewQuality > $OldQuality && $NewQuality >= GetSetting('MinimumDownloadQuality')) {
											$DownloadArr[$DownloadArrID][0] = $Torrent['URI'];
											$DownloadArr[$DownloadArrID][1] = $Episode['ID'];
											$DownloadArr[$DownloadArrID][2] = $Torrent['ID'];
										}
									}
									else {
										$DownloadArr[$DownloadArrID][0] = $Torrent['URI'];
										$DownloadArr[$DownloadArrID][1] = $Episode['ID'];
										$DownloadArr[$DownloadArrID][2] = $Torrent['ID'];
									}
									
									if($Episode['File']) {
										if($Episode['AirDate'] > strtotime('-1 month')) {
											$OldQuality = GetQualityRank($Episode['File']);
										
											if($NewQuality > $OldQuality && $NewQuality >= GetSetting('MinimumDownloadQuality')) {
												if(is_file($Episode['File'])) {
													if(unlink($Episode['File'])) {
														AddLog(EVENT.'Series', 'Success', 'Deleted "'.$Episode['File'].'" in favour of "'.$TorrentTitle.'"', 0, 'clean');
													
														$UpdateEpisodePrep = $this->PDO->prepare('UPDATE Episodes SET File = "", TorrentKey = "" WHERE File = :File');
														$UpdateEpisodePrep->execute(array(':File' => $Episode['File']));
														
														$DownloadArr[$DownloadArrID][0] = $Torrent['URI'];
														$DownloadArr[$DownloadArrID][1] = $Episode['ID'];
														$DownloadArr[$DownloadArrID][2] = $Torrent['ID'];
													}
													else {
														AddLog(EVENT.'Series', 'Failure', 'Tried to delete "'.$Episode['File'].'" in favour of "'.$TorrentTitle.'"');
													}
												}
											}
											else {
												unset($DownloadArr[$DownloadArrID]);
											}
										}
										else {
											unset($DownloadArr[$DownloadArrID]);
										}
									}
									else if($Episode['TorrentKey']) {
										$Torrents = $this->PDO->query('SELECT Title AS Title FROM Torrents WHERE ID = '.$Episode['TorrentKey'])->fetch();
									
										$OldQuality = GetQualityRank($Torrents['Title']);
										if($NewQuality > $OldQuality && $NewQuality >= GetSetting('MinimumDownloadQuality')) {
											$DownloadArr[$DownloadArrID][0] = $Torrent['URI'];
											$DownloadArr[$DownloadArrID][1] = $Episode['ID'];
											$DownloadArr[$DownloadArrID][2] = $Torrent['ID'];
										}
										else {
											unset($DownloadArr[$DownloadArrID]);
										}
									}
								}
							}
						}
					}
				}
				else if(is_array($Parsed) && $Parsed['Type'] == 'Movie') {
					if(!stristr($Torrent['Title'], 'hebrew') && !stristr($Torrent['Title'], 'hebsub')) {
						$WishlistPrep = $this->PDO->prepare('SELECT * FROM Wishlist WHERE Title = :Title AND Year = :Year');
						$WishlistPrep->execute(array(':Title' => $Parsed['Title'],
												 	 ':Year'  => $Parsed['Year']));
												 
						if($WishlistPrep->rowCount()) {
							$Wishlists = $WishlistPrep->fetchAll();
						
							foreach($Wishlists AS $Wishlist) {
								$NewQuality = GetQualityRank($Torrent['Title']);
								if($NewQuality >= GetSetting('MinimumDownloadQuality') && $NewQuality <= GetSetting('MaximumDownloadQuality')) {
									if(!isset($DownloadArr[$Parsed['Title']])) {
										$DownloadArr[$Parsed['Title']] = array();
									}
								
									if(isset($DownloadArr[$Parsed['Title']][0])) {
										$OldQuality = GetQualityRank($DownloadArr[$Parsed['Title']][0]);
							
										if($NewQuality > $OldQuality && $NewQuality >= GetSetting('MinimumDownloadQuality')) {
											$DownloadArr[$Parsed['Title']][0] = $Torrent['URI'];
											$DownloadArr[$Parsed['Title']][1] = 99999999;
											$DownloadArr[$Parsed['Title']][2] = $Torrent['ID'];
											$DownloadArr[$Parsed['Title']][3] = $Parsed['Title'];
										}
									}
									else {
										$DownloadArr[$Parsed['Title']][0] = $Torrent['URI'];
										$DownloadArr[$Parsed['Title']][1] = 99999999;
										$DownloadArr[$Parsed['Title']][2] = $Torrent['ID'];
										$DownloadArr[$Parsed['Title']][3] = $Parsed['Title'];
									}
						
									if($Wishlist['File']) {
										$OldQuality = GetQualityRank($Wishlist['File']);
									
										if($NewQuality > $OldQuality && $NewQuality >= GetSetting('MinimumDownloadQuality')) {
											if(is_file($Wishlist['File'])) {
												if(unlink($Wishlist['File'])) {
													AddLog(EVENT.'Wishlist', 'Success', 'Deleted "'.$Wishlist['File'].'"  in favour of "'.$TorrentTitle.'"', 0, 'clean');
											
													$WishlistUpdatePrep = $this->PDO->prepare('UPDATE Wishlist SET File = "" WHERE File = :File');
													$WishlistUpdatePrep->execute(array(':File' => $Wishlist['File']));
												
													$DownloadArr[$Parsed['Title']][0] = $Torrent['URI'];
													$DownloadArr[$Parsed['Title']][1] = 99999999;
													$DownloadArr[$Parsed['Title']][2] = $Torrent['ID'];
													$DownloadArr[$Parsed['Title']][3] = $Parsed['Title'];
												}
												else {
													AddLog(EVENT.'Wishlist', 'Failure', 'Tried to delete "'.$Wishlist['File'].'" in favour of "'.$TorrentTitle.'"');
												}
											}
										}
										else {
											unset($DownloadArr[$Parsed['Title']]);
										}
									}
									else if($Wishlist['TorrentKey']) {
										$Torrents = $this->PDO->query('SELECT Title AS Title FROM Torrents WHERE ID = '.$Wishlist['TorrentKey'])->fetch();
								
										$OldQuality = GetQualityRank($Torrents['Title']);
										if($NewQuality > $OldQuality && $NewQuality >= GetSetting('MinimumDownloadQuality')) {
											$DownloadArr[$Parsed['Title']][0] = $Torrent['URI'];
											$DownloadArr[$Parsed['Title']][1] = 99999999;
											$DownloadArr[$Parsed['Title']][2] = $Torrent['ID'];
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
			$UTorrentObj = new UTorrent;
			$UTorrentObj->Connect();
			$UTorrentObj->DownloadTorrents($DownloadArr);
			
			throw new RestException(200);
		}
		else {
			throw new RestException(404, 'Did not find any torrents matching your criteria');
		}
	}
	
	/**
	 * @url GET /refresh
	**/
	function RefreshRSSFeeds() {
		try {
			$RSSFeeds = $this->RSSAll();
		}
		catch(RestException $e) {
			throw new RestException(400, $e->getMessage());
		}
		
		$NewItems = 0;
		foreach($RSSFeeds AS $RSSFeed) {
			$Update = $this->PDO->query('SELECT PubDate AS Last FROM Torrents WHERE RSSKey = "'.$RSSFeed['ID'].'" ORDER BY PubDate DESC LIMIT 1')->fetch();
			
			$RSSFile = @file_get_contents($RSSFeed['Feed']);
						
			if($RSSFile) {
				$XML = new SimpleXMLElement($RSSFile);
				
				foreach($XML->channel->item as $Item) {
					$Item->pubDate = strtotime($Item->pubDate);
						
					if($Item->pubDate > $Update['Last']) {
						$RSSPrep = $this->PDO->prepare('INSERT INTO
						                                	Torrents
						                                		(ID,
						                                		Date,
						                                		PubDate,
						                                		URI,
						                                		Title,
						                                		Category,
						                                		RSSKey)
						                                	VALUES
						                                		(:ID,
						                                		:Date,
						                                		:PubDate,
						                                		:URI,
						                                		:Title,
						                                		:Category,
						                                		:RSSKey)');
						                                		
						$RSSPrep->execute(array(':ID'       => NULL,
												':Date'     => time(),
												':PubDate'  => $Item->pubDate,
												':URI'      => $this->StripCData($Item->link),
												':Title'    => $this->StripCData($Item->title),
												':Category' => $Item->category,
												':RSSKey'   => $RSSFeed['ID']));
						$NewItems++;
					}
				}
			}
		}
		
		$LogEntry = 'Added '.$NewItems.' torrents spread across '.sizeof($RSSFeeds).' RSS feeds';
		if($NewItems) {
			AddLog(EVENT.'RSS', 'Success', $LogEntry);
		}
		
		throw new RestException(200, $LogEntry);
	}
	
	function StripCData($Str) { 
		preg_match_all('/<!\[cdata\[(.*?)\]\]>/is', $Str, $Matches);
		
		return str_replace($Matches[0], $Matches[1], $Str); 
	}
	
	/**
	 * @url GET /:ID
	**/
	function GetTorrentsFromFeed($ID) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		try {
			$TorrentPrep = $this->PDO->prepare('SELECT
			                                    	*
			                                    FROM
			                                    	Torrents
			                                    WHERE
			                                    	RSSKey = :RSSKey
			                                    ORDER BY
			                                    	PubDate
			                                    DESC
			                                    LIMIT 100');
			                                    
			$TorrentPrep->execute(array(':RSSKey' => $ID));
			
			if($TorrentPrep->rowCount()) {
				return $TorrentPrep->fetchAll();
			}
			else {
				throw new RestException(404, 'Did not find any torrents in the database matching your criteria');
			}
		}
		catch(PDOException $e) {
			throw new RestException(412, 'MySQL: '.$e->getMessage());
		}
	}
}
?>