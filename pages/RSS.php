<div class="head">RSS</div>

<?php
$Entries = json_decode($Hub->Request('/rss/'.$_GET['ID']));

if(is_object($Entries) && property_exists($Entries, 'error')) {
	echo '<div class="notification information">'.$Entries->error->message.'</div>'."\n";
}
else {
	echo '
	<table width="100%">
	 <thead>
	 <tr>
	  <th style="width: 85px">Published</th>
	  <th>Title</th>
	  <th>Category</th>
	  <th style="width: 54px">&nbsp;</th>
	 </tr>
	 </thead>'."\n";

	foreach($Entries AS $Entry) {
		echo '
		<tr>
		 <td>'.date('d.m.y H:i', $Entry->PubDate).'</td>
		 <td>'.$Entry->Title.'</td>
		 <td>
		  <a href="'.urlencode($Entry->Category).'">'.$Entry->Category.'</a>
		 </td>
		 <td style="text-align: right">
		  <img src="images/icons/imdb.png" />
		  <img src="images/icons/heart_add.png" />
		  <a id="TorrentDownload-'.$Entry->ID.'" rel="ajax"><img src="images/icons/download.png" /></a>
		 </td>
		</tr>'."\n";
	}
	
	echo '</table>'."\n";
}
?>