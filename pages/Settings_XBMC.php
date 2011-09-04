<style>
#maincontent td {
	width: 550px;
}

input {
	width: 500px;
}
.column1 {
	font-weight: bold;
}

.column1 small {
	font-weight: normal;
}
</style>

<div class="head">Settings &raquo; XBMC <small style="font-size: 12px;">(<a href="#!/Help/Main">?</a>)</small></div>

<form id="SettingsForm" name="SettingsXBMC" method="post" action="load.php?page=SaveSettings">
<input type="hidden" name="SettingSection" value="XBMC" />
<table>
 <tbody>
 <tr>
  <th scope="row" class="column1">
   Log<br />
   <small>Where is your XBMC log file located?</small>
  </th>
  <td><input name="SettingXBMCLogFile" type="text" value="<?php echo $Settings['SettingXBMCLogFile']; ?>" /></td>
 </tr>
 <tr>
  <th scope="row" class="column1">
   Sources.xml<br />
   <small>Where is your XBMC Sources.xml file located?</small>
  </th>
  <td><input name="SettingXBMCSourcesFile" type="text" value="<?php echo $Settings['SettingXBMCSourcesFile']; ?>" /></td>
 </tr>
 <tr>
  <th scope="row" class="column1">
   RSS_Feeds.xml<br />
   <small>Where is your XBMC RSS_Feeds.xml located?</small>
  </th>
  <td><input name="SettingXBMCRSSFile" type="text" value="<?php echo $Settings['SettingXBMCRSSFile']; ?>" /></td>
 </tr>
 <tr>
  <th scope="row" class="column1">
   Database folder<br />
   <small>Where is your XBMC database folder located?</small>
  </th>
  <td><input name="SettingXBMCDatabaseFolder" type="text" value="<?php echo $Settings['SettingXBMCDatabaseFolder']; ?>" /></td>
 </tr>
 <tr> 
  <td colspan="2" style="text-align: right"><a id="SettingsSave" class="button positive"><span class="inner"><span class="label" nowrap="">Save</span></span></a></td> 
 </tr>
 </tbody>
</table>
</form>