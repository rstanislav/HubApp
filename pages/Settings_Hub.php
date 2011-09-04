<script type="text/javascript">
$('select#MinDownloadQuality, select#MaxDownloadQuality').selectToUISlider({
	labels: 4,
	labelSrc: 'text'
}).hide();

$('select#MinActiveDiskPercentage').selectToUISlider({
	labels: 9,
	tooltip: false
}).hide();
</script>

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

<div class="head">Settings &raquo; Hub <small style="font-size: 12px;">(<a href="#!/Help/Main">?</a>)</small></div>

<form id="SettingsForm" name="SettingsHub" method="post" action="load.php?page=SaveSettings">
<input type="hidden" name="SettingSection" value="Hub" />
<table> 
 <tbody> 
  <tr> 
   <th scope="row" class="column1">
    Local IP<br />
    <small>IP address of your server where XBMC, uTorrent etc is located</small>
   </th> 
   <td><input name="SettingHubLocalIP" type="text" value="<?php echo $Settings['SettingHubLocalIP']; ?>" /></td> 
  </tr>	
  <tr> 
   <th scope="row" class="column1">
    Minimum active disk percentage<br />
    <small>At which percentage will a new active disk be chosen?</small>
   </th> 
   <td style="padding:0 20px 0 30px">
    <select name="SettingHubMinimumActiveDiskPercentage" id="MinActiveDiskPercentage">
    <?php
    $PercentArr = array(1, 2, 3, 4, 5, 10, 15, 20, 30);
    
    foreach($PercentArr AS $Percent) {
    	$PercentSelected = ($Percent == $Settings['SettingHubMinimumActiveDiskPercentage']) ? ' selected="selected"' : '';
    	echo '<option value="'.$Percent.'%"'.$PercentSelected.'>'.$Percent.'%</option>'."\n";
    }
    ?>
    </select>
   </td> 
  </tr>
  <tr> 
   <th scope="row" class="column1">
    Download quality<br />
    <small>Which quality do you prefer?</small>
   </th> 
   <td style="padding:0 20px 0 30px">
    <select name="SettingHubMinimumDownloadQuality" id="MinDownloadQuality">
    <?php
    $MinQualityArr = array(5000  => 'HDTV/DVDRip',
                           6000  => 'BRRip/BDRip',
                           10000 => '480p',
                           20000 => '540p',
                           30000 => '720p',
                           40000 => '810p',
                           50000 => '1080p');
    
    foreach($MinQualityArr AS $MinQualityVal => $MinQualityText) {
    	$MinQualitySelected = ($MinQualityVal == $Settings['SettingHubMinimumDownloadQuality']) ? ' selected="selected"' : '';
    	echo '<option value="'.$MinQualityVal.'"'.$MinQualitySelected.'>'.$MinQualityText.'</option>'."\n";
    }
    ?>
     </select>
    
     <select name="SettingHubMaximumDownloadQuality" id="MaxDownloadQuality">
     <?php
     $MaxQualityArr = array(5999  => 'HDTV/DVDRip',
                            6999  => 'BRRip/BDRip',
                            19999 => '480p',
                            29999 => '540p',
                            39999 => '720p',
                            49999 => '810p',
                            59999 => '1080p');
     
     foreach($MaxQualityArr AS $MaxQualityVal => $MaxQualityText) {
     	$MaxQualitySelected = ($MaxQualityVal == $Settings['SettingHubMaximumDownloadQuality']) ? ' selected="selected"' : '';
     	echo '<option value="'.$MaxQualityVal.'"'.$MaxQualitySelected.'>'.$MaxQualityText.'</option>'."\n";
     }
     ?>
     </select>
   </td> 
  </tr>
  <tr> 
   <th scope="row" class="column1">
    TheTVDB API Key<br />
    <small>Your personal API key for TheTVDB.org</small>
   </th> 
   <td><input name="SettingHubTheTVDBAPIKey" type="text" value="<?php echo $Settings['SettingHubTheTVDBAPIKey']; ?>" /</td> 
  </tr>
  <tr> 
   <th scope="row" class="column1">
    Hub files backup<br />
    <small>Backup Hub files?</small>
   </th> 
   <td>
   <?php
   $BackupChecked = ($Settings['SettingHubBackup']) ? ' checked="checked"' : '';
   echo '<input name="SettingHubBackup" type="checkbox"'.$BackupChecked.' />'."\n";
   ?>
   </td> 
  </tr>
  <tr> 
   <th scope="row" class="column1">
    Hub kill switch<br />
    <small>Prevent Hub from running in the background</small>
   </th> 
   <td>
   <?php
   $KillSwitchChecked = ($Settings['SettingHubKillSwitch']) ? ' checked="checked"' : '';
   echo '<input name="SettingHubKillSwitch" type="checkbox"'.$KillSwitchChecked.' />'."\n";
   ?>
   </td> 
  </tr>
  <tr> 
   <td colspan="2" style="text-align: right"><a id="SettingsSave" class="button positive"><span class="inner"><span class="label" nowrap="">Save</span></span></a></td> 
  </tr>
 </tbody> 
</table>
</form> 
