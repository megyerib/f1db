<?php
require_once('included/database.php');
if (isset($_GET['cons'])) {
	$cons = $_GET['cons'];
}
else {
	header('Location: '.root.'/engine');
}

$query = mysqli_query($f1db,
	"SELECT *
	FROM team
	WHERE id = '$cons'
	AND engine = 1");

if (mysqli_num_rows($query) == 0) {
	header('Location: '.root.'engine');
}

$row = mysqli_fetch_array($query);
$no = $row['no'];
$name = $row['fullname'];
$is_team = $row['isteam']; // Lehet hogy nem pontos de sztem az
$is_chassis = $row['chassis'];

$pagetitle = $name.' engines';
$maintitle = '<h1>'.$name.' <span class="subtitle">engines</span></h1>';
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

// Tovább
echo '<div class="right">';
// Dobogó - ez a legjobb megvalósítás a kettős győzelmek miatt
$first = mysqli_query($f1db,
	"SELECT COUNT(DISTINCT(race.rnd)) AS count
	FROM f1_race AS race
	INNER JOIN engine
	ON (race.engine = engine.no)
	WHERE engine.cons = $no
	AND race.finish = 1");
$first = mysqli_fetch_array($first);
$first = $first['count'];

$second = mysqli_query($f1db,
	"SELECT COUNT(DISTINCT(race.rnd)) AS count
	FROM f1_race AS race
	INNER JOIN engine
	ON (race.engine = engine.no)
	WHERE engine.cons = $no
	AND race.finish = 2");
$second = mysqli_fetch_array($second);
$second = $second['count'];

$third = mysqli_query($f1db,
	"SELECT COUNT(DISTINCT(race.rnd)) AS count
	FROM f1_race AS race
	INNER JOIN engine
	ON (race.engine = engine.no)
	WHERE engine.cons = $no
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
		INNER JOIN engine
		ON (race.engine = engine.no)
		WHERE engine.cons = $no
		ORDER BY finish, status ASC
		LIMIT 1"
	);
	
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

echo '</div>'; // Hasáb vége
// Cím
echo '<h1 class="title">'.$pagetitle.'</h1>';

// Konstruktőr
if ($is_team + $is_chassis > 0) {
	echo 'This article is about engine constructor <b>'.$name.'</b>';
	echo '<ul>';
	
	if ($is_team > 0) {
		echo '<li>';
		echo 'For team <a href="/team/'.$cons.'">click here</a>';
		echo '</li>';
	}
	if ($is_chassis > 0) {
		echo '<li>';
		echo 'For chassis constructor <a href="/chassis/'.$cons.'">click here</a>';
		echo '</li>';
	}
	
	echo '</ul>';
}

// Motrok
$eng = array();
$inrace = mysqli_query($f1db,
	"SELECT race.engine, race.yr
	FROM f1_race AS race
	INNER JOIN engine
	ON race.engine = engine.no
	WHERE engine.cons = $no
	GROUP BY engine, yr
	ORDER BY race.rnd ASC");

while ($row = mysqli_fetch_array($inrace)) {
	$eng[$row['engine']]['years'][$row['yr']] = $row['yr'];
}

$engines = mysqli_query($f1db,
	"SELECT *
	FROM engine
	WHERE cons = $no
	ORDER BY type ASC");
	
while ($row = mysqli_fetch_array($engines)) {
	if ($row['type'] != '') {
		$name = $row['type'];
	}
	else {
		$name = 'Unknown';
	}
	
	$vol   = number_format($row['volume'], 1, '.', '');
	$conc  = $row['concept'];
	if ($row['cylinders'] > 0) {$conc .= $row['cylinders'];}
	$turbo = $row['turbo'];
	
	$params = array();
	if ($vol > 0)     {array_push($params, $vol  );}
	if ($conc != '')  {array_push($params, $conc );}
	if ($turbo != '') {array_push($params, $turbo);}
	
	$params = implode(' ', $params);
	
	$eng[$row['no']]['name'] = $name;
	$eng[$row['no']]['params'] = $params;
}
	
$num = mysqli_num_rows($engines);
echo '<h2 style="margin-top:0px;">Engines ('.$num.')</h2>';

echo '<table class="results">';
echo '<tr><th>Model</th><th>Specs</th><th>Used</th></tr>';
foreach ($eng as $engine => $props) {
	echo '<tr>';
		echo '<td style="padding-right:20px;" class="rnd">'.engine_link($cons, $engine, $props['name']).'</td>';
		echo '<td>'.$props['params'].'</td>';
		echo '<td>';
			if(!empty($props['years'])){echo intervals($props['years']);}
		echo '</td>';
	echo '</tr>';
}
echo '</table>';

/*
$known = mysqli_query($f1db,
	"SELECT *
	FROM engine
	WHERE cons = $no
	AND type != ''");
	
echo '<table>';
while ($row = mysqli_fetch_array($known)) {
	echo '<tr>';
	echo '<td style="padding-right:15px;">'.engine_link($cons, $row['no'], $row['type']).'<td>';
	$vol = number_format($row['volume'], 1, '.', '');
	if ($row['volume'] > 0) {echo $vol.' ';}
	echo $row['concept'].$row['cylinders'].' '.$row['turbo'].'</td>';
	echo '</tr>';
}

$unknown = mysqli_query($f1db,
	"SELECT *
	FROM engine
	WHERE cons = $no
	AND type = ''");

if(mysqli_num_rows($unknown) > 0) {
	echo '<td colspan="2"><p><b>Unknown types</b></p></td>';
	
	while ($row = mysqli_fetch_array($unknown)) {
		echo '<tr>';
		echo '<td></td>';
		echo '<td>';
		$name = $row['concept'].$row['cylinders'].' '.$row['turbo'];
		$vol = number_format($row['volume'], 1, '.', '');
		if ($row['volume'] > 0) {$name = $vol.' '.$name;}
	
		echo engine_link($cons, $row['no'], $name);
		
		echo '</a>';
		echo '</td>';
		echo '</tr>';
	}
}

echo '</table>';*/

require_once('included/foot.php');
?>