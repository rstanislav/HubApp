<script type="text/javascript">
$('a[id|="TorrentPause"],a[id|="TorrentStop"],a[id|="TorrentStart"],a[id|="TorrentDelete"],a[id|="TorrentDeleteData"]').click(function(event) {
	AjaxLink(this);
});
</script>

<?php
$UTorrentObj->Connect();
$Torrents = $UTorrentObj->GetTorrents();

if(is_array($Torrents) && sizeof($Torrents)) {
	echo '
	<table>
	 <thead>
	 <tr>
	  <th>Name</th>
	  <th style="width: 50px">Status</th>
	  <th style="width: 70px">Size</th>
	  <th style="width: 50px">Progress</th>
	  <th style="width: 70px">Speed</th>
	  <th style="width: 90px">Time remaining</th>
	  <th style="width: 64px">&nbsp;</th>
	 </tr>
	 </thead>'."\n";
	
	foreach($Torrents AS $Torrent) {
		$TimeRemaining = ($Torrent[UTORRENT_TORRENT_DOWNSPEED]) ? $HubObj->ConvertSeconds(($Torrent[UTORRENT_TORRENT_SIZE] - $Torrent[UTORRENT_TORRENT_DOWNLOADED]) / $Torrent[UTORRENT_TORRENT_DOWNSPEED]) : '∞';
		
		$TCPause   = ($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentPause')) ? '<a id="TorrentPause-'.$Torrent[UTORRENT_TORRENT_HASH].'" rel="'.$Torrent[UTORRENT_TORRENT_NAME].'"><img src="images/icons/control_pause.png" /></a>' : '';
		$TCStop    = ($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentStop')) ? '<a id="TorrentStop-'.$Torrent[UTORRENT_TORRENT_HASH].'" rel="'.$Torrent[UTORRENT_TORRENT_NAME].'"><img src="images/icons/control_stop.png" /></a>' : '';
		$TCStart   = ($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentStart')) ? '<a id="TorrentStart-'.$Torrent[UTORRENT_TORRENT_HASH].'" rel="'.$Torrent[UTORRENT_TORRENT_NAME].'"><img src="images/icons/control_play.png" /></a>' : '';
		$TCDelete  = ($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentDelete')) ? '<a id="TorrentDelete-'.$Torrent[UTORRENT_TORRENT_HASH].'" rel="'.$Torrent[UTORRENT_TORRENT_NAME].'"><img src="images/icons/delete.png" /></a>' : '';
		$TCDelete .= ($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentDeleteData')) ? '<a id="TorrentDeleteData-'.$Torrent[UTORRENT_TORRENT_HASH].'" rel="'.$Torrent[UTORRENT_TORRENT_NAME].'"><img src="images/icons/delete_plus.png" /></a>' : '';
		
		$TorrentControls = $TCDelete;
		
		if($Torrent[UTORRENT_TORRENT_STATUS] == 128) {
			$TorrentStatus = 'Stopped';
			$TorrentControls = $TCStart.$TCDelete;
		}
		if($Torrent[UTORRENT_TORRENT_STATUS] & 2) {
			$TorrentStatus = 'Checking';
			$TorrentControls = $TCStop.$TCPause.$TCDelete;
		}
		if($Torrent[UTORRENT_TORRENT_STATUS] & 16) {
			$TorrentStatus = 'Error';
		}
		if($Torrent[UTORRENT_TORRENT_STATUS] & 32) {
			$TorrentStatus = 'Paused (F)';
			$TorrentControls = $TCStop.$TCStart.$TCDelete;
		}
		
		if($Torrent[UTORRENT_TORRENT_STATUS] == 233) {
			$TorrentStatus   = 'Paused';
			$TorrentControls = $TCStart.$TCDelete;
		}
		if($Torrent[UTORRENT_TORRENT_STATUS] == 136) {
			$TorrentStatus   = ($Torrent[UTORRENT_TORRENT_PROGRESS] == 1000) ? 'Finished'    : 'Stopped';
			$TorrentControls = $TCStart.$TCDelete;
		}
		if($Torrent[UTORRENT_TORRENT_STATUS] == 137) {
			$TorrentStatus   = ($Torrent[UTORRENT_TORRENT_PROGRESS] == 1000) ? 'Seeding (F)' : 'Downloading (F)';
			$TorrentControls = $TCStop.$TCPause.$TCDelete; 
		}
		if($Torrent[UTORRENT_TORRENT_STATUS] == 200) {
			$TorrentStatus   = ($Torrent[UTORRENT_TORRENT_PROGRESS] == 1000) ? 'Queued Seed' : 'Queued';
			$TorrentControls = $TCStop.$TCPause.$TCDelete;
		}
		if($Torrent[UTORRENT_TORRENT_STATUS] == 201) {
			$TorrentStatus   = ($Torrent[UTORRENT_TORRENT_PROGRESS] == 1000) ? 'Seeding'     : 'Downloading';
			$TorrentControls = $TCStop.$TCPause.$TCDelete;
		}
		
		echo '
		<tr>
		 <td>'.$Torrent[UTORRENT_TORRENT_NAME].'</td>
		 <td>'.$TorrentStatus.'</td>
		 <td>'.$HubObj->BytesToHuman($Torrent[UTORRENT_TORRENT_SIZE]).'</td>
		 <td>'.($Torrent[UTORRENT_TORRENT_PROGRESS] / 10).'%</td>
		 <td>'.$HubObj->BytesToHuman($Torrent[UTORRENT_TORRENT_DOWNSPEED]).'/s</td>
		 <td>'.$TimeRemaining.'</td>
		 <td style="text-align: right">
		  '.$TorrentControls.'
		 </td>
		</tr>'."\n";
	}
	echo '</table>'."\n";
}
else {
	echo '<div class="notification">No torrents loaded</div>';
}
?>