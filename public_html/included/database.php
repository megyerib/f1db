<?php
  // F1 adatbázis
  $f1db = mysqli_connect('localhost', 'root', '', 'f1')
	or die (mysqli_error());
  
  // STB adatbázis
  $sdb  = mysqli_connect('localhost', 'root', '', 'race-data');
?>