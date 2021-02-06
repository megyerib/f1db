<?php
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

// Cím
echo '<h1 class="title">';
echo '<a href="/admin/race/'.($race_no-1).'/results">&lt;</a> ';
echo $race_yr.' '.$race['name'].' GP results';
echo ' <a href="/admin/race/'.($race_no+1).'/results">&gt;</a>';
echo '</h1>';
echo '<a href="/admin/race/'.$race_no.'">Back to race menu</a><br>';
	
	echo '<ul>';
	echo '<li><a href="/admin/race/'.$race_no.'/entrants">Entrants</a></li>';
	echo '<br>';
	
	// Kell ide, mert később meg fog jelenni
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
	$events = mysqli_query($f1db,
		"SELECT *
		FROM f1_gp_schedule
		WHERE rnd = $race_no
		ORDER BY tme, no, num ASC");
	
	$q_set = $pq_set = $r_set = false;
	
	while ($row = mysqli_fetch_array($events)) {
		$type = $row['type'];
		if ($row['shown'] == 1) {
			$shown = '<span style="background-color:green; color:white; margin-left:5px; padding-left:3px; padding-right:3px;">shown</span>';
		}
		else {
			$shown = '<span style="background-color:red; color:white; margin-left:5px; padding-left:3px; padding-right:3px;">hidden</span>';
		}
		
		if ($type == 'P') {
			$link = '/admin/race/'.$race_no.'/results/practice/'.$row['no'];
			$text = 'Practice '.$row['num'];
		}
		if ($type == 'PQ') {
			$link = '/admin/race/'.$race_no.'/results/pre-qualifying';
			$text = 'Pre-qualifying';
			$pq_set = true;
		}
		if ($type == 'Q') {
			$link = '/admin/race/'.$race_no.'/results/qualifying';
			$text = 'Qualifying';
			$q_set = true;
		}
		if ($type == 'R') {
			$link = '/admin/race/'.$race_no.'/results/race';
			$text = 'Race';
			$r_set = true;
		}
		if ($row['name'] != '') {
			$text = $row['name'];
		}
		echo '<li><a href="'.$link.'">'.$text.'</a>'.$shown.'</li>';
	}
	echo '</ul>';
	if (!($q_set && $r_set)) { // Vmelyik nincs beállítva
		echo '<hr width="200" align="left">';
		echo '<h2>Schedule didn\'t set</h2>';
		echo '<ul>';
		if (!$q_set) {
			echo '<li><a href="/admin/race/'.$race_no.'/results/qualifying">Qualifying</a></li>';
		}
		if (!$r_set) {
			echo '<li><a href="/admin/race/'.$race_no.'/results/race">Race</a></li>';
		}
		echo '</ul>';
	}
	
	// FASTEST LAP
	echo '<h2>Fastest lap</h2>';
	echo '<form method="post">';

	// Save fastest
	if (isset($_POST['submit_fastest'])) {
		$driver = $_POST['fastest_driver'];
		$time   = timetodec($_POST['fastest_time']);
		
		if ($_POST['fstst_exists'] == 0) {
			mysqli_query($f1db,
				"INSERT INTO f1_fastest(rnd, driver, time)
				VALUES($race_no, $driver, $time)");
		}
		
		mysqli_query($f1db,
			"UPDATE f1_fastest
			SET driver = $driver,
			time = $time
			WHERE rnd = $race_no");
	}
	
	$fastest = mysqli_query($f1db,
		"SELECT *
		FROM f1_fastest
		WHERE rnd = $race_no
		LIMIT 1");
	if (mysqli_num_rows($fastest) > 0) {
		$fstst = mysqli_fetch_array($fastest);
		$fastest_driver = $fstst['driver'];
		$fastest_time = dectotime($fstst['time']);
		$fstst_exists = 1;
	}
	else {
		$fastest_driver = 0;
		$fastest_time = 0;
		$fstst_exists = 0;
	}

	/*$entrants = mysqli_query($f1db,
		"SELECT DISTINCT race.driver, driver.no, driver.first, driver.de, driver.last, driver.sr
		FROM f1_race AS race
		INNER JOIN driver
		ON race.driver = driver.no
		WHERE rnd = $race_no");

	echo '<select name="fastest_driver">';
	echo '<option value="0">---</option>';
	while ($row = mysqli_fetch_array($entrants)) {
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		$row['driver'] == $fastest_driver ? $selected = ' selected' : $selected = '';
		echo '<option value="'.$row['no'].'"'.$selected.'>'.$name.'</option>';
	}
	echo '</select> ';
	echo '<input type="text" name="fastest_time" value="'.$fastest_time.'" size="8"> ';
	echo '<input type="hidden" name="fstst_exists" value="'.$fstst_exists.'">';
	echo '<input type="submit" name="submit_fastest" value="Save">';
	echo '</form>';*/
	
	$entrants =
		"SELECT race.driver, driver.first, driver.de, driver.last, driver.sr
		FROM f1_race AS race
		INNER JOIN driver
		ON race.driver = driver.no
		WHERE rnd = $race_no
		ORDER BY driver.last ASC";
	
	custom_dropdown('fastest_driver', $entrants, 'driver', 'name_2 first,de,last,sr', $fastest_driver);
	
	echo '<input type="text" name="fastest_time" value="'.$fastest_time.'" size="8"> ';
	echo '<input type="hidden" name="fstst_exists" value="'.$fstst_exists.'">';
	echo '<input type="submit" name="submit_fastest" value="Save">';
	echo '</form>';
	
	// races.php-ból másolva, a többi része fent van
	echo '<h2>Schedule</h2>';
	
	function event_dropdown($dropdown_name, $chosen) {
		$query = array(
			'P'  => 'Practice',
			'Q'  => 'Qualifying',
			'R'  => 'Race',
			'PQ' => 'Pre-q'
		);
		custom_dropdown($dropdown_name, $query, '', '', $chosen);
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
?>