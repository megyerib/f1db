<?php
require_once('included/database.php');

$no   = $_GET['no'];
$cons = $_GET['cons'];

$query = mysqli_query($f1db,
	"SELECT *
	FROM engine
	INNER JOIN team
	ON (engine.cons = team.no)
	WHERE team.id = '$cons'
	AND engine.no = $no
	LIMIT 1");
	
if (mysqli_num_rows($query) == 0) {
	header('Location: /engine/'.$cons);
}
	
$row = mysqli_fetch_array($query);

if (empty($row['type'])) {
	$unknown = 'Unknown ';
	$param = ' '.$row['concept'].$row['cylinders'].$row['turbo'];
	$type = '';
} else {
	$unknown = '';
	$param = '';
	$type = ' '.$row['type'];
}
$pagetitle = $unknown.$row['fullname'].$type.$param.' engine';
$maintitle = '<h1>'.$unknown.$row['fullname'].$type.$param.' <span class="subtitle">engine</span></h1>';
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

// Táblázat tovább
echo '<div class="right">';
// Dobogó - ez a legjobb megvalósítás a kettős győzelmek miatt
$first = mysqli_query($f1db,
	"SELECT COUNT(DISTINCT(rnd)) AS count
	FROM f1_race
	WHERE engine = $no
	AND finish = 1");
$first = mysqli_fetch_array($first);
$first = $first['count'];

$second = mysqli_query($f1db,
	"SELECT COUNT(DISTINCT(rnd)) AS count
	FROM f1_race
	WHERE engine = $no
	AND finish = 2");
$second = mysqli_fetch_array($second);
$second = $second['count'];

$third = mysqli_query($f1db,
	"SELECT COUNT(DISTINCT(rnd)) AS count
	FROM f1_race
	WHERE engine = $no
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
		WHERE engine = $no
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
echo '</div>'; // Div vége

// Cím
echo '<h1 class="title">'.$pagetitle.'</h1>';

// Motor kép
$img = 'images/engine/'.$no;
echo picture($img, 'max-width:300px;');

// Koncepció kép
$concept = 'images/engine/'.strtolower($row['concept']);
	echo '<table>';
	echo '<tr><td style="font-weight:bold; font-size:30px; text-align:center;">'.$row['cylinders'].'</td>';
	if ($row['turbo'] != '') {
		echo '<td rowspan="2" align="right" valign="center" width="130"><img src="/images/engine/turbo.png" width="100"></td>';
	}
	echo '</tr>';
	echo '<tr><td>'.picture($concept, 'max-width:200px;').'</td></tr>';
	
	echo '</table><br>';

echo '<b>Concept</b>: '.$row['concept'].$row['cylinders'].'</br>';
echo '<b>Volume</b>: '.$row['volume'].' liters<br>';
echo '<br>';

if ($row['turbo'] != '') {
	echo '<p>Turbocharged</p>';
}

$used = mysqli_query($f1db,
	"SELECT DISTINCT yr
	FROM f1_race
	WHERE engine = $no
	ORDER BY yr ASC");
	
$years = array();
while ($row = mysqli_fetch_array($used)) {
	array_push($years, $row['yr']);
}
echo '<b>Used</b>: '.intervals($years).'</p>';

// Csapatok
echo '<table><tr valign="top">';

// Gyári csapatok
$teams = mysqli_query($f1db,
	"SELECT DISTINCT race.team, team.fullname, team.id
	FROM f1_race AS race
	INNER JOIN team
	ON (race.team = team.no)
	WHERE race.engine = $no
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
	WHERE race.engine = $no
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

// Kasztnik

$chassis = mysqli_query($f1db,
	"SELECT DISTINCT chassis.no AS chassisno, team.fullname AS teamname, chassis.type, team.id AS teamid
	FROM f1_race AS race
	INNER JOIN chassis
	ON race.chassis = chassis.no
	INNER JOIN team
	ON chassis.cons = team.no
	WHERE race.engine = $no
	ORDER BY teamname ASC");
	
echo '<p style="font-weight:bold;">Additional chassises</p>';
echo '<table>';

while ($row = mysqli_fetch_array($chassis)) {
	echo '<tr>';
	echo '<td style="padding-right:10px;">'.chassis_cons_link($row['teamid'], $row['teamname']).'</td>';
	echo '<td>'.chassis_link($row['teamid'], $row['chassisno'], $row['type']).'</td>';
	echo '</tr>';
}
echo '</table>';

require_once('included/foot.php');
?>