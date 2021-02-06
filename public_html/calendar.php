<?php
$pagetitle = 'Calendar';
require_once('included/head.php');

if (isset($_GET['input'])) { // Hogy tovább bővíthető legyen (év, stb.)
	$input = explode('/', $_GET['input']);
	foreach ($input as $time) {
		if ((strlen($time) == 2 || strlen($time) == 1) && is_numeric($time)) {$day = $time;}
		if (strlen($time) == 3) {
			$monthsname = array(
				'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4,
				'may' => 5, 'jun' => 6, 'jul' => 7, 'aug' => 8,
				'sep' => 9, 'oct' =>10, 'nov' =>11, 'dec' =>12
			);
			if (isset($monthsname[$time])) {$month = $monthsname[$time];}
		}
	}
} else {
	$time = localtime();
	$month = $time[4] + 1;
	$day   = $time[3];
}
if (!(isset($month) && isset($day))) {
	header('Location: /calendar');
}

$date = date('j F', mktime(0,0,0, $month, $day, 0));
echo '<h1 align="center">'.$date.' in Formula One</b></h1>';

// NEW

// Nagydíjak
$gps = mysqli_query($f1db,
	"SELECT race.yr, race.gp, country.name AS gpname, race.no AS raceno, race.finish,
	driver.id AS driverid, driver.first, driver.de, driver.last, driver.sr, driver.country,
	team.id AS teamid, team.fullname AS teamname, team.country AS teamcountry
FROM f1_race AS race
INNER JOIN f1_details AS det ON (race.rnd = det.no)
INNER JOIN driver ON (race.driver = driver.no)
INNER JOIN country ON (race.gp = country.gp)
INNER JOIN team ON (race.team = team.no)
WHERE race.finish <= 3
AND MONTH(det.dat) = $month
AND DAY(det.dat) = $day");
	
if (mysqli_num_rows($gps) > 0) {
	$results = array();
	
	// Verseny első 3
	while ($row = mysqli_fetch_array($gps)) {
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		$link = flag($row['country']).driver_link($row['driverid'], $name);
		$team = '<span style="padding-left:25px;">'.team_link($row['teamid'], $row['teamname']).'</span>';
		
		$gp   = $row['yr'].$row['gp'];
		$short= $row['gp'];
		$yr   = $row['yr'];
		$name = $row['gpname'];
		$no   = $row['raceno'];
		
		$place = $row['finish'];
		
		$results[$gp]['yr'] = $yr;
		$results[$gp]['short'] = $short;
		$results[$gp]['name'] = $name;
		$results[$gp][$place]['driver'][$no] = $link;
		$results[$gp][$place]['team'] = $team;
	}
	
	// Pole
	$poles = mysqli_query($f1db,
		"SELECT race.yr, race.gp,
			driver.id AS driverid, driver.first, driver.de, driver.last, driver.sr, driver.country,
			team.id AS teamid, team.fullname AS teamname
		FROM f1_q AS q
		INNER JOIN f1_race AS race ON q.entr_no = race.no
		INNER JOIN f1_details AS det ON (race.rnd = det.no)
		INNER JOIN driver ON (race.driver = driver.no)
		INNER JOIN team ON (race.team = team.no)
		WHERE q.place = 1
		AND MONTH(det.dat) = $month
		AND DAY(det.dat) = $day
		ORDER BY rnd ASC");
	while ($row = mysqli_fetch_array($poles)) {
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		$link = flag($row['country']).driver_link($row['driverid'], $name);
		$team = '<span style="padding-left:25px;">'.team_link($row['teamid'], $row['teamname']).'</span>';
		$gp = $row['yr'].$row['gp'];
		
		$results[$gp]['pole']['driver'] = $link;
		$results[$gp]['pole']['team'] = $team;
	}
	
	// Leggyorsabb kör
	$fstst = mysqli_query($f1db,
		"SELECT race.yr, race.gp,
			driver.id AS driverid, driver.first, driver.de, driver.last, driver.sr, driver.country,
			team.id AS teamid, team.fullname AS teamname
		FROM f1_fastest AS fstst
		INNER JOIN f1_race AS race ON fstst.entr_no = race.no
		INNER JOIN f1_details AS det ON (race.rnd = det.no)
		INNER JOIN driver ON (race.driver = driver.no)
		INNER JOIN team ON (race.team = team.no)
		AND MONTH(det.dat) = $month
		AND DAY(det.dat) = $day
		ORDER BY race.rnd ASC");
	while ($row = mysqli_fetch_array($fstst)) {
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		$link = flag($row['country']).driver_link($row['driverid'], $name);
		$team = '<span style="padding-left:25px;">'.team_link($row['teamid'], $row['teamname']).'</span>';
		$gp = $row['yr'].$row['gp'];
		
		$results[$gp]['fastest']['driver'] = $link;
		$results[$gp]['fastest']['team'] = $team;
	}
	
	// Kiírja az egészet
	echo '<table class="results" align="center">';
	echo '<th>Grand Prix</th><th>Winner</th><th>2nd</th><th>3rd</th><th>Pole position</th><th>Fastest lap</th>';
	foreach ($results as $gp) {
		echo '<tr>';
			$name = $gp['yr'] . ' ' . $gp['name'] . ' GP';
		echo '<td>'.flag($gp['short']).race_link($gp['yr'], $gp['short'], $name).'</td>';
		for ($place = 1; $place <= 3 ; $place++) {
			echo '<td>'.implode($gp[$place]['driver'], '</br>').'<br>';
			echo $gp[$place]['team'].'</td>';
		}
		echo '<td>'.$gp['pole']['driver'].'<br>'.$gp['pole']['team'].'</td>';
		echo '<td>'.$gp['fastest']['driver'].'<br>'.$gp['fastest']['team'].'</td>';
		echo '</tr>';
	}
	echo '</table></br>';
}
else {
	echo '<p align="center">No results for this day :(</p>';
}
//
echo '<table class="results">';
$cur_yr = date('Y');

// Egész vége

echo '<h1 align="center">Other days</h1>';
echo '<p align="center"><a href="/calendar">Today</a></p>';
require_once('included/calendar_yr.php');

require_once('included/foot.php');
?>