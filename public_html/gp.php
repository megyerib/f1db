<?php
require_once('included/database.php');

	$gp = $_GET['gp'];
		
	// Név keresése
	$query_name = mysqli_query($f1db, 
		"SELECT name
		FROM country
		WHERE gp = '$gp'
		LIMIT 1");

	$name = mysqli_fetch_array($query_name);
	$name = $name['name'];

$pagetitle = $name . ' GPs';
require_once('included/head.php');
		
	// Lekérdezés
	$query = mysqli_query($f1db, // Folytasd
		"SELECT yr
		FROM f1_gp
		WHERE gp = '$gp'
		ORDER BY no");

	// Kép link
	$img = '/images/flag/big/' . $gp . '.png';
	
	// Intervallum
	$yrs = array();
	while ($row = mysqli_fetch_array($query)) {
		array_push($yrs, $row['yr']);
	}

	echo '<div class="right">';
	// Zászló
	echo '<img src="' . $img . '" width="280">';
	// Intervallum
	echo '<p><b>Held: </b>';
	echo intervals($yrs);
	echo '</p>';
	echo '</div>';
	
	$months = array(
		1 => 0,
		2 => 0,
		3 => 0,
		4 => 0,
		5 => 0,
		6 => 0,
		7 => 0,
		8 => 0,
		9 => 0,
		10 => 0,
		11 => 0,
		12 => 0
	);
	
	// Időpontok
	
	$times = mysqli_query($f1db,
		"SELECT MONTH(dat) AS mon, COUNT(*) AS cnt
		FROM f1_details
		INNER JOIN f1_gp
		ON f1_details.no = f1_gp.no
		WHERE f1_gp.gp = '$gp'
		AND MONTH(dat) > 0
		GROUP BY MONTH(dat)"
	);
	$max_cnt = 0;
	while ($row = mysqli_fetch_array($times)) {
		$months[$row['mon']] = $row['cnt'];
		if ($row['cnt'] > $max_cnt) $max_cnt = $row['cnt'];
	}
	
	function gradientcolor($num, $min, $max) {
		$r1 = 255; $g1 =   0; $b1 =   0; // Nagy szín
		$r2 = 255; $g2 = 255; $b2 =   0; // Kis szín
		
		$range = $max - $min;
		$big   = $max - $num;
		$small = $num - $min;
		
		if ($range == 0) {
			$range = 1;
			$big = 1;
			$small = 0;
		}
		
		$red =   dechex(floor(($small*$r1+$big*$r2)/$range));
		$green = dechex(floor(($small*$g1+$big*$g2)/$range));
		$blue =  dechex(floor(($small*$b1+$big*$b2)/$range));
		
		$red =   strlen($red)   == 2 ? $red : '0'.$red;
		$green = strlen($green) == 2 ? $green : '0'.$green;
		$blue =  strlen($blue)  == 2 ? $blue : '0'.$blue;
		
		return $red.$green.$blue;
	}
	
	echo '<table class="results" style="text-align:center;"><tr>';
	foreach ($months as $mon => $cnt) {
		$color = $cnt > 0 ? 'background-color:#'.gradientcolor($cnt, 1, $max_cnt).';' : '';
		echo '<td style="width:30px; vertical-align:top; '.$color.'">'.date("M", mktime(0, 0, 0, $mon+1, 0, 0)).'<br>';
		if ($cnt>0) echo $cnt;
		echo '</td>';
	}
	echo '</tr></table><br>';
	
	// Versenyek (ha esetleg nincs) helyszín
	// Sok a kivételkezelés, de muszáj, ha valamelyik adat hiányzik
	
	$results = array();
	$races = mysqli_query($f1db, // Folytasd
		"SELECT *
		FROM f1_gp AS gps
		INNER JOIN f1_details AS data
		ON (gps.no = data.no)
		WHERE gp = '$gp'
		ORDER BY no_gp"
	);
	
	while ($row = mysqli_fetch_array($races)) {
		$link = race_link($row['yr'], $row['gp'], $row['yr']);
		$results[$row['yr']]['link'] = $link;
		$results[$row['yr']]['date'] = $row['dat'];
		$results[$row['yr']]['win'][0] = ''; // -JAV-
	}
	
	// Helyszín
	$places = mysqli_query($f1db, // Folytasd
		"SELECT gps.yr, circuit.place, circuit.id AS circuitid
		FROM f1_gp AS gps
		INNER JOIN f1_details AS data
		ON (gps.no = data.no)
		INNER JOIN circuit
		ON data.circuit = circuit.no
		WHERE gp = '$gp'
		ORDER BY no_gp"
	);
	
	while ($row = mysqli_fetch_array($places)) {
		$results[$row['yr']]['place'] = $row['place'];
		$results[$row['yr']]['c_id'] = $row['circuitid'];
	}
	
	$winners = mysqli_query($f1db,
		"SELECT race.yr, driver.id AS driverid,
			team.id AS teamid, team.fullname, team.country AS teamcountry,
            driver.country, driver.first, driver.de, driver.last, driver.sr
		FROM f1_race AS race
		INNER JOIN driver
		ON (race.driver = driver.no)
		INNER JOIN team
		ON (race.team = team.no)
		WHERE race.gp = '$gp'
		AND finish = 1
		ORDER BY race.rnd ASC");
	
	
	$i = 1;
	while ($row = mysqli_fetch_array($winners)) {
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		$link = flag($row['country']).driver_link($row['driverid'], $name).'<br>';
		
		$results[$row['yr']]['win'][$i] = $link;
		
		$results[$row['yr']]['team'] = flag($row['teamcountry']).team_link($row['teamid'], $row['fullname']);
		$i++;
	}
	
	echo '<table class="results">';
	echo '<th>Year</th>
		<th>Date</th>
		<th>Place</th>
		<th>Winner</th>
		<th>Team</th>';
	
	foreach ($results as $yr => $year) {
		if (isset($prevyr)) {
			if ($prevyr != ($yr-1)) {
				echo '<tr><td>';
				$break1 = $prevyr+1;
				$break2 = $yr-1;
				if ($break1 != $break2) {echo $break1.'</br>-</br>'.$break2;}
				else {echo $break1;}
				echo '</td><td class="notheld" colspan="4"><i>Not held</i></td>';
				echo '</tr>';
			}
		}
		echo '<tr>';
		echo '<td>'.$year['link'].'</td>';
		if ($year['date'] > 0) {
			$date = date('F j', strtotime($year['date']));
		}
		else {
			$date = '';
		}
		echo '<td>'.$date.'</td>';
		if (isset($year['c_id'])) {
			$place = circuit_link($year['c_id'], $year['place']);
		}
		else {
			$place = '';
		}
		echo '<td>'.$place.'</td>';
		echo '<td>';
		
		foreach ($year['win'] as $winner) {
			echo $winner; // Alapból van sortörés (lehet h nem túl elegáns)
		}
		
		if (isset($year['team'])) {
			$team = $year['team'];
		}
		else {
			$team = '';
		}
		echo '<td>'.$team.'</td>';
		
		echo '</td>';
		echo '</tr>';
		$prevyr = $yr;
	}
	echo '</table>';
	
	// Győztesek
	echo '<h2>Winners</h2>';
	
	$winners = mysqli_query($f1db,
		"SELECT race.driver, COUNT(race.no) AS count,
		driver.first, driver.de, driver.last, driver.sr, driver.id, driver.country
		FROM f1_race AS race
                INNER JOIN driver
                ON (race.driver = driver.no)
		WHERE gp = '$gp'
                AND finish = 1
		GROUP BY driver
                ORDER BY count DESC");
				
	$wnnrs = array();
	$i = 1;
	while ($wrs = mysqli_fetch_array($winners)) {
		$name = name($wrs['first'], $wrs['de'], $wrs['last'], $wrs['sr']);
		
		$wnnrs[$wrs['count']][$i] = flag($wrs['country']).driver_link($wrs['id'], $name);
		$i++;
	}
	
	echo '<table class="results"><th>Wins</th><th>Drivers</th>';
	foreach ($wnnrs as $times => $drivers) {
		$count = count($drivers);
		$i = 0;
		foreach ($drivers as $driver) {
			echo '<tr>';
			if ($i == 0) {
				echo '<td rowspan="'.$count.'" align="center">'.$times.'</td>';
			}
			echo '<td>'.$driver.'</td></tr>';
			$i++;
		}
	}
	echo '</table>';
	
	// Hazai győztesek
	$homewin = mysqli_query($f1db,
		"SELECT race.yr, driver.id, driver.first, driver.de, driver.last, driver.sr
		FROM f1_race AS race
		INNER JOIN driver
		ON (race.driver = driver.no)
		WHERE race.finish = 1
		AND race.gp = '$gp'
		AND driver.country = '$gp'");
		
	if (mysqli_num_rows($homewin) > 0) {
		echo '<h2>Home winners</h2>';
		
		echo '<table class="results"><th>Year</th><th>Driver</th>';
		while ($home = mysqli_fetch_array($homewin)) {
			$name = name($home['first'], $home['de'], $home['last'], $home['sr']);
			
			echo '<tr>';
			echo '<td>'.$home['yr'].'</td>';
			echo '<td>'.flag($gp).driver_link($home['id'], $name).'</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	
require_once('included/foot.php');
?>