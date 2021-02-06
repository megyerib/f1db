<?php
include('resources/head.php');
$teamId = $_GET['cons'];

$teamQuery = mysqli_query($f1db,
	"SELECT *
	FROM team
	WHERE team.id = '$teamId'
	AND chassis = 1
	LIMIT 1"
);
	
// Ha nincs
if (mysqli_num_rows($teamQuery) == 0) {
	header('Location: /chassis');
}

// Alapadatok
$teamQuery = mysqli_fetch_array($teamQuery);
$teamName  = $teamQuery['fullname'];
$teamNo  = $teamQuery['no'];

// Logo
ob_start();

$logoImg = img("img/team/$teamId.png", 'max-width:300px; max-height:300px;');

if ($logoImg) {
	echo '<div style="text-align:center; padding:10px;">'.$logoImg.'</div>';
}

$teamLogo = ob_get_contents();
ob_clean();

// Kasztnik
ob_start();

$chassises = mysqli_query($f1db,
	"SELECT *
	FROM chassis
	WHERE cons = $teamNo
	ORDER BY no"
);
while ($row = mysqli_fetch_array($chassises)) {
	echo linkChassis($teamId, $row['no'], $row['type']?$row['type']:'Unknown').'<br>';
}

$divChassises = ob_get_contents();
ob_clean();


// Kiírás
echo "<h1>$teamName chassises</h1>";

// Jobb oldal
echo '<div style="float:right;">';
	echo $teamLogo;
echo '</div>';

// Felsorolás
echo '<div class="double">';
	echo $divChassises;
echo '</div>';


include('resources/foot.php');
?>
