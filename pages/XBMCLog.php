<div class="head">XBMC Log</div>

<?php
$Logs = json_decode($Hub->Request('xbmc/log/'));

if(is_object($Logs) && is_object($Logs->error)) {
	echo '<div class="notification warning">'.$Logs->error->message.'</div>'."\n";
}
else {
	echo '
	<table id="xbmc-log" class="text-select">
	 <thead>
	 <tr>
	  <th style="text-align:center;width: 60px">Time</th>
	  <th>&nbsp;</th>
	  <th>&nbsp;</th>
	  <th>Text</th>
	 </tr>
	 </thead>'."\n";
	 
	foreach($Logs AS $Log) {
		echo '
		<tr>
		 <td>'.$Log[0].'</td>
		 <td>'.$Log[1].'</td>
		 <td>'.$Log[2].'</td>
		 <td>'.$Log[3].'</td>
		</tr>'."\n";
	}
	
	echo '</table>'."\n";
}
?>