<div class="head-control">
 <a id="DriveAdd" class="button positive"><span class="inner"><span class="label" nowrap="">Add Drive</span></span></a>
</div>

<div class="head">Drives</div>

<table id="tbl-drives">
 <thead>
 <tr>
  <th style="width:55px">Since</th>
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
$Drives = json_decode($Hub->Request('drives/'));

if(is_object($Drives) && is_object($Drives->error)) {
	echo '
	<tr>
	 <td colspan="9">'.$Drives->error->message.'</td>
	</tr>'."\n";
}
else {
?>

 
<?php
$TotalFreeSpace = $TotalSpace = 0;
foreach($Drives AS $Drive) {
	$FreeSpace       = $Drive->FreeSpaceInBytes;
	$Space           = $Drive->TotalSpaceInBytes;
	$TotalFreeSpace += $FreeSpace;
	$TotalSpace     += $Space;
	
	$DriveActiveLink = ($Drive->IsActive) ? '<img src="images/icons/drive_active.png" />' : '<a id="DriveActive-'.$Drive->ID.'" rel="ajax"><img src="images/icons/drive_active_off.png" /></a>';
	echo '
	<tr id="Drive-'.$Drive->ID.'">
	 <td>'.date('d.m.y', $Drive->Date).'</td>
	 <td>'.$Drive->Share.'</td>
	 <td>'.$Drive->User.'</td>
	 <td>'.$Drive->Password.'</td>
	 <td>'.$Drive->Mount.'</td>
	 <td>'.BytesToHuman($FreeSpace).'</td>
	 <td>'.BytesToHuman($Space).'</td>
	 <td>'.GetFreeSpacePercentage($FreeSpace, $Space).'% free</td>
	 <td style="text-align:center">
	  '.$DriveActiveLink.'
	  <a id="DriveRemove-'.$Drive->ID.'" rel="ajax"><img src="images/icons/drive_remove.png" /></a>
	 </td>
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
  <th><?php echo BytesToHuman($TotalFreeSpace); ?></th>
  <th><?php echo BytesToHuman($TotalSpace); ?></th>
  <th><?php echo GetFreeSpacePercentage($TotalFreeSpace, $TotalSpace).'% free'; ?></th>
  <th style="text-align:center"></th>
 </tr>
 </tfoot>
</table>
<?php
}
?>