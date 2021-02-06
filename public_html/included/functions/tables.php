<?php
// FUTAM
function table_race($f1db, $yr, $no) {
	$race_enabled = mysqli_query($f1db,
		"SELECT shown
		FROM f1_gp_schedule
		WHERE rnd = $no
		AND type = 'R'
		LIMIT 1");
	if (mysqli_num_rows($race_enabled) > 0) {
		$race_enabled = mysqli_fetch_array($race_enabled);
		if ($race_enabled['shown'] == 0) {
			$hide_race = true;
		}
	}
	if (isset($hide_race) && $hide_race) {
		return false;
	}

	// Lekérdezés
	$race = mysqli_query($f1db,
		"SELECT *, driver.id AS driverid, team.id AS teamid, driver.country
		FROM f1_race AS race
		INNER JOIN driver
		ON (race.driver = driver.no)
		INNER JOIN team
		ON (race.team = team.no)
		WHERE rnd = $no
		AND status > 0
		ORDER BY status ASC, finish ASC");
	
	if (mysqli_num_rows($race) == 0) {
		return false;
	}
	
	// Tömb
	$results = array();
	while ($rac = mysqli_fetch_array($race)) {		
		if ($rac['status'] == 1) {
			$status = $rac['finish'];
		}
		else {
			$status = status($rac['status']);
		}
		
		// Helyezés
		$results[$rac['finish']]['status'] = $status;
		$results[$rac['finish']]['class'] = cellclass($status, $yr);
		$results[$rac['finish']]['team'] = $rac['fullname'];
		$results[$rac['finish']]['teamid'] = $rac['teamid'];
		if ($rac['time'] != 0) {
			if ($rac['finish'] == 1) {
				$time = racetime($rac['time']);
				$firsttime = $rac['time'];
			}
			else {
				$time = '+'.racetime($rac['time'] - $firsttime);
			}			
			$results[$rac['finish']]['time'] = $time;
		}
		else {
			$results[$rac['finish']]['time'] = $rac['note'];
		}
		$results[$rac['finish']]['car_no'] = $rac['car_no'];
		$results[$rac['finish']]['tyre'] = $rac['tyre'];
		// Versenyző
		$results[$rac['finish']]['driver'][$rac['driverid']]['country'] = $rac['country'];
		$results[$rac['finish']]['driver'][$rac['driverid']]['name'] = name($rac['first'], $rac['de'], $rac['last'], $rac['sr']);
		$results[$rac['finish']]['driver'][$rac['driverid']]['laps'] = $rac['laps'];
		
	}
	// Fejléc
	echo '<table class="results">';
	echo '<th width="30">#</th>' .
		'<th width="40">Car #</th>' .
		'<th width="150">Driver</th>' .
		'<th width="100">Team</th>' .
		'<th width="20">T</th>' .
		'<th width="30">Laps</th>' .
		'<th width="50">Time</th>';
	
	foreach ($results AS $res) {
		$count = count($res['driver']);
		$i = 1;
		foreach ($res['driver'] AS $id => $rw) { // Ha megosztott
			echo '<tr>';
			if ($i == 1) { // Első sor
				if($count>1){$rs=' rowspan="'.$count.'"';}else{$rs='';}
				echo '<td'.$rs.' class="'.$res['class'].'" align="center">'.$res['status'].'</td>';
				echo '<td'.$rs.' align="center">'.$res['car_no'].'</td>';
				
				$link = driver_link($id, $rw['name']);
				echo '<td>'.flag($rw['country']).$link.'</td>';
				
				$link = team_link($res['teamid'], $res['team']);
				echo '<td'.$rs.'>'.$link.'</td>';
				
				if($res['tyre']!=''){$tclass=' class="tyre_'.$res['tyre'].'"';}else{$tclass='';}
				echo '<td'.$rs.$tclass.' align="center"><b>'.$res['tyre'].'</b></td>';
				echo '<td>'.$rw['laps'].'</td>';
				echo '<td'.$rs.'>'.$res['time'].'</td>';
				
			}
			else { // Többi
				$link = driver_link($id, $rw['name']);
				echo '<td>'.flag($rw['country']).$link.'</td>';
				echo '<td>'.$rw['laps'].'</td>';
			}
			echo '</tr>';
			$i++;
		}
	}
	echo '</table>';
}

// HAGYOMÁNYOS IDŐMÉRŐ
function table_q_simple($f1db, $yr, $no) {
	$race_enabled = mysqli_query($f1db,
		"SELECT shown
		FROM f1_gp_schedule
		WHERE rnd = $no
		AND type = 'Q'
		LIMIT 1");
	if (mysqli_num_rows($race_enabled) > 0) {
		$race_enabled = mysqli_fetch_array($race_enabled);
		if ($race_enabled['shown'] == 0) {
			$hide_race = true;
		}
	}
	if (isset($hide_race)) {
		return false;
	}
	
	$quali = mysqli_query($f1db,
		"SELECT q.q1, q.dnq, q.place,
			driver.first, driver.de, driver.last, driver.sr, driver.id AS driverid, driver.country,
			race.car_no,
			team.id AS teamid, team.fullname
		FROM f1_q AS q
		INNER JOIN f1_race AS race
			ON (race.no = q.entr_no)
				INNER JOIN driver
			ON (race.driver = driver.no)
		INNER JOIN team
			ON (race.team = team.no)
		WHERE race.rnd = $no
		ORDER BY place = 0, place ASC");
		
	if (mysqli_num_rows($quali) == 0) {
		return false;
	}
	
	$qtime = array();

	while ($row = mysqli_fetch_array($quali)) {	
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
			$driverlink = driver_link($row['driverid'], $name);
		$id = $row['driverid'];
		if ($row['q1'] > 0) {
			$time = $row['q1'];
		}
		else {
			$time = 'no time';
		}
		$team = $row['fullname'];
			$teamlink = team_link($row['teamid'], $team);
		$car = $row['car_no'];
		
		$qtime[$id]['place'] = $row['place'];
		$qtime[$id]['name'] = flag($row['country']).$driverlink;
		$qtime[$id]['team'] = $teamlink;
		$qtime[$id]['car'] = $car;
		$qtime[$id]['time'] = $time;
		$qtime[$id]['dnq'] = $row['dnq'];
	}
		
	echo '<table class="results">';
	echo '<th width="30">#</th>' .
		'<th width="40">Car #</th>' .
		'<th width="150">Driver</th>' .
		'<th width="100">Team</th>' .
		'<th width="50">Time</th>'.
		'<th width="50">Gap</th>';

	foreach ($qtime as $id => $driver) {
		if ($driver['place'] == 1) {
			$class = ' class="first"';
		}
		else {
			$class = '';
		}
		
		echo '<tr>';
		if ($driver['dnq'] == 0) {
			echo '<td'.$class.' align="center">'.$driver['place'].'</td>';
		}
		else {
			echo '<td class="DNQ" align="center">DNQ</td>';
		}
		echo '<td align="center">'.$driver['car'].'</td>';
		echo '<td>'.$driver['name'].'</td>';
		echo '<td>'.$driver['team'].'</td>';
		if (is_numeric($driver['time'])) {
			if ($driver['place'] == 1) {
				$gap = '---';
				$firsttime = $driver['time'];
			}
			else {
				$gap = '+'.racetime($driver['time'] - $firsttime);
			}
		}
		else {
			$gap = '';
		}
		echo '<td>'.racetime($driver['time']).'</td>';
		echo '<td>'.$gap.'</td>';
		echo '</tr>';
	}

	echo '</table>';
}

// 3 SZAKASZOS IDŐMÉRŐ
function table_q_3($f1db, $yr, $no) {
	$race_enabled = mysqli_query($f1db,
		"SELECT shown
		FROM f1_gp_schedule
		WHERE rnd = $no
		AND type = 'Q'
		LIMIT 1"
	);
	if (mysqli_num_rows($race_enabled) > 0) {
		$race_enabled = mysqli_fetch_array($race_enabled);
		if ($race_enabled['shown'] == 0) {
			$hide_race = true;
		}
	}
	if (isset($hide_race)) {
		return false;
	}
	
	$query = mysqli_query($f1db,
		"SELECT q.place, q.q1, q.q2, q.q3, q.dnq, q.dsq,
			driver.first, driver.de, driver.last, driver.sr, driver.id AS driverid, driver.country,
			race.status, race.car_no, race.start,
			team.id AS teamid, team.fullname AS teamname, team.country AS teamcountry
		FROM f1_q AS q
		INNER JOIN f1_race AS race
			ON (race.no = q.entr_no)
		INNER JOIN driver
			ON (race.driver = driver.no)
		INNER JOIN team
			ON (race.team = team.no)
		WHERE race.rnd = $no
		ORDER BY place = 0, place ASC"
	);
		
	if (mysqli_num_rows($query) == 0) {
		return false;
	}
	
	echo '<table class="results">';
	echo '<th width="30">#</th><th>Driver</th><th>Team</th><th width="65">Q1</th><th width="65">Q2</th><th width="65">Q3</th><th>Grid</th>';
	
	while ($row = mysqli_fetch_array($query)) {
		echo '<tr>';
		// Helyezés, DNQ, DSQ
		if ($row['dnq'] == 0 && $row['dsq'] == 0) {
			$class = '';
			$place = $row['place'];
		}
		else if ($row['dnq'] == 1) {
			$class = 'class="DNQ" ';
			$place = 'DNQ';
		}
		else {
			$class = 'class="DSQ" ';
			$place = 'DSQ';
		}
		
		echo '<td '.$class.'align="center">'.$place.'</td>';
		
		// Név
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		echo '<td>'.flag($row['country']).driver_link($row['driverid'], $name).'</td>';
		echo '<td>'.flag($row['teamcountry']).team_link($row['teamid'], $row['teamname']).'</td>';
		
		$q1 = $row['q1'] > 0 ? racetime($row['q1']) : ($row['q1'] == -1 ? 'no time' : '');
		$q2 = $row['q2'] > 0 ? racetime($row['q2']) : ($row['q2'] == -1 ? 'no time' : '');
		$q3 = $row['q3'] > 0 ? racetime($row['q3']) : ($row['q3'] == -1 ? 'no time' : '');
		
		echo'<td style="text-align:right;">'.$q1.'</td>';
		echo'<td style="text-align:right;">'.$q2.'</td>';
		echo'<td style="text-align:right;">'.$q3.'</td>';
		echo'<td>'.$row['start'].'</td>';
		echo '</tr>';
	}
	
	echo '</table>';
}

// SZABADEDZÉS
function table_practice($f1db, $no) {
	$prac_enabled = mysqli_query($f1db,
		"SELECT shown
		FROM f1_gp_schedule
		WHERE no = $no
		AND type = 'P'
		LIMIT 1");
	if (mysqli_num_rows($prac_enabled) > 0) {
		$prac_enabled = mysqli_fetch_array($prac_enabled);
		if ($prac_enabled['shown'] == 0) {
			$hide_prac = true;
		}
	}
	if ($hide_prac) {
		return false;
	}
	
	$quali = mysqli_query($f1db,
		"SELECT p.tme, p.place, p.laps,
			driver.first, driver.de, driver.last, driver.sr, driver.id AS driverid, driver.country,
			race.status, race.car_no,
			team.id AS teamid, team.fullname
		FROM f1_practice AS p
		INNER JOIN f1_race AS race
			ON (race.no = p.entr_no)
				INNER JOIN driver
			ON (race.driver = driver.no)
		INNER JOIN team
			ON (race.team = team.no)
		WHERE p.practice = $no
		ORDER BY place ASC");
		
	$qtime = array();

	while ($row = mysqli_fetch_array($quali)) {	
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
			$driverlink = driver_link($row['driverid'], $name);
		$id = $row['driverid'];
		if ($row['tme']!=0) {
			$time = $row['tme'];
		}
		else {
			$time = 'no time';
		}
		$team = $row['fullname'];
			$teamlink = team_link($row['teamid'], $team);
		$car = $row['car_no'];
		
		$qtime[$id]['place'] = $row['place'];
		$qtime[$id]['name'] = flag($row['country']).$driverlink;
		$qtime[$id]['team'] = $teamlink;
		$qtime[$id]['car'] = $car;
		$qtime[$id]['time'] = $time;
		$qtime[$id]['laps'] = $row['laps'];
	}
		
	echo '<table class="results">';
	echo '<th width="30">#</th>' .
		'<th width="40">Car #</th>' .
		'<th width="150">Driver</th>' .
		'<th width="100">Team</th>' .
		'<th width="50">Time</th>'.
		'<th width="30">Gap</th>'.
		'<th width="50">Laps</th>';

	foreach ($qtime as $id => $driver) {
		echo '<tr>';
		echo '<td align="center">'.$driver['place'].'</td>';
		echo '<td align="center">'.$driver['car'].'</td>';
		echo '<td>'.$driver['name'].'</td>';
		echo '<td>'.$driver['team'].'</td>';
		echo '<td>'.racetime($driver['time']).'</td>';
		if ($driver['place'] == 1) {
			$besttime = $driver['time'];
			$gap = '---';
		}
		else if ($driver['time'] > 0) {
			$gap = '+'.racetime($driver['time'] - $besttime);
		}
		else {
			$gap = '';
		}
		echo '<td>'.$gap.'</td>';
		echo '<td>'.$driver['laps'].'</td>';
		echo '</tr>';
	}

	echo '</table>';
}

// PILÓTA EREDMÉNYEI
function results_driver($driver_no) {
	global $f1db;
	
	// Eredmények lekérdezése
	$res_s = mysqli_query($f1db,
		"SELECT DISTINCT rnd, yr, gp, team, status, finish
		FROM f1_race
		WHERE driver = $driver_no
		AND status > 0
		ORDER BY finish DESC"
	);
	
	if (mysqli_num_rows($res_s) == 0) {
		$act = mysqli_query($f1db,
			"SELECT no
			FROM f1_active_driver
			WHERE no = $driver_no
			AND status = 'R'
			LIMIT 1"
		);
		if (mysqli_num_rows($act) == 0) {
			return;
		}
	}
		
	while ($res = mysqli_fetch_array($res_s)) {
		$_yr   = $res['yr'];
		$_team = $res['team'];
		$_gp  = $res['gp'];
		if ($res['status'] == 1) {$_res = $res['finish'];}
		else {$_res = status($res['status']);}	
		
		$results[$_yr][$_team][$_gp] = $_res;
	}

	// Év(/csapat?)
	$rows = mysqli_query($f1db,
		"SELECT DISTINCT yr
		FROM f1_race
		WHERE driver = $driver_no
		ORDER BY rnd ASC");

	$years = array();
	while ($yrs = mysqli_fetch_array($rows)) {
		array_push($years, $yrs['yr']);
		$lastyr = $yrs['yr'];
	}
	
	// Ha idén még nem volt verseny, de benne van az active táblában	
	if (empty($lastyr) || $lastyr != actual) {
		$cur_yr = mysqli_query($f1db,
			"SELECT team.id, team.fullname, team.no
			FROM f1_active_driver AS act
			INNER JOIN team
			ON act.team = team.no
			WHERE act.no = $driver_no
			AND status = 'R'
			LIMIT 1"
		);
		if (mysqli_num_rows($cur_yr) > 0) {
			array_push($years, actual);
			$row = mysqli_fetch_array($cur_yr);
			$act_team_no   = $row['no'];
			$act_team_id   = $row['id'];
			$act_team_name = $row['fullname'];
		}
	}
		
	$yrs = implode(' OR yr = ', $years);

	// összes futam
	$gps = mysqli_query($f1db,
		"SELECT yr, gp, no_yr
		FROM f1_gp
		WHERE yr = $yrs
		ORDER BY no ASC");
		
	while ($gp = mysqli_fetch_array($gps)) {
		$race[$gp['yr']][$gp['no_yr']] = $gp['gp'];
	}
		
	// Év/csapat (Fő ciklus)
	$rows = mysqli_query($f1db,
		"SELECT DISTINCT
		GREATEST(yr, team) AS yr,
		LEAST(team, yr) AS team,
		team.fullname, team.id
		FROM f1_race AS race
		INNER JOIN team
		ON (team = team.no)
		WHERE driver = $driver_no
		AND status > 0
		ORDER BY rnd ASC"
	);
		
	$i = 0;

	while ($tr = mysqli_fetch_array($rows)) {
		$cells[$i]['yr'] = $tr['yr'];
		$cells[$i]['team'] = $tr['team'];
		
		$cells[$i]['fullname'] = team_link($tr['id'], $tr['fullname']);
		$i++;
	}
	// Ha aktív
	if (isset($act_team_id)) {
		$cells[$i]['yr'] = actual;
		$cells[$i]['team'] = $act_team_no;
		$cells[$i]['fullname'] = team_link($act_team_id, $act_team_name);
		$results[actual] = array();
	}
		
	$max = 0;
	foreach ($race as $cnt) {
		if (count($cnt) > $max) {
			$max = count($cnt);
		}
	}
	
	$final = mysqli_query($f1db,
		"SELECT yr, score, place
		FROM f1_tbl
		WHERE driver = ".$driver_no);
		
	while ($fnl = mysqli_fetch_array($final)) {
		$results['score'][$fnl['yr']] = $fnl['score'] - 0; // Nincsenek tizedesjegyek
		$results['place'][$fnl['yr']] = $fnl['place'];
	}
	
	if (isset($act_team_id)) {
		$results['score'][actual] = 0;
		$results['place'][actual] = '<br><br>';
	}

	echo '<h2>Results</h2>';

	// Táblázat
	echo '<table class="results" style="text-align:center;">';

	$prevyr = 0;
	foreach ($cells as $head) {	
		// Szünet
		if ($prevyr > 0 && $prevyr < $head['yr'] - 1) {
			$first = $prevyr + 1;
			$last  = $head['yr'] - 1;
			
			echo '<tr><td>';
			if ($first != $last) {
				echo $first.'<br>-<br>'.$last;
			} else {
				echo $first;
			}
			echo '</td><td align="left" class="notheld" colspan="'.($max+3).'">Not raced for <b>'.($last-$first+1).'</b> years</td>';
			echo '</tr>';
		}
		$rowspan = sizeof($results[$head['yr']]);
		
		echo '<tr>';

		if ($prevyr != $head['yr']) {
			echo '<td rowspan="'.$rowspan.'">'.season_link($head['yr']).'</td>';
		}
		// $prevyear lent növekszik
		echo '<td>' . $head['fullname'] . '</td>';
		
		foreach ($race[$head['yr']] as $cell) {
			$pos = '';
			$class = 'empty';
			
			if (isset($results[$head['yr']][$head['team']][$cell])) {
				$pos = $results[$head['yr']][$head['team']][$cell];
				if (is_numeric($pos)) {
					switch ($pos) {
						case 1:
							$class = 'first';
						break;
						
						case 2:
							$class = 'second';
						break;
						
						case 3:
							$class = 'third';
						break;
						
						default: $class = scored($pos, $head['yr']);
					}
				}
				else {
					$class = $pos;
				}
			$pos = '<br>' . $pos;
			}
			echo '<td class="' . $class . '">'.race_link($head['yr'], $cell, $cell);
			echo $pos;
			echo '</td>';	
		}
		
		$i = count($race[$head['yr']]);
		while ($i < $max) {
			echo '<td></td>';
			$i++;
		}
		
		if ($prevyr != $head['yr']) {
			$score = $results['score'][$head['yr']];
			$place = $results['place'][$head['yr']];
			
			if ($place == 999) {$place = 'DSQ';}
			if ($score == 0 && is_numeric($place)) {$place = 'NC<br>('.$place.')';} // $place != '' => Ha még nem volt verseny
					
			switch ($place) {
				case 1:     $class = 'class="first"';  break;
				case 2:     $class = 'class="second"'; break;
				case 3:     $class = 'class="third"';  break;
				case 'DSQ': $class = 'class="DSQ"';    break;
				default: '';
			}
			
			if (is_numeric($place)) {$place = '<b>'.$place.'</b>';}
			
			echo '<td width="30" '.$class.' rowspan="'.$rowspan.'">'.$score.'</td>';
			echo '<td width="30" '.$class.' rowspan="'.$rowspan.'">'.$place.'</td>';
		}
		echo '</tr>';
		
		$prevyr = $head['yr'];
	}
	echo '</table>';
}

// CSAPAT EREDMÉNYEI
function results_team($team_no) {
	global $f1db;
	
	// 0. Aktív?
	$active = mysqli_query($f1db,
		"SELECT *
		FROM f1_active_team
		WHERE no = $team_no
		LIMIT 1"
	);
	
	// 1. Év végi eredmények
	$tbl = mysqli_query($f1db,
		"SELECT *
		FROM f1_tbl_cons
		WHERE chassis = $team_no
		ORDER BY yr ASC, place DESC"
	);
	$places = array();
	while ($row = mysqli_fetch_array($tbl)) {
		$places[$row['yr']]['place'] = $row['place'];
		$places[$row['yr']]['score'] = $row['score'];
		$last_yr = $row['yr'];
	}
	
	// 2. Eredmények lekérdezése
	$res_s = mysqli_query($f1db,
		"SELECT DISTINCT rnd, yr, gp, driver, status, finish
		FROM f1_race
		WHERE team = $team_no
		AND status > 0
		ORDER BY rnd ASC, car_no ASC");
		
	while ($res = mysqli_fetch_array($res_s)) {
		$_yr   = $res['yr'];
		$_driver = $res['driver'];
		$_gp  = $res['gp'];
		if ($res['status'] == 1) {$_res = $res['finish'];}
		else {$_res = status($res['status']);}	
		
		$results[$_yr][$_driver][$_gp] = $_res;
	}

	$new_season = (mysqli_num_rows($active) > 0 && $_yr != actual) ? true : false;
	
	if ($new_season) {
		$places[actual]['place'] = $row['<br><br>'];
		$places[actual]['score'] = 0;
	}

	// 3. Év/csapat
	$rows = mysqli_query($f1db,
		"SELECT DISTINCT yr
		FROM f1_race
		WHERE team = $team_no
		AND status > 0
		ORDER BY rnd ASC"
	);

	$years = array();
	while ($yrs = mysqli_fetch_array($rows)) {
		array_push($years, $yrs['yr']);
	}
	if ($new_season) {
		array_push($years, actual);
	}
		
	$yrs = implode(' OR yr = ', $years);

	// 4. Összes futam
	$gps = mysqli_query($f1db,
		"SELECT yr, gp, no_yr
		FROM f1_gp
		WHERE yr = $yrs
		ORDER BY no ASC");
		
	while ($gp = mysqli_fetch_array($gps)) {
		$race[$gp['yr']][$gp['no_yr']] = $gp['gp'];
	}
		
	// 5. Év/csapat (Fő ciklus)
	$rows = mysqli_query($f1db,
		"SELECT DISTINCT
		GREATEST(yr, driver) AS yr,
		LEAST(driver, yr) AS driver,
		driver.first, driver.de, driver.last, driver.sr, driver.id, driver.country
		FROM f1_race AS race
		INNER JOIN driver
		ON (driver = driver.no)
		WHERE team = $team_no
		AND race.status > 0
		ORDER BY rnd ASC");
		
	$i = 0;

	while ($tr = mysqli_fetch_array($rows)) {
		$cells[$i]['yr'] = $tr['yr'];
		$cells[$i]['driver'] = $tr['driver'];
		
		$name = name($tr['first'],$tr['de'],$tr['last'],$tr['sr']);
		$link = 'driver.php?driver='.$tr['id'];
		
		$cells[$i]['country'] = $tr['country'];
		$cells[$i]['fullname'] = driver_link($tr['id'], $name);
		$i++;
	}
	if ($new_season) {
		$new_drivers = mysqli_query($f1db,
			"SELECT driver.no, id, first, de, last, sr, country
			FROM f1_active_driver AS act
			INNER JOIN driver
			ON act.no = driver.no
			WHERE team = $team_no
			AND status = 'R'"
		);
		while ($row = mysqli_fetch_array($new_drivers)) {
			$cells[$i]['yr'] = actual;
			$cells[$i]['driver'] = $row['no'];
			
			$name = name($row['first'],$row['de'],$row['last'],$row['sr']);
			$link = 'driver.php?driver='.$row['id'];
			
			$cells[$i]['country'] = $row['country'];
			$cells[$i]['fullname'] = driver_link($row['id'], $name);
			
			// 2. ponthoz
			$results[actual][$row['no']] = array();
			
			$i++;
		}
	}
		
	$max = 0;
	foreach ($race as $cnt) {
		if (count($cnt) > $max) {
			$max = count($cnt);
		}
	}
	
	echo "\n".'<table class="results" style="text-align:center;">';

	$prevyr = 0;
	foreach ($cells as $head) {	
		// Szünet
		if ($prevyr > 0 && $prevyr < $head['yr'] - 1) {
			$first = $prevyr + 1;
			$last  = $head['yr'] - 1;
			
			echo '<tr><td>';
			if ($first != $last) {
				echo $first.'<br>-<br>'.$last;
			} else {
				echo $first;
			}
			echo '</td><td align="left" class="notheld" colspan="'.($max+3).'">Not competed for <b>'.($last-$first+1).'</b> years</td>';
			echo '</tr>';
		}
		
		$rowspan = sizeof($results[$head['yr']]) + 1; // !!!
			
		echo '<tr>';

		if ($prevyr != $head['yr']) {	
			echo '<td rowspan="'.$rowspan.'">'.season_link($head['yr']).'</td>';
			
			echo '<td></td>';
			foreach ($race[$head['yr']] as $cell) {
				echo '<td>'.race_link($head['yr'], $cell, $cell).'</td>';
			}
			
			$i = count($race[$head['yr']]);
			while ($i < $max) {
				echo '<td></td>';
				$i++;
			}
			
			if ($prevyr != $head['yr']) {
				if (isset($places[$head['yr']])) {
					$score = ($places[$head['yr']]['score']) + 0;
					$place = $places[$head['yr']]['place'];
					
					switch ($place) {
						case 1:
							$class = ' class="first"';
						break;
						
						case 2:
							$class = ' class="second"';
						break;
						
						case 3:
							$class = ' class="third"';
						break;
						
						default: $class = '';
					}
					
					echo '<td width="35" rowspan="'.$rowspan.'"'.$class.'>'.$score.'</td>';
					echo '<td width="35" rowspan="'.$rowspan.'"'.$class.'>'.$place.'</td>';
				}
			}
			
			$prevyr = $head['yr'];		
				
			echo '</tr><tr>';
		}
		// $prevyear lent növekszik
		
		echo '<td align="left">'.flag($head['country']).$head['fullname'].'</td>';
			
		foreach ($race[$head['yr']] as $cell) {
			$pos = '';
			$class = 'empty';
			
			if (isset($results[$head['yr']][$head['driver']][$cell])) {
				$pos = $results[$head['yr']][$head['driver']][$cell];
				if (is_numeric($pos)) {
					switch ($pos) {
						case 1:
							$class = 'first';
						break;
						
						case 2:
							$class = 'second';
						break;
						
						case 3:
							$class = 'third';
						break;
						
						default: $class = scored($pos, $head['yr']);
					}
				}
				else {
					$class = $pos;
				}
			}
			echo '<td class="' . $class . '">';
			echo $pos;
			echo '</td>';	
		}
		
		$i = count($race[$head['yr']]);
		while ($i < $max) {
			echo '<td></td>';
			$i++;
		}
		
		echo '</tr>';
	}
	echo '</table>';
}

// PILÓTA HELYEZÉSEI
function places_driver($driver_no) {
	global $f1db;
	
	// Legjobb, legrosszabb (célbaérés)
	$minmax = mysqli_query($f1db,
		"SELECT MIN(finish) AS min, MAX(finish) AS max
		FROM f1_race
		WHERE driver = ".$driver_no."
		AND status = 1");
		
	$mm = mysqli_fetch_array($minmax);

		$min = $mm['min'];
		$max = $mm['max'];
		
	// Összes célbaérés

	$allfinish = mysqli_query($f1db,
		"SELECT finish,
		COUNT(*) AS count
		FROM f1_race
		WHERE driver = ".$driver_no."
		AND status = 1
		GROUP BY finish
		ORDER BY finish ASC");

	$places = array();
		
	while ($fnshs = mysqli_fetch_array($allfinish)) {
		$offset = $fnshs['finish'];
		
		$places[$offset] = $fnshs['count'];
	}

	$i = $min;

	echo '<table class="results" style="text-align:left;">';

	if (mysqli_num_rows($allfinish) > 0) {
		while ($i <= $max) {
			if (isset($places[$i])) {
			
			$width = $places[$i] * 7;
			
			echo '<tr><td><b>'.$i . '</b></td><td><span class="bar" style="padding-left:'.$width.'px;">&nbsp;</span> '.$places[$i].'</td></tr>';
			
			}
			else {
				echo '<tr><td><b>'.$i.'</b></td><td>0</td></tr>';
			}
			
			$i++;
		}
	}

	// Nem ért célba

	$nonfinish = mysqli_query($f1db,
		"SELECT status,
		COUNT(*) AS count
		FROM f1_race
		WHERE driver = ".$driver_no."
		AND status > 1
		GROUP BY status
		ORDER BY status ASC"); // status a 0 kizárása miatt nagyobb 1-nél
			
	if (mysqli_num_rows($nonfinish) > 0) {
		while ($fnshs = mysqli_fetch_array($nonfinish)) {
			$width = $fnshs['count'] * 7;
			
			echo '<tr><td><b>'.status($fnshs['status']) . '</b></td><td><span class="bar_no" style="padding-left:'.$width.'px;">&nbsp;</span> '.$fnshs['count'].'</td></tr>';
		}
	}
		echo '</table>';
}

// PILÓTA IDŐMÉRŐS HELYEZÉSEI
function places_driver_qual($driver_no) {
	global $f1db;
	
	// Legjobb, legrosszabb (célbaérés)
	$minmax = mysqli_query($f1db,
		"SELECT MIN(place) AS min, MAX(place) AS max
		FROM f1_q AS q
		INNER JOIN f1_race AS race
		ON q.entr_no = race.no
		WHERE race.driver = $driver_no
		AND dnq = 0 AND dsq = 0");
		
	$mm = mysqli_fetch_array($minmax);

		$min = $mm['min'];
		$max = $mm['max'];
		
	// Összes célbaérés

	$allfinish = mysqli_query($f1db,
		"SELECT place,
		COUNT(*) AS count
		FROM f1_q AS q
		INNER JOIN f1_race AS race
		ON q.entr_no = race.no
		WHERE race.driver = $driver_no
		AND q.dsq = 0
		AND q.dnq = 0
		GROUP BY q.place
		ORDER BY q.place ASC");

	$places = array();
		
	while ($fnshs = mysqli_fetch_array($allfinish)) {
		$offset = $fnshs['place'];
		
		$places[$offset] = $fnshs['count'];
	}

	$i = $min;

	echo '<table class="results" style="text-align:left;">';

	if (mysqli_num_rows($allfinish) > 0) {
		while ($i <= $max) {
			if (isset($places[$i])) {
			
			$width = $places[$i] * 7;
			
			echo '<tr><td><b>'.$i . '</b></td><td><span class="bar" style="padding-left:'.$width.'px;">&nbsp;</span> '.$places[$i].'</td></tr>';
			
			}
			else {
				echo '<tr><td><b>'.$i.'</b></td><td>0</td></tr>';
			}
			
			$i++;
		}
	}

	/*// Nem ért célba

	$nonfinish = mysqli_query($f1db,
		"SELECT finish,
		COUNT(*) AS count
		FROM f1_q
		WHERE driver = $driver_no
		AND (dns = 1 OR dnq = 1)
		GROUP BY place
		ORDER BY place ASC"); // status a 0 kizárása miatt nagyobb 1-nél
			
	if (mysqli_num_rows($nonfinish) > 0) {
		while ($fnshs = mysqli_fetch_array($nonfinish)) {
			$width = $fnshs['count'] * 7;
			
			echo '<tr><td><b>'.status($fnshs['status']) . '</b></td><td><span class="bar_no" style="padding-left:'.$width.'px;">&nbsp;</span> '.$fnshs['count'].'</td></tr>';
		}
	}*/
		echo '</table>';
}

// CSAPAT HELYEZÉSEI
function places_team($f1db, $team_no) {
	// Legjobb, legrosszabb (célbaérés)
	$minmax = mysqli_query($f1db,
		"SELECT MIN(finish) AS min, MAX(finish) AS max
		FROM f1_race
		WHERE team = ".$team_no."
		AND status = 1");
		
	$mm = mysqli_fetch_array($minmax);

		$min = $mm['min'];
		$max = $mm['max'];
		
	// Összes célbaérés

	$allfinish = mysqli_query($f1db,
		"SELECT finish,
		COUNT(*) AS count
		FROM f1_race
		WHERE team = ".$team_no."
		AND status = 1
		GROUP BY finish
		ORDER BY finish ASC");

	$places = array();
		
	while ($fnshs = mysqli_fetch_array($allfinish)) {
		$offset = $fnshs['finish'];
		
		$places[$offset] = $fnshs['count'];
	}

	$i = $min;

	echo '<table class="results" style="text-align:left;">';

	if (mysqli_num_rows($allfinish) > 0) {
		while ($i <= $max) {
			if (isset($places[$i])) {
			
			$width = $places[$i] * 1;
			
			echo '<tr><td><b>'.$i . '</b></td><td><span class="bar" style="padding-left:'.$width.'px;">&nbsp;</span> '.$places[$i].'</td></tr>';
			
			}
			else {
				echo '<tr><td><b>'.$i.'</b></td><td>0</td></tr>';
			}
			
			$i++;
		}
	}

	// Nem ért célba

	$nonfinish = mysqli_query($f1db,
		"SELECT status,
		COUNT(*) AS count
		FROM f1_race
		WHERE team = ".$team_no."
		AND status != 1
		GROUP BY status
		ORDER BY status ASC");
			
	if (mysqli_num_rows($nonfinish) > 0) {
		while ($fnshs = mysqli_fetch_array($nonfinish)) {
			$width = $fnshs['count'] * 1;
			
			echo '<tr><td><b>'.status($fnshs['status']) . '</b></td><td><span class="bar_no" style="padding-left:'.$width.'px;">&nbsp;</span> '.$fnshs['count'].'</td></tr>';
		}
	}
		echo '</table>';
}

// GUMIGYÁRTÓ
function table_tyre($f1db, $tyre_no) {
	$entrance = mysqli_query($f1db,
		"SELECT yr, COUNT(finish) AS count
		FROM f1_race
		WHERE tyre = '$tyre_no'
		GROUP BY yr");
	
	$start = mysqli_query($f1db,
		"SELECT yr, COUNT(finish) AS count
		FROM f1_race
		WHERE tyre = '$tyre_no'
		AND status <= 3
		GROUP BY yr");
		
	$finish = mysqli_query($f1db,
		"SELECT yr, COUNT(finish) AS count
		FROM f1_race
		WHERE tyre = '$tyre_no'
		AND status = 1
		GROUP BY yr");
	
	$first = mysqli_query($f1db,
		"SELECT yr, finish, COUNT(finish) AS count
		FROM f1_race
		WHERE tyre = '$tyre_no'
		AND finish <= 3
		GROUP BY yr, finish");
		
	$scored = mysqli_query($f1db,
		"SELECT yr, COUNT(finish) AS count
		FROM f1_race
		WHERE tyre = '$tyre_no'
		AND score > 0
		GROUP BY yr");
	
	$only = mysqli_query($f1db,
		"SELECT DISTINCT tyre, yr, COUNT( DISTINCT tyre ) AS count
		FROM f1_race
		WHERE tyre != ''
		GROUP BY yr
		HAVING count = 1
		AND tyre = '$tyre_no'");
		
	$results = array();
	
	while ($row = mysqli_fetch_array($entrance)) {
		$yr     = $row['yr'];
		$count  = $row['count'];
		
		$results[$yr]['entr'] = $count;
	}
	
	while ($row = mysqli_fetch_array($start)) {
		$yr     = $row['yr'];
		$count  = $row['count'];
		
		$results[$yr]['start'] = $count;
	}
	
	while ($row = mysqli_fetch_array($finish)) {
		$yr     = $row['yr'];
		$count  = $row['count'];
		
		$results[$yr]['finish'] = $count;
	}
	
	while ($row = mysqli_fetch_array($scored)) {
		$yr     = $row['yr'];
		$count  = $row['count'];
		
		$results[$yr]['scored'] = $count;
	}
	
	while ($row = mysqli_fetch_array($first)) {
		$yr     = $row['yr'];
		$finish = $row['finish'];
		$count  = $row['count'];
		
		$results[$yr][$finish] = $count;
	}
	
	while ($row = mysqli_fetch_array($only)) {		
		$yr = $row['yr'];
		$results[$yr]['only'] = 1;
	}
	
	// Táblázat
	echo '<table class="results" style="text-align:center;">';
	echo '<th>Year</th>
		<th width="30">1st</th>
		<th width="30">2nd</th>
		<th width="30">3rd</th>
		<th width="40">Scrd</th>
		<th width="40">Fnsd</th>
		<th width="40">Start</th>
		<th width="40">Entr</th>';
	
	foreach ($results as $yr => $res) {
		if (isset($prevyr)) {
			if ($prevyr != ($yr-1)) {
				echo '<tr><td>';
				$break1 = $prevyr+1;
				$break2 = $yr-1;
				if ($break1 != $break2) {echo $break1.'<br>-<br>'.$break2;}
				else {echo $break1;}
				echo '</td><td class="notheld" colspan="7" align="left"><i>Didn\'t compete</i></td>';
				echo '</tr>';
			}
		}
		echo '<tr>';
		echo '<td>'.season_link($yr).'</td>';
		if (!isset($res['only'])) {
			// Első
			if (isset($res[1])) {
				echo '<td class="first">'.$res[1].'</td>';
			}
			else { echo '<td></td>'; }
			// 2
			if (isset($res[2])) {
				echo '<td class="second">'.$res[2].'</td>';
			}
			else { echo '<td></td>'; }
			// 3
			if (isset($res[3])) {
				echo '<td class="third">'.$res[3].'</td>';
			}
			else { echo '<td></td>'; }
			// Pontszerző
			if (isset($res['scored'])) {
				echo '<td class="scrd">'.$res['scored'].'</td>';
			}
			else { echo '<td></td>'; }
			// Cél
			if (isset($res['finish'])) {
				echo '<td>'.$res['finish'].'</td>';
			}
			else { echo '<td></td>'; }
			// Rajt
			if (isset($res['start'])) {
				echo '<td>'.$res['start'].'</td>';
			}
			else { echo '<td></td>'; }
			// nevezés
			if (isset($res['entr'])) {
				echo '<td>'.$res['entr'].'</td>';
			}
			else { echo '<td></td>'; }
		}
		else {
			echo '<td colspan="7">Sole tyre supplier</td>';
		}
		echo '</tr>';
		$prevyr = $yr;
	}
	
	echo '</table>';
}

// RÉGI KONSTRUKTŐRI VB (legjobb autó számít)
function cc_old($f1db, $yr) {	
	$races_query = mysqli_query($f1db,
		"SELECT no, gp
		FROM f1_gp
		WHERE yr = $yr
		AND gp != '500'
		ORDER BY no ASC");
	$races = array();
	while ($row = mysqli_fetch_array($races_query)) {
		$races[$row['no']] = $row['gp'];
	}
	
	$results_query = mysqli_query($f1db,
		"SELECT rnd, status, finish,
			chassis.cons AS chassis, engine.cons AS engine
		FROM f1_race AS race
		INNER JOIN chassis ON race.chassis = chassis.no
		INNER JOIN engine ON race.engine = engine.no
		WHERE yr = $yr
		ORDER BY rnd ASC, finish DESC");
	$results = array();
	while ($row = mysqli_fetch_array($results_query)) {
		if ($row['status'] == 1) {
			$place = $row['finish'];
		}
		else {
			$place = status($row['status']);
		}
		
		$rnd = $row['rnd'];
		$ch  = $row['chassis'];
		$en  = $row['engine'];
		$results[$rnd][$ch][$en] = $place;
	}
	
	echo '<table class="results">';
	// Fejlécek
	echo '<tr><th width="30">#</th><th>Constructor</th>';
	foreach ($races AS $gp) {
		echo '<td width="30" align="center">';
		echo race_link($yr, $gp, '<b>'.$gp.'</b>').'<br>';
		echo '<img src="/images/flag/icon/'.$gp.'.png" width="22" height="14">';
		echo '</td>';
	}
	echo '<th>Score</th></tr>';
	
	$tbl = mysqli_query($f1db,
		"SELECT tbl.place, tbl.score, tbl.chassis AS ch_no, tbl.engine AS en_no,
			en.fullname AS engine, en.id AS en_id, 
			ch.country, ch.fullname AS chassis, ch.id AS ch_id
		FROM f1_tbl_cons AS tbl
		INNER JOIN team AS ch
		ON tbl.chassis = ch.no
		INNER JOIN team AS en
		ON tbl.engine = en.no
		WHERE tbl.yr = $yr
		ORDER BY tbl.place = 0, tbl.place ASC, tbl.places DESC, tbl.no ASC");
		
	while ($row = mysqli_fetch_array($tbl)) {
		echo '<tr>';
		if ($row['place'] == 0) {
			$row['place'] = '-';
		}
		echo '<td class="rnd">'.$row['place'].'</td>';
		if ($row['chassis'] == $row['engine']) {
			$cons = team_link($row['ch_id'], $row['chassis']); // Úgy is mindenki erre kíváncsi
		}
		else {
			$chassis = team_link($row['ch_id'], $row['chassis']);
			$engine  = engine_cons_link($row['en_id'], $row['engine']);
			$cons = $chassis.' - '.$engine;
		}
		echo '<td>'.flag($row['country']).$cons.'</td>';
		
		// Versenyek		
		$ch_no = $row['ch_no'];
		$en_no = $row['en_no'];		
		foreach ($races AS $rnd => $gp) {
			if (count($results[$rnd][$ch_no][$en_no]) > 0) {
				$place = $results[$rnd][$row['ch_no']][$row['en_no']];
				echo '<td align="center" class="'.cellclass($place, $yr).'">';
				echo $place;
			}
			else {
				echo '<td>';
			}
			echo '</td>';
		}		
		
		echo '<td>'.($row['score']+0).'</td>';
		echo '</tr>';
	}
	
	// Fejlécek alul
	echo '<tr><th>#</th><th>Constructor</th>';
	foreach ($races AS $gp) {
		echo '<td align="center">';
		echo race_link($yr, $gp, '<b>'.$gp.'</b>').'<br>';
		echo '<img src="/images/flag/icon/'.$gp.'.png" width="22" height="14">';
		echo '</td>';
	}
	echo '<th>Score</th></tr>';
	
	echo '</table>';
}

// ÚJ KONSTRUKTŐRI VB (minden autó számít)
function cc($f1db, $yr) {	
	$races_query = mysqli_query($f1db,
		"SELECT no, gp
		FROM f1_gp
		WHERE yr = $yr
		ORDER BY no ASC");
	$races = array();
	while ($row = mysqli_fetch_array($races_query)) {
		$races[$row['no']] = $row['gp'];
	}
	
	$results_query = mysqli_query($f1db,
		"SELECT rnd, status, finish, car_no,
			chassis.cons AS chassis, engine.cons AS engine
		FROM f1_race AS race
		INNER JOIN chassis ON race.chassis = chassis.no
		INNER JOIN engine ON race.engine = engine.no
		WHERE yr = $yr
		AND status > 0");
	$results = array();
	while ($row = mysqli_fetch_array($results_query)) {
		if ($row['status'] == 1) {
			$place = $row['finish'];
		}
		else {
			$place = status($row['status']);
		}
		
		$rnd = $row['rnd'];
		$ch  = $row['chassis'];
		$en  = $row['engine'];
		$car = $row['car_no'];
		$results[$ch][$en][$car][$rnd] = $place;
	}
	
	echo '<table class="results">';
	// Fejlécek
	echo '<tr><th width="30">#</th><th>Constructor</th><th width="20">#</th>';
	foreach ($races AS $gp) {
		echo '<td width="30" align="center">';
		echo race_link($yr, $gp, '<b>'.$gp.'</b>').'<br>';
		echo '<img src="/images/flag/icon/'.$gp.'.png" width="22" height="14">';
		echo '</td>';
	}
	echo '<th>Score</th></tr>';
	
	$tbl = mysqli_query($f1db,
		"SELECT tbl.place, tbl.score, tbl.chassis AS ch_no, tbl.engine AS en_no,
			en.fullname AS engine, en.id AS en_id, 
			ch.country, ch.fullname AS chassis, ch.id AS ch_id
		FROM f1_tbl_cons AS tbl
		INNER JOIN team AS ch
		ON tbl.chassis = ch.no
		INNER JOIN team AS en
		ON tbl.engine = en.no
		WHERE tbl.yr = $yr
		ORDER BY tbl.place = 0, tbl.place ASC, tbl.places DESC, tbl.no ASC");
		

	while ($row = mysqli_fetch_array($tbl)) {
		$ch_no = $row['ch_no'];
		$en_no = $row['en_no'];
		
		$nums = array();
		if (isset($results[$ch_no][$en_no]) && count($results[$ch_no][$en_no])>0) { // Ha nincs eredmény, ne jelezzen hibát
			$cons_res = $results[$ch_no][$en_no];
			ksort($cons_res);
			
			foreach ($cons_res as $car_no => $res) {
				array_push($nums, $car_no);
			}
		}
		$first = isset($nums[0]) ? $nums[0] : '';
		
		$rows = count($nums);
		
		echo '<tr>';
		// Eleje
		if ($row['score'] == 0) {
			$row['place'] = '-';
		}
		echo '<td rowspan="'.$rows.'" class="rnd">'.$row['place'].'</td>';
		if ($row['chassis'] == $row['engine']) {
			$cons = team_link($row['ch_id'], $row['chassis']); // Úgy is mindenki erre kíváncsi
		}
		else {
			$chassis = team_link($row['ch_id'], $row['chassis']);
			$engine  = engine_cons_link($row['en_id'], $row['engine']);
			$cons = $chassis.' - '.$engine;
		}
		echo '<td rowspan="'.$rows.'">'.flag($row['country']).$cons.'</td>';
		
		// Első sor (rajtszám szerint)				
		echo '<td>'.$first.'</td>';
		foreach ($races AS $rnd => $gp) {
			if (isset($cons_res[$first][$rnd])) {
				$place = $cons_res[$first][$rnd];
				echo '<td align="center" class="'.cellclass($place, $yr).'">';
				echo $place;
			}
			else {
				echo '<td>';
			}
			echo '</td>';
		}	
		
		// Vége
		echo '<td rowspan="'.$rows.'" align="center">'.($row['score']+0).'</td>';
		echo '</tr>';
		
		// További sorok
		foreach ($nums as $rw => $num) { if ($rw > 0) { // Nem az első eleme a tömbnek (már volt)
			echo '<tr>';
			echo '<td>'.$num.'</td>';
			foreach ($races AS $rnd => $gp) {
				if (isset($cons_res[$num][$rnd])) {
					$place = $cons_res[$num][$rnd];
					echo '<td align="center" class="'.cellclass($place, $yr).'">';
					echo $place;
				}
				else {
					echo '<td>';
				}
				echo '</td>';
			}
			echo '</tr>';
		}}	
	}
	
	// Fejlécek alul
	echo '<tr><th>#</th><th>Constructor</th><th>#</th>';
	foreach ($races AS $gp) {
		echo '<td align="center">';
		echo race_link($yr, $gp, '<b>'.$gp.'</b>').'<br>';
		echo '<img src="/images/flag/icon/'.$gp.'.png" width="22" height="14">';
		echo '</td>';
	}
	echo '<th>Score</th></tr>';
	
	echo '</table>';
}

// Aktuális csapatok pilótákkal
function active_teams($f1db) {
	// Csapatok
	$teams = mysqli_query($f1db,
		"SELECT active.no, team.id, team.fullname, team.country, active.font_color, active.bg_color, active.border_color
		FROM f1_active_team AS active
		INNER JOIN team
		ON active.no = team.no
		ORDER BY ordering ASC");
	$active = array();
	while ($row = mysqli_fetch_array($teams)) {
		$active[$row['no']]['id']      = $row['id'];
		$active[$row['no']]['name']    = $row['fullname'];
		$active[$row['no']]['country'] = $row['country'];
		$active[$row['no']]['font']    = $row['font_color'];
		$active[$row['no']]['bg']      = $row['bg_color'];
		$active[$row['no']]['border']  = $row['border_color'];
	}

	// Pilóták
	$drivers = mysqli_query($f1db,
		"SELECT active.team, active.car_no, driver.country, driver.id, driver.first, driver.de, driver.last, driver.sr, active.status
		FROM f1_active_driver AS active
		INNER JOIN driver
		ON active.no = driver.no
		ORDER BY ordering ASC");
	while ($row = mysqli_fetch_array($drivers)) {
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		$active[$row['team']][$row['status']][$row['id']]['name']    = $name;
		$active[$row['team']][$row['status']][$row['id']]['country'] = $row['country'];
		$active[$row['team']][$row['status']][$row['id']]['car_no'] = $row['car_no'];
	}

	// Kiírás
	echo '<center><table class="results">';
	echo '<tr>
		<th width="53">Team</th><th width="180">Racing drivers</th><th width="180">Test drivers</th>
		<th width="53">Team</th><th width="180">Racing drivers</th><th width="180">Test drivers</th>
	</tr>';
	$col = 1;
	foreach ($active as $no => $team) {
		if ($col % 2 == 1) {
			echo '<tr>';
		}
		echo '<td style="border:1px solid #'.$team['border'].'; color:#'.$team['font'].'; background-color:#'.$team['bg'].'; height:49px;">'; // Lehető legvékonyabb
			//echo flag($team['country']).team_link($id, $team['name']);
			//echo flag($team['country']).'<a href="/team/'.$team['id'].'" style="color:#'.$team['font'].'; font-weight:bold;">'.$team['name'].'</a>';
			// A link így nem a legjobb
			echo '<a href="/team/'.$team['id'].'">';
			echo '<img src="/images/team/icon/'.$team['id'].'.png" style="width:50px; height:50px; position:relative; top:2px; left:2px;">';
			echo '</a>';
		echo '</td>';
		echo '<td>';
		foreach ($team['R'] as $id => $driver) {
			echo flag($driver['country']).'<span style="display: inline-block; width:15px; font-weight:bold; text-align:right; padding-right:2px;">'.$driver['car_no'].'</span>'.driver_link($id, $driver['name']).'<br>';
		}
		echo '</td>';
		echo '<td>';
			if (isset($team['T'])) { // Mertvoltmárrápélda (a másikra sztem nem) (de, arra is, pl. év elején [#előrelátás] )
				foreach ($team['T'] as $id => $driver) {
					echo flag($driver['country']).'<span style="display: inline-block; width:15px; font-weight:bold; text-align:right; padding-right:2px;">'.$driver['car_no'].'</span>'.driver_link($id, $driver['name']).'<br>';
				}
			}
		echo '</td>';
		if ($col % 2 == 0) {
			echo '</tr>';
		}
		$col++;
}
if ($col % 2 == 0) {
			echo '<td colspan="3"></td>';
		}
echo '</table></center>';
}

// Tesztnap
function table_test($no) {
	global $f1db;
	
	$quali = mysqli_query($f1db,
		"SELECT p.tme, p.place, p.laps,
			driver.first, driver.de, driver.last, driver.sr, driver.id AS driverid, driver.country, race.car_no,
			team.id AS teamid, team.fullname
		FROM f1_test_results AS p
		INNER JOIN f1_test_entrants AS race
			ON (race.no = p.entr_no)
				INNER JOIN driver
			ON (race.driver = driver.no)
		INNER JOIN team
			ON (race.team = team.no)
		WHERE p.session = $no
		ORDER BY place ASC");
		
	$qtime = array();

	while ($row = mysqli_fetch_array($quali)) {	
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
			$driverlink = driver_link($row['driverid'], $name);
		$id = $row['driverid'];
		if ($row['tme']!=0) {
			$time = $row['tme'];
		}
		else {
			$time = 'no time';
		}
		$team = $row['fullname'];
			$teamlink = team_link($row['teamid'], $team);
		$car = $row['car_no'];
		
		$qtime[$id]['place'] = $row['place'];
		$qtime[$id]['name'] = flag($row['country']).$driverlink;
		$qtime[$id]['team'] = $teamlink;
		$qtime[$id]['car'] = $car;
		$qtime[$id]['time'] = $time;
		$qtime[$id]['laps'] = $row['laps'];
	}
		
	echo '<table class="results">';
	echo '<th width="30">#</th>' .
		'<th width="40">Car #</th>' .
		'<th width="150">Driver</th>' .
		'<th width="100">Team</th>' .
		'<th width="50">Time</th>'.
		'<th width="30">Gap</th>'.
		'<th width="50">Laps</th>';

	foreach ($qtime as $id => $driver) {
		echo '<tr>';
		echo '<td align="center">'.$driver['place'].'</td>';
		echo '<td align="center">'.$driver['car'].'</td>';
		echo '<td>'.$driver['name'].'</td>';
		echo '<td>'.$driver['team'].'</td>';
		echo '<td>'.racetime($driver['time']).'</td>';
		if ($driver['place'] == 1) {
			$besttime = $driver['time'];
			$gap = '---';
		}
		else if ($driver['time'] > 0) {
			$gap = '+'.racetime($driver['time'] - $besttime);
		}
		else {
			$gap = '';
		}
		echo '<td>'.$gap.'</td>';
		echo '<td>'.$driver['laps'].'</td>';
		echo '</tr>';
	}

	echo '</table>';
}

// Bajnokság
function drivers_championship($year) {
	global $f1db;
	
	// Versenyek	
	$races_query = mysqli_query($f1db,
		"SELECT no, gp
		FROM f1_gp
		WHERE yr = $year
		ORDER BY no ASC");
	$races = array();
	while ($row = mysqli_fetch_array($races_query)) {
		$races[$row['no']] = $row['gp'];
	}
	
	// Eredmények
	$results_query = mysqli_query($f1db,
		"SELECT rnd, status, finish, car_no,
			driver.id AS driverid
		FROM f1_race AS race
		INNER JOIN driver ON race.driver = driver.no
		WHERE yr = $year
		AND status > 0");
	$results = array();
	while ($row = mysqli_fetch_array($results_query)) {
		if ($row['status'] == 1) {
			$place = $row['finish'];
		}
		else {
			$place = status($row['status']);
		}
		
		$rnd = $row['rnd'];
		$drvr  = $row['driverid'];
		$results[$drvr][$rnd] = $place;
	}
	
	echo '<table class="results">';
	
	// Fejlécek
	echo '<tr><th width="30">#</th><th>Driver</th>';
	foreach ($races AS $gp) {
		echo '<td width="30" align="center">';
		echo race_link($year, $gp, '<b>'.$gp.'</b>').'<br>';
		echo '<img src="/images/flag/icon/'.$gp.'.png" width="22" height="14">';
		echo '</td>';
	}
	echo '<th>Score</th></tr>';
	
	// Rangsor
	$tbl = mysqli_query($f1db,
		"SELECT tbl.place, tbl.score, tbl.dsq,
			driver.country, driver.id AS driverid, driver.first, driver.de, driver.last, driver.sr 
		FROM f1_tbl AS tbl
		INNER JOIN driver
		ON tbl.driver = driver.no
		WHERE tbl.yr = $year
		ORDER BY tbl.place = 0, tbl.place, tbl.no ASC");

	while ($row = mysqli_fetch_array($tbl)) {
		$driver_id = $row['driverid'];
				
		echo '<tr>';
		if ($row['dsq'] == 0) {
			$place = $row['place'];
		}
		else {
			$place = 'DSQ';
		}
		echo '<td class="rnd">'.$place.'</td>';
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		$link = driver_link($driver_id, $name);
		echo '<td>'.flag($row['country']).$link.'</td>';
		
		// Eredmények
		foreach ($races AS $rnd => $gp) {
			if (isset($results[$driver_id][$rnd])) {
				$place = $results[$driver_id][$rnd];
				echo '<td align="center" class="'.cellclass($place, $year).'">';
				echo $place;
			}
			else {
				echo '<td>';
			}
			echo '</td>';
		}	
		
		// Vége
		echo '<td align="center">'.($row['score']+0).'</td>';
		echo '</tr>';	
	}
	
	// Fejlécek alul
	echo '<tr><th>#</th><th>Driver</th>';
	foreach ($races AS $gp) {
		echo '<td align="center">';
		echo race_link($year, $gp, '<b>'.$gp.'</b>').'<br>';
		echo '<img src="/images/flag/icon/'.$gp.'.png" width="22" height="14">';
		echo '</td>';
	}
	echo '<th>Score</th></tr>';
	
	echo '</table>';
}
?>