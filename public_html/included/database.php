<?php
  // F1 adatbázis
  $f1db = mysqli_connect('localhost', 'root', '', 'f1')
	or die (mysqli_error());
  mysqli_set_charset($f1db, 'utf8');
  
  // STB adatbázis
  $sdb  = mysqli_connect('localhost', 'root', '', 'f1'); // Egy adatbázisom van...
  mysqli_set_charset($sdb, 'utf8');
?>