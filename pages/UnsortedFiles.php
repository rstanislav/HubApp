<?php
if($UserObj->CheckPermission($UserObj->UserGroupID, 'UnsortedFilesRename')) {
?>
<script type="text/javascript">
$('#tbl-unsorted .editable').editable('load.php?page=UnsortedFileRename', {
	submitdata: function(value, settings) {
		Action = $(this).attr('id').split('-');
		ID = Action[1];
		
		return {
			FilePath: ""+$('#FilePath-' + ID).html()+"",
			DefaultFile: ""+$('#File-' + ID).html()+""
		};
	},
	onblur: 'submit',
});
</script>
<?php
}

if($UserObj->CheckPermission($UserObj->UserGroupID, 'UnsortedFilesDelete')) {
?>
<script type="text/javascript">
$('a[id|="UnsortedFileDelete"]').click(function() {
	Action = $(this).attr('id').split('-');
	ID = Action[1];
			
	jConfirm('Are you sure you want to delete this unsorted file/folder?', 'Delete unsorted file/folder', function(response) {
		if(response) {
			$.ajax({
				method: 'get',
				url:    'load.php',
				data:   'page=UnsortedFileDelete&File='+ $('#FilePath-' + ID).html() + $('#File-' + ID).html(),
				beforeSend: function() {
					$('#UnsortedFileDelete-' + ID).html('<img src="images/spinners/ajax-light.gif" />');
				},
				success: function(Return) {
					if(Return != '') {
						$('#UnsortedFileDelete-' + ID).html('<img src="images/icons/error.png" />');
					}
					else {
						$('#UnsortedFile-' + ID).remove();
					}
				}
			});
		}
	});
});
</script>
<?php
}

if($UserObj->CheckPermission($UserObj->UserGroupID, 'UnsortedFilesMove')) {
?>
<script type="text/javascript">
$('a[id|="UnsortedFileMove"]').click(function() {
	Action = $(this).attr('id').split('-');
	ID = Action[1];
	
	LinkVal = $('#UnsortedFileMove-' + ID).html();
	$('form[id=UnsortedFileMoveForm-' + ID + ']').ajaxSubmit({
		beforeSubmit: function() {
			$('#UnsortedFileMove-' + ID).html('<img src="images/spinners/ajax-light.gif" />');
		},
		success: function() {
			$('#UnsortedFile-' + ID).remove();
		},
		error: function() {
			$('#UnsortedFileMove-' + ID).html('<img src="images/icons/error.png" />');
		}
	});
});
</script>
<?php
}
?>

<div class="head">Unsorted Files <small style="font-size: 12px;">(<a href="#!/Help/UnsortedFiles">?</a>)</small></div>

<?php
$UnsortedFiles = $UnsortedFilesObj->GetUnsortedFiles();

if(is_array($UnsortedFiles)) {
	echo '
	<table id="tbl-unsorted">
	 <thead>
	  <tr>
	   <th style="width:36px">&nbsp;</th>
	   <th>Path</th>
	   <th>File/Folder</th>
	   <th>&nbsp;</th>
	   <th style="width:36px">&nbsp;</th>
	  </tr>
	 </thead>'."\n";
	 
	 $i = 0;
	 foreach($UnsortedFiles AS $DriveRoot => $UnsortedFiles) {
	 	foreach($UnsortedFiles AS $UnsortedFile) {
	 		$i++;
	 		$UnsortedFile = str_replace('\\', '/', $UnsortedFile);
	 		$FilePath = str_replace('\\', '/', dirname($UnsortedFile).'/');
	 		$File = str_replace($FilePath, '', $UnsortedFile);
	 		
	 		echo '<form id="UnsortedFileMoveForm-'.$i.'" action="load.php?page=UnsortedFileMove" method="post">'."\n";
	 		if(is_dir($UnsortedFile)) {
	 			$UnsortedFileDeleteLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'UnsortedFilesDelete')) ? '<a id="UnsortedFileDelete-'.$i.'"><img src="images/icons/folder_delete.png" /></a>' : '';
	 			
	 			echo '
	 			<tr id="UnsortedFile-'.$i.'">
	 			 <td style="text-align:center;"><img src="images/icons/folder_error.png" /></td>
	 			 <td>
	 			  '.$FilePath.'
	 			  <span id="FilePath-'.$i.'" style="display:none">'.$FilePath.'</span>
	 			  <span id="File-'.$i.'" style="display:none">'.$File.'</span>
	 			 </td>
	 			 <td>'.$File.'</td>
	 			 <td>&nbsp;</td>
	 		 	 <td style="text-align:center;">'.$UnsortedFileDeleteLink.'</td>
	 			</tr>'."\n";
	 		}
	 		else if(is_file($UnsortedFile)) {
	 			$ParsedFile = $RSSObj->ParseRelease($UnsortedFile);
	 			$ParsedFile['Title'] = str_replace($FilePath, '', $ParsedFile['Title']);
	 			
	 			$Editable               = ($UserObj->CheckPermission($UserObj->UserGroupID, 'UnsortedFilesRename')) ? ' class="editable"' : '';
	 			$UnsortedFileMoveLink   = ($UserObj->CheckPermission($UserObj->UserGroupID, 'UnsortedFilesMove'))   ? '<a id="UnsortedFileMove-'.$i.'"><img src="images/icons/file_move.png" /></a>'     : '';
	 			$UnsortedFileDeleteLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'UnsortedFilesDelete')) ? '<a id="UnsortedFileDelete-'.$i.'"><img src="images/icons/file_delete.png" /></a>' : '';
	 			
	 			echo '
	 			<tr id="UnsortedFile-'.$i.'">
	 			 <td style="text-align:center;"><img src="images/icons/file_error.png" /></td>
	 			 <td>
	 			  '.$FilePath.'
	 			  <span id="FilePath-'.$i.'" style="display:none">'.$FilePath.'</span>
	 			  <span id="File-'.$i.'" style="display:none">'.$File.'</span>
	 			  <input type="hidden" value="'.$FilePath.'" name="UnsortedFilePath" />
	 			  <input type="hidden" value="'.$File.'" name="UnsortedFile" />
	 			 </td>
	 			 <td '.$Editable.'id="File-'.$i.'">'.$File.'</td>
	 			 <td>
	 			  <select name="ContentFolder">'."\n";
	 			$Iterator = new DirectoryTree(new RecursiveDirectoryIterator($DriveRoot.'/Media/'), RecursiveIteratorIterator::SELF_FIRST);
	 			    
	 			foreach($Iterator AS $Directory) {
	 				if(!empty($Directory)) {
	 					$Type = (array_key_exists('Type', $ParsedFile)) ? $ParsedFile['Type'] : '';
	 					if($Type == 'TV' && $ParsedFile['Title'] == str_replace('&nbsp;', '', $Directory)) {
	 						$Selected = 'selected="selected"';
	 					}
	 					else if($Type == 'Movies' && str_replace('&nbsp;', '', $Directory) == 'Movies') {
	 						$Selected = 'selected="selected"';
	 					}
	 					else {
	 						$Selected = '';
	 					}
	 					
	 					$Enabled = ($Directory == 'TV') ? ' disabled="disabled"' : '';
	 					
	 					echo '<option value="'.$Iterator->parent().'"'.$Selected.$Enabled.'>'.$Directory.'</option>';
	 				}
	 			}
	 			echo '
	 			  </select>
	 			 </td>
	 			 <td style="text-align:center;">
	 			  '.$UnsortedFileMoveLink.'
	 			  '.$UnsortedFileDeleteLink.'
	 			 </td>
	 			</tr>'."\n";
	 		}
	 		echo '
	 		</form>'."\n";
	 	}
	 }
}
else {
	echo '<div class="notification information">No unsorted files available</div>';
}
?>