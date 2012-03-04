<?php
$Days = (filter_has_var(INPUT_COOKIE, 'PastScheduleDays')) ? $_COOKIE['PastScheduleDays'] : '5';
?>

<div class="head">Last <span id="LastXDays"><?php echo $Days; ?></span> days <small style="font-size: 12px;">(<a href="#!/Help/Main">?</a>)</small></div>

<script type="text/javascript">
if(!GetCookie('PastScheduleDays')) {
	SetCookie('PastScheduleDays', 5, '999', '/', '', '' );
}

if($('select#PastScheduleSlider').length != 0) {
	$('select#PastScheduleSlider').selectToUISlider({
		labels: 31,
		tooltip: false,
		sliderOptions: {
			change: function(event, ui) {
				$('#LastXDays').html(ui.value);
				SetCookie('PastScheduleDays', ui.value, '999', '/', '', '' );
				
				$.ajax({
					method: 'get',
					url:    'load.php',
					data:   'page=PastSchedule&Days=' + ui.value,
					success: function(data) {
						$('#PastSchedule').html(data);
					}
				});
			} 
		}
	}).hide();
}
</script>

<select name="PastScheduleSlider" id="PastScheduleSlider" style="display:none">
<?php
for($i = 0; $i <= 31; $i++) {
	$Selected = ($i == $Days) ? ' selected="selected"' : '';
	echo '<option value="'.$i.'"'.$Selected.'>'.$i.'</option>'."\n";
}
?>
</select>

<br />

<script type="text/javascript">
$.ajax({
	method: 'get',
	url:    'load.php',
	data:   'page=PastSchedule&Days=' + GetCookie('PastScheduleDays'),
	beforeSend: function() {
		$('#PastSchedule').html('<img src="images/spinners/ajax-light-large.gif" />');
	},
	success: function(data) {
		$('#PastSchedule').html(data);
	}
});

$.ajax({
	method: 'get',
	url:    'load.php',
	data:   'page=FutureSchedule',
	beforeSend: function() {
		$('#FutureSchedule').html('<img src="images/spinners/ajax-light-large.gif" />');
	},
	success: function(data) {
		$('#FutureSchedule').html(data);
	}
});

$('#PastSchedule').everyTime(60000, function(i) {
	$.get('load.php', { page: 'PastSchedule', Days: GetCookie('PastScheduleDays') }, function(data) {
		complete: jQuery('#PastSchedule').html(data);
	});
}, 0);

$('#FutureSchedule').everyTime(60000, function(i) {
	$.get('load.php', { page: 'FutureSchedule' }, function(data) {
		complete: jQuery('#FutureSchedule').html(data);
	});
}, 0);
</script>

<div id="PastSchedule"></div>

<br />

<div class="head">Upcoming <small style="font-size: 12px;">(<a href="#!/Help/Main">?</a>)</small></div>

<div id="FutureSchedule"></div>