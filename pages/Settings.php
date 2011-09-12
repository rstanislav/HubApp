<script type="text/javascript">
$('#SettingsSave').click(function() {
	$('form[id=' + $(this).parents('form:eq(0)').attr('id') + ']').ajaxSubmit({
		beforeSubmit: function() {
			$('#SettingsSave').contents().find('.label').text('Saving ...');
		},
		success: function() {
			$('#SettingsSave').contents().find('.label').text('Saved!');
		},
		error: function() {
			$('#SettingsSave').contents().find('.label').text('Error!');
		}
	});
});
</script>

<?php
$Settings = $HubObj->Settings;
?>

<div class="head-control">
 <a href="#!/Settings/Hub" class="button positive"><span class="inner"><span class="label" nowrap="">Hub</span></span></a>
 <a href="#!/Settings/Notifications" class="button positive"><span class="inner"><span class="label" nowrap="">Notifications</span></span></a>
 <a href="#!/Settings/XBMC" class="button positive"><span class="inner"><span class="label" nowrap="">XBMC</span></span></a>
 <a href="#!/Settings/UTorrent" class="button positive"><span class="inner"><span class="label" nowrap="">uTorrent</span></span></a>
</div>