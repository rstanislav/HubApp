<div class="head">Settings &raquo; XBMC  <small style="font-size: 12px;">(<a href="#!/Help/Main">?</a>)</small></div>

<form id="SettingsForm" name="SettingsXBMC" method="post" action="load.php?page=SaveSettings">
<input type="hidden" name="SettingSection" value="XBMC" />
<div id="form-wrap">
 <dl>
 
  <dt>XBMC</dt>
  <dd>
   <div class="field">
    <label>
     <input name="XBMCDataFolder" style="width: 400px" type="text" value="<?php echo $HubObj->GetSetting('XBMCDataFolder'); ?>" />
     <span>XBMC data folder</span>
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