<?php
ini_set('error_reporting', E_ALL);
session_start();
ob_start();

require_once './resources/config.php';
require_once './libraries/libraries.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 

<html> 
<head>
 <meta http-equiv="Content-type" content="text/html; charset=utf-8" /> 
 <title>Hub</title> 
 
 <?php
 if(!$UserObj->LoggedIn) {
 	echo '<link type="text/css" rel="stylesheet" href="css/login.css" />'."\n";
 }
 else {
 	echo '
 	<link type="text/css" rel="stylesheet" href="css/stylesheet.css" />
 	<link type="text/css" rel="stylesheet" href="css/jquery.qtip.css" />
 	<link type="text/css" rel="stylesheet" href="css/jquery.selectBox.css" />'."\n";
 }
 ?>
 
 <noscript> 
  <meta http-equiv="Refresh" content="0;url=http://www.google.com/enable_javascript/" />
 </noscript> 
     
 <link rel="icon" href="favicon.ico" type="image/x-icon" /> 
 <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" /> 

 <script type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
 <script type="text/javascript" src="js/jquery-ui-1.8.12.custom.min.js"></script>
 <script type="text/javascript" src="js/jquery.qtip.js"></script>
 <script type="text/javascript" src="js/jquery.contextMenu-1.01.js"></script>
 <script type="text/javascript" src="js/jquery.alerts.js"></script>
 <script type="text/javascript" src="js/jquery.address-1.4.min.js"></script>
 <script type="text/javascript" src="js/selectToUISlider.jQuery.js"></script>
 <script type="text/javascript" src="js/jquery.form.js"></script>
 <script type="text/javascript" src="js/jquery.jeditable.js"></script>
 <script type="text/javascript" src="js/jquery.timers.js"></script>
 <script type="text/javascript" src="js/jquery.selectBox.js"></script>
 <script type="text/javascript" src="js/hub.script.js"></script>
</head>

<body>

<?php
if(!$UserObj->LoggedIn) {
	include_once './pages/Login.php';
}
else {
?>
<div id="loading">
 <img class="spinner" src="images/blank.gif"/> 
 <span class="spinnertext"> Loading...</span>
</div>

<div id="upload-wrapper">
 <div id="upload-inner">
  Drop files to upload
 </div>
</div>

<div id="error" style="display:none">
 <img src="images/alerts/confirm.png" />
 <div class="error-head">Page missing!</div>
  
  The file "random.php" does not exist
 </div>
</div>

<table class="main">
 <tr>
  <td class="header left">
   <a href="#!/"><img src="images/logo.png" /></a>
   <img src="images/blank.gif" id="divider" />
  </td>
  <td class="header middle">
   <?php
   if($UserObj->CheckPermission($UserObj->UserGroupID, 'Search')) {
   	echo '<input type="search" id="search" placeholder="Search..." results="5" />'."\n";
   }
   ?>
  </td>
  <td class="header right">
   <?php
   if($UserObj->CheckPermission($UserObj->UserGroupID, 'ZoneSwitch')) {
   ?>
   <form method="post" action="">
   <select name="zoneSelect" id="zoneSelect">
    <?php
    $Zones = $ZonesObj->GetZones();
    foreach($Zones AS $Zone) {
    	$ZoneSelected = ($Zone['ZoneName'] == $HubObj->CurrentZone) ? ' selected="selected"' : '';
    	
    	echo '<option value=""'.$ZoneSelected.'>'.$Zone['ZoneName'].'</option>'."\n";
    }
    ?>
   </select>
   </form>
   <?php
   }
   else {
       echo '<span style="padding-right:10px; color: white; font-weight: bold">'.$HubObj->CurrentZone.'</span>';
   }
   ?>
  </td>
 </tr>
 <tr>
  <td id="navigation">
   <div id="navbuttons">
    <span id="LockStatus"></span>
    <?php
   	if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewStatistics')) {
   		//echo '<a rel="Statistics" href="#!/Statistics"><img id="IconStat" src="images/icons/statistics_dark.png" /></a>'."\n";
   	}
   	if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewSettings')) {
   		echo '<a rel="Settings" href="#!/Settings"><img id="IconSettings" src="images/icons/settings_dark.png" /></a>'."\n";
   	}
   	if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewUsers')) {
   		echo '<a rel="Users" href="#!/Users"><img id="IconUsers" src="images/icons/users_dark.png" /></a>'."\n";
   	}
   	?>
   	<a rel="Logout" href="#!/Logout"><img id="IconLogout" src="images/icons/logout_dark.png" /></a>
   </div>
   <ul>
    <?php
    if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewDrives')) {
    	echo '<li class="drive"><a rel="Drives" href="#!/Drives">Drives</a></li>'."\n";
    }
    if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewSeries')) {
    	echo '<li class="series"><a rel="Series" href="#!/Series">Series</a></li>'."\n";
    }
    if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewMovies')) {
    	echo '<li class="movies"><a rel="Movies" href="#!/Movies">Movies</a></li>'."\n";
    }
    if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewWishlist')) {
    	echo '<li class="wishlist"><a rel="Wishlist" href="#!/Wishlist">Wishlist</a><span id="WishlistBadge"></span></li>'."\n";
    }
    if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewUnsortedFiles')) {
    	echo '<li class="unsorted"><a rel="UnsortedFiles" href="#!/UnsortedFiles">Unsorted Files</a></li>'."\n";
    }
    if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewExtractFiles')) {
    	echo '<li class="extract"><a rel="ExtractFiles" href="#!/ExtractFiles">Extract Files</a></li>'."\n";
    }
    if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewHubLog')) {
    	echo '<li class="log"><a rel="HubLog" href="#!/HubLog">Log</a></li>'."\n";
    }
    ?>
   </ul>
       
   <h3>XBMC</h3>
   <ul>
    <?php
    if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewXBMCCP')) {
    	echo '<li class="control-panel"><a rel="XBMCControlPanel" href="#!/XBMCCP">Control Panel</a></li>'."\n";
    }
    if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewXBMCZones')) {
    	echo '<li class="zones"><a rel="XBMCZones" href="#!/XBMCZones">Zones</a></li>'."\n";
    }
    if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewXBMCScreenshots')) {
    	echo '<li class="screenshot"><a rel="XBMCScreenshots" href="#!/XBMCScreenshots">Screenshots</a></li>'."\n";
    }
    if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewXBMCLog')) {
    	echo '<li class="log"><a rel="XBMCLog" href="#!/XBMCLog">Log</a></li>'."\n";
    }
    ?>
   </ul>
       
   <?php
   if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewUTorrentCP')) {
       echo '
   	   <h3>uTorrent</h3>
   	   <ul>
   	    <li class="control-panel"><a rel="UTorrentControlPanel" href="#!/uTorrentCP">Control Panel</a><span id="UTorrentBadge"></span></li>
   	   </ul>'."\n";
   }
   
   if($UserObj->CheckPermission($UserObj->UserGroupID, 'ViewRSSFeed')) {
       echo '
   	   <h3>RSS Feeds</h3>  
   	   <ul>
   	    <li class="control-panel"><a rel="RSSCP" href="#!/RSSCP">Control Panel</a></li>'."\n";
   	   
   	   $RSSFeeds = $RSSObj->GetRSSFeeds();
   	   if(is_array($RSSFeeds)) {
   	   		foreach($RSSFeeds AS $RSSFeed) {
   	   			echo '<li class="feeds"><a rel="RSS" href="#!/RSS/'.$RSSFeed['RSSTitle'].'">'.$RSSFeed['RSSTitle'].'</a><span id="RSS-'.$RSSFeed['RSSID'].'"></span></li>'."\n";
   	   		}
   	   }
   	   echo '
   	   </ul>'."\n";
   }
   ?>
  </td>
  <td colspan="2" id="maincontent"></td>
 </tr>
</table>
<?php
}
?>
</body>	
</html> 