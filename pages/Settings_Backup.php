<div class="head">Settings &raquo; Backup <small style="font-size: 12px;">(<a href="#!/Help/Main">?</a>)</small></div>

<?php 
if(is_dir($HubObj->GetSetting('BackupFolder'))) {
	echo '
	<div class="notification information">
	 Your backup folder is currently: '.$HubObj->BytesToHuman($ExtractFilesObj->GetDirectorySize($HubObj->GetSetting('BackupFolder'))).'
	</div>'."\n";
}
?>

<form id="SettingsForm" name="SettingsBackup" method="post" action="load.php?page=SaveSettings">
<input type="hidden" name="SettingSection" value="Backup" />
<div id="form-wrap">
 <dl>
  
  <dt>Backup</dt>
  <dd>
   
   <div class="field">
    <label>
     <select name="BackupAge">
     <?php
     $DaysArr = array(1, 2, 3, 4, 5, 6, 7, 14, 21, 28);
    
     foreach($DaysArr AS $Days) {
    	 $DaySelected = ($Days == $HubObj->GetSetting('BackupAge')) ? ' selected="selected"' : '';
    	 $DaysText = ($Days == 1) ? 'day' : 'days';
    	 echo '<option value="'.$Days.'"'.$DaySelected.'>'.$Days.' '.$DaysText.'</option>'."\n";
     }
     ?>
     </select>
     <span>How many days you wish to keep your backups</span>
    </label>
   </div>
   
   <div class="field">
    <label>
     <input type="text" name="BackupFolder" value="<?php echo $HubObj->GetSetting('BackupFolder'); ?>" placeholder="Backup folder" />
     <span>Backup folder. Preferably inside a <a href="http://dropbox.com" target="_blank">Dropbox</a> folder or on a network location</span>
    </label>
   </div>
   
   <div class="field">
    <label>
     <?php
     $BackupChecked = ($HubObj->GetSetting('BackupHubFiles')) ? ' checked="checked"' : '';
     echo '<input name="BackupHubFiles" type="checkbox"'.$BackupChecked.' />'."\n";
     ?>
     <span>Backup Hub files <span style="color: red">CAUTION: Can result in very large files</span></span>
    </label>
   </div>
   
   <div class="field">
    <label>
     <?php
     $BackupChecked = ($HubObj->GetSetting('BackupHubDatabase')) ? ' checked="checked"' : '';
     echo '<input name="BackupHubDatabase" type="checkbox"'.$BackupChecked.' />'."\n";
     ?>
     <span>Backup Hub database</span>
    </label>
   </div>
   
   <div class="field">
    <label>
     <?php
     $BackupChecked = ($HubObj->GetSetting('BackupXBMCFiles')) ? ' checked="checked"' : '';
     echo '<input name="BackupXBMCFiles" type="checkbox"'.$BackupChecked.' />'."\n";
     ?>
     <span>Backup XBMC files <span style="color: red">CAUTION: Can result in very large files</span></span>
    </label>
   </div>
   
   <div class="field">
    <label>
     <?php
     $BackupChecked = ($HubObj->GetSetting('BackupXBMCDatabase')) ? ' checked="checked"' : '';
     echo '<input name="BackupXBMCDatabase" type="checkbox"'.$BackupChecked.' />'."\n";
     ?>
     <span>Backup XBMC databases (music and video)</span>
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