<?php
class TheTVDBAPI {
	private static $APIKey     = '';
	
	public $XMLMirror          = '';
	public $BannerMirror       = '';
	public $ZipMirror          = '';
	public $TheTVDBServerTime  = '';
	
	public $Language           = 'en';
	public $PreviousUpdateTime = '';
	
	public $TemporaryFolder    = '';
	
	function __construct($APIKey) {
		self::$APIKey = $APIKey;
		$this->TemporaryFolder = APP_PATH.'/tmp';
		
		try {
			if(!$this->SetMirrors() || !$this->GetServerTime() || !$this->SetTemporaryFolder()) {
				return FALSE;
			}
		}
		catch(Exception $e) {
			echo '<strong>Exception:</strong> '.$e->getMessage().' in '.$e->getTraceAsString();
		}
	}
	
	private function SetTemporaryFolder() {
		if(!is_dir($this->TemporaryFolder)) {
			return FALSE;
			
			// throw new Exception($this->TemporaryFolder.' does not exist');
		}
	}
	
	private function SetMirrors() {
		if($Mirrors = @simplexml_load_file('http://www.thetvdb.com/api/'.self::$APIKey.'/mirrors.xml')) {
			$XMLMirror = $BannerMirror = $ZipMirror = array();
			foreach($Mirrors AS $Mirror) {
				if($Mirror->typemask & 1) {
					$XMLMirror[] = $Mirror->mirrorpath;
				}
				if($Mirror->typemask & 2) {
					$BannerMirror[] = $Mirror->mirrorpath;
				}
				if($Mirror->typemask & 4) {
					$ZipMirror[] = $Mirror->mirrorpath;
				}
			}
			
			$this->XMLMirror    = $XMLMirror[rand(0,    (sizeof($XMLMirror)    - 1))];
			$this->BannerMirror = $BannerMirror[rand(0, (sizeof($BannerMirror) - 1))];
			$this->ZipMirror    = $ZipMirror[rand(0,    (sizeof($ZipMirror)    - 1))];
			
			return TRUE;
		}
		else {
			return FALSE;
			
			// throw new Exception('Unable to load http://www.thetvdb.com/api/'.self::$APIKey.'/mirrors.xml');
		}
	}
	
	public function GetServerTime() {
		if(!$ServerTime = @file_get_contents('http://www.thetvdb.com/api/Updates.php?type=none')) {
			return FALSE;
			
			// throw new Exception('Unable to get http://www.thetvdb.com/api/Updates.php?type=none');
		}
		
		$this->TheTVDBServerTime = $ServerTime;
		
		return $this->TheTVDBServerTime;
	}
	
	public function GetLanguage() {
		if(!$Languages = @simplexml_load_file($this->XMLMirror.'/api/'.self::$APIKey.'/languages.xml')) {
			throw new Exception('Unable to load '.$this->XMLMirror.'/api/'.self::$APIKey.'/languages.xml');
		}
		
		return $Languages;
	}
	
	public function SetLanguage($Language) {
		$this->Language = $Language;
	}
	
	public function GetSeries($SearchStr, $Language = 'en') {
		$Language = empty($this->Language) ? $this->Language : 'en';
		
		if(!$Series = @simplexml_load_file('http://www.thetvdb.com/api/GetSeries.php?seriesname='.$SearchStr.'&language='.$Language)) {
			return FALSE;
			
			// throw new Exception('Unable to get http://www.thetvdb.com/api/GetSeries.php?seriesname='.$SearchStr.'&language='.$Language);
		}
		
		return $Series;
	}
	
	public function GetSeriesInfo($SerieID, $Language = 'en') {
		/*
		a. Retrieve <mirrorpath_zip>/api/<apikey>/series/<seriesid>/all/<language>.zip and extract <language>.xml and banners.xml.
		b. Process the XML data in <language>.xml and store all <Series> data.
		c. Download each series banner in banners.xml and prompt the user to see which they want to keep.
		Note: Make sure you record <id> from each series, since it's returned in updates as <Series>.
		*/
		$Language = empty($this->Language) ? $this->Language : 'en';
		
		if($this->ZipMirror) {
			$ZipFile = $this->ZipMirror.'/api/'.self::$APIKey.'/series/'.$SerieID.'/all/'.$Language.'.zip';
			
			if(@copy($ZipFile, $this->TemporaryFolder.'/'.$SerieID.'.zip')) {
				$ZipArchive = new ZipArchive;
				
				if(@$ZipArchive->open($this->TemporaryFolder.'/'.$SerieID.'.zip') === TRUE) {
			    	if(@$ZipArchive->extractTo($this->TemporaryFolder.'/'.$SerieID.'/')) {
			    		$ZipArchive->close();
			    	
			    		if(!$SeriesInfo = @simplexml_load_file($this->TemporaryFolder.'/'.$SerieID.'/'.$Language.'.xml')) {
			    			return FALSE;
			    			
			    			// throw new Exception('Unable to load '.$this->TemporaryFolder.'/'.$SerieID.'/'.$Language.'.xml');
			    		}
			    	
			    		if(is_dir($this->TemporaryFolder.'/'.$SerieID.'/')) {
			    			if(!$this->RecursiveRmDir($this->TemporaryFolder.'/'.$SerieID.'/')) {
			    				return FALSE;
			    				
			    				// throw new Exception('Unable to delete '.$this->TemporaryFolder.'/'.$SerieID.'.zip');
			    			}
			    		}
			    		
			    	}
			    	else {
			    		return FALSE;
			    		
			    		// throw new Exception('Unable to extract '.$this->TemporaryFolder.'/'.$SerieID.'.zip');
			    	}
				}
				else {
			    	return FALSE;
			    	
			    	// throw new Exception($this->TemporaryFolder.'/'.$SerieID.'.zip is an invalid zip file');
				}
				
				if(is_file($this->TemporaryFolder.'/'.$SerieID.'.zip')) {
					if(!unlink($this->TemporaryFolder.'/'.$SerieID.'.zip')) {
						return FALSE;
						
						// throw new Exception('Unable to delete '.$this->TemporaryFolder.'/'.$SerieID.'.zip');
					}
				}
			}
			else {
				return FALSE;
				
				// throw new Exception('Unable to copy '.$ZipFile.' to '.$this->TemporaryFolder.'/'.$SerieID.'.zip');
			}
			
			return $SeriesInfo;
		}
		else {
			return FALSE;
		}
	}
	
	public function SetPreviousUpdateTime($Time = '') {
		$this->PreviousUpdateTime = empty($Time) ? time() : $Time;
	}
	
	private function RecursiveRmDir($dir) {
		if(is_dir($dir)) { 
			$objects = scandir($dir); 
			
			foreach($objects as $object) { 
				if($object != '.' && $object != '..') { 
		    		if(filetype($dir.'/'.$object) == 'dir') {
		    			rrmdir($dir.'/'.$object); 
		    		}
		    		else {
		    			@unlink($dir.'/'.$object);
		    		}
		   		} 
		 	} 
		 
		 	reset($objects); 
		 	return @rmdir($dir); 
		}
	}
	
	public function GetBanner($Banner) {
		return $this->BannerMirror.'/banners/_cache/'.$Banner;
	}
	
	public function GetFanart($Fanart) {
		return $this->BannerMirror.'/banners/_cache/'.$Fanart;
	}
	
	public function GetPoster($Poster) {
		return $this->BannerMirror.'/banners/_cache/'.$Poster;
	}
	
	public function GetAllUpdates() {
		/*
		a. Retrieve http://www.thetvdb.com/api/Updates.php?type=all&time=<previoustime>.
		b. Process the returned XML and loop through each series (<seriesid>) and episode (<episodeid>) entry.
		*/
		if(!$Updates = @simplexml_load_file('http://www.thetvdb.com/api/Updates.php?type=all&time='.$this->PreviousUpdateTime)) {
			return FALSE;
			
			// throw new Exception('Unable to get http://www.thetvdb.com/api/Updates.php?type=all&time='.$this->PreviousUpdateTime);
		}
		
		return $Updates;
	}
	
	public function GetSerieUpdates($SerieID, $Language = 'en') {
		$Language = empty($this->Language) ? $this->Language : 'en';
		
		if(!$Series = @simplexml_load_file($this->XMLMirror.'/api/'.self::$APIKey.'/series/'.$SerieID.'/'.$Language.'.xml')) {
			return FALSE;
			
			// throw new Exception('Unable to load '.$this->XMLMirror.'/api/'.self::$APIKey.'/series/'.$SerieID.'/'.$Language.'.xml');
		}
		
		return $Series->Series;
	}
	
	public function GetEpisodeUpdates($EpisodeID, $Language = 'en') {
		$Language = empty($this->Language) ? $this->Language : 'en';
		
		if(!$Episodes = @simplexml_load_file($this->XMLMirror.'/api/'.self::$APIKey.'/episodes/'.$EpisodeID.'/'.$Language.'.xml')) {
			return FALSE;
			
			// throw new Exception('Unable to load '.$this->XMLMirror.'/api/'.self::$APIKey.'/episodes/'.$EpisodeID.'/'.$Language.'.xml');
		}
		
		return $Episodes->Episode;
	}
	
	public function User_PreferredLanguage($AccountID) {
		/*
		<mirrorpath>/api/User_PreferredLanguage.php?accountid=<accountidentifier>
		
		<accountidentifier>
		This is the unique identifier assigned to every user. They can access this value by visiting the account settings page on the site. This is a 16 character alphanumeric string, but you should program your applications to handle id strings up to 32 characters in length.
		*/
		
		if(!$PreferredLanguage = @simplexml_load_file($this->XMLMirror.'/api/User_PreferredLanguage.php?accountid='.$AccountID)) {
			return FALSE;
			
			// throw new Exception('Unable to get '.$this->XMLMirror.'/api/User_PreferredLanguage.php?accountid='.$AccountID);
		}
		
		return $PreferredLanguage;
	}
	
	public function User_Favorites($AccountID, $Type = '', $SeriesID = '') {
		/*
		<mirrorpath>/api/User_Favorites.php?accountid=<accountidentifier>
		<mirrorpath>/api/User_Favorites.php?accountid=<accountidentifier>&type=<type>&seriesid=<seriesid>
		
		<accountidentifier>
		This is the unique identifier assigned to every user. They can access this value by visiting the account settings page on the site. This is a 16 character alphanumeric string, but you should program your applications to handle id strings up to 32 characters in length.
		
		<type>
		This is an optional field. If set, it should be either add or remove. Add will add <seriesid> to the user's favorites list, while Remove will remove <seriesid> from their list. If this field is not passed, the list is just returned without any modifications.
		
		<seriesid>
		This is a required field ONLY when <type> is set. This is the id of the series you're adding or removing for the user.
		*/
		
		$APIPath = $this->XMLMirror.'/api/User_Favorites.php?accountid='.$AccountID;
		if(!empty($Type) && ($Type == 'add' || $Type == 'remove')) {
			if(!empty($SeriesID) && is_int($SeriesID)) {
				if(!$Favorites = @simplexml_load_file($APIPath.'&type='.$Type.'&seriesid='.$SeriesID)) {
					return FALSE;
					
					// throw new Exception('Unable to get '.$APIPath.'&type='.$Type.'&seriesid='.$SeriesID);
				}
			}
			else {
				return FALSE;
				
				// throw new Exception('$SeriesID is required to add a new favorite');
			}
		}
		else {
			if($Favorites = @simplexml_load_file($APIPath)) {
				return $Favorites;
			}
			else {
				return FALSE;
				
				// throw new Exception('Unable to get '.$APIPath);
			}
		}
		
		return TRUE;
	}
	
	
	public function User_Rating($AccountID, $ItemType = '', $ItemID = '', $Rating = 0) {
		/*
		<mirrorpath>/api/User_Rating.php?accountid=<accountidentifier>&itemtype=<itemtype>&itemid=<itemid>&rating=<rating>
		
		<accountidentifier>
		This is the unique identifier assigned to every user. They can access this value by visiting the account settings page on the site. This is a 16 character alphanumeric string, but you should program your applications to handle id strings up to 32 characters in length.
		
		<itemtype>
		This is either series or episode, depending on which one they're rating.
		
		<itemid>
		This is the series id or episode id, depending on whether they're rating a series or episode.
		
		<rating>
		This is an integer value from 0 to 10. If 0 is sent, any existing user ratings for this series/episode will be removed. If a value from 1 to 10 is sent, that rating will be recorded for this user.
		*/
		
		if(strtolower($ItemType) == 'series') { //  || strtolower($ItemType) == 'episode')
			if(!empty($ItemID) && is_int($ItemID)) {
				if($Rating <= 10 && $Rating >= 0) {
					if($Rating = @simplexml_load_file($this->XMLMirror.'/api/User_Rating.php?accountid='.$AccountID.'&itemtype='.$ItemType.'&itemid='.$ItemID.'&rating='.$Rating)) {
						if($Rating->result != 'rating updated') {
							return FALSE;
							
							// throw new Exception('Something went wrong with your rating update');
						}
					}
					else {
						return FALSE;
						
						// throw new Exception('Unable to get '.$this->XMLMirror.'/api/User_Favorites.php?accountid='.$AccountID);
					}
				}
				else {
					return FALSE;
					
					// throw new Exception('$Rating has to be between 0-10');
				}
			}
			else {
				return FALSE;
				
				// throw new Exception('$ItemID is not an integer value or is empty');
			}
		}
		else {
			return FALSE;
			
			// throw new Exception('$ItemType does not match either "series" nor "episode"');
		}
		
		return TRUE;
	}
}

/*
$tv = new TheTVDB(THETVDB_API_KEY);
try {
	$tv->SetPreviousUpdateTime(time() - (60 * 10)); // Timestamp
	$tv->SetLanguage('en');

	$Languages = $tv->GetLanguage();
	echo '<strong>$Languages</strong><pre>'; print_r($Languages); echo '</pre>';
	foreach($Languages AS $Language) {
		// id
		// name
		// abbreviation
	}

	$Series = $tv->GetSeries('Lost'); // $SearchStr, $Language = 'en'
	echo '<strong>$Series</strong><pre>'; print_r($Series); echo '</pre>';
	foreach($Series AS $Serie) {
		// seriesid
		// language
		// SeriesName
		// banner // http://www.thetvdb.com/banners/_cache
		// Overview
		// FirstAired
		// id
	}
	
	$SeriesInfo = $tv->GetSeriesInfo('73739'); // $SerieID
	echo '<strong>$SeriesInfo</strong><pre>'; print_r($SeriesInfo); echo '</pre>';
	foreach($SeriesInfo AS $Serie) {
		if($Serie->SeriesName) {
			// serie
		
			// [id] => 73739
	    	// [Airs_DayOfWeek] => Tuesday
	    	// [Airs_Time] => 9:00 PM
	    	// [ContentRating] => TV-14
	    	// [FirstAired] => 2004-09-22
	    	// [Genre] => |Action and Adventure|Drama|Science-Fiction|
	    	// [IMDB_ID] => tt0411008
	    	// [Language] => en
	    	// [Network] => ABC
	    	// [Overview] => After their plane, Oceanic Air flight 815, tore apart whilst thousands of miles off course, the survivors find themselves on a mysterious deserted island where they soon find out they are not alone.
	    	// [Rating] => 9.1
	    	// [RatingCount] => 532
	    	// [Runtime] => 60
	    	// [SeriesID] => 24313
	    	// [SeriesName] => Lost
	    	// [Status] => Ended
	    	// [banner] => graphical/73739-g4.jpg
	    	// [fanart] => fanart/original/73739-34.jpg
	    	// [lastupdated] => 1298784452
	    	// [poster] => posters/73739-7.jpg
		}
		else {
			// episode
			
			// [id] => 323552 // Make sure you record <id> from each episode, since it's returned in updates as <Episode>.
	    	// [Combined_episodenumber] => 20
	    	// [Combined_season] => 2
	    	// [EpisodeName] => Two for the Road
	    	// [EpisodeNumber] => 20
	    	// [FirstAired] => 2006-05-03
	    	// [Language] => en
	    	// [Overview] => After finding an exhausted Michael in the forest, Jack and Kate bring him back to the main camp. When he finally wakes up, Michael has some new details about "The Others." Also, a lovestruck Hurley plans a date for Libby.
	    	// [Rating] => 7.9
	    	// [RatingCount] => 28
	    	// [SeasonNumber] => 2
	    	// [filename] => episodes/73739/323552.jpg // <mirrorpath_banners>/banners/<filename>
	    	// [lastupdated] => 1273646666
	    	// [seasonid] => 6346
	    	// [seriesid] => 73739
		}
	}
	
	$Updates = $tv->GetAllUpdates();
	echo '<strong>$Updates</strong><pre>'; print_r($Updates); echo '</pre>';
	foreach($Updates->Series AS $SerieID) {
		if(1) { // If $SerieID is in the database, get updates
			// $tv->GetSerieUpdates($SerieID);
		}
	}
	
	foreach($Updates->Episode AS $EpisodeID) {
		if(1) { // If $EpisodeID is in the database, get updates
			// $tv->GetSerieUpdates($EpisodeID);
		}
		else if(0) { // If $EpisodeID is not in database, but $SerieID is, add to database
			// $tv->GetEpisodeUpdates($EpisodeID);
		}
	}
	
	$SerieUpdates = $tv->GetSerieUpdates('73739'); // $SerieID, $Language = 'en'
	echo '<strong>$SerieUpdates</strong><pre>'; print_r($SerieUpdates); echo '</pre>';
	foreach($SerieUpdates->Series AS $Serie) {
		// update series in database
				
		// [id] => 71326
	    // [Actors] => |Craig Charles|Chris Barrie|Danny John-Jules|Norman Lovett|Robert Llewellyn|Hattie Hayridge|Chloe Annett|Claire Patricia Grogan|
	    // [Airs_DayOfWeek] => SimpleXMLElement Object
	    //             (
	    //             )
	
	    // [Airs_Time] => SimpleXMLElement Object
	    //             (
	    //             )
	
	    // [ContentRating] => TV-PG
	    // [FirstAired] => 1988-02-15
	    // [Genre] => |Comedy|Science-Fiction|
	    // [IMDB_ID] => tt0094535
	    // [Language] => en
	    // [Network] => BBC Two
	    // [NetworkID] => SimpleXMLElement Object
	    //             (
	    //             )
	
	    // [Overview] => Three million years after the demise of humanity, third technician Dave Lister awakes aboard the mining ship Red Dwarf. Sentenced to a period of suspended animation for smuggling his pet cat on board, he is joined by just four fellow survivors: second technician Arnold J Rimmer, a sneering-yet-inept hologram based on his one-time superior; Holly, a ship's computer reduced to near-senility by eons adrift in space; a humanoid descendant of the cat obsessed with fashion and fish; and Kryten, a salvaged android programmed to serve his useless companions. Together, this bickering band must come to terms with an existence which, in terms of productivity and purpose, isn't that far removed from its old one.
	    // [Rating] => 9.0
	    // [RatingCount] => 78
	    // [Runtime] => 30
	    // [SeriesID] => 132
	    // [SeriesName] => Red Dwarf
	    // [Status] => Continuing
	    // [added] => SimpleXMLElement Object
	    //             (
	    //             )
	
	    // [addedBy] => SimpleXMLElement Object
	    //             (
	    //             )
	
	    // [banner] => graphical/71326-g11.jpg
	    // [fanart] => fanart/original/71326-28.jpg
		// [lastupdated] => 1298795456
		// [poster] => posters/71326-3.jpg
		// [zap2it_id] => SH003584
	}
	
	$EpisodeUpdates = $tv->GetEpisodeUpdates('323552'); // $EpisodeID, $Language = 'en'
	echo '<strong>$EpisodeUpdates</strong><pre>'; print_r($EpisodeUpdates); echo '</pre>';
	foreach($EpisodeUpdates->Episode AS $Episode) {
		// update episodes in database
		    		
		// [id] => 301420
	    // [Combined_episodenumber] => 1
	    // [Combined_season] => 0
	    // [EpisodeName] => Unaired Pilot
	    // [EpisodeNumber] => 1
	    // [FirstAired] => SimpleXMLElement Object
	    //                     (
	    //                     )
	    // [Language] => en
	    // [Overview] => The Unaired Pilot takes place in the past. It tells the story of Karen and Dan and how she got pregnant. Then, after Dan marries Deb it goes into the future further where Lucas has his first day at Tree Hill High. He meets mouth and others and realizes that maybe basketball is for him. That is until him and Nathan get into a fight about Dan. This is where it all begins.
	    // [ProductionCode] => 100
	    // [Rating] => 10.0
	    // [RatingCount] => 1
	    // [SeasonNumber] => 0
		// [filename] => SimpleXMLElement Object
	    //                     (
	    //                     )
	    // [lastupdated] => 1179686128
		// [seasonid] => 19625
		// [seriesid] => 72158
	}
	
	/*
	$PreferredLanguage = $tv->User_PreferredLanguage('3D00D0BC14688242'); // $AccountID
	echo '<strong>$PreferredLanguage</strong><pre>'; print_r($PreferredLanguage); echo '</pre>';
	foreach($PreferredLanguage->Languages AS $Language) {
		// id
		// abbreviation
		// name
	}
	
	$Favorites = $tv->User_Favorites('3D00D0BC14688242'); // $AccountID
	echo '<strong>$Favorites</strong><pre>'; print_r($Favorites); echo '</pre>';
	foreach($Favorites->Favorites AS $Favorite) {
		// series // id
	}
}
catch(Exception $e) {
	echo '<strong>Exception:</strong> '.$e->getMessage().' in '.$e->getTraceAsString();
}
*/
?>