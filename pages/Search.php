<?php
$Search = (filter_has_var(INPUT_GET, 'Search')) ? $_GET['Search'] : '';
$Result = (filter_has_var(INPUT_GET, 'Result')) ? $_GET['Result'] : '';

if(!strlen($Result)) {
	include_once './pages/Search_Default.php';
}
else {
	switch($Result) {
		case 'TheTVDB':
			include_once './pages/Search_Result_TheTVDB.php';
		break;
		
		case 'Torrents':
			include_once './pages/Search_Result_Torrents.php';
		break;
	}
}
?>