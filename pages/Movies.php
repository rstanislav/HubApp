<div class="head-control">
 <a id="MovieToggleGenre-0" class="button positive"><span class="inner"><span class="label" nowrap="">Toggle Genre</span></span></a>
 <a id="MovieTogglePath-0" class="button positive"><span class="inner"><span class="label" nowrap="">Toggle File Path</span></span></a>
</div>

<div class="head">Recently added movies <small style="font-size: 12px;">(<a href="#!/Help/Movies">?</a>)</small></div>

<?php
$XBMCObj->Connect();

if(is_object($XBMCObj->XBMCRPC)) {
	$RecentMovies = $XBMCObj->GetRecentlyAddedMovies();
	
	//$HubObj->d($RecentMovies);
	
	//$XBMCObj->d($XBMCObj->GetCommands($HubObj->XBMCRPC));
	
	if(is_array($RecentMovies)) {
		$i = 1;
		echo '
		<table width="100%" class="nostyle">
		 <tr>'."\n";
		foreach($RecentMovies['movies'] AS $Movie) {
			if(is_file(APP_PATH.'/posters/thumbnails/movie-'.$Movie['movieid'].'.jpg')) {
				$Thumbnail = 'posters/thumbnails/movie-'.$Movie['movieid'].'.jpg';
			}
			else {
				$Thumbnail = 'images/poster-unavailable.png';
			}
			
			$Genre = (array_key_exists('genre', $Movie)) ? $Movie['genre'] : '';
			$Files = $HubObj->ConcatFilePath($Movie['file']);
			
			$FilePath = '';
			if(is_array($Files)) {
				$FilePath = implode('<br />', $Files);
			}
			else {
				$FilePath = $Files;
			}
			
			$MoviePlayLink  = ($UserObj->CheckPermission($UserObj->UserGroupID, 'XBMCPlay')) ? '<a id="MoviePlay-'.$Movie['movieid'].'" class="cover-link"><img src="images/icons/control_play.png" /></a>' : '';
			
			if(array_key_exists('trailer', $Movie)) {
				if(strstr($Movie['trailer'], 'plugin.video.youtube')) {
					$MovieTrailerLink = '<a href="http://youtube.com/watch?v='.str_replace('plugin://plugin.video.youtube/?action=play_video&videoid=', '', $Movie['trailer']).'" rel="trailer" class="cover-link" title="'.$Movie['label'].' ('.$Movie['year'].') Trailer"><img  src="images/icons/youtube.png" /></a>';
				}
				else if(strstr($Movie['trailer'], 'http://playlist.yahoo.com')) {
					$MovieTrailerLink = '<a href="'.$Movie['trailer'].'" rel="trailer" class="cover-link" title="'.$Movie['label'].' ('.$Movie['year'].') Trailer"><img  src="images/icons/yahoo.png" /></a>';
				}
			}
			else {
				$MovieTrailerLink = '<a href="http://youtube.com/results?search_query='.urlencode($Movie['label'].' '.$Movie['year'].' trailer').'" target="_blank" class="cover-link" title="Search for trailer on YouTube"><img  src="images/icons/youtube.png" /></a>';
			}
			
			$MovieInfoLink   = ($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewMovieInformation')) ? '<a id="MovieInfo-'.$Movie['movieid'].'" class="cover-link"><img src="images/icons/information.png" /></a>'  : '';
			
			$Watched = (array_key_exists('lastplayed', $Movie)) ? '<div class="cover-watched">watched</div>' : '';
			
			$MoviePoster = '
			 <div id="Cover-'.$Movie['movieid'].'" class="cover">
			  <img class="poster" width="150" height="250" src="'.$Thumbnail.'" />
			  '.$Watched.'
			  <div id="CoverControl-'.$Movie['movieid'].'" class="cover-control">
			   '.$MoviePlayLink.'
			   '.$MovieInfoLink.'
			   '.$MovieTrailerLink.'
			  </div>
			 </div>';
			
			$GenreShow = (filter_has_var(INPUT_COOKIE, 'MovieGenre')) ? ' style="'.$_COOKIE['MovieGenre'].'"' : ' style="display: inline;"';
			$PathShow = (filter_has_var(INPUT_COOKIE, 'MoviePath')) ? ' style="'.$_COOKIE['MoviePath'].'"' : ' style="display: inline;"';
			
			echo '
			<td style="text-align: center; width:33%;">
			 <div style="width: 151px; height: 250px; margin: 0 auto;">'.$MoviePoster.'</div><br />
			 <strong>'.$Movie['label'].' ('.$Movie['year'].')</strong>
			 <span class="MovieGenre"'.$GenreShow.'><br /><em>'.$Genre.'</em></span>
			 <span class="MoviePath"'.$PathShow.'><br /><small>'.$FilePath.'</small></span><br /><br />
			</td>'."\n";
			
			if($i++ % 3 == 0) {
				echo '
				</tr>
				<tr>'."\n";
			}
		}
		echo '</table>'."\n";
	}
	?>
	
	<div class="head">All Movies <small style="font-size: 12px;">(<a href="#!/Help/Movies">?</a>)</small></div>
	<?php
	$AllMovies = $XBMCObj->GetMovies();
	
	if(is_array($AllMovies)) {
		$Movies = array();
		foreach($AllMovies['movies'] AS $Movie) {
			$Title = trim(str_replace('The ', '', trim($Movie['label'])));
			$Movies[$Title]['id']         = trim($Movie['movieid']);
			$Movies[$Title]['label']      = trim($Movie['label']);
			$Movies[$Title]['file']       = trim($Movie['file']);
			$Movies[$Title]['genre']      = (!isset($Movie['genre']))      ? '' : trim($Movie['genre']);
			$Movies[$Title]['year']       = (!isset($Movie['year']))       ? '' : trim($Movie['year']);
			$Movies[$Title]['thumbnail']  = (!isset($Movie['thumbnail']))  ? '' : trim($Movie['thumbnail']);
			$Movies[$Title]['fanart']     = (!isset($Movie['fanart']))     ? '' : trim($Movie['fanart']);
			$Movies[$Title]['lastplayed'] = (array_key_exists('lastplayed', $Movie)) ? $Movie['lastplayed'] : '';
		}
		
		ksort($Movies);
		
		echo '
		<table width="100%">
		 <thead>
		 <tr>
		  <th>Title</th>
		  <th>Year</th>
		  <th>Genre</th>
		  <th style="width:74px">&nbsp;</th>
		 </tr>
		 </thead>'."\n";
		 
		foreach($Movies AS $Movie) {
			$MoviePlayLink   = ($UserObj->CheckPermission($UserObj->UserGroupID, 'XBMCPlay'))             ? '<a id="MoviePlay-'.$Movie['id'].'"><img src="images/icons/control_play.png" /></a>' : '';
			$MovieInfoLink   = ($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewMovieInformation')) ? '<a id="MovieInfo-'.$Movie['id'].'"><img src="images/icons/information.png" /></a>'  : '';
			$MovieDeleteLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'MovieDelete'))          ? '<a id="MovieDelete-'.$Movie['id'].'"><img src="images/icons/delete.png" /></a>'    : '';
			
			$Watched = (strlen($Movie['lastplayed'])) ? '<img style="vertical-align:text-bottom;" src="images/icons/watched.png" /> ' : '';
			
			echo '
			<tr>
			 <td>'.$Watched.''.$Movie['label'].'</td>
			 <td>'.$Movie['year'].'</td>
			 <td>'.$Movie['genre'].'</td>
			 <td style="text-align: right">
			  '.$MoviePlayLink.'
			  '.$MovieInfoLink.'
			  <a href="http://www.youtube.com/results?search_query='.urlencode($Movie['label'].' '.$Movie['year'].' trailer').'" target="_blank" title="Search for trailer on YouTube"><img src="images/icons/youtube.png" /></a>
			  '.$MovieDeleteLink.'
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
	echo '<div class="notification warning">Unable to connect to XBMC</div>';
}
?>