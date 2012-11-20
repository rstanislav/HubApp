<div class="head-control">
 <a id="TorrentStartAll" class="button positive"><span class="inner"><span class="label" nowrap="">Start All</span></span></a>
 <a id="TorrentPauseAll" class="button positive"><span class="inner"><span class="label" nowrap="">Pause All</span></span></a>
 <a id="TorrentStopAll" class="button negative"><span class="inner"><span class="label" nowrap="">Stop All</span></span></a>
 <a id="TorrentRemoveFinished" class="button negative"><span class="inner"><span class="label" nowrap="">Remove All Finished</span></span></a>
</div>

<div class="head">uTorrent Control Panel</div>

<?php
$Torrents = json_decode($Hub->Request('/utorrent'));

if(is_object($Torrents) && is_object($Torrents->error)) {
	echo '<div class="notification information">'.$Torrents->error->message.'</div>'."\n";
}
else {
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
		$TotalSize       += $Torrent->SizeInBytes;
		$TotalDownloaded += $Torrent->DownloadedInBytes;
		$TotalSpeed      += $Torrent->DownSpeedInBytes;
		
		$TCPause   = '<a id="TorrentPause-'.$Torrent->Hash.'" rel="ajax"><img src="images/icons/control_pause.png" /></a>';
		$TCStop    = '<a id="TorrentStop-'.$Torrent->Hash.'" rel="ajax"><img src="images/icons/control_stop.png" /></a>';
		$TCStart   = '<a id="TorrentStart-'.$Torrent->Hash.'" rel="ajax"><img src="images/icons/control_play.png" /></a>';
		$TCDelete  = '<a id="TorrentDelete-'.$Torrent->Hash.'" rel="ajax"><img src="images/icons/delete.png" /></a>';
		$TCDelete .= '<a id="TorrentDeleteData-'.$Torrent->Hash.'" rel="ajax"><img src="images/icons/delete_plus.png" /></a>';
	
		$TorrentControls = $TCDelete;
	
		if($Torrent->StatusCode == 128) {
			$TorrentStatus = 'Stopped';
			$TorrentControls = $TCStart.$TCDelete;
		}
		if($Torrent->StatusCode & 2) {
			$TorrentStatus = 'Checking';
			$TorrentControls = $TCStop.$TCPause.$TCDelete;
		}
		if($Torrent->StatusCode & 16) {
			$TorrentStatus = 'Error';
		}
		if($Torrent->StatusCode & 32) {
			$TorrentStatus = 'Paused (F)';
			$TorrentControls = $TCStop.$TCStart.$TCDelete;
		}
	
		if($Torrent->StatusCode == 233) {
			$TorrentStatus   = 'Paused';
			$TorrentControls = $TCStart.$TCDelete;
		}
		if($Torrent->StatusCode == 136) {
			$TorrentStatus   = ($Torrent->Progress == 1000) ? 'Finished'    : 'Stopped';
			$TorrentControls = $TCStart.$TCDelete;
		}
		if($Torrent->StatusCode == 137) {
			$TorrentStatus   = ($Torrent->Progress == 1000) ? 'Seeding (F)' : 'Downloading (F)';
			$TorrentControls = $TCStop.$TCPause.$TCDelete; 
		}
		if($Torrent->StatusCode == 200) {
			$TorrentStatus   = ($Torrent->Progress == 1000) ? 'Queued Seed' : 'Queued';
			$TorrentControls = $TCStop.$TCPause.$TCDelete;
		}
		if($Torrent->StatusCode == 201) {
			$TorrentStatus   = ($Torrent->Progress == 1000) ? 'Seeding'     : 'Downloading';
			$TorrentControls = $TCStop.$TCPause.$TCDelete;
		}
		
		echo '
		<tr>
		 <td>'.$Torrent->Name.'</td>
		 <td>'.$Torrent->Status.'</td>
		 <td>'.$Torrent->Size.'</td>
		 <td>'.$Torrent->Downloaded.'</td>
		 <td>'.$Torrent->Progress.'</td>
		 <td>'.$Torrent->DownSpeed.'</td>
		 <td>'.$Torrent->TimeRemaining.'</td>
		 <td style="text-align: right">
		  '.$TorrentControls.'
		 </td>
		</tr>'."\n";
	}
	
	$TotalTimeRemaining = ($TotalSpeed > (20 * 1024)) ? ConvertSeconds(($TotalSize - $TotalDownloaded) / $TotalSpeed) : '∞';
	echo '
	 <tfoot>
	 <tr>
	  <th></th>
	  <th></th>
	  <th>'.BytesToHuman($TotalSize).'</th>
	  <th>'.BytesToHuman($TotalDownloaded).'</th>
	  <th></th>
	  <th>'.BytesToHuman($TotalSpeed).'/s</th>
	  <th>'.$TotalTimeRemaining.'</th>
	  <th></th>
	 </tr>
	 </tfoot>
	</table>'."\n";
}
?>