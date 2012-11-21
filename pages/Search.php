<div class="head">Search &raquo; Torrents</div>

<?php
$Torrents = json_decode($Hub->Request('/rss/search/'.$_GET['Search']));

if(is_object($Torrents) && property_exists($Torrents, 'error')) {
	echo '<div class="notification information">'.$Torrents->error->message.'</div>'."\n";
}
else {
	echo '
	<table width="100%">
	 <thead>
	 <tr>
	  <th style="width: 55px">Since</th>
	  <th>Title</th>
	  <th style="width: 100px">Feed</th>
	  <th style="width: 100px">Category</th>
	  <th style="width: 16px">Quality</th>
	  <th style="width: 54px">&nbsp;</th>
	 </tr>
	 </thead>'."\n";

	foreach($Torrents AS $Torrent) {
		switch($Torrent->Quality) {
			case 0: $Quality = '<img src="images/icons/download_low_quality.png" />'; break;
			case 1: $Quality = '<img src="images/icons/download.png" />'; break;
			case 2: $Quality = '<img src="images/icons/download_multiple.png" />'; break;
		}
		
		echo '
		<tr>
		 <td>'.date('d.m.y', $Torrent->Date).'</td>
		 <td>'.$Torrent->Title.'</td>
		 <td>'.$Torrent->Feed.'</td>
		 <td>'.$Torrent->Category.'</td>
		 <td>'.$Quality.'</td>
		 <td style="text-align: right">
		  <a id="TorrentDownload-'.$Torrent->ID.'" class="button positive"><span class="inner"><span class="label" style="min-width:50px;" nowrap="">Download</span></span></a>
		 </td>
		</tr>'."\n";
	}
	
	echo '</table>'."\n";
}
?>

<br />

<div class="head">Search &raquo; TheTVDB</div>

<?php
$Series = json_decode($Hub->Request('/series/search/'.$_GET['Search']));

if(is_object($Series) && property_exists($Series, 'error')) {
	echo '<div class="notification information">'.$Series->error->message.'</div>'."\n";
}
else {
	echo '
	<table width="100%">
	 <thead>
	 <tr>
	  <th style="width: 65px">First Aired</th>
	  <th style="width: 150px">Title</th>
	  <th>Plot</th>
	  <th style="width: 54px">&nbsp;</th>
	 </tr>
	 </thead>'."\n";

	foreach($Series->Series AS $Serie) {
		$Plot       = property_exists($Serie, 'Overview') ? ShortText($Serie->Overview, 90) : 'NA';
		$FirstAired = property_exists($Serie, 'FirstAired') ? date('d.m.y', strtotime($Serie->FirstAired)) : 'NA';
		$Title      = property_exists($Serie, 'SeriesName') ? $Serie->SeriesName : 'NA';
		
		if(property_exists($Serie, 'id')) {
			echo '
			<tr>
			 <td>'.$FirstAired.'</td>
			 <td>'.$Title.'</td>
			 <td>'.$Plot.'</td>
			 <td style="text-align: right">
			  <a id="SerieAdd-'.$Serie->id.'" class="button positive"><span class="inner"><span class="label" style="min-width:50px;" nowrap="">Add</span></span></a>
			 </td>
			</tr>'."\n";
		}
	}
	
	echo '</table>'."\n";
}
?>