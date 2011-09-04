<div class="head">Users <small style="font-size: 12px;">(<a href="#!/Help/Users">?</a>)</small></div>

<?php
$Users = $UserObj->GetUsers();

if(is_array($Users)) {
	echo '
	<table>
	 <thead>
	  <tr>
	   <th style="width:60px">Registered</th>
	   <th>Username</th>
	   <th>Group</th>
	   <th style="width:40%">E-Mail</th>
	   <th style="width:20px">&nbsp;</th>
	  </tr>
	 </thead>'."\n";
	foreach($Users AS $User) {
		echo '
		<tr>
		 <td>'.date('d.m.y', $User['UserDate']).'</td>
		 <td>'.$User['UserName'].'</td>
		 <td>'.$User['UserGroupName'].'</td>
		 <td>'.$User['UserEMail'].'</td>
		 <td><img src="images/icons/delete.png" /></td>
		</tr>'."\n";
	}
	echo '</table>'."\n";
}
?>