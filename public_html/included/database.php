<?php
  // F1 adatbázis
  $f1db = mysqli_connect('localhost', 'root', '', 'f1')
	or die (mysqli_error());
  
  // STB adatbázis
  $sdb  = mysqli_connect('localhost', 'root', '', 'f1'); // Egy adatbázisom van...
  
  //mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $f1db);
  //mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $sdb);
?>