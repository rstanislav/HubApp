<div class="head-control">
 <a id="MovieCoverCache" class="button positive"><span class="inner"><span class="label" nowrap="">Cache Covers</span></span></a>
 <a id="SharedMoviesUpdate" class="button positive"><span class="inner"><span class="label" nowrap="">Update Shared Movies</span></span></a>
 <a id="MovieTogglePath" class="button positive"><span class="inner"><span class="label" nowrap="">Toggle File Path</span></span></a>
</div>

<div class="head">Recently added movies</div>

<?php
$RecentMovies = json_decode($Hub->Request('/xbmc/movies/recent'));

if(is_object($RecentMovies) && is_object($RecentMovies->error)) {
	echo '<div class="notification information">'.$RecentMovies->error->message.'</div>'."\n";
}
else {
	echo '
	<table class="nostyle">
	 <thead>
	  <tr>'."\n";
	 
	$i = 1;
	foreach($RecentMovies AS $Movie) {
		$Watched = ($Movie->playcount) ? '<div class="cover-watched">watched</div>' : '';
		
		$MoviePoster = '
		 <div id="Cover-'.$Movie->movieid.'" class="cover">
		  <img class="poster" width="150" height="250" src="'.$Movie->postersmall.'" />
		  '.$Watched.'
		  <div id="CoverControl-'.$Movie->movieid.'" class="cover-control">
		   <a id="" class="cover-link"><img src="images/icons/control_play.png" /></a>
		   <a id="" class="cover-link"><img src="images/icons/information.png" /></a>
		   <a id="" class="cover-link"><img src="images/icons/youtube.png" /></a>
		  </div>
		 </div>';
		 
		echo '
		<td style="text-align: center; width:33%;">
		 <div style="width: 151px; height: 250px; margin: 0 auto;">'.$MoviePoster.'</div><br />
		 <strong>'.$Movie->label.' ('.$Movie->year.')</strong>
		 <br /><br />
		</td>'."\n";
		
		if($i++ % 3 == 0) {
			echo '
			</tr>
			<tr>'."\n";
		}
	}
	
	echo '
	<table>'."\n";
}
?>

<div class="head">All movies</div>

<?php
$Movies = json_decode($Hub->Request('/xbmc/movies'));

echo '
<table>
 <thead>
  <tr>
   <th style="width: 16px">&nbsp;</th>
   <th style="width: 300px">Title</th>
   <th style="width: 30px">Year</th>
   <th>File</th>
   <th style="width: 73px">&nbsp;</th>
  </tr>
 </thead>'."\n";
 
if(is_object($Movies) && property_exists('error', $Movies)) {
	echo '
	<tr>
	 <td colspan="5">'.$Movies->error->message.'</td>
	</tr>'."\n";
}
else {
	foreach($Movies AS $Movie) {
		$WatchedIcon = ($Movie->playcount) ? '<img src="images/icons/watched.png" />' : '';
		
		echo '
		<tr>
		 <td>'.$WatchedIcon.'</td>
		 <td>'.$Movie->label.'</td>
		 <td>'.$Movie->year.'</td>
		 <td>'.ConcatFilePath($Movie->file).'</td>
		 <td>
		  <a id="FilePlay-'.$Movie->file.'" rel="ajax"><img src="images/icons/control_play.png" /></a>
		  <a id="MovieInformation-'.$Movie->movieid.'" rel="ajax"><img src="images/icons/information.png" /></a>
		  <img src="images/icons/youtube.png" />
		  <a id="FileDelete-'.$Movie->file.'" rel="ajax"><img src="images/icons/delete.png" /></a>
		 </td>
		</tr>'."\n";
	}
}

echo '
</table>'."\n";
?>