<script type="text/javascript">
$('#CheckAllNotifications').click(function() {
	$(this).parents('table:eq(0)').find(':checkbox').attr('checked', 'checked');
});
</script>

<div class="head">Settings &raquo; Notifications <small style="font-size: 12px;">(<a href="#!/Help/Main">?</a>)</small></div>

<?php
if($UserObj->UserEMail) {
	$Notifications = $UserObj->GetNotifications();
	
	if(is_array($Notifications)) {
		echo '
		<div class="notification information">
		 <strong>INFORMATION:</strong> This feature requires an account at <a href="http://boxcar.io/">Boxcar</a> using "'.$UserObj->UserEMail.'" and a subscription to the <a href="http://boxcar.io/services/provider_services/new?provider_id=994" target="_blank">Hub service</a>
		</div>
		
		<form id="SettingsForm" name="SettingsNotifications" method="post" action="load.php?page=SaveSettings">
		<input type="hidden" name="SettingSection" value="Notifications" />
		<table>
		 <thead>
		  <tr>
		   <th>Get a notification when…</th>
		   <th style="text-align: center"><a id="CheckAllNotifications">All</a></th>
		  </tr>
		 </thead>
		 <tbody>'."\n";
		 
		foreach($Notifications AS $Notification) {
			$NotificationChecked = ($UserObj->GetUserNotification($Notification['NotificationID'], $UserObj->UserID)) ? ' checked="checked"' : '';
			
			echo '
		    <tr>
		     <td>'.$Notification['NotificationText'].'</td>
		     <td style="width: 30px; text-align:center"><input type="checkbox" name="Notification['.$Notification['NotificationID'].']"'.$NotificationChecked.' /></td>
		    </tr>'."\n";
		}
		
		echo '
		  <tr> 
		   <td colspan="2" style="text-align: right"><a id="SettingsSave" class="button positive"><span class="inner"><span class="label" nowrap="">Save</span></span></a></td> 
		  </tr>
		 </tbody> 
		</table>
		</form>'."\n";
	}
}
else {
	echo '
	<div class="notification information">
	 <strong>INFORMATION:</strong> You need to have an e-mail address in order to get notifications. You can setup one <a href="#!/Profile">here</a>
	</div>';
}
?>