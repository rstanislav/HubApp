<script type="text/javascript">
$(document).ready(function() {
	$('a[id=ExtractOK]').click(function() {
		$(document).dequeue("ajaxRequests");
	});
});
</script>

<div class="head">Extract Files </div>

<?php
$CompletedFiles = json_decode($Hub->Request('/drives/files/completed'));

if(is_object($CompletedFiles) && property_exists($CompletedFiles, 'error')) {
	echo '<div class="notification information">'.$CompletedFiles->error->message.'</div>'."\n";
}
else {
	echo 'Are you happy with these results? <a id="ExtractOK">Go ahead and execute them</a><br /><br />'."\n";
	
	if(is_object($CompletedFiles) && property_exists($CompletedFiles, 'Extract')) {
		$FileNo = 0;
		foreach($CompletedFiles->Extract AS $File) {
			$FileNo++;
			
			$ExtractArr = array('File' => $File, 'debug' => TRUE);
			$ExtractFile = json_decode($Hub->Request('/drives/files/extract', 'POST', $ExtractArr));
			
			if(is_object($ExtractFile) && property_exists($ExtractFile, 'error')) {
				echo '<div id="ExtractFile-'.$FileNo.'">Waiting: '.$ExtractFile->error->message.' ...</div>'."\n";
				
				echo '
				<script type="text/javascript">
				$(document).queue("ajaxRequests", function() {
					$.ajax({
						type: 	"post",
						url:    "/api/drives/files/extract",
						data:   { File: "'.$File.'" },
						beforeSend: function() {
							$("#ExtractFile-'.$FileNo.'").html("<img src=\"images/spinners/ajax-light.gif\" /> Working \"'.$ExtractFile->error->message.'\" ...");
						},
						success: function(data, textStatus, jqXHR) {
							$("#ExtractFile-'.$FileNo.'").html("<img src=\"images/icons/check.png\" /> " + data.error.message);
						
							$(document).dequeue("ajaxRequests");
						},
						error: function(jqXHR, textStatus, errorThrown) {
						    var responseObj = JSON.parse(jqXHR.responseText);
						    $("#ExtractFile-'.$FileNo.'").html("<img src=\"images/icons/error.png\" /> " + responseObj.error.message);
						}
					});
				});
				</script>'."\n";
			}
		}
	}
	
	if(is_object($CompletedFiles) && property_exists($CompletedFiles, 'Move')) {
		foreach($CompletedFiles->Move AS $File) {
			$FileNo++;
			
			$MoveArr = array('File' => $File, 'debug' => TRUE);
			$MoveFile = json_decode($Hub->Request('/drives/files/move', 'POST', $MoveArr));
			
			if(is_object($MoveFile) && property_exists($MoveFile, 'error')) {
				echo '<div id="MoveFile-'.$FileNo.'">Waiting: '.$MoveFile->error->message.' ...</div>'."\n";
				
				echo '
				<script type="text/javascript">
				$(document).queue("ajaxRequests", function() {
					$.ajax({
						type: 	"post",
						url:    "/api/drives/files/move",
						data:   { File: "'.$File.'" },
						beforeSend: function() {
							$("#MoveFile-'.$FileNo.'").html("<img src=\"images/spinners/ajax-light.gif\" /> Working \"'.$ExtractFile->error->message.'\" ...");
						},
						success: function(data, textStatus, jqXHR) {
							$("#MoveFile-'.$FileNo.'").html("<img src=\"images/icons/check.png\" /> " + data.error.message);
						
							$(document).dequeue("ajaxRequests");
						},
						error: function(jqXHR, textStatus, errorThrown) {
						    var responseObj = JSON.parse(jqXHR.responseText);
						    $("#MoveFile-'.$FileNo.'").html("<img src=\"images/icons/error.png\" /> " + responseObj.error.message);
						}
					});
				});
				</script>'."\n";
			}
		}
	}
	
	echo '
	<script type="text/javascript">
	$(document).queue("ajaxRequests", function() {
		$.ajax({
			type: 	"get",
			url:    "/api/drives/clean",
			success: function(data, textStatus, jqXHR) {
				console.log(data.error.message);
			
				$(document).dequeue("ajaxRequests");
			}
		});
	});
	</script>'."\n";
}
?>