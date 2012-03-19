<script type="text/javascript">
$('select[id="MoveTo-<?php echo $_GET['ID']; ?>"]').selectBox();

$('a[id|="FileManagerMoveSubmit"]').click(function(event) {
	event.preventDefault();
	
	$.ajax({
		method: 'get',
		url:    'load.php',
		data:   'page=FileManagerMove&From=<?php echo $_GET['Move']; ?>&To=' + $('#MoveTo-<?php echo $_GET['ID']; ?> option:selected').val(),
		beforeSend: function() {
			$('#FileManagerMove-<?php echo $_GET['ID']; ?>').html('<img src="images/spinners/ajax-light.gif" />');
		},
		success: function(Return) {
			$('a[id|=FileManagerMove]').each(function() {
				$(this).qtip().hide();
			});
			
			$('select[id="MoveTo-<?php echo $_GET['ID']; ?>"]').selectBox('destroy');
			
			if(Return != '') {
				$('#FileManagerMove-<?php echo $_GET['ID']; ?>').html('<img src="images/icons/error.png" />');
						
				noty({
					text: Return,
					type: 'error',
					timeout: false,
				});
			}
			else {
				$('#FileManager-<?php echo $_GET['ID']; ?>').slideUp('slow').remove();
			}
		}
	});
});

$('a[id|="FileManagerMoveCancel"]').click(function(event) {
	event.preventDefault();
	
	$('a[id|=FileManagerMove]').each(function() {
		$(this).qtip().hide();
	});
});
</script>
<?php
function AllowedDirectories($Directory) {
	 $AllowedDirectories = array('Completed', 'Downloads', 'Media', 'Unsorted');
	 foreach($AllowedDirectories AS $AllowedDirectory) {
	 	if(strstr($Directory, $AllowedDirectory)) { 
		 	return $Directory;
	 	}
	 	else {
	 		continue;
	 	}
	 }
}

function NestedFolderTreeSelect($Path, $Level = 0, $Parent = '', $DrivesObj) {
	$Paths = array_filter(glob(str_replace('\\', '/', $Path).'*', GLOB_ONLYDIR | GLOB_NOSORT), 'AllowedDirectories');
	
	if(!empty($Paths)) {
		$Level++;
		foreach($Paths AS $SubPath) {
			$SubPath = str_replace('\\', '/', $SubPath);
			$Space = ($Level > 1) ? str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $Level) : '';
			
			$Enabled = ($Parent == basename($SubPath)) ? ' disabled="disabled"' : '';
			echo '<option value="'.$SubPath.'"'.$Enabled.'>'.$Space.basename($SubPath).'</option>';
			NestedFolderTreeSelect($SubPath.'/', $Level, $Parent);
		}	
	}
}
?>

<select id="MoveTo-<?php echo $_GET['ID']; ?>" class="dark">
<option disabled="disabled">Select a new location</option>
<?php
$Drives = $DrivesObj->GetDrives();
if(is_array($Drives)) {
	foreach($Drives AS $Drive) {
		if(strstr($_GET['Move'], $Drive['DriveShare'])) {
			$Path = $Drive['DriveShare'];
			break;
		}
		else if(strstr($_GET['Move'], $Drive['DriveMount'])) {
			$Path = $Drive['DriveMount'];
			break;
		}
	}
}

NestedFolderTreeSelect($Path.'/', 0, basename($_GET['Move']), $DrivesObj);
?>
</select>
<a id="FileManagerMoveSubmit-<?php echo $_GET['ID']; ?>"><img style="vertical-align: middle" src="images/icons/check.png" /></a> 
<a id="FileManagerMoveCancel-<?php echo $_GET['ID']; ?>"><img style="vertical-align: middle" src="images/icons/delete.png" /></a>