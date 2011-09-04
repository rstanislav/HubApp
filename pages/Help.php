<?php
$Topic = (filter_has_var(INPUT_GET, 'Topic')) ? $_GET['Topic'] : '';

switch($Topic) {
	case 'Search':
		include_once './pages/help/Search.php';
	break;
	
	default:
		//include_once './pages/help/Default.php';
	break;
}
?>