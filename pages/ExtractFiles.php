<div class="head">Extract Files <small style="font-size: 12px;">(<a href="#!/Help/ExtractFiles">?</a>)</small></div>

<?php
if(!strlen(EVENT)) {
	if($HubObj->CheckLock()) {
		echo '
		<div class="notification information">
		 <strong>INFORMATION:</strong> Hub is currently locked
		</div>';
	}
	else {
		$HubObj->Lock();
		
		$Files = $ExtractFilesObj->GetFiles();
		
		if(is_array($Files) && sizeof($Files)) {
			$FileNo = 0;
			$AjaxQueue = FALSE;
			
			if(array_key_exists('Extract', $Files)) {
				foreach($Files['Extract'] AS $File) {
					$FileNo++;
					$AjaxQueue = TRUE;
					list($File, $DriveID) = explode(',', $File);
					
					echo '
					<div id="ExtractFile-'.$FileNo.'">Waiting to extract "'.$File.'" ...</div>
					
					<script type="text/javascript">
					$(document).queue("ajaxRequests", function() {
						$("#ExtractFile-'.$FileNo.'").html("<img src=\"images/spinners/ajax-light.gif\" /> Extracting \"'.$File.'\" ...");
					    
						$.ajax({ url:  "load.php",
								 data: "page=ExtractFile&File='.urlencode($File).'&ID='.$FileNo.'&DriveID='.$DriveID.'",
								 success: function(data) {
								     $("#ExtractFile-'.$FileNo.'").html(data);
					       	 	     $(document).dequeue("ajaxRequests");
					             }
						});
					});
					</script>'."\n";
				}
			}
			
			if(array_key_exists('Move', $Files)) {
				foreach($Files['Move'] AS $File) {
					$FileNo++;
					$AjaxQueue = TRUE;
					list($File, $DriveID) = explode(',', $File);
					
					echo '
					<div id="MoveFile-'.$FileNo.'">Waiting to move '.$File.' ...</div>
					
					<script type="text/javascript">
					$(document).queue("ajaxRequests", function() {
						$("#ExtractFile-'.$FileNo.'").html("<img src=\"images/spinners/ajax-light.gif\" /> Moving \"'.$File.'\" ...");
					    
						$.ajax({ url:  "load.php",
								 data: "page=MoveFile&File='.urlencode($File).'&ID='.$FileNo.'&DriveID='.$DriveID.'",
								 success: function(data) {
								     $("#MoveFile-'.$FileNo.'").html(data);
					       	 	     $(document).dequeue("ajaxRequests");
					             }
						});
					});
					</script>'."\n";
				}
			}
			
			if($AjaxQueue && $UserObj->CheckPermission($UserObj->UserGroupID, 'ExtractFiles')) {
				echo '
				<script type="text/javascript">
				$(document).dequeue("ajaxRequests");
				</script>'."\n";
			}
		}
		else {
			echo '
			<div class="notification information">
			 <strong>INFORMATION:</strong> No files to move/extract
			</div>';
		}
	}
}
?>