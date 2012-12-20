<?php
require_once '../resources/config.php';
require_once APP_PATH.'/api/resources/functions.php';
require_once APP_PATH.'/resources/api.hub.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 

<html>
<head>
 <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
 <title>Hub - Install</title>
 
 <link type="text/css" rel="stylesheet" href="../css/jquery.noty.css" />
 
 <script type="text/javascript" src="../js/jquery-1.8.2.min.js"></script>
 <script type="text/javascript" src="../js/jquery.noty.js"></script>
</head>

<body>

<script type="text/javascript">
$(document).ready(function() {
    $('form').submit(function(){ return false; });
    
    $('#submit1').click(function() {
    	$('#step1').hide();
    	$('#step2').fadeIn();
    });
    
    $('#submit2').click(function() {
    	AjaxPost('xbmc/check/folder', { XBMCDataFolder: $('input[name=XBMCDataFolder]').val() }, '2', '3');
    });

    $('#submit3').click(function() {
    	AjaxPost('xbmc/check/zone', { XBMCZoneName: $('input[name=XBMCZoneName]').val(),
    	                              XBMCZoneHost: $('input[name=XBMCZoneHost]').val(),
    	                              XBMCZonePort: $('input[name=XBMCZonePort]').val(),
    	                              XBMCZoneUser: $('input[name=XBMCZoneUser]').val(),
    	                              XBMCZonePassword: $('input[name=XBMCZonePassword]').val()
    	                            }, '3', '4');
    });

    $('#submit4').click(function() {
    	AjaxPost('utorrent/check', { UTorrentIP: $('input[name=UTorrentIP]').val(),
    	                             UTorrentPort: $('input[name=UTorrentPort]').val(),
    	                             UTorrentUsername: $('input[name=UTorrentUsername]').val(),
    	                             UTorrentPassword: $('input[name=UTorrentPassword]').val(),
    	                             UTorrentWatchFolder: $('input[name=UTorrentWatchFolder]').val(),
    	                             UTorrentDefaultUpSpeed: $('input[name=UTorrentDefaultUpSpeed]').val(),
    	                             UTorrentDefaultDownSpeed: $('input[name=UTorrentDefaultDownSpeed]').val(),
    	                             UTorrentDefinedUpSpeed: $('input[name=UTorrentDefinedUpSpeed]').val(),
    	                             UTorrentDefinedDownSpeed: $('input[name=UTorrentDefinedDownSpeed]').val()
    	                           }, '4', '5');
    });

    $('#submit5').click(function() {
    	AjaxPost('hub/check/settings', { MinimumDownloadQuality: $('select[name=MinimumDownloadQuality] :selected').attr('value'),
    	                                 MaximumDownloadQuality: $('select[name=MaximumDownloadQuality] :selected').attr('value'),
    	                                 MinimumDiskSpaceRequired: $('select[name=MinimumDiskSpaceRequired] :selected').attr('value'),
    	                                 TheTVDBAPIKey: $('input[name=TheTVDBAPIKey]').val(),
    	                                 SearchURIMovies: $('input[name=SearchURIMovies]').val(),
    	                                 SearchURITVSeries: $('input[name=SearchURITVSeries]').val()
    	                               }, '5', '6');
    });
    
    $('#submit6').click(function() {
    	AjaxPost('drives/check', { Share: $('input[name=Share]').val(),
    	                           User: $('input[name=User]').val(),
    	                           Password: $('input[name=Password]').val(),
    	                           Mount: $('input[name=Mount]').val(),
    	                           IsNetwork: $('input[name=IsNetwork]').val()
    	                         }, '6', '7');
    });
    
    $('#submit7').click(function() {
    	AjaxPost('hub/check/backup', '', '7', '8');
    });
    
    $('#submit8').click(function() {
		AjaxPost('hub/check/share', '', '8');
    });
});

function AjaxPost(URL, Data, StepNow, StepNext) {
	$.ajax({
		type: 	'post',
		url:    '../api/' + URL,
		data:   Data,
		beforeSend: function() {
			$('#submit' + StepNow).attr('value', 'Trying ...');
		},
		success: function(data, textStatus, jqXHR) {
			$('#step' + StepNow).hide();
			
			if(StepNext != undefined) {
				$('#step' + StepNext).fadeIn();
			}
			else {
				//
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			$('#submit' + StepNow).attr('value', 'Failed. Try again');
				
			var responseObj = JSON.parse(jqXHR.responseText);
		    noty({
		    	text: responseObj.error.message,
		    	type: 'error',
		    	timeout: false,
		    });
		}
	});
}
</script>

<style>
body {
	font-family: Helvetica;
	font-size: 14px;
}
table {
	width: 1100px;
}

thead tr th {
	text-align: left;
}

thead tr {
	background-color: #aaa;
}

tbody tr:nth-child(odd) {
	background-color: #ddd;
}

td:nth-child(1) {
	width: 300px;
}

td:nth-child(2) {
	vertical-align: top;
	width: 300px;
}

td:nth-child(3) {
	vertical-align: top;
	width: 500px;
}

#step1, #step2, #step3, #step4, #step5, #step7, #step8 {
	display: none;
} 
</style>

<div id="step1">
 <h4>Hub Dependencies</h4>
 <table id="dependencies">
  <thead>
   <tr>
    <th>Description</th>
    <th>Status</th>
    <th>Purpose</th>
   </tr>
  </thead>
  
  <?php 
  $RequiredExtensions = array('cURL'      => 'Communicating with several APIs',
                              'RAR'       => 'Extracting downloaded files',
                              'MySQL'     => 'Storing information',
                              'GD'        => 'Making thumbnails',
                              'PDO'       => 'Abstraction layer for the database',
                              'SimpleXML' => 'Parsing RSS XML feeds',
                              'JSON'      => 'Parsing API results',
                              'PDO_MySQL' => 'Abstraction layer for the database',
                              'Zip'       => 'Compressing backup files');
                    
  $ExtError = FALSE;          
  foreach($RequiredExtensions AS $Ext => $ExtDesc) {
      if(!extension_loaded($Ext)) {
          $ExtError = TRUE;
          $Loaded = '<img src="../images/icons/error.png" />';
      }
      else {
      	  $ExtError = FALSE;
      	  $Loaded = '<img src="../images/icons/check.png" />';
      }
      
      echo '
      <tr>
       <td>'.$Ext.'</td>
       <td>'.$Loaded.'</td>
       <td>'.$ExtDesc.'</td>
      </tr>'."\n";
  }
          
  ?>
  <tr>
   <td colspan="3">
    <?php
    if($ExtError) {
    	echo 'Fix the above mentioned errors before you can continue with the installation';
    }
    else {
    	echo '<input type="submit" value="Continue" id="submit1" />';
    }
    ?>
   </td>
  </tr>
 </table>
</div>

<div id="step2">
 <h4>XBMC</h4>
 <table id="xbmc">
  <thead>
   <tr>
    <th>Description</th>
    <th>Setting</th>
    <th>Example</th>
   </tr>
  </thead>
  <tr>
   <td>
    <label>Data Folder</label><br />
    <small></small>
   </td>
   <td><input type="text" name="XBMCDataFolder" /></td>
   <td>C:/Users/&lt;user&gt;/AppData/Roaming/XBMC</td>
  </tr>
  <tr>
   <td colspan="3"><input type="submit" value="Continue" id="submit2" /></td>
  </tr>
 </table>
</div>

<div id="step3">
 <h4>Zones</h4>
 <table id="zones">
  <thead>
   <tr>
    <th>Description</th>
    <th>Setting</th>
    <th>Example</th>
   </tr>
  </thead>
  <tr>
   <td><label>Zone Name</label></td>
   <td><input type="text" name="XBMCZoneName" /></td>
   <td>Living Room, Garage, etc</td>
  </tr>
  <tr>
   <td><label>Zone Host</label></td>
   <td><input type="text" name="XBMCZoneHost" /></td>
   <td>127.0.0.1</td>
  </tr>
  <tr>
   <td><label>Zone Port</label></td>
   <td><input type="text" name="XBMCZonePort" /></td>
   <td>1234</td>
  </tr>
  <tr>
   <td><label>Zone User</label></td>
   <td><input type="text" name="XBMCZoneUser" /></td>
   <td></td>
  </tr>
  <tr>
   <td><label>Zone Password</label></td>
   <td><input type="text" name="XBMCZonePassword" /></td>
   <td></td>
  </tr>
  <tr>
   <td colspan="3"><input type="submit" value="Continue" id="submit3" /></td>
  </tr>
 </table>
</div>

<div id="step4">
 <h4>uTorrent</h4>
 <table id="utorrent">
  <thead>
   <tr>
    <th>Description</th>
    <th>Setting</th>
    <th>Example</th>
   </tr>
  </thead>
  <tr>
   <td><label>Host</label></td>
   <td><input type="text" name="UTorrentIP" /></td>
   <td>127.0.0.1</td>
  </tr>
   <td><label>Port</label></td>
   <td><input type="text" name="UTorrentPort" /></td>
   <td>1234</td>
  </tr>
  <tr>
   <td><label>User</label></td>
   <td><input type="text" name="UTorrentUsername" /></td>
   <td></td>
  </tr>
  <tr>
   <td><label>Password</label></td>
   <td><input type="text" name="UTorrentPassword" /></td>
   <td></td>
  </tr>
  <tr>
   <td>
    <label>Watch Folder</label><br />
    <small>Where uTorrent will look for torrent files to add</small>
   </td>
   <td><input type="text" name="UTorrentWatchFolder" /></td>
   <td>C:/Torrents</td>
  </tr>
  <tr>
   <td>
    <label>Default Up Speed</label><br />
    <small>Download speed cap when speed limiter is disabled</small>
   </td>
   <td><input type="text" name="UTorrentDefaultUpSpeed" /> kB/s</td>
   <td>10</td>
  </tr>
  <tr>
   <td>
    <label>Default Down Speed</label><br />
    <small>Download speed cap when speed limiter is disabled</small>
   </td>
   <td><input type="text" name="UTorrentDefaultDownSpeed" /> kB/s</td>
   <td>0</td>
  </tr>
  <tr>
   <td>
    <label>Limited Up Speed</label><br />
    <small>Upload speed cap when speed limiter is enabled</small>
   </td>
   <td><input type="text" name="UTorrentDefinedUpSpeed" /> kB/s</td>
   <td>10</td>
  </tr>
  <tr>
   <td>
    <label>Limited Down Speed</label><br />
    <small>Download speed cap when speed limiter is enabled</small>
   </td>
   <td><input type="text" name="UTorrentDefinedDownSpeed" /> kB/s</td>
   <td>10</td>
  </tr>
  <tr>
   <td colspan="3"><input type="submit" value="Continue" id="submit4" /></td>
  </tr>
 </table>
</div>

<div id="step5">
 <h4>Hub</h4>
 <table id="hub">
  <thead>
   <tr>
    <th>Description</th>
    <th>Setting</th>
    <th>Example</th>
   </tr>
  </thead>
  <tr>
   <td>
    <label>Minimum Download Quality</label><br />
    <small>Minimum media quality Hub will download automatically</small>
   </td>
   <td>
    <select name="MinimumDownloadQuality">
     <?php
     $MaxQualityArr = array(5999  => 'HDTV/DVDRip',
     						6999  => 'BRRip/BDRip',
     						19999 => '480p',
     						29999 => '540p',
     						39999 => '720p',
     						49999 => '810p',
     						59999 => '1080p');
     
     foreach($MaxQualityArr AS $MaxQualityVal => $MaxQualityText) {
     	echo '<option value="'.$MaxQualityVal.'">'.$MaxQualityText.'</option>'."\n";
     }
     ?>
    </select>
   </td>
   <td></td>
  </tr>
  <tr>
   <td>
    <label>Maximum Download Quality</label><br />
    <small>Maximum media quality Hub will download automatically</small>
   </td>
   <td>
    <select name="MaximumDownloadQuality">
     <?php
     foreach($MaxQualityArr AS $MaxQualityVal => $MaxQualityText) {
     	echo '<option value="'.$MaxQualityVal.'">'.$MaxQualityText.'</option>'."\n";
     }
     ?>
    </select>
   </td>
   <td></td>
  </tr>
  <tr>
   <td>
    <label>Minimum Disk Space Required</label><br />
    <small>When this limit is reached, Hub will choose a new disk to download files to</small>
   </td>
   <td>
    <select name="MinimumDiskSpaceRequired">
     <option value="5">5 GB</option>
     <option value="10">10 GB</option>
     <option value="15">15 GB</option>
     <option value="20">20 GB</option>
    </select>
   </td>
   <td></td>
  </tr>
  <tr>
   <td>
    <label>TheTVDB API Key</label><br />
    <small>API key used for fetching information about tv series. <a href="http://thetvdb.com/?tab=apiregister" target="_blank">Available here</a> (login required)</small>
   </td>
   <td><input type="text" name="TheTVDBAPIKey" /></td>
   <td></td>
  </tr>
  <tr>
   <td>
    <label>Search URI for movies</label><br />
    <small>URI which will be used for automatic search links for movies</small>
   </td>
   <td><input type="text" name="SearchURIMovies" /></td>
   <td>http://thepiratebay.se/search/{QUERY}/0/99/201</td>
  </tr>
  <tr>
   <td>
    <label>Search URI for tv series</label><br />
    <small>URI which will be used for automatic search links for tv series</small>
   </td>
   <td><input type="text" name="SearchURITVSeries" /></td>
   <td>http://thepiratebay.se/search/{QUERY}/0/99/205</td>
  </tr>
  <tr>
   <td colspan="3"><input type="submit" value="Continue" id="submit5" /></td>
  </tr>
 </table>
</div>

<div id="step6">
 <h4>Hard Drive</h4>
 <table id="hdd">
  <thead>
   <tr>
    <th>Description</th>
    <th>Setting</th>
    <th>Example</th>
   </tr>
  </thead>
  <tr>
   <td>
    <label>Share</label><br />
    <small>Network share point</small>
   </td>
   <td><input type="text" name="Share" /></td>
   <td>//&lt;computer&gt;/&lt;shared-folder&gt;</td>
  </tr>
  <tr>
   <td><label>User</label></td>
   <td><input type="text" name="User" /></td>
   <td></td>
  </tr>
  <tr>
   <td><label>Password</label></td>
   <td><input type="text" name="Password" /></td>
   <td></td>
  </tr>
  <tr>
   <td>
    <label>Mount</label><br />
    <small>Local mount point</small>
   </td>
   <td><input type="text" name="Mount" /></td>
   <td>D:</td>
  </tr>
  <tr>
   <td><label>Network Drive</label></td>
   <td><input type="checkbox" name="IsNetwork" /></td>
   <td></td>
  </tr>
  <tr>
   <td colspan="3"><input type="submit" value="Continue" id="submit6" /></td>
  </tr>
 </table>
</div>

<div id="step7">
 <h4>Backup</h4>
 <table id="backup">
  <thead>
   <tr>
    <th>Description</th>
    <th>Setting</th>
    <th>Example</th>
   </tr>
  </thead>
  <tr>
   <td><label>XBMC Database</label></td>
   <td><input type="checkbox" name="BackupXBMCDatabase" /></td>
   <td></td>
  </tr>
  <tr>
   <td><label>XBMC Files</label></td>
   <td><input type="checkbox" name="BackupXBMCFiles" /></td>
   <td></td>
  </tr>
  <tr>
   <td><label>Hub Database</label></td>
   <td><input type="checkbox" name="BackupHubDatabase" /></td>
   <td></td>
  </tr>
  <tr>
   <td><label>Hub Files</label></td>
   <td><input type="checkbox" name="BackupHubFiles" /></td>
   <td></td>
  </tr>
  <tr>
   <td>
    <label>Backup Folder</label><br />
    <small>Where the above mentioned backups will be stored on disk. Preferably a Dropbox folder</small>
   </td>
   <td><input type="text" name="BackupFolder" /></td>
   <td>C:/Dropbox/Hub</td>
  </tr>
  <tr>
   <td><label>Delete backups older than</label></td>
   <td>
    <select>
     <option>1</option>
     <option>2</option>
     <option>3</option>
     <option>4</option>
     <option>5</option>
     <option>6</option>
     <option>7</option>
    </select> days</td>
   <td></td>
  </tr>
  <tr>
   <td colspan="3"><input type="submit" value="Continue" id="submit7" /></td>
  </tr>
 </table>
</div>

<div id="step8">
 <h4>Share</h4>
 <table id="share">
  <thead>
   <tr>
    <th>Description</th>
    <th>Setting</th>
    <th>Example</th>
   </tr>
  </thead>
  <tr>
   <td>
    <label>Publicly share movies</label><br />
    <small>Automatically generate a list of your movies for showing friends/family</small>
   </td>
   <td><input type="checkbox" name="ShareMovies" /></td>
   <td></td>
  </tr>
  <tr>
   <td>
    <label>Publicly share wishlist</label><br />
    <small>Automatically generate a list of your wishes for showing friends/family</small>
   </td>
   <td><input type="checkbox" name="ShareWishlist" /></td>
   <td></td>
  </tr>
  <tr>
   <td colspan="3"><input type="submit" value="Finalize" id="submit8" /></td>
  </tr>
 </table>
</div>

</body>
</html>