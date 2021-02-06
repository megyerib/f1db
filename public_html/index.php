<?php
include('resources/head.php');

// Visszaszámláló

ob_start();

$next = mysqli_query($f1db,
	"SELECT country.name, det.tme, det.name AS event, det.type, det.num,
		gp.yr, gp.gp
	FROM f1_gp_schedule AS det
	INNER JOIN f1_gp AS gp
	ON det.rnd = gp.no
	INNER JOIN country
	ON gp.gp = country.gp
	WHERE tme > ((NOW() - INTERVAL 90 MINUTE))
	ORDER BY det.tme, det.no ASC
	LIMIT 1");
if (mysqli_num_rows($next) > 0) { // Van esemény beállítva
$next = mysqli_fetch_array($next);
$date = date('M d, Y H:i:s', strtotime($next['tme']));

if (!empty($next['event'])) {
	$event_name = $next['event'];
}
else {
	$event = $next['type'];
	switch ($event) {
		case 'P':
			$event_name = 'Practice '.$next['num'];
			break;
		case 'Q':
			$event_name = 'Qualifying';
			break;
		case 'R':
			$event_name = 'Race';
			break;
	}
}

$gp_name = $next['name'].' GP';
$precise = true;
} // Van beállítva vége
else { // Nincs idő beállítva
	$next = mysqli_query($f1db,
		"SELECT country.gp, country.name, f1_gp.yr
		FROM f1_gp
		INNER JOIN country ON f1_gp.gp = country.gp
		WHERE no > (SELECT MAX(rnd) FROM f1_race WHERE finish = 1)
		ORDER BY no ASC
		LIMIT 1" // Info2 bitch! :D
	);
	$next = mysqli_fetch_array($next);
	
	$gp_name = $next['name'].' GP';
}
// Megjelenítés
// Cím
echo '<p style="font-family:\'Russo One\'; text-align: center; margin:0; font-size:24px;">Next event</span><br>';

// Hátralévő idő
if (isset ($precise)) {
echo '<span id="countdown" style="font-size:44px;" style="font-size:24px;">loading...</span><br>';
echo '<script>
    var target_date = new Date("'.$date.'").getTime(); // Date

    var days, hours, minutes, seconds;
    var countdown = document.getElementById("countdown");

    setInterval(function () {
        var current_date = new Date().getTime();
        var seconds_left = (target_date - current_date) / 1000;
        days = parseInt(seconds_left / 86400);
        if (days > 0) {
            days = days + "d, ";
        } else {
            days = "";
        }
        seconds_left = seconds_left % 86400;
        hours = parseInt(seconds_left / 3600);
        seconds_left = seconds_left % 3600;
        if (hours < 10) {
            hours = "0" + hours;
        }
        minutes = parseInt(seconds_left / 60);
        if (minutes < 10) {
            minutes = "0" + minutes;
        }
        /*seconds = parseInt(seconds_left % 60);
        if (seconds < 10) {
            seconds = "0" + seconds;
        }*/

        if (target_date > current_date) {
            countdown.innerHTML = days + hours + ":" + minutes;
        } else {
            countdown.innerHTML = "LIVE!";
        }
    }, 1000);
</script>';
}

echo '<span style="text-align:center; font-family:\'Russo One\'; font-size:16px;" id="indexCountdown">';
$flag = '<img src="/img/flag/big/'.$next['gp'].'.png" style="position:relative; top:6px; height:21px;" alt="'.$next['name'].'">';
echo $flag.' '.linkRace($next['yr'], $next['gp'], $gp_name);

if (isset ($precise)) {
	echo ' - '.$event_name;
}
echo '</span></p>';

$nextEvent = ob_get_contents();
ob_end_clean();

// Pilóták állása
ob_start();

	$yr = actual;
	$driver_list = mysqli_query($f1db,
		"SELECT *
		FROM f1_tbl AS tbl
		INNER JOIN driver
		ON tbl.driver = driver.no
		WHERE yr = $yr
		AND place <= 10
		ORDER BY place ASC");
	echo '<table class="standing" width="100%">';
	echo '<tr><th style="width:20px;">#</th><th style="width:170px;">Driver</th><th colspan="2">Score</th></tr>';
	while ($row = mysqli_fetch_array($driver_list)) {
		if ($row['place'] > 1) {
			$gap   = '+'.($prev - $row['score'] + 0);
		} else {
			$gap   = '';
		}
		$prev = $row['score'];
		echo '<tr>';
		echo '<td style="text-align:center;" class="place">'.$row['place'].'</td>';
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		echo '<td>'.flag($row['country']).linkDriver($row['id'], $name).'</td>';
		echo '<td>'.($row['score']+0).'</td>';
		echo '<td>'.$gap.'</td>';
		echo '</tr>';
	}
	echo '</table>';
$driversStanding = ob_get_contents();
ob_end_clean();

// Konstruktőri állása
ob_start();
$driver_list = mysqli_query($f1db,
		"SELECT *,
			chassis.fullname AS chassis_name, chassis.id AS ch_id, chassis.country AS ch_country,
			engine.fullname AS engine_name, engine.id AS en_id
		FROM f1_tbl_cons AS tbl
		INNER JOIN team AS chassis
		ON tbl.chassis = chassis.no
		INNER JOIN team AS engine
		ON tbl.engine = engine.no
		WHERE yr = $yr
		AND place <= 10
		ORDER BY place ASC");
	echo '<table class="standing" width="100%">';
	echo '<tr><th style="width:20px;">#</th><th style="width:170px;">Constructor</th><th colspan="2">Score</th></tr>';
	while ($row = mysqli_fetch_array($driver_list)) {
		if ($row['place'] > 1) {
			if ($row['score'] > 0) {
				$gap   = '+'.($prev - $row['score'] + 0);
			}
			else {
				$gap = '';
			}
		} else {
			$gap   = '';
		}
		$prev = $row['score'];
		echo '<td style="text-align:center;" class="place">'.$row['place'].'</td>';
		if ($row['ch_id'] != $row['en_id']) {
			$link = flag($row['ch_country']).linkTeam($row['ch_id'], $row['chassis_name']).' - '.linkEngineCons($row['en_id'], $row['engine_name']);
		}
		else {
			$link = flag($row['ch_country']).linkTeam($row['ch_id'], $row['chassis_name']);
		}
		echo '<td>'.$link.'</td>';
		echo '<td>'.($row['score']+0).'</td>';
		echo '<td>'.$gap.'</td>';
		echo '</tr>';
	}
	echo '</table>';
$constructorsStanding = ob_get_contents();
ob_end_clean();

// Mai nap
ob_start();

$time = localtime();
$month = $time[4] + 1;
$day   = $time[3];

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
	
	$results = array();
	
	while ($row = mysqli_fetch_array($gps)) {
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		$link = flag($row['country']).linkDriver($row['driverid'], $name);
		$team = flag($row['teamcountry']).linkTeam($row['teamid'], $row['teamname']);
		
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
	
	echo '<table class="info" width="100%">';
	echo '<tr><th width="40%">GP</th><th width="40%">Winner</th><th width="30%">Team</th></tr>';
	foreach ($results as $gp) {
		echo '<tr>';
			$name = $gp['yr'] . ' ' . $gp['name'] . ' GP';
		echo '<td>'.flag($gp['short']).linkRace($gp['yr'], $gp['short'], $name).'</td>';
		echo '<td>'.implode($gp['winner'], '<br>').'</td>';
		echo '<td>'.$gp['team'].'</td>';
		echo '</tr>';
	}
	if (mysqli_num_rows($gps) == 0) {
		echo '<tr><td colspan="3">No GP was held on '.date("j F").'</td></tr>';
	}
	echo '</table>';

$todayInF1 = ob_get_contents();
ob_end_clean();

// Születések
ob_start();
$births = mysqli_query($f1db,
	"SELECT driver.id, driver.country, YEAR(driver.birth) AS yr,
		driver.first, driver.de, driver.last, driver.sr
	FROM driver
	WHERE DAY(`birth`) = $day
	AND MONTH(`birth`) = $month
	ORDER BY birth ASC");
	
if (mysqli_num_rows($births) > 0) {
	echo '<table class="info" width="100%">';
	echo '<tr><th width="70%">Driver</th><th>Born</th></tr>';
	while ($driver = mysqli_fetch_array($births)) {
		$name = name($driver['first'], $driver['de'], $driver['last'], $driver['sr']);
		
		echo '<tr>';
		echo '<td>'.flag($driver['country']);
		echo linkDriver($driver['id'], $name).'</td>';
		echo '<td>'.$driver['yr'].' ('.(date('Y') - $driver['yr']).')</td>';
		echo '</tr>';
	}
	if (mysqli_num_rows($births) == 0) {
		echo '<tr><td colspan="2">No driver was born on '.date("j F").'</td></tr>';
	}
	echo '</table>';
}
$driversBorn = ob_get_contents();
ob_end_clean();

// Halálozások
ob_start();
$deaths = mysqli_query($f1db,
	"SELECT driver.id, driver.country, YEAR(driver.death) AS yr,
		driver.first, driver.de, driver.last, driver.sr
	FROM driver
	WHERE DAY(`death`) = $day
	AND MONTH(`death`) = $month
	ORDER BY birth ASC");
	
	echo '<table class="info" width="100%">';
	echo '<tr><th width="70%">Driver</th><th>Died</th></tr>';
	
	while ($died = mysqli_fetch_array($deaths)) {
		$name = name($died['first'], $died['de'], $died['last'], $died['sr']);
		
		echo '<tr>';
		echo '<td>'.flag($died['country']);
		echo driver_link($died['id'], $name).'</td>';
		echo '<td>'.$died['yr'].' ('.($cur_yr - $died['yr']).')</td>';
		echo '</tr>';
	}
	if (mysqli_num_rows($deaths) == 0) {
		echo '<tr><td colspan="2">No driver was died on '.date("j F").'</td></tr>';
	}
	echo '</table>';
$driversDied = ob_get_contents();
ob_end_clean();

?>



<!-- Here is where content begins -->
	<div class="frame">
	<!-- Right bar -->
	<div style="float:right;">
		<div class="single" style="color:white; background-color:#004687"><?php echo $nextEvent ?></div>
		<h2>Drivers standing</h2>
		<div class="singleFull"><?php echo $driversStanding ?></div>
		<p><a href="/f1/<?php echo actual ?>#drivers_standing">All</a></p>
		<h2>Constructors' standing</h2>
		<div class="singleFull"><?php echo $constructorsStanding ?></div>
		<p><a href="/f1/<?php echo actual ?>#constructors_standing">All</a></p>
	</div>
	
	<!-- Welcome -->
	<div class="double"><h2 style="margin-top:0;">Welcome!</h2>Yo bitchez!</div>
	
	<!-- This day -->
	<h2>This day in F1</h2>
	<div class="doubleFull"><?php echo $todayInF1 ?></div>
	<div class="doubleFull" style="box-shadow:none;">
		<div style="float:right;">
			<h2 style="margin-top:0;">Drivers died</h2>
			<div class="singleFull" style="margin:0"><?php echo $driversDied ?></div>
		</div>
		<h2>Drivers born</h2>
		<div class="singleFull" style="margin:0">
			<?php echo $driversBorn ?>
		</div>
	</div>
	
	


<?php
include('resources/foot.php');
?>
