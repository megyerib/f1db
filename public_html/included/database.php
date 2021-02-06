<?php
  // F1 adatbázis
  $f1db = mysqli_connect('localhost', 'f1db', 'ecsyuJUfC6zV', 'f1')
	or die (mysqli_error());
  
  // STB adatbázis
  $sdb  = mysqli_connect('localhost', 'f1db', 'ecsyuJUfC6zV', 'f1'); // Egy adatbázisom van...
?>