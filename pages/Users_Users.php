<?php
$UserGroups = $UserObj->GetUserGroups();

$UserGroupSel = '';
foreach($UserGroups AS $UserGroup) {
	$UserGroupSel .= '<option value="'.$UserGroup['UserGroupID'].'">'.$UserGroup['UserGroupName'].'</option>';
}

$UserGroupSel = '<select style="width: 150px" name="UserGroup">'.$UserGroupSel.'</select>';
?>

<script type="text/javascript">
$('#newUser').click(function(event) {
	event.preventDefault();
	
	UserID = randomString();
	$('#tbl-users tr:first').after(
	    '<tr id="' + UserID + '">' +
		 '<form name="' + UserID + '" method="post" action="load.php?page=UserAdd" style="display:none">' +
		  '<td>Now</td>' +
		  '<td><input name="UserName" style="width:250px" type="text" /></td>' +
		  '<td>' + 
		  '<?php
		  echo $UserGroupSel;
		  ?>' +
		  '</td>' +
		  '<td><input name="UserEMail" style="width:250px" type="text" /></td>' +
		  '<td style="text-align:center">' +
	 	   '<a onclick="javascript:ajaxSubmit(\'' + UserID + '\');"><img src="images/icons/add.png" /></a>' +
	 	   '<a onclick="javascript:$(\'#' + UserID + '\').remove();"><img src="images/icons/delete.png" /></a>' +
		  '</td>' +
		 '</form>' +
		'</tr>');
});
</script>

<div class="head">Users <small style="font-size: 12px;">(<a href="#!/Help/Users">?</a>)</small></div>

<?php
if($UserObj->CheckPermission($UserObj->UserGroupID, 'UserAdd')) {
?>
<div class="head-control">
 <a id="newUser" class="button positive"><span class="inner"><span class="label" nowrap="">Add User</span></span></a>
</div>
<?php
}

$Users = $UserObj->GetUsers();

if(is_array($Users)) {
	echo '
	<table id="tbl-users">
	 <thead>
	  <tr>
	   <th style="width:60px">Registered</th>
	   <th>Username</th>
	   <th>Group</th>
	   <th style="width:40%">E-Mail</th>
	   <th style="width:40px">&nbsp;</th>
	  </tr>
	 </thead>'."\n";
	foreach($Users AS $User) {
		$UserDeleteLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'UserDelete')) ? '<a id="UserDelete-'.$User['UserID'].'" rel="'.$User['UserName'].'"><img src="images/icons/delete.png" /></a>' : '';
		
		echo '
		<tr id="User-'.$User['UserID'].'">
		 <td>'.date('d.m.y', $User['UserDate']).'</td>
		 <td>'.$User['UserName'].'</td>
		 <td>'.$User['UserGroupName'].'</td>
		 <td>'.$User['UserEMail'].'</td>
		 <td style="text-align:center">'.$UserDeleteLink.'</td>
		</tr>'."\n";
	}
	echo '</table>'."\n";
}
?>