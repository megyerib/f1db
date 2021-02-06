<?php
$time = localtime();
$month = $time[4] + 1;
$day   = $time[3];

$date = date('j F', mktime(0,0,0, $month, $day, 0));
echo '<b>'.$date.' in Formula One</b><br><br>';

// Nagydíjak
$gps = mysqli_query($f1db,
	"SELECT race.yr, race.gp, country.name AS gpname, race.no AS raceno,
	driver.id AS driverid, driver.first, driver.de, driver.last, driver.sr, driver.country,
	team.id AS teamid, team.fullname AS teamname, team.country AS teamcountry
FROM f1_race AS race
INNER JOIN f1_details AS det ON (race.rnd = det.no)
INNER JOIN driver ON (race.driver = driver.no)
INNER JOIN country ON (race.gp = country.gp)
INNER JOIN team ON (race.team = team.no)
WHERE race.finish = 1
AND MONTH(det.dat) = $month
AND DAY(det.dat) = $day");
	
if (mysqli_num_rows($gps) > 0) {
	$results = array();
	/*
	$results (
		'yr' =>
		'short' =>
		'name' =>
		'winner' =>
			no =>
			no => Ha 2 győztes van(megosztott)
		'team' (mivel ez ugyanaz)
	)*/
	
	while ($row = mysqli_fetch_array($gps)) {
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		$link = flag($row['country']).driver_link($row['driverid'], $name);
		$team = flag($row['teamcountry']).team_link($row['teamid'], $row['teamname']);
		
		$gp   = $row['yr'].$row['gp'];
		$short= $row['gp'];
		$yr   = $row['yr'];
		$name = $row['gpname'];
		$no   = $row['raceno'];
		
		$results[$gp]['yr'] = $yr;
		$results[$gp]['short'] = $short;
		$results[$gp]['name'] = $name;
		$results[$gp]['winner'][$no] = $link;
		$results[$gp]['team'] = $team;
	}
	
	echo '<table class="results">';
	echo '<tr><th>Grands Prix</th><th>Winner</th><th>Team</th></tr>';
	foreach ($results as $gp) {
		echo '<tr>';
			$name = $gp['yr'] . ' ' . $gp['name'] . ' GP';
		echo '<td>'.flag($gp['short']).race_link($gp['yr'], $gp['short'], $name).'</td>';
		echo '<td>'.implode($gp['winner'], '<br>').'</td>';
		echo '<td>'.$gp['team'].'</td>';
		echo '</tr>';
	}
	echo '</table><br>';
}
//
echo '<table class="results">';
$cur_yr = date('Y');
/* DÁTUMOK ELLENŐRZÉSÉIG KISZEDTEM
// Születések
$births = mysqli_query($f1db,
	"SELECT driver.id, driver.country, YEAR(driver.birth) AS yr,
		driver.first, driver.de, driver.last, driver.sr
	FROM driver
	WHERE DAY(`birth`) = $day
	AND MONTH(`birth`) = $month
	ORDER BY birth ASC");
	
if (mysqli_num_rows($births) > 0) {
	echo '<th colspan="2">Drivers born</th>';
	
	while ($driver = mysqli_fetch_array($births)) {
		$name = name($driver['first'], $driver['de'], $driver['last'], $driver['sr']);
		
		echo '<tr>';
		echo '<td>'.flag($driver['country']);
		echo driver_link($driver['id'], $name).'</td>';
		echo '<td>'.$driver['yr'].' ('.($cur_yr - $driver['yr']).')</td>';
		echo '</tr>';
	}
}

// Halálozások
$deaths = mysqli_query($f1db,
	"SELECT driver.id, driver.country, YEAR(driver.death) AS yr,
		driver.first, driver.de, driver.last, driver.sr
	FROM driver
	WHERE DAY(`death`) = $day
	AND MONTH(`death`) = $month
	ORDER BY death ASC");
	
if (mysqli_num_rows($deaths) > 0) {
	echo '<th colspan="2">Drivers died</th>';
	
	while ($died = mysqli_fetch_array($deaths)) {
		$name = name($died['first'], $died['de'], $died['last'], $died['sr']);
		
		echo '<tr>';
		echo '<td>'.flag($died['country']);
		echo driver_link($died['id'], $name).'</td>';
		echo '<td>'.$died['yr'].' ('.($cur_yr - $died['yr']).')</td>';
		echo '</tr>';
	}
}

if (mysqli_num_rows($gps) == 0 && mysqli_num_rows($births) == 0 && mysqli_num_rows($deaths) == 0) {
	echo 'Nothing has happened today! :D';
}
*/

// Egész vége
echo '</table>';
?>