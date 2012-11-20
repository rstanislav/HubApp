<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', (60 * 60 * 5));

if(PHP_SAPI == 'cli') {
	define('EVENT', 'Schedule/');
}
else {
	define('EVENT', '');
}

define('APP_PATH', realpath(dirname(__FILE__).'/../'));

require_once 'resources/restler.php';
require_once 'resources/DB.php';
require_once 'resources/functions.php';

function autoload_class($ClassName) {
    $Directories = array('controllers/');
                         
    foreach($Directories AS $Directory) {
        $Filename = $Directory.$ClassName.'.php';
        
        if(is_file($Filename)) {
            require($Filename);
            
            break;
        }
    }
}

spl_autoload_register('autoload_class');

$Restler = new Restler();
$Restler->addAPIClass('Drives');
$Restler->addAPIClass('Hub');
$Restler->addAPIClass('Log');
$Restler->addAPIClass('RSS');
$Restler->addAPIClass('Series');
$Restler->addAPIClass('Settings');
$Restler->addAPIClass('Users');
$Restler->addAPIClass('UTorrent');
$Restler->addAPIClass('Wishlist');
$Restler->addAPIClass('XBMC');
$Restler->addAuthenticationClass('Authentication');
$Restler->handle();
?>