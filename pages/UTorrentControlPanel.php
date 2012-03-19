<script type="text/javascript">
$(document).ready(function() {
	var ShiftDown = false;
	$(document).keydown(function(event) {
		if(event.shiftKey) {
			$('a[id|="TorrentRemove"]').each(function() {
				if(!$(this).hasClass('disabled')) {
					$(this).contents().find('.label').text('Remove All');
				}
			});
					
			ShiftDown = true;
		}
	});

	$(document).keyup(function(event) {
		if(!event.shiftKey) {
			$('a[id|="TorrentRemove"]').each(function() {
				if(!$(this).hasClass('disabled')) {
					if($(this).contents().find('.label').text() != 'Removing ...') {
						$(this).contents().find('.label').text('Remove All Finished');
					}
				}
			});
		
			ShiftDown = false;
		}
	});

	$('a[id|="TorrentRemove"]').click(function(event) {
		if(ShiftDown) {
			AjaxButton(this, 'All');
		}
		else {
			AjaxButton(this, 'Finished');
		}
	});
});
</script>

<?php
$UTorrentStartLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentStart')) ? '<a id="TorrentStartAll-0" class="button positive"><span class="inner"><span class="label" nowrap="">Start All</span></span></a>' : '';
$UTorrentPauseLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentPause')) ? '<a id="TorrentPauseAll-0" class="button neutral"><span class="inner"><span class="label" nowrap="">Pause All</span></span></a>' : '';
$UTorrentStopLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentStop')) ? '<a id="TorrentStopAll-0" class="button negative"><span class="inner"><span class="label" nowrap="">Stop All</span></span></a>' : '';
$UTorrentRemoveLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'TorrentDelete')) ? '<a id="TorrentRemove-0" class="button negative"><span class="inner"><span class="label" nowrap="">Remove All Finished</span></span></a>' : '';
	
echo '
<div class="head-control">
 '.$UTorrentStartLink.'
 '.$UTorrentPauseLink.'
 '.$UTorrentStopLink.'
 '.$UTorrentRemoveLink.'
</div>'."\n";
?>
 
<div class="head">uTorrent Control Panel <small style="font-size: 12px;">(<a href="#!/Help/uTorrent">?</a>)</small></div>

<script type="text/javascript">
$('#utorrent').everyTime(1000, function(i) {
	$.ajax({
		method: 'get',
		url:    'load.php',
		data:   'page=UTorrentCP',
		success: function(data) {
			$('#utorrent').html(data);
		}
	});
}, 0);
</script>

<div id="utorrent"><img src="images/spinners/ajax-light-large.gif" /></div>