<?php
$ip = $_SERVER['REMOTE_ADDR'];
/*$addr = mysqli_query($sdb,
	"SELECT *
	FROM ip
	WHERE ip = '$ip'
	AND valid = 1
	LIMIT 1");*/

if ($ip != '84.3.137.138') {		
	echo '<h1>Maintenance</h1>We\'ll be back soon.<p><a href="https://twitter.com/racedatanet">Follow us on twitter!</a></p>';
	die();
}
?>