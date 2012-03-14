<?php
class Share extends Hub {
	function UpdateWishlist() {
		$WishlistShare = '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
		
		<html> 
		<head>
		 <meta http-equiv="Content-type" content="text/html; charset=utf-8" /> 
		 <title>Hub &raquo; Share &raquo; Wishlist</title> 
		 <link type="text/css" rel="stylesheet" href="../css/stylesheet.css" />
		</head>
		
		<body>
		
		<div id="maincontent">
		
		<div class="head">Wishlist <small><small><small>updated: '.date('d.m.Y H:i').'</small></small></small></div>
		<table>
		 <thead>
		  <tr>
		   <th>Title</th>
		   <th style="width:50px; text-align: center;">Year</th>
		   <th style="width:100px; text-align: center;">Since</th>
		  </tr>
		 </thead>'."\n";
		
		$Wishes = Wishlist::GetUnfulfilledWishlistItems();
		
		if(is_array($Wishes)) {
			foreach($Wishes AS $Wish) {
				$WishlistShare .= '
				<tr>
				 <td>'.$Wish['WishlistTitle'].'</td>
				 <td style="text-align: center;">'.$Wish['WishlistYear'].'</td>
				 <td style="text-align: center;">'.date('d.m H:i', $Wish['WishlistDate']).'</td>
				</tr>'."\n";
			}
		}
		
		$WishlistShare .= '
		</table>
		
		<br />
		
		<div class="head">Wishlist &raquo; Granted</div>'."\n";
		
		$Wishes = Wishlist::GetFulfilledWishlistItems();
		
		if(is_array($Wishes)) {
			$WishlistShare .= '
			<table>
			 <thead>
			 <tr>
			  <th>Title</th>
			  <th style="text-align: center;">Year</th>
			  <th>Since</th>
			  <th>&nbsp;</th>
			 </tr>
			 </thead>'."\n";
			foreach($Wishes AS $Wish) {
				$WishlistShare .= '
				<tr>
				 <td>'.$Wish['WishlistTitle'].'</td>
				 <td style="width:50px; text-align: center;">'.$Wish['WishlistYear'].'</td>
				 <td style="width:150px">Granted on '.date('d.m.y H:i', $Wish['WishlistDownloadDate']).'</td>
				 <td style="text-align: center;width:18px">
				  <a href="http://www.youtube.com/results?search_query='.urlencode($Wish['WishlistTitle'].' '.$Wish['WishlistYear'].' trailer').'" target="_blank" title="Search for trailer on YouTube"><img src="../images/icons/youtube.png" /></a>
				 </td>
				</tr>'."\n";
			}
			$WishlistShare .= '</table>'."\n";
		}
		
		file_put_contents(APP_PATH.'/share/wishlist.html', $WishlistShare);
		
		$UpdatePrep = $this->PDO->prepare('UPDATE Hub SET Value = :Time WHERE Setting = "LastWishlistUpdate"');
		$UpdatePrep->execute(array(':Time' => time()));
		
		Hub::AddLog(EVENT.'Public Sharing', 'Success', 'Updated "/share/wishlist.html"');
	}
	
	function UpdateMovies($AllMovies) {
		$Movies = array();
		foreach($AllMovies['movies'] AS $Movie) {
			if(!trim($Movie['label'])) {
				$Title = trim(str_replace('The ', '', trim($Movie['originaltitle'])));
			}
			else {
				$Title = trim(str_replace('The ', '', trim($Movie['label'])));
			}
			
			$Movies[$Title][] = $Movie;
		}
		
		ksort($Movies);
		
		$MovieShare = '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
		
		<html> 
		<head>
		 <meta http-equiv="Content-type" content="text/html; charset=utf-8" /> 
		 <title>Hub &raquo; Share &raquo; Movies</title> 
		 <link type="text/css" rel="stylesheet" href="../css/stylesheet.css" />
		</head>
		
		<body>
		
		<div id="maincontent">
		
		<div class="head">Movies <small><small><small>updated: '.date('d.m.Y H:i').'</small></small></small></div>
		<table width="100%" class="nostyle">
		 <tr>'."\n";
		
		$i = 1;
		foreach($Movies AS $Movie) {
			if(is_file(APP_PATH.'/posters/thumbnails/movie-'.$Movie[0]['movieid'].'.jpg')) {
				$Thumbnail = '../posters/thumbnails/movie-'.$Movie[0]['movieid'].'.jpg';
			}
			else {
				$Thumbnail = '../images/poster-unavailable.png';
			}
			
			if(!empty($Movie[0]['trailer'])) {
				if(strstr($Movie[0]['trailer'], 'plugin.video.youtube')) {
					$MovieTrailerLink = '<a href="http://youtube.com/watch?v='.str_replace('plugin://plugin.video.youtube/?action=play_video&videoid=', '', $Movie[0]['trailer']).'" target="_blank" title="'.$Movie[0]['label'].' ('.$Movie[0]['year'].') Trailer"><img  src="../images/icons/youtube.png" /></a>';
				}
				else if(strstr($Movie[0]['trailer'], 'http://playlist.yahoo.com')) {
					$MovieTrailerLink = '<a href="'.$Movie[0]['trailer'].'" target="_blank" title="'.$Movie[0]['label'].' ('.$Movie[0]['year'].') Trailer"><img  src="../images/icons/yahoo.png" /></a>';
				}
			}
			else {
				$MovieTrailerLink = '<a href="http://youtube.com/results?search_query='.urlencode($Movie[0]['label'].' '.$Movie[0]['year'].' trailer').'" target="_blank" title="Search for trailer on YouTube"><img  src="../images/icons/youtube.png" /></a>';
			}
			
			$Watched = ($Movie[0]['playcount']) ? '<div class="cover-watched">watched</div>' : '';
			
			$MoviePoster = '
			 <div id="Cover-'.$Movie[0]['movieid'].'" class="cover">
			  <img class="poster" width="150" height="250" src="'.$Thumbnail.'" />
			  '.$Watched.'
			 </div>';
			 
			$MovieTitle = (empty($Movie[0]['label'])) ? $Movie[0]['originaltitle'] : $Movie[0]['label'];
			$MovieShare .= '
			<td style="text-align: center;">
			 <div style="height:310px;">
			  <div style="width: 151px; height: 250px; margin: 0 auto;">'.$MoviePoster.'</div><br />
			  <strong>'.$MovieTitle.' ('.$Movie[0]['year'].') '.$MovieTrailerLink.'</strong>
			 </div>
			</td>'."\n";
			
			if($i++ % 6 == 0) {
				$MovieShare .= '
				</tr>
				<tr>'."\n";
			}
		}
		$MovieShare .= '</table></div>'."\n";
		
		file_put_contents(APP_PATH.'/share/movies.html', $MovieShare);
		
		$UpdatePrep = $this->PDO->prepare('UPDATE Hub SET Value = :Time WHERE Setting = "LastMoviesUpdate"');
		$UpdatePrep->execute(array(':Time' => time()));
		
		Hub::AddLog(EVENT.'Public Sharing', 'Success', 'Updated "/share/movies.html"');
	}
}
?>