<?php
if(filter_has_var(INPUT_GET, 'ID')) {
	include_once APP_PATH.'/pages/SeriesDetailed.php';
}
else {
?>

<div class="head-control">
 <a id="SerieRefreshAll" class="button positive"><span class="inner"><span class="label" nowrap="">Refresh Series</span></span></a>
 <a id="EpisodesRebuild" class="button positive"><span class="inner"><span class="label" nowrap="">Rebuild Episodes</span></span></a>
 <a id="FoldersRebuild" class="button positive"><span class="inner"><span class="label" nowrap="">Rebuild Folders</span></span></a>
</div>

<div class="head">Series</div>
<?php
$Series = json_decode($Hub->Request('/series'));

echo '
<table>
 <thead>
  <tr>
   <th style="width: 50px">Since</th>
   <th>Title</th>
   <th style="width: 65px">First Aired</th>
   <th style="width: 120px">Schedule</th>
   <th style="width: 100px">Network</th>
   <th style="width: 70px">Status</th>
   <th style="width: 54px">&nbsp;</th>
  </tr>
 </thead>'."\n";
 
if(is_object($Series) && is_object($Series->error)) {
	echo '
	<tr>
	 <td colspan="7">'.$Series->error->message.'</td>
	</tr>'."\n";
}
else {
	foreach($Series AS $Serie) {
		$TitleAlt = (!is_null($Serie->TitleAlt)) ? '/'.$Serie->TitleAlt : '';
			
		echo '
		<tr>
		 <td>'.date('d.m.y', $Serie->Date).'</td>
		 <td><a href="?Page=Series&ID='.$Serie->ID.'">'.$Serie->Title.$TitleAlt.'</a> ('.$Serie->EpisodeCount.' episodes)</td>
		 <td>'.date('d.m.y', $Serie->FirstAired).'</td>
		 <td>'.$Serie->AirDay.' '.$Serie->AirTime.'</td>
		 <td>'.$Serie->Network.'</td>
		 <td>'.$Serie->Status.'</td>
		 <td>
		  <a id="SerieRefresh-'.$Serie->ID.'" rel="ajax"><img src="images/icons/refresh.png" /></a>
		  <img src="images/icons/spelling.png" />
		  <a id="SerieDelete-'.$Serie->ID.'" rel="ajax"><img src="images/icons/delete.png" /></a>
		 </td>
		</tr>'."\n";
	}
}

echo '
</table>'."\n";
?>
<?php
}
?>