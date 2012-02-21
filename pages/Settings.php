<script type="text/javascript">
$('[rel=SettingsSave]').click(function() {
	SettingBtn = this;
	$('form[id=' + $(this).parents('form:eq(0)').attr('id') + ']').ajaxSubmit({
		beforeSubmit: function() {
			$(SettingBtn).contents().find('.label').text('Saving ...');
		},
		success: function() {
			//$('[rel=SettingsSave]').contents().find('.label').text('Saved!');
			$(SettingBtn).contents().find('.label').text('Saved!');
		},
		error: function() {
			$(SettingBtn).contents().find('.label').text('Error!');
		}
	});
});
</script>

<style type="text/css">

</style>

<?php
$Settings = $HubObj->Settings;
?>

<div class="head-control">
 <a href="#!/Settings/Hub" class="button positive"><span class="inner"><span class="label" nowrap="">Hub</span></span></a>
 <a href="#!/Settings/Notifications" class="button positive"><span class="inner"><span class="label" nowrap="">Notifications</span></span></a>
 <a href="#!/Settings/XBMC" class="button positive"><span class="inner"><span class="label" nowrap="">XBMC</span></span></a>
 <a href="#!/Settings/UTorrent" class="button positive"><span class="inner"><span class="label" nowrap="">uTorrent</span></span></a>
</div>