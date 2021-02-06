<?php
/* $det = details tömb
   $rac = race tömb
*/

require_once('included/database.php');

$yr = $_GET['year'];
$gp = $_GET['race'];

// Részletek lekérdezés

$results = mysqli_query($f1db, 
  "SELECT *, circuit.fullname AS circuitname, circuit.id AS circuitid, gps.no AS gpno, data.fullname AS racename
   FROM f1_gp AS gps
   INNER JOIN f1_details AS data
   ON (gps.no = data.no)
   INNER JOIN circuit
   ON (data.circuit = circuit.no)
   INNER JOIN country
   ON (gps.gp = country.gp)
   WHERE gps.yr = $yr
   AND gps.gp = '$gp'
   LIMIT 1");

if (mysqli_num_rows($results) == 0) {
	//$_SESSION['error'] = 2;
	header('Location: /f1');
}
							   
$det = mysqli_fetch_array($results);

// Cím
$pagetitle = $det['yr'] . ' ' . $det['name'] . ' Grand Prix';
require_once('included/head.php');

//////////////////////////
// Jobb oldali táblázat //
//////////////////////////	

// Verseny száma
$no = $det['gpno'];
$img = '/images/flag/big/' . $det['gp'] . '.png';
	
echo '<div class="right">';
//echo '<div class="righthead" id="race">' . $title . '</div>';
echo '<img src="' . $img . '" width="278" style="border:solid 1px #CCCCCC;">';

echo '<div class="thead">Race details</div>';

echo '<table class="right">';
// Hely, idő
$date = date('F j, Y', strtotime($det['dat']));

echo '<tr><td class="right">Place</td>';
echo '<td class="right"><b>' . circuit_link($det['circuitid'], $det['circuitname']) . '</b></br>';
echo $det['place'] . '</td></tr>';
echo '<tr><td class="right">Date</td><td class="right">';
echo $date . '</td><tr>';
echo '</table>';

// Sorszámok
echo '<div class="thead">Ordinals</div>';
echo ordinal($det['gpno']) . ' Formula One Grand Prix</br>';
echo ordinal($det['no_yr']) . ' Grand Prix in ' . $det['yr'] . '</br>';
echo ordinal($det['no_gp']) . ' ' . $det['name'] . ' Grand Prix</br>';

// Leggyorsabb kör
$fastest = mysqli_query($f1db,
	"SELECT *
	FROM f1_fastest AS fastest
	INNER JOIN driver
	ON (fastest.driver = driver.no)
	WHERE rnd = $no");

if (mysqli_num_rows($fastest) > 0) {	
	$fst = mysqli_fetch_array($fastest);
	$name = name($fst['first'], $fst['de'], $fst['last'], $fst['sr']);
	$time = racetime($fst['time']);
	
	echo '<div class="thead">Fastest lap</div>';
	echo '<table width="100%"><tr><td>'.flag($fst['country']).driver_link($fst['id'], $name).'</td><td style="text-align:right;">'.$time.'</td></tr></table>';
}

echo '<hr>';
echo '<center><a href="http://en.wikipedia.org/wiki/' . $det['yr'] . '_' . $det['name'] . '_Grand_Prix" target="_blank">Wikipedia</a></center>';
echo '</div>';

//////////
// Body //
//////////
// Bevezető szöveg
if (($det['name'] . ' Grand Prix') != $det['racename'] && $det['racename'] != '') {
	$formally =  ' (formally the ' . $det['racename'] . ')';
}
else {
	$formally = '';
}

echo '<p>'.
	$det['yr'] . ' ' . $det['name'] . ' Grand Prix' . $formally . ' '.
	'was held on ' . $date . ' at the ' . circuit_link($det['circuitid'] ,$det['circuitname']) . ' in ' . $det['place'] . '. '.
	'It was the ' . ordinal($det['no_yr']) . ' race of the ' . $det['yr'] . ' Formula One season.'.
	'</p>';

// Schedule
$schedule = mysqli_query($f1db,
	"SELECT *
	FROM f1_gp_schedule
	WHERE rnd = $no
	ORDER BY tme, no ASC");

if (mysqli_num_rows($schedule) > 0) {
	echo '<h2>Schedule</h2>';
	echo '<table class="results">';
	echo '<th>Event</th><th>Time (CET)</th>';
	while ($row = mysqli_fetch_array($schedule)) {
		echo '<tr>';
		if ($row['type'] == 'P') {
			if ($row['num'] > 0) {
				$event_name = 'Practice '.$row['num'];
				$link = 'p'.$row['num'];
			} else {
				$event_name = 'Practice';
				$link = 'p';
			}
		}
		if ($row['type'] == 'Q') {
			$event_name = 'Qualifying';
			$link = 'qual';
		}
		if ($row['type'] == 'R') {
			$event_name = 'Race';
			$link = 'race';
		}
		echo '<td><a href="#'.$link.'">'.$event_name.'</a></td>';
		
		if ($row['tme'] > 0) {
			$event_time = date('j F, H:i', strtotime($row['tme']));
		}
		echo '<td>'.$event_time.'</td>';
		echo '</tr>';
	}
}
echo '</table>';
///////////////////
// Szabadedzések //
///////////////////

$practices = mysqli_query($f1db,
	"SELECT *
	FROM f1_gp_schedule
	WHERE rnd = $no
	AND type = 'P'
	AND shown = 1
	ORDER BY num, no ASC");
while ($row = mysqli_fetch_array($practices)) {
	if (empty($row['name'])) {
		if ($row['num'] > 0) {
			$event_name = 'Practice '.$row['num'];
			$h_id = 'p'.$row['num'];
		} else {
			$event_name = 'Practice';
			$h_id = 'p';
		}
	}
	else {
		$event_name = $row['name'];
		$h_id = 'p'.$row['no'];
	}
	echo '<h2 id="'.$h_id.'">'.$event_name.'</h2>';
	table_practice($f1db, $row['no']);
}

/////////////
// Időmérő //
/////////////
echo '<h2 id="qual">Qualifying</h2>';
if ($no <= 750) {
	table_q_simple($f1db, $yr, $no);
}
else {
	table_q_3($f1db, $yr, $no);
}

///////////
// Futam //
///////////
echo '<h2 id="race">Race</h2>';
table_race($f1db, $yr, $no);
	
///////////////////////
// Navigációs linkek //
///////////////////////

gp_nav($no, $det['gp'], $det['no_gp'], $det['yr'], $f1db);

require_once('included/foot.php');
?>