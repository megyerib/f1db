<?php
set_time_limit(0);
require_once('included/head_admin.php');

if (!isset($_SESSION['done'])) {

	echo '<form method="post">
	<center><p><br>You\'re about to refresh the constructor\'s<br>score standings of the
	<input type="number" min="1991" name="yr" style="width:70px;" value="'.actual.'">Season.</p>
	<p>Are you sure?</p>
	<input type="submit" name="ok" value="Yup">
	<input type="submit" name="no" value="Not now">
	</form></p></center>';

	if (isset($_POST['no'])) {
		header('Location: /admin/');
	}
	if (isset($_POST['ok'])) {
	if (is_numeric($_POST['yr']) && $_POST['yr'] >= 1991) {
		$yr = $_POST['yr'];
	}
	else {
		$yr = actual;
	}

	// Kiürítés
	mysqli_query($f1db,
		"DELETE FROM f1_tbl_cons
		WHERE yr = $yr");

	$allresults = array();
	$places = array (
		1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0,
		11 => 0, 12 => 0, 13 => 0, 14 => 0, 15 => 0, 16 => 0, 17 => 0, 18 => 0, 19 => 0, 20 => 0,
		21 => 0, 22 => 0, 23 => 0, 24 => 0,
		'NC' => 0,'ret' => 0,'DNS' => 0,'DNQ' => 0,'DNPQ' => 0,'DSQ' => 0);
		
	// 1. kör (Pontok)
	$select = mysqli_query($f1db,
		"SELECT yr, chassis.cons AS chassis, engine.cons AS engine, SUM(score) AS score
		FROM f1_race AS race
		INNER JOIN chassis ON race.chassis = chassis.no
		INNER JOIN engine  ON race.engine  =  engine.no
		WHERE race.yr = $yr
		GROUP BY chassis.cons, engine.cons");
	
		// Volt már verseny
		if (mysqli_num_rows($select) > 0) {
			while ($row = mysqli_fetch_array($select)) {
				$year   = $row['yr'];
				$chassis= $row['chassis'];
				$engine = $row['engine'];
				$score  = $row['score'];
				
				mysqli_query($f1db, // A pontokat beírja a táblázatba
					"INSERT INTO f1_tbl_cons(yr, chassis, engine, score)
					VALUES($year, $chassis, $engine, $score)");
					
				$allresults[$year][$chassis][$engine] = $places; // Hozzáadja az üres places tömböt
			}

			// 2. Kör, helyezések alapján rendezés
			// Célbaérések
			$finishes = mysqli_query($f1db,
				"SELECT race.yr, race.status, count(race.finish) as count, race.finish,
					chassis.cons AS chassis, engine.cons AS engine
				FROM f1_race AS race
				INNER JOIN chassis ON race.chassis = chassis.no
				INNER JOIN engine  ON race.engine  =  engine.no
				WHERE race.status = 1
				AND race.yr = $yr
				GROUP BY race.finish, chassis.cons, engine.cons");
				
			while ($fnshs = mysqli_fetch_array($finishes)) {
				$year   = $fnshs['yr'];
				$chassis= $fnshs['chassis'];
				$engine = $fnshs['engine'];
				$status = $fnshs['finish'];
				$sum    = $fnshs['count'];
				
				$allresults[$year][$chassis][$engine][$status] = $sum; // A tömbbe írja a helyezéseket
			}
			// Egyéb
			$nonfinishes = mysqli_query($f1db,
				"SELECT race.yr, race.status, count(race.status) as count,
					chassis.cons AS chassis, engine.cons AS engine
				FROM f1_race AS race
				INNER JOIN chassis ON race.chassis = chassis.no
				INNER JOIN engine  ON race.engine  =  engine.no
				WHERE race.status > 1
				AND race.yr = $yr
				GROUP BY race.status, chassis.cons, engine.cons");
				
			while ($fnshs = mysqli_fetch_array($nonfinishes)) {
				$year   = $fnshs['yr'];
				$chassis= $fnshs['chassis'];
				$engine = $fnshs['engine'];
				$status = status($fnshs['status']);
				$sum    = $fnshs['count'];
				
				$allresults[$year][$chassis][$engine][$status] = $sum; // A tömbbe írja a helyezéseket
			}
			// Beírás
			foreach ($allresults as $yr => $year) { // Tömb: 3 sorral feljebb Évszám => tömb adatai 

				foreach ($year as $ch => $engine) { // Chassis no =>többi
					foreach ($engine as $en => $stat) { // Engine no =>többi
					
					// a számsort bővíti
					$counter = '1';
					
					foreach ($stat as $status) {
						if ($status > 9) {
							$status = 9;
						}
						
						$counter = $counter.$status;
					}
					
					mysqli_query($f1db,
						"UPDATE f1_tbl_cons
						SET places = $counter
						WHERE yr = $yr
						AND chassis = $ch
						AND engine = $en");
					}
				}
			}
			// 3. kör: helyezések
			$order = mysqli_query($f1db,
				"SELECT *
				FROM f1_tbl_cons
				WHERE yr = $yr
				ORDER BY score DESC, places DESC");

			$prevscore  = 0;
			$prevplaces = 0;
			$prevyear   = 0;
			$place      = 0;	

			while ($row = mysqli_fetch_array($order)) {
				$score  = $row['score'];
				$places = $row['places'];
				
				$year   = $row['yr'];
				$chassis= $row['chassis'];
				$engine = $row['engine'];
				
				if ($score != $prevscore ||
					$places != $prevplaces) {
				
					$place++;
				}
				if ($year != $prevyear) {
					$place = 1;
				}
					
				mysqli_query($f1db,
					"UPDATE f1_tbl_cons
					SET place = $place
					WHERE yr = $year
					AND chassis = $chassis
					AND engine = $engine");
				
				$prevyear  = $year;
				$prevscore  = $score;
				$prevplaces = $places;
			}
		}
		// Nem volt még verseny
		else {
			$active = mysqli_query($f1db,
				"SELECT ordering, ch.cons AS ch_cons, en.cons AS en_cons
				FROM f1_active_team AS active
				INNER JOIN chassis AS ch
				ON active.chassis = ch.no
				INNER JOIN engine AS en
				ON active.engine = en.no
				ORDER BY active.ordering ASC"
			);
			
			while ($row = mysqli_fetch_array($active)) {
				$place   = $row['ordering'];
				$yr      = actual;
				$chassis = $row['ch_cons'];
				$engine  = $row['en_cons'];
				
				mysqli_query($f1db,
					"INSERT INTO f1_tbl_cons(place, yr, chassis, engine, score)
					VALUES($place, $yr, $chassis, $engine, 0)"
				);
			}
		}
		$_SESSION['done'] = '';
		header('Location: '.$_SERVER['PHP_SELF']);
	}
}
else {
	echo '<center><p>Done!</br>';
	echo '<a href="'.$_SERVER['PHP_SELF'].'">Do it again!</a></p><p><a href="/admin">Back</a></p></center>';
	unset($_SESSION['done']);
}
require_once('included/foot_admin.php');
?>