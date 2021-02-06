<?php
// Mindennek alul van margója!!!
// Sok a névütközés
require_once('included/vars.php');
require_once('included/database.php');
require_once('included/functions/functions.php');

$driver_id = $_GET['driver'];

$query = mysqli_query($f1db,
	"SELECT * 
	FROM driver
	INNER JOIN country
	ON (driver.country = country.gp)
	WHERE driver.id = '$driver_id'
	LIMIT 1");
	
// Ha nincs
if (mysqli_num_rows($query) == 0) {
	header('Location: /driver');
}
	
$drvr = mysqli_fetch_array($query);

$driver_no = $drvr['no'];

$country = $drvr['gp'];   // 3 betűs
$nationality  = $drvr['name']; // Nemzetiség
if  ($nationality == 'United States') {
	$nationality = 'American';
}
	$pagetitle = $driver_name = name($drvr['first'], $drvr['de'], $drvr['last'], $drvr['sr']);
		
	/*Fejléc*/require_once('included/head.php');

	// CÍM
	$active = mysqli_query($f1db, // Aktív
		"SELECT *
		FROM f1_active_driver AS active
		INNER JOIN team
		ON active.team = team.no
		WHERE active.no = $driver_no
		LIMIT 1");
	
	if (mysqli_num_rows($active) > 0) {
		$act = mysqli_fetch_array($active);
		$cur_car_no = '<p style="font-size:54px; margin:0px;margin-right:15px; font-weight:bold;">'.$act['car_no'].'</p>';
		echo '<div><span style="float: left; display: block;">'.$cur_car_no.'</span>';
		echo '<h1 style="margin:0px; padding-bottom:15px;">'.$driver_name.'<br>'.$act['fullname'].'</h1></div>';
	}
	else {
		// 500
		$_500 = '';
		if ($drvr['i500'] == 1) {
			$_500 = '<img src="/images/icon/500.png" height="34" style="position:relative; top:5px; left:15px;" title="Participate only in Indianapolis 500 races">';
		}
		echo '<h1 style="margin:0px; padding-bottom:15px;">'.$driver_name.$_500.'</h1>';
	} // oldalcím
	
// Jobb oldali táblázat

// Kép
	$img = '/images/driver/' . $driver_id . '.jpg';
	$img_path = $_SERVER['DOCUMENT_ROOT'].'/images/driver/' . $driver_id . '.jpg';
	$img_path2 = $_SERVER['DOCUMENT_ROOT'].'/images/driver/' . $driver_id . '.JPG'; //Case sensitive
	if (file_exists($img_path2)) { // Nem a legjobb megoldás
		$img_path = $img_path2;
		$img = '/images/driver/' . $driver_id . '.JPG';
	}
	if (file_exists($img_path)) {
	echo '<div class="right" style="padding:0px; width:300px;">';
	echo '<div class="img_r">';
	echo '<img style="width:300px;" src="' . $img . '">';
	echo '<span>';
	$exif = exif_read_data($img_path);
	if (isset($exif['Artist'])) {
		echo $exif['Artist'];
	}
	echo '</span>';
	echo '</div>';
	echo '</div>';
	}
echo '<div class="right">';

// Nemzetiség	
echo '<b>Nationality</b>: '.flag($country).country_link($country, $nationality).'<br>';
	
// Született/meghalt
$borndied = mysqli_query($f1db,
	"SELECT birth, death
	FROM driver
	WHERE no = $driver_no");
	
$bd = mysqli_fetch_array($borndied);
	
$born = $bd['birth'];
$died = $bd['death'];

if ($died == 0) { // ÉL
	echo '<b>Born</b>: '.date('j F, Y', strtotime($born)).' (age '.age($born, $died).')<br>';
}
else { // MEGHALT
	echo '<b>Born</b>: '.date('j F, Y', strtotime($born)).'<br>';
	echo '<b>Died</b>: '.date('j F, Y', strtotime($died)).' (age '.age($born, $died).')<br>';
}
echo '</div>';
	
// KARRIER //
echo '<div class="right">';
	echo '<div class="thead" id="driver">F1 career</div>';

// Aktív évek
	$activeyrs = mysqli_query($f1db,
		"SELECT DISTINCT yr
		FROM f1_tbl
		WHERE driver = $driver_no");
		
	$active = array();

	while ($ctv = mysqli_fetch_array($activeyrs)) {
		array_push($active, $ctv['yr']);
	}

	echo '<b>Active years</b>: ';	
	echo intervals($active);

// Nagydíjak
	$gps = mysqli_query($f1db,
		"SELECT COUNT(rnd) AS count
		FROM f1_race
		WHERE driver = $driver_no");

	echo '<p>';	
	$gps = mysqli_fetch_array($gps);
	echo '<b>GPs</b>: ' . $gps['count'] . '</br>';
	
// Rajtok
	$starts = mysqli_query($f1db,
		"SELECT COUNT(rnd) AS count
		FROM f1_race
		WHERE driver = $driver_no
		AND status <= 3");
	

	$starts = mysqli_fetch_array($starts);
	echo '<b>Starts</b>: ' . $starts['count'] . '</br>';
	echo '</p>';
	
// Első nagydíj (egy lekérdezés?)
$first = mysqli_query($f1db,
	"SELECT gps.yr, gps.gp, country.name
	FROM f1_race AS race
	INNER JOIN f1_gp AS gps
	ON (race.rnd = gps.no)
	INNER JOIN country
	ON (gps.gp = country.gp)
	WHERE race.driver = $driver_no
	ORDER BY race.rnd ASC
	LIMIT 1");
	
$frst = mysqli_fetch_array($first);

echo '<p>';
$gpname = $frst['yr'] . ' ' . $frst['name'] . ' GP';
echo '<b>First race</b>: '.race_link($frst['yr'], $frst['gp'], $gpname).'</br>';

// Utolsó nagydíj
$last = mysqli_query($f1db,
	"SELECT gps.yr, gps.gp, country.name
	FROM f1_race AS race
	INNER JOIN f1_gp AS gps
	ON (race.rnd = gps.no)
	INNER JOIN country
	ON (gps.gp = country.gp)
	WHERE race.driver = $driver_no
	ORDER BY race.rnd DESC
	LIMIT 1");
	
$lst = mysqli_fetch_array($last);

$gpname = $lst['yr'] . ' ' . $lst['name'] . ' GP';
echo '<b>Last race</b>: '.race_link($lst['yr'], $lst['gp'], $gpname).'</p>';

// Dobogó

$podium = mysqli_query($f1db,
	"SELECT finish, COUNT(finish) AS count
	FROM f1_race
	WHERE driver = $driver_no
	AND status = 1
	AND finish <= 3
	GROUP BY finish");
	
if (mysqli_num_rows($podium) > 0) { // Van dobogó
	$plcs = array (1 => '',
		2 => '',
		3 => '');	
	
	while ($pod = mysqli_fetch_array($podium)) {
		$pos = $pod['finish'];
		$plcs[$pos] = $pod['count'];
	}
	
	echo '<center><table style="text-align:center;"><tr>';
	echo '<td width="72" class="podium" style="top:15px;">' . $plcs[2] . '</td>';
	echo '<td width="72" class="podium" >' . $plcs[1] . '</td>';
	echo '<td width="72" class="podium" style="top:30px;">' . $plcs[3] . '</td>';
	echo '</tr></table>';

	echo '<img src="/images/podium.png"></center>';
}
else {
	$best = mysqli_query($f1db,
		"SELECT MIN(finish) AS min
		FROM f1_race
		WHERE driver = $driver_no
		AND status = 1");
		
	$best = mysqli_fetch_array($best);
	$bestplace = $best['min'];
	if ($bestplace > 0) {
		echo '<b>Best place</b>: '.$bestplace.'</br>';
	}
}

// Pole
$poles = mysqli_query($f1db,
	"SELECT COUNT(*) AS count
	FROM f1_q AS q
	INNER JOIN f1_race AS race
	ON q.entr_no = race.no
	WHERE race.driver = '$driver_no'
	AND q.place = 1");
	
$poles = mysqli_fetch_array($poles);
$poles = $poles['count'];
if ($poles > 0) {
	echo '<p><b>Poles</b>: '.$poles.'</p>';
}

// Később hozzá jön a q3 is
	
// Pontszerző
$scored = mysqli_query($f1db,
	"SELECT COUNT(rnd) AS count, SUM(score) AS sum
	FROM f1_race
	WHERE driver = $driver_no
	AND score > 0");

$scrd = mysqli_fetch_array($scored);
$scoredplaces = $scrd['count'];

if ($scoredplaces > 0) {
	echo '<p><b>Scored</b>: ' . $scoredplaces . ' times</br>';
	
	$sumscore = $scrd['sum'] + 0; // Tizedesjegyek le
	echo '<b>Total career score</b>: '.$sumscore.'</br>';
}
// Körök
echo '<p><b>Laps driven</b>: ';

$laps = mysqli_query($f1db,
	"SELECT SUM(laps) as sum
	FROM f1_race
	WHERE driver = $driver_no");
	
$laps = mysqli_fetch_array($laps);
$total_laps = $laps['sum'];
echo $total_laps.'</p>';

// Teams
/////////////////////// Ha VB, akkor félkövér
	$teams = mysqli_query($f1db,
		"SELECT DISTINCT
		GREATEST(yr, team) AS yr,
		LEAST(team, yr) AS team,
		team.fullname, team.id
		FROM f1_race AS race
		INNER JOIN team
		ON (team = team.no)
		WHERE driver = $driver_no
		ORDER BY rnd ASC");

	$tms = array();
	$teamname = array();
	$i = 0;
	while ($drvr = mysqli_fetch_array($teams)) {
		$tms[$drvr['id']][$i] = $drvr['yr'];
		$teamname[$drvr['id']] = $drvr['fullname'];
		$i++;
	}
	
	foreach ($teamname as $id => $name) {
		echo team_link($id, $name);
		echo ' ('.intervals($tms[$id]).')<br>';
	}
// Táblázat vége
echo '</div>';

  //////////////
 // BEVEZETŐ //
//////////////

// VB trófeák
$act = actual;
$champion = mysqli_query($f1db,
	"SELECT yr
	FROM f1_tbl
	WHERE driver = $driver_no
	AND place = 1
	AND yr < $act
	ORDER BY yr ASC");
		
if (mysqli_num_rows($champion) == 1) {
	echo '<table style="font-size:18px;">';
	echo '<tr><td><img src="/images/trophy.png" height="120"></td></tr>';
	$yr = mysqli_fetch_array($champion);
	echo '<tr><td align="center">'.$yr['yr'].'</td><tr>';
	echo '</table><br />';
}
if (mysqli_num_rows($champion) > 1) {
	echo '<table style="font-size:18px;"><tr>';
	echo '<td width="70"><img src="/images/trophy.png" height="120"></td>';
	
	echo '<td><table><tr valign="top"><td width="50">';
	$i = 1;
	while ($yr = mysqli_fetch_array($champion)) {
		echo $yr['yr'].'<br>';
		if ($i < 5) {
			$i++;
		}
		else {
			echo '</td><td width="50">';
			$i = 1;
		}
	}
	echo '</td></tr></table></td>';
	
	echo '</tr></table><br />';
}

// Születésnap
if ($died == 0) {
	$birthday = birthday($born);
	if ($birthday > 0) {
		echo '<span class="birthday">';
		echo '<img src="/images/icon/cake.png" height="30" style="position:relative; top:5px; padding-right:4px;">';
		echo 'Happy '.ordinal($birthday).' birthday!';
		echo '</span>';
	}
}

// SOCIAL MEDIA
$medias = mysqli_query($f1db,
	"SELECT *
	FROM social_media
	WHERE subj_type = 'D'
	AND subj = $driver_no
	ORDER BY type ASC");
if (mysqli_num_rows($medias) > 0) {
	echo '<h2>Social media</h2>';
	while ($drvr = mysqli_fetch_array($medias)) {
		media_list($drvr['type'], $name = $drvr['name']);
	}
}

  ///////////
 // AKTÍV //
///////////

$active = mysqli_query($f1db,
	"SELECT *
	FROM f1_active_driver AS active
	INNER JOIN team
	ON active.team = team.no
	WHERE active.no = $driver_no
	LIMIT 1");

if (mysqli_num_rows($active) > 0) {
	echo '<h2>Current season</h2>';
	
	$ctv = mysqli_fetch_array($active);
	$team_no = $ctv['team'];
	$car_no = $ctv['car_no'];
	
	$active = mysqli_query($f1db,
		"SELECT *
		FROM f1_active_team
		WHERE no = $team_no");
	
	$active = mysqli_fetch_array($active);
	
	$engine  = $active['engine'];
	$chassis = $active['chassis'];
	$tyre    = $active['tyre'];
	
	// Kasztni
	$chassis = mysqli_query($f1db,
		"SELECT *, chassis.no AS ch_no
		FROM chassis
		INNER JOIN team ON chassis.cons = team.no
		WHERE chassis.no = $chassis
		LIMIT 1");
	
	$chassis = mysqli_fetch_array($chassis);
	$chassis = chassis_link($chassis['id'], $chassis['ch_no'], chassis_name($chassis['fullname'], $chassis['type']));
	// Motor
	$engine = mysqli_query($f1db,
		"SELECT *, engine.no AS eng_no
		FROM engine
		INNER JOIN team ON engine.cons = team.no
		WHERE engine.no = $engine
		LIMIT 1");
	
	$engine = mysqli_fetch_array($engine);
	$name = engine_name($engine['fullname'], $engine['type'], $engine['volume'], $engine['concept'], $engine['cylinders'], $engine['turbo']);
	$engine = engine_link($engine['id'], $engine['eng_no'], $name);
	
	// Gumi
	$tyre = mysqli_query($f1db,
		"SELECT *
		FROM tyre
		WHERE id = '$tyre'
		LIMIT 1");
	$tyre = mysqli_fetch_array($tyre);
	$tyre = tyre_link($tyre['id'], $tyre['fullname']);
	
	// Kiírás	
	echo '<div class="box">';
	echo '<table>';
	echo '<tr>';
		echo '<td rowspan="5" style="padding-right:15px;" align="center" valign="center">';
		// Kép
		$img = '/images/team/' . $ctv['id'] . '.png';
		$img_path = $_SERVER['DOCUMENT_ROOT'].'/images/team/' . $ctv['id'] . '.png';
	
		if (file_exists($img_path)) {
			echo '<img src="' . $img . '" style="max-height:90px; max-width:250px;">';
		}	
		echo '</td>';
		echo '<td colspan="2" style="font-weight:bold; font-size:200%">'.$ctv['fullname'].'</td>';
	echo '</tr>';
	echo '<tr><td style="padding-right:10px; font-weight:bold;">Chassis</td><td>'.$chassis.'</td></tr>';
	echo '<tr><td style="padding-right:10px; font-weight:bold;">Engine</td><td>' .$engine. '</td></tr>';
	echo '<tr><td style="padding-right:10px; font-weight:bold;">Tyres</td><td>'  .$tyre.   '</td></tr>';
	echo '<tr><td style="padding-right:10px; font-weight:bold;">Car #</td><td>'  .$car_no. '</td></tr>';
	echo '</table>';
	echo '</div>';
}

// Nyert, nem nyert (hatékonyság?)
$all = mysqli_query($f1db,
	"SELECT DISTINCT(race.gp), country.name
	FROM f1_race AS race
	INNER JOIN country
	ON race.gp = country.gp
	WHERE race.driver = $driver_no");

$won_in = array();	
while ($drvr = mysqli_fetch_array($all)) {
	$won_in[$drvr['gp']]['v'] = 0;
	$won_in[$drvr['gp']]['name'] = $drvr['name'];
	$i++;
}

$won = mysqli_query($f1db,
	"SELECT DISTINCT(race.gp), country.name
	FROM f1_race AS race
	INNER JOIN country
	ON race.gp = country.gp
	WHERE race.driver = $driver_no
	AND race.finish = 1");
	
if (mysqli_num_rows($won) > 0) {
	echo '<h2>Wins</h2>';
	
	while ($drvr = mysqli_fetch_array($won)) {
		$won_in[$drvr['gp']]['v'] = 1;
	}
	
	echo '<table><tr>';
	echo '<td valign="top"><b>Has won</b><br />';
	$i = 0;
	foreach ($won_in as $gp => $wn) {
		if ($wn['v'] == 1) {
			echo flag($gp).gp_link($gp, $wn['name']).'<br />';
		$i++;
		}
	}
	echo '</td>';
	if ($i >= 10) {
		echo '<td valign="top" style="padding-left:10px;">';
		echo '<b>Hasn\'t won</b><br />';
		foreach ($won_in as $gp => $wn) {
			if ($wn['v'] == 0) {
				echo flag($gp).gp_link($gp, $wn['name']).'<br />';
			}
		}
		echo '</td>';
	}
	echo '</tr></table>';
}

// RAJTSZÁMOK
 
echo '<h2>Worn car numbers</h2>';
$car_numbers = mysqli_query($f1db,
	"SELECT DISTINCT
			GREATEST(yr, car_no) AS yr,
			LEAST(car_no, yr) AS car_no
	FROM f1_race
	WHERE driver = $driver_no
	AND car_no
	ORDER BY no ASC"
);
$numbers = array();
while ($row = mysqli_fetch_array($car_numbers)) {
	if(!isset($numbers[$row['car_no']])) {
		$numbers[$row['car_no']] = array();
	}
	array_push($numbers[$row['car_no']], $row['yr']);
}
echo '<table class="results">';
foreach ($numbers as $car_no => $years) {
	echo '<tr>';
	echo '<td>'.$car_no.'</td>';
	echo '<td>'.intervals($years).'</td>';
	echo '</tr>';
}
echo '</table>';

  ////////////////
 // Eredmények //
////////////////

//////////////
// TÁBLÁZAT //
//////////////
results_driver($driver_no);

echo '<script type="text/javascript">
    google.load("visualization", "1", {packages:["corechart"]});
    google.setOnLoadCallback(drawSeriesChart);

    function drawSeriesChart() {

      var data = google.visualization.arrayToDataTable(['."
        ['ID', 'Start', 'Finish', 'Difference', 'Count']";
// Rajt - cél
$sf = mysqli_query($f1db,
	"SELECT start, finish
	FROM `f1_race`
	WHERE driver = $driver_no
	AND status = 1
	AND start > 0
	AND finish > 0"
);
$cnts = array();
while ($row = mysqli_fetch_array($sf)) {
	if (isset($cnts[$row['start']][$row['finish']])) {
		$cnts[$row['start']][$row['finish']]++;
	}
	else {
		$cnts[$row['start']][$row['finish']] = 1;
	}
	//echo ",\n[".$row['start'].", ".$row['finish']."]";
}

foreach ($cnts as $start => $f) {
	foreach ($f as $finish => $cnt) {
		if ($start > $finish) {
			$diff = 'Better';
		}
		else if ($start == $finish) {
			$diff = 'Same';
		}
		else {
			$diff = 'Worse';
		}
		echo ",\n['', ".$start.", ".$finish.", '".$diff."', ".$cnt."]";
	}
}
  echo"]);

      var options = {
        title: 'Start -> Finish',
        hAxis: {title: 'Start'},
        vAxis: {title: 'Finish'},
        bubble: {textStyle: {fontSize: 11}},
		legend: 'none'
      };
	  
	  options.vAxis.direction = -1;

      var chart = new google.visualization.BubbleChart(document.getElementById('series_chart_div'));
      chart.draw(data, options);
    }
    </script>";
	echo '<div id="series_chart_div" style="width: 900px; height: 500px;"></div>';

////////////////
// HELYEZÉSEK //
////////////////
$avg = mysqli_query($f1db,
	"SELECT AVG(`start`) AS start, AVG(`finish`) AS finish
	FROM `f1_race`
	WHERE driver = $driver_no
	AND status = 1"
);
$avg = mysqli_fetch_array($avg);
$avg_start  = number_format($avg['start'], 2, '.', '');
$avg_finish = number_format($avg['finish'], 2, '.', '');;
$diff = $avg_start - $avg_finish;
$diff = $diff > 0 ? '+'.$diff : $diff;

echo "<p>Average start: $avg_start<br>Average finish: $avg_finish<br>Difference: $diff</p>";

echo '<h2>Places</h2>';
places_driver($driver_no);
echo '<h2>Qualifying places</h2>';
places_driver_qual($driver_no);
require_once('included/functions/graph.php');
//driver_results_graph($driver_no);
//driver_qual_results_graph($driver_no);
require_once('included/foot.php');
?>