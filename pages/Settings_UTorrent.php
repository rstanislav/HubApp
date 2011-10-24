<div class="head">Settings <small style="font-size: 12px;">(<a href="#!/Help/Main">?</a>)</small></div>

<form id="SettingsForm" name="SettingsUTorrent" method="post" action="load.php?page=SaveSettings">
<input type="hidden" name="SettingSection" value="UTorrent" />
<div id="form-wrap">
 <dl>
 
  <dt>Web UI</dt>
  <dd>
   <div class="field">
    <label>
     <?php
     $UTorrentAPIHostname = ($Settings['SettingUTorrentHostname']) ? $Settings['SettingUTorrentHostname'] : 'localhost';
     echo '<input name="SettingUTorrentHostname" type="text" value="'.$UTorrentAPIHostname.'" />'."\n";
     ?>
     <span>IP Address</span>
    </label>
   </div>
   
   <div class="field">
    <label>
     <input name="SettingUTorrentPort" type="text" value="<?php echo $Settings['SettingUTorrentPort']; ?>" />
     <span>Port</span>
    </label>
   </div>
   
   <div class="field">
    <label>
     <input name="SettingUTorrentUsername" type="text" value="<?php echo $Settings['SettingUTorrentUsername']; ?>" />
     <span>Username</span>
    </label>
   </div>
   
   <div class="field">
    <label>
     <input name="SettingUTorrentPassword" type="text" value="<?php echo $Settings['SettingUTorrentPassword']; ?>" />
     <span>Password</span>
    </label>
   </div>
  </dd>
  
  <div style="float: right">
   <a id="SettingsSave" class="button positive"><span class="inner"><span class="label" nowrap="">Save</span></span></a>
  </div>
            
  <dd class="clear"></dd>
  
  <dt>Miscellaneous</dt>
  <dd>
   <div class="field">
    <label>
     <input name="SettingUTorrentWatchFolder" type="text" value="<?php echo $Settings['SettingUTorrentWatchFolder']; ?>" />
     <span>Watch folder</span>
    </label>
   </div>
  
   <div class="field">
    <label>
     <input name="SettingUTorrentDefaultDownSpeed" style="width: 40px; text-align:center" type="text" value="<?php echo $Settings['SettingUTorrentDefaultDownSpeed']; ?>" /> / 
     <input name="SettingUTorrentDefaultUpSpeed" style="width: 40px; text-align:center" type="text" value="<?php echo $Settings['SettingUTorrentDefaultUpSpeed']; ?>" />
     <span>Regular down/up speed in KiB/s</span>
    </label>
   </div>
  
   <div class="field">
    <label>
     <input name="SettingUTorrentDefinedDownSpeed" style="width: 40px; text-align:center" type="text" value="<?php echo $Settings['SettingUTorrentDefinedDownSpeed']; ?>" /> / 
     <input name="SettingUTorrentDefinedUpSpeed" style="width: 40px; text-align:center" type="text" value="<?php echo $Settings['SettingUTorrentDefinedUpSpeed']; ?>" />
     <span>Limited down/up speed in KiB/s</span>
    </label>
   </div>
  </dd>
  
  <div style="float: right">
   <a id="SettingsSave" class="button positive"><span class="inner"><span class="label" nowrap="">Save</span></span></a>
  </div>
  
  <dd class="clear"></dd>
  
 </dl>
 <div class="clear"></div>
</div>
</form>