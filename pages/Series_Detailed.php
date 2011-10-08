<script type="text/javascript" src="js/hub.torrentDownload.js"></script>

<?php
$Series = $SeriesObj->GetSerieByTitle(urldecode($SerieTitle));

if(is_array($Series)) {
	foreach($Series AS $Serie) {
		$SerieID = $Serie['SerieID'];
		$Serie['SerieTitleAlt'] = (strlen($Serie['SerieTitleAlt'])) ? '/'.$Serie['SerieTitleAlt'] : '';
		
		$SeriePosterThumb = str_replace('posters/', 'posters/thumbnails/', $Serie['SeriePoster']);
		
		if(is_file($SeriePosterThumb)) {
			$SeriePoster = '<img class="poster" src="'.$SeriePosterThumb.'" />';
			
			if(is_file($Serie['SeriePoster'])) {
				$SeriePoster = '<a href="'.$Serie['SeriePoster'].'">'.$SeriePoster.'</a>';
			}
		}
		else {
			$SeriePoster = '<img class="poster" src="images/posters/unavailable.png" />';
		}
		
		$SerieRefreshLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'SerieRefresh')) ? '<a id="SerieRefresh-'.$SerieID.'" class="button neutral"><span class="inner"><span class="label" nowrap="">Refresh</span></span></a>' : '';
		$SerieSpellingLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'SerieAddSpelling')) ? '<a id="SerieSpelling-'.$SerieID.'" rel="'.$Serie['SerieTitle'].'" class="button positive"><span class="inner"><span class="label" nowrap="">+Spelling</span></span></a>' : '';
		$SerieDeleteLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'SerieDelete')) ? '<a id="SerieDelete-'.$SerieID.'" rel="'.$Serie['SerieTitle'].'" class="button negative"><span class="inner"><span class="label" nowrap="">Delete</span></span></a>' : '';
		
		echo '
		<div class="head-control">
		 '.$SerieRefreshLink.'
		 '.$SerieSpellingLink.'
		 '.$SerieDeleteLink.'
		</div>
	
		<div class="head">'.$Serie['SerieTitle'].$Serie['SerieTitleAlt'].' ('.date('Y', $Serie['SerieFirstAired']).')</div>
	
		<table width="300" class="nostyle">
		 <tr>
		  <td rowspan="9" width="100">
		   '.$SeriePoster.'
		  </td>
		  <td><strong>First Aired:</strong></td>
		  <td>'.date('F jS, Y', $Serie['SerieFirstAired']).'</td>
		 </tr>
		 <tr>
		  <td><strong>Genre:</strong></td>
		  <td>'.$Serie['SerieGenre'].'</td>
		 </tr>
		 <tr>
	 	  <td><strong>Schedule:</strong></td>
		  <td>'.$Serie['SerieAirDay'].' '.$Serie['SerieAirTime'].'</td>
		 </tr>
		 <tr>
		  <td><strong>Runtime:</strong></td>
		  <td>'.$Serie['SerieRuntime'].'</td>
		 </tr>
		 <tr>
		  <td><strong>Rating:</strong></td>
		  <td>'.$Serie['SerieRating'].' ('.$Serie['SerieRatingCount'].' votes)</td>
		 </tr>
		 <tr>
		  <td width="100"><strong>Content Rating:</strong></td>
		  <td>'.$Serie['SerieContentRating'].'</td>
		 </tr>
		 <tr>
		  <td><strong>Network:</strong></td>
		  <td>'.$Serie['SerieNetwork'].'</td>
		 </tr>
		 <tr>
		  <td><strong>Status:</strong></td>
		  <td>'.$Serie['SerieStatus'].'</td>
		 </tr>
		 <tr>
	 	 <td style="vertical-align: top"><strong>External info:</strong></td>
	 	 <td style="vertical-align: top">
	 	  <a href="http://imdb.com/title/'.$Serie['SerieIMDBID'].'/" target="_blank">IMDB</a> |
		   <a href="http://thetvdb.com/?tab=series&id='.$Serie['SerieTheTVDBID'].'" target="_blank">TheTVDB</a>
		  </td>
		 </tr>
		 <tr>
		  <td colspan="3">
		   '.nl2br($Serie['SeriePlot']).'
		  </td>
		 </tr>
		</table>
	
		<br />';
	}
	?>

	<div class="head">Episodes</div>
	<?php
	$Seasons = $SeriesObj->GetSeasons($SerieID);
	if(is_array($Seasons)) {
		echo '<table>'."\n";

		$SeasonNo = $Seasons[0]['EpisodeSeason'];
		foreach($Seasons AS $Episode) {
			$EpisodeControl = '';
			$OtherOptions = FALSE;
			
			if($Episode['EpisodeFile']) {
				$Episode['EpisodeFile'] = $HubObj->ConcatFilePath($Episode['EpisodeFile']);
				
				$PlayFileLink      = ($UserObj->CheckPermission($UserObj->UserGroupID, 'XBMCPlay'))           ? '<a id="FilePlay-'.urlencode($Episode['EpisodeFile']).'"><img src="images/icons/control_play.png" title="Play '.$Episode['EpisodeFile'].'" /></a>' : '';
				$DeleteEpisodeLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'SerieDeleteEpisode')) ? '<a id="DeleteEpisode-'.$Episode['EpisodeID'].'" rel="'.$Serie['SerieTitle'].' s'.sprintf('%02s', $Episode['EpisodeSeason']).'e'.sprintf('%02s', $Episode['EpisodeEpisode']).'"><img src="images/icons/delete.png" /></a>'       : '';
				
				$EpisodeControl = $PlayFileLink.' '.$DeleteEpisodeLink;
				$OtherOptions = TRUE;
			}
			else if($Episode['TorrentKey']) {
				$Episode['EpisodeFile'] = 'Episode has been added to uTorrent';
				$EpisodeControl = '<img src="images/icons/downloaded.png" />';
				$OtherOptions = TRUE;
			}
			else {
				if($Episode['EpisodeAirDate'] > time()) {
					$Episode['EpisodeFile'] = 'Upcoming';
					$OtherOptions = TRUE;
				}
				else {
					if(!empty($Serie['SerieTitleAlt'])) {
						$SearchFile = $Serie['SerieTitleAlt'].' s'.sprintf('%02s', $Episode['EpisodeSeason']).'e'.sprintf('%02s', $Episode['EpisodeEpisode']);
					}
					else {
						$SearchFile = $Serie['SerieTitle'].' s'.sprintf('%02s', $Episode['EpisodeSeason']).'e'.sprintf('%02s', $Episode['EpisodeEpisode']);
					}
					
					$RSSTorrents = $RSSObj->SearchTitle($SearchFile);
				
					if($RSSTorrents) {
						if(sizeof($RSSTorrents) > 1) {
							$EpisodeControl = '<a id="DownloadMultipleTorrent-'.$Episode['EpisodeID'].'" rel="load.php?page=DownloadMultiple&File='.urlencode($SearchFile).'&EpisodeID='.$Episode['EpisodeID'].'"><img src="images/icons/download_multiple.png" /></a>';
							$Episode['EpisodeFile'] = 'Local Torrent Entry Available';
							$OtherOptions = TRUE;
						}
						else {
							$Settings = $HubObj->Settings;
							$TorrentQuality = $RSSObj->GetQualityRank($RSSTorrents[0]['TorrentTitle']);
							if($TorrentQuality >= $Settings['SettingHubMinimumDownloadQuality'] && $TorrentQuality <= $Settings['SettingHubMaximumDownloadQuality']) {
								$EpisodeControl = '<a id="DownloadTorrent-'.$Episode['EpisodeID'].'-'.$RSSTorrents[0]['TorrentID'].'"><img src="images/icons/download.png" /></a>';
								$Episode['EpisodeFile'] = 'Local Torrent Entry Available';
								$OtherOptions = TRUE;
							}
							else {
								$Episode['EpisodeFile'] = 'Not Available';
							}
						}
						
						$EpisodeControl = ($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentDownload')) ? $EpisodeControl : '';
					}
				}
			}
			
			if(!$OtherOptions) {
				$SearchQuery = strtolower($Serie['SerieTitle']);
				$SeasonEpisodeFormatted = sprintf("S%02sE%02s", $Episode['EpisodeSeason'], $Episode['EpisodeEpisode']);
				$EpisodeControl = '<a href="http://www.torrentleech.org/torrents/browse/index/query/'.urlencode($Serie['SerieTitle'].' s'.sprintf('%02s', $Episode['EpisodeSeason']).'e'.sprintf('%02s', $Episode['EpisodeEpisode'])).'/facets/e8044d" target="_blank"><img src="images/icons/search.png" title="Search TorrentLeech.org for '.htmlspecialchars('"'.$Serie['SerieTitle'].' s'.sprintf("%02de%02d", $Episode['EpisodeSeason'], $Episode['EpisodeEpisode']).'"').'" /></a>';
				
				$Episode['EpisodeFile'] = 'Not Available';
			}
		
			if($Episode['EpisodeSeason'] == $SeasonNo) {
				echo '
				<thead>
				 <tr>
				  <th colspan="5" style="text-align:center">Season '.$SeasonNo.'</th>
				 </tr>
				</thead>
				<thead>
				<tr>
				 <th width="50">&nbsp;</th>
				 <th width="25%">Title</th>
				 <th width="60">Air Date</th>
				 <th>File/Search</th>
				 <th width="36">&nbsp;</th>
				</tr>
				</thead>'."\n";
			
				$SeasonNo--;
			}
		
			echo '
			<tr id="Episode-'.$Episode['EpisodeID'].'">
			 <td>'.sprintf("S%02sE%02s", $Episode['EpisodeSeason'], $Episode['EpisodeEpisode']).'</td>
			 <td>'.$Episode['EpisodeTitle'].'</td>
			 <td>'.date('d.m.y', $Episode['EpisodeAirDate']).'</td>
			 <td>'.$Episode['EpisodeFile'].'</td>
			 <td style="text-align:right">
			  '.$EpisodeControl.'
			 </td>
			</tr>'."\n";
		}
	
		echo '</table>'."\n";
	}
	else {
		echo '<div class="notification information">No data available</div>';
	}
}
else {
	echo '<div class="notification information">No data available</div>';
}
?>