<?php
if(filter_has_var(INPUT_GET, 'Time')) {
	$XBMCObj->GetLogFile($_GET['Time']);
}
else {
?>
<script type="text/javascript"> 
$('#xbmc-log td[rel=time]:first').everyTime(5000, function(i) {
	updateLog($('td[rel=time]:first').text());
}, 0);

function updateLog(toTime) {
	$.ajax({
		method: 'get',
		url:    'load.php',
		data:   'page=XBMCLog&Time=' + toTime,
		success: function(html) {
			if(html) {
				$('#xbmc-log td[rel=time]:first').parent().before(html);
			}
		}
	});
}
</script>

<div class="head">XBMC Log <small style="font-size: 12px;">(<a href="#!/Help/XBMCLog">?</a>)</small></div>

<table id="xbmc-log" class="text-select">
 <thead>
 <tr>
  <th style="text-align:center;width: 60px">Time</th>
  <th>&nbsp;</th>
  <th>&nbsp;</th>
  <th>Text</th>
 </tr>
 </thead>
<?php
$XBMCObj->GetLogFile();
?>
</table>
<?php
}
?>