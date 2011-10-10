<div class="head-control">
<?php
if($UserObj->CheckPermission($UserObj->UserGroupID, 'SerieRefreshAll')) {
	echo '<a id="SerieRefreshAll-0" class="button positive"><span class="inner"><span class="label" nowrap="">Refresh Series</span></span></a>'."\n";
}
if($UserObj->CheckPermission($UserObj->UserGroupID, 'SerieRebuild')) {
	echo '<a id="EpisodesRebuild-0" class="button positive"><span class="inner"><span class="label" nowrap="">Rebuild Episodes</span></span></a>'."\n";
}
if($UserObj->CheckPermission($UserObj->UserGroupID, 'SerieRebuildFolders')) {
	echo '<a id="FoldersRebuild-0" class="button positive"><span class="inner"><span class="label" nowrap="">Rebuild Folders</span></span></a>'."\n";
}
?>
</div>

<div class="head">Series <small style="font-size: 12px;">(<a href="#!/Help/Series">?</a>)</small></div>

<?php
$Series = $SeriesObj->GetSeries();

if(is_array($Series)) {
?>
<table>
 <thead>
 <tr>
  <th style="text-align:center; width:50px">Since</th>
  <th>Title</th>
  <th style="text-align:center; width:80px">First Aired</th>
  <th>Schedule</th>
  <th style="width:100px">Network</th>
  <th style="width:100px">Status</th>
  <th style="width: 54px">&nbsp;</th>
 </tr>
 </thead>
 
<?php
foreach($Series AS $Serie) {
	$Serie['SerieTitleAlt'] = (strlen($Serie['SerieTitleAlt'])) ? '/'.$Serie['SerieTitleAlt'] : '';
	$Serie['FirstAired']    = ($Serie['SerieFirstAired']) ? date('d.m.y', $Serie['SerieFirstAired']) : '';
	
	$SerieRefreshLink  = ($UserObj->CheckPermission($UserObj->UserGroupID, 'SerieRefresh'))     ? '<a id="SerieRefresh-'.$Serie['SerieID'].'"><img src="images/icons/refresh.png" /></a>'   : '';
	$SerieSpellingLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'SerieAddSpelling')) ? '<a id="SerieSpelling-'.$Serie['SerieID'].'" rel="'.$Serie['SerieTitle'].'"><img src="images/icons/spelling.png" /></a>' : '';
	$SerieDeleteLink   = ($UserObj->CheckPermission($UserObj->UserGroupID, 'SerieDelete'))      ? '<a id="SerieDelete-'.$Serie['SerieID'].'" rel="'.$Serie['SerieTitle'].'"><img src="images/icons/delete.png" /></a>'     : '';
	
	echo '
	<tr id="Serie-'.$Serie['SerieID'].'">
	 <td style="text-align:center;">'.date('d.m.y', $Serie['SerieDate']).'</td>
	 <td><a href="#!/Series/'.urlencode($Serie['SerieTitle']).'">'.$Serie['SerieTitle'].$Serie['SerieTitleAlt'].'</a> ('.$SeriesObj->GetEpisodeCount($Serie['SerieID']).' episodes)</td>
	 <td style="text-align:center;">'.$Serie['FirstAired'].'</td>
	 <td>'.$Serie['SerieAirDay'].' '.$Serie['SerieAirTime'].'</td>
	 <td>'.$Serie['SerieNetwork'].'</td>
	 <td>'.$Serie['SerieStatus'].'</td>
	 <td style="text-align:center">
	  '.$SerieRefreshLink.'
	  '.$SerieSpellingLink.'
	  '.$SerieDeleteLink.'
	 </td>
	</tr>'."\n";
}
?>
</table>
<?php
}
else {
	echo '<div class="notification information">No data available</div>';
}
?>