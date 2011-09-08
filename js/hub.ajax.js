$(document).ready(function() {
	$('#alert').click(function(event) {
		event.preventDefault();
		jAlert('You can use HTML, such as <strong>bold</strong>, <em>italics</em>, and <u>underline</u>!', 'Alert Dialog');
	});
				
	$('#confirm').click(function(event) {
		event.preventDefault();
		jConfirm('Can you confirm this?', 'Confirmation Dialog', function(response) {
			console.log('Confirmed: ' + response);
		});
	});
				
	$('#prompt').click(function(event) {
		event.preventDefault();
		jPrompt('Type something:', '', 'Prompt Dialog', function(response) {
			if(response) {
				console.log('You entered: ' + response);
			}
		});
	});
	
	$('#TorrentViewButton').click(function() {
		$('#TorrentViewForm').submit();
	});
	
	$('#TorrentViewForm').submit(function() {
		$.ajax({
			method: 'get',
			url:    'load.php',
			data:   'page=RSSCategories&' + $(this).serialize(),
			success: function(html) {
				loadURL('TLRSS&Category=undefined');
			}
		});
		
	  	return false;
	});
	
	$(
	'a[id|="SerieRefresh"],' +
	'a[id|="SerieSpelling"],' +
	'a[id|="SerieDelete"],' +
	'a[id|="ZoneDelete"],' +
	'a[id|="FoldersRebuild"],' +
	'a[id|="EpisodesRebuild"],' +
	'a[id|="SerieRefreshAll"],' +
	'a[id|="DriveActive"],' +
	'a[id|="DriveRemove"],' +
	'a[id|="MovieInfo"],' +
	'a[id|="MoviePlay"],' +
	'a[id|="FilePlay"],' +
	'a[id|="MovieDelete"],' +
	'a[id|="RSSUpdate"],' +
	'a[id|="DownloadTorrent"],' +
	'a[id|="SerieRefresh"],' +
	'a[id|="SerieSpelling"],' +
	'a[id|="SerieDelete"],' +
	'a[id|="TorrentStart"],' +
	'a[id|="TorrentStop"],' +
	'a[id|="TorrentPause"],' +
	'a[id|="TorrentDelete"],' +
	'a[id|="TorrentDeleteData"],' +
	'a[id|="RSSUpdate"],' +
	'a[id|="DownloadTorrent"],' +
	'a[id|="UserGroupEdit"],' +
	'a[id|="TorrentDownload"],' +
	'a[id|="WishlistDelete"],' +
	'a[id|="RSSFeedDelete"],' +
	'a[id|="TorrentStartAll"],' +
	'a[id|="TorrentPauseAll"],' +
	'a[id|="TorrentStopAll"],' +
	'a[id|="XBMCLibraryUpdate"],' +
	'a[id|="XBMCLibraryClean"],' +
	'a[id|="DeleteEpisode"]').click(function(event) {
		if($(this).hasClass('button')) {
			if(!$(this).hasClass('disabled')) {
				AjaxButton(this);
			}
		}
		else {
			AjaxLink(this);
		}
	});
	
	$('#seasons-button').click(function() {
		$('tr[id|="SerieSeason"], thead[id|="SerieSeasonHead"], thead[id|="SerieSeasonInfo"]').toggle();
		ButtonContent = $('#seasons-button').contents().find('.label').text();
		
		if(ButtonContent == 'All seasons') {
			NewButtonContent = 'Latest Season';
		}
		else {
			NewButtonContent = 'All Seasons';
		}
		$('#seasons-button').contents().find('.label').text(NewButtonContent);
	});
	
	$('a[id|="zoneSwitch"]').click(function(event) {
		ZoneID = $(this).attr('id').replace('zoneSwitch-', '');
		button = this;
		$.ajax({
			method: 'get',
			url:    'load.php',
			data:   'page=SwitchZone&ZoneID=' + ZoneID,
			beforeSend: function() {
				$(button).removeClass('positive').addClass('disabled');
				$(button).contents().find('.label').text('Switching ...');
			},
			success: function(html) {
				$(button).contents().find('.label').text('Current');
				
				$('a[id|="zoneSwitch"]').each(function(index) {
					button = this;
					if($(this).attr('id').replace('zoneSwitch-', '') != ZoneID) {
						$(this).contents().find('.label').each(function(index) {
							if($(this).text() == 'Current') {
								$(button).removeClass('disabled').addClass('positive');
								$(button).contents().find('.label').text('Switch to');
							}
						});
					}
				});
			}
		});
	});
	
	$('.context').contextMenu({
		menu: 'torrentControl'
	},
	function(action, el, pos) {
	    alert("Action: " + action + "\n\n" +
	          "Element ID: " + $(el).attr("id") + "\n\n" +
	          "X: " + pos.x + "  Y: " + pos.y + " (relative to element)\n\n" +
	          "X: " + pos.docX + "  Y: " + pos.docY + " (relative to document)"
	    );
	});
});

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

function ajaxSubmit(ID) {
	$('form[name=' + ID + ']').ajaxSubmit({
		success: ajaxSubmitResponse
	});
}

function ajaxSubmitResponse(responseText, statusText, xhr, $form)  {
	if(responseText == 'OK' || responseText == '') {
		$('#' + $form.attr('name') + ' input').each(function(index) {
			$(this).replaceWith($(this).val());
		});
		
		$('#' + $form.attr('name') + ' img').each(function(index) {
			if(index == 0) {
				$(this).replaceWith();
			}
		});
	}
	else {
		jAlert(responseText, 'Something went wrong...');
	}
}

function AjaxButton(Button, Extra) {
	Action = $(Button).attr('id').split('-');
	ID = Action[1];
	Action = Action[0];
	
	if(Extra != undefined) {
		ExtraParam = '&' + Extra;
	}
	else {
		ExtraParam = '';
	}
	
	ButtonObj = Button;
	ButtonVal = $(ButtonObj).contents().find('.label').text();
	
	if($(ButtonObj).hasClass('regular'))  ButtonClass = 'regular';
	if($(ButtonObj).hasClass('positive')) ButtonClass = 'positive';
	if($(ButtonObj).hasClass('negative')) ButtonClass = 'negative';
	if($(ButtonObj).hasClass('blue'))     ButtonClass = 'blue';
	if($(ButtonObj).hasClass('neutral'))  ButtonClass = 'neutral';
	if(!ButtonClass)                      ButtonClass = 'positive';
	
	switch(Action) {
		case 'XBMCLibraryUpdate':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=XBMCLibraryUpdate',
				beforeSend: function() {
					$(ButtonObj).removeClass(ButtonClass).addClass('disabled');
					$(ButtonObj).contents().find('.label').text('Updating ...');
				},
				success: function(Return) {
					if(Return != '') {
						$(ButtonObj).contents().find('.label').text('Error!');
					}
					else {
						$(ButtonObj).removeClass('disabled').addClass(ButtonClass);
						$(ButtonObj).contents().find('.label').text(ButtonVal);
					}
				}
			});
		break;
		
		case 'XBMCLibraryClean':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=XBMCLibraryClean',
				beforeSend: function() {
					$(ButtonObj).removeClass(ButtonClass).addClass('disabled');
					$(ButtonObj).contents().find('.label').text('Cleaning ...');
				},
				success: function(Return) {
					if(Return != '') {
						$(ButtonObj).contents().find('.label').text('Error!');
					}
					else {
						$(ButtonObj).removeClass('disabled').addClass(ButtonClass);
						$(ButtonObj).contents().find('.label').text(ButtonVal);
					}
				}
			});
		break;
		
		case 'TorrentStartAll':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=TorrentStartAll',
				beforeSend: function() {
					$(ButtonObj).removeClass(ButtonClass).addClass('disabled');
					$(ButtonObj).contents().find('.label').text('Starting ...');
				},
				success: function(Return) {
					if(Return != '') {
						$(ButtonObj).contents().find('.label').text('Error!');
					}
					else {
						$(ButtonObj).removeClass('disabled').addClass(ButtonClass);
						$(ButtonObj).contents().find('.label').text(ButtonVal);
					}
				}
			});
		break;
		
		case 'TorrentPauseAll':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=TorrentPauseAll',
				beforeSend: function() {
					$(ButtonObj).removeClass(ButtonClass).addClass('disabled');
					$(ButtonObj).contents().find('.label').text('Pausing ...');
				},
				success: function(Return) {
					if(Return != '') {
						$(ButtonObj).contents().find('.label').text('Error!');
					}
					else {
						$(ButtonObj).removeClass('disabled').addClass(ButtonClass);
						$(ButtonObj).contents().find('.label').text(ButtonVal);
					}
				}
			});
		break;
		
		case 'TorrentStopAll':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=TorrentStopAll',
				beforeSend: function() {
					$(ButtonObj).removeClass(ButtonClass).addClass('disabled');
					$(ButtonObj).contents().find('.label').text('Stopping ...');
				},
				success: function(Return) {
					if(Return != '') {
						$(ButtonObj).contents().find('.label').text('Error!');
					}
					else {
						$(ButtonObj).removeClass('disabled').addClass(ButtonClass);
						$(ButtonObj).contents().find('.label').text(ButtonVal);
					}
				}
			});
		break;
		
		case 'TorrentRemove':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=TorrentRemove' + ExtraParam,
				beforeSend: function() {
					$(ButtonObj).removeClass(ButtonClass).addClass('disabled');
					$(ButtonObj).contents().find('.label').text('Removing ...');
				},
				success: function(Return) {
					if(Return != '') {
						$(ButtonObj).contents().find('.label').text('Error!');
					}
					else {
						$(ButtonObj).removeClass('disabled').addClass(ButtonClass);
						$(ButtonObj).contents().find('.label').text('Remove All Finished');
					}
				}
			});
		break;
		
		break;
		
		case 'FoldersRebuild':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=FoldersRebuild',
				beforeSend: function() {
					$(ButtonObj).removeClass(ButtonClass).addClass('disabled');
					$(ButtonObj).contents().find('.label').text('Rebuilding ...');
				},
				success: function(Return) {
					if(Return != '') {
						$(ButtonObj).contents().find('.label').text('Error!');
					}
					else {
						$(ButtonObj).removeClass('disabled').addClass(ButtonClass);
						$(ButtonObj).contents().find('.label').text(ButtonVal);
					}
				}
			});
		break;
		
		case 'SerieAdd':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=SerieAdd&TheTVDBID=' + ID + ExtraParam,
				beforeSend: function() {
					$(ButtonObj).removeClass(ButtonClass).addClass('disabled');
					$(ButtonObj).contents().find('.label').text('Adding ...');
				},
				success: function(Return) {
					if(Return != '') {
						$(ButtonObj).contents().find('.label').text('Error!');
					}
					else {
						$(ButtonObj).contents().find('.label').text('Added!');
					}
				}
			});
		break;
		
		case 'SerieRefresh':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=SerieRefresh&SerieID=' + ID,
				beforeSend: function() {
					$(ButtonObj).removeClass(ButtonClass).addClass('disabled');
					$(ButtonObj).contents().find('.label').text('Refreshing ...');
				},
				success: function(Return) {
					if(Return != '') {
						$(ButtonObj).contents().find('.label').text('Error!');
					}
					else {
						$(ButtonObj).removeClass('disabled').addClass(ButtonClass);
						$(ButtonObj).contents().find('.label').text(ButtonVal);
					}
				}
			});
		break;
		
		case 'SerieSpelling':
			jPrompt('Type in a new alternate title for "' + $(ButtonObj).attr('rel') + '"', '', 'Alternate Title', function(response) {
				if(response) {
					$.ajax({
						method: 'get',
						url:    'load.php',
						data:   'page=SerieSpelling&SerieID=' + ID + '&Spelling=' + response,
						beforeSend: function() {
							$(ButtonObj).removeClass(ButtonClass).addClass('disabled');
							$(ButtonObj).contents().find('.label').text('Updating ...');
						},
						success: function(Return) {
							if(Return != 'OK') {
								$(ButtonObj).contents().find('.label').text('Error!');
							}
							else {
								$(ButtonObj).removeClass('disabled').addClass(ButtonClass);
								$(ButtonObj).contents().find('.label').text(ButtonVal);
							}
						}
					});
				}
			});
		break;
		
		case 'SerieDelete':
			jPrompt('Are you sure you want to delete "' + $(ButtonObj).attr('rel') + '" along with all episodes and folders?' + "\n\n" + 'Type "delete" to confirm', '', 'Delete Serie', function(response) {
				if(response == 'delete') {
					$.ajax({
						method: 'get',
						url:    'load.php',
						data:   'page=SerieDelete&SerieID=' + ID,
						beforeSend: function() {
							$(ButtonObj).removeClass(ButtonClass).addClass('disabled');
							$(ButtonObj).contents().find('.label').text('Deleting ...');
						},
						success: function(Return) {
							if(Return != '') {
								$(ButtonObj).contents().find('.label').text('Error!');
							}
							else {
								$('#Serie-' + ID).slideUp('slow').remove();
							}
						}
					});
				}
			});
		break;
		
		case 'EpisodesRebuild':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=EpisodesRebuild',
				beforeSend: function() {
					$(ButtonObj).removeClass(ButtonClass).addClass('disabled');
					$(ButtonObj).contents().find('.label').text('Rebuilding ...');
				},
				success: function(Return) {
					if(Return != '') {
						$(ButtonObj).contents().find('.label').text('Error!');
					}
					else {
						$(ButtonObj).removeClass('disabled').addClass(ButtonClass);
						$(ButtonObj).contents().find('.label').text(ButtonVal);
					}
				}
			});
		break;
		
		case 'SerieRefreshAll':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=SerieRefreshAll',
				beforeSend: function() {
					$(ButtonObj).removeClass(ButtonClass).addClass('disabled');
					$(ButtonObj).contents().find('.label').text('Refreshing ...');
				},
				success: function(Return) {
					if(Return != '') {
						$(ButtonObj).contents().find('.label').text('Error!');
					}
					else {
						$(ButtonObj).removeClass('disabled').addClass(ButtonClass);
						$(ButtonObj).contents().find('.label').text(ButtonVal);
					}
				}
			});
		break;
		
		case 'MovieInfo':
			console.log('MovieInfo ID: ' + ID);
		break;
		
		case 'MoviePlay':
			console.log('MoviePlay ID: ' + ID);
		break;
		
		case 'MovieDelete':
			console.log('MovieDelete ID: ' + ID);
		break;
		
		case 'RSSUpdate':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=RSSUpdate',
				beforeSend: function() {
					$(ButtonObj).removeClass(ButtonClass).addClass('disabled');
					$(ButtonObj).contents().find('.label').text('Updating ...');
				},
				success: function(Return) {
					if(Return != '') {
						$(ButtonObj).contents().find('.label').text('Error!');
					}
					else {
						$(ButtonObj).removeClass('disabled').addClass(ButtonClass);
						$(ButtonObj).contents().find('.label').text(ButtonVal);
					}
				}
			});
		break;
		
		case 'TorrentDownload':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=TorrentDownload&TorrentID=' + ID,
				beforeSend: function() {
					$(ButtonObj).removeClass(ButtonClass).addClass('disabled');
					$(ButtonObj).contents().find('.label').text('Downloading ...');
				},
				success: function(Return) {
					if(Return != '') {
						$(ButtonObj).contents().find('.label').text('Error!');
					}
					else {
						//$(ButtonObj).removeClass('disabled').addClass(ButtonClass);
						$(ButtonObj).contents().find('.label').text('Downloaded!');
					}
				}
			});
		break;
	}
}

function AjaxLink(Link) {
	Action = $(Link).attr('id').split('-');
	SecondID = Action[2];
	FirstID = Action[1];
	Action = Action[0];
	
	LinkVal = $(Link).html();
	switch(Action) {
		case 'DeleteEpisode':
			jConfirm('Are you sure you want to delete "' + $(Link).attr('rel') + '"?', 'Delete Episode', function(response) {
				if(response) {
					$.ajax({
						method: 'get',
						url:    'load.php',
						data:   'page=DeleteEpisode&EpisodeID=' + FirstID,
						beforeSend: function() {
							$(Link).html('<img src="images/spinners/ajax-light.gif" />');
						},
						success: function(Return) {
							if(Return != '') {
								$(Link).html('<img src="images/icons/error.png" />');
							}
							else {
								$('#Episode-' + FirstID).slideUp('slow').remove();
							}
						}
					});
				}
			});
		break;
			case 'ZoneDelete':
				jPrompt('Are you sure you want to delete zone "' + $(Link).attr('rel') + '"?' + "\n\n" + 'Type "delete" to confirm', '', 'Delete Zone', function(response) {
					if(response == 'delete') {
						$.ajax({
							method: 'get',
							url:    'load.php',
							data:   'page=ZoneDelete&ZoneID=' + FirstID,
							beforeSend: function() {
								$(Link).html('<img src="images/spinners/ajax-light.gif" />');
							},
							success: function(Return) {
								if(Return != '') {
									$(Link).html('<img src="images/icons/error.png" />');
								}
								else {
									$('#Zone-' + FirstID).slideUp('slow').remove();
								}
							}
						});
					}
				});
			break;
		
		case 'DriveActive':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=DriveActive&DriveID='+ FirstID,
				beforeSend: function() {
					$(Link).html('<img src="images/spinners/ajax-light.gif" />');
				},
				success: function(Return) {
					if(Return != '') {
						$(Link).html('<img src="images/icons/error.png" />');
					}
					else {
						$(Link).html(LinkVal);
					}
				}
			});
		break;
	
		case 'FilePlay':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=FilePlay&File='+ FirstID + '-' + SecondID,
				beforeSend: function() {
					$(Link).html('<img src="images/spinners/ajax-light.gif" />');
				},
				success: function(Return) {
					if(Return != '') {
						$(Link).html('<img src="images/icons/error.png" />');
					}
					else {
						$(Link).html('<img src="images/icons/check.png" />');
					}
				}
			});
		break;
		
		case 'DriveRemove':
			jConfirm('Are you sure you want to remove "' + $(Link).attr('rel') + '"?', 'Remove Drive', function(response) {
				if(response) {
					$.ajax({
						method: 'get',
						url:    'load.php',
						data:   'page=DriveRemove&DriveID='+ FirstID,
						beforeSend: function() {
							$(Link).html('<img src="images/spinners/ajax-light.gif" />');
						},
						success: function(Return) {
							if(Return != '') {
								$(Link).html('<img src="images/icons/error.png" />');
							}
							else {
								$('#Drive-' + FirstID).slideUp('slow').remove();
							}
						}
					});
				}
			});
		break;
		
		case 'SerieRefresh':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=SerieRefresh&SerieID='+ FirstID,
				beforeSend: function() {
					$(Link).html('<img src="images/spinners/ajax-light.gif" />');
				},
				success: function(Return) {
					if(Return != '') {
						$(Link).html('<img src="images/icons/error.png" />');
					}
					else {
						$(Link).html('<img src="images/icons/check.png" />');
					}
				}
			});
		break;
		
		case 'SerieSpelling':
			jPrompt('Type in a new alternate title for "' + $(Link).attr('rel') + '"', '', 'Alternate Title', function(response) {
				if(response) {
					$.ajax({
						method: 'get',
						url:    'load.php',
						data:   'page=SerieSpelling&SerieID=' + FirstID + '&Spelling=' + response,
						beforeSend: function() {
							$(Link).html('<img src="images/spinners/ajax-light.gif" />');
						},
						success: function(Return) {
							if(Return != '') {
								$(Link).html('<img src="images/icons/error.png" />');
							}
							else {
								$(Link).html(LinkVal);
							}
						}
					});
				}
			});
		break;
		
		case 'SerieDelete':
			jPrompt('Are you sure you want to delete "' + $(Link).attr('rel') + '" along with all episodes and folders?' + "\n\n" + 'Type "delete" to confirm', '', 'Delete Serie', function(response) {
				if(response == 'delete') {
					$.ajax({
						method: 'get',
						url:    'load.php',
						data:   'page=SerieDelete&SerieID='+ FirstID,
						beforeSend: function() {
							$(Link).html('<img src="images/spinners/ajax-light.gif" />');
						},
						success: function(Return) {
							if(Return != '') {
								$(Link).html('<img src="images/icons/error.png" />');
							}
							else {
								$(Link).html(LinkVal);
								$('#Serie-' + FirstID).slideUp('slow').remove();
							}
						}
					});
				}
			});
		break;
		
		case 'DownloadTorrent':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=TorrentDownload&TorrentID=' + SecondID + '&EpisodeID=' + FirstID,
				beforeSend: function() {
					$('#DownloadMultipleTorrent-' + FirstID).html('<img src="images/spinners/ajax-dark.gif" />');
					$('#DownloadTorrent-' + FirstID + '-' + SecondID).html('<img src="images/spinners/ajax-dark.gif" />');
					$('#DownloadMultipleTorrent-' + FirstID).qtip().hide();
					
				},
				success: function(Return) {
					if(Return == '') {
						$('#DownloadMultipleTorrent-' + FirstID).html('<img src="images/icons/downloaded.png" />');
						$('#DownloadTorrent-' + FirstID + '-' + SecondID).html('<img src="images/icons/downloaded.png" />');
					}
					else {
						$('#DownloadMultipleTorrent-' + FirstID).html('<img src="images/icons/error.png" />');
						$('#DownloadTorrent-' + FirstID + '-' + SecondID).html('<img src="images/icons/error.png" />');
					}
				}
			});
		break;
		
		case 'UserGroupEdit':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=UserGroupEdit&UserGroupID=' + FirstID,
				beforeSend: function() {
					$(Link).html('<img src="images/spinners/ajax-light.gif" />');
				},
				success: function(Return) {
					if(Return == '') {
						$(Link).html('<img src="images/icons/error.png" />');
					}
					else {
						$(Link).html(LinkVal);
						$('#UserGroupEdit').html(Return);
					}
				}
			});
		break;
		
		case 'TorrentStart':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=TorrentStart&TorrentHash=' + FirstID,
				beforeSend: function() {
					$(Link).html('<img src="images/spinners/ajax-light.gif" />');
				},
				success: function(Return) {
					if(Return != '') {
						$(Link).html('<img src="images/icons/error.png" />');
					}
					else {
						$(Link).html(LinkVal);
					}
				}
			});
		break;
		
		case 'TorrentStop':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=TorrentStop&TorrentHash=' + FirstID,
				beforeSend: function() {
					$(Link).html('<img src="images/spinners/ajax-light.gif" />');
				},
				success: function(Return) {
					if(Return != '') {
						$(Link).html('<img src="images/icons/error.png" />');
					}
					else {
						$(Link).html(LinkVal);
					}
				}
			});
		break;
		
		case 'TorrentPause':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=TorrentPause&TorrentHash=' + FirstID,
				beforeSend: function() {
					$(Link).html('<img src="images/spinners/ajax-light.gif" />');
				},
				success: function(Return) {
					if(Return != '') {
						$(Link).html('<img src="images/icons/error.png" />');
					}
					else {
						$(Link).html(LinkVal);
					}
				}
			});
		break;
		
		case 'TorrentDelete':
			jConfirm('Are you sure you want to delete "' + $(Link).attr('rel') + '"?', 'Delete Torrent?', function(response) {
				if(response) {
					$.ajax({
						method: 'get',
						url:    'load.php',
						data:   'page=TorrentDelete&TorrentHash=' + FirstID,
						beforeSend: function() {
							$(Link).html('<img src="images/spinners/ajax-light.gif" />');
						},
						success: function(Return) {
							if(Return != '') {
								$(Link).html('<img src="images/icons/error.png" />');
							}
							else {
								$(Link).html(LinkVal);
							}
						}
					});
				}
			});
		break;
		
		case 'TorrentDeleteData':
			jConfirm('Are you sure you want to delete "' + $(Link).attr('rel') + '" with all of its data?', 'Delete Torrent', function(response) {
				if(response) {
					$.ajax({
						method: 'get',
						url:    'load.php',
						data:   'page=TorrentDeleteData&TorrentHash=' + FirstID,
						beforeSend: function() {
							$(Link).html('<img src="images/spinners/ajax-light.gif" />');
						},
						success: function(Return) {
							if(Return != '') {
								$(Link).html('<img src="images/icons/error.png" />');
							}
							else {
								$(Link).html(LinkVal);
							}
						}
					});
				}
			});
		break;
		
		case 'TorrentDownload':
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=TorrentDownload&TorrentID=' + FirstID,
				beforeSend: function() {
					$(Link).html('<img src="images/spinners/ajax-light.gif" />');
				},
				success: function(Return) {
					if(Return != '') {
						$(Link).html('<img src="images/icons/error.png" />');
					}
					else {
						$(Link).html('<img src="images/icons/downloaded.png" />');
					}
				}
			});
		break;
		
		case 'WishlistDelete':
			jConfirm('Are you sure you want to delete "' + $(Link).attr('rel') + '" from the Wishlist?', 'Delete Wish', function(response) {
				if(response) {
					$.ajax({
						method: 'get',
						url:    'load.php',
						data:   'page=WishlistDelete&WishlistID=' + FirstID,
						beforeSend: function() {
							$(Link).html('<img src="images/spinners/ajax-light.gif" />');
						},
						success: function(Return) {
							if(Return != '') {
								$(Link).html('<img src="images/icons/error.png" />');
							}
							else {
								 $('#Wishlist-' + FirstID).slideUp('slow').remove();
							}
						}
					});
				}
			});
		break;
		
		case 'RSSFeedDelete':
			jConfirm('Are you sure you want to delete "' + $(Link).attr('rel') + '" along with all the data?', 'Delete RSS Feed', function(response) {
				if(response) {
					$.ajax({
						method: 'get',
						url:    'load.php',
						data:   'page=RSSFeedDelete&RSSID=' + FirstID,
						beforeSend: function() {
							$(Link).html('<img src="images/spinners/ajax-light.gif" />');
						},
						success: function(Return) {
							if(Return != '') {
								$(Link).html('<img src="images/icons/error.png" />');
							}
							else {
								 $('#RSSFeed-' + FirstID).slideUp('slow').remove();
							}
						}
					});
				}
			});
		break;
	}
}