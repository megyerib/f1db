<?php
require_once('included/head_admin.php');

// ÖSSZES
if (empty($_GET)) {
	echo '<h1 class="title">Tests</h1>';
	echo '<p><a href="/admin/test/new">+ Add new</a><br>';
	echo '</p>';
	
	$tests = mysqli_query($f1db,
		"SELECT yr, no, name
		FROM f1_test
		ORDER BY yr DESC, no_yr ASC");
	
	$prev = 0;
	while ($row = mysqli_fetch_array($tests)) {
		if ($prev != $row['yr']) {
			echo '<p style="font-weight:bold;">'.$row['yr'].'</p>';
		}
		$prev = $row['yr'];
		
		echo '<a href="/admin/test/'.$row['no'].'">'.$row['name'].'</a><br>';
	}
}

// ÚJ
if (isset($_GET['new'])) {
	echo '<h1 class="title">Add new test</h1>';
	
	if (isset($_POST['addnew2'])) { // Beírja az adatbázisba
		$no    = $_POST['no'];
		$yr    = $_POST['yr'];
		$no_yr = $_POST['no_yr'];
		
		mysqli_query($f1db,
			"INSERT INTO f1_test(no, yr, no_yr)
			VALUES($no, $yr, $no_yr)");
		
		header('Location: /admin/test/'.$no);
	}
	
	if (isset($_POST['addnew1'])) {
		$next = mysqli_query($f1db,
			"SELECT MAX(no) AS max
			FROM f1_test");
		$next = mysqli_fetch_array($next);
		$next = $next['max'] + 1;
		
		// Kitöltetni mindet
		$no = $_POST['no'];
		$yr = $_POST['yr'];
		
		if ($no == $next) {
			// Kiszámolja a sorszámokat
			$next_yr = mysqli_query($f1db,
				"SELECT MAX(no_yr) AS max
				FROM f1_test
				WHERE yr = $yr");
			$next_yr = mysqli_fetch_array($next_yr);
			$no_yr = $next_yr['max'] + 1;
		}
		else {
			// Nem számolja ki
			$no_yr = '';
			
		}
		echo '<form method="post" action="/admin/test/new">';
			echo '<p>Calculated values: (no_yr)<br>';
			
			echo '<input type="hidden" name="yr" value="'.$yr.'">';
			
			echo '<input type="hidden" name="no" value="'.$no.'" size="3"> ';
			echo '<p><input type="text" name="no_yr" value="'.$no_yr.'" size="3"></p>';
			echo '<input type="submit" name="addnew2" value="Next"> <a href="/admin/test/new">Back</a>';
		echo '</form>';
	}
	
	if (!(isset($_POST['addnew1']) || isset($_POST['addnew2']))) {
		$next = mysqli_query($f1db,
			"SELECT MAX(no) AS max
			FROM f1_test");
		$next = mysqli_fetch_array($next);
		$next = $next['max'] + 1;
		
		echo '<form method="post" action="/admin/test/new">';
		echo '<p><label class="input">Year</label>';
		echo '<input type="text" name="yr" value="'.date('Y').'" size="3">';
		echo '<input type="hidden" name="no" value="'.$next.'" size="3"></p>';
		echo '<input type="submit" name="addnew1" value="Next"> <a href="/admin/test/">Back</a>';
		echo '</form>';
	}
}

// EGY VERSENY
if (isset($_GET['test'])) {
	$test_no = $_GET['test'];
	
	// SZERKESZTÉS
	if (isset($_POST['save']) || isset($_POST['savequit'])) {
		$no       = $_POST['no'];
		$yr       = $_POST['yr'];
		$no_yr    = $_POST['no_yr'];
		$name     = addslashes($_POST['name']);
		$circuit  = $_POST['circuit'];
		$start    = $_POST['start'];
		$end      = $_POST['end'];
		
		
		$save = mysqli_query($f1db,
			"UPDATE f1_test
			SET yr = $yr,
			no_yr = $no_yr,
			name = '$name',
			circuit = $circuit,
			start = '$start',
			end = '$end'
			WHERE no = $no"
		);
		 
		if (isset($_POST['savequit']) && $save) {
			$_SESSION['msg'] = 'Saved';
			header('Location: /admin/test');
		}
		
		if (isset($_POST['save']) && $save) {
			msg('Saved');
		}
		else {
			alert('Error!');
		}
	}
	
	$test = mysqli_query($f1db,
		"SELECT *
		FROM f1_test
		WHERE no = $test_no");
	
	if (mysqli_num_rows($test) == 0) {
		header('Location: /admin/test');
	}
	
	// KEZDŐDIK
	$rw = mysqli_fetch_array($test);
	
	$test_yr = $rw['yr'];
	// Év többi tesztje
	$yr_tests = mysqli_query($f1db,
		"SELECT *
		FROM f1_test
		WHERE yr = $test_yr
		ORDER BY no_yr ASC");

	echo '<div style="float:right;"><fieldset>';
	echo '<legend>'.$test_yr.'</legend>';
	while ($row = mysqli_fetch_array($yr_tests)) {
		echo '<a href="/admin/test/'.$row['no'].'">'.$row['name'].'</a><br>';
	}
	echo '</fieldset></div>';
	
	echo '<h1 class="title">';
	echo $rw['yr'].' '.$rw['name'];
	echo '</h1>';
		
	echo '<a href="/admin/test">Back</a> | ';
	echo '<a href="/admin/test/delete/'.$test_no.'">Delete</a> | ';
	echo '<b><a href="/admin/test/'.$test_no.'/results">Edit results</a></b>';
	
	echo '<br><br>';
	
	// Alapadatok, helyszín
	echo '<form method="post">';
	echo '<label class="input">Year</label>';
	echo '<input type="text" name="yr" value="'.$rw['yr'].'" size="3"><br>';
	
	// No
	echo '<input type="hidden" name="no" value="'.$rw['no'].'">';
	echo '<label class="input">In this yr</label>';
	echo '<input type="text" name="no_yr" value="'.$rw['no_yr'].'" size="3">';
	echo '<br><br>';
	echo '<label class="input">Name</label>';
	echo '<input type="text" name="name" value="'.$rw['name'].'">';
	echo '<br><br>';
	echo '<label class="input">Circuit</label>';
	circuit_dropdown('circuit', $rw['circuit']);
	echo '<br><br>';
	echo '<label class="input">From</label>';
	echo '<input type="date" name="start" value="'.$rw['start'].'"><br>';
	echo '<label class="input">To</label>';
	echo '<input type="date" name="end" value="'.$rw['end'].'"><br>';
	// Submit
	echo '<p><input type="submit" name="save" value="Save"> ';
	echo '<input type="submit" name="savequit" value="Save & Quit"></p>';
	
	echo '</form>';
	
	echo '<hr>';
	echo '<h2>Schedule</h2>';
	
	// ADD SCHEDULE
	if (isset($_POST['schedule_add'])) {
		$num  = $_POST['eventnum'];
		$time = $_POST['tme'];
		
		mysqli_query($f1db,
			"INSERT INTO f1_test_schedule(test, num, tme)
			VALUES($test_no, $num, '$time')");
	}
	
	// SAVE SCHEDULE
	if (isset($_POST['schedule_save'])) {
		$del = 0;
		foreach ($_POST['no'] as $key => $event_no) {
			if (isset($_POST['schedule_delete'][$del]) && $_POST['schedule_delete'][$del] == $event_no) {
				mysqli_query($f1db,
					"DELETE FROM f1_test_schedule
					WHERE no = $event_no"
				);
				$del++;
			}
			else {
				$num  = $_POST['eventnum'][$key];
				$time = $_POST['tme'][$key];
				
				mysqli_query($f1db,
					"UPDATE f1_test_schedule
					SET num = $num,
					tme = '$time'
					WHERE no = '$event_no'"
				);
			}
		}
	}
	
	// Schedule
	$schedule = mysqli_query($f1db,
		"SELECT *
		FROM f1_test_schedule
		WHERE test = $test_no
		ORDER BY tme, no ASC");
	
	echo '<form method="post">';
	while ($row = mysqli_fetch_array($schedule)) {
		echo '<input type="hidden" name="no[]" value="'.$row['no'].'">';
		echo 'Test day <input type="number" name="eventnum[]" value="'.$row['num'].'"> ';
		//echo '<input type="text" name="eventname[]" value="'.$row['name'].'" size="10"> ';
		$time = substr($row['tme'], 0, -3);
		$time[10] = 'T';
		echo '<input type="datetime-local" name="tme[]" value="'.$time.'">';
		echo '<input type="checkbox" name="schedule_delete[]" value="'.$row['no'].'"><br>';	
	}
	echo '<input type="submit" name="schedule_save" value="Save">';
	echo '</form>';
	
	echo '<hr width="200" align="left">';
	echo '<form method="post">';
	echo 'Test day <input type="number" name="eventnum" value="0"> ';
	//echo '<input type="text" name="eventname" value="'.$row['name'].'" size="10"> ';
	echo '<input type="datetime-local" name="tme"> ';
	echo '<input type="Submit" name="schedule_add" value="Add">';
	echo '</form>';
}

// TÖRLÉS
if (isset($_GET['delete'])) {
	// Megerősítés
	if (!isset($_POST['submit']) && !isset($_POST['cancel'])) {
		$no = $_GET['delete'];
		$query = mysqli_query($f1db,
			"SELECT yr, name
			FROM f1_test
			WHERE no = $no");
		$row = mysqli_fetch_array($query);
				
		echo '<h1 class="title" style="color:red;">Delete test</h1>';
		echo '<p>Do you really delete <b>'.$row['name'].'</b>?</p>';
		echo '<form method="post">';
			echo '<input type="submit" name="submit" value="Yup"> ';
			echo '<input type="submit" name="cancel" value="Not today">';
		echo '</form>';
		
	}
	// Törlés
	if (isset($_POST['submit'])) {
		$no = $_GET['delete'];
		mysqli_query($f1db,
			"DELETE
			FROM f1_test
			WHERE no = $no");
		header('Location: /admin/test');
	}
	// Mégse
	if (isset($_POST['cancel'])) {
		header("Location: /admin/test/$no");
	}
}

require_once('included/foot_admin.php');
?>