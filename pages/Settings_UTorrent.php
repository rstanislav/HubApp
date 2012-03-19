<div class="head">Settings &raquo; uTorrent  <small style="font-size: 12px;">(<a href="#!/Help/Main">?</a>)</small></div>

<form id="SettingsForm" name="SettingsUTorrent" method="post" action="load.php?page=SaveSettings">
<input type="hidden" name="SettingSection" value="UTorrent" />
<div id="form-wrap">
 <dl>
 
  <dt>Web UI</dt>
  <dd>
   <div class="field">
	<label>
	 <?php
	 $UTorrentIP = ($HubObj->GetSetting('UTorrentIP')) ? $HubObj->GetSetting('UTorrentIP') : '127.0.0.1';
	 $UTorrentIPArr = explode('.', $UTorrentIP);
	 	
	 echo '<input name="UTorrentIP[1]" class="ip" type="text" value="'.$UTorrentIPArr[0].'" />.'.
	 	  '<input name="UTorrentIP[2]" class="ip" type="text" value="'.$UTorrentIPArr[1].'" />.'.
	 	  '<input name="UTorrentIP[3]" class="ip" type="text" value="'.$UTorrentIPArr[2].'" />.'.
	 	  '<input name="UTorrentIP[4]" class="ip" type="text" value="'.$UTorrentIPArr[3].'" />'."\n";
	 ?>
	 <span>IP Address</span>
	</label>
   </div>
   
   <div class="field">
	<label>
	 <input name="UTorrentPort" type="text" value="<?php echo $HubObj->GetSetting('UTorrentPort'); ?>" />
	 <span>Port</span>
	</label>
   </div>
   
   <div class="field">
	<label>
	 <input name="UTorrentUsername" type="text" value="<?php echo $HubObj->GetSetting('UTorrentUsername'); ?>" />
	 <span>Username</span>
	</label>
   </div>
   
   <div class="field">
	<label>
	 <input name="UTorrentPassword" type="text" value="<?php echo $HubObj->GetSetting('UTorrentPassword'); ?>" />
	 <span>Password</span>
	</label>
   </div>
  </dd>
  
  <div style="float: right">
   <a rel="SettingsSave" class="button positive"><span class="inner"><span class="label" nowrap="">Save</span></span></a>
  </div>
			
  <dd class="clear"></dd>
  
  <dt>Miscellaneous</dt>
  <dd>
   <div class="field">
	<label>
	 <input name="UTorrentWatchFolder" type="text" value="<?php echo $HubObj->GetSetting('UTorrentWatchFolder'); ?>" />
	 <span>Watch folder</span>
	</label>
   </div>
  
   <div class="field">
	<label>
	 <input name="UTorrentDefaultDownSpeed" style="width: 40px; text-align:center" type="text" value="<?php echo $HubObj->GetSetting('UTorrentDefaultDownSpeed'); ?>" /> / 
	 <input name="UTorrentDefaultUpSpeed" style="width: 40px; text-align:center" type="text" value="<?php echo $HubObj->GetSetting('UTorrentDefaultUpSpeed'); ?>" />
	 <span>Regular down/up speed in KiB/s</span>
	</label>
   </div>
  
   <div class="field">
	<label>
	 <input name="UTorrentDefinedDownSpeed" style="width: 40px; text-align:center" type="text" value="<?php echo $HubObj->GetSetting('UTorrentDefinedDownSpeed'); ?>" /> / 
	 <input name="UTorrentDefinedUpSpeed" style="width: 40px; text-align:center" type="text" value="<?php echo $HubObj->GetSetting('UTorrentDefinedUpSpeed'); ?>" />
	 <span>Limited down/up speed in KiB/s</span>
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