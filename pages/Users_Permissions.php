<script type="text/javascript">
$('#tbl-permissions .editable').editable('load.php?page=PermissionEdit');
$('#newPermission').click(function(event) {
	event.preventDefault();
	
	PermissionID = randomString();
	$('#tbl-permissions tr:first').after(
		'<tr id="' + PermissionID + '">' +
		 '<form name="' + PermissionID + '" method="post" action="load.php?page=PermissionAdd" style="display:none">' +
		  '<td>Now</td>' +
		  '<td><input name="PermissionText" style="width:250px" placeholder="text" type="text" /></td>' +
		  '<td><input name="PermissionAction" style="width:115px" placeholder="action" type="text" /></td>' +
		  '<td><input name="PermissionValue" style="width:50px" type="hidden" value="1" /></td>' +
		  '<td style="text-align:right">' +
	 	   '<a onclick="javascript:ajaxSubmit(\'' + PermissionID + '\');"><img src="images/icons/add.png" /></a>' +
	 	   '<a onclick="javascript:$(\'#' + PermissionID + '\').remove();"><img src="images/icons/delete.png" /></a>' +
		  '</td>' +
		 '</form>' +
		'</tr>');
});
</script>

<div class="head">Users &raquo; Permissions <small style="font-size: 12px;">(<a href="#!/Help/UserPermissions">?</a>)</small></div>

<div class="head-control">
 <a id="newPermission" class="button positive"><span class="inner"><span class="label" nowrap="">Add Permission</span></span></a>
</div>

<?php
$Permissions = $UserObj->GetPermissions();

if(is_array($Permissions)) {
	echo '
	<table id="tbl-permissions">
	 <thead>
	  <tr>
	   <th style="width:60px">Added</th>
	   <th>Text</th>
	   <th style="width: 150px">Action</th>
	   <th style="width: 80px">Bit Value</th>
	   <th style="width:34px">&nbsp;</th>
	  </tr>
	 </thead>'."\n";
	foreach($Permissions AS $Permission) {
		echo '
		<tr id="Permission-'.$Permission['PermissionID'].'">
		 <td>'.date('d.m.y', $Permission['PermissionDate']).'</td>
		 <td class="editable" id="'.$Permission['PermissionID'].'-|-PermissionText">'.$Permission['PermissionText'].'</td>
		 <td class="editable" id="'.$Permission['PermissionID'].'-|-PermissionAction">'.$Permission['PermissionAction'].'</td>
		 <td>'.$Permission['PermissionValue'].'</td>
		 <td></td>
		</tr>'."\n";
	}
	echo '</table>'."\n";
}
?>