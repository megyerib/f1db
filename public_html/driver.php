<?php
include('resources/head.php');
$driverId = $_GET['driver'];

$driverQuery = mysqli_query($f1db,
	"SELECT *, country.name AS nat, country.gp AS nat_short
	FROM driver
	INNER JOIN country
	ON (driver.country = country.gp)
	WHERE driver.id = '$driverId'
	LIMIT 1"
);
	
// Ha nincs
if (mysqli_num_rows($driverQuery) == 0) {
	header('Location: /driver');
}

$driverQuery = mysqli_fetch_array($driverQuery);
$driverName  = name($driverQuery['first'], $driverQuery['de'], $driverQuery['last'], $driverQuery['sr']);
$driverNo    = $driverQuery['no'];
$driverbirth  = $driverQuery['birth'];
$driverdeath  = $driverQuery['death'];
$driverNat   = $driverQuery['nat'];

// Fénykép
ob_start();

echo img("img/driver/$driverId", 'width:292px;');

$driverPhoto = ob_get_contents();
ob_clean();

// Logo
ob_start();

$logoImg = img("img/driverLogo/$driverId.png", 'max-width:260px; max-height:260px;');

if ($logoImg) {
	echo '<div style="text-align:center; padding:10px;">'.$logoImg.'</div>';
}

$driverLogo = ob_get_contents();
ob_clean();

// Aktív
ob_start();

$active = mysqli_query($f1db,
	"SELECT *
	FROM f1_active_driver AS active
	INNER JOIN team
	ON active.team = team.no
	WHERE active.no = $driverNo
	LIMIT 1"
);

if (mysqli_num_rows($active) > 0) {
	$ctv = mysqli_fetch_array($active);
	$team_no = $ctv['team'];
	$car_no = $ctv['car_no'];
	$driverTeamId = $ctv['id'];
	
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
	$chassis = linkChassis($chassis['id'], $chassis['ch_no'], chassisName($chassis['fullname'], $chassis['type']));
	// Motor
	$engine = mysqli_query($f1db,
		"SELECT *, engine.no AS eng_no
		FROM engine
		INNER JOIN team ON engine.cons = team.no
		WHERE engine.no = $engine
		LIMIT 1");
	
	$engine = mysqli_fetch_array($engine);
	$name   = engineName($engine['fullname'], $engine['type'], $engine['volume'], $engine['concept'], $engine['cylinders'], $engine['turbo']);
	$engine = linkEngine($engine['id'], $engine['eng_no'], $name);
	
	// Gumi
	$tyre = mysqli_query($f1db,
		"SELECT *
		FROM tyre
		WHERE id = '$tyre'
		LIMIT 1");
	$tyre = mysqli_fetch_array($tyre);
	$tyre = linkTyre($tyre['id'], $tyre['fullname']);
	
	// Kiírás	
	echo '<table class="plain">';
	echo '<tr>';
		echo '<td rowspan="5" style="padding-right:15px;" align="center" valign="center">';
		// Kép
		$img = '/img/team/' . $ctv['id'] . '.png';
		$img_path = 'img/team/' . $ctv['id'] . '.png';
	
		if (file_exists($img_path)) {
			echo '<img src="' . $img . '" style="max-height:90px; max-width:250px;">';
		}	
		echo '</td>';
		echo '<td colspan="2"><h2 style="margin-top:0px;">'.$ctv['fullname'].'</h2></td>';
	echo '</tr>';
	echo '<tr><td style="padding-right:10px; font-weight:bold;">Chassis</td><td>'.$chassis.'</td></tr>';
	echo '<tr><td style="padding-right:10px; font-weight:bold;">Engine</td><td>' .$engine. '</td></tr>';
	echo '<tr><td style="padding-right:10px; font-weight:bold;">Tyres</td><td>'  .$tyre.   '</td></tr>';
	echo '<tr><td style="padding-right:10px; font-weight:bold;">Car #</td><td>'  .$car_no. '</td></tr>';
	echo '</table>';
}

$driverActive = ob_get_contents();
ob_clean();

// Eredmények

// QPA
ob_start();
$act = actual;
$champion = mysqli_query($f1db,
	"SELECT yr
	FROM f1_tbl
	WHERE driver = $driverNo
	AND place = 1
	AND yr < $act
	ORDER BY yr ASC"
);

if (mysqli_num_rows($champion) > 0) {
	echo '<div>';
	echo '<h3 style="text-align:center; margin-top:0;">Champion</h3>';
	echo '<img src="/img/site/trophyDriver.png" height="120">';
	echo '<div style="float:right;"><div style="display: table-cell; vertical-align: middle; height:110px; margin-left:7px;">';
	while ($yr = mysqli_fetch_array($champion)) {
		echo '<h4 style="margin:0px;">'.$yr['yr'].'</h4>';
	}
	echo '</div></div></div>';
}

$champ = ob_get_contents();
ob_clean();

// Career
ob_start();

// Dobogó (A végén lesz)
$driverPlaces = mysqli_query($f1db,
	"SELECT finish, COUNT(finish) AS count
	FROM f1_race
	WHERE driver = $driverNo
	AND status = 1
	GROUP BY finish"
);

$podium    = 0;
$bestPlace = 0;

$plcs = array (1 => 0, 2 => 0, 3 => 0);	
while ($pod = mysqli_fetch_array($driverPlaces)) {
	$plcs[$pod['finish']] = $pod['count'];
}

if ($plcs[1] + $plcs[2] + $plcs[3] > 0) { // Van dobogó
	$podium = podium($plcs[1], $plcs[2], $plcs[3]); // functionsMisc
}
else {
	foreach ($plcs as $place => $cnt) {
		if ($cnt > 0) {
			$bestPlace = $place;
			break;
		}
	}
}

// Első div
echo '<div>';

	// Aktív évek
	$activeyrs = mysqli_query($f1db,
		"SELECT DISTINCT yr
		FROM f1_tbl
		WHERE driver = $driverNo"
	);
		
	$active = array();

	while ($ctv = mysqli_fetch_array($activeyrs)) {
		array_push($active, $ctv['yr']);
	}

	echo '<b>Active</b>: ';	
	echo implode(interval($active), ', ').'<br>';
	
	// Nagydíjak
	$gps = mysqli_query($f1db,
		"SELECT COUNT(rnd) AS count
		FROM f1_race
		WHERE driver = $driverNo"
	);

	$gps = mysqli_fetch_array($gps);
	echo '<b>GPs</b>: ' . $gps['count'] . '<br>';
	
	// Rajtok
	$starts = mysqli_query($f1db,
		"SELECT COUNT(rnd) AS count
		FROM f1_race
		WHERE driver = $driverNo
		AND status <= 3"
	);
	
	$starts = mysqli_fetch_array($starts);
	echo '<b>Starts</b>: ' . $starts['count'] . '<br>';
	
	// Első nagydíj (egy lekérdezés?)
	$first = mysqli_query($f1db,
		"SELECT race.rnd, gps.yr, gps.gp, country.name
		FROM f1_race AS race
		INNER JOIN f1_gp AS gps
		ON (race.rnd = gps.no)
		INNER JOIN country
		ON (gps.gp = country.gp)
		WHERE race.driver = $driverNo
		ORDER BY race.rnd ASC
		LIMIT 1"
	);
		
	$frst = mysqli_fetch_array($first);

	$gpname = $frst['yr'] . ' ' . $frst['name'] . ' GP';
	echo '<b>First GP</b>: '.linkRace($frst['yr'], $frst['gp'], $gpname).'<br>';

	// Utolsó nagydíj
	$last = mysqli_query($f1db,
		"SELECT race.rnd, gps.yr, gps.gp, country.name
		FROM f1_race AS race
		INNER JOIN f1_gp AS gps
		ON (race.rnd = gps.no)
		INNER JOIN country
		ON (gps.gp = country.gp)
		WHERE race.driver = $driverNo
		ORDER BY race.rnd DESC
		LIMIT 1"
	);
		
	$lst = mysqli_fetch_array($last);

	if ($frst['rnd'] != $lst['rnd']) {
		$gpname = $lst['yr'] . ' ' . $lst['name'] . ' GP';
		echo '<b>Last GP</b>: '.linkRace($lst['yr'], $lst['gp'], $gpname).'<br>';
	}
	
echo '</div>';
// Első div vége

// Második div
echo '<div style="width:240px;">';
	// Dobogó/legjobb
	echo $podium?$podium:'';
	echo $bestPlace?'<b>Best place</b>: '.ordinal($bestPlace).'<br>':'';
	
	// Pole
	$poles = mysqli_query($f1db,
		"SELECT COUNT(*) AS count
		FROM f1_q AS q
		INNER JOIN f1_race AS race
		ON q.entr_no = race.no
		WHERE race.driver = '$driverNo'
		AND q.place = 1"
	);
		
	$poles = mysqli_fetch_array($poles);
	$poles = $poles['count'];
	if ($poles > 0) {
		echo '<b>Poles</b>: '.$poles.'<br>';
	}
	
	// Pontszerző
	$scored = mysqli_query($f1db,
		"SELECT COUNT(rnd) AS count, SUM(score) AS sum
		FROM f1_race
		WHERE driver = $driverNo
		AND score > 0"
	);

	$scrd = mysqli_fetch_array($scored);
	$scoredplaces = $scrd['count'];

	if ($scoredplaces > 0) {
		echo '<b>Scored</b>: ' . $scoredplaces . ' times<br>';
		
		$sumscore = $scrd['sum'] + 0; // Tizedesjegyek le
		echo '<b>Total career score</b>: '.$sumscore.'<br>';
	}
	
echo '</div>';
// Második div vége


$driverCareer = ob_get_contents();
ob_clean();

// Birthday
ob_start();
$bd = explode('-', $driverbirth);
$hasBirthday = $bd[2].'-'.$bd[1] == date("d-m");

if ($hasBirthday) {
	$text = 'Happy '.ordinal(date("Y")-$bd[0]).' birthday!';
	$cake = '<img src="/img/site/cake.png" style="height:26px; margin-right:10px;">';
	echo '<h3 style="margin:0; color:white;">'.$cake.'<span style="position:relative; bottom:6px;">'.$text.'</span></h2>';
}

$birthday = ob_get_contents();
ob_clean();

// About
ob_start();

$nat_short = $driverQuery['nat_short'];
echo '<b>Nationality</b>: '.flag($nat_short).linkCountry($nat_short, $driverNat).'<br>';

$birth = $driverQuery['birth'];
$death = $driverQuery['death'];

echo '<b>Born</b>: '.date('j F, Y', strtotime($birth)).(!$death?(' ('.passed($birth).')'):'').'<br>';
echo $death>0?('<b>Died</b>: '.date('j F, Y', strtotime($death)).' ('.passed($birth, $death).')<br>'):'';

$driverAbout = ob_get_contents();
ob_clean();

// Social Media
ob_start();

socialMedia($driverNo, 'D');

$socialMedia = ob_get_contents();
ob_clean();

// Social Media
ob_start();

$car_numbers = mysqli_query($f1db,
	"SELECT DISTINCT
			GREATEST(yr, car_no) AS yr,
			LEAST(car_no, yr) AS car_no
	FROM f1_race
	WHERE driver = $driverNo
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
echo '<table class="plain">';
foreach ($numbers as $car_no => $years) {
	echo '<tr>';
	echo '<td style="font-weight:bold; width:25px;">'.$car_no.'</td>';
	echo '<td>'.implode(', ',interval($years)).'</td>';
	echo '</tr>';
}
echo '</table>';

$carNumbers = ob_get_contents();
ob_clean();

// Csapatok
ob_start();

$teams = mysqli_query($f1db,
	"SELECT DISTINCT
	GREATEST(yr, team) AS yr,
	LEAST(team, yr) AS team,
	team.fullname, team.id
	FROM f1_race AS race
	INNER JOIN team
	ON (team = team.no)
	WHERE driver = $driverNo
	ORDER BY rnd ASC"
);

$tms = array();
while ($row = mysqli_fetch_array($teams)) {
	if (!isset($tms[$row['id']])) {
		$tms[$row['id']]['yrs']  = array();
		$tms[$row['id']]['name'] = $row['fullname'];
	}
	array_push($tms[$row['id']]['yrs'], $row['yr']);
}
echo '<table class="plain">';
foreach ($tms as $id => $tm) {
	echo '<tr>';
		echo '<td>'.linkTeam($id, $tm['name']).'</td>';
		echo '<td>'.implode(', ',interval($tm['yrs'])).'</td>';
	echo '</tr>';
}
echo '</table>';

$driverTeams = ob_get_contents();
ob_clean();

// Victories
ob_start();

$driverVictories = ob_get_contents();

?>
<h1><?php echo $driverName; ?></h1>
<div style="overflow:auto;"><!-- First block begin -->
<?php
	// Jobb oldal
	echo '<div style="float:right;">';
	
	// Kép
	if (!empty($driverPhoto)) {
		echo '<div class="single" style="padding:0; width:292px;">'.$driverPhoto.'</div>';
	}
	
	echo '<div class="single"">'.$driverAbout.'</div>';
	
	// Logo
	if (!empty($driverLogo)) {
		echo $driverLogo;
	}
	
	echo '</div>';
	// Jobb oldal vége
	
	if ($hasBirthday) {
		echo '<div class="double"style="background-color:#ffa000; color:white;
			  border-color:#ff7f00; background-image: url(\'/img/bg/birthday.png\')">'.
			  $birthday.'</div>';
	}
	
	// Aktív
	if (!empty($driverActive)) {
		echo '<div class="double '.$driverTeamId.'">'.$driverActive.'</div>';
	}
?>
<div style="display:flex;">
	<?php // Champion
		echo !empty($champ)?'<div class="single" style="width:130px; background-color:#E0E0E0; background-image: url(\'/img/bg/trophy.png\');">'.$champ.'</div>':'';
	?>
	
	<div class="double" style="background-image: url('/img/bg/steering.png');
	background-repeat:no-repeat; display:flex; justify-content: space-between;">
		<?php echo $driverCareer; ?>
	</div>
</div>
<!-- Left col -->
<div style="float:left;">
<?php
// Social media
if (!empty($socialMedia)) {
	echo '<h2 style="margin-top:0;">Social media</h2>';
	echo '<div class="single">'.$socialMedia.'</div>';
}

// Victories
if (!empty($driverVictories)) {
	if (!empty($driverNoVictories)) {
		
	}
}
?>
</div>

<div style="float:left;">
	<h2 style="margin-top:0;">Teams</h2>
	<div class="single" style="float:left;"><?php echo $driverTeams; ?></div>
	<h2 style="margin-top:0;">Car numbers</h2>
	<div class="single" style="float:left;"><?php echo $carNumbers; ?></div>
</div>


</div><!-- End of first block -->
<div class="triple">Stuff</div>
<?php
include('resources/foot.php');
?>
