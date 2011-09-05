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
$Drives = $DrivesObj->GetDrives();

$TotalFreeSpace = $TotalSpace = 0;
foreach($Drives AS $Drive) {
	if($Drive['DriveID'] == $HubObj->ActiveDrive) {
		$DriveActiveLink = '';
		$DriveRemoveLink = '';
	}
	else {
		$DriveActiveLink = '<a id="DriveActive-'.$Drive['DriveID'].'"><img src="images/icons/drive_active.png" /></a>';
		$DriveRemoveLink = '<a id="DriveRemove-'.$Drive['DriveID'].'"><img src="images/icons/drive_remove.png" /></a>';
	}
	
	$DriveActiveLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'DriveActive')) ? $DriveActiveLink : '';
	$DriveRemoveLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'DriveRemove')) ? $DriveRemoveLink : '';
	
	$DriveRoot       = ($Drive['DriveNetwork']) ? $Drive['DriveRoot']                                : $Drive['DriveLetter'];
	$DriveRootText   = ($Drive['DriveNetwork']) ? $Drive['DriveRoot'].' ('.$Drive['DriveLetter'].')' : $Drive['DriveLetter'];
	$FreeSpace       = $DrivesObj->GetFreeSpace($DriveRoot, TRUE);
	$Space           = $DrivesObj->GetTotalSpace($DriveRoot, TRUE);
	$TotalFreeSpace += $FreeSpace;
	$TotalSpace     += $Space;
	
	echo '
	<tr id="Drive-'.$Drive['DriveID'].'">
	 <td style="text-align: center;">'.date('d.m.y', $Drive['DriveDate']).'</td>
	 <td>'.$DriveRootText.'</td>
	 <td>'.$DrivesObj->BytesToHuman($FreeSpace).'</td>
	 <td>'.$DrivesObj->BytesToHuman($Space).'</td>
	 <td>'.$DrivesObj->GetFreeSpacePercentage($FreeSpace, $TotalSpace).'% free</td>
	 <td style="text-align:center">'.$DriveActiveLink.' '.$DriveRemoveLink.'</td>
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