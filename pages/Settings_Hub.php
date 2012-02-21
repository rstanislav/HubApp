<div class="head">Settings <small style="font-size: 12px;">(<a href="#!/Help/Main">?</a>)</small></div>

<form id="SettingsForm" name="SettingsHub" method="post" action="load.php?page=SaveSettings">
<input type="hidden" name="SettingSection" value="Hub" />
<div id="form-wrap">
 <dl>
 
  <dt>Hub</dt>
  <dd>
   <div class="field">
    <label>
     <?php
     $LocalIP = ($Settings['SettingHubLocalIP']) ? $Settings['SettingHubLocalIP'] : '127.0.0.1';
     $LocalIPArr = explode('.', $LocalIP);
     	
     echo '<input name="SettingHubLocalIP[1]" class="ip" type="text" value="'.$LocalIPArr[0].'" />.'.
     	  '<input name="SettingHubLocalIP[2]" class="ip" type="text" value="'.$LocalIPArr[1].'" />.'.
     	  '<input name="SettingHubLocalIP[3]" class="ip" type="text" value="'.$LocalIPArr[2].'" />.'.
     	  '<input name="SettingHubLocalIP[4]" class="ip" type="text" value="'.$LocalIPArr[3].'" />'."\n";
     ?>
     <!--<input name="SettingHubLocalIP" type="text" value="<?php echo $Settings['SettingHubLocalIP']; ?>" />//-->
     <span>Local IP</span>
    </label>
   </div>
   
   <div class="field">
    <label>
     <?php
     $KillSwitchChecked = ($Settings['SettingHubKillSwitch']) ? ' checked="checked"' : '';
     echo '<input name="SettingHubKillSwitch" type="checkbox"'.$KillSwitchChecked.' />'."\n";
     ?>
     <span>Prevent Hub from running in the background</span>
    </label>
   </div>
  </dd>
  
  <div style="float: right">
   <a rel="SettingsSave" class="button positive"><span class="inner"><span class="label" nowrap="">Save</span></span></a>
  </div>
            
  <dd class="clear"></dd>
  
  <dt>General</dt>
  <dd>
   <div class="field">
    <label>
     <select name="SettingHubMinimumActiveDiskPercentage" id="MinActiveDiskPercentage">
     <?php
     $PercentArr = array(1, 2, 3, 4, 5, 10, 15, 20, 30);
     
     foreach($PercentArr AS $Percent) {
     	$PercentSelected = ($Percent == $Settings['SettingHubMinimumActiveDiskPercentage']) ? ' selected="selected"' : '';
     	echo '<option value="'.$Percent.'%"'.$PercentSelected.'>'.$Percent.'%</option>'."\n";
     }
     ?>
     </select>
     <span>Minimum free space required on active disk</span>
    </label>
   </div>
  
   <div class="field">
    <label>
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
     </select> to 
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
     <span>Quality range</span>
    </label>
   </div>
  </dd>
  
  <div style="float: right">
   <a rel="SettingsSave" class="button positive"><span class="inner"><span class="label" nowrap="">Save</span></span></a>
  </div>
            
  <dd class="clear"></dd>
  
  <dt>Remote Services</dt>
  <dd>
   <div class="field">
    <label>
     <input name="SettingHubTheTVDBAPIKey" type="text" value="<?php echo $Settings['SettingHubTheTVDBAPIKey']; ?>" />
     <span>TheTVDB API key</span>
    </label>
   </div>
  
   <div class="field">
    <span class="info">TheTVDB is the scraper service used to retrieve information about TV series</span>
    <a target="_blank" href="#">Apply here</a>
   </div>
  
   <div class="field">
    <label>
     <input type="text" name="" value="" placeholder="Dropbox Username" />
     <span>Dropbox Username</span>
    </label>
   </div>
  
   <div class="field">
    <label>
     <input type="text" name="" value="" placeholder="Dropbox Password" />
     <span>Dropbox Password</span>
    </label>
   </div>
   
   <div class="field">
    <span class="info">Dropbox is used for sharing your wishlist and backing up your XBMC database</span>
    <!--<a target="_blank" href="#">Apply here</a>//-->
   </div>
  </dd>
  
  <div style="float: right">
   <a rel="SettingsSave" class="button positive"><span class="inner"><span class="label" nowrap="">Save</span></span></a>
  </div>
  
  <dd class="clear"></dd>
  
  <dt>Backup</dt>
  <dd>
   <div class="field">
    <label>
     <?php
     $BackupChecked = ($Settings['SettingHubBackup']) ? ' checked="checked"' : '';
     echo '<input name="SettingHubBackup" type="checkbox"'.$BackupChecked.' />'."\n";
     ?>
     <span>Backup Hub files</span>
    </label>
   </div>
   
   <div class="field">
    <label>
     <?php
     //$BackupChecked = ($Settings['SettingDatabaseBackup']) ? ' checked="checked"' : '';
     echo '<input name="SettingDatabaseBackup" type="checkbox"'.$BackupChecked.' />'."\n";
     ?>
     <span>Backup Hub database</span>
    </label>
   </div>
   
   <div class="field">
    <label>
     <?php
     //$BackupChecked = ($Settings['SettingXBMCBackup']) ? ' checked="checked"' : '';
     echo '<input name="SettingXBMCBackup" type="checkbox"'.$BackupChecked.' />'."\n";
     ?>
     <span>Backup XBMC files</span>
    </label>
   </div>
  </dd>
  
  <div style="float: right">
   <a rel="SettingsSave" class="button positive"><span class="inner"><span class="label" nowrap="">Save</span></span></a>
  </div>
            
  <dd class="clear"></dd>
  
 </dl>
 <div class="clear"></div>
</div>
</form>