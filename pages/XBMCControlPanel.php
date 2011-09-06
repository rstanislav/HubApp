<div class="head-control">
 <a id="XBMCUpdateLibrary-0" class="button positive"><span class="inner"><span class="label" nowrap="">Update Library</span></span></a>
 <a id="XBMCCleanLibrary-0" class="button positive"><span class="inner"><span class="label" nowrap="">Clean Library</span></span></a>
 <!--<a id="XBMCTakeScreenshot-0" class="button positive"><span class="inner"><span class="label" nowrap="">Take A Screenshot</span></span></a>//-->
</div>
 
<div class="head">XBMC Control Panel <small style="font-size: 12px;">(<a href="#!/Help/XBMCCP">?</a>)</small></div>

<?php
$XBMCObj->Connect();

if(is_object($XBMCObj->XBMCRPC)) {
	// $HubObj->d($XBMCObj->GetCommands());

	$InfoLabelParams = explode('-', 'Fanart.Image-'.
	                                'Container.TvshowThumb-'.
                                    'Container.SeasonThumb-'.
                                    'Container.FolderThumb-'.
                                    'Container.FolderPath-'.
                                    'Container.Viewmode-'.
                                    'Container.ShowPlot-'.
                                    'Container.TvshowThumb-'.
                                    'ListItem.Plot-'.
                                    'VideoPlayer.Cover-'.
                                    'VideoPlayer.TVShowTitle-'.
                                    'VideoPlayer.Title-'.
                                    'VideoPlayer.Duration-'.
                                    'VideoPlayer.Time-'.
                                    'VideoPlayer.Year-'.
                                    'VideoPlayer.Rating-'.
                                    'VideoPlayer.Plot-'.
                                    'VideoPlayer.PlotOutline-'.
                                    'VideoPlayer.Tagline-'.
                                    'VideoPlayer.TimeRemaining-'.
                                    'VideoPlayer.Cast-'.
                                    'VideoPlayer.CastAndRole-'.
                                    'VideoPlayer.PlayCount-'.
                                    'VideoPlayer.VideoCodec-'.
                                    'VideoPlayer.VideoResolution-'.
                                    'VideoPlayer.VideoAspect-'.
                                    'VideoPlayer.AudioCodec-'.
                                    'VideoPlayer.AudioChannels-'.
                                    'VideoPlayer.PlaylistPosition-'.
                                    'VideoPlayer.PlaylistLength-'.
                                    'VideoPlayer.mpaa-'.
                                    'VideoPlayer.RatingAndVotes-'.
                                    'VideoPlayer.PlayCount-'.
                                    'Player.StarRating-'.
                                    'Player.TimeRemaining-'.
                                    'Player.Duration-'.
                                    'Player.Folderpath-'.
                                    'Player.Filenameandpath-'.
                                    'Player.FinishTime-'.
                                    'Player.Volume-'.
                                    'Player.Filenameandpath-'.
                                    'System.ScreenMode-'.
                                    'System.CurrentWindow-'.
                                    'System.CurrentControl-'.
                                    'System.Uptime-'.
                                    'System.TotalUptime');
                                
	$ActivePlayer = $XBMCObj->MakeRequest('Player', 'GetActivePlayers');
	$XBMC = $XBMCObj->MakeRequest('System', 'GetInfoLabels', $InfoLabelParams);

	if($ActivePlayer['video']) {
		$HubObj->d($XBMCObj->MakeRequest('VideoPlayer', 'State'));
	
		if($XBMC['VideoPlayer.TVShowTitle']) { // TV Episode Playing
			echo '
			<div class="head-control">
			 <a id="XBMCPause-0" class="button positive"><span class="inner"><span class="label" nowrap="">Pause</span></span></a>
			 <a id="XBMCStop-0" class="button positive"><span class="inner"><span class="label" nowrap="">Stop</span></span></a>
			</div>'."\n";
		
			$CoverThumb = ($XBMC['Container.TvshowThumb']) ? '<img class="poster" src="'.$XBMC['Container.TvshowThumb'].'" />' : '<img class="poster" src="images/posters/unavailable.png" />';
			
			$XBMC['Player.Folderpath'] = str_replace('\\', '/', $XBMC['Player.Folderpath']);
			$XBMC['Player.Filenameandpath'] = str_replace('\\', '/', $XBMC['Player.Filenameandpath']);
		
			echo '
			<table width="300" class="nostyle">
			 <tr>
			  <td rowspan="9" style="text-align: center; width: 100px">
			   '.$CoverThumb.'
			  </td>
			  <td style="width: 100px"><strong>Serie:</strong></td>
			  <td>'.$XBMC['VideoPlayer.TVShowTitle'].'</td>
			 </tr>
			 <tr>
			  <td><strong>Episode:</strong></td>
			  <td>'.$XBMC['VideoPlayer.Title'].'</td>
			 </tr>
			 <tr>
			  <td><strong>Year:</strong></td>
			  <td>'.$XBMC['VideoPlayer.Year'].'</td>
			 </tr>
			 <tr>
			  <td><strong>MPAA:</strong></td>
			  <td>'.$XBMC['VideoPlayer.mpaa'].'</td>
			 </tr>
			 <tr>
		 	 <td><strong>Time:</strong></td>
		 	 <td>
		 	  '.$XBMC['VideoPlayer.Time'].' of '.$XBMC['VideoPlayer.Duration'].'. 
		 	  Finished in '.$XBMC['VideoPlayer.TimeRemaining'].' at '.$XBMC['Player.FinishTime'].'
			  </td>
			 </tr>
			 <tr>
			  <td><strong>Video:</strong></td>
			  <td>
			   '.$XBMC['VideoPlayer.VideoCodec'].' /
			   '.$XBMC['VideoPlayer.VideoAspect'].' /
			   '.$XBMC['VideoPlayer.VideoResolution'].'
			  </td>
			 </tr>
			 <tr>
			  <td><strong>Audio:</strong></td>
			  <td>
		 	   '.$XBMC['VideoPlayer.AudioChannels'].' channels /
			   '.$XBMC['VideoPlayer.AudioCodec'].'
			  </td>
			 </tr>
			 <tr>
			  <td><strong>Rating:</strong></td>
			  <td>'.$XBMC['VideoPlayer.Rating'].'</td>
			 </tr>
			 <tr>
			  <td><strong>File:</strong></td>
			  <td>'.$XBMC['Player.Filenameandpath'].'</td>
			 </tr>
			 <tr>
			  <td colspan="3">
			   '.nl2br($XBMC['VideoPlayer.Plot']).'
			  </td>
			 </tr>
			</table>'."\n";
		
			/*
			echo 
			'You are currently watching: '.$XBMC['VideoPlayer.Title'].'<br />'.
			'Audio: '.$XBMC['VideoPlayer.AudioChannels'].'<br />'.
			'Audio Codec: '.$XBMC['VideoPlayer.AudioCodec'].'<br />'.
			'Video aspect: '..'<br />'.
			'Video codec: '.$XBMC['VideoPlayer.VideoCodec'].'<br />'.
			'Video resolution: '.$XBMC['VideoPlayer.VideoResolution'].'<br />'.
			'Year: '.$XBMC['VideoPlayer.Year'].'<br />'.
			'mpaa: '.$XBMC['VideoPlayer.mpaa'].'<br />'.
			'current time: '.$XBMC['VideoPlayer.Time'].'<br />'.
			'remaining time: '.$XBMC['VideoPlayer.TimeRemaining'].'<br />'.
			'duration: '.$XBMC['VideoPlayer.Duration'].'<br />'.
			'finished at: '.$XBMC['Player.FinishTime'].'<br />'.
			'plot: '.$XBMC['VideoPlayer.Plot'].'<br />'.
			'rating: '.$XBMC['VideoPlayer.Rating'].'<br />'.
			'cast: '.$XBMC['VideoPlayer.Cast'].'<br />'.
			'File with path: '.$XBMC['Player.Filenameandpath'].'<br />'.
			'Folderpath: '.$XBMC['Player.Folderpath'].'<br />'.
			'Window: '.$XBMC['System.ScreenMode'];
			*/
		}
	
		print_r($XBMCObj->d($XBMC));
	}
	else {
		// info about lists
		echo '<div class="notification">Nothing is playing right now</div>';
	}
}
else {
	echo '<div class="notification">Unable to connect to XBMC</div>';
}
?>