<?php
/*
* Hatalmas gány az egész, az elsők közt írd újra az egészet
* Új rendszer az egész adminhoz (+js)

  Bővíthető:
  * Pre-qualifying
  * Race shared
*/
require_once($_SERVER['DOCUMENT_ROOT'].'/admin/included/head_admin.php');

$mode = $_GET['mode'];

/* VERSENYADATOK
   Ha meg van adva egy verseny száma, betölti a szükséges
   adatokat / függvényeket
   
   Van értelme külön fájlba kiszedni? */
   
if (isset($_GET['race'])) {
	$race_no = $_GET['race'];
	
	// Versenyadatok
	$race = mysqli_query($f1db,
			"SELECT gp.yr, country.name, gp.gp
			FROM f1_gp AS gp
			INNER JOIN country
			ON gp.gp = country.gp
			WHERE gp.no = $race_no");
	$race = mysqli_fetch_array($race);
	$race_yr = $race['yr'];
	$race_gp = $race['gp'];
	$race_name = $race['name'];
	
	// Nevezők dropdown
	function entrant_dropdown($slctd, $race, $f1db) {
		$entrants = mysqli_query($f1db,
			"SELECT race.no, LEFT(driver.first, 1) AS first, driver.de, driver.last, driver.sr
			FROM f1_race AS race
			INNER JOIN driver
			ON race.driver = driver.no
			WHERE rnd = $race
			ORDER BY driver.last ASC");
		echo '<select>';
		echo '<option value="0">---</option>';
		while ($row = mysqli_fetch_array($entrants)) {
			$row['de'] == '' ? $de = '' : $de = $row['de'].' ';
			$row['sr'] == '' ? $sr = '' : $de = ' '.$row['sr'];
			$name = $row['first'].' '.$de.$row['last'].$sr;
			
			$slctd != $row['no'] ? $selected == '' : $selected = ' selected';
			
			echo '<option value="'.$row['no'].'"'.$selected.'>'.$name.'</option>';
		}
		echo '</select>';
	}
}	
	switch ($mode) {
		case 'main': // Main results page
			$load = 'race_results_main.php';
			break;
		case 'entrants': // Entrants
			$load = 'entrants.php';
			break;
		case 'addentrant': // Add an entrant
			$load = 'entrant_add.php';
			break;
		case 'editentrant': // Edit entrant
			$load = 'entrant_edit.php';
			break;
		case 'practice': // Free practice
			$load = 'race_practice.php';
			break;
		case 'pre-qualifying': // Pre-qualifying
			$load = 'race_prequal.php';
			break;
		case 'qualifying': // Qualifying
			$load = 'race_qualifying.php';
			break;
		case 'race': // Race
			$load = 'race_race.php';
			break;
		default: $load = false;
	}

	if ($load) {
		require_once($load);
	}

require_once($_SERVER['DOCUMENT_ROOT'].'/admin/included/foot_admin.php');
?>