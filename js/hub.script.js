$(document).ready(function() {
	$.ajaxSetup({
	//  timeout: 5000
	});
	
	$.address.init(function(event) {
		//$('form').address();
	}).change(function(event) {
		$('#xbmc-log td[rel=time]:first').stopTime();
		$('#utorrent').stopTime();
		$('#PastSchedule').stopTime();
		$('#FutureSchedule').stopTime();
		
		if(event.pathNames[0] == 'Search') {
			Search = escape(/[A-z0-9!-., #$\-/%]+$/i.exec(event.pathNames[1]));
			loadURL('Search&Search=' + Search);
			
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=Search&Search=' + Search + '&Result=TheTVDB',
				beforeSend: function() {
					$('#TVLoading').fadeIn('fast');
				},
				complete: function() {
					$('#TVLoading').hide();
				},
				success: function(html) {
					$('#SearchTVResult').fadeIn('slow');
					$('#SearchTVResult').html(html);
				}
			});
			
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=Search&Search=' + Search + '&Result=Torrents',
				beforeSend: function() {
					$('#TorrentLoading').fadeIn('fast');
				},
				complete: function() {
					$('#TorrentLoading').hide();
				},
				success: function(html) {
					$('#SearchTorrentResult').fadeIn('slow');
					$('#SearchTorrentResult').html(html);
				}
			});
			
		}
		else if(event.pathNames[0] == 'Password') {
			$.ajax({
				method: 'get',
				url:    'pages/ForgotPassword.php',
				success: function(html) {
					$('#login').html(html);
				}
			});
		}
		else if(event.pathNames[0] == 'Logout') {
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=Logout',
				success: function(html) {
					window.location='/hub/';
				}
			});
		}
		else if(event.pathNames[0] == 'Settings') {
			loadURL('Settings&SettingPage=' + /[A-z0-9!-., #$\-/%]+$/i.exec(event.pathNames[1]));
		}
		else if(event.pathNames[0] == 'Users') {
			loadURL('Users&UserPage=' + /[A-z0-9!-., #$\-/%]+$/i.exec(event.pathNames[1]));
		}
		else if(event.pathNames[0] == 'Series') {
			loadURL('Series&Serie=' + /[A-z0-9!-., #$\-/%]+$/i.exec(event.pathNames[1]));
		}
		else if(event.pathNames[0] == 'RSS') {
			loadURL('RSS&Feed=' + /[A-z0-9!-., #$\-/%]+$/i.exec(event.pathNames[1]) + '&Category=' + /[A-z0-9!-., #$\-/%]+$/i.exec(event.pathNames[2]));
		}
		else if(event.pathNames[0] == 'Help') {
			loadURL('Help&Topic=' + /[A-z0-9!-., #$\-/%]+$/i.exec(event.pathNames[1]));
		}
		else {
			loadURL($('[href="#!' + event.value + '"]').attr('rel'));
		}
	});
	
	UpdateBadge('Wishlist');
	UpdateBadge('UTorrent');
	
	$('span[id|="RSS"]').each(function(index) {
		Action = $(this).attr('id').split('-');
		ID = Action[1];
		Action = Action[0];
		
		UpdateBadge('RSS', ID);
	});
	
	LockStatus();
	
	$('#LockStatus').click(function() {
		jConfirm('Are you sure you wish to unlock Hub? Only do this if you are sure that it has stalled!', 'Unlock', function(response) {
			if(response) {
				$.ajax({
					method: 'get',
					url:    'load.php',
					data:   'page=Unlock',
					beforeSend: function() {
						$(this).html('<img src="images/spinners/ajax-light.gif" />');
					},
					success: function(Return) {
						if(Return != '') {
							$(this).html('<img src="images/icons/error.png" />');
						}
						else {
							$(this).html();
						}
					}
				});
			}
		});
	});
	
	$('#IconStat').mouseover(function() {
		$(this).attr('src', 'images/icons/statistics.png');
	}).mouseout(function() {
		$(this).attr('src', 'images/icons/statistics_dark.png');
	});
	
	$('#IconSettings').mouseover(function() {
		$(this).attr('src', 'images/icons/settings.png');
	}).mouseout(function() {
		$(this).attr('src', 'images/icons/settings_dark.png');
	});
	
	$('#IconLogout').mouseover(function() {
		$(this).attr('src', 'images/icons/logout.png');
	}).mouseout(function() {
		$(this).attr('src', 'images/icons/logout_dark.png');
	});
	
	$('#IconUsers').mouseover(function() {
		$(this).attr('src', 'images/icons/users.png');
	}).mouseout(function() {
		$(this).attr('src', 'images/icons/users_dark.png');
	});
	
	$('#search').focus(function() {
		$('#search').animate({ width:'400px' }, { queue: false, duration: 200 });
		
		$('#search').blur(function() {
			if($('#search').attr('value') == '') {
				$('#search').attr('placeholder', 'Search ...');
				
				$('#search').animate({ width:'100px' }, { queue: false, duration: 200 });
			}
		});
	});
	
	$('#search').keypress(function(event) {
		if(event.which == '13') {
	    	event.preventDefault();
	    	
	    	if($('#search').attr('value') != 'Search ...' && $('#search').attr('value') != '') {
	    		$.address.value('Search/' + escape($('#search').attr('value')));
	    	}
	   	}
	});
	
	$('select').selectBox({ menuTransition: 'slide' });
	
	$('#zone').each(function() {
		$(this).qtip( {
			content: {
				text: '<img class="spinner" src="images/blank.gif" /><span class="spinnertext"> Loading...</span>',
	            ajax: {
	               url: 'load.php?page=ZonesDropdown'
	            }
	        },
	        position: {
	            at: 'bottom right',
	            my: 'top left',
	            viewport: $(window)
	        },
	        show: {
	            event: 'mousedown',
	            solo: true
	        },
	        hide: {
	        	event: 'unfocus',
	            fixed: true,
	        },
	        style: {
	            tip: {
	            	corner: false
	            }
	        }
	    })
	      
	    .click(function() { return false; });
	});
	
	$('#zone').mousedown(function() {
		$('#zone').addClass('sel');
	});
	
	$('#zone').mouseup(function() {
		$('#zone').removeClass('sel');
	});
});

function loadURL(url) {
	if(url == undefined) {
		url = 'default';
	}
	
	$('#maincontent').hide();
	
	$.ajax({
		method: 'get',
		url:    'load.php',
		data:   'page=' + url,
		beforeSend: function() {
			$('#loading').fadeIn('fast');
		},
		complete: function() {
			$.getScript('js/hub.ajax.js');
			$('#loading').fadeOut('fast');
		},
		success: function(html) {
			$('#maincontent').fadeIn('slow');
			$('#maincontent').html(html);
		}
	});
}

function UpdateBadge(Badge, ID) {
	if(Badge == 'RSS') {
		$.ajax({
			method: 'get',
			url:    'load.php',
			data:   'page=Badge&Badge=' + Badge + '&ID=' + ID,
			
			success: function(html) {
				$('#RSS-' + ID).html(html);
			},
			timeout: 5000
		});
		
		setTimeout('UpdateBadge("RSS", ' + ID + ')', 5000);
	}
	else {
		$.ajax({
			method: 'get',
			url:    'load.php',
			data:   'page=Badge&Badge=' + Badge,
			
			success: function(html) {
				$('#' + Badge + 'Badge').html(html);
			},
			timeout: 5000
		});
		
		setTimeout('UpdateBadge("' + Badge + '")', 5000);
	}
}

function LockStatus() {
	$.ajax({
		method: 'get',
		url:    'load.php',
		data:   'page=LockStatus',
		
		success: function(html) {
			$('#LockStatus').html(html);
		},
		timeout: 5000
	});
	
	setTimeout('LockStatus()', 5000);
}

function SetCookie(name, value, expires, path, domain, secure) {
	// set time, it's in milliseconds
	var today = new Date();
	today.setTime(today.getTime());

	/*
	if the expires variable is set, make the correct
	expires time, the current script below will set
	it for x number of days, to make it for hours,
	delete * 24, for minutes, delete * 60 * 24
	*/
	if(expires) {
		expires = expires * 1000 * 60 * 60 * 24;
	}
	
	var expires_date = new Date(today.getTime() + (expires));

	document.cookie = name + "=" +escape(value) +
		((expires) ? ";expires=" + expires_date.toGMTString() : "") +
		((path)    ? ";path="    + path                       : "") +
		((domain)  ? ";domain="  + domain                     : "") +
		((secure)  ? ";secure"                                : "");
}

function GetCookie(check_name) {
	var a_all_cookies  = document.cookie.split(';');
	var a_temp_cookie  = '';
	var cookie_name    = '';
	var cookie_value   = '';
	var b_cookie_found = false; // set boolean t/f default f

	for(i = 0; i < a_all_cookies.length; i++) {
		// now we'll split apart each name=value pair
		a_temp_cookie = a_all_cookies[i].split('=');

		// and trim left/right whitespace while we're at it
		cookie_name = a_temp_cookie[0].replace(/^\s+|\s+$/g, '');

		// if the extracted name matches passed check_name
		if (cookie_name == check_name) {
			b_cookie_found = true;
			// we need to handle case where cookie has no value but exists (no = sign, that is):
			if (a_temp_cookie.length > 1) {
				cookie_value = unescape(a_temp_cookie[1].replace(/^\s+|\s+$/g, ''));
			}
			// note that in cases where cookie is initialized but no value, null is returned
			return cookie_value;
			break;
		}
		
		a_temp_cookie = null;
		cookie_name   = '';
	}
	
	if (!b_cookie_found) {
		return null;
	}
}

function DeleteCookie(name, path, domain) {
	if(GetCookie(name)) {
		document.cookie = name + "=" +
			((path)   ? ";path="   + path   : "") +
			((domain) ? ";domain=" + domain : "") +
			";expires=Thu, 01-Jan-1970 00:00:01 GMT";
	}
}