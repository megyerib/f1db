<?php
/*Adatok, cím*/require_once('included/database.php');
if (isset($_GET['country'])) {
	$country = $_GET['country'];
}
else {
	header('Location: countries.php');
}

$ctry = mysqli_query($f1db,
	"SELECT *
	FROM country
	WHERE country != ''
	AND gp = '$country'");
	
if (mysqli_num_rows($ctry) == 0) {
	header('Location: /country');
}

$ctr = mysqli_fetch_array($ctry);

$cname = $ctr['country'];
$nat   = $ctr['name'];

$pagetitle = $cname;
/*Fejléc*/require_once('included/head.php');

// Jobb
echo '<div class="right">';
	// Zászló
	$img = '/images/flag/big/' . $country . '.png';
	$img_path = 'images/flag/big/' . $country . '.png';
	if (file_exists($img_path)) {
		echo '<img src="'.$img.'" width="278" style="border:1px solid #CCCCCC;">';
	}
	// Címer
	$img = '/images/coa/' . $country . '.png';
	$img_path = 'images/coa/' . $country . '.png';
	if (file_exists($img_path)) {
		echo '<center><p><img src="'.$img.'" style="max-width:250px; max-height:250px;"></p></center>';
	}
	// Címek
	$chships = mysqli_query($f1db,
		"SELECT COUNT(place) AS count
		FROM f1_tbl AS tbl
		INNER JOIN driver
		ON (tbl.driver = driver.no)
		WHERE driver.country = '$country'
		AND tbl.place = 1");
		
	$count = mysqli_fetch_array($chships);
	$count = $count['count'];
	if ($count > 0) {
		echo '<b>Driver championships</b>: '.$count.'</br>';
	}
	// Pontok
	$scores = mysqli_query($f1db,
	"SELECT SUM(score) AS sum
	FROM f1_race
	INNER JOIN driver
	ON (f1_race.driver = driver.no)
	WHERE driver.country = '$country'");
	
	$sum = mysqli_fetch_array($scores);
	$sum = $sum['sum'];
	if ($sum > 0) {
		echo '<b>Championship points</b>: '.($sum+0).'</br>';
	}
echo '</div>';

// Drivers
$drivers = mysqli_query($f1db,
	"SELECT *
	FROM driver
	WHERE country = '$country'");

if (mysqli_num_rows($drivers) > 0) {
	$num = mysqli_num_rows($drivers);
	echo '<h2 class="top">Drivers ('.$num.')</h2>';
	while ($row = mysqli_fetch_array($drivers)) {
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);		
		echo driver_link($row['id'], $name).'</br>';
	}
}
// Teams
$teams = mysqli_query($f1db,
	"SELECT *
	FROM team
	WHERE country = '$country'");

if (mysqli_num_rows($teams) > 0) {
	$num = mysqli_num_rows($teams);
	echo '<h2>Teams ('.$num.')</h2>';
	while ($row = mysqli_fetch_array($teams)) {		
		echo team_link($row['id'], $row['fullname']).'</br>';
	}
}

// Grand Prix
$gp = mysqli_query($f1db,
	"SELECT *
	FROM f1_gp
	INNER JOIN f1_details
	ON (f1_gp.no = f1_details.no)
	WHERE gp = '$country'
	ORDER BY f1_gp.no ASC");
	
if (mysqli_num_rows($gp) > 0) {
	echo '<h2>'.$nat.' Grand Prix</h2>';
	
	$yrs = array();
	while($row = mysqli_fetch_array($gp)){
		array_push($yrs, $row['yr']);
	}
	
	echo '<p>Held '.mysqli_num_rows($gp).' times</br>('.intervals($yrs).')</p>';
	
}

// Circuits
$circuits = mysqli_query($f1db,
	"SELECT *
	FROM circuit
	WHERE country = '$country'"
);
if (mysqli_num_rows($circuits) > 0) {
	echo '<h2>Circuits</h2>';
	while ($row = mysqli_fetch_array($circuits)) {
		echo circuit_link($row['id'], $row['fullname']).'<br>';
	}
}

require_once('included/foot.php');
?>