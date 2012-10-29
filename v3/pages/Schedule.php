<div class="head">Recent episodes</div>

<?php
$RecentEpisodes = json_decode($Hub->Request('/series/recent/3'));

echo '
<table>
 <thead>
  <tr>
   <th>&nbsp;</th>
   <th>Serie</th>
   <th>Episode</th>
   <th>Title</th>
   <th>Time Since</th>
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
		echo '
		<tr>
		 <td>&nbsp;</td>
		 <td><a href="?Page=Series&ID='.$Episode->ID.'">'.$Episode->Title.'</a></td>
		 <td>'.$Episode->Season.'x'.$Episode->Episode.'</td>
		 <td>'.$Episode->EpisodeTitle.'</td>
		 <td>'.$Episode->AirDate.'</td>
		</tr>'."\n";
	}
}

echo '
</table><br />'."\n";
?>

<div class="head">Upcoming episodes</div>

<?php
$UpcomingEpisodes = json_decode($Hub->Request('/series/upcoming'));

echo '
<table>
 <thead>
  <tr>
   <th>&nbsp;</th>
   <th>Serie</th>
   <th>Episode</th>
   <th>Title</th>
   <th>Time Since</th>
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
		echo '
		<tr>
		 <td>&nbsp;</td>
		 <td><a href="?Page=Series&ID='.$Episode->ID.'">'.$Episode->Title.'</a></td>
		 <td>'.$Episode->Season.'x'.$Episode->Episode.'</td>
		 <td>'.$Episode->EpisodeTitle.'</td>
		 <td>'.$Episode->AirDate.'</td>
		</tr>'."\n";
	}
}

echo '
</table>'."\n";
?>