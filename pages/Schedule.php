<div class="head">Last 3 days</div>

<?php
$RecentEpisodes = json_decode($Hub->Request('/series/recent/3'));

echo '
<table>
 <thead>
  <tr>
   <th style="width: 16px">&nbsp;</th>
   <th style="width: 350px">Serie</th>
   <th style="width: 50px; text-align: center">Episode</th>
   <th>Title</th>
   <th style="width: 85px; text-align: right">Time Since</th>
  </tr>
 </thead>'."\n";
 
if(is_object($RecentEpisodes) && is_object($RecentEpisodes->error)) {
	echo '
	<tr>
	 <td colspan="5">'.$RecentEpisodes->error->message.'</td>
	</tr>'."\n";
}
else {
	foreach($RecentEpisodes AS $Episode) {
		if(date('d.m.y', $Episode->AirDate) == date('d.m.y', time())) {
			$Heading = 'Today';
		}
		else if(date('d.m.y', $Episode->AirDate) == date('d.m.y', (time() - (60 * 60 * 24)))) {
			$Heading = 'Yesterday';
		}
		else {
			$Heading = date('l', $Episode->AirDate);
		}
		
		if($Heading != @$PrevHeading) {
			echo '
			<tr class="heading">
			 <td style="color: white" colspan="5">'.$Heading.'</td>
			</tr>'."\n";
		}
		
		switch($Episode->Status) {
			case 'Available':
				$ActionLink = '<a id="FilePlay-'.$Episode->File.'" rel="ajax"><img src="images/icons/control_play.png" /></a>';
			break;
			
			case 'Downloaded':
				$ActionLink = '<img src="images/icons/downloaded.png" />';
			break;
			
			case 'Torrent':
				$ActionLink = '<a id="TorrentDownload-'.$Episode->Torrents[0]->ID.'" rel="ajax"><img src="images/icons/download.png" /></a>';
			break;
			
			case 'Torrents':
				$ActionLink = '<img src="images/icons/download_multiple.png" />';
			break;
			
			default:
				$ActionLink = '<img src="images/icons/search.png" />';
			break;
		}
		
		echo '
		<tr>
		 <td>'.$ActionLink.'</td>
		 <td><a href="?Page=Series&ID='.$Episode->ID.'">'.$Episode->Title.'</a></td>
		 <td style="text-align: center">'.$Episode->Season.'x'.$Episode->Episode.'</td>
		 <td>'.$Episode->EpisodeTitle.'</td>
		 <td style="text-align: right">'.ConvertSeconds(time() - $Episode->AirDate, FALSE).'</td>
		</tr>'."\n";
		
		$PrevHeading = $Heading;
	}
}

echo '
</table><br />'."\n";
?>

<div class="head">Upcoming</div>

<?php
$UpcomingEpisodes = json_decode($Hub->Request('/series/upcoming'));

echo '
<table>
 <thead>
  <tr>
   <th style="width: 16px">&nbsp;</th>
   <th style="width: 350px">Serie</th>
   <th style="width: 50px; text-align: center">Episode</th>
   <th>Title</th>
   <th style="width: 85px; text-align: right">Time Since</th>
  </tr>
 </thead>'."\n";
 
if(is_object($UpcomingEpisodes) && is_object($UpcomingEpisodes->error)) {
	echo '
	<tr>
	 <td colspan="5">'.$UpcomingEpisodes->error->message.'</td>
	</tr>'."\n";
}
else {
	foreach($UpcomingEpisodes AS $Episode) {
		if(date('d.m.y', $Episode->AirDate) == date('d.m.y', time())) {
			$Heading = 'Today';
		}
		else if(date('d.m.y', $Episode->AirDate) == date('d.m.y', (time() + (60 * 60 * 24)))) {
			$Heading = 'Tomorrow';
		}
		else {
			if($Episode->AirDate - time() > (60 * 60 * 24 * 3)) {
				$Heading = 'Upcoming';
			}
			else {
				$Heading = date('l', $Episode->AirDate);
			}
		}
		
		if($Heading != @$PrevHeading) {
			echo '
			<tr class="heading">
			 <td style="color: white" colspan="5">'.$Heading.'</td>
			</tr>'."\n";
		}
		
		switch($Episode->Status) {
			case 'Available':
				$ActionLink = '<a id="FilePlay-'.urlencode($Episode->File).'" rel="ajax"><img src="images/icons/control_play.png" /></a>';
			break;
			
			case 'Downloaded':
				$ActionLink = '<img src="images/icons/downloaded.png" />';
			break;
			
			case 'Torrent':
				$ActionLink = '<a id="TorrentDownload-'.$Episode->Torrents[0]->ID.'" rel="ajax"><img src="images/icons/download.png" /></a>';
			break;
			
			case 'Torrents':
				$ActionLink = '<img src="images/icons/download_multiple.png" />';
			break;
			
			default:
				$ActionLink = '';
			break;
		}
		
		echo '
		<tr>
		 <td>'.$ActionLink.'</td>
		 <td><a href="?Page=Series&ID='.$Episode->ID.'">'.$Episode->Title.'</a></td>
		 <td style="text-align: center">'.$Episode->Season.'x'.$Episode->Episode.'</td>
		 <td>'.$Episode->EpisodeTitle.'</td>
		 <td style="text-align: right">'.ConvertSeconds($Episode->AirDate - time(), FALSE).'</td>
		</tr>'."\n";
		
		$PrevHeading = $Heading;
	}
}

echo '
</table>'."\n";
?>