<?php
$maintitle = 'Welcome to race-data.net F1 database!';
require_once('included/head.php');
?>
<div style="float:right; margin-left:10px;">
<?php 	
// NEXT EVENT
echo '<div class="timer" style="margin-bottom:15px;">';
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
$flag = '<img src="/images/flag/big/'.$next['gp'].'.png" height="21" alt="'.$next['name'].'">';
$precise = true;
} // Van beállítva vége
else { // Nincs idő beállítva
	$cur_yr = date("Y");
	$next2 = mysqli_query($f1db,
		"SELECT MAX(rnd) AS max
		FROM f1_race
		WHERE yr >= $cur_yr
		AND finish = 1"
	);
	$rnd = mysqli_fetch_array($next2);
	$rnd = $rnd['max'];
	$next = mysqli_query($f1db,
		"SELECT country.gp, country.name, f1_gp.yr
		FROM f1_gp
		INNER JOIN country ON f1_gp.gp = country.gp
		WHERE no > $rnd
		ORDER BY no ASC
		LIMIT 1"
	);
	$next = mysqli_fetch_array($next);
	
	$gp_name = $next['name'].' GP';
	$flag = '<img src="/images/flag/big/'.$next['gp'].'.png" height="21" alt="'.$next['name'].'">';
}
// Megjelenítés

echo 'Next event: '.$flag.' '.race_link($next['yr'], $next['gp'], $gp_name);
if (isset ($precise)) {
echo ' - '.$event_name;
echo '<br><span id="countdown">loading...</span>';
echo '<script>
    var target_date = new Date("'.$date.'").getTime(); // Date

    var days, hours, minutes, seconds;
    var countdown = document.getElementById("countdown");

    setInterval(function () {
        var current_date = new Date().getTime();
        var seconds_left = (target_date - current_date) / 1000;
        days = parseInt(seconds_left / 86400);
        if (days > 0) {
            days = days + " days, ";
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
        seconds = parseInt(seconds_left % 60);
        if (seconds < 10) {
            seconds = "0" + seconds;
        }

        if (target_date > current_date) {
            countdown.innerHTML = "in " + days + hours + ":" + minutes + ":" + seconds;
        } else {
            countdown.innerHTML = "LIVE!";
        }
    }, 1000);
</script>';
}
echo '</div>'; // Next event vége

	require_once('today.php');
	
	// DRIVERS STANDING
	echo '<h2>Drivers standing</h2>';
	$yr = actual;
	$driver_list = mysqli_query($f1db,
		"SELECT *
		FROM f1_tbl AS tbl
		INNER JOIN driver
		ON tbl.driver = driver.no
		WHERE yr = $yr
		AND place <= 10
		ORDER BY place ASC");
	echo '<table class="results">';
	echo '<tr><th style="width:20px;">#</th><th style="width:170px;">Driver</th><th colspan="2">Score</th></tr>';
	while ($row = mysqli_fetch_array($driver_list)) {
		if ($row['place'] > 1) {
			$gap   = '+'.($prev - $row['score'] + 0);
		} else {
			$gap   = '';
		}
		$prev = $row['score'];
		echo '<tr>';
		echo '<td style="text-align:center;">'.$row['place'].'</td>';
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		echo '<td>'.flag($row['country']).driver_link($row['id'], $name).'</td>';
		echo '<td>'.($row['score']+0).'</td>';
		echo '<td>'.$gap.'</td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '<p><a href="/f1/'.actual.'#drivers_standing">All</a></p>';
	
	// CONSTRUCTOSR STANDING
	echo '<h2>Constructors standing</h2>';
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
	echo '<table class="results">';
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
		echo '<td style="text-align:center;">'.$row['place'].'</td>';
		if ($row['ch_id'] != $row['en_id']) {
			$link = flag($row['ch_country']).team_link($row['ch_id'], $row['chassis_name']).' - '.engine_cons_link($row['en_id'], $row['engine_name']);
		}
		else {
			$link = flag($row['ch_country']).team_link($row['ch_id'], $row['chassis_name']);
		}
		echo '<td>'.$link.'</td>';
		echo '<td>'.($row['score']+0).'</td>';
		echo '<td>'.$gap.'</td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '<p><a href="/f1/'.actual.'#constructors_standing">All</a></p>';
?>
</div>

<h2 style="margin-top:0px;">Hi there!</h2>
My site is in beta phase, so I'm constantly adding many-many features to it. If you've found some bugs, mistakes in the datas os just have a suggestion, please let me know of it.<br>
Subscribe for updates on Facebook, Twitter or Rss. Thanks!
<?php
// BLOG
echo '<h2>Latest news</h2>';
$news = mysqli_query($sdb,
	"SELECT *
	FROM blog
	WHERE public = 1
	ORDER BY time DESC
	LIMIT 5");
echo '<hr>';
while ($row = mysqli_fetch_array($news)) {
	echo '<h2>'.$row['title'].'</h2>';
	echo $row['head'];
	echo ' <a href="/news/'.$row['id'].'">Read more</a>';

}
$pagetitle = 'Index';
require_once('included/foot.php');
?>