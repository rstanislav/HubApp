<div class="head">Settings &raquo; Hub <small style="font-size: 12px;">(<a href="#!/Help/Main">?</a>)</small></div>

<form id="SettingsForm" name="SettingsHub" method="post" action="load.php?page=SaveSettings">
<input type="hidden" name="SettingSection" value="Hub" />
<div id="form-wrap">
 <dl>
 
  <dt>Hub</dt>
  <dd>
   <div class="field">
    <label>
     <input name="LocalHostname" type="text" value="<?php echo $HubObj->GetSetting('LocalHostname'); ?>" placeholder="Local hostname" />
     <span>Local hostname</span>
    </label>
   </div>
   
   <div class="field">
    <label>
     <?php
     $LocalIP = ($HubObj->GetSetting('LocalIP')) ? $HubObj->GetSetting('LocalIP') : '127.0.0.1';
     $LocalIPArr = explode('.', $LocalIP);
     	
     echo '<input name="LocalIP[1]" class="ip" type="text" value="'.$LocalIPArr[0].'" />.'.
     	  '<input name="LocalIP[2]" class="ip" type="text" value="'.$LocalIPArr[1].'" />.'.
     	  '<input name="LocalIP[3]" class="ip" type="text" value="'.$LocalIPArr[2].'" />.'.
     	  '<input name="LocalIP[4]" class="ip" type="text" value="'.$LocalIPArr[3].'" />'."\n";
     ?>
     <span>Local IP</span>
    </label>
   </div>
   
   <div class="field">
    <label>
     <?php
     $KillSwitchChecked = ($HubObj->GetSetting('KillSwitch')) ? ' checked="checked"' : '';
     echo '<input name="KillSwitch" type="checkbox"'.$KillSwitchChecked.' />'."\n";
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
     <select name="MinimumDiskSpaceRequired" id="MinimumActiveDiskFreeSpaceInGB">
     <?php
     $GBArr = array(5, 10, 15, 20, 30, 40, 50, 75, 100);
     
     foreach($GBArr AS $GB) {
     	$GBSelected = ($GB == $HubObj->GetSetting('MinimumDiskSpaceRequired')) ? ' selected="selected"' : '';
     	echo '<option value="'.$GB.'"'.$GBSelected.'>'.$GB.' GB</option>'."\n";
     }
     ?>
     </select>
     <span>Minimum free space required on active disk</span>
    </label>
   </div>
  
   <div class="field">
    <label>
     <select name="MinimumDownloadQuality" id="MinDownloadQuality">
     <?php
     $MinQualityArr = array(5000  => 'HDTV/DVDRip',
                            6000  => 'BRRip/BDRip',
                            10000 => '480p',
                            20000 => '540p',
                            30000 => '720p',
                            40000 => '810p',
                            50000 => '1080p');
     
     foreach($MinQualityArr AS $MinQualityVal => $MinQualityText) {
     	$MinQualitySelected = ($MinQualityVal == $HubObj->GetSetting('MinimumDownloadQuality')) ? ' selected="selected"' : '';
     	echo '<option value="'.$MinQualityVal.'"'.$MinQualitySelected.'>'.$MinQualityText.'</option>'."\n";
     }
     ?>
     </select> to 
     <select name="MaximumDownloadQuality" id="MaxDownloadQuality">
     <?php
     $MaxQualityArr = array(5999  => 'HDTV/DVDRip',
                            6999  => 'BRRip/BDRip',
                            19999 => '480p',
                            29999 => '540p',
                            39999 => '720p',
                            49999 => '810p',
                            59999 => '1080p');
     
     foreach($MaxQualityArr AS $MaxQualityVal => $MaxQualityText) {
     	$MaxQualitySelected = ($MaxQualityVal == $HubObj->GetSetting('MaximumDownloadQuality')) ? ' selected="selected"' : '';
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
     <input type="text" name="SearchURITVSeries" value="<?php echo $HubObj->GetSetting('SearchURITVSeries'); ?>" placeholder="http://" />
     <span>Search URI for tv series</span>
    </label>
   </div>
   
   <div class="field">
    <label>
     <input type="text" name="SearchURIMovies" value="<?php echo $HubObj->GetSetting('SearchURIMovies'); ?>" placeholder="http://" />
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
     <input name="TheTVDBAPIKey" type="text" value="<?php echo $HubObj->GetSetting('TheTVDBAPIKey'); ?>" />
     <span>TheTVDB API key</span>
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