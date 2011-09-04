<script type="text/javascript">
$('a[id|="DownloadTorrent"]').click(function(event) {
	AjaxLink(this);
});
</script>
<?php
if(filter_has_var(INPUT_GET, 'File') && !empty($_GET['File'])) {
	if(filter_has_var(INPUT_GET, 'EpisodeID') && !empty($_GET['EpisodeID'])) {
		$RSSTorrents = $RSSObj->SearchTitle($_GET['File']);

		if(sizeof($RSSTorrents)) {
			echo '<table class="nostyle" style="width: 100%">'."\n";
			foreach($RSSTorrents AS $Torrent) {
				echo '
				<tr>
				 <td style="width: 20px">
				  <a id="DownloadTorrent-'.$_GET['EpisodeID'].'-'.$Torrent['TorrentID'].'"><img src="images/icons/download.png" /></a>
				 </td>
				 <td>'.$Torrent['TorrentTitle'].'</td>
				</tr>'."\n";
			}
			echo '</table>'."\n";
		}
	}
}
else {
	echo 'Something is not right…';
}
?>