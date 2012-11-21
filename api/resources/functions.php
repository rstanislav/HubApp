<?php
function GetSetting($Setting) {
	$PDO = DB::Get();
	
	try {
		$SettingsPrep = $PDO->prepare('SELECT
										*
									   FROM
										Hub
									   WHERE
										Setting = :Setting');
												
		$SettingsPrep->execute(array(':Setting' => $Setting));
	
		if($SettingsPrep->rowCount()) {
			$Settings = $SettingsPrep->fetch();
		
			return $Settings['Value'];
		}
		else {
			throw new RestException(404, 'Setting: "'.$Setting.'" does not exist');
		}
	}
	catch(PDOException $e) {
		throw new RestException(412, 'MySQL: '.$e->getMessage());
	}
}

function ConvertSeconds($Seconds, $TimeFormat = TRUE) {
	$CSeconds   = ($Seconds % 60);
	$Remaining  = intval($Seconds / 60);
	$CMinutes   = ($Remaining % 60);
	$Remaining  = intval($Remaining / 60);
	$CHours     = ($Remaining % 24);
	$CDays      = intval($Remaining / 24);

	if($TimeFormat) {
		return sprintf("%02d:%02d:%02d:%02d", $CDays, $CHours, $CMinutes, $CSeconds);
	}
	else {
		if($CDays) {
			return sprintf("%01dd %02dh %02dm ", $CDays, $CHours, $CMinutes);
		}
		else if($CHours) {
			return sprintf("%02dh %02dm ", $CHours, $CMinutes);
		}
		else {
			return sprintf("%02dm ", $CMinutes);
		}
	}
}

function GetQualityRank($Str) {
	$Str         = str_replace('.', ' ', $Str);
	$Str         = str_replace('_', ' ', $Str);
	$Str         = str_replace('-', ' ', $Str);
	$Str         = str_replace('[', ' ', $Str);
	$Str         = str_replace(']', ' ', $Str);
	$QualityRank = 0;
	$Words       = array_unique(explode(' ', $Str));
	
	foreach($Words AS $Word) {
		switch(strtolower($Word)) {
			case '1080p':    $QualityRank += 60000; break;
			case '1080i':    $QualityRank += 50000; break;
			case '810p':     $QualityRank += 40000; break;
			case '720p':     $QualityRank += 30000; break;
			case '540p':     $QualityRank += 20000; break;
			case '480p':     $QualityRank += 10000; break;
			
			case 'bluray':
			case 'brrip':
			case 'bdrip':    $QualityRank += 6000;  break;
			case 'dvdrip':
			case 'hdtv':
			case 'pdtv':
			case 'hdtvrip':  $QualityRank += 5000;  break;
			case 'dvdscr':   $QualityRank += 3000;  break;
			case 'ts':
			case 'telesync':
			case 'cam':      $QualityRank -= 100000; break;
			
			case 'proper':
			case 'repack':   $QualityRank += 100;   break;
			
			case 'truehd7':
			case 'truehd5':
			case 'truehd':   $QualityRank += 30;    break;
			case 'dts':      $QualityRank += 20;    break;
			case 'ac3':      $QualityRank += 10;    break;
			
			case 'x264':     $QualityRank += 20;    break;
			case 'xvid':     $QualityRank += 10;    break;
		}
	}
	
	return $QualityRank;
}

function RecursiveDirRemove($Directory) { 
	if(is_dir($Directory)) { 
		$Objects = scandir($Directory); 
		
		foreach($Objects AS $Object) { 
			if($Object != '.' && $Object != '..') { 
				if(filetype($Directory.'/'.$Object) == 'dir') {
					RecursiveDirRemove($Directory.'/'.$Object); 
				}
				else {
					unlink($Directory.'/'.$Object);
				}
	   		} 
	 	} 
	 
	 	reset($Objects);
	 	
	 	return rmdir($Directory); 
   	}
}

function MakeThumbnail($src, $dst, $width, $height, $crop=0) {
	if(!list($w, $h) = getimagesize($src)) return "Unsupported picture type!";
	
	$type = strtolower(substr(strrchr($src,"."),1));
	if($type == 'jpeg') $type = 'jpg';

	switch($type){
		case 'bmp': $img = imagecreatefromwbmp($src); break;
		case 'gif': $img = imagecreatefromgif($src); break;
		case 'jpg': $img = imagecreatefromjpeg($src); break;
		case 'png': $img = imagecreatefrompng($src); break;
		default : return "Unsupported picture type!";
	}
	
	// resize
	if($crop){
		if($w < $width or $h < $height) return "Picture is too small!";
		$ratio = max($width/$w, $height/$h);
		$h = $height / $ratio;
		$x = ($w - $width / $ratio) / 2;
		$w = $width / $ratio;
	}
	else{
		if($w < $width and $h < $height) return "Picture is too small!";
		$ratio = min($width/$w, $height/$h);
		$width = $w * $ratio;
		$height = $h * $ratio;
		$x = 0;
	}
	
	$new = imagecreatetruecolor($width, $height);
	
	// preserve transparency
	if($type == "gif" or $type == "png"){
		imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
		imagealphablending($new, false);
		imagesavealpha($new, true);
	}
	
	imagecopyresampled($new, $img, 0, 0, $x, 0, $width, $height, $w, $h);

	switch($type){
		case 'bmp': imagewbmp($new, $dst); break;
		case 'gif': imagegif($new, $dst); break;
		case 'jpg': imagejpeg($new, $dst); break;
		case 'png': imagepng($new, $dst); break;
	}
	
	return true;
}

function d($Arr) {
	echo '<pre>'; print_r($Arr); echo '</pre>';
}

function osort(&$Arr, $Property) {
    usort($Arr, function($A, $B) use ($Property) {
    	return $A[$Property] > $B[$Property] ? 1 : -1;
    });	
}

function BytesToHuman($Bytes) {
	$Types = array('B', 'KB', 'MB', 'GB', 'TB');
	
	for($i = 0; $Bytes >= 1024 && $i < (count($Types) - 1); $Bytes /= 1024, $i++);
	
	return(round($Bytes, 2).' '.$Types[$i]);
}

function GetFreeSpacePercentage($FreeSpace, $TotalSpace) {
	$PercentageFree = $FreeSpace ? round($FreeSpace / $TotalSpace, 2) * 100 : 0;
	
	return $PercentageFree;
}

function ParseRelease($Release) {
	$Search  = array(' ', '_', '(', ')', '.-.', '[', ']', '{', '}');
	$Replace = array('.', '.', '',  '',  '.',   '',  '',  '',  '');
	$Release = str_replace($Search, $Replace, $Release);
	
	$SerieRegEx    = '/(.*?)\.?((?:(?:s[0-9]{1,2})?[.-]?e[0-9]{1,2}|[0-9]{1,2}x[0-9]{1,2})(?:[.-]?(?:s?[0-9]{1,2})?[xe]?[0-9]{1,2})*)\.(.*)/i';
	$MovieRegEx    = '/([A-z0-9 \&._\-:\\pL]+)([0-9]{4})(.*)/';
	$TalkShowRegEx = '/([A-z0-9 \&._\-:]+)([0-9]{4}).([0-9]{2}).([0-9]{2})([. ])/';
	
	if(preg_match($SerieRegEx, $Release, $Match)) {
		$ReleaseTitle      = str_replace('.', ' ', str_replace('_', ' ', trim($Match[1])));
  		$ReleaseEpisodeStr = trim($Match[2]);
  		$ReleaseQuality    = str_replace('.', ' ', str_replace('_', ' ', $Match[3]));
  		$ReleaseEpisodes   = array();
  
  		preg_match_all('/\G[.-]?(?:s?([0-9]{1,2}+))?[.-]?[xe]?([0-9]{1,3})/i', $ReleaseEpisodeStr, $Matches, PREG_SET_ORDER);
  	
  		$ReleaseSeason = 'NA';
  		foreach($Matches as $Match) {
			if(isset($Match[1]) && strlen($Match[1]) > 0) {
	  			$ReleaseSeason = (int) $Match[1];
			}
		
			if($ReleaseSeason != 72) {
				$Episodes[] = (int) $Match[2];
			}
  		}
  		
  		if(sizeof($Episodes) > 1) {
  			for($i = min($Episodes); $i <= max($Episodes); $i++) {
  				$ReleaseEpisodes[] = array($ReleaseSeason, $i);
  			}
  		}
  		else {
  			$ReleaseEpisodes[] = array($ReleaseSeason, $Episodes[0]);
  		}
  			  		
  		if(!empty($ReleaseTitle)) {
  			return array('Type'     => 'TV',
  					 	 'Title'    => $ReleaseTitle,
					 	 'Episodes' => $ReleaseEpisodes,
					 	 'Quality'  => $ReleaseQuality);
		}
		else {
			return FALSE;
		}
	}
	else if(preg_match($TalkShowRegEx, $Release, $Match)) {
		return array('Type'  => 'Talk Show',
					 'Title' => trim(str_replace($Replace, $Search, $Match[1])),
					 'Year'  => $Match[2]);
	}
	else if(preg_match($MovieRegEx, $Release, $Match)) {
		if($Match[2] == '1080') {
			preg_match('/([0-9]{4})(.*?)/i', $Match[1], $Matches);
			
			$Match[2] = $Matches[1];
			$Match[1] = str_replace($Match[2], '', trim($Match[1]));
			$Match[3] = $Matches[2].'1080'.$Match[3];
		}
		
		return array('Type'    => 'Movie',
					 'Title'   => trim(str_replace($Replace, $Search, $Match[1])),
					 'Year'    => trim($Match[2]),
					 'Quality' => trim(str_replace('.', ' ', str_replace('_', ' ', $Match[3]))));
	}
	else {
		return FALSE;
	}
}

function AddLog($LogEvent, $LogType, $LogText, $LogError = FALSE, $LogAction = '') {
	$PDO = DB::Get();
	$LogError = (is_array($LogError)) ? implode("\n", $LogError) : $LogError;
	
	try {
		$LogPrep = $PDO->prepare('INSERT INTO
		                          	Log
		                            	(ID,
		                                Date,
		                                Event,
		                                Type,
		                                Error,
		                                Text,
		                                Action)
		                            VALUES
		                                (NULL,
		                                :Date,
		                                :Event,
		                                :Type,
		                                :Error,
		                                :Text,
		                                :Action)');
		                                		
		$LogPrep->execute(array(':Date'   => time(),
							    ':Event'  => $LogEvent,
							    ':Type'   => $LogType,
							    ':Error'  => $LogError,
							    ':Text'   => $LogText,
							    ':Action' => $LogAction));
	}
	catch(PDOException $e) {
		throw new RestException(400, 'MySQL: '.$e->getMessage());
	}
}

function GetDirectorySize($Directory) { 
	$DirSize = 0; 
	foreach(new RecursiveIteratorIterator(new IgnorantRecursiveDirectoryIterator($Directory)) AS $File) { 
		$DirSize += GetFileSize($File); 
	}
	
	return $DirSize; 
} 

function GetFileSize($File) {
	clearstatcache();
	
	$IntegerMax  = 4294967295; // 2147483647+2147483647+1;
	$FileSize    = filesize($File);
	$FilePointer = fopen($File, 'r');
	fseek($FilePointer, 0, SEEK_END);
	
	if(ftell($FilePointer) == 0) {
		$FileSize += $IntegerMax;
	}
	
	fclose($FilePointer);
	
	if($FileSize < 0) {
		$FileSize += $IntegerMax;
	}
	
	return $FileSize;
}

function ShortText($Str, $Limit, $Reverse = FALSE) {
	if($Reverse) {
		$Str = substr($Str, $Limit, strlen($Str));
	}
	else {
		$Str = substr($Str, 0, $Limit);
	}
	
	return $Str;
}

function ConcatFilePath($Path) {
	$Path = preg_replace('/.+\:.+@/', '//', $Path);
	
	if(strstr($Path, 'stack')) {
		$Path = str_replace('stack://', '', $Path);
		
		$FileArr = explode(',', $Path);
		
		$ConcatFileArr = array();
		foreach($FileArr AS $File) {
			$ConcatFileArr[] = trim(ConcatFilePath($File));
		}
		
		return implode('<br />', $ConcatFileArr);
	}
	else {
		$Path = str_replace('smb:', '', $Path);
		$Path = str_replace('\\', '/', $Path);
		$First = strpos($Path, 'Media');
		$Last = strrpos($Path, '/');
	
		$First = substr($Path, 0, $First);
		$Last = substr($Path, $Last, strlen($Path));
		
		if($First && $Last) {
			return $First.' … '.$Last;
		}
		else {
			return FALSE;
		}
	}
}

function RecursiveDirSearch($Directory, $Extensions = null) {
	try {
		$Iterator = new IgnorantRecursiveDirectoryIterator($Directory);
		$Extensions = (!is_array($Extensions)) ? array('mpeg', 'mpg', 'mp4', 'mkv', 'avi', 'rar', 'wmv') : $Extensions;
	
		$Files = array();
		foreach(new RecursiveIteratorIterator($Iterator, RecursiveIteratorIterator::SELF_FIRST) AS $Object) {
			if($Object->isFile()) {
				$File = str_replace('\\', '/', $Object->__toString());
				$FileInfo = pathinfo($File);
		
				if(array_key_exists('extension', $FileInfo) && in_array($FileInfo['extension'], $Extensions)) {
					$Files[] = $File;
				}
			}
		}
	}
	catch(UnexpectedValueException $e) {
		throw new RestException(400, $e->getMessage());
	}
	
	return $Files;
}

class IgnorantRecursiveDirectoryIterator extends RecursiveDirectoryIterator {
	function getChildren() {
		try {
			return parent::getChildren();
		}
		catch(UnexpectedValueException $e) {
			return new RecursiveArrayIterator(array());
		}
	}
}
?>