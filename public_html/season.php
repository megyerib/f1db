<?php
	$year  = $_GET['year'];
	$pagetitle = $year.' Formula One Season';
	
	require_once('included/head.php');
	//require_once('included/functions.php');
	
	$first = 1950;
	
	if (!isset($year) ||
		$year < $first ||
		$year > last) {
		$_SESSION['error'] = 1;
		header('Location: /races');
	}
	
	///////////////
	// Részletek //
	///////////////
	
	/*echo '<div class="right" id="season">';
	echo '<p>The ' . ordinal($year - 1949) . ' Formula One season</p>';
	echo '<table width="100%"><tr>';
	echo '<td>&lt;&lt;'.season_link($year-1).'</td>';
	echo '<td align="center"><a href="/race">Seasons</a></td>';
	echo '<td align="right">'.season_link($year+1).'&gt;&gt;</td></tr></table>';
	echo '<hr>';
	echo '<center><a href="http://en.wikipedia.org/wiki/' . $year . '_Formula_One_season" target="_blank">Wikipedia</a></center>';
	echo '</div>';*/
	
	///////////////
	// BAJNOKSÁG //
	///////////////
	
	echo '<div class="right" style="font-size:13px;">';
	echo '<center><span style="font-size:200%;">Championship</span><hr></center>';
	
	$champ = mysqli_query($f1db,
		"SELECT tbl.score,
			driver.first, driver.de, driver.last, driver.sr, driver.no, driver.id
		FROM f1_tbl AS tbl
		INNER JOIN driver
			ON (tbl.driver = driver.no)
		WHERE yr = $year
		AND place <= 3
		AND score > 0
		ORDER by place ASC");
		
	$i = 1;
	echo '<center>';
	while ($row = mysqli_fetch_array($champ)) {
		$driver_no = $row['no'];
		$team = mysqli_query($f1db,
			"SELECT DISTINCT race.team, team.fullname
			FROM f1_race AS race
			INNER JOIN team
			ON (race.team = team.no)
			WHERE race.yr = $year
			AND race.driver = $driver_no");
			
		$teams = array();
		while ($rw = mysqli_fetch_array($team)) {
			$tm = $rw['fullname'];
			array_push($teams, $tm);
		}
		
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		$size = 210 - 30 * $i;
		
		echo '<p>';
		echo '<img src="/images/driver/'.$row['id'].'.jpg" style="max-width:200px; max-height:200px;"></br>';
		echo '<span style="font-size:'.$size.'%">'.ordinal($i).' <b>'.$name.'</b></span></br>';
		echo '('.implode($teams, ', ').') ';
		echo ($row['score']+0).' pts';
		echo '</p>';
		
		$i++;
	}
	echo '</center>';
	echo '</div>';
	// Jobb oldali div vége
	
	/*if ($year >= (actual - 1)) {
		switch ($year) {
		case actual:
			echo '<span class="message" id="headinfo">Current season</span>';
		break;
		
		case (actual - 1):
			echo '<span class="message" id="headinfo">Previous season</span>';
		break;
		
		case (actual + 1):
			echo '<span class="message" id="headinfo">Next season</span>';
		break;
		
		default:
			echo '<span class="message" id="headinfo">Scheduled season</span>';
		}
	}*/
	echo '<h1 class="title">';
	if ($year != $first) {
		echo '<a href="/f1/'.($year-1).'"><img src="/images/icon/left_2.png" style="width:18px; height:18px; margin-right:3px;"></a>';
	}
	echo $year.' Formula One season';
	if ($year != last) {	
		echo '<a href="/f1/'.($year+1).'"><img src="/images/icon/right_2.png" style="width:18px; height:18px; margin-left:3px;"></a>';
	}
	echo '</h1>';
	
	// Tesztek
	$tests = mysqli_query($f1db,
		"SELECT *
		FROM f1_test AS test
		INNER JOIN circuit
		ON test.circuit = circuit.no
		WHERE yr = $year
		ORDER BY no_yr ASC"
	);
	if (mysqli_num_rows($tests) > 0) {
		echo '<h2>Tests</h2>';
		echo '<table class="results">';
		echo '<th style="width:20px;">#</th><th>Name</th><th colspan="2">Place</th><th>Date</th>';
		while ($row = mysqli_fetch_array($tests)) {
			echo '<tr>';
			echo '<td class="rnd">'.$row['no_yr'].'</td>';
			echo '<td>'.test_link($row['yr'], $row['no_yr'], $row['name']).'</td>';
			echo '<td>'.circuit_link($row['id'], $row['fullname']).'</td>';
			echo '<td>'.$row['place'].'</td>';
			$start = strtotime($row['start']);
			$end   = strtotime($row['end']);
			if (date('F', $start) == date('F', $end)) {
				$time = date('j', $start).' - '.date('j F', $end);
			}
			else {
				$time = date('j F', $start).' - '.date('j F', $end);
			}
			echo '<td>'.$time.'</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	
	/////////////
	// Futamok //
	/////////////
	
	echo '<h2>Grands Prix</h2>';
	
	$query_races = mysqli_query($f1db,
		"SELECT *
		FROM f1_gp AS gps
		INNER JOIN f1_details AS data
		ON gps.no = data.no
		INNER JOIN country AS name
		ON gps.gp = name.gp
		WHERE gps.yr = $year
		ORDER BY gps.no ASC"
	);
	
	$query_circ = mysqli_query($f1db,
		"SELECT gps.no, circuit.fullname, circuit.place, circuit.id
		FROM f1_gp AS gps
		INNER JOIN f1_details AS data
		ON gps.no = data.no
		INNER JOIN country AS name
		ON gps.gp = name.gp
		INNER JOIN circuit
		ON data.circuit = circuit.no
		WHERE gps.yr = $year
		ORDER BY gps.no ASC"
	);
	$circ = array();
	while ($row = mysqli_fetch_array($query_circ)) {
		$circ[$row['no']]['name']  = $row['fullname'];
		$circ[$row['no']]['id']    = $row['id'];
		$circ[$row['no']]['place'] = $row['place'];
	}

	echo '<table class="results"><tbody><tr>
		<th>#</th>
		<th>Grand Prix</th>
		<th>Place</th>
		<th>Circuit</th>
		<th>Date</th>';
	
	while ($row = mysqli_fetch_array($query_races)) {
		$date = strtotime($row['dat']);
		$date = $date > 0 ? date('j F', $date) : '';
		
		if (isset($circ[$row['no']])) {
			$circuit = circuit_link($circ[$row['no']]['id'], $circ[$row['no']]['name']);
			$place   = $circ[$row['no']]['place'];
		}
		else {
			$circuit = '';
			$place   = '';
		}

		echo '<tr>';
		echo '<td class="rnd">' . $row['no_yr'] . '</td>';
		echo '<td>'.flag($row['gp']).race_link($row['yr'], $row['gp'], $row['name'] . ' GP').'</td>';
		echo '<td>'.$place.'</td>';
		echo '<td>'.$circuit.'</td>';
		echo '<td>'.$date.'</td>';
		echo '</tr>';
	}
	
	echo '</tbody></table>';
	/////////////
	// Results //
	/////////////
	
	echo '<h2>Results</h2>';
	$seasonresults = array();
	
	// Győztes
	$winners = mysqli_query($f1db,
		"SELECT race.gp, country.name,
			driver.first, driver.de, driver.last, driver.sr, driver.id, driver.country
		FROM f1_race AS race
		INNER JOIN driver
		ON (race.driver = driver.no)
		INNER JOIN country
		ON race.gp = country.gp
		WHERE yr = $year
		AND finish = 1
		ORDER BY race.no ASC");
		
	$i = 1;
	while ($row = mysqli_fetch_array($winners)) {
		$seasonresults[$row['gp']]['name'] = $row['name'];		
			$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		$seasonresults[$row['gp']]['winner'][$i]['name'] = $name;
		$seasonresults[$row['gp']]['winner'][$i]['id'] = $row['id'];
		$seasonresults[$row['gp']]['winner'][$i]['country'] = $row['country'];
		$i++;
	}
	
	// Pole
	$qual = mysqli_query($f1db,
		"SELECT race.gp, country.name,
			driver.first, driver.de, driver.last, driver.sr, driver.id, driver.country
		FROM f1_q AS q
		INNER JOIN f1_race AS race
		ON (race.no = q.entr_no)
		INNER JOIN driver
		ON (race.driver = driver.no)
		INNER JOIN country
		ON (race.gp = country.gp)
		WHERE q.place = 1
		AND race.yr = $year"
	);

	while ($row = mysqli_fetch_array($qual)) {
		$seasonresults[$row['gp']]['name'] = $row['name']; // Verseny neve (az időmérőt rakom ki először)
			// De ha nem, akkor nem látszik, ezért beraktam a futamhoz is
			$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		$seasonresults[$row['gp']]['pole']['name'] = $name;
		$seasonresults[$row['gp']]['pole']['id'] = $row['id'];
		$seasonresults[$row['gp']]['pole']['country'] = $row['country'];
	}
	// Fastest
	$fastest = mysqli_query($f1db,
		"SELECT gp.gp,
		driver.first, driver.de, driver.last, driver.sr, driver.id, driver.country
		FROM f1_fastest AS fastest
		INNER JOIN f1_gp AS gp
		ON (fastest.rnd = gp.no)
		INNER JOIN driver
		ON (fastest.driver = driver.no)
		WHERE gp.yr = $year");
		
	while ($row = mysqli_fetch_array($fastest)) {
			$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		$seasonresults[$row['gp']]['fastest']['name'] = $name;
		$seasonresults[$row['gp']]['fastest']['id'] = $row['id'];
		$seasonresults[$row['gp']]['fastest']['country'] = $row['country'];
	}
	
	// Kiírás
	echo '<table class="results">';
	echo '<th>#</th><th>Grand Prix</th><th>Winner</th><th>Pole</th><th>Fastest lap</th>';
	$i = 1;
	foreach ($seasonresults as $gp => $res) {
		echo '<tr>';
		// Rnd
		echo '<td class="rnd" width="15" align="center">'.$i.'</td>';
		echo '<td>'.flag($gp).race_link($year, $gp, $res['name'].' GP').'</td>';
		// Winner
		echo '<td>';
		if (count($res['winner']) > 0) {
			foreach ($res['winner'] as $wnr) { // Ha kettős győzelem (régen)
				echo flag($wnr['country']).driver_link($wnr['id'], $wnr['name']).'</br>';
			}
		}
		echo '</td>';
		// Pole
		echo '<td>';
		if (isset($res['pole'])) {
			echo flag($res['pole']['country']).driver_link($res['pole']['id'], $res['pole']['name']).'</br>';
		}
		echo '</td>';
		// Fastest
		echo '<td>';
		if (isset($res['fastest'])) {
			echo flag($res['fastest']['country']).driver_link($res['fastest']['id'], $res['fastest']['name']).'</br>';
		}
		echo '</td>';
		echo '</tr>';
		$i++;
	}
	echo '</table>';
	
	// VB
	echo '<h2 id="drivers_standing">Drivers\' championship</h2>';
	drivers_championship($year);
	
	// Konstruktőri VB
	if ($year >= 1958) {
		echo '<h2 id="constructors_standing">Constructors\' championship</h2>';
		if ($year >= 1976) {
			cc($f1db, $year);
		}
		else {
			cc_old($f1db, $year);
		}
	}

	require_once('included/foot.php');	
?>