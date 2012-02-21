<div class="head-control">
 <a id="MovieTogglePath-0" class="button positive"><span class="inner"><span class="label" nowrap="">Toggle File Path</span></span></a>
</div>

<div class="head">Recently added movies <small style="font-size: 12px;">(<a href="#!/Help/Movies">?</a>)</small></div>

<?php
$XBMCObj->Connect();

if(is_object($XBMCObj->XBMCRPC)) {
    $RecentMovies = $XBMCObj->GetRecentlyAddedMovies();
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
			
			$Files = $HubObj->ConcatFilePath($Movie['file']);
			
			$FilePath = '';
			if(is_array($Files)) {
				$FilePath = implode('<br />', $Files);
			}
			else {
				$FilePath = $Files;
			}
			
			$MoviePlayLink  = ($UserObj->CheckPermission($UserObj->UserGroupID, 'XBMCPlay')) ? '<a id="MoviePlay-'.$Movie['movieid'].'" class="cover-link"><img src="images/icons/control_play.png" /></a>' : '';

			if(!empty($Movie['trailer'])) {
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
			
			$Watched = ($Movie['playcount']) ? '<div class="cover-watched">watched</div>' : '';
			
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
			
			$PathShow = (filter_has_var(INPUT_COOKIE, 'MoviePath')) ? ' style="'.$_COOKIE['MoviePath'].'"' : ' style="display: inline;"';
			echo '
			<td style="text-align: center; width:33%;">
			 <div style="width: 151px; height: 250px; margin: 0 auto;">'.$MoviePoster.'</div><br />
			 <strong>'.$Movie['label'].' ('.$Movie['year'].')</strong>
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
			$Movies[$Title][] = $Movie;
		}
		
		ksort($Movies);
		
		echo '
		<table width="100%">
		 <thead>
		 <tr>
		  <th>Title</th>
		  <th style="width:40px; text-align:center">Year</th>
		  <th>File</th>
		  <th style="width:74px">&nbsp;</th>
		 </tr>
		 </thead>'."\n";
		
		foreach($Movies AS $Movie) {
			$MoviePlayLink   = ($UserObj->CheckPermission($UserObj->UserGroupID, 'XBMCPlay'))             ? '<a id="MoviePlay-'.$Movie[0]['movieid'].'"><img src="images/icons/control_play.png" /></a>' : '';
			$MovieInfoLink   = ($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewMovieInformation')) ? '<a id="MovieInfo-'.$Movie[0]['movieid'].'"><img src="images/icons/information.png" /></a>'  : '';
			$MovieDeleteLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'MovieDelete'))          ? '<a id="MovieDelete-'.$Movie[0]['movieid'].'"><img src="images/icons/delete.png" /></a>'    : '';
			
			$WatchedIcon = ($Movie[0]['playcount']) ? '<img style="vertical-align:text-bottom;" src="images/icons/watched.png" /> ' : '';
			
			$MovieID        = trim($Movie[0]['movieid']);
			$MovieLabel     = trim($Movie[0]['label']);
			$MovieFile      = trim($Movie[0]['file']);
			$MovieYear      = (array_key_exists('year', $Movie[0]))      ? trim($Movie[0]['year'])      : '';
			$MovieThumbnail = (array_key_exists('thumbnail', $Movie[0])) ? trim($Movie[0]['thumbnail']) : '';
			$MovieFanart    = (array_key_exists('fanart', $Movie[0]))    ? trim($Movie[0]['fanart'])    : '';
			$MovieGenre     = (array_key_exists('genre', $Movie[0]))     ? trim($Movie[0]['genre'])     : '';
			$MovieFile      = (array_key_exists('file', $Movie[0]))      ? $HubObj->ConcatFilePath(trim($Movie[0]['file']))      : '';
			
			if(array_key_exists('trailer', $Movie[0])) {
				if(strstr($Movie[0]['trailer'], 'plugin.video.youtube')) {
					$MovieTrailerLink = '<a href="http://youtube.com/watch?v='.str_replace('plugin://plugin.video.youtube/?action=play_video&videoid=', '', $Movie[0]['trailer']).'" rel="trailer" title="'.$MovieLabel.' ('.$MovieYear.') Trailer"><img  src="images/icons/youtube.png" /></a>';
				}
				else if(strstr($Movie[0]['trailer'], 'http://playlist.yahoo.com')) {
					$MovieTrailerLink = '<a href="'.$Movie[0]['trailer'].'" rel="trailer" title="'.$MovieLabel.' ('.$MovieYear.') Trailer"><img  src="images/icons/yahoo.png" /></a>';
				}
				else {
					$MovieTrailerLink = '<a href="http://youtube.com/results?search_query='.urlencode($MovieLabel.' '.$MovieYear.' trailer').'" target="_blank" title="Search for trailer on YouTube"><img  src="images/icons/youtube.png" /></a>';
				}
			}
			else {
				$MovieTrailerLink = '<a href="http://youtube.com/results?search_query='.urlencode($MovieLabel.' '.$MovieYear.' trailer').'" target="_blank" title="Search for trailer on YouTube"><img  src="images/icons/youtube.png" /></a>';
			}
			
			if(strlen($MovieLabel)) {
				echo '
				<tr>
				 <td>'.$WatchedIcon.''.$MovieLabel.'</td>
				 <td style="text-align:center">'.$MovieYear.'</td>
				 <td>'.$MovieFile.'</td>
				 <td style="text-align: right">
				  '.$MoviePlayLink.'
				  '.$MovieInfoLink.'
				  '.$MovieTrailerLink.'
				  '.$MovieDeleteLink.'
				 </td>
				</tr>'."\n";
			}
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