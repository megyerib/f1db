<?php
set_time_limit(0);
require_once('included/head_admin.php');

if (!isset($_SESSION['done'])) {
	echo '<form method="post">
		<center><p><br>You\'re about to refresh the driver\'s<br>score standings of the
		<input type="number" min="1991" name="yr" style="width:70px;" value="'.actual.'">Season.</p>
		<p>Are you sure?</p>
		<input type="submit" name="ok" value="Yup">
		<input type="submit" name="no" value="Not now">
		</form></p></center>';

	if (isset($_POST['no'])) {
		header('Location: /');
	}
	if (isset($_POST['ok'])) {
		if ($_POST['yr'] >= 1950) {
			$yr = $_POST['yr'];
		}
		else {
			$yr = actual;
		}

		// Kiürítés
		mysqli_query($f1db,
			"DELETE FROM f1_tbl
			WHERE yr = $yr");

		$allresults = array();
		$places = array (
			1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0,
			11 => 0, 12 => 0, 13 => 0, 14 => 0, 15 => 0, 16 => 0, 17 => 0, 18 => 0, 19 => 0, 20 => 0,
			21 => 0, 22 => 0, 23 => 0, 24 => 0,
			'NC' => 0,'ret' => 0,'DNS' => 0,'DNQ' => 0,'DNPQ' => 0,'DSQ' => 0);
			
		// 1. kör (Pontok)
		$select = mysqli_query($f1db,
			"SELECT yr, driver, SUM(score) AS score
			FROM f1_race
			WHERE yr = $yr
			AND status > 0
			GROUP BY driver"
		);
		
		if (mysqli_num_rows($select) > 0) { // Volt már verseny
			while ($row = mysqli_fetch_array($select)) {
				$year   = $row['yr'];
				$driver = $row['driver'];
				$score  = $row['score'];
				
				mysqli_query($f1db,
					"INSERT INTO f1_tbl(yr, driver, score)
					VALUES('$year', '$driver', '$score')");
					
				$allresults[$year][$driver] = $places;
			}

			// 2. Kör, helyezések alapján rendezés
			// Célbaérések
			$finishes = mysqli_query($f1db,
				"SELECT yr, driver, finish, count(finish) as count
				FROM f1_race
				WHERE status = 1
				AND yr = $yr
				GROUP BY finish, driver");
				
			while ($fnshs = mysqli_fetch_array($finishes)) {
				$year   = $fnshs['yr'];
				$driver = $fnshs['driver'];
				$status = $fnshs['finish'];
				$sum    = $fnshs['count'];
				
				$allresults[$year][$driver][$status] = $sum;
			}
			// Egyéb
			$nonfinishes = mysqli_query($f1db,
				"SELECT yr, driver, status, count(status) as count
				FROM f1_race
				WHERE status > 1
				AND yr = $yr
				GROUP BY status, driver");
				
			while ($fnshs = mysqli_fetch_array($nonfinishes)) {
				$year   = $fnshs['yr'];
				$driver = $fnshs['driver'];
				$status = status($fnshs['status']);
				$sum    = $fnshs['count'];
				
				$allresults[$year][$driver][$status] = $sum;
			}
			// Beírás
			foreach ($allresults as $yr => $year) {

				foreach ($year as $drvr => $driver) {
				
					// a számsort bővíti
					$counter = '1';
					
					foreach ($driver as $status) {
						if ($status > 9) {
							$status = 9;
						}
						
						$counter = $counter.$status;
					}
					
					mysqli_query($f1db,
						"UPDATE f1_tbl
						SET places = $counter
						WHERE yr = $yr
						AND driver = $drvr");
				}
			}
			// 3. kör: helyezések

			$order = mysqli_query($f1db,
				"SELECT *
				FROM f1_tbl
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
				$driver = $row['driver'];
				
				if ($score != $prevscore ||
					$places != $prevplaces) {
				
					$place++;
				}
				if ($year != $prevyear) {
					$place = 1;
				}
					
				mysqli_query($f1db,
					"UPDATE f1_tbl
					SET place = $place
					WHERE yr = $year
					AND driver = $driver"
				);
				
				$prevyear  = $year;
				$prevscore  = $score;
				$prevplaces = $places;
			}
		}
		// Nem volt még verseny
		else {
			$active = mysqli_query($f1db,
				"SELECT no, ordering
				FROM f1_active_driver
				WHERE status = 'R'
				ORDER BY ordering ASC"
			);
			while ($row = mysqli_fetch_array($active)) {
				$year = actual;
				$driver = $row['no'];
				$place = $row['ordering'];
				
				mysqli_query($f1db,
					"INSERT INTO f1_tbl(place, yr, driver)
					VALUES($place, $yr, $driver)"
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