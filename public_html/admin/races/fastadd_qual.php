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
	"place",
	"driver",
	"q1",
	"q2",
	"q3",
	"laps"
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
			<option value="place">Place</option>
			<option value="driver">Driver</option>
			<option value="q1">Q1</option>
			<option value="q2">Q2</option>
			<option value="q3">Q3</option>
			<option value="laps">Laps</option>
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
		"place" => -1,
		"driver" => -1,
		"q1"   => -1,
		"q2"   => -1,
		"q3"  => -1,
		"laps"  => -1
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
			if (isset($_POST['col'.$val][$i])) { // Nem biztos, hogy van pl. laps oszlop
				$write = $_POST['col'.$val][$i];
			}
			else {
				$write = '';
			}
			echo '<td><input type="text" name="'.$label.'[]" value="'.$write.'" size="12"></td>';
		}
		echo '</tr>';
	}
	echo '</table>';
	echo '<input type="submit" name="phase3" value="Next">';
	echo '</form>';
}

// 3. SOROK - KIHAGYHATÓ
if (isset($_POST['phase3'])) {
	$row_count = count($_POST['place']);
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
		if (!isset($_POST['place'][$i])) {
			continue;
		}
		echo '<tr>';
		foreach($columns as $label) {
			echo '<td><input type="text" name="'.$label.'[]" value="'.$_POST[$label][$i].'" size="12"></td>';
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
	$active_drivers = mysqli_query($f1db, // Pilóta száma helyett az entr_no oszlop lesz hozzárendelve
		"SELECT race.no, driver.first, driver.de, driver.last, driver.sr
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
	for ($i = 0; $i < count($_POST['place']); $i++) {
		if (!isset($_POST['place'][$i])) {
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

				//driver_dropdown('driver[]', $dropdown_no);
				// Helyette csak a versenyre nevezők közül válogat
				
				$entrants =
					"SELECT race.no, driver.first, driver.de, driver.last, driver.sr
					FROM f1_race AS race
					INNER JOIN driver
					ON race.driver = driver.no
					WHERE rnd = $race_no
					ORDER BY driver.last ASC";
				
				custom_dropdown('entr_no[]', $entrants, 'no', 'name_2 first,de,last,sr', $dropdown_no);
				
				echo $_POST[$label][$i].'</td>';
			}
		}
		echo '</tr>';
	}
	echo '</table>';
	
	// DROPDOWN KELL HOZZÁ
	echo '<p>Race: ';
	race_dropdown('race', $_GET['race']);
	echo ' <input type="checkbox" name="make_visible" checked> Visible</p>';
	
	echo '<input type="submit" name="phase5" value="Next">';
	echo '</form>';
}

// 5. KÉSZ
if (isset($_POST['phase5'])) {
	foreach ($_POST['entr_no'] as $key => $entr_no) {
		// Place, dnq, dsq
		$dnq = 0;
		$dsq = 0;
		if (is_numeric($_POST['place'][$key])) {
			$place = $_POST['place'][$key];
		}
		else {
			$place = $key + 1; // Valószínűleg annyi, de úgy sem érdekes
			if (strtolower($_POST['place'][$key]) == 'dnq') {
				$dnq = 1;
			}
			if (strtolower($_POST['place'][$key]) == 'dsq') {
				$dsq = 1;
			}
		}
		
		// Laps
		if (is_numeric($_POST['laps'][$key])) {
			$laps = $_POST['laps'][$key];
		}
		else {
			$laps = 0;
		}
		
		/*// Q1
		switch ($_POST['q1'][$key]) {
			case '':
				$q1 = 0;
				break;
			case 'no time':
				$q2 = -1;
				break;
			default:
				$q1 = timetodec($_POST['q1'][$key]);
		}
		
		// Q2
		switch ($_POST['q2'][$key]) {
			case '':
				$q2 = 0;
				break;
			case 'no time':
				$q2 = -1;
				break;
			default:
				$q2 = timetodec($_POST['q2'][$key]);
		}
		
		// Q3
		switch ($_POST['q3'][$key]) {
			case '':
				$q3 = 0;
				break;
			case 'no time':
				$q3 = -1;
				break;
			default:
				$q3 = timetodec($_POST['q3'][$key]);
		}*/
		
		// Q1
		if ($_POST['q1'][$key] == '') {
			$q1 = 0;
		}
		else if (strtolower($_POST['q1'][$key]) == 'no time') {
			$q1 = -1;
		}
		else {
			$q1 = timetodec($_POST['q1'][$key]);
		}
		
		// Q2
		if ($_POST['q2'][$key] == '') {
			$q2 = 0;
		}
		else if (strtolower($_POST['q2'][$key]) == 'no time') {
			$q2 = -1;
		}
		else {
			$q2 = timetodec($_POST['q2'][$key]);
		}
		
		// Q3
		if ($_POST['q3'][$key] == '') {
			$q3 = 0;
		}
		else if (strtolower($_POST['q3'][$key]) == 'no time') {
			$q3 = -1;
		}
		else {
			$q3 = timetodec($_POST['q3'][$key]);
		}
		
		//echo $entr_no.' '.$place.' '.$q1.' '.$q2.' '.$q3.' '.$laps.' '.$dnq.' '.$dsq.'<br>';
		$add = mysqli_query($f1db,
			"INSERT INTO f1_q(entr_no, place, q1, q2, q3, laps, dnq, dsq)
			VALUES($entr_no, $place, $q1, $q2, $q3, $laps, $dnq, $dsq)"
		);
		if (!$add) {
			mysqli_query($f1db,
				"UPDATE f1_q
				SET place = $place,
				q1 = $q1,
				q2 = $q2,
				q3 = $q3,
				laps = $laps,
				dnq = $dnq,
				dsq = $dsq
				WHERE entr_no = $entr_no"
			);
		}
	}
	if (isset($_POST['make_visible'])) {
		$vis = mysqli_query($f1db,
			"UPDATE f1_gp_schedule
			SET shown = 1
			WHERE rnd = $race_no
			AND type = 'Q'"
		);
		if ($vis) {
			echo 'Qualifying set visible<br>';
		}
		else {
			echo 'Qualifying couldn\'t set visible<br>';
		}
	}
	echo '<p><a href="/admin/race/'.$race_no.'/results/qualifying">View</a></p>';
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