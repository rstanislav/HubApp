<?php
require_once APP_PATH.'/libraries/class.hub.php';
require_once APP_PATH.'/libraries/class.drives.php';
require_once APP_PATH.'/libraries/class.extract-files.php';
require_once APP_PATH.'/libraries/class.movies.php';
require_once APP_PATH.'/libraries/class.rss.php';
require_once APP_PATH.'/libraries/class.search.php';
require_once APP_PATH.'/libraries/class.series.php';
require_once APP_PATH.'/libraries/class.unsorted-files.php';
require_once APP_PATH.'/libraries/class.user.php';
require_once APP_PATH.'/libraries/class.utorrent.php';
require_once APP_PATH.'/libraries/class.wishlist.php';
require_once APP_PATH.'/libraries/class.xbmc.php';
require_once APP_PATH.'/libraries/class.zones.php';

$HubObj           = new Hub;
$HubObj->CheckHub();
$DrivesObj        = new Drives;
$ExtractFilesObj  = new ExtractFiles;
$MoviesObj        = new Movies;
$RSSObj           = new RSS;
$SearchObj        = new Search;
$SeriesObj        = new Series;
$UnsortedFilesObj = new UnsortedFiles;
$UserObj          = new User;
$UTorrentObj      = new UTorrent;
$WishlistObj      = new Wishlist;
$XBMCObj          = new XBMC;
$ZonesObj         = new Zones;
?>