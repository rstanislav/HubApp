<?php
$XBMCLibraryUpdateLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'XBMCLibraryUpdate')) ? '<a id="XBMCLibraryUpdate-0" class="button positive"><span class="inner"><span class="label" nowrap="">Update Library</span></span></a>' : '';
$XBMCLibraryCleanLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'XBMCLibraryClean')) ? '<a id="XBMCLibraryClean-0" class="button positive"><span class="inner"><span class="label" nowrap="">Clean Library</span></span></a>' : '';
?>
<div class="head-control">
 <?php echo $XBMCLibraryUpdateLink; ?>
 <?php echo $XBMCLibraryCleanLink; ?>
</div>

<div class="head">XBMC Control Panel</div>

<?php
$XBMCObj->Connect();

if(is_object($XBMCObj->XBMCRPC)) {
	$ActivePlayer = $XBMCObj->MakeRequest('Player', 'GetActivePlayers');
	if(sizeof($ActivePlayer) && $ActivePlayer[0]['type'] == 'video') {
		$ItemInfo = $XBMCObj->XBMCRPC->Player->GetItem(array('playerid' => 1, 'properties' => array('tvshowid', 'duration', 'mpaa', 'writer', 'plotoutline', 'votes', 'year', 'rating', 'season', 'imdbnumber', 'studio', 'showlink', 'showtitle', 'episode', 'country', 'premiered', 'originaltitle', 'cast', 'firstaired', 'tagline', 'top250', 'trailer', 'plot', 'file')));
		
		$PlayerInfo = $XBMCObj->XBMCRPC->Player->GetProperties(array('playerid' => 1, 'properties' => array('speed', 'subtitleenabled', 'percentage', 'currentaudiostream', 'currentsubtitle', 'audiostreams', 'position', 'subtitles', 'totaltime', 'time')));
		
		$CurrentTime = sprintf('%02s:%02s:%02s', $PlayerInfo['time']['hours'], $PlayerInfo['time']['minutes'], $PlayerInfo['time']['seconds']);
		$TotalTime   = sprintf('%02s:%02s:%02s', $PlayerInfo['totaltime']['hours'], $PlayerInfo['totaltime']['minutes'], $PlayerInfo['totaltime']['seconds']);
		
		if($PlayerInfo['speed']) {
			$PlayStatus = 'Playing';
			$PlayPauseText = 'Pause';
		}
		else {
			$PlayStatus = 'Paused';
			$PlayPauseText = 'Play';
		}
		
		echo '
		<div class="head-control">
		 <a id="XBMCPlayPause-'.$ActivePlayer[0]['playerid'].'" class="button positive"><span class="inner"><span class="label" nowrap="">'.$PlayPauseText.'</span></span></a>
		 <a id="XBMCPlayStop-'.$ActivePlayer[0]['playerid'].'" class="button negative"><span class="inner"><span class="label" nowrap="">Stop</span></span></a>
		</div>'."\n";
		
		if($ItemInfo['item']['type'] == 'episode') { // TV Episode Playing
			$SerieFromDB = $SeriesObj->GetSerieByTitle($ItemInfo['item']['showtitle']);
			if(is_array($SerieFromDB)) {
				$SeriePosterThumb = str_replace('posters/', 'posters/thumbnails/', $SerieFromDB[0]['SeriePoster']);
		
				if(is_file($SeriePosterThumb)) {
					$SeriePoster = '<img class="poster" src="'.$SeriePosterThumb.'" />';
			
					if(is_file($SerieFromDB[0]['SeriePoster'])) {
						$SeriePoster = '<a href="'.$SerieFromDB[0]['SeriePoster'].'">'.$SeriePoster.'</a>';
					}
				}
				else {
					$SeriePoster = '<img class="poster" src="images/posters/unavailable.png" />';
				}
			}
			else {
				$SeriePoster = '<img class="poster" src="images/posters/unavailable.png" />';
			}
			
			echo '
			<div class="head">'.$PlayStatus.': '.$ItemInfo['item']['showtitle'].' &ndash; Season '.$ItemInfo['item']['season'].' Episode '.$ItemInfo['item']['episode'].'</div>
			
			<table width="300" class="nostyle">
			 <tr>
			  <td rowspan="9" style="text-align: center; width: 100px">
			   '.$SeriePoster.'
			  </td>
			 </tr>
			 <tr>
			  <td style="width: 100px"><strong>Episode:</strong></td>
			  <td>'.$ItemInfo['item']['label'].'</td>
			 </tr>
			 <tr>
		 	 <td><strong>Time:</strong></td>
		 	 <td>'.$CurrentTime.' of '.$TotalTime.'</td>
			 </tr>
			 <tr>
			  <td><strong>Aired:</strong></td>
			  <td>'.$ItemInfo['item']['firstaired'].'</td>
			 </tr>
			 <tr>
			  <td><strong>Audio:</strong></td>
			  <td>
		 	   '.$PlayerInfo['currentaudiostream']['channels'].' channels /
			   '.$PlayerInfo['currentaudiostream']['codec'].' ('.$PlayerInfo['currentaudiostream']['name'].')
			  </td>
			 </tr>
			 <tr>
			  <td><strong>Rating:</strong></td>
			  <td>'.number_format($ItemInfo['item']['rating'], 2).'</td>
			 </tr>
			 <tr>
			  <td><strong>MPAA:</strong></td>
			  <td>'.$ItemInfo['item']['mpaa'].'</td>
			 </tr>
			 <tr>
			  <td><strong>File:</strong></td>
			  <td>'.$ItemInfo['item']['file'].'</td>
			 </tr>
			</table>
			<br />
				
			<div class="head">Plot</div>
			'.nl2br($ItemInfo['item']['plot'])."\n";
		}
		else if($ItemInfo['item']['type'] == 'movie') {
			if(array_key_exists('id', $ItemInfo['item']) && is_file(APP_PATH.'/posters/thumbnails/movie-'.$ItemInfo['item']['id'].'.jpg')) {
				$Thumbnail = 'posters/thumbnails/movie-'.$ItemInfo['item']['id'].'.jpg';
			}
			else {
				$Thumbnail = 'images/poster-unavailable.png';
			}

			$Tagline  = !empty($ItemInfo['item']['tagline']) ? $ItemInfo['item']['tagline'] : 'NA';
			
			if($ItemInfo['item']['label'] == $ItemInfo['item']['originaltitle']) {
				$Title = $ItemInfo['item']['label'];
			}
			else {
				if($ItemInfo['item']['originaltitle']) {
					$Title = $ItemInfo['item']['label'].' ('.$ItemInfo['item']['originaltitle'].')';
				}
				else {
					$Title = $Title = $ItemInfo['item']['label'];
				}
			}
			
			$Country  = !empty($ItemInfo['item']['country']) ? $ItemInfo['item']['country'] : 'NA';
			$IMDBLink = ($ItemInfo['item']['imdbnumber'])    ? '<a href="http://www.imdb.com/title/'.$ItemInfo['item']['imdbnumber'].'/" target="_blank"><img style="vertical-align:text-bottom;" src="images/icons/imdb.png" /></a>' : '';
			$SubTitle = (is_array($PlayerInfo['currentsubtitle']) && array_key_exists('name', $PlayerInfo['currentsubtitle'])) ? $PlayerInfo['currentsubtitle']['name']                                              : 'NA';
			$Year     = ($ItemInfo['item']['year'])          ? ' ('.$ItemInfo['item']['year'].')' : '';
			$Studio   = ($ItemInfo['item']['studio'])        ? $ItemInfo['item']['studio']        : 'NA';
			$MPAA     = ($ItemInfo['item']['mpaa'])          ? $ItemInfo['item']['mpaa']          : 'NA';
			$Plot     = ($ItemInfo['item']['plot'])          ? nl2br($ItemInfo['item']['plot'])   : 'NA';
			
			echo '
			<div class="head">'.$PlayStatus.': '.$Title.$Year.' '.$IMDBLink.'</div>
			
			<table width="300" class="nostyle">
			 <tr>
			  <td rowspan="11" style="text-align: center; width: 100px">
			   <img class="poster" src="'.$Thumbnail.'" />
			  </td>
			 </tr>
			 <tr>
			  <td style="width: 60px"><strong>Tagline:</strong></td>
			  <td>'.$Tagline.'</td>
			 </tr>
			 <tr>
		 	 <td><strong>Time:</strong></td>
		 	 <td>'.$CurrentTime.' of '.$TotalTime.'</td>
			 </tr>
			 <tr>
			  <td><strong>Audio:</strong></td>
			  <td>
		 	   '.$PlayerInfo['currentaudiostream']['channels'].' channels /
			   '.$PlayerInfo['currentaudiostream']['codec'].' ('.$PlayerInfo['currentaudiostream']['name'].')
			  </td>
			 </tr>
			 <tr>
			  <td><strong>Rating:</strong></td>
			  <td>'.number_format($ItemInfo['item']['rating'], 2).' based on '.$ItemInfo['item']['votes'].' votes</td>
			 </tr>
			 <tr>
			  <td><strong>Country:</strong></td>
			  <td>'.$Country.'</td>
			 </tr>
			 <tr>
			  <td><strong>Studio:</strong></td>
			  <td>'.$Studio.'</td>
			 </tr>
			 <tr>
			  <td><strong>MPAA:</strong></td>
			  <td>'.$MPAA.'</td>
			 </tr>
			 <tr>
			  <td><strong>Subtitle:</strong></td>
			  <td>'.$SubTitle.'</td>
			 </tr>
			 <tr>
			  <td><strong>File:</strong></td>
			  <td>'.$ItemInfo['item']['file'].'</td>
			 </tr>
			</table>
			<br />
				
			<div class="head">Plot</div>
			'.$Plot."\n";
		}
		else {
			echo '<div class="notification information">Unable to decipher what is playing at the moment</div>';
		}
		
		if(sizeof($ItemInfo['item']['cast'])) {
			echo '<br /><br />
				
			<div class="head">Cast</div>
			<table style="width:520px">
			 <thead>
			 <tr>
			  <th style="width:250px; text-align:right">Actor</th>
			  <th style="width:20px">&nbsp;</th>
			  <th style="width:250px">Role</th>
			 </tr>
			 </thead>'."\n";
			
			$SupportingCast = '';
			foreach($ItemInfo['item']['cast'] AS $Cast) {
				if(!empty($Cast['name']) && !empty($Cast['role'])) {
					echo '
					<tr>
					 <td style="text-align: right">'.$Cast['name'].'</td>
					 <td style="text-align: center">as</td>
					 <td>'.$Cast['role'].'</td>
					</tr>'."\n";
				}
				else {
					$SupportingCast .= $Cast['name'].', ';
				}
			}
			
			if(strlen($SupportingCast)) {
				echo '
				<tr>
				 <td colspan="3" style="text-align: center; font-weight: bold">Supporting actors</td>
				</tr>
				<tr>
				 <td colspan="3" style="text-align: center">'.substr($SupportingCast, 0, -2).'</td>
				</tr>'."\n";
			}
			
			echo '</table>'."\n";
		}
	}
	else {
		echo '<div class="notification information">XBMC is currently idle</div>';
	}
}
else {
	echo '<div class="notification warning">Unable to connect to XBMC</div>';
}
?>
<!--
<div class="head">JSONRPC Introspect</div>

<script type="text/javascript">
$('div[id|="command"]').click(function(event) {
    Action = $(this).attr('id').split('-');
	ID = Action[1];
	Action = Action[0];
	
	$('div[id="data-' + ID + '"]').toggle();
});
</script>
//-->
<?php
$XBMCObj->Connect();

if(is_object($XBMCObj->XBMCRPC)) {
    //$HubObj->d($XBMCObj->GetCommands());
}
?>