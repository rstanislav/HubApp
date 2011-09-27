<?php
if($UserObj->CheckPermission($UserObj->UserGroupID, 'DriveAdd')) {
?>
<div class="head-control">
 <a id="AddNetworkDrive" class="button positive"><span class="inner"><span class="label" nowrap="">Add Network Drive</span></span></a>
</div>
<?php
}
?>

<div class="head">Drives <small style="font-size: 12px;">(<a href="#!/Help/Drives">?</a>)</small></div>

<?php
$Drives = $DrivesObj->GetDrives();

if(is_array($Drives)) {
?>
<table>
 <thead>
 <tr>
  <th style="text-align: center; width:60px">Since</th>
  <th>Root</th>
  <th>Free</th>
  <th>Total</th>
  <th>&nbsp;</th>
  <th style="width: 36px">&nbsp;</th>
 </tr>
 </thead>
 
<?php
$TotalFreeSpace = $TotalSpace = 0;
foreach($Drives AS $Drive) {
	$DriveDB = $DrivesObj->GetDriveByLetter($Drive);
	
	if(is_array($DriveDB)) {
		$DriveRoot     = ($DriveDB['DriveNetwork']) ? $DriveDB['DriveRoot']                                : $DriveDB['DriveLetter'];
		$DriveRootText = ($DriveDB['DriveNetwork']) ? $DriveDB['DriveRoot'].' ('.$DriveDB['DriveLetter'].')' : $DriveDB['DriveLetter'];
	
		$DriveAdd = '';
		
		if($DriveDB['DriveID'] == $HubObj->ActiveDrive) {
			$DriveActiveLink = '<a id="DriveActive-'.$DriveDB['DriveID'].'" rel="'.$DriveRootText.'"><img src="images/icons/drive_active.png" /></a>';
		}
		else {
			$DriveActiveLink = '<a id="DriveActive-'.$DriveDB['DriveID'].'" rel="'.$DriveRootText.'"><img src="images/icons/drive_active_off.png" /></a>';
		}
		
		$DriveRemoveLink = '<a id="DriveRemove-'.$DriveDB['DriveID'].'" rel="'.$DriveRootText.'"><img src="images/icons/drive_remove.png" /></a>';
		
		$DriveActiveLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'DriveActive')) ? $DriveActiveLink : '';
		$DriveRemoveLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'DriveRemove')) ? $DriveRemoveLink : '';
		
		$DriveDate = date('d.m.y', $DriveDB['DriveDate']);
		$DriveID = $DriveDB['DriveID'];
	}
	else {
		$DriveID = rand();
		$DriveRoot = $DriveRootText = $Drive;
		$DriveAdd = '<img src="images/icons/drive_add.png" />';
		$DriveActiveLink = '';
		$DriveRemoveLink = '';
		$DriveDate = '';
	}
	
	$FreeSpace       = $DrivesObj->GetFreeSpace($DriveRoot, TRUE);
	$Space           = $DrivesObj->GetTotalSpace($DriveRoot, TRUE);
	$TotalFreeSpace += $FreeSpace;
	$TotalSpace     += $Space;
	
	echo '
	<tr id="Drive-'.$DriveID.'">
	 <td style="text-align: center;">'.$DriveDate.'</td>
	 <td>'.$DriveRootText.'</td>
	 <td>'.$DrivesObj->BytesToHuman($FreeSpace).'</td>
	 <td>'.$DrivesObj->BytesToHuman($Space).'</td>
	 <td>'.$DrivesObj->GetFreeSpacePercentage($FreeSpace, $TotalSpace).'% free</td>
	 <td style="text-align:center">'.$DriveActiveLink.' '.$DriveRemoveLink.' '.$DriveAdd.'</td>
	</tr>'."\n";
}

$DrivesNetwork = $DrivesObj->GetDrivesNetwork();

foreach($DrivesNetwork AS $Drive) {
	$DriveRoot     = ($Drive['DriveNetwork']) ? $Drive['DriveRoot']                                : $Drive['DriveLetter'];
	$DriveRootText = ($Drive['DriveNetwork']) ? $Drive['DriveRoot'].' ('.$Drive['DriveLetter'].')' : $Drive['DriveLetter'];

	$DriveAdd = '';
	
	if($Drive['DriveID'] == $HubObj->ActiveDrive) {
		$DriveActiveLink = '<a id="DriveActive-'.$Drive['DriveID'].'" rel="'.$DriveRootText.'"><img src="images/icons/drive_active.png" /></a>';
	}
	else {
		$DriveActiveLink = '<a id="DriveActive-'.$Drive['DriveID'].'" rel="'.$DriveRootText.'"><img src="images/icons/drive_active_off.png" /></a>';
	}
	
	$DriveRemoveLink = '<a id="DriveRemove-'.$Drive['DriveID'].'" rel="'.$DriveRootText.'"><img src="images/icons/drive_remove.png" /></a>';
	
	$DriveActiveLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'DriveActive')) ? $DriveActiveLink : '';
	$DriveRemoveLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'DriveRemove')) ? $DriveRemoveLink : '';
	
	$DriveDate = date('d.m.y', $Drive['DriveDate']);
	$DriveID = $Drive['DriveID'];
	
	$FreeSpace       = $DrivesObj->GetFreeSpace($DriveRoot, TRUE);
	$Space           = $DrivesObj->GetTotalSpace($DriveRoot, TRUE);
	$TotalFreeSpace += $FreeSpace;
	$TotalSpace     += $Space;
	
	echo '
	<tr id="Drive-'.$DriveID.'">
	 <td style="text-align: center;">'.$DriveDate.'</td>
	 <td>'.$DriveRootText.'</td>
	 <td>'.$DrivesObj->BytesToHuman($FreeSpace).'</td>
	 <td>'.$DrivesObj->BytesToHuman($Space).'</td>
	 <td>'.$DrivesObj->GetFreeSpacePercentage($FreeSpace, $TotalSpace).'% free</td>
	 <td style="text-align:center">'.$DriveActiveLink.' '.$DriveRemoveLink.' '.$DriveAdd.'</td>
	</tr>'."\n";
}
?>
 <tfoot>
 <tr>
  <th style="text-align: center;"></th>
  <th>Total</td>
  <th><?php echo $DrivesObj->BytesToHuman($TotalFreeSpace); ?></th>
  <th><?php echo $DrivesObj->BytesToHuman($TotalSpace); ?></th>
  <th><?php echo $DrivesObj->GetFreeSpacePercentage($TotalFreeSpace, $TotalSpace).'% free'; ?></th>
  <th style="text-align:center"></th>
 </tr>
 </tfoot>
</table>
<?php
}
else {
	echo '<div class="notification">Unable to get drive data</div>';
}
?>