<?php
ini_set('max_execution_time', (60 * 60 * 5)); // 5 hours
ini_set('error_reporting', E_ALL);
date_default_timezone_set('Europe/Oslo');

define('APP_PATH', realpath(dirname(__FILE__).'/../'));

if(PHP_SAPI == 'cli') {
	define('EVENT', 'Schedule/');
}
else {
	define('EVENT', '');
}

if(!is_dir(APP_PATH.'/backup/'))                 { mkdir(APP_PATH.'/backup/');                 }
if(!is_dir(APP_PATH.'/posters/'))                { mkdir(APP_PATH.'/posters/');                }
if(!is_dir(APP_PATH.'/posters/thumbnails/'))     { mkdir(APP_PATH.'/posters/thumbnails/');     }
if(!is_dir(APP_PATH.'/screenshots/'))            { mkdir(APP_PATH.'/screenshots/');            }
if(!is_dir(APP_PATH.'/screenshots/thumbnails/')) { mkdir(APP_PATH.'/screenshots/thumbnails/'); }
if(!is_dir(APP_PATH.'/tmp'))                     { mkdir(APP_PATH.'/tmp/');                    }

require_once APP_PATH.'/resources/db-config.php';
?>