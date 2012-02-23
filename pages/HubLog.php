<div class="head">Hub Log <small style="font-size: 12px;">(<a href="#!/Help/HubLog">?</a>)</small></div>

<?php
$Logs = $HubObj->GetLogs();

if(is_array($Logs) && sizeof($Logs)) {
	echo '
	<table class="text-select">
	 <thead>
	 <tr>
	  <th width="70">Date</th>
	  <th width="150">Event</th>
	  <th>Text</th>
	 </tr>
	 </thead>'."\n";
	
	for($i = 0; $i < sizeof($Logs); $i++) {
		if($Logs[$i]['LogEvent'] == 'Schedule/RSS') {
			preg_match('/(Added) ([0-9]+) (torrents spread across) ([0-9]+) (RSS feeds)/', $Logs[$i]['LogText'], $LogPrevious);
			
			$TorrentsAdded = $LogPrevious[2];
			$RSSFeeds      = $LogPrevious[4];
			for($k = ($i + 1); $k < sizeof($Logs); $k++) {
				if($Logs[$k]['LogEvent'] == 'Schedule/RSS') {
					preg_match('/(Added) ([0-9]+) (torrents spread across) ([0-9]+) (RSS feeds)/', $Logs[$k]['LogText'], $LogNew);
				
					$ToDate         = $Logs[$i]['LogDate'];
					$TorrentsAdded += $LogNew[2];
					$RSSFeeds       = ($LogNew[4] > $RSSFeeds) ? $LogNew[4] : $RSSFeeds;
					continue;
				}
				else {
					$i = ($k - 1);
					break;
				}
			}
		}
		else {
			$TorrentsAdded = 0;
			$ToDate = 0;
		}
		
		if($TorrentsAdded) {
			$Logs[$i]['LogText'] = 'Added '.$TorrentsAdded.' torrents spread across '.$RSSFeeds.' RSS feeds';
		}
		
		$LogDateTo = !empty($ToDate) ? date('-d.m H:i', $ToDate) : '';
		
		echo '
		<tr>
		 <td>'.date('d.m H:i', $Logs[$i]['LogDate']).$LogDateTo.'</td>
		 <td>'.$Logs[$i]['LogEvent'].'</td>
		 <td>'.$Logs[$i]['LogText'].'</td>
		</tr>'."\n";
	}
	echo '</table>'."\n";
}
else {
	echo '<div class="notification information">No data available</div>';
}
?>