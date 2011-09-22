<script type="text/javascript">
$('a[id|="TorrentPause"],a[id|="TorrentStop"],a[id|="TorrentStart"],a[id|="TorrentDelete"],a[id|="TorrentDeleteData"]').click(function(event) {
	AjaxLink(this);
});
</script>

<?php
$UTorrentObj->Connect();

if($UTorrentObj->UTorrentAPI->Token) {
	$Torrents = $UTorrentObj->GetTorrents();
	
	if(is_array($Torrents) && sizeof($Torrents)) {
		echo '
		<table>
		 <thead>
		 <tr>
		  <th>Name</th>
		  <th style="width: 50px">Status</th>
		  <th style="width: 70px">Size</th>
		  <th style="width: 70px">Downloaded</th>
		  <th style="width: 50px">Progress</th>
		  <th style="width: 70px">Speed</th>
		  <th style="width: 90px">Time remaining</th>
		  <th style="width: 64px">&nbsp;</th>
		 </tr>
		 </thead>'."\n";
	
		$TotalSize = $TotalDownloaded = $TotalSpeed = 0;
		foreach($Torrents AS $Torrent) {
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
		
			$TimeRemaining = ($Torrent[UTORRENT_TORRENT_DOWNSPEED] > (20 * 1024) && $TorrentStatus == 'Downloading') ? $HubObj->ConvertSeconds(($Torrent[UTORRENT_TORRENT_SIZE] - $Torrent[UTORRENT_TORRENT_DOWNLOADED]) / $Torrent[UTORRENT_TORRENT_DOWNSPEED]) : '∞';
			
			$TotalSize       += $Torrent[UTORRENT_TORRENT_SIZE];
			$TotalDownloaded += $Torrent[UTORRENT_TORRENT_DOWNLOADED];
			$TotalSpeed      += $Torrent[UTORRENT_TORRENT_DOWNSPEED];
			
			echo '
			<tr>
			 <td>'.$Torrent[UTORRENT_TORRENT_NAME].'</td>
			 <td>'.$TorrentStatus.'</td>
			 <td>'.$HubObj->BytesToHuman($Torrent[UTORRENT_TORRENT_SIZE]).'</td>
			 <td>'.$HubObj->BytesToHuman($Torrent[UTORRENT_TORRENT_DOWNLOADED]).'</td>
			 <td>'.($Torrent[UTORRENT_TORRENT_PROGRESS] / 10).'%</td>
			 <td>'.$HubObj->BytesToHuman($Torrent[UTORRENT_TORRENT_DOWNSPEED]).'/s</td>
			 <td>'.$TimeRemaining.'</td>
			 <td style="text-align: right">
			  '.$TorrentControls.'
			 </td>
			</tr>'."\n";
		}
		
		$TotalTimeRemaining = ($TotalSpeed > (20 * 1024)) ? $HubObj->ConvertSeconds(($TotalSize - $TotalDownloaded) / $TotalSpeed) : '∞';
		echo '
		 <tfoot>
		 <tr>
		  <th></th>
		  <th></th>
		  <th>'.$HubObj->BytesToHuman($TotalSize).'</th>
		  <th>'.$HubObj->BytesToHuman($TotalDownloaded).'</th>
		  <th></th>
		  <th>'.$HubObj->BytesToHuman($TotalSpeed).'/s</th>
		  <th>'.$TotalTimeRemaining.'</th>
		  <th></th>
		 </tr>
		 </tfoot>
		</table>'."\n";
	}
	else {
		echo '<div class="notification">No torrents loaded</div>';
	}
}
else {
	echo '<div class="notification">Unable to connect to uTorrent</div>';
}
?>