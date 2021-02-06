<?php
require_once('included/head.php');
echo '<h1>'.$lang['ranklists'].'</h1>';
$act_yr = actual;
$mode = 'main';
if (isset($_GET['mode'])) {
	switch ($_GET['mode']) {
		case 'live':
			$mode = 'live';
			break;
			
		case 'champ':
			$mode = 'champ';
			break;
			
		case 'wins':
			$mode = 'wins';
			break;
			
		case 'podiums':
			$mode = 'podiums';
			break;
			
		case 'score':
			$mode = 'score';
			break;
		
		case 'fastest':
			$mode = 'fastest';
			break;
			
		case 'dsq':
			$mode = 'dsq';
			break;
			
		case 'ret':
			$mode = 'ret';
			break;
		
		case 'champ_row':
			$mode = 'champ_row';
			break;
		
		case 'win_row':
			$mode = 'win_row';
			break;
			
		default: $mode = 'main';
	}
}
// Alap táblázatkészítő függvény
function makelist($f1db, $query, $header3) {
	$results = array();
	
	$actives = mysqli_query($f1db,
		"SELECT driver.id
		FROM f1_active_driver AS active
		INNER JOIN driver
		ON active.no = driver.no");
	$act = array();
	while ($row = mysqli_fetch_array($actives)) {
		$act[$row['id']] = true;
	}
	
	$i = 1;
	while ($row = mysqli_fetch_array($query)) { // Nehezen karbantartható
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		if(isset($act[$row['id']])){$name='<b>'.$name.'</b>';}
		$link = flag($row['country']).driver_link($row['id'], $name);
		
		$results[$row['count']][$i] = $link;
		$i++;
	}
	
	echo '<table class="results"><th>Place</th><th>Driver</th><th>'.$header3.'</th>';
	$p = 1;
	foreach ($results as $times => $drivers) {
		$count = count($drivers);
		$i = 0;
		foreach ($drivers as $driver) {
			echo '<tr>';
			// Helyezés
			if ($i == 0) {
				echo '<td rowspan="'.$count.'" align="center">'.$p.'</td>';
			}
			// Név
			echo '<td>';
			echo $driver;
			echo '</td>';
			// Győzelem
			if ($i == 0) {
				echo '<td rowspan="'.$count.'" align="center">'.($times+0).'</td>';
			}
			echo '</tr>';
			$i++;
			$p++;
		}
	}
	echo '</table>';
}

// Main
if ($mode == 'main') {
	echo '<a href="/f1/list/live">Live rank list</a> (last 50 races)</br>';
	echo '<a href="/f1/list/champ">Championships</a></br>';
	echo '<a href="/f1/list/score">Scores</a></br>';
	echo '<a href="/f1/list/fastest">Fastest laps</a></br>';
	echo '<a href="/f1/list/wins">Wins</a></br>';
	echo '<a href="/f1/list/ret">Retirements</a></br>';
	echo '<a href="/f1/list/dsq">Disqualifications</a><br />';
	echo '<a href="/f1/list/champ_row">Consecutive championships</a><br />';
	echo '<a href="/f1/list/podiums">Podiums</a><br />';
}

// Live
if ($mode == 'live') {
echo '<h1>Live rank list</h1>';

$max = mysqli_query($f1db,
	"SELECT max(rnd) AS max
	FROM f1_race
	WHERE finish = 1");
	
$last = mysqli_fetch_array($max);
$from = $last['max'] - 50;

$list = mysqli_query($f1db,
	"SELECT SUM(race.score) AS count, race.driver,
		driver.first, driver.de, driver.last, driver.sr, driver.id, driver.country
	FROM f1_race AS race
	INNER JOIN driver
	ON (race.driver = driver.no)
	WHERE rnd > $from
	AND score > 0
    GROUP BY driver
    ORDER BY count DESC");
	
makelist($f1db, $list, 'Points');
echo '<p><b>Bold</b>: active</p>';
}

// Bajnoki címek
if ($mode == 'champ') {
	echo '<h1>Championships</h1>';
	
	$champions = mysqli_query($f1db,
		"SELECT tbl.yr, COUNT(yr) AS count,
			driver.first, driver.de, driver.last, driver.sr, driver.id, driver.country
		FROM f1_tbl AS tbl
		INNER JOIN driver
		ON (tbl.driver = driver.no)
		WHERE tbl.place = 1
		AND yr < $act_yr
		GROUP BY driver
        ORDER BY count DESC, yr ASC");
		
	makelist($f1db, $champions, 'Titles');
}

// Győzelmek
if ($mode == 'wins') {
	echo '<h1>Wins</h1>';
	
	$winners = mysqli_query($f1db,
		"SELECT race.driver, COUNT(race.no) AS count,
		driver.first, driver.de, driver.last, driver.sr, driver.id, driver.country
		FROM f1_race AS race
                INNER JOIN driver
                ON (race.driver = driver.no)
		WHERE finish = 1
		GROUP BY driver
                ORDER BY count DESC, rnd ASC");
				
	makelist($f1db, $winners, 'Wins');
}
// Dobogók
if ($mode == 'podiums') {
	echo '<h1>Podium finishes</h1>';
	
	$podium = mysqli_query($f1db,
		"SELECT race.driver, COUNT(race.no) AS count,
		driver.first, driver.de, driver.last, driver.sr, driver.id, driver.country
		FROM f1_race AS race
                INNER JOIN driver
                ON (race.driver = driver.no)
		WHERE finish <= 3
		GROUP BY driver
                ORDER BY count DESC, rnd ASC");
				
	makelist($f1db, $podium, 'Podiums');
}
// Pontok
if ($mode == 'score') {
	echo '<h1>Earned scores</h1>';
	
	$scores = mysqli_query($f1db,
		"SELECT race.driver, SUM(score) AS count,
		driver.first, driver.de, driver.last, driver.sr, driver.id, driver.country
		FROM f1_race AS race
                INNER JOIN driver
                ON (race.driver = driver.no)
		WHERE score > 0
		GROUP BY driver
                ORDER BY count DESC, rnd ASC");
				
	makelist($f1db, $scores, 'Score');
}
// Leggyorsabb kör
if ($mode == 'fastest') {
	echo '<h1>Fastest laps</h1>';
	
	$fastest = mysqli_query($f1db,
		"SELECT fastest.driver, COUNT(*) AS count,
		driver.first, driver.de, driver.last, driver.sr, driver.id, driver.country
		FROM f1_fastest AS fastest
                INNER JOIN driver
                ON (fastest.driver = driver.no)
		GROUP BY driver
                ORDER BY count DESC, rnd ASC");
				
	makelist($f1db, $fastest, 'F.laps');
}
// Kizárások
if ($mode == 'dsq') {
	echo '<h1>Disqualifications</h1>';
	
	$dsq = mysqli_query($f1db,
		"SELECT race.driver, COUNT(*) AS count,
		driver.first, driver.de, driver.last, driver.sr, driver.id, driver.country
		FROM f1_race AS race
                INNER JOIN driver
                ON (race.driver = driver.no)
		WHERE status = 5
		GROUP BY driver
                ORDER BY count DESC, rnd ASC");
				
	makelist($f1db, $dsq, 'DSQs');
}

// Kiesések
if ($mode == 'ret') {
	echo '<h1>Most retirements</h1>';
	
	$ret = mysqli_query($f1db,
		"SELECT race.driver, COUNT(*) AS count,
		driver.first, driver.de, driver.last, driver.sr, driver.id, driver.country
		FROM f1_race AS race
                INNER JOIN driver
                ON (race.driver = driver.no)
		WHERE status = 3
		GROUP BY driver
                ORDER BY count DESC, rnd ASC");
				
	makelist($f1db, $ret, 'RETs');
}

// Bajnokságok egymás után
if ($mode == 'champ_row') {
	echo '<h1>Consecutive championships</h1>';
	
	/*$last = mysqli_query($f1db,
		"SELECT MAX(yr) AS max
		FROM f1_tbl");
	
	$last = mysqli_fetch_array($last);*/
	$last = actual;
		
	$query = mysqli_query($f1db,
		"SELECT tbl.yr,
			driver.first, driver.de, driver.last, driver.sr, driver.id, driver.country
		FROM f1_tbl AS tbl
		INNER JOIN driver
		ON (tbl.driver = driver.no)
		WHERE tbl.place = 1
		AND tbl.yr < $last
        	ORDER BY yr ASC");
        
        // Karbantartásra tökéletesen alkalmatlan
        $prev_id = '';
        $prev_name = '';
        $prev_country = '';
        $in_a_row = 1;
        $results = array();
        while($row = mysqli_fetch_array($query)) {
        	$yr = $row['yr'];
        	$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
        	$country = $row['country'];
        	
        	$id = $row['id'];
        	if ($id == $prev_id && $yr == ($last - 1)) {
        		$in_a_row++;
        		$results[$in_a_row][$firstyr]['id'] = $prev_id;
        		$results[$in_a_row][$firstyr]['name'] = $name;
        		$results[$in_a_row][$firstyr]['country'] = $country;
        		$results[$in_a_row][$firstyr]['last'] = $yr;
        		$results[$in_a_row][$firstyr]['first'] = $firstyr;
        	}
        	
        	if ($id == $prev_id) {
        		$in_a_row++;
        	}
        	else if ($in_a_row > 1) {
        		$results[$in_a_row][$firstyr]['id'] = $prev_id;
        		$results[$in_a_row][$firstyr]['name'] = $prev_name;
        		$results[$in_a_row][$firstyr]['country'] = $prev_country;
        		$results[$in_a_row][$firstyr]['last'] = ($yr - 1);
        		$results[$in_a_row][$firstyr]['first'] = $firstyr;
        		
        		$in_a_row = 1;
        		$firstyr = $yr;
        	}
        	else {
        		$in_a_row = 1;
        		$firstyr = $yr;
        	}
        	$prev_id = $id;
        	$prev_name = $name;
        	$prev_country = $country;
        }
	krsort($results);
	// Táblázat
	echo '<table class="results"><th>Driver</th><th>Titles</th><th>Years</th>';
	
	foreach ($results as $times => $drivers) {
		$count = count($drivers);
		$i = 1;
		foreach ($drivers as $driver) {
			echo '<tr>';
			echo '<td>'.flag($driver['country']).driver_link($driver['id'], $driver['name']).'</td>';
			if ($i == 1) {
				echo '<td rowspan="'.$count.'" style="text-align:center;">'.$times.'</td>';
				$i = 0;
			}
			echo '<td>'.$driver['first'].' - '.$driver['last'].'</td>';
			echo '</tr>';
		}
	}
	echo '</table>';
}

// Győzelmek egymás után
if ($mode == 'win_row') {
	echo '<h1>Consecutive wins</h1>';
	
	$last = mysqli_query($f1db,
		"SELECT MAX(yr) AS max
		FROM f1_tbl");
	
	$last = mysqli_fetch_array($last);
	$last = $last['max'];
		
	$query = mysqli_query($f1db,
		"SELECT tbl.yr,
			driver.first, driver.de, driver.last, driver.sr, driver.id, driver.country, driver.active
		FROM f1_tbl AS tbl
		INNER JOIN driver
		ON (tbl.driver = driver.no)
		WHERE tbl.place = 1
        	ORDER BY yr ASC");
        
        // Karbantartásra tökéletesen alkalmatlan
        $prev_id = '';
        $prev_name = '';
        $prev_country = '';
        $in_a_row = 1;
        $results = array();
        while($row = mysqli_fetch_array($query)) {
        	$yr = $row['yr'];
        	$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
        	$country = $row['country'];
        	
        	$id = $row['id'];
        	if ($id == $prev_id && $yr == $last) {
        		$in_a_row++;
        		$results[$in_a_row][$firstyr]['id'] = $prev_id;
        		$results[$in_a_row][$firstyr]['name'] = $name;
        		$results[$in_a_row][$firstyr]['country'] = $country;
        		$results[$in_a_row][$firstyr]['last'] = $yr;
        		$results[$in_a_row][$firstyr]['first'] = $firstyr;
        	}
        	
        	if ($id == $prev_id) {
        		$in_a_row++;
        	}
        	else if ($in_a_row > 1) {
        		$results[$in_a_row][$firstyr]['id'] = $prev_id;
        		$results[$in_a_row][$firstyr]['name'] = $prev_name;
        		$results[$in_a_row][$firstyr]['country'] = $prev_country;
        		$results[$in_a_row][$firstyr]['last'] = ($yr - 1);
        		$results[$in_a_row][$firstyr]['first'] = $firstyr;
        		
        		$in_a_row = 1;
        		$firstyr = $yr;
        	}
        	else {
        		$in_a_row = 1;
        		$firstyr = $yr;
        	}
        	$prev_id = $id;
        	$prev_name = $name;
        	$prev_country = $country;
        }
	krsort($results);
	
	// Táblázat
	echo '<table class="results"><th>Driver</th><th>Titles</th><th>Years</th>';
	foreach ($results as $times => $drivers) {
		$count = count($drivers);
		$i = 1;
		foreach ($drivers as $driver) {
			echo '<tr>';
			echo '<td>'.flag($driver['country']).driver_link($driver['id'], $driver['name']).'</td>';
			if ($i == 1) {
				echo '<td rowspan="'.$count.'" style="text-align:center;">'.$times.'</td>';
				$i = 0;
			}
			echo '<td>'.$driver['first'].' - '.$driver['last'].'</td>';
			echo '</tr>';
		}
	}
	echo '</table>';
}
require_once('included/foot.php');
?>