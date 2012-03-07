<div class="head">Settings <small style="font-size: 12px;">(<a href="#!/Help/Main">?</a>)</small></div>

<form id="SettingsForm" name="SettingsHub" method="post" action="load.php?page=SaveSettings">
<input type="hidden" name="SettingSection" value="Hub" />
<div id="form-wrap">
 <dl>
 
  <dt>Hub</dt>
  <dd>
   <div class="field">
    <label>
     <input name="SettingHubLocalHostname" type="text" value="<?php echo $Settings['SettingHubLocalHostname']; ?>" placeholder="Local hostname" />
     <span>Local hostname</span>
    </label>
   </div>
   
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
     <select name="SettingHubMinimumActiveDiskFreeSpaceInGB" id="MinimumActiveDiskFreeSpaceInGB">
     <?php
     $GBArr = array(5, 10, 15, 20, 30, 40, 50, 75, 100);
     
     foreach($GBArr AS $GB) {
     	$GBSelected = ($GB == $Settings['SettingHubMinimumActiveDiskFreeSpaceInGB']) ? ' selected="selected"' : '';
     	echo '<option value="'.$GB.'"'.$GBSelected.'>'.$GB.' GB</option>'."\n";
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
  
  <dt>Torrent Search</dt>
  <dd>
   <div class="field">
    <label>
     <input type="text" name="SettingHubSearchURITVSeries" value="<?php echo $Settings['SettingHubSearchURITVSeries']; ?>" placeholder="http://" />
     <span>Search URI for tv series</span>
    </label>
   </div>
   
   <div class="field">
    <label>
     <input type="text" name="SettingHubSearchURIMovies" value="<?php echo $Settings['SettingHubSearchURIMovies']; ?>" placeholder="http://" />
     <span>Search URI for movies</span>
    </label>
   </div>
   
   <div class="field">
    <span class="info">
     For example: <strong>http://torrentz.eu/search?f=<em>{QUERY}</em></strong>
    </span>
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