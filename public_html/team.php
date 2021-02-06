<?php
include('resources/head.php');
$teamId = $_GET['team'];

$teamQuery = mysqli_query($f1db,
	"SELECT *
	FROM team
	WHERE team.id = '$teamId'
	AND entrant = 1
	LIMIT 1"
);
	
// Ha nincs
if (mysqli_num_rows($teamQuery) == 0) {
	header('Location: /team');
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

// Aktív
ob_start();

$active = mysqli_query($f1db,
	"SELECT *
	FROM f1_active_team
	WHERE no = $teamNo
	LIMIT 1"
);

if (mysqli_num_rows($active) > 0) {
	$active = mysqli_fetch_array($active);
	
	$engine  = $active['engine'];
	$chassis = $active['chassis'];
	$tyre    = $active['tyre'];
	
	// Kasztni
	$chassis = mysqli_query($f1db,
		"SELECT *, chassis.no AS ch_no
		FROM chassis
		INNER JOIN team ON chassis.cons = team.no
		WHERE chassis.no = $chassis
		LIMIT 1");
	
	$chassis = mysqli_fetch_array($chassis);
	$chassis = linkChassis($chassis['id'], $chassis['ch_no'], chassisName($chassis['fullname'], $chassis['type']));
	// Motor
	$engine = mysqli_query($f1db,
		"SELECT *, engine.no AS eng_no
		FROM engine
		INNER JOIN team ON engine.cons = team.no
		WHERE engine.no = $engine
		LIMIT 1");
	
	$engine = mysqli_fetch_array($engine);
	$name   = engineName($engine['fullname'], $engine['type'], $engine['volume'], $engine['concept'], $engine['cylinders'], $engine['turbo']);
	$engine = linkEngine($engine['id'], $engine['eng_no'], $name);
	
	// Gumi
	$tyre = mysqli_query($f1db,
		"SELECT *
		FROM tyre
		WHERE id = '$tyre'
		LIMIT 1");
	$tyre = mysqli_fetch_array($tyre);
	$tyre = linkTyre($tyre['id'], $tyre['fullname']);
	
	// Kiírás
	echo '<b>Chassis</b>: '.$chassis.'<br>';
	echo '<b>Engine</b>: ' .$engine. '<br>';
	echo '<b>Tyres</b>: '  .$tyre.   '<br>';
	
	// Bajnokság
	/*$cship = mysqli_query($f1db,
		"SELECT *
		FROM f1_*/
}

$teamActive = ob_get_contents();
ob_clean();







// Kiírás
echo "<h1>$teamName</h1>";

// Jobb oldal
echo '<div style="float:right;">';
	echo $teamLogo;
echo '</div>';

// Aktív szezon

// Aktív
if (!empty($teamActive)) {
	echo '<div class="double '.$teamId.'">'.$teamActive.'</div>';
}


include('resources/foot.php');
?>
