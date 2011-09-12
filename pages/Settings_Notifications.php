<script type="text/javascript">
$('#CheckAllNotifications').click(function() {
	$(this).parents('table:eq(0)').find(':checkbox').attr('checked', 'checked');
});
</script>

<div class="head">Settings &raquo; Notifications <small style="font-size: 12px;">(<a href="#!/Help/Main">?</a>)</small></div>

<?php
if($UserObj->UserEMail) {
?>
<form id="SettingsForm" name="SettingsNotifications" method="post" action="load.php?page=SaveSettings">
<input type="hidden" name="SettingSection" value="Notifications" />
<table>
 <thead>
  <tr>
   <th>Get a notification when…</th>
   <th style="text-align: center"><a id="CheckAllNotifications">All</a></th>
  </tr>
 </thead>
 <tbody> 
  <tr>
   <td>New episodes are available</td>
   <td style="width: 30px; text-align:center"><input type="checkbox" name="Notification[]" /></td>
  </tr>
  <tr>
   <td>A wish has been granted</td>
   <td style="width: 30px; text-align:center"><input type="checkbox" name="Notification[]" /></td>
  </tr>
  <tr>
   <td>Your active drive is approaching its limit</td>
   <td style="width: 30px; text-align:center"><input type="checkbox" name="Notification[]" /></td>
  </tr>
  <tr>
   <td>New torrents are available</td>
   <td style="width: 30px; text-align:center"><input type="checkbox" name="Notification[]" /></td>
  </tr>
  <tr>
   <td>A torrent is downloaded manually</td>
   <td style="width: 30px; text-align:center"><input type="checkbox" name="Notification[]" /></td>
  </tr>
  <tr>
   <td>A torrent is downloaded automatically</td>
   <td style="width: 30px; text-align:center"><input type="checkbox" name="Notification[]" /></td>
  </tr>
  <tr> 
   <td colspan="2" style="text-align: right"><a id="SettingsSave" class="button positive"><span class="inner"><span class="label" nowrap="">Save</span></span></a></td> 
  </tr>
 </tbody> 
</table>
</form>
<?php
}
else {
	echo '<div class="notification">You need to have an e-mail address in order to get notifications</div>';
}
?>