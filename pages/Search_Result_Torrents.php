<?php
$Torrents = $RSSObj->SearchTitle($Search);

if(is_array($Torrents)) {
	echo '
	<table>
	 <tr>
	  <th>Title</th>
	  <th>Date</th>
	  <th>Category</th>
	  <th>&nbsp;</th>
	 </tr>'."\n";
	
	foreach($Torrents AS $Torrent) {
		echo '
		<tr>
		 <td>'.$Torrent['TorrentTitle'].'</td>
		 <td>'.date('d.m.y H:i', $Torrent['TorrentDate']).'</td>
		 <td>'.$Torrent['TorrentCategory'].'</td>
		 <td style="text-align: right">
		  <a id="TorrentDownload-'.$Torrent['TorrentID'].'" onclick="AjaxButton(this);" class="button positive"><span class="inner"><span class="label" style="min-width:50px;" nowrap="">Download</span></span></a>
		 </td>
		</tr>'."\n";
	}
	echo '
	</table>'."\n";
}
else {
	echo '<div class="notification">No results</div>';
}
?>