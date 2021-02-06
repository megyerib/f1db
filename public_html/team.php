<?php
require_once('included/database.php');

$team_id = $_GET['team'];

$query = mysqli_query($f1db,
	"SELECT *
	FROM team 
	WHERE id = '$team_id'
	LIMIT 1");
	
// Ha nincs ilyen csapat
if (mysqli_num_rows($query) == 0) {
	header('Location: /team');
}
	
$row_team = mysqli_fetch_array($query);
$team_no = $row_team['no'];
$is_engine  = $row_team['engine'];
$is_chassis = $row_team['chassis'];

$teamname = $row_team['fullname'];

if ($row_team['longname'] != '') {
	$fullname =  $row_team['longname'];
}
else {
	$fullname =  $row_team['fullname'];
}
	// Aktív
	$active = mysqli_query($f1db,
		"SELECT no, bg_color, font_color, border_color
		FROM f1_active_team
		WHERE no = $team_no
		LIMIT 1");
	$ctv = mysqli_num_rows($active);
	
	/*// Színek
	$act = mysqli_fetch_array($active);
	if ($act['bg_color'] != $act['font_color']) {
		$title_style = 'background-color:#'.$act['bg_color'].'; color:#'.$act['font_color'].'; border-color:#'.$act['border_color'].';';
	}*/
/*Fejléc*/require_once('included/head.php');
	
	$active = $ctv ? '<span class="active">Active</span>' : '';
	echo '<h1 style="margin-top:0px;">'.$fullname.$active.'</h1>';

$img = 'images/team/'.$team_id.'.png';
	
// Jobb oldali táblázat
// Kép
echo '<div class="right">';
/*if (file_exists($img)) {
	echo '<center><img src="'.$img.'" style="max-width:280px; max-height:280px;"></center></br>';
}
else {
	echo '<div class="righthead">'.$teamname.'</div>';
}*/
echo picture($img, 'max-width:280px; max-height:280px; margin-bottom:10px;');


	
// Nagydíjak
$gps = mysqli_query($f1db,
	"SELECT DISTINCT rnd " .
	"FROM f1_race " .
	"WHERE team = " . $team_no);
	
echo '<b>GPs</b>: ' . mysqli_num_rows($gps) . '</br>';
	
// Nevezések
$entrances = mysqli_query($f1db,
	"SELECT no " .
	"FROM f1_race " .
	"WHERE team = " . $team_no);
	
echo '<b>Entrances</b>: ' . mysqli_num_rows($entrances) . '</br>';

// Rajtok
$starts = mysqli_query($f1db,
	"SELECT no " .
	"FROM f1_race " .
	"WHERE team = " . $team_no .
	" AND status <= 5"); // ???
	
echo '<b>Starts</b>: ' . mysqli_num_rows($starts) . '</br>';

// Dobogó

$podium = mysqli_query($f1db,
	"SELECT finish, COUNT(finish) AS count
	FROM f1_race
	WHERE team = $team_no
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
	
	podium($plcs[1], $plcs[2], $plcs[3]);
}
else {
	$best = mysqli_query($f1db,
		"SELECT MIN(finish) AS min
		FROM f1_race
		WHERE team = $team_no
		AND status = 1");
		
	$best = mysqli_fetch_array($best);
	$bestplace = $best['min'];
	if ($bestplace > 0) {
		echo '<b>Best place</b>: '.$bestplace.'</br>';
	}
}
// Kettős győzelem

$double = mysqli_query($f1db,
	"SELECT rnd, finish
	FROM f1_race
	WHERE team = $team_no
	AND finish <= 2
	ORDER BY rnd, finish ASC");

$dw = 0;
$prevrace = 0;
$prevplace = 0;
while ($row = mysqli_fetch_array($double)) {
	if ($prevrace == $row['rnd'] && $prevplace == 1 && $row['finish'] == 2) {
		$dw++;
	}
	
	$prevrace = $row['rnd'];
	$prevplace = $row['finish'];
}
if ($dw > 0) {
	echo '<p><b>Double wins</b>: '.$dw.'</p>';
}

echo '</div>';

/*// SOCIAL MEDIA
$medias = mysqli_query($f1db,
	"SELECT *
	FROM social_media
	WHERE subj_type = 'T'
	AND subj = $team_no
	ORDER BY type ASC");
if (mysqli_num_rows($medias) > 0) {
	echo '<div class="right">';
	echo '<div class="thead" id="driver">Social media</div>';
	while ($row = mysqli_fetch_array($medias)) {
		media_list($row['type'], $media_name = $row['name']);
	}
	echo '</div>';
}*/

// JOBB OLDAL VÉGE

// Konstruktőr
if ($is_engine + $is_chassis > 0) {
	echo 'This article is about team <b>'.$teamname.'</b>';
	echo '<ul>';
	
	if ($is_engine > 0) {
		echo '<li>';
		echo 'For engine constructor '.engine_cons_link($team_id, 'click here');
		echo '</li>';
	}
	if ($is_chassis > 0) {
		echo '<li>';
		echo 'For chassis constructor '.chassis_cons_link($team_id, 'click here');
		echo '</li>';
	}
	
	echo '</ul>';
}

// VB trófeák
$act = actual;
$champion = mysqli_query($f1db,
	"SELECT yr
	FROM f1_tbl_cons
	WHERE chassis = $team_no
	AND place = 1
	AND yr < $act
	ORDER BY yr ASC");
	
if (mysqli_num_rows($champion) == 1) {
	echo '<table style="font-size:18px;">';
	echo '<tr><td><img src="/images/trophy_cc.png" height="120"></td></tr>';
	$yr = mysqli_fetch_array($champion);
	echo '<tr><td align="center">'.$yr['yr'].'</td><tr>';
	echo '</table><br />';
}
if (mysqli_num_rows($champion) > 1) {
	echo '<table style="font-size:18px;"><tr>';
	echo '<td width="70"><img src="/images/trophy_cc.png" height="120"></td>';
	
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

  ///////////////
 // NAVIGÁCIÓ //
///////////////
/*?>
<div class="content">
<p><center><b>Content</b></center></p>
<ol>
	<li><a href="#actual">Actual season</a></li>
	<li><a href="#results">Results</a></li>
	<li><a href="#places">Places</a></li>
</ol>
</div>
<?php*/

// SOCIAL MEDIA
$medias = mysqli_query($f1db,
	"SELECT *
	FROM social_media
	WHERE subj_type = 'T'
	AND subj = $team_no
	ORDER BY type ASC");
if (mysqli_num_rows($medias) > 0) {
	echo '<h2>Social media</h2>';
	while ($row = mysqli_fetch_array($medias)) {
		media_list($row['type'], $name = $row['name']);
	}
}

  ///////////
 // AKTÍV //
///////////
if ($ctv == 1) {
	echo '<h2 id="actual">Actual season</h2>';
	
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
	echo '<tr><td style="padding-right:10px; font-weight:bold;">Chassis</td><td>'.$chassis.'</td></tr>';
	echo '<tr><td style="padding-right:10px; font-weight:bold;">Engine</td><td>' .$engine. '</td></tr>';
	echo '<tr><td style="padding-right:10px; font-weight:bold;">Tyres</td><td>'  .$tyre.   '</td></tr>';
	echo '</table>';
	echo '</div>';
	
	// Pilóták
	echo '<h2>Drivers</h2>';
	$drivers = mysqli_query($f1db,
		"SELECT *
		FROM f1_active_driver AS active
		INNER JOIN driver ON active.no = driver.no
		WHERE team = $team_no
		AND status = 'R'");
	
	echo '<p>';
	while ($row = mysqli_fetch_array($drivers)) {
		echo '<div class="box" style="margin-right:5px;">';
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		$link = flag($row['country']).driver_link($row['id'], $name);
		
		$img_link = 'images/driver/'.$row['id'];
		$style = 'max-width:195px; max-height:195px;';
		$img = picture($img_link, $style);
		
			echo '<table>';
			echo '<tr><td colspan="2" width="200" height="200" align="center" valign="center">'.$img.'</td></tr>';
			echo '<tr><td colspan="2"><hr></td></tr>';
			echo '<tr><td width="30" style="font-weight:bold;">'.$row['car_no'].'</td>';
			echo '<td>'.$link.'</td></tr>';
			echo '</table>';
		echo '</div>';
	}
	echo '</p>';
	
	// Tesztpilóták
	echo '<h2>Test drivers</h2>';
	$drivers = mysqli_query($f1db,
		"SELECT *
		FROM f1_active_driver AS active
		INNER JOIN driver ON active.no = driver.no
		WHERE team = $team_no
		AND status != 'R'");
	
	echo '<p>';
	while ($row = mysqli_fetch_array($drivers)) {
		echo '<div class="box" style="margin-right:5px;">';
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		$link = flag($row['country']).driver_link($row['id'], $name);
		
		$img_link = 'images/driver/'.$row['id'];
		$style = 'max-width:195px; max-height:195px;';
		$img = picture($img_link, $style);
		
			echo '<table>';
			echo '<tr><td colspan="2" width="200" height="200" align="center" valign="center">'.$img.'</td></tr>';
			echo '<tr><td colspan="2"><hr></td></tr>';
			echo '<tr><td width="30" style="font-weight:bold;">'.$row['car_no'].'</td>';
			echo '<td>'.$link.'</td></tr>';
			echo '</table>';
		echo '</div>';
	}
	echo '</p>';
}
  ////////////////
 // EREDMÉNYEK //
////////////////

echo '<h2 id="results">Results</h2>';
results_team($team_no);

  ////////////////
 // HELYEZÉSEK // 
////////////////

// Helyezések
echo '<h2 id="places">Places</h2>';
places_team($f1db, $team_no);
  
// Gubby találatjelző :)
if ($team_id == 'gubby') {
	echo marker_404('gubby');
}

// Gumi
echo '<h2>Used tyres</h2>';
$car_numbers = mysqli_query($f1db,
	"SELECT DISTINCT
	yr, tyre, tyre.fullname
	FROM f1_race AS race
	INNER JOIN tyre
	ON (race.tyre = tyre.id)
	WHERE team = $team_no
	ORDER BY rnd ASC"
);
$tyres = array();
$i = 0;
$prev_tyre = '';
while ($row = mysqli_fetch_array($car_numbers)) {
	if ($prev_tyre != $row['tyre']) { // Új sor
		$i++;
		$tyres[$i]['id'] = $row['tyre'];
		$prev_tyre = $row['tyre'];
		$tyres[$i]['name'] = $row['fullname'];
		$tyres[$i]['years'] = array();
	}
	array_push($tyres[$i]['years'], $row['yr']);
}
echo '<table class="results">';
foreach ($tyres as $per) {
	echo '<tr>';
	echo '<td class="tyre_'.$per['id'].'" style="font-weight:bold;">'.$per['name'].'</td>';
	echo '<td>'.intervals($per['years']).'</td>';
	echo '</tr>';
}
echo '</table>';

require_once('included/foot.php');
?>