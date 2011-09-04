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
		
		if(!empty($Serie['EpisodeFile'])) {
			$FileAction = ($UserObj->CheckPermission($UserObj->UserGroupID, 'XBMCPlay')) ? '<a id="FilePlay-'.$Serie['EpisodeFile'].'"><img src="images/icons/control_play.png" title="Play '.$Serie['EpisodeFile'].'" /></a>' : '';
		}
		else if($Serie['TorrentKey']) {
			$FileAction = '<img src="images/icons/downloaded.png" title="Episode has been added to uTorrent" />';
		}
		else if($RSSTorrents) {
			if(sizeof($RSSTorrents) > 1) {
				$FileAction = ($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentDownload')) ? '<a id="DownloadMultipleTorrent-'.$Serie['EpisodeID'].'" rel="load.php?page=DownloadMultiple&File='.urlencode($SearchFile).'&EpisodeID='.$Serie['EpisodeID'].'"><img src="images/icons/download_multiple.png" /></a>' : '';
			}
			else {
				$FileAction = ($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentDownload')) ? '<a id="DownloadTorrent-'.$Serie['EpisodeID'].'-'.$RSSTorrents[0]['TorrentID'].'"><img src="images/icons/download.png" /></a>' : '';
			}
		}
		else {
			$FileAction = '';
		}
		
		echo '
		<tr>
		 <td style="text-align:center">'.$FileAction.'</td>
		 <td><a href="#!/Series/'.urlencode($Serie['SerieTitle']).'">'.$Serie['SerieTitle'].'</a></td>
		 <td style="text-align: center">'.sprintf('%02s', $Serie['EpisodeSeason']).'x'.sprintf('%02s', $Serie['EpisodeEpisode']).'</td>
		 <td>'.$Serie['EpisodeTitle'].'</td>
		 <td style="text-align: right">'.$HubObj->ConvertSeconds($Serie['EpisodeAirDate'] - time(), FALSE).'</td>
		</tr>'."\n";
	}
	
	echo '</table>'."\n";
}
else {
	echo 'No upcoming episodes data';
}
?>