<?php
exec('git pull', $Response);

if(is_array($Response)) {
	if($Response[0] == 'Already up-to-date.') {
		echo '0';
	}
	else {
		echo json_encode($Response);
	}
}
?>