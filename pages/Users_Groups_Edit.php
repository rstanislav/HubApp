<script type="text/javascript">
$('#CheckAllPermissions').click(function() {
	$(this).parents('table:eq(0)').find(':checkbox').attr('checked', 'checked');
});

$('#GroupPermissionsSave').click(function() {
	$('form[id=' + $(this).parents('form:eq(0)').attr('id') + ']').ajaxSubmit({
		beforeSubmit: function() {
			$('#GroupPermissionsSave').contents().find('.label').text('Saving ...');
		},
		success: function() {
			$('#GroupPermissionsSave').contents().find('.label').text('Saved!');
		},
		error: function() {
			$('#GroupPermissionsSave').contents().find('.label').text('Error!');
		}
	});
});
</script>
<?php
$UserGroupID  = (filter_has_var(INPUT_GET, 'UserGroupID')) ? $_GET['UserGroupID'] : '';

if($UserGroupID) {
	$UserGroup = $UserObj->GetUserGroup($UserGroupID);
	
	if(is_array($UserGroup)) {
		echo '
		<form id="GroupPermissionsForm" name="GroupPermissions" method="post" action="load.php?page=GroupPermissions">
		<table>'."\n";
		
		foreach($UserGroup AS $Group) {
			echo '
			<thead>
		  	<tr>
		  	 <th>'.$Group['UserGroupName'].'<input type="hidden" name="GroupID" value="'.$Group['UserGroupID'].'" /></th>
		  	 <th style="text-align: center"><a id="CheckAllPermissions">All</th>
		  	</tr>
		  	</thead>
		  	<tbody>'."\n";
		  	
		  	$Permissions = $UserObj->GetPermissions();
		  	
		  	if(is_array($Permissions)) {
		  		foreach($Permissions AS $Permission) {
		  			$PermissionChecked = ($UserObj->GetGroupPermission($Permission['PermissionID'], $Group['UserGroupID'])) ? ' checked="checked"' : '';
		  			
		  			echo '
		  			<tr>
		  			 <td>'.$Permission['PermissionText'].'</td>
		  			 <td style="width: 30px; text-align:center"><input type="checkbox" name="Permission['.$Permission['PermissionID'].']"'.$PermissionChecked.' /></td>
		  			</tr>'."\n";
		  		}
		  	}
		}
		
		echo '
		  <tr>
		   <td colspan="2" style="text-align: right"><a id="GroupPermissionsSave" class="button positive"><span class="inner"><span class="label" nowrap="">Save</span></span></a></td>
		  </tr>
		 </tbody>
		</table>
		</form>'."\n";
	}
}
?>