<?php
$rdb = new mysqli('localhost', 'root', '', 'f1_new');
if ($rdb->connect_error) {
	die("Error<br>Database connection failed<br>".$rdb->connect_error);
}
?>
