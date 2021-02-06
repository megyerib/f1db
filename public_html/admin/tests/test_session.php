<?php
$event_no = $_GET['session'];

$p_no = mysqli_query($f1db,
	"SELECT num
	FROM f1_test_schedule
	WHERE no = $event_no
	AND test = $test_no");
if (mysqli_num_rows($p_no) == 0) {
	header('Location: /admin/test/'.$test_no.'/results');
}
$p_no = mysqli_fetch_array($p_no);
$p_no = $p_no['num'];

// Add drivers PARANCS
if (isset($_POST['add_current'])) {
	foreach ($_POST['add_entrant'] as $entr_id) {
		// Ellenőrzi, hogy van-e ilyen
		$read = mysqli_query($f1db,
			"SELECT no
			FROM f1_test_results
			WHERE session = $event_no
			AND entr_no = $entr_id");
		
		// Nincs
		if (mysqli_num_rows($read) == 0) {				
			mysqli_query($f1db,
				"INSERT INTO f1_test_results(entr_no, session)
				VALUES($entr_id, $event_no)");
		}
	}
}

// Add drivers FORM
$cur_drvrs = mysqli_query($f1db,
		"SELECT test.no, LEFT(driver.first, 1) AS first, driver.de, driver.last, driver.sr
		FROM f1_test_entrants AS test
		INNER JOIN driver
		ON test.driver = driver.no
		WHERE test.test = $test_no
		ORDER BY driver.last ASC");

// Check form kezdődik
echo '<div style="float:right"><fieldset><legend style="font-weight:bold;">Add drivers</legend>';
echo '<form id="drvr_add" name="SelectedItems" method="post">';
	while ($row = mysqli_fetch_array($cur_drvrs)) {
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		echo '<input type="checkbox" name="add_entrant[]" value="'.$row['no'].'">'.$name.'<br>';
	}
	echo '<hr>';
	echo '<script type="text/javascript" language="javascript">';
	echo "function SetAllCheckBoxes(FormName, AreaID, CheckValue) {
		if(!document.forms[FormName]) return;
		var objCheckBoxes = document.getElementById(AreaID).getElementsByTagName('input');
		if(!objCheckBoxes) return;
		var countCheckBoxes = objCheckBoxes.length;
		if(!countCheckBoxes)objCheckBoxes.checked = CheckValue;
		else for(var i = 0; i < countCheckBoxes; i++) objCheckBoxes[i].checked = CheckValue;}";
	echo '</script>';
	echo '<input type="checkbox" onclick="SetAllCheckBoxes(\'SelectedItems\',\'drvr_add\',this.checked);" /> All<br>';
	echo '<input type="submit" name="add_current" value="Add">';				
echo '</form></fieldset></div>';// Check form vége

// CÍM
if (empty($p_name)) {
	echo '<h1 class="title">'.$test_yr.' '.$test_name.' - Test day '.$p_no.'</h1>';
}
else {
	echo '<h1 class="title">'.$test_yr.' '.$test_name.' GP - '.$p_name.'</h1>';
}
echo '<form method="post">'; // Gomb formja kezdődik
echo '<p><b>Back</b>: <a href="/admin/test/'.$test_no.'/results">Test results</a> | ';
echo '<a href="/admin/test/'.$test_no.'">This test</a> | ';
echo '<a href="/admin/test">All tests</a> | ';
echo '<a href="/admin/test/'.$test_no.'/results/'.$event_no.'/fastadd" style="font-weight:bold;">Fast- add</a> | ';

// SHOWN
if (isset($_POST['show_event'])) {
	mysqli_query($f1db,
		"UPDATE f1_test_schedule
		SET shown = 1
		WHERE no = $event_no");
}
if (isset($_POST['hide_event'])) {
	mysqli_query($f1db,
		"UPDATE f1_test_schedule
		SET shown = 0
		WHERE no = $event_no");
}

// GUI
$shown = mysqli_query($f1db,
	"SELECT shown
	FROM f1_test_schedule
	WHERE no = $event_no");
$shown = mysqli_fetch_array($shown);
$shown = $shown['shown'];

if ($shown == 0) {echo '<input type="submit" name="show_event" value="Hidden - SHOW" style="background-color:red; color:white;">';}
else {echo '<input type="submit" name="hide_event" value="Shown - HIDE" style="background-color:green; color:white;">';}
echo '</form>';
echo '</p>';

// SAVE RESULTS
if (isset($_POST['save_res'])) {
	$count = count($_POST['no']);
	
	if (isset($_POST['del'])) {
	foreach ($_POST['del'] AS $del) {
		mysqli_query($f1db,
			"DELETE
			FROM f1_test_results
			WHERE no = $del");
	}
	}
	
	for ($i = 0; $i < $count; $i++) {
		$prac_no = $_POST['no'][$i];
		$place = $_POST['place'][$i];
		$time  = timetodec($_POST['time'][$i]);
		$laps  = $_POST['laps'][$i];
		
		$save = mysqli_query($f1db,
			"UPDATE f1_test_results
			SET place = $place,
			tme = $time,
			laps = $laps
			WHERE no = $prac_no"
		);
		if ($save) {
			msg('Saved');
		}
		else {
			alert('Error!');
		}
	}
}

// SZERKESZTŐ FELÜLET
$practice_results = mysqli_query($f1db,
	"SELECT LEFT(driver.first,1) AS first, driver.de, driver.last, driver.sr,
		prac.laps, prac.no AS prac_no, prac.tme, prac.place
	FROM f1_test_results AS prac
	INNER JOIN f1_test_entrants AS test
	ON prac.entr_no = test.no
	INNER JOIN driver
	ON test.driver = driver.no
	WHERE session = $event_no
	ORDER BY place = 0, place, driver.last");
echo '<form method="post">';
echo '<table><th>Place</th><th>Driver</th><th>Time</th><th>Laps</th><th>Del</th>';
while ($row = mysqli_fetch_array($practice_results)) {
	echo '<tr>';
	$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
	echo '<input type="hidden" name="no[]" value="'.$row['prac_no'].'">';
	echo '<td><input type="number" min="0" name="place[]" value="'.$row['place'].'"></td>';
	echo '<td>'.$name.'</td>';
	echo '<td><input type="text" name="time[]" value="'.dectotime($row['tme']).'" size="6"></td>';
	echo '<td><input type="number" min="0" name="laps[]" value="'.$row['laps'].'"></td>';
	echo '<td><input type="checkbox" name="del[]" value="'.$row['prac_no'].'"></td>';
	echo '</tr>';
}
echo '</table>';
echo '<input type="submit" name="save_res" value="Save" style="margin-top:3px;">';
echo '</form>';	
?>