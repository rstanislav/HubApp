<?php
$Movie = $XBMCObj->GetMovieDetails($_GET['MovieID']);

if(is_array($Movie)) {
	$Movie = $Movie['moviedetails'];
	
	if(array_key_exists('movieid', $Movie) && is_file(APP_PATH.'/posters/thumbnails/movie-'.$Movie['movieid'].'.jpg')) {
		$Thumbnail = 'posters/thumbnails/movie-'.$Movie['movieid'].'.jpg';
	}
	else {
		$Thumbnail = 'images/poster-unavailable.png';
	}
	
	$Tagline  = !empty($Movie['tagline']) ? $Movie['tagline'] : 'NA';
	
	if($Movie['label'] == $Movie['originaltitle']) {
		$Title = $Movie['label'];
	}
	else {
		if($Movie['originaltitle']) {
			$Title = $Movie['label'].' ('.$Movie['originaltitle'].')';
		}
		else {
			$Title = $Movie['label'];
		}
	}
	
	$Country  = !empty($Movie['country'])                         ? $Movie['country']                                  : 'NA';
	$Year     = ($Movie['year'])                                  ? ' ('.$Movie['year'].')'                            : '';
	$Studio   = ($Movie['studio'])                                ? $Movie['studio']                                   : 'NA';
	$MPAA     = ($Movie['mpaa'])                                  ? $Movie['mpaa']                                     : 'NA';
	$Plot     = ($Movie['plot'])                                  ? wordwrap(nl2br($Movie['plot']), 120, "<br />\n")   : 'NA';
	$File     = (is_array($Movie['file']))                        ? implode('<br />', $Movie['file'])                  : $Movie['file'];
	$File     = preg_replace('/[A-z0-9-]+\:[A-z0-9-]+@/', '', $File);
	$Top250   = ($Movie['top250'])                                ? ' <img src="images/navigation/star.png" />'        : '';
	$Channels = ($Movie['streamdetails']['audio'][0]['channels']) ? $Movie['streamdetails']['audio'][0]['channels']    : 'NA';
	$Codec    = ($Movie['streamdetails']['audio'][0]['codec'])    ? $Movie['streamdetails']['audio'][0]['codec']       : 'NA';
	
	echo '
	<div class="head">'.$Title.$Year.$Top250.'</div>
	
	<table class="nostyle">
	 <tr>
	  <td rowspan="11" style="text-align: center; width: 200px">
	   <img class="poster" src="'.$Thumbnail.'" />
	  </td>
	 </tr>
	 <tr>
	  <td style="width: 60px"><strong>Tagline:</strong></td>
	  <td>'.$Tagline.'</td>
	 </tr>
	 <tr>
	  <td><strong>Time:</strong></td>
	  <td>'.$Movie['runtime'].' minutes</td>
	 </tr>
	 <tr>
	  <td><strong>Genre:</strong></td>
	  <td>'.$Movie['genre'].'</td>
	 </tr>
	 <tr>
	  <td><strong>Audio:</strong></td>
	  <td>
	  '.$Channels.' channels /
	  '.$Codec.'
	  </td>
	 </tr>
	 <tr>
	  <td><strong>Rating:</strong></td>
	  <td>'.number_format($Movie['rating'], 2).' based on '.$Movie['votes'].' votes</td>
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
	  <td><strong>File:</strong></td>
	  <td>'.$HubObj->ConcatFilePath($File).'</td>
	 </tr>
	 <tr>
	  <td rowspan="3" colspan="2">&nbsp;</td>
	 </tr>
	</table>
	<br />
		
	<div class="head">Plot</div>
	'.$Plot."\n";
}
else {
	echo 'Unable to find information for this movie';
}
?>