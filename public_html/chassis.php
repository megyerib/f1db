<?php
include('resources/head.php');

$chassisNo     = $_GET['chassis'];
$chassisConsId = $_GET['cons'];

$chassisQuery = mysqli_query($f1db,
	"SELECT t.fullname cons, c.type
	FROM chassis c
	JOIN team t ON c.cons = t.no
	WHERE c.no = $chassisNo
	AND c.cons = (SELECT no FROM team WHERE id = '$chassisConsId')
	LIMIT 1"
);
$chassisBasic = mysqli_fetch_array($chassisQuery);
$chassisName = chassisName($chassisBasic['cons'], $chassisBasic['type']);

// Kiírás
echo '<h1>'.$chassisName.' chassis</h1>';

include('resources/foot.php');
?>
