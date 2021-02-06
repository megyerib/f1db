<?php
include('resources/head.php');
$circuitId = $_GET['circuit'];

$circuitQuery = mysqli_query($f1db,
	"SELECT *
	FROM circuit
	WHERE id = '$circuitId'"
);
$row = mysqli_fetch_array($circuitQuery);
$fullname = $row['fullname'];

echo "<h1>$fullname</h1>";

echo img("img/circuit/$circuitId", 'max-width:500px; max-height:500px;');

include('resources/foot.php');
?>
