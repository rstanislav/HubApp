<style>
#maincontent td {
	width: 250px;
}

.column1 {
	font-weight: bold;
}

.column1 small {
	font-weight: normal;
}
</style>

<div class="head">Settings &raquo; uTorrent <small style="font-size: 12px;">(<a href="#!/Help/Main">?</a>)</small></div>

<form id="SettingsForm" name="SettingsUTorrent" method="post" action="load.php?page=SaveSettings">
<input type="hidden" name="SettingSection" value="UTorrent" />
<table>
 <tbody>
 <tr>
  <th scope="row" class="column1">
   Hostname<br />
   <small>Hostname for the uTorrent API server. <em>Default is "localhost"</em></small>
  </th>
  <td>
  <?php
  $UTorrentAPIHostname = ($Settings['SettingUTorrentHostname']) ? $Settings['SettingUTorrentHostname'] : 'localhost';
  echo '<input name="SettingUTorrentHostname" type="text" value="'.$UTorrentAPIHostname.'" />'."\n";
  ?>
  </td>
 </tr>
 <tr>
  <th scope="row" class="column1">
   Port<br />
   <small>Port for the uTorrent API server</small>
  </th>
  <td><input name="SettingUTorrentPort" type="text" value="<?php echo $Settings['SettingUTorrentPort']; ?>" /></td>
 </tr>
 <tr>
  <th scope="row" class="column1">
   Username<br />
   <small>Username for connecting to the uTorrent API server</small>
  </th>
  <td><input name="SettingUTorrentUsername" type="text" value="<?php echo $Settings['SettingUTorrentUsername']; ?>" /></td>
 </tr>
 <tr>
  <th scope="row" class="column1">
   Password<br />
   <small>Password for connecting to the uTorrent API server</small>
  </th>
  <td><input name="SettingUTorrentPassword" type="text" value="<?php echo $Settings['SettingUTorrentPassword']; ?>" /></td>
 </tr>
 <tr>
  <th scope="row" class="column1">
   Watch Folder<br />
   <small>Where is your uTorrent watch folder located?</small>
  </th>
  <td><input name="SettingUTorrentWatchFolder" type="text" value="<?php echo $Settings['SettingUTorrentWatchFolder']; ?>" /></td>
 </tr>
 <tr>
  <th scope="row" class="column1">
   Default Upload Limit<br />
   <small>Your day-to-day upload limit in KiB/s</small>
  </th>
  <td><input name="SettingUTorrentDefaultUpSpeed" type="text" value="<?php echo $Settings['SettingUTorrentDefaultUpSpeed']; ?>" /></td>
 </tr>
 <tr>
  <th scope="row" class="column1">
   Default Download Limit<br />
   <small>Your day-to-day download limit in KiB/s</small>
  </th>
  <td><input name="SettingUTorrentDefaultDownSpeed" type="text" value="<?php echo $Settings['SettingUTorrentDefaultDownSpeed']; ?>" /></td>
 </tr>
 <tr>
  <th scope="row" class="column1">
   Defined Upload Limit<br />
   <small>Your "slow" upload limit in KiB/s</small>
  </th>
  <td><input name="SettingUTorrentDefinedUpSpeed" type="text" value="<?php echo $Settings['SettingUTorrentDefinedUpSpeed']; ?>" /></td>
 </tr>
 <tr>
  <th scope="row" class="column1">
   Defined Download Limit<br />
   <small>Your "slow" download limit in KiB/s</small>
  </th>
  <td><input name="SettingUTorrentDefinedDownSpeed" type="text" value="<?php echo $Settings['SettingUTorrentDefinedDownSpeed']; ?>" /></td>
 </tr>
 <tr> 
  <td colspan="2" style="text-align: right"><a id="SettingsSave" class="button positive"><span class="inner"><span class="label" nowrap="">Save</span></span></a></td> 
 </tr>
 </tbody>
</table>
</form>