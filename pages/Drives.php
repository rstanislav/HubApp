<?php
$UnusedDrives = $DrivesObj->GetUnusedDriveLetters();

if(is_array($UnusedDrives)) {
	$UnusedDrives = implode('</option><option>', $UnusedDrives);
	
	$UnusedDrives = '<option>'.$UnusedDrives.'</option>';
}
?>
<script type="text/javascript">
$('#NewNetworkDrive').click(function(event) {
	event.preventDefault();
	
	DriveID = randomString();
	$('#tbl-drives tr:first').after(
	    '<tr id="' + DriveID + '">' +
		 '<form name="' + DriveID + '" method="post" action="load.php?page=DriveNetworkAdd">' +
		  '<td style="text-align: center">Now</td>' +
		  '<td>' +
		  '//<input name="DriveNetworkComputer" style="width:105px" type="text" placeholder="computer" />' +
		  '/<input name="DriveNetworkShare" style="width:70px" type="text" placeholder="share" />' +
		  ' <select name="DriveNetworkLetter" style="width:50px"><?php echo $UnusedDrives; ?></select>' +
		  '</td>' +
		  '<td>&nbsp;</td>' +
		  '<td>&nbsp;</td>' +
		  '<td>&nbsp;</td>' +
		  '<td style="text-align:right">' +
	 	   '<a onclick="javascript:ajaxSubmit(\'' + DriveID + '\');"><img src="images/icons/add.png" /></a>' +
	 	   '<a onclick="javascript:$(\'#' + DriveID + '\').remove();"><img src="images/icons/delete.png" /></a>' +
		  '</td>' +
		 '</form>' +
		'</tr>');
});
</script>

<?php
if($UserObj->CheckPermission($UserObj->UserGroupID, 'DriveAdd')) {
?>
<div class="head-control">
 <a id="NewNetworkDrive" class="button positive"><span class="inner"><span class="label" nowrap="">Add Network Drive</span></span></a>
</div>
<?php
}
?>

<div class="head">Drives <small style="font-size: 12px;">(<a href="#!/Help/Drives">?</a>)</small></div>

<?php
$Drives = $DrivesObj->GetDrives();

if(is_array($Drives)) {
?>
<table id="tbl-drives">
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
		$DriveAdd = '<a id="DriveAdd-'.$DriveRoot.'"><img src="images/icons/drive_add.png" /></a>';
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
	 <td>'.$DrivesObj->GetFreeSpacePercentage($FreeSpace, $Space).'% free</td>
	 <td style="text-align:center">'.$DriveActiveLink.' '.$DriveRemoveLink.' '.$DriveAdd.'</td>
	</tr>'."\n";
}

$DrivesNetwork = $DrivesObj->GetDrivesNetwork();

if(is_array($DrivesNetwork)) {
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