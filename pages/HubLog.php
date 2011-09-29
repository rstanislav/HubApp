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
	
	foreach($Logs AS $Log) {
		echo '
		<tr>
		 <td>'.date('d.m H:i', $Log['LogDate']).'</td>
		 <td>'.$Log['LogEvent'].'</td>
		 <td>'.$Log['LogText'].'</td>
		</tr>'."\n";
	}
	echo '</table>'."\n";
}
else {
	echo '
	<div class="notification information">
	 <strong>INFORMATION:</strong> No data available
	</div>';
}
?>