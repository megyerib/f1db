<?php
include($_SERVER['DOCUMENT_ROOT'].'/admin/included/head_admin.php');

$race_no = $_GET['race'];

// Cím
$title = mysqli_query($f1db,
	"SELECT f1_gp.yr, country.name
	FROM f1_gp
	INNER JOIN country
	ON f1_gp.gp = country.gp
	WHERE f1_gp.no = $race_no
	LIMIT 1"
);
$title = mysqli_fetch_array($title);
$title = $title['yr'].' '.$title['name'];
echo '<h1 class="title">'.$title.' GP - Add race results</h1>';

$columns = array(
	"finish",
	"driver",
	"laps",
	"time",
	"start",
	"score"
);
// 0. Bekérés
if (empty($_POST)) {
	echo '<form method="post">';
	echo '<textarea name="raw_text" style="width:900px; height:400px;"></textarea>';
	echo '<br><input type="submit" name="phase1" value="Next">';
}

// 1. ELŐFELDOLGOZÁS
if (isset($_POST['phase1'])) {
	function col_header($name) {
		return '<select name="'.$name.'">
			<option value="delete"></option>
			<option value="finish">Finish</option>
			<option value="driver">Driver</option>
			<option value="laps">Laps</option>
			<option value="time">Time</option>
			<option value="start">Start</option>
			<option value="score">Score</option>
		</select>';
	}

	$raw1 = explode("\n", $_POST['raw_text']);
	$raw2 = array();
	$i = 1;
	$max_col = 0;
	foreach($raw1 as $line) {
		if ($line != '') {
			$raw2[$i] = explode("\t", $line);
			if (count($raw2[$i]) > $max_col) {
				$max_col = count($raw2[$i]);
			}
		}
		$i++;
	}

	echo '<form method="post">';
	echo '<table border="1">';
	echo '<tr>';
	for ($i = 0; $i < $max_col; $i++) {
		echo '<td>'.col_header('colset[]').'</td>';
	}
	echo '</tr>';
	foreach($raw2 as $line) {
		echo '<tr>';
		for($i = 0; $i < $max_col; $i++) {
			if (isset($line[$i])) {
				$val = $line[$i] != ' ' ? $line[$i] : ''; // formula1.com-os táblázatok miatt
			}
			else {
				$val='';
			}
			echo '<td><input type="text" name="col'.$i.'[]" value="'.$val.'" size="10"></td>';
		}
		echo '</tr>';
	}
	echo '</table>';
	echo '<input type="hidden" name="cols" value="'.$max_col.'">';
	echo '<input type="submit" name="phase2" value="Next">';
	echo '</form>';
}

// 2. OSZLOPOK
if (isset($_POST['phase2'])) {
	$to_process = array(
		"finish" => -1,
		"driver" => -1,
		"time"   => -1,
		"laps"   => -1,
		"start"  => -1,
		"score"  => -1
	);
	
	$i = 0;
	foreach ($_POST['colset'] as $col_no) {
		if ($col_no != 'delete') {
			$to_process[$col_no] = $i;
		}
		$i++;
	}
	
	echo '<form method="post">';
	echo '<table class="race_input">';
	echo '<th></th>';
	foreach($to_process as $label => $val) {
		echo '<th>'.$label.'</th>';
	}
	for ($i = 0; $i < count($_POST['col0']); $i++) {
		echo '<tr>';
		echo '<td><input type="checkbox" name="delete_row[]" value="'.$i.'"></td>';
		foreach($to_process as $label => $val) {
			//echo '<td><input type="text" name="'.$label.'[]" value="'.$_POST['col'.$val][$i].'"></td>';
			if (isset($_POST['col'.$val][$i])) { // Nem biztos, hogy van pl. laps oszlop
				$write = $_POST['col'.$val][$i];
			}
			else {
				$write = '';
			}
			echo '<td><input type="text" name="'.$label.'[]" value="'.$write.'" size="14"></td>';
		}
		echo '</tr>';
	}
	echo '</table>';
	echo '<input type="submit" name="phase3" value="Next">';
	echo '</form>';
}

// 3. SOROK - KIHAGYHATÓ
if (isset($_POST['phase3'])) {
	$row_count = count($_POST['finish']);
	if (isset($_POST['delete_row'])) {
		foreach ($_POST['delete_row'] as $no) {
			foreach($columns as $label) {
				unset($_POST[$label][$no]);
			}
		}
	}
	
	echo '<form method="post">';
	echo '<table class="race_input">';
	foreach($columns as $label) {
		echo '<th>'.$label.'</th>';
	}
	for ($i = 0; $i < $row_count; $i++) {
		if (!isset($_POST['finish'][$i])) {
			continue;
		}
		echo '<tr>';
		foreach($columns as $label) {
			echo '<td><input type="text" name="'.$label.'[]" value="'.$_POST[$label][$i].'" size="14"></td>';
		}
		echo '</tr>';
	}
	echo '</table>';
	echo '<input type="submit" name="phase4" value="Next">';
	echo '</form>';
}

// 4. DRIVER
if (isset($_POST['phase4'])) {
	$drivers = array();
	
	// A verseny saját entrants listáját nézi végig
	$active_drivers = mysqli_query($f1db,
		"SELECT driver.no, driver.first, driver.de, driver.last, driver.sr
		FROM f1_race AS race
		INNER JOIN driver
		ON race.driver = driver.no
		WHERE rnd = $race_no
		ORDER BY driver.last ASC"
	);
	
	while ($row = mysqli_fetch_array($active_drivers)) {
		$drivers[$row['no']] = name($row['first'], $row['de'], $row['last'], $row['sr']);
	}
	
	echo '<form method="post">';
	echo '<table class="race_input">';
	foreach($columns as $label) {
		echo '<th>'.$label.'</th>';
	}
	for ($i = 0; $i < count($_POST['finish']); $i++) {
		if (!isset($_POST['finish'][$i])) {
			continue;
		}
		echo '<tr>';
		foreach($columns as $label) {
			if ($label != 'driver') { // Többi oszlop
				$cell_val = $_POST[$label][$i];
				echo '<td><input type="text" name="'.$label.'[]" value="'.$_POST[$label][$i].'" size="8"></td>';
			}
			else { // Driver oszlop - kiválasztó
				$dropdown_no = 0;
				foreach ($drivers as $driver_no => $driver) {
					if ($_POST[$label][$i] == $driver) {
						$dropdown_no = $driver_no;
						break;
					}
				}
				if (!$dropdown_no) { // Ha nincs pontos egyezés, hasonlót keres
				// Karakterkódolási hibák miatt, szar, de evvan, így kényelmesebb
					foreach ($drivers as $driver_no => $driver) {
						similar_text($_POST[$label][$i], $driver, $similar);
						if ($similar >= 80) {
							$dropdown_no = $driver_no;
							break;
						}
					}
				}
				echo '<td>';

				driver_dropdown('driver[]', $dropdown_no);
				
				echo $_POST[$label][$i].'</td>';
			}
		}
		echo '</tr>';
	}
	echo '</table>';
	
	// DROPDOWN KELL HOZZÁ
	echo '<p style="font-weight:bold; color:red;">SET ENTRANTS FIRST!</p>';
	echo '<p>Race: ';
	race_dropdown('race', $_GET['race']);
	echo ' <input type="checkbox" name="make_visible" checked> Visible</p>';
	
	echo '<input type="submit" name="phase5" value="Next">';
	echo '</form>';
}

// 5. KÉSZ
if (isset($_POST['phase5'])) {
	// Az egész a race_race.php-ból van beemelve
	$count = count($_POST['finish']);
	
	$rnd = $_POST['race'];
	
	$err_no = 0;
	
	for ($i = 0; $i < $count; $i++) {
		$start  = is_numeric($_POST['start'][$i]) ? $_POST['start'][$i] : 0;
		$finish = $_POST['finish'][$i];
		$laps   = $_POST['laps'][$i];
		$score  = $_POST['score'][$i] != '' ? $_POST['score'][$i] : 0;
		
		// +
		$driver = $_POST['driver'][$i];
		
		// Státusz
		if (is_numeric($finish)) {
			$status = 1;
		}
		else {
			$status = status2num($finish);
			$finish = $i + 1;
		}
					
		// Idő mező vizsgálata (megérne egy külön függvényt?)
		$timenote = $_POST['time'][$i];
		
		if ($timenote != '') {
		$is_first = preg_match('/^[0-9.:]+$/', $timenote);
		$is_gap   = preg_match('/^[0-9.:+]+$/', $timenote);
		$chck = $is_first.$is_gap;
		
		if ($chck == '11') {
			$time = timetodec($timenote);
			$note = '';
			$firsttime = $time;
		}
		else if ($i == 0) { // Az első helyezettnél nem jó az érték
			$i = $count;
			$die_query = true;
			echo '<div class="alert">Incorrect datas!</div>';
		}

		if ($chck == '01') {
			$gap = timetodec(substr($timenote, 1));
			$time = $firsttime + $gap;
			$note = '';
		}
		if ($chck == '00') {
			$time = 0;
			$note = $timenote;
		}
		} else {
			$time = 0;
			$note = '';
		}
					
		if (!isset($die_query)) {
			$query =mysqli_query($f1db,
				"UPDATE f1_race
				SET start = $start,
				status = $status,
				finish = $finish,
				laps = $laps,
				score = $score,
				time = $time,
				note = '$note'
				WHERE driver = $driver AND rnd = $rnd");
		}
		else {
			break;
		}
		
		if (!$query) {
			echo 'ERROR in row '.$i.'<br>';
			$err_no = 0;
		}
	}
	if (!$err_no) {
		echo 'RND '.$rnd.' DONE!<br>';
	}
	if (isset($_POST['make_visible'])) {
		$vis = mysqli_query($f1db,
			"UPDATE f1_gp_schedule
			SET shown = 1
			WHERE rnd = $rnd
			AND type = 'R'"
		);
		if ($vis) {
			echo 'Race set visible<br>';
		}
		else {
			echo 'Race didn\'t set visible<br>';
		}
	}
	echo '<p><a href="/admin/race/'.$race_no.'/results/race">View</a></p>';
}

include($_SERVER['DOCUMENT_ROOT'].'/admin/included/foot_admin.php');

/*
TODO:
* Pontfrissítés
* Felhasználóbarátabb
* Vissza???
* Többi event
* Univerzális

*/

?>