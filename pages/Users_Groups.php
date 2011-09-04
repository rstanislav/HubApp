<div class="head">Users &raquo; Groups <small style="font-size: 12px;">(<a href="#!/Help/UserGroups">?</a>)</small></div>

<?php
$UserGroups = $UserObj->GetUserGroups();

if(is_array($UserGroups)) {
	echo '
	<table>
	 <thead>
	  <tr>
	   <th style="width:60px">Added</th>
	   <th>Group</th>
	   <th style="width:36px">&nbsp;</th>
	  </tr>
	 </thead>'."\n";
	foreach($UserGroups AS $UserGroup) {
		echo '
		<tr>
		 <td>'.date('d.m.y', $UserGroup['UserGroupDate']).'</td>
		 <td>'.$UserGroup['UserGroupName'].'</td>
		 <td>
		  <a id="UserGroupEdit-'.$UserGroup['UserGroupID'].'"><img src="images/icons/group_edit.png" /></a>
		  <img src="images/icons/delete.png" />
		 </td>
		</tr>'."\n";
	}
	echo '</table>'."\n";
}
?>

<br />

<div id="UserGroupEdit"></div>