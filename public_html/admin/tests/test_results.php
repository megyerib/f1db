<?php
/*
* Hatalmas gány az egész, az elsők közt írd újra az egészet
* Új rendszer az egész adminhoz (+js)

  Bővíthető:
  * Pre-qualifying
  * test shared
*/
require_once($_SERVER['DOCUMENT_ROOT'].'/admin/included/head_admin.php');

$mode = $_GET['mode'];

/* VERSENYADATOK
   Ha meg van adva egy verseny száma, betölti a szükséges
   adatokat / függvényeket
   
   Van értelme külön fájlba kiszedni? */
   
if (isset($_GET['test'])) {
	$test_no = $_GET['test'];
	
	// Versenyadatok
	$test = mysqli_query($f1db,
			"SELECT yr, name
			FROM f1_test
			WHERE no = $test_no");
	$test = mysqli_fetch_array($test);
	$test_yr = $test['yr'];
	$test_name = $test['name'];
	
	// Nevezők dropdown
	function entrant_dropdown($slctd, $test, $f1db) {
		$entrants = mysqli_query($f1db,
			"SELECT test.no, LEFT(driver.first, 1) AS first, driver.de, driver.last, driver.sr
			FROM f1_test AS test
			INNER JOIN driver
			ON test.driver = driver.no
			WHERE rnd = $test
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
			$load = 'test_results_main.php';
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
		case 'session': // Free practice
			$load = 'test_session.php';
			break;
		default: $load = false;
	}

	if ($load) {
		require_once($load);
	}

require_once($_SERVER['DOCUMENT_ROOT'].'/admin/included/foot_admin.php');
?>