<script type="text/javascript" src="js/hub.torrentDownload.js"></script>

<?php
$Series = $SeriesObj->GetFutureSchedule();

if($Series) {
	echo '
	<table>
	 <thead>
	 <tr>
	  <th width="16">&nbsp;</th>
	  <th width="30%">Serie</th>
	  <th width="50">Episode</th>
	  <th>Title</th>
	  <th style="width: 85px; text-align: right">Time Until</th>
	 </tr>
	 </thead>'."\n";
	
	foreach($Series AS $Serie) {
		$SearchFile = $Serie['SerieTitle'].' s'.sprintf('%02s', $Serie['EpisodeSeason']).'e'.sprintf('%02s', $Serie['EpisodeEpisode']);
		$RSSTorrents = $RSSObj->SearchTitle($SearchFile);
		
		$FileManagerLink = '';
		if(!empty($Serie['EpisodeFile'])) {
			$FileAction = ($UserObj->CheckPermission($UserObj->UserGroupID, 'XBMCPlay')) ? '<a id="FilePlay-'.urlencode($Serie['EpisodeFile']).'"><img src="images/icons/control_play.png" title="Play '.$Serie['EpisodeFile'].'" /></a>' : '';
			$FileManagerLink = '<a href="#!/FileManager/'.$DrivesObj->GetLocalLocation(dirname($Serie['EpisodeFile'])).'" title="View \''.$DrivesObj->GetLocalLocation(dirname($Serie['EpisodeFile'])).'\' in File Manager"><img style="vertical-align: middle" src="images/icons/go_arrow.png" /></a> ';
		}
		else if($Serie['TorrentKey']) {
			$FileAction = ($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentDownload')) ? '<a id="DownloadTorrent-'.$Serie['EpisodeID'].'-'.$RSSTorrents[0]['TorrentID'].'"><img src="images/icons/downloaded.png" title="Episode has been added to uTorrent. Click to re-download" /></a>' : '';
		}
		else if($RSSTorrents) {
			if(sizeof($RSSTorrents) > 1) {
				$FileAction = ($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentDownload')) ? '<a id="DownloadMultipleTorrent-'.$Serie['EpisodeID'].'" rel="load.php?page=DownloadMultiple&File='.urlencode($SearchFile).'&EpisodeID='.$Serie['EpisodeID'].'"><img src="images/icons/download_multiple.png" /></a>' : '';
			}
			else {
				$TorrentQuality = $RSSObj->GetQualityRank($RSSTorrents[0]['TorrentTitle']);
				if($TorrentQuality >= $HubObj->GetSetting('MinimumDownloadQuality') && $TorrentQuality <= $HubObj->GetSetting('MaximumDownloadQuality')) {
					$EpisodeControlImg = 'images/icons/download.png';
				}
				else if($TorrentQuality < $HubObj->GetSetting('MinimumDownloadQuality')) {
					$EpisodeControlImg = 'images/icons/download_low_quality.png';
				}
				
				$FileAction = ($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentDownload')) ? '<a id="DownloadTorrent-'.$Serie['EpisodeID'].'-'.$RSSTorrents[0]['TorrentID'].'" title="Download \''.$RSSTorrents[0]['TorrentTitle'].'\' from \''.$RSSTorrents[0]['RSSTitle'].'\'"><img src="'.$EpisodeControlImg.'" /></a>' : '';
			}
		}
		else {
			$FileAction = '';
		}
		
		if(date('d.m.y', $Serie['EpisodeAirDate']) == date('d.m.y', time())) {
			$Heading = 'Today';
        }
        else if(date('d.m.y', $Serie['EpisodeAirDate']) == date('d.m.y', (time() + (60 * 60 * 24)))) {
			$Heading = 'Tomorrow';
        }
        else {
        	if($Serie['EpisodeAirDate'] - time() > (60 * 60 * 24 * 3)) {
				$Heading = 'Upcoming';
        	}
        	else {
        		$Heading = date('l', $Serie['EpisodeAirDate']);
        	}
        }
		
		if($Heading != @$PrevHeading) {
			echo '
        	<tr class="heading">
        	 <td style="color: white" colspan="5">'.$Heading.'</td>
        	</tr>'."\n";
        }
        
		echo '
		<tr>
		 <td style="text-align:center">'.$FileAction.'</td>
		 <td>'.$FileManagerLink.'<a href="#!/Series/'.urlencode($Serie['SerieTitle']).'">'.$Serie['SerieTitle'].'</a></td>
		 <td style="text-align: center">'.sprintf('%02s', $Serie['EpisodeSeason']).'x'.sprintf('%02s', $Serie['EpisodeEpisode']).'</td>
		 <td>'.$Serie['EpisodeTitle'].'</td>
		 <td style="text-align: right">'.$HubObj->ConvertSeconds($Serie['EpisodeAirDate'] - time(), FALSE).'</td>
		</tr>'."\n";
		
		$PrevHeading = $Heading;
	}
	
	echo '</table>'."\n";
}
else {
	echo '<div class="notification information">No upcoming episodes data</div>';
}
?>