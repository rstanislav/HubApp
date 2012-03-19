<?php
if($UserObj->CheckPermission($UserObj->UserGroupID, 'ZoneSwitch')) {
	$Zones = $ZonesObj->GetZones();

	if(sizeof($Zones)) {
		echo '
		<select name="zoneSelect" id="zoneSelect" class="blue">'."\n";
	
		foreach($Zones AS $Zone) {
 			$ZoneSelected = ($Zone['ZoneName'] == $HubObj->CurrentZone) ? ' selected="selected"' : '';
 	
 			echo '<option value="'.$Zone['ZoneName'].'"'.$ZoneSelected.'>'.$Zone['ZoneName'].'</option>'."\n";
 		}

		echo '
		</select>'."\n";
	}
	else {
		echo '<span style="color: white">No zones available</span>';
	}
}
else {
	echo '<span style="padding-right:10px; color: white; font-weight: bold">'.$HubObj->CurrentZone.'</span>';
}
?>