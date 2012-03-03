<?php
if($UserObj->CheckPermission($UserObj->UserGroupID, 'ZoneSwitch')) {
	$Zones = $ZonesObj->GetZones();

	if(sizeof($Zones)) {
		echo '
		<form method="post" action="">
		<select name="zoneSelect" id="zoneSelect" class="blue">'."\n";
	
		foreach($Zones AS $Zone) {
 			$ZoneSelected = ($Zone['ZoneName'] == $HubObj->CurrentZone) ? ' selected="selected"' : '';
 	
 			echo '<option value=""'.$ZoneSelected.'>'.$Zone['ZoneName'].'</option>'."\n";
 		}

		echo '
		</select>
		</form>'."\n";
	}
	else {
    	echo '<span style="color: white">No zones available</span>';
	}
}
else {
    echo '<span style="padding-right:10px; color: white; font-weight: bold">'.$HubObj->CurrentZone.'</span>';
}
?>