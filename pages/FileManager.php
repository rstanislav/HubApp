<script type="text/javascript">
$('a[id|=FileManagerMove]').each(function() {
	$(this).qtip({
		content: {
			text: '<img src="images/spinners/ajax-light.gif" alt="Loading..." />',
            ajax: {
				url: $(this).attr('rel')
			},
         },
         position: {
            at: 'left top', // Position the tooltip above the link
            my: 'right center',
            viewport: $(window), // Keep the tooltip on-screen at all times
            effect: false, // Disable positioning animation
            container: $('#maincontent')
         },
         show: {
            event: 'click',
            solo: true // Only show one tooltip at a time
         },
         hide: 'click',
         style: {
            classes: 'ui-tooltip-shadow', 
            tip: {
            	size: {
            		x: 10,
                	y: 5
            	}
            }
         }
	})
}).click(function(event) { event.preventDefault(); });
</script>
<?php
if(filter_has_var(INPUT_GET, 'crumbs')) {
	if(array_key_exists(0, $_GET['crumbs'])) {
		$Drive = $DrivesObj->GetDriveByLetter($_GET['crumbs'][0]);
		
		if(is_array($Drive)) {
			$Crumb = $Drive['DriveLetter'];
			
			if(sizeof($_GET['crumbs']) > 1) {
				for($i = 1; $i <= (sizeof($_GET['crumbs']) - 1); $i++) {
					$Crumb .= '/'.$_GET['crumbs'][$i];
				}
			}
		}
	}
	
	$Crumbs = explode('/', $Crumb);
	
	$Bread = '';
	$y = '';
	foreach($Crumbs AS $x) {
		$y .= '/'.$x;
		$Bread .= ' / <a href="#!/FileManager'.$y.'">'.$x.'</a>';
	}
	
	echo '<div class="head"><a href="#!/FileManager">File Manager</a> '.$Bread.' <small style="font-size: 12px;">(<a href="#!/Help/FileManager">?</a>)</small></div>';
	
	$HiddenFiles = array('$RECYCLE.BIN', 'pagefile.sys', 'System Volume Information');
	$Iterator = new HubDirectoryIterator(($Drive['DriveNetwork']) ? str_replace($Drive['DriveLetter'], $Drive['DriveRoot'], $Crumb) : $Crumb);
	echo '
	<table>
	 <thead>
	 <tr>
	  <th>Name</th>
	  <th style="width:100px;">Size</th>
	  <th style="width: 32px; text-align: center">Action</th>
	 </tr>
	 </thead>'."\n";
	
	$i = 0;
	foreach($Iterator AS $Obj) {
		$RandomID = $HubObj->GetRandomID();
		if(!$Obj->isDot() && !in_array($Obj->__toString(), $HiddenFiles) && substr($Obj->__toString(), 0, 1) != '.') {
			if($Obj->isFile()) {
				$Name = $Obj->getFilename();
				$Size = $HubObj->BytesToHuman($Obj->getSize());
			}
			else {
				$Name = '<a href="#!/FileManager/'.$Crumb.'/'.$Obj->getFilename().'">'.$Obj->getFilename().'</a>';
				$Size = 'NA';
			}
			
			if($Obj->isWritable()) {
				$MoveLink   = '<a id="FileManagerMove-'.$RandomID.'" title="Move \''.$Obj->__toString().'\'" rel="load.php?page=FileManagerMove&Move='.urlencode($Obj->getPath().'/'.$Obj->__toString()).'&ID='.$RandomID.'"><img src="images/icons/file_move.png" /></a>';
				
				if($Obj->isFile()) {
					$DeleteLink = '<a id="FileManagerFileDelete-'.$RandomID.'" title="Delete \''.$Obj->__toString().'\'" rel="'.$Obj->getPath().'/'.$Obj->__toString().'"><img src="images/icons/file_delete.png" /></a>';
				}
				else {
					$DeleteLink = '<a id="FileManagerFolderDelete-'.$RandomID.'" title="Delete \''.$Obj->__toString().'\'" rel="'.$Obj->getPath().'/'.$Obj->__toString().'"><img src="images/icons/folder_delete.png" /></a>';
				}
			}
			else {
				$MoveLink = $DeleteLink = '';
			}
			
			echo '
			<tr id="FileManager-'.$RandomID.'">
			 <td>'.$Name.'</td>
			 <td>'.$Size.'</td>
			 <td style="text-align: center">
			  '.$MoveLink.'
			  '.$DeleteLink.'
			 </td>
			</tr>'."\n";
			
			$i++;
		}
	}
	
	if(!$i) {
		echo '
		<tr>
		 <td colspan="3"><strong>This directory is empty</strong></td>
		</tr>'."\n";
	}
	
	echo '</table>'."\n";
}
else {
	echo '<div class="head">File Manager <small style="font-size: 12px;">(<a href="#!/Help/FileManager">?</a>)</small></div>';
	
	$Drives = $DrivesObj->GetDrivesFromDB();

	if(is_array($Drives)) {
		echo '
		<table>
		 <thead>
		 <tr>
		  <th>Name</th>
		 </tr>
		 </thead>'."\n";
		
		foreach($Drives AS $Drive) {
			$DriveNetwork = ($Drive['DriveNetwork']) ? ' ('.$Drive['DriveRoot'].')' : '';
			
			$DriveTxt = ($Drive['DriveActive']) ? '<strong>'.$Drive['DriveLetter'].$DriveNetwork.'</strong>' : $Drive['DriveLetter'].$DriveNetwork;
			echo '
			<tr>
			 <td><a href="#!/FileManager/'.$Drive['DriveLetter'].'">'.$DriveTxt.'</a></td>
			</tr>'."\n";
		}
		
		echo '</table>'."\n";
	}
}
?>