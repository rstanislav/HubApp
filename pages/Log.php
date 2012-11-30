<div class="head">Hub Log</div>

<?php
$Logs = json_decode($Hub->Request('/log/'));

if(is_object($Logs) && is_object($Logs->error)) {
	echo '<div class="notification warning">'.$Logs->error->message.'</div>'."\n";
}
else {
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
		if($Logs[$i]->Event == 'RSS') {
			preg_match('/(Added) ([0-9]+) (torrents spread across) ([0-9]+) (RSS feeds)/', $Logs[$i]->Text, $LogPrevious);
			
			$TorrentsAdded = $LogPrevious[2];
			$RSSFeeds      = $LogPrevious[4];
			for($k = ($i + 1); $k < sizeof($Logs); $k++) {
				if($Logs[$k]->Event == 'RSS') {
					preg_match('/(Added) ([0-9]+) (torrents spread across) ([0-9]+) (RSS feeds)/', $Logs[$k]->Text, $LogNew);
				
					$ToDate         = $Logs[$i]->Date;
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
			$Logs[$i]->Text = 'Added '.$TorrentsAdded.' torrents spread across '.$RSSFeeds.' RSS feeds';
		}
		
		$LogDateTo = !empty($ToDate) ? date('-d.m H:i', $ToDate) : '';
		
		echo '
		<tr>
		 <td>'.date('d.m H:i', $Logs[$i]->Date).$LogDateTo.'</td>
		 <td>'.$Logs[$i]->Event.'</td>
		 <td>'.$Logs[$i]->Text.'</td>
		</tr>'."\n";
	}
	
	echo '</table>'."\n";
}
?>