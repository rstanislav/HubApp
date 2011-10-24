<div class="head">Settings <small style="font-size: 12px;">(<a href="#!/Help/Main">?</a>)</small></div>

<form id="SettingsForm" name="SettingsXBMC" method="post" action="load.php?page=SaveSettings">
<input type="hidden" name="SettingSection" value="XBMC" />
<div id="form-wrap">
 <dl>
 
  <dt>XBMC</dt>
  <dd>
   <div class="field">
    <label>
     <input name="SettingXBMCLogFile" style="width: 400px" type="text" value="<?php echo $Settings['SettingXBMCLogFile']; ?>" />
     <span>Log file</span>
    </label>
   </div>
   
   <div class="field">
    <label>
     <input name="SettingXBMCSourcesFile" style="width: 400px" type="text" value="<?php echo $Settings['SettingXBMCSourcesFile']; ?>" />
     <span>Sources.xml</span>
    </label>
   </div>
   
   <div class="field">
    <label>
     <input name="SettingXBMCRSSFile" style="width: 400px" type="text" value="<?php echo $Settings['SettingXBMCRSSFile']; ?>" />
     <span>RSS_Feeds.xml</span>
    </label>
   </div>
   
   <div class="field">
    <label>
     <input name="SettingXBMCDatabaseFolder" style="width: 400px" type="text" value="<?php echo $Settings['SettingXBMCDatabaseFolder']; ?>" />
     <span>Database folder</span>
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