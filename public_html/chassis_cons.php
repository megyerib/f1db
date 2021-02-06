<?php
require_once('included/database.php');
require_once('included/language/language.php');
if (isset($_GET['cons'])) {
	$cons = $_GET['cons'];
}
else {
	header('Location: chassises.php');
}

$query = mysqli_query($f1db,
	"SELECT *
	FROM team
	WHERE id = '$cons'
	AND chassis = 1");

if (mysqli_num_rows($query) == 0) {
	header('Location: chassises.php');
}

$row = mysqli_fetch_array($query);
$no = $row['no'];
$name = $row['fullname'];
$is_team = $row['isteam']; // Lehet hogy nem pontos de sztem az
$is_engine = $row['engine'];

$pagetitle = $name.' '.$lang['chassises'];
$maintitle = '<h1>'.$name.' <span class="subtitle">'.$lang['chassises'].'</span></h1>';
require_once('included/head.php');

// Jobb oldali táblázat
echo '<div class="right" style="background:none; border:none;">';
// Kép
$img1 = 'images/cons/'.$cons.'.png';
$img2 = 'images/team/'.$cons.'.png';

if (file_exists($img1)) {
	echo '<center><img src="/'.$img1 . '" style="max-width:280px; max-height:280px;"></center>';
}
else if (file_exists($img2)) {
	echo '<center><img src="/'.$img2 . '" style="max-width:280px; max-height:280px;"></center>';
}
echo '</div>';

echo '<div class="right">';
// Dobogó - ez a legjobb megvalósítás a kettős győzelmek miatt
$first = mysqli_query($f1db,
	"SELECT COUNT(DISTINCT(race.rnd)) AS count
	FROM f1_race AS race
	INNER JOIN chassis
	ON (race.chassis = chassis.no)
	WHERE chassis.cons = $no
	AND race.finish = 1");
$first = mysqli_fetch_array($first);
$first = $first['count'];

$second = mysqli_query($f1db,
	"SELECT COUNT(DISTINCT(race.rnd)) AS count
	FROM f1_race AS race
	INNER JOIN chassis
	ON (race.chassis = chassis.no)
	WHERE chassis.cons = $no
	AND race.finish = 2");
$second = mysqli_fetch_array($second);
$second = $second['count'];

$third = mysqli_query($f1db,
	"SELECT COUNT(DISTINCT(race.rnd)) AS count
	FROM f1_race AS race
	INNER JOIN chassis
	ON (race.chassis = chassis.no)
	WHERE chassis.cons = $no
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
		INNER JOIN chassis
		ON (race.chassis = chassis.no)
		WHERE chassis.cons = $no
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

// Konstruktőr
if ($is_team + $is_engine > 0) {
	echo 'This article is about chassis constructor <b>'.$name.'</b>';
	echo '<ul>';
	
	if ($is_team > 0) {
		echo '<li>';
		echo 'For team <a href="/team/'.$cons.'">click here</a>';
		echo '</li>';
	}
	if ($is_engine > 0) {
		echo '<li>';
		echo 'For engine constructor <a href="/engine/'.$cons.'">click here</a>';
		echo '</li>';
	}
	
	echo '</ul><br>';
}

// Kasztnik
$chass = array();
$inrace = mysqli_query($f1db,
	"SELECT race.chassis, race.yr
	FROM f1_race AS race
	INNER JOIN chassis
	ON race.chassis = chassis.no
	WHERE chassis.cons = $no
	GROUP BY chassis, yr
	ORDER BY race.rnd ASC");

while ($row = mysqli_fetch_array($inrace)) {
	$chass[$row['chassis']]['years'][$row['yr']] = $row['yr'];
}

$chassises = mysqli_query($f1db,
	"SELECT *
	FROM chassis
	WHERE cons = $no
	ORDER BY type ASC");
	
while ($row = mysqli_fetch_array($chassises)) {
	if ($row['type'] != '') {
		$name = $row['type'];
	}
	else {
		$name = 'Unknown';
	}
	
	$chass[$row['no']]['name'] = $name;
}
	
$num = mysqli_num_rows($chassises);
echo '<h2 style="margin-top:0px;">Chassises ('.$num.')</h2>';

echo '<table class="results">';
echo '<tr><th>Model</th><th>Used</th></tr>';
foreach ($chass as $chassis => $props) {
	echo '<tr>';
		echo '<td style="padding-right:20px;" class="rnd">'.chassis_link($cons, $chassis, $props['name']).'</td>';
		echo '<td>';
			if(count($props['years']) > 0){echo intervals($props['years']);}
		echo '</td>';
	echo '</tr>';
}
echo '</table>';



require_once('included/foot.php');
?>