<script type="text/javascript">
$('a[id|="FilePlay"]').click(function(event) {
	AjaxLink(this);
});
</script>
<script type="text/javascript" src="js/hub.torrentDownload.js"></script>

<?php
$Days = (filter_has_var(INPUT_GET, 'Days')) ? $_GET['Days'] : '5';
$Series = $SeriesObj->GetPastSchedule($Days);

if($Series) {
	echo '
	<table>
	 <thead>
	 <tr>
	  <th width="16">&nbsp;</th>
	  <th width="30%">Serie</th>
	  <th width="50">Episode</th>
	  <th>Title</th>
	  <th style="width: 90px; text-align: right">Time Since</th>
	 </tr>
	 </thead>'."\n";
	 
	foreach($Series AS $Serie) {
		$SearchFile = $Serie['SerieTitle'].' s'.sprintf('%02s', $Serie['EpisodeSeason']).'e'.sprintf('%02s', $Serie['EpisodeEpisode']);
		$RSSTorrents = $RSSObj->SearchTitle($SearchFile);
		
		if(!empty($Serie['EpisodeFile'])) {
			$FileAction = ($UserObj->CheckPermission($UserObj->UserGroupID, 'XBMCPlay')) ? '<a id="FilePlay-'.urlencode($Serie['EpisodeFile']).'"><img src="images/icons/control_play.png" title="Play '.$Serie['EpisodeFile'].'" /></a>' : '';
		}
		else if($Serie['TorrentKey']) {
			$FileAction = '<a id="DownloadTorrent-'.$Serie['EpisodeID'].'-'.$Serie['TorrentKey'].'"><img src="images/icons/downloaded.png" title="Episode has been added to uTorrent. Click to re-download" /></a>';
		}
		else if($RSSTorrents) {
			if(sizeof($RSSTorrents) > 1) {
				$FileAction = ($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentDownload')) ? '<a id="DownloadMultipleTorrent-'.$Serie['EpisodeID'].'" rel="load.php?page=DownloadMultiple&File='.urlencode($SearchFile).'&EpisodeID='.$Serie['EpisodeID'].'"><img src="images/icons/download_multiple.png" /></a>' : '';
			}
			else {
				$Settings = $HubObj->Settings;
				$TorrentQuality = $RSSObj->GetQualityRank($RSSTorrents[0]['TorrentTitle']);
				if($TorrentQuality >= $Settings['SettingHubMinimumDownloadQuality'] && $TorrentQuality <= $Settings['SettingHubMaximumDownloadQuality']) {
					$EpisodeControlImg = 'images/icons/download.png';
				}
				else if($TorrentQuality < $Settings['SettingHubMinimumDownloadQuality']) {
					$EpisodeControlImg = 'images/icons/download_low_quality.png';
				}
				
				$FileAction = ($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentDownload')) ? '<a id="DownloadTorrent-'.$Serie['EpisodeID'].'-'.$RSSTorrents[0]['TorrentID'].'" title="Download \''.$RSSTorrents[0]['TorrentTitle'].'\' from \''.$RSSTorrents[0]['RSSTitle'].'\'"><img src="'.$EpisodeControlImg.'" /></a>' : '';
			}
		}
		else {
			$SearchQuery = strtolower($Serie['SerieTitle']);
			$SeasonEpisodeFormatted = sprintf("S%02sE%02s", $Serie['EpisodeSeason'], $Serie['EpisodeEpisode']);
			$FileAction = $RSSObj->CreateSearchLink($Serie['SerieTitle'].' s'.sprintf("%02de%02d", $Serie['EpisodeSeason'], $Serie['EpisodeEpisode']), 'tv');
		}
		
		if(date('d.m.y', $Serie['EpisodeAirDate']) == date('d.m.y', time())) {
			$Heading = 'Today';
        }
        else if(date('d.m.y', $Serie['EpisodeAirDate']) == date('d.m.y', (time() - (60 * 60 * 24)))) {
			$Heading = 'Yesterday';
        }
        else {
        	$Heading = date('l', $Serie['EpisodeAirDate']);
        }
		
		if($Heading != @$PrevHeading) {
			echo '
        	<tr class="heading">
        	 <td style="color: white" colspan="5">'.$Heading.'</td>
        	</tr>'."\n";
        }
		
		echo '
		<tr>
		 <td style="text-align: center">'.$FileAction.'</td>
		 <td><a href="#!/Series/'.urlencode($Serie['SerieTitle']).'">'.$Serie['SerieTitle'].'</a></td>
		 <td style="text-align: center">'.sprintf('%02s', $Serie['EpisodeSeason']).'x'.sprintf('%02s', $Serie['EpisodeEpisode']).'</td>
		 <td>'.$Serie['EpisodeTitle'].'</td>
		 <td style="text-align: right">'.$HubObj->ConvertSeconds(time() - $Serie['EpisodeAirDate'], FALSE).'</td>
		</tr>'."\n";
		
		$PrevHeading = $Heading;
	}
	
	echo '</table>'."\n";
}
else {
	echo '<div class="notification information">No episodes in the last '.$Days.' days</div>';
}
?>