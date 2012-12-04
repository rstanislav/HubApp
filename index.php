<?php
ini_set('max_execution_time', (60 * 60 * 5));

require_once './resources/config.php';
require_once APP_PATH.'/api/resources/functions.php';
require_once APP_PATH.'/resources/api.hub.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 

<html> 
<head>
 <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
 <title>Hub</title>
 
 <link type="text/css" rel="stylesheet" href="css/stylesheet.css" />
 <link type="text/css" rel="stylesheet" href="css/jquery.noty.css" />
 
 <script type="text/javascript" src="js/jquery-1.8.2.min.js"></script>
 <script type="text/javascript" src="js/jquery.noty.js"></script>
 
 <!--<link rel="shortcut icon" href="images/favicon.ico" />//-->
 <link rel="apple-touch-icon" href="images/logo-iphone.png" />
 <link rel="apple-touch-icon" sizes="72x72" href="images/logo-ipad.png" />
 <link rel="apple-touch-icon" sizes="114x114" href="images/logo-iphone4.png" />
 <link rel="apple-touch-icon" sizes="144x144" href="images/logo-ipad3.png" />
</head>

<body>

<script type="text/javascript">
$(document).ready(function() {
	$('div[id|="Cover"]').mouseover(function() {
		$('#CoverControl-' + $(this).attr('id').split('-')[1]).css('display', 'block');
	}).mouseout(function() {
		$('#CoverControl-' + $(this).attr('id').split('-')[1]).css('display', 'none');
	});
	
	$('a[rel="ajax"]').click(function() {
		Action   = $(this).attr('id').split('-');
		SecondID = Action[2];
		FirstID  = Action[1];
		Action   = Action[0];
		OriginalImg  = $(this).html();
		ImageObj = this;
		
		switch(Action) {
			case 'TorrentStart':
				AjaxImage('utorrent/start/' + FirstID, ImageObj, OriginalImg);
			break;
			
			case 'TorrentStop':
				AjaxImage('utorrent/stop/' + FirstID, ImageObj, OriginalImg);
			break;
			
			case 'TorrentPause':
				AjaxImage('utorrent/pause/' + FirstID, ImageObj, OriginalImg);
			break;
			
			case 'TorrentDelete':
				AjaxImage('utorrent/remove/' + FirstID, ImageObj, OriginalImg);
			break;
			
			case 'TorrentDeleteData':
				AjaxImage('utorrent/remove/data/' + FirstID, ImageObj, OriginalImg);
			break;
			
			case 'SerieRefresh':
				AjaxImage('series/refresh/' + FirstID, ImageObj, OriginalImg);
			break;
			
			case 'SerieDelete':
				AjaxImage('series/' + FirstID, ImageObj, OriginalImg, 'delete');
			break;
			
			case 'ZoneDelete':
				AjaxImage('xbmc/zones/' + FirstID, ImageObj, OriginalImg, 'delete');
			break;
			
			case 'FilePlay':
				AjaxImage('xbmc/default/play', ImageObj, OriginalImg, 'post', { File: $(this).attr('id').replace(new RegExp(Action + '-', 'i'), '') });
			break;
			
			case 'FileDelete':
				console.log(Action + ' ' + $(this).attr('id').replace(new RegExp(Action + '-', 'i'), ''));
			break;
			
			case 'WishlistDelete':
				AjaxImage('wishlist/' + FirstID, ImageObj, OriginalImg, 'delete');
			break;
			
			case 'DriveRemove':
				AjaxImage('drives/' + FirstID, ImageObj, OriginalImg, 'delete');
			break;
			
			case 'TorrentDownload':
				if(SecondID) {
					SecondID = '/' + SecondID;
				}
				else {
					SecondID = '';
				}
				
				AjaxImage('rss/download/' + FirstID + SecondID, ImageObj, OriginalImg);
			break;
			
			case 'FeedDelete':
				AjaxImage('rss/' + FirstID, ImageObj, OriginalImg, 'delete');
			break;
			
			default:
				console.log(Action + ' ' + FirstID + ' default action');
		}
	});
	
	$('a[class*="button"]').click(function() {
		ID = $(this).attr('id');
		ButtonObj = this;
		ButtonVal = $(this).contents().find('.label').text();
		
		if($(this).hasClass('regular'))  ButtonClass = 'regular';
		if($(this).hasClass('positive')) ButtonClass = 'positive';
		if($(this).hasClass('negative')) ButtonClass = 'negative';
		if($(this).hasClass('blue'))     ButtonClass = 'blue';
		if($(this).hasClass('neutral'))  ButtonClass = 'neutral';
		if(!ButtonClass)                 ButtonClass = 'positive';
		
		if(ID) {
			Pattern = /[A-z]+\-[0-9]+/i;
			Result  = Pattern.test(ID);
			
			if(Result) {
				Action = ID.split('-');
				ID = Action[1];
				Action = Action[0];
				
				switch(Action) {
					case 'SerieAdd':
						AjaxButton('series/add/' + ID, ButtonObj, 'Adding ...', ButtonClass, ButtonVal);
					break;
					
					case 'SerieRefresh':
						AjaxButton('series/refresh/' + ID, ButtonObj, 'Refreshing ...', ButtonClass, ButtonVal);
					break;
					
					case 'SerieSpelling':
						console.log(Action);
					break;
					
					case 'SerieDelete':
						AjaxButton('series/' + ID, ButtonObj, 'Deleting ...', ButtonClass, ButtonVal, 'delete');
					break;
					
					case 'TorrentDownload':
						AjaxButton('rss/download/' + ID, ButtonObj, 'Downloading ...', ButtonClass, ButtonVal);
					break;
					
					default:
						console.log(Action + ' default action');
				}
			}
			else {
				switch(ID) {
					case 'SerieRefreshAll':
						AjaxButton('series/refresh/all', ButtonObj, 'Refreshing ...', ButtonClass, ButtonVal);
					break;
					
					case 'EpisodesRebuild':
						AjaxButton('series/rebuild/episodes', ButtonObj, 'Rebuilding ...', ButtonClass, ButtonVal);
					break;
					
					case 'FoldersRebuild':
						AjaxButton('series/rebuild/folders', ButtonObj, 'Rebuilding ...', ButtonClass, ButtonVal);
					break;
					
					case 'SharedMoviesUpdate':
						console.log(ID);
					break;
					
					case 'MovieTogglePath':
						console.log(ID);
					break;
					
					case 'WishlistUpdateShared':
						console.log(ID);
					break;
					
					case 'WishlistAddItem':
						WishlistID = randomString();
						
						$('#tbl-wishlist tbody tr:first').before(
						    '<tr id="' + WishlistID + '">' +
						     '<td>Now</td>' +
						     '<td><input name="Title" style="width:250px" type="text" /></td>' +
						     '<td><input name="Year" style="width:30px" type="text" /></td>' +
						     '<td id="action-' + WishlistID + '" style="text-align:right">' +
						      '<a onclick="javascript:AjaxPost(\'WishlistAddItem\', \'' + WishlistID + '\');"><img src="images/icons/add.png" /></a>' +
						      '<a onclick="javascript:$(\'#' + WishlistID + '\').remove();"><img src="images/icons/delete.png" /></a>' +
						     '</td>' +
						    '</tr>');
					break;
					
					case 'WishlistRefresh':
						AjaxButton('wishlist/refresh', ButtonObj, 'Refreshing ...', ButtonClass, ButtonVal);
					break;
					
					case 'DriveAdd':
						DriveID = randomString();
						
						$('#tbl-drives tbody tr:first').before(
						    '<tr id="' + DriveID + '">' +
						     '<td>Now</td>' +
						     '<td><input name="Share" style="width:250px" type="text" /></td>' +
						     '<td><input name="User" style="width:30px" type="text" /></td>' +
						     '<td><input name="Password" style="width:30px" type="text" /></td>' +
						     '<td><input name="Mount" style="width:30px" type="text" /></td>' +
						     '<td><input name="IsNetwork" type="hidden" value="0" />0</td>' +
						     '<td>0</td>' +
						     '<td>0</td>' +
						     '<td id="action-' + DriveID + '" style="text-align:right">' +
						      '<a onclick="javascript:AjaxPost(\'DriveAddItem\', \'' + DriveID + '\');"><img src="images/icons/add.png" /></a>' +
						      '<a onclick="javascript:$(\'#' + DriveID + '\').remove();"><img src="images/icons/delete.png" /></a>' +
						     '</td>' +
						    '</tr>');
					break;
					
					case 'XBMCLibraryUpdate':
						AjaxButton('xbmc/library/update', ButtonObj, 'Updating ...', ButtonClass, ButtonVal);
					break;
					
					case 'XBMCLibraryClean':
						AjaxButton('xbmc/library/clean', ButtonObj, 'Cleaning ...', ButtonClass, ButtonVal);
					break;
					
					case 'XBMCPlayerTogglePlayback':
						if(ButtonVal == 'Pause') {
							ButtonVal = 'Play';
							ButtonLoadVal = 'Pausing ...';
						}
						else if(ButtonVal == 'Play') {
							ButtonVal = 'Pause';
							ButtonLoadVal = 'Playing ...';
						}
						
						AjaxButton('xbmc/default/play', ButtonObj, ButtonLoadVal, ButtonClass, ButtonVal);
					break;
					
					case 'XBMCPlayerStop':
						AjaxButton('xbmc/default/stop', ButtonObj, 'Stopping ...', ButtonClass, ButtonVal);
					break;
					
					case 'ZoneAdd':
						ZoneID = randomString();
						
						$('#tbl-zones tbody tr:first').before(
						    '<tr id="' + ZoneID + '">' +
						     '<td>Now</td>' +
						     '<td><input name="Name" style="width:250px" type="text" /></td>' +
						     '<td><input name="Host" style="width:90px" type="text" /></td>' +
						     '<td><input name="Port" style="width:30px" type="text" /></td>' +
						     '<td><input name="User" style="width:50px" type="text" /></td>' +
						     '<td><input name="Password" style="width:60px" type="text" /></td>' +
						     '<td id="action-' + ZoneID + '" style="text-align:right">' +
						      '<a onclick="javascript:AjaxPost(\'ZonesAddItem\', \'' + ZoneID + '\');"><img src="images/icons/add.png" /></a>' +
						      '<a onclick="javascript:$(\'#' + ZoneID + '\').remove();"><img src="images/icons/delete.png" /></a>' +
						     '</td>' +
						    '</tr>');
					break;
					
					case 'TorrentStartAll':
						AjaxButton('utorrent/start/all', ButtonObj, 'Starting ...', ButtonClass, ButtonVal);
					break;
					
					case 'TorrentPauseAll':
						AjaxButton('utorrent/pause/all', ButtonObj, 'Pausing ...', ButtonClass, ButtonVal);
					break;
					
					case 'TorrentStopAll':
						AjaxButton('utorrent/stop/all', ButtonObj, 'Stopping ...', ButtonClass, ButtonVal);
					break;
					
					case 'TorrentRemoveFinished':
						AjaxButton('utorrent/remove/finished', ButtonObj, 'Removing ...', ButtonClass, ButtonVal);
					break;
					
					case 'TorrentRemoveAll':
						AjaxButton('utorrent/remove/data/all', ButtonObj, 'Removing ...', ButtonClass, ButtonVal);
					break;
					
					case 'RSSUpdate':
						AjaxButton('rss/refresh', ButtonObj, 'Updating ...', ButtonClass, ButtonVal);
					break;
					
					case 'RSSAddFeed':
						FeedID = randomString();
						
						$('#tbl-feeds tbody tr:first').before(
						    '<tr id="' + FeedID + '">' +
						     '<td>Now</td>' +
						     '<td><input name="Title" style="width:250px" type="text" /></td>' +
						     '<td><input name="Feed" style="width:300px" type="text" /></td>' +
						     '<td id="action-' + FeedID + '" style="text-align:right">' +
						      '<a onclick="javascript:AjaxPost(\'FeedAddItem\', \'' + FeedID + '\');"><img src="images/icons/add.png" /></a>' +
						      '<a onclick="javascript:$(\'#' + FeedID + '\').remove();"><img src="images/icons/delete.png" /></a>' +
						     '</td>' +
						    '</tr>');
					break;
					
					case 'MovieCoverCache':
						AjaxButton('xbmc/movies/cachecovers', ButtonObj, 'Caching ...', ButtonClass, ButtonVal);
					break;
					
					default:
						console.log(ID + ' default');
				}
			}
		}
	});
	
	$('#search').focus(function() {
		$('#search').animate({ width:'400px' }, { queue: false, duration: 200 });
		
		$('#search').blur(function() {
			if($(this).attr('value') == '') {
				$(this).attr('placeholder', 'Search ...');
				
				$(this).animate({ width:'100px' }, { queue: false, duration: 200 });
			}
		});
	});
	
	$('#search').keypress(function(event) {
		if(event.which == '13') {
			event.preventDefault();
			
			if($(this).attr('value') != 'Search ...' && $(this).attr('value') != '') {
				window.location = '?Page=Search&Search=' + escape($(this).attr('value'));
			}
	   	}
	});
});

function AjaxPost(Action, RowID) {
	switch(Action) {
		case 'WishlistAddItem':
			URL = 'wishlist';
		break;
		
		case 'DriveAddItem':
			URL = 'drives';
		break;
		
		case 'ZonesAddItem':
			URL = 'xbmc/zones';
		break;
		
		case 'FeedAddItem':
			URL = 'rss';
		break;
	}
	
	switch(Action) {
		default:
			var Data = '{';
			$('#' + RowID).contents().each(function(index) {
				Name = $(this).contents().attr('name');
				Value = $(this).contents().attr('value');
				
				ImageObj = $(this).contents().find('img').first();
				
				if(Name != undefined && Value != undefined) {
					Data = Data + '"' + Name + '": "' + Value + '",';
				}
			});
			Data = Data.slice(0, -1) + '}';
	}
	
	$.ajax({
		type: 	'post',
		url:    'api/' + URL,
		data:   $.parseJSON(Data),
		beforeSend: function() {
			$(ImageObj).attr('src', 'images/spinners/ajax-light.gif');
		},
		success: function(data, textStatus, jqXHR) {
			$('#' + RowID).contents().each(function(index) {
				Name = $(this).contents().attr('name');
				Value = $(this).contents().attr('value');
				
				if(Name != undefined && Value != undefined) {
					$(this).html($(this).contents().attr('value'));
				}
			});
			
			$('#action-' + RowID).html('');
			
			noty({
				text: data.error.message,
				type: 'success',
				timeout: 3000,
			});
		},
		error: function(jqXHR, textStatus, errorThrown) {
			$(ImageObj).attr('src', 'images/icons/error.png');
		    
		    var responseObj = JSON.parse(jqXHR.responseText);
		    noty({
		    	text: responseObj.error.message,
		    	type: 'error',
		    	timeout: false,
		    });
		}
	});
}

function randomString() {
	var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
	var string_length = 8;
	var randomstring = '';
	for(var i = 0; i < string_length; i++) {
		var rnum = Math.floor(Math.random() * chars.length);
		randomstring += chars.substring(rnum, rnum+1);
	}
	
	return randomstring;
}

function AjaxButton(URL, ButtonObj, BeforeText, ButtonClass, ButtonVal, Method, Data) {
	if(Method == undefined) {
		Method = 'get';
	}
	
	$.ajax({
		type: 	Method,
		url:    '/api/' + URL,
		data:   Data,
		beforeSend: function() {
			$(ButtonObj).removeClass(ButtonClass).addClass('disabled');
			$(ButtonObj).contents().find('.label').text(BeforeText);
		},
		success: function(data, textStatus, jqXHR) {
			$(ButtonObj).removeClass('disabled').addClass(ButtonClass);
			$(ButtonObj).contents().find('.label').text(ButtonVal);
			
			noty({
				text: data.error.message,
				type: 'success',
				timeout: 3000,
			});
		},
		error: function(jqXHR, textStatus, errorThrown) {
			$(ButtonObj).removeClass('disabled').addClass(ButtonClass);
		    $(ButtonObj).contents().find('.label').text('Error!');
		    
		    var responseObj = JSON.parse(jqXHR.responseText);
		    noty({
		    	text: responseObj.error.message,
		    	type: 'error',
		    	timeout: false,
		    });
		}
	});
}

function AjaxImage(URL, ImageObj, OriginalImg, Method, Data) {
	if(Method == undefined) {
		Method = 'get';
	}
	
	$.ajax({
		type: 	Method,
		url:    'api/' + URL,
		data:   Data,
		beforeSend: function() {
			$(ImageObj).html('<img src="images/spinners/ajax-light.gif" />');
		},
		success: function(data, textStatus, jqXHR) {
			$(ImageObj).html(OriginalImg);		
			
			if(Method == 'delete') {
				$(ImageObj).parent().parent().remove();
			}
			
			noty({
				text: data.error.message,
				type: 'success',
				timeout: 3000,
			});
		},
		error: function(jqXHR, textStatus, errorThrown) {
			$(ImageObj).html('<img src="images/icons/error.png" />');
		    
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

<table class="main">
 <tr>
  <td class="header left">
   <a href="/"><img src="images/logo.png" title="Hub v<?php echo json_decode($Hub->Request('/hub/version')); ?>" /></a>
   <img src="images/blank.gif" id="divider" />
  </td>
  <td class="header middle">
   <input type="search" id="search" placeholder="Search..." results="5" />
  </td>
  <td class="header right">
   <?php
   $Zones = json_decode($Hub->Request('/xbmc/zones'));
   
   if(is_array($Zones)) {
   		echo '<select name="zoneSelect" id="zoneSelect" class="blue">'."\n";
    
    	foreach($Zones AS $Zone) {
    		$Selected = $Zone->IsDefault ? ' selected="selected"' : '';
    		echo '<option value="'.$Zone->Name.'"'.$Selected.'>'.$Zone->Name.'</option>'."\n";
    	}
    	
   		echo '</select>'."\n";
   }
   ?>
  </td>
 </tr>
 <tr>
  <td id="navigation">
   <div id="navbuttons">
    <span id="LockStatus"></span>
    <span id="TorrentSpeedSetting"></span>
    <a href="?Page=Settings"><img id="IconSettings" src="images/icons/settings_dark.png" /></a>
    <a href="?Page=Profile"><img id="IconProfile" src="images/icons/profile_dark.png" /></a>
    <a href="?Page=Users"><img id="IconUsers" src="images/icons/users_dark.png" /></a>
   	<a href="?Page=Logout"><img id="IconLogout" src="images/icons/logout_dark.png" /></a>
   </div>
   <ul>
    <li class="series"><a href="?Page=Series">Series</a></li>
    <li class="movies"><a href="?Page=Movies">Movies</a></li>
    <li class="wishlist"><a href="?Page=Wishlist">Wishlist</a><span id="WishlistBadge"></span></li>
    <li class="drive"><a href="?Page=Drives">Drives</a></li>
    <li class="extract"><a href="?Page=ExtractFiles">Extract Files</a></li>
    <li class="log"><a href="?Page=Log">Log</a></li>
   </ul>
       
   <h3>XBMC</h3>
   <ul>
    <li class="control-panel"><a href="?Page=XBMCCP">Control Panel</a></li>
    <li class="zones"><a href="?Page=Zones">Zones</a></li>
    <li class="log"><a href="?Page=XBMCLog">Log</a></li>
   </ul>
       
   <h3>uTorrent</h3>
   <ul>
   	<li class="control-panel"><a href="?Page=UTorrentCP">Control Panel</a><span id="UTorrentBadge"></span></li>
   </ul>
   
   <h3>RSS Feeds</h3>  
   <ul>
   	<li class="control-panel"><a href="?Page=RSSCP">Control Panel</a></li>
   	<?php 
   	$Feeds = json_decode($Hub->Request('rss/'));
   	
   	if(is_array($Feeds)) {
   		foreach($Feeds AS $Feed) {
   			echo '<li class="feeds"><a href="?Page=RSS&ID='.$Feed->ID.'">'.$Feed->Title.'</a><span id="RSS-'.$Feed->ID.'"></span></li>'."\n";
   		}
   	}
   	?>
   </ul>
  </td>
  <td colspan="2" id="maincontent">
   <?php
   if(filter_has_var(INPUT_GET, 'Page')) {
   	$Page = $_GET['Page'];
   }
   else {
   	$Page = '';
   }
   
   switch($Page) {
   	case 'Series':
   		include_once APP_PATH.'/pages/Series.php';
   	break;
   	
   	case 'Movies':
   		include_once APP_PATH.'/pages/Movies.php';
   	break;
   	
   	case 'Wishlist':
   		include_once APP_PATH.'/pages/Wishlist.php';
   	break;
   	
   	case 'Drives':
   		include_once APP_PATH.'/pages/Drives.php';
   	break;
   	
   	case 'FileManager':
   		include_once APP_PATH.'/pages/FileManager.php';
   	break;
   	
   	case 'ExtractFiles':
   		include_once APP_PATH.'/pages/ExtractFiles.php';
   	break;
   	
   	case 'Log':
   		include_once APP_PATH.'/pages/Log.php';
   	break;
   	
   	case 'XBMCCP':
   		include_once APP_PATH.'/pages/XBMCCP.php';
   	break;
   	
   	case 'Zones':
   		include_once APP_PATH.'/pages/XBMCZones.php';
   	break;
   	
   	case 'XBMCLog':
   		include_once APP_PATH.'/pages/XBMCLog.php';
   	break;
   	
   	case 'UTorrentCP':
   		include_once APP_PATH.'/pages/UTorrentCP.php';
   	break;
   	
   	case 'RSSCP':
   		include_once APP_PATH.'/pages/RSSCP.php';
   	break;
   	
   	case 'RSS':
   		include_once APP_PATH.'/pages/RSS.php';
   	break;
   	
   	case 'Search':
   		include_once APP_PATH.'/pages/Search.php';
   	break;
   	
   	case 'Schedule':
   	default:
   		include_once APP_PATH.'/pages/Schedule.php';
   }
   ?>
  </td>
 </tr>
</table>

</body>	
</html> 