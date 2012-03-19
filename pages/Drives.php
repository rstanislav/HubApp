<script type="text/javascript">
$('#NewDrive').click(function(event) {
	event.preventDefault();
	
	$('#drive-instruction').show();
	
	DriveID = randomString();
	$('#tbl-drives tr:first').after(
		'<tr id="' + DriveID + '">' +
		 '<form name="' + DriveID + '" method="post" action="load.php?page=DriveAdd">' +
		  '<td style="text-align: center">Now</td>' +
		  '<td>' +
		  '//<input name="DriveComputer" style="width:105px" type="text" placeholder="computer" />' +
		  '/<input name="DriveShare" style="width:70px" type="text" placeholder="share" />' +
		  '</td>' +
		  '<td><input name="DriveUser" style="width:40px" type="text" placeholder="user" /></td>' +
		  '<td><input name="DrivePass" style="width:40px" type="text" placeholder="pass" /></td>' +
		  '<td><input name="DriveMount" style="width:80px" type="text" placeholder="X:" /></td>' +
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
 <a id="NewDrive" class="button positive"><span class="inner"><span class="label" nowrap="">Add Drive</span></span></a>
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
  <th>Share</th>
  <th style="width:50px">User</th>
  <th style="width:50px">Pass</th>
  <th style="width:90px">Mount</th>
  <th style="width:60px">Free</th>
  <th style="width:80px">Total</th>
  <th>&nbsp;</th>
  <th style="width: 36px">&nbsp;</th>
 </tr>
 </thead>
 
<?php
$TotalFreeSpace = $TotalSpace = 0;
foreach($Drives AS $Drive) {
	$DriveShareCred = $DrivesObj->DriveShareCredentials($Drive['DriveShare'], $Drive['DriveUser'], $Drive['DrivePass']);

	if($Drive['DriveID'] == $HubObj->ActiveDrive) {
		$DriveActiveLink = '<a id="DriveActive-'.$Drive['DriveID'].'" rel="'.$Drive['DriveShare'].'"><img src="images/icons/drive_active.png" /></a>';
	}
	else {
		$DriveActiveLink = '<a id="DriveActive-'.$Drive['DriveID'].'" rel="'.$Drive['DriveShare'].'"><img src="images/icons/drive_active_off.png" /></a>';
	}
	
	$DriveActiveLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'DriveActive')) ? $DriveActiveLink : '';
	$DriveRemoveLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'DriveRemove')) ? '<a id="DriveRemove-'.$Drive['DriveID'].'" rel="'.$Drive['DriveShare'].'"><img src="images/icons/drive_remove.png" /></a>' : '';
	
	$DriveUser = (empty($Drive['DriveUser'])) ? '' : '<em>hidden</em>';
	$DrivePass = (empty($Drive['DrivePass'])) ? '' : '<em>hidden</em>';
	
	$FreeSpace       = $DrivesObj->GetFreeSpace($Drive['DriveID'], TRUE);
	$Space           = $DrivesObj->GetTotalSpace($Drive['DriveID'], TRUE);
	$TotalFreeSpace += $FreeSpace;
	$TotalSpace     += $Space;
	echo '
	<tr id="Drive-'.$Drive['DriveID'].'">
	 <td style="text-align: center;">'.date('d.m.y', $Drive['DriveDate']).'</td>
	 <td><a href="#!/FileManager/'.$Drive['DriveMount'].'" title="View \''.$Drive['DriveMount'].'\' in File Manager"><img style="vertical-align: middle" src="images/icons/go_arrow.png" /></a> '.$Drive['DriveShare'].'</td>
	 <td>'.$DriveUser.'</td>
	 <td>'.$DrivePass.'</td>
	 <td>'.$Drive['DriveMount'].'</td>
	 <td>'.$DrivesObj->BytesToHuman($FreeSpace).'</td>
	 <td>'.$DrivesObj->BytesToHuman($Space).'</td>
	 <td>'.$DrivesObj->GetFreeSpacePercentage($FreeSpace, $Space).'% free</td>
	 <td style="text-align:center">'.$DriveActiveLink.' '.$DriveRemoveLink.'</td>
	</tr>'."\n";
}
?>
 <tfoot>
 <tr>
  <th style="text-align:center">Total</th>
  <th>&nbsp;</th>
  <th>&nbsp;</th>
  <th>&nbsp;</th>
  <th>&nbsp;</td>
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
	echo '<div class="notification warning">Unable to get drive data</div>';
}
?>