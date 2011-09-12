<div class="head-control">
 <a id="MovieToggleGenre-0" class="button positive"><span class="inner"><span class="label" nowrap="">Toggle Genre</span></span></a>
 <a id="MovieTogglePath-0" class="button positive"><span class="inner"><span class="label" nowrap="">Toggle File Path</span></span></a>
</div>

<div class="head">Recently added movies <small style="font-size: 12px;">(<a href="#!/Help/Movies">?</a>)</small></div>

<?php
$XBMCObj->Connect();

if(is_object($XBMCObj->XBMCRPC)) {
	$RecentMovies = $XBMCObj->GetRecentlyAddedMovies();
	
	//$XBMCObj->d($XBMCObj->GetCommands($HubObj->XBMCRPC));
	
	if(is_array($RecentMovies)) {
		$i = 1;
		echo '
		<table width="100%" class="nostyle">
		 <tr>'."\n";
		foreach($RecentMovies['movies'] AS $Movie) {
			$Thumbnail = (array_key_exists('thumbnail', $Movie)) ? $XBMCObj->GetImage($Movie['thumbnail']) : '';
			$Genre     = (array_key_exists('genre', $Movie))     ? $Movie['genre']                         : '';
			$Files      = $HubObj->ConcatFilePath($Movie['file']);
			
			$FilePath = '';
			if(is_array($Files)) {
				$FilePath = implode('<br />', $Files);
			}
			else {
				$FilePath = $Files;
			}
			
			$MoviePoster = ($UserObj->CheckPermission($UserObj->UserGroupID, 'XBMCPlay')) ? '<a id="MoviePlay-'.$Movie['movieid'].'"><img class="poster" width="150" height="250" src="'.$Thumbnail.'" /></a>' : '<img class="poster" width="150" height="250" src="'.$Thumbnail.'" />';
			
			$GenreShow = (filter_has_var(INPUT_COOKIE, 'MovieGenre')) ? ' style="'.$_COOKIE['MovieGenre'].'"' : ' style="display: inline;"';
			$PathShow = (filter_has_var(INPUT_COOKIE, 'MoviePath')) ? ' style="'.$_COOKIE['MoviePath'].'"' : ' style="display: inline;"';
			
			echo '
			<td style="text-align: center">
			 '.$MoviePoster.'<br />
			 <strong>'.$Movie['label'].' ('.$Movie['year'].')</strong>
			 <span class="MovieGenre"'.$GenreShow.'><br /><em>'.$Genre.'</em></span>
			 <span class="MoviePath"'.$PathShow.'><br /><small>'.$FilePath.'</small></span>
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
	<br /><br />
	
	<div class="head">All Movies <small style="font-size: 12px;">(<a href="#!/Help/Movies">?</a>)</small></div>
	<?php
	$AllMovies = $XBMCObj->GetMovies();
	
	if(is_array($AllMovies)) {
		$Movies = array();
		foreach($AllMovies['movies'] AS $Movie) {
			$Title = trim(str_replace('The ', '', trim($Movie['label'])));
			$Movies[$Title]['id']        = trim($Movie['movieid']);
			$Movies[$Title]['label']     = trim($Movie['label']);
			$Movies[$Title]['file']      = trim($Movie['file']);
			$Movies[$Title]['genre']     = (!isset($Movie['genre']))     ? '' : trim($Movie['genre']);
			$Movies[$Title]['year']      = (!isset($Movie['year']))      ? '' : trim($Movie['year']);
			$Movies[$Title]['thumbnail'] = (!isset($Movie['thumbnail'])) ? '' : trim($Movie['thumbnail']);
			$Movies[$Title]['fanart']    = (!isset($Movie['fanart']))    ? '' : trim($Movie['fanart']);
		}
		
		ksort($Movies);
		
		echo '
		<table width="100%">
		 <thead>
		 <tr>
		  <th>Title</th>
		  <th>Year</th>
		  <th>Genre</th>
		  <th style="width:54px">&nbsp;</th>
		 </tr>
		 </thead>'."\n";
		 
		foreach($Movies AS $Movie) {
			$MoviePlayLink   = ($UserObj->CheckPermission($UserObj->UserGroupID, 'XBMCPlay'))             ? '<a id="MoviePlay-'.$Movie['id'].'"><img src="images/icons/control_play.png" /></a>' : '';
			$MovieInfoLink   = ($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewMovieInformation')) ? '<a id="MovieInfo-'.$Movie['id'].'"><img src="images/icons/information.png" /></a>'  : '';
			$MovieDeleteLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'MovieDelete'))          ? '<a id="MovieDelete-'.$Movie['id'].'"><img src="images/icons/delete.png" /></a>'    : '';
			
			echo '
			<tr>
			 <td>'.$Movie['label'].'</td>
			 <td>'.$Movie['year'].'</td>
			 <td>'.$Movie['genre'].'</td>
			 <td style="text-align: right">
			  '.$MoviePlayLink.'
			  '.$MovieInfoLink.'
			  '.$MovieDeleteLink.'
			 </td>
			</tr>'."\n";
		}
		echo '</table>'."\n";
	}
	else {
		echo '<div class="notification">No data available</div>';
	}
}
else {
	echo '<div class="notification">Unable to connect to XBMC</div>';
}
?>