<?php
require_once('included/database.php');

$tyre_id = $_GET['tyre'];

$query = mysqli_query($f1db,
	"SELECT *
	FROM tyre 
	WHERE id = '$tyre_id'
	LIMIT 1");
	
// Ha nincs
if (mysqli_num_rows($query) == 0) {
	header('Location: tyres.php');
}
	
$row_tyre = mysqli_fetch_array($query);

$name = $row_tyre['fullname'];
$tyre_no = $row_tyre['no'];
$pagetitle = $name;
require_once('included/head.php');
$tyre_id = $row_tyre['id'];

// Tablehead
/*echo '<div class="rhead">' . $name . '</div>';*/
// Jobb oldali táblázat
echo '<div class="right" id="tyre" >';
echo '<p><center><img src="/images/tyre/' . $tyre_id . '.png" width="100%"></center></p>';


	
// Nagydíjak
$gps = mysqli_query($f1db,
	"SELECT DISTINCT rnd
	FROM f1_race
	WHERE tyre = '$tyre_id'");
	
echo '<b>GPs</b>: ' . mysqli_num_rows($gps) . '</br>';
	
// Nevezések
$entrances = mysqli_query($f1db,
	"SELECT no
	FROM f1_race
	WHERE tyre = '$tyre_id'");
	
echo '<b>Entrances</b>: ' . mysqli_num_rows($entrances) . '</br>';

// Rajtok
$starts = mysqli_query($f1db,
	"SELECT no
	FROM f1_race
	WHERE tyre = '$tyre_id'
	AND status <= 3");
	
echo '<b>Starts</b>: ' . mysqli_num_rows($starts) . '</br>';
echo '<br>';

// Dobogó - ez a legjobb megvalósítás a kettős győzelmek miatt
$first = mysqli_query($f1db,
	"SELECT COUNT(DISTINCT(race.rnd)) AS count
	FROM f1_race AS race
	WHERE tyre = '$tyre_id'
	AND race.finish = 1");
$first = mysqli_fetch_array($first);
$first = $first['count'];

$second = mysqli_query($f1db,
	"SELECT COUNT(DISTINCT(race.rnd)) AS count
	FROM f1_race AS race
	WHERE tyre = '$tyre_id'
	AND race.finish = 2");
$second = mysqli_fetch_array($second);
$second = $second['count'];

$third = mysqli_query($f1db,
	"SELECT COUNT(DISTINCT(race.rnd)) AS count
	FROM f1_race AS race
	WHERE tyre = '$tyre_id'
	AND race.finish = 3");
$third = mysqli_fetch_array($third);
$third = $third['count'];

if (($first + $second + $third) > 0) { // Van dobogó	
	podium($first, $second, $third);
}
else {
	$best = mysqli_query($f1db,
		"SELECT finish, status
		FROM f1_race AS race
		WHERE tyre = '$tyre_id'
		ORDER BY finish, status ASC
		LIMIT 1");
	
	$best = mysqli_fetch_array($best);	
	$bestplace = $best['finish'];
	$status = $best['status'];
	if ($bestplace > 0 && $status == 1) {
		echo '<b>Best place</b>: '.$bestplace.'</br>';
	}
	if ($status > 1) {
		echo '<b>Best place</b>: '.status($status).'</br>';
	}
}
echo '</div>';

$social_media_type = 'TR';
require_once('included/social_media.php');

table_tyre($f1db, $tyre_id);
	
require_once('included/foot.php');
?>