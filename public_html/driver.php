<?php
include('resources/head.php');
$driverId = $_GET['driver'];

$driverQuery = mysqli_query($f1db,
	"SELECT * 
	FROM driver
	INNER JOIN country
	ON (driver.country = country.gp)
	WHERE driver.id = '$driverId'
	LIMIT 1"
);
	
// Ha nincs
if (mysqli_num_rows($driverQuery) == 0) {
	header('Location: /driver');
}

$driverQuery = mysqli_fetch_array($driverQuery);
$driverName  = name($driverQuery['first'], $driverQuery['de'], $driverQuery['last'], $driverQuery['sr']);
$driverNo    = $driverQuery['no'];

// Fénykép
ob_start();

echo $driverPhotoOk = img("img/driver/$driverId", 'width:292px;');

$photo = ob_get_contents();
ob_clean();

// Aktív
ob_start();

$active = mysqli_query($f1db,
	"SELECT *
	FROM f1_active_driver AS active
	INNER JOIN team
	ON active.team = team.no
	WHERE active.no = $driverNo
	LIMIT 1"
);

if (mysqli_num_rows($active) > 0) {
	$ctv = mysqli_fetch_array($active);
	$team_no = $ctv['team'];
	$car_no = $ctv['car_no'];
	$driverTeamId = $ctv['id'];
	
	$active = mysqli_query($f1db,
		"SELECT *
		FROM f1_active_team
		WHERE no = $team_no");
	
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
	echo '<table class="plain">';
	echo '<tr>';
		echo '<td rowspan="5" style="padding-right:15px;" align="center" valign="center">';
		// Kép
		$img = '/img/team/' . $ctv['id'] . '.png';
		$img_path = 'img/team/' . $ctv['id'] . '.png';
	
		if (file_exists($img_path)) {
			echo '<img src="' . $img . '" style="max-height:90px; max-width:250px;">';
		}	
		echo '</td>';
		echo '<td colspan="2"><h2 style="margin-top:0px;">'.$ctv['fullname'].'</h2></td>';
	echo '</tr>';
	echo '<tr><td style="padding-right:10px; font-weight:bold;">Chassis</td><td>'.$chassis.'</td></tr>';
	echo '<tr><td style="padding-right:10px; font-weight:bold;">Engine</td><td>' .$engine. '</td></tr>';
	echo '<tr><td style="padding-right:10px; font-weight:bold;">Tyres</td><td>'  .$tyre.   '</td></tr>';
	echo '<tr><td style="padding-right:10px; font-weight:bold;">Car #</td><td>'  .$car_no. '</td></tr>';
	echo '</table>';
}

$active = ob_get_contents();
ob_clean();
?>
<?php
if ($driverPhotoOk) {
	echo '<div class="single" style="float:right; padding:0; width:292px;">'.$photo.'</div>';
}
?>
<h1><?php echo $driverName; ?></h1>
<?php
	// Aktív
	if (!empty($active)) {
		echo '<div class="double '.$driverTeamId.'">'.$active.'</div>';
	}
?>
<?php
include('resources/foot.php');
?>