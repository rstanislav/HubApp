<?php
$SerieTitle = (filter_has_var(INPUT_GET, 'Serie')) ? $_GET['Serie'] : '';

if($SerieTitle != 'undefined') {
	include_once './pages/Series_Detailed.php';
}
else {
	include_once './pages/Series_Default.php';
}
?>