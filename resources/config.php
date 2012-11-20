<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Europe/Oslo');

define('APP_PATH', realpath(dirname(__FILE__).'/../'));

if(filter_has_var(INPUT_SERVER, 'SERVER_ADDR')) {
	define('API_URL', sprintf('http://%s/api', $_SERVER['SERVER_ADDR']));
}
else {
	define('API_URL', sprintf('http://%s/api', '127.0.0.1'));
}

define('API_KEY', '');

if(PHP_SAPI == 'cli') {
	define('EVENT', 'Schedule/');
}
else {
	define('EVENT', '');
}

if(!is_dir(APP_PATH.'/share/'))                  { mkdir(APP_PATH.'/share/');                  }
if(!is_dir(APP_PATH.'/posters/'))                { mkdir(APP_PATH.'/posters/');                }
if(!is_dir(APP_PATH.'/posters/movies'))          { mkdir(APP_PATH.'/posters/movies/');         }
if(!is_dir(APP_PATH.'/posters/series'))          { mkdir(APP_PATH.'/posters/series');          }
if(!is_dir(APP_PATH.'/tmp'))                     { mkdir(APP_PATH.'/tmp/');                    }
?>