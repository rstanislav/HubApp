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
	
	foreach($Logs AS $Log) {
		echo '
		<tr>
		 <td>'.date('d.m H:i', $Log->Date).'</td>
		 <td>'.$Log->Event.'</td>
		 <td>'.nl2br($Log->Text).'</td>
		</tr>'."\n";
	}
	
	echo '</table>'."\n";
}
?>