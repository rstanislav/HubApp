<script type="text/javascript">
$('#tbl-zones .editable').editable('load.php?page=ZoneEdit');
$('#newZone').click(function(event) {
	event.preventDefault();
	
	ZoneID = randomString();
	$('#tbl-zones tr:first').after(
	    '<tr id="' + ZoneID + '">' +
		 '<form name="' + ZoneID + '" method="post" action="load.php?page=ZoneAdd">' +
		  '<td>Now</td>' +
		  '<td><input name="ZoneName" style="width:145px" placeholder="name" type="text" /></td>' +
		  '<td><input name="ZoneHost" style="width:115px" placeholder="host" type="text" /></td>' +
		  '<td><input name="ZonePort" style="width:35px" placeholder="port" type="text" /></td>' +
		  '<td><input name="ZoneUser" style="width:65px" placeholder="user" type="text" /></td>' +
		  '<td><input name="ZonePass" style="width:65px" placeholder="password" type="text" /></td>' +
		  '<td style="text-align:right">' +
	 	   '<a onclick="javascript:ajaxSubmit(\'' + ZoneID + '\');"><img src="images/icons/add.png" /></a>' +
	 	   '<a onclick="javascript:$(\'#' + ZoneID + '\').remove();"><img src="images/icons/delete.png" /></a>' +
		  '</td>' +
		 '</form>' +
		'</tr>');
});
</script>

<?php
if($UserObj->CheckPermission($UserObj->UserGroupID, 'ZoneAdd')) {
?>
<div class="head-control">
 <a id="newZone" class="button positive"><span class="inner"><span class="label" nowrap="">Add Zone</span></span></a>
</div>
<?php
}
?>
<div class="head">Zones <small style="font-size: 12px;">(<a href="#!/Help/Zones">?</a>)</small></div>

<table id="tbl-zones">
 <thead>
 <tr>
  <th style="width:60px">Since</th>
  <th style="width:155px">Name</th>
  <th style="width:125px">Host</th>
  <th style="width:45px">Port</th>
  <th style="width:75px">Username</th>
  <th style="width:75px">Password</th>
  <th>&nbsp;</th>
 </tr>
 </thead>
<?php
$Zones = $ZonesObj->GetZones();

if(sizeof($Zones)) {
	foreach($Zones AS $Zone) {
		if($Zone['ZoneName'] == $ZonesObj->CurrentZone) {
			$SwitchButton = 'disabled';
			$SwitchButtonText = 'Current';
		}
		else {
			$SwitchButton = 'positive';
			$SwitchButtonText = 'Switch to';
		}
	
		$ZoneDeleteLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'ZoneDelete')) ? '<a id="ZoneDelete-'.$Zone['ZoneID'].'" rel="'.$Zone['ZoneName'].'"><img src="images/icons/delete.png" /></a>' : '';
	
		echo '
		 <tr id="Zone-'.$Zone['ZoneID'].'">
		  <td>'.date('d.m.y', $Zone['ZoneDate']).'</td>
		  <td class="editable" id="'.$Zone['ZoneID'].'-|-ZoneName">'.$Zone['ZoneName'].'</td>
		  <td class="editable" id="'.$Zone['ZoneID'].'-|-ZoneXBMCHost">'.$Zone['ZoneXBMCHost'].'</td>
		  <td class="editable" id="'.$Zone['ZoneID'].'-|-ZoneXBMCPort">'.$Zone['ZoneXBMCPort'].'</td>
		  <td class="editable" id="'.$Zone['ZoneID'].'-|-ZoneXBMCUsername">'.$Zone['ZoneXBMCUsername'].'</td>
		  <td class="editable" id="'.$Zone['ZoneID'].'-|-ZoneXBMCPassword" style="font-style: italic;">hidden</td>
		  <td style="text-align:right">
		   '.$ZoneDeleteLink.'
		  </td>
		 </tr>'."\n";
	}
}
else {
	echo '
	 <tr>
	  <td colspan="7">
	   <div class="notification information">
	    <strong>INFORMATION:</strong> No zones added
	   </div>
	  </td>
	 </tr>'."\n";
}
?>
</table>