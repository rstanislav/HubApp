<?php
$sql = array();
$sql[0] = "CREATE TABLE IF NOT EXISTS `seeding` (
  `SeedingID` int(4) NOT NULL AUTO_INCREMENT,
  `SeedingDate` int(10) NOT NULL,
  `SeedingFile` varchar(255) NOT NULL,
  PRIMARY KEY (`SeedingID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
?>