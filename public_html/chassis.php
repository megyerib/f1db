<?php
require_once('included/database.php');
require_once('included/language/language.php');

$no   = $_GET['no'];
$cons = $_GET['cons'];

$query = mysqli_query($f1db,
	"SELECT *
	FROM chassis
	INNER JOIN team
	ON (chassis.cons = team.no)
	WHERE team.id = '$cons'
	AND chassis.no = $no
	LIMIT 1");
	
if (mysqli_num_rows($query) == 0) {
	header('Location: /chassis/'.$cons);
}
	
$row = mysqli_fetch_array($query);

if (empty($row['type'])) {
	$unknown = 'Unknown ';
}
else {
	$unknown = '';
}

$pagetitle = $unknown.$row['fullname'].' '.$row['type'].' chassis';
require_once('included/head.php');

// Jobb oldali táblázat
echo '<div class="right" style="background:none; border:none;">';

// Kép
$img1 = 'images/cons/'.$cons.'.png';
$img2 = 'images/team/'.$cons.'.png';

if (file_exists($img1)) {
	echo '<p><center><img src="/'.$img1.'" style="max-width:280px; max-height:280px;"></center></p>';
}
else if (file_exists($img2)) {
	echo '<p><center><img src="/'.$img2.'" style="max-width:280px; max-height:280px;"></center></p>';
}
echo '</div>';

echo '<div class="right">';
// Dobogó - ez a legjobb megvalósítás a kettős győzelmek miatt
$first = mysqli_query($f1db,
	"SELECT COUNT(DISTINCT(rnd)) AS count
	FROM f1_race
	WHERE chassis = $no
	AND finish = 1");
$first = mysqli_fetch_array($first);
$first = $first['count'];

$second = mysqli_query($f1db,
	"SELECT COUNT(DISTINCT(rnd)) AS count
	FROM f1_race
	WHERE chassis = $no
	AND finish = 2");
$second = mysqli_fetch_array($second);
$second = $second['count'];

$third = mysqli_query($f1db,
	"SELECT COUNT(DISTINCT(rnd)) AS count
	FROM f1_race
	WHERE chassis = $no
	AND finish = 3");
$third = mysqli_fetch_array($third);
$third = $third['count'];

if (($first + $second + $third) > 0) { // Van dobogó	
	podium($first, $second, $third);
}
else {
	$best = mysqli_query($f1db,
		"SELECT finish, status
		FROM f1_race
		WHERE chassis = $no
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

// CÍM
echo '<h1 style="margin-top:0px;">'.$unknown.$row['fullname'].' '.$row['type'].' <span class="subtitle">chassis</span></h1>';

// Kép
$img = 'images/chassis/'.$no;
echo picture($img, 'max-width:460px; border:1px solid grey; border-radius:2px;').'<br>';

$used = mysqli_query($f1db,
	"SELECT DISTINCT yr
	FROM f1_race
	WHERE chassis = $no
	ORDER BY yr ASC");
	
$years = array();
while ($row = mysqli_fetch_array($used)) {
	array_push($years, $row['yr']);
}
echo '<b>Used in</b>: '.intervals($years).'<br><br>';

// Csapatok
echo '<table><tr valign="top">';

// Gyári csapatok
$teams = mysqli_query($f1db,
	"SELECT DISTINCT race.team, team.fullname, team.id
	FROM f1_race AS race
	INNER JOIN team
	ON (race.team = team.no)
	WHERE race.chassis = $no
	AND team.isteam = 1
	ORDER BY team.fullname ASC");

if (mysqli_num_rows($teams) > 0) {
	echo '<td><b>Used by</b>:</br><ul>';	
	while ($row = mysqli_fetch_array($teams)) {
		echo '<li>'.team_link($row['id'], $row['fullname']).'</a></li>';
	}
	echo '</ul></td>';
}

$private = mysqli_query($f1db,
	"SELECT DISTINCT race.team, team.fullname, team.id
	FROM f1_race AS race
	INNER JOIN team
	ON (race.team = team.no)
	WHERE race.chassis = $no
	AND team.isteam = 0
	ORDER BY team.fullname ASC");

if (mysqli_num_rows($private) > 0) {
	echo '<td><b>Private teams</b>:</br>';
	echo '<ul>';
	while ($row = mysqli_fetch_array($private)) {
		echo '<li>'.team_link($row['id'], $row['fullname']).'</a></li>';
	}
	echo '</ul></td>';
}
echo '</tr></table>';

// Motor

$engine = mysqli_query($f1db,
	"SELECT DISTINCT engine.no, team.fullname, engine.type, team.id,
	engine.concept, engine.cylinders, engine.volume, engine.turbo
	FROM f1_race AS race
	INNER JOIN engine
	ON race.engine = engine.no
	INNER JOIN team
	ON engine.cons = team.no
	WHERE race.chassis = $no");
	
echo '<p style="font-weight:bold;">Additional engines</p>';
echo '<table>';

while ($row = mysqli_fetch_array($engine)) {
	echo '<tr>';
	if (!empty($row['type'])) {
		$e_name = $row['fullname'].' '.$row['type'];
	}
	else {
		$e_name = ' Unknown '.$row['fullname'];
	}
	echo '<td style="padding-right:10px;">'.engine_link($row['id'], $row['no'], $e_name).'</td>';
	echo '<td>'.engine_param($row['volume'], $row['concept'], $row['cylinders'], $row['turbo']).'</td>';
	echo '</tr>';
}
echo '</table>';

require_once('included/foot.php');
?>