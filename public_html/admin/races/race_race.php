<?php
// Év többi versenye
$yr_races = mysqli_query($f1db,
	"SELECT *
	FROM f1_gp AS gp
	INNER JOIN country
	ON gp.gp = country.gp
	WHERE gp.yr = $race_yr
	ORDER BY gp.no ASC");

echo '<div style="float:right;">';
echo '<fieldset><legend style="font-weight:bold;">'.$race_yr.'</legend>';
while ($row = mysqli_fetch_array($yr_races)) {
	echo '<a href="/admin/race/'.$row['no'].'/results/race">'.$row['name'].' GP</a><br>';
}
echo '</fieldset></div>';

$self_link = '/admin/race/'.$race_no.'/results/race';
echo '<h1 class="title">';
echo '<a href="/admin/race/'.($race_no-1).'/results/race">&lt;</a> ';
echo $race_yr.' '.$race_name.' GP results';
echo ' <a href="/admin/race/'.($race_no+1).'/results/race">&gt;</a> ';
echo '</h1>';

echo '<form method="post">'; // Itt kezdődik a form, hogy egy sorba kerüljön a gomb a menüvel
echo '<p><b>Back</b>: ';
echo '<a href="/admin/race/'.$race_no.'/results">Results</a> | ';
echo '<a href="/admin/race/'.$race_no.'">This race</a> | ';
echo '<a href="/admin/race">Races</a> | ';
echo '<a href="/admin/race/'.$race_no.'/results/race/fastadd" style="font-weight:bold;">Fast- add</a> | ';

// SHOWN
if (isset($_POST['show_event'])) {
	$ev_no = $_POST['ev_no'];
	mysqli_query($f1db,
		"UPDATE f1_gp_schedule
		SET shown = 1
		WHERE no = $ev_no");
}
if (isset($_POST['hide_event'])) {
	$ev_no = $_POST['ev_no'];
	mysqli_query($f1db,
		"UPDATE f1_gp_schedule
		SET shown = 0
		WHERE no = $ev_no");
}

// GUI
$shown = mysqli_query($f1db,
	"SELECT no, shown
	FROM f1_gp_schedule
	WHERE rnd = $race_no
	AND type = 'R'
	LIMIT 1");

if (mysqli_num_rows($shown) > 0) {
	$shwn = mysqli_fetch_array($shown);
	$shown = $shwn['shown'];
	$event_no = $shwn['no'];
	
	echo '<input type="hidden" name="ev_no" value="'.$event_no.'">';
	if ($shown == 0) {echo '<input type="submit" name="show_event" value="Hidden - SHOW" style="background-color:red; color:white;">';}
	else {echo '<input type="submit" name="hide_event" value="Shown - HIDE" style="background-color:green; color:white;">';}
	echo '</form>';
	echo '</p>';
}
else {
	echo 'Event can\'t be hide. Create it first in the <a href="/admin/race/'.$race_no.'">schedule tab</a>!</p>';
}

// SAVE
if (isset($_POST['save_res'])) {
	$count = count($_POST['no']);
	for ($i = 0; $i < $count; $i++) {
		$no     = $_POST['no'][$i];
		$start  = $_POST['start'][$i];
		$status = $_POST['status'][$i];
		$finish = $_POST['finish'][$i];
		$laps   = $_POST['laps'][$i];
		$score  = $_POST['score'][$i];
					
		// Idő mező vizsgálata
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
		else if ($i == 0) {
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
					
		if (!isset($die_query)) {mysqli_query($f1db,
			"UPDATE f1_race
			SET start = $start,
			status = $status,
			finish = $finish,
			laps = $laps,
			score = $score,
			time = $time,
			note = '$note'
			WHERE no = $no");
		}
	}
} // SAVE

$racers = mysqli_query($f1db,
	"SELECT LEFT(driver.first, 1) AS first, driver.de, driver.last, driver.sr,
		race.no, race.start, race.finish, race.status, race.note, race.laps, race.time, race.score
	FROM f1_race AS race
	INNER JOIN driver
	ON race.driver = driver.no
	WHERE rnd = $race_no
	ORDER BY finish = 0, finish, driver.last ASC");

echo '<form method="post">';
echo '<table><th>Driver</th><th>Start</th><th>Status</th><th style="background-color:#328EFD; color:white;">Finish</th><th>Time / Gap / Note</th><th>Laps</th><th>Score</th>';
while ($row = mysqli_fetch_array($racers)) {
	echo '<tr>';
	$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
	echo '<td>'.$name.'</td>';
	echo '<input type="hidden" name="no[]" value="'.$row['no'].'">';
		if ($row['start'] == null) {$row['start'] = 0;}
	echo '<td><input type="number" min="0" name="start[]" value="'.$row['start'].'"></td>';
	echo '<td style="padding-right:0px;">';
		status_dropdown('status[]', $row['status']);
	echo '</td>';
		if ($row['finish'] == null) {$row['finish'] = 0;}
	echo '<td style="background-color:#328EFD; padding-left:8px;"><input type="number" min="0" name="finish[]" value="'.$row['finish'].'"></td>';
	if ($row['time'] > 0) {
		if ($row['finish'] == 1) {
			$time = dectotime($row['time']);
			$first_time = $row['time'];
		}
		else {
			$time = '+'.dectotime($row['time'] - $first_time);
		}
	}
	else {
		$time = $row['note'];
	}
	echo '<td><input type="text" name="time[]" value="'.$time.'"></td>';
		if ($row['laps'] == null) {$row['laps'] = 0;}
	echo '<td><input type="number" min="0" name="laps[]" value="'.$row['laps'].'"></td>';
	echo '<td><input type="text" name="score[]" value="'.($row['score']+0).'" size="3"></td>';
	echo '</tr>';
}
echo '</table>';
echo '<input type="submit" name="save_res" value="Save">';
echo '</form>';
?>