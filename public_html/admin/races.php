<?php
require_once('included/head_admin.php');

// ÖSSZES
if (empty($_GET)) {
	echo '<h1 class="title">Races</h1>';
	echo '<p><a href="/admin/race/new">+ Add new</a><br>';
	/*echo '<a href="/admin/race?cleanup=1">Clean up</a>';*/
	echo '</p>';
	
	$races = mysqli_query($f1db,
		"SELECT *
		FROM f1_gp AS gp
		INNER JOIN country
		ON gp.gp = country.gp
		ORDER BY gp.yr DESC, gp.no ASC");
	
	$prev = 0;
	while ($row = mysqli_fetch_array($races)) {
		if ($prev != $row['yr']) {
			echo '<p style="font-weight:bold;">'.$row['yr'].'</p>';
		}
		$prev = $row['yr'];
		
		echo '<a href="/admin/race/'.$row['no'].'">'.$row['yr'].' '.$row['name'].' GP</a><br>';
	}
}

// ÚJ
if (isset($_GET['new'])) {
	echo '<h1 class="title">Add new race</h1>';
	
	if (isset($_POST['addnew2'])) { // Beírja az adatbázisba
		$no    = $_POST['no'];
		$yr    = $_POST['yr'];
		$gp    = $_POST['gp'];
		$no_yr = $_POST['no_yr'];
		$no_gp = $_POST['no_gp'];
		
		mysqli_query($f1db,
			"INSERT INTO f1_gp(no, yr, gp, no_gp, no_yr)
			VALUES($no, $yr, '$gp', $no_gp, $no_yr)");
		mysqli_query($f1db,
			"INSERT INTO f1_details(no)
			VALUES($no)");
		
		header('Location: /admin/race/'.$no);
	}
	
	if (isset($_POST['addnew1'])) {
		$next = mysqli_query($f1db,
			"SELECT MAX(no) AS max
			FROM f1_gp");
		$next = mysqli_fetch_array($next);
		$next = $next['max'] + 1;
		
		// Kitöltetni mindet
		$no = $_POST['no'];
		$yr = $_POST['yr'];
		$gp = $_POST['gp'];
		
		if ($no == $next) {
			// Kiszámolja a sorszámokat
			$next_yr = mysqli_query($f1db,
				"SELECT MAX(no_yr) AS max
				FROM f1_gp
				WHERE yr = $yr");
			$next_yr = mysqli_fetch_array($next_yr);
			$no_yr = $next_yr['max'] + 1;
			
			$next_gp = mysqli_query($f1db,
				"SELECT MAX(no_gp) AS max
				FROM f1_gp
				WHERE gp = '$gp'");
			$next_gp = mysqli_fetch_array($next_gp);
			$no_gp = $next_gp['max'] + 1;
		}
		else {
			// Nem számolja ki
			$no_yr = '';
			$no_gp = '';
			
		}
		echo '<form method="post" action="/admin/race/new">';
			echo '<p>Calculated values: (no, no_yr, no_gp)<br>';
			
			echo '<input type="hidden" name="yr" value="'.$yr.'">';
			echo '<input type="hidden" name="gp" value="'.$gp.'">';
			
			echo '<input type="text" name="no" value="'.$no.'" size="3"> ';
			echo '<input type="text" name="no_yr" value="'.$no_yr.'" size="3"> ';
			echo '<input type="text" name="no_gp" value="'.$no_gp.'" size="3"></p>';
			echo '<input type="submit" name="addnew2" value="Save"> <a href="/admin/race/new">Back</a>';
		echo '</form>';
	}
	
	if (!(isset($_POST['addnew1']) || isset($_POST['addnew2']))) {
		$next = mysqli_query($f1db,
			"SELECT MAX(no) AS max
			FROM f1_gp");
		$next = mysqli_fetch_array($next);
		$next = $next['max'] + 1;
		
		echo '<form method="post" action="/admin/race/new">';
		echo '<p><label class="input">Year, GP</label>';
		echo '<input type="text" name="yr" value="'.date('Y').'" size="3"> ';
		gp_dropdown('gp', '');
		echo '<br><label class="input">N<sup>o</sup> (if next)</label>';
		echo '<input type="text" name="no" value="'.$next.'" size="3"></p>';
		echo '<input type="submit" name="addnew1" value="Save"> <a href="/admin/race/">Back</a>';
		echo '</form>';
	}
}

// EGY VERSENY
if (isset($_GET['race'])) {
	$race_no = $_GET['race'];
	
	// SZERKESZTÉS
	if (isset($_POST['save']) || isset($_POST['savequit'])) {
		$no       = $_POST['no'];
		$yr       = $_POST['yr'];
		$no_yr    = $_POST['no_yr'];
		$no_gp    = $_POST['no_gp'];
		$gp       = $_POST['gp'];
		
		$fullname = addslashes($_POST['fullname']);
		$circuit  = $_POST['circuit'];
		$date     = $_POST['date'];
		$laps     = $_POST['laps'];
		
		$main = mysqli_query($f1db,
			"UPDATE f1_gp
			SET no_yr = $no_yr,
			no_gp = $no_gp,
			gp = '$gp',
			yr = $yr
			WHERE no = $no");
		
		$details = mysqli_query($f1db,
			"UPDATE f1_details
			SET fullname = '$fullname',
			circuit = $circuit,
			dat = '$date',
			laps = $laps
			WHERE no = $no");
		 
		 if (isset($_POST['savequit']) && $main && $details) {
		 	header('Location: /admin/race');
		 }
	}
	
	$race = mysqli_query($f1db,
		"SELECT *
		FROM f1_gp AS gp
		INNER JOIN country
		ON gp.gp = country.gp
		INNER JOIN f1_details AS det
		ON gp.no = det.no
		WHERE gp.no = $race_no");
	
	if (mysqli_num_rows($race) == 0) {
		header('Location: /admin/race');
	}
	
	// KEZDŐDIK
	$rw = mysqli_fetch_array($race);
	
	$race_yr = $rw['yr'];
	// Év többi versenye
	$yr_races = mysqli_query($f1db,
		"SELECT *
		FROM f1_gp AS gp
		INNER JOIN country
		ON gp.gp = country.gp
		WHERE gp.yr = $race_yr
		ORDER BY gp.no ASC");

	echo '<div style="float:right;"><fieldset>';
	echo '<legend>'.$race_yr.'</legend>';
	while ($row = mysqli_fetch_array($yr_races)) {
		echo '<a href="/admin/race/'.$row['no'].'/results">'.$row['name'].' GP</a><br>';
	}
	echo '</fieldset></div>';
	
	echo '<h1 class="title">';
	echo '<a href="/admin/race/'.($race_no-1).'">&lt;</a> ';
	echo $rw['yr'].' '.$rw['name'].' GP';
	echo ' <a href="/admin/race/'.($race_no+1).'">&gt;</a>';
	echo '</h1>';
		
	echo '<a href="/admin/race">Back</a> | ';
	echo '<a href="/admin/race/delete/'.$race_no.'">Delete</a> | ';
	echo '<b><a href="/admin/race/'.$race_no.'/results">Edit results</a></b>';
	
	if (isset($_POST['save']) || isset($_POST['savequit'])) {
		if ($main && $details) {
			msg('Saved');
		}
		else {
			alert('Error!');
		}
	}
	echo '<br><br>';
	
	// Alapadatok, helyszín
	echo '<form method="post" action="/admin/race/'.$race_no.'">';
	echo '<input type="text" name="yr" value="'.$rw['yr'].'" size="3"> ';
	echo gp_dropdown('gp', $rw['gp']).'<br><br>';
	
	// No
	echo '<input type="hidden" name="no" value="'.$rw['no'].'">';
	echo '<label class="input">No</label>';
	echo '<input value="'.$rw['no'].'" size="3" disabled><br>'; // Csak szemléltető
	echo '<label class="input">In this yr</label>';
	echo '<input type="text" name="no_yr" value="'.$rw['no_yr'].'" size="3"><br>';
	echo '<label class="input">This GP</label>';
	echo '<input type="text" name="no_gp" value="'.$rw['no_gp'].'" size="3">';
	echo '<br><br>';
	echo '<label class="input">Official name</label>';
	echo '<input type="text" name="fullname" value="'.$rw['fullname'].'" size="37"><br>';
	echo '<label class="input">Circuit</label>';
	circuit_dropdown('circuit', $rw['circuit']);
	echo '<br><br>';
	echo '<label class="input">Date (race)</label>';
	echo '<input type="date" name="date" value="'.$rw['dat'].'"><br>';
	echo '<label class="input">Laps</label>';
	echo '<input type="number" name="laps" value="'.$rw['laps'].'"><br>';
	// Submit
	echo '<p><input type="submit" name="save" value="Save"> ';
	echo '<input type="submit" name="savequit" value="Save & Quit"></p>';
	
	echo '</form>';
	
	function event_dropdown($dropdown_name, $chosen) {
		$query = array(
			'P'  => 'Practice',
			'Q'  => 'Qualifying',
			'R'  => 'Race',
			'PQ' => 'Pre-q'
		);
		custom_dropdown($dropdown_name, $query, '', '', $chosen);
	}
	
	echo '<hr>';
	echo '<h2>Schedule</h2>';
	
	// ADD SCHEDULE
	if (isset($_POST['schedule_add'])) {
		$type = $_POST['type'];
		$num  = $_POST['eventnum'];
		$name = $_POST['eventname'];
		$time = $_POST['tme'];
		
		mysqli_query($f1db,
			"INSERT INTO f1_gp_schedule(rnd, type, num, name, tme)
			VALUES($race_no, '$type', $num, '$name', '$time')");
	}
	
	// SAVE SCHEDULE
	if (isset($_POST['schedule_save'])) {
		$del = 0;
		foreach ($_POST['no'] as $key => $event_no) {
			if (isset($_POST['schedule_delete'][$del]) && $_POST['schedule_delete'][$del] == $event_no) {
				mysqli_query($f1db,
					"DELETE FROM f1_gp_schedule
					WHERE no = $event_no"
				);
				$del++;
			}
			else {
				$type = $_POST['type'][$key];
				$num  = $_POST['eventnum'][$key];
				$name = $_POST['eventname'][$key];
				$time = $_POST['tme'][$key];
				
				mysqli_query($f1db,
					"UPDATE f1_gp_schedule
					SET type = '$type',
					num = $num,
					name = '$name',
					tme = '$time'
					WHERE no = '$event_no'"
				);
			}
		}
	}
	
	// Schedule
	$schedule = mysqli_query($f1db,
		"SELECT *
		FROM f1_gp_schedule
		WHERE rnd = $race_no
		ORDER BY tme, no ASC");
	
	echo '<form method="post">';
	while ($row = mysqli_fetch_array($schedule)) {
		event_dropdown('type[]', $row['type']);
		echo '<input type="hidden" name="no[]" value="'.$row['no'].'">';
		echo '<input type="number" name="eventnum[]" value="'.$row['num'].'"> ';
		echo '<input type="text" name="eventname[]" value="'.$row['name'].'" size="10"> ';
		$time = substr($row['tme'], 0, -3);
		$time[10] = 'T';
		echo '<input type="datetime-local" name="tme[]" value="'.$time.'">';
		echo '<input type="checkbox" name="schedule_delete[]" value="'.$row['no'].'"><br>';	
	}
	echo '<input type="submit" name="schedule_save" value="Save">';
	echo '</form>';
	
	echo '<hr width="200" align="left">';
	echo '<form method="post" action="/admin/race/'.$race_no.'">';
	event_dropdown('type', 0);
	echo '<input type="number" name="eventnum" value="0"> ';
	echo '<input type="text" name="eventname" value="'.$row['name'].'" size="10"> ';
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
			"SELECT gp.yr, country.name
			FROM f1_gp AS gp
			INNER JOIN country
			ON gp.gp = country.gp
			WHERE gp.no = $no");
		$row = mysqli_fetch_array($query);
				
		echo '<h1 class="title" style="color:red;">Delete race</h1>';
		echo 'Do you really delete <b>'.$row['yr'].' '.$row['name'].' GP</b>?<br>';
		echo '<form method="post" action="/admin/race/delete/'.$no.'">';
			echo '<input type="submit" name="submit" value="Yup">';
			echo '<input type="submit" name="cancel" value="Not today">';
		echo '</form>';
		
	}
	// Törlés
	if (isset($_POST['submit'])) {
		$no = $_GET['delete'];
		mysqli_query($f1db,
			"DELETE
			FROM f1_gp
			WHERE no = $no");
		mysqli_query($f1db,
			"DELETE
			FROM f1_details
			WHERE no = $no");
		header('Location: /admin/race');
	}
	// Mégse
	if (isset($_POST['cancel'])) {
		header('Location: /admin/race');
	}
}
/*// CLEAN UP
if (isset($_GET['cleanup'])) {
	$cleanup = mysqli_query($f1db,
		"DELETE
		FROM f1_race
		WHERE yr = 0
		AND gp = ''");
	if ($cleanup) {
		$_SESSION['alert'] = 'Entrant DB cleaned';
	}
	header('Location: /admin/race');
}*/

require_once('included/foot_admin.php');
?>