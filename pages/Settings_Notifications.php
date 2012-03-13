<script type="text/javascript">
$('#CheckAllNotifications').click(function() {
	$(this).parents('table:eq(0)').find(':checkbox').attr('checked', 'checked');
});
</script>

<div class="head">Settings &raquo; Notifications  <small style="font-size: 12px;">(<a href="#!/Help/Main">?</a>)</small></div>

<?php
if($UserObj->UserEMail) {
	$Notifications = $UserObj->GetNotifications();
	
	if(is_array($Notifications)) {
		echo '
		<div class="notification information">
		 This feature requires an account at <a href="http://boxcar.io/">Boxcar</a> using "'.$UserObj->UserEMail.'" and a subscription to the <a href="http://boxcar.io/services/provider_services/new?provider_id=994" target="_blank">Hub service</a>
		</div>
		
		<br />
		
		<form id="SettingsForm" name="SettingsNotifications" method="post" action="load.php?page=SaveSettings">
		<input type="hidden" name="SettingSection" value="Notifications" />
		<div id="form-wrap">
		 <dl>
		 
		  <dt>Notifications</dt>
		  <dd>'."\n";
		 
		foreach($Notifications AS $Notification) {
			$NotificationChecked = ($UserObj->GetUserNotification($Notification['NotificationID'], $UserObj->UserID)) ? ' checked="checked"' : '';
			
			echo '
			<div class="field">
			 <label>
			  <input type="checkbox" name="Notification['.$Notification['NotificationID'].']"'.$NotificationChecked.' />
			  <span>'.$Notification['NotificationText'].'</span>
			 </label>
			</div>'."\n";
		}
		
		echo '
		  </dd>
		  <div style="float: right">
		   <a rel="SettingsSave" class="button positive"><span class="inner"><span class="label" nowrap="">Save</span></span></a>
		  </div>
		  
		  <dd class="clear"></dd>
		  
		 </dl>
		 <div class="clear"></div>
		</div>
		</form>'."\n";
	}
}
else {
	echo '
	<div class="notification information">
	 You need to have an e-mail address in order to get notifications. You can setup one <a href="#!/Profile">here</a>
	</div>';
}
?>