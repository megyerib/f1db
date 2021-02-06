<?php
// AKT. SZEZON
if ($race['yr'] == actual) {
// Add
if (isset($_POST['add_current'])) {
	foreach ($_POST['add_driver'] as $driver_id) {
		$read = mysqli_query($f1db,
			"SELECT *
			FROM f1_race
			WHERE rnd = $race_no
			AND driver = $driver_id");
		if (mysqli_num_rows($read) == 0) {
			$data = mysqli_query($f1db,
				"SELECT *
				FROM f1_active_driver AS driver
				INNER JOIN f1_active_team AS team
				ON driver.team = team.no
				WHERE driver.no = $driver_id");
			$dta = mysqli_fetch_array($data);
			
			$team = $dta['team'];
			$engine = $dta['engine'];
			$chassis = $dta['chassis'];
			$tyre = $dta['tyre'];
			$car_no = $dta['car_no'];
			
			mysqli_query($f1db,
				"INSERT INTO f1_race(yr, gp, rnd, driver, car_no, team, chassis, engine, tyre)
				VALUES($race_yr, '$race_gp', $race_no, $driver_id, '$car_no', $team, $chassis, $engine, '$tyre')");
		}
	}
}

// I. Check form (versenypilóták)
echo '<div style="float:right;">';
echo '<fieldset><legend>Racing drivers</legend>';
	$cur_drvrs = mysqli_query($f1db,
		"SELECT team.fullname, driver.no, LEFT(driver.first, 1) AS first, driver.de, driver.last, driver.sr
		FROM f1_active_driver AS drvr
		INNER JOIN driver
		ON drvr.no = driver.no
		INNER JOIN team
		ON drvr.team = team.no
		WHERE drvr.status = 'R'
		ORDER BY driver.last ASC");

echo '<form id="drvr_add" name="SelectedItems" method="post">';
	while ($row = mysqli_fetch_array($cur_drvrs)) {
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		echo '<input type="checkbox" name="add_driver[]" value="'.$row['no'].'">'.$name.'<br>';
	}
	echo '<hr>';
	echo '<script type="text/javascript" language="javascript">';
	echo "function SetAllCheckBoxes(FormName, AreaID, CheckValue) {
		if(!document.forms[FormName]) return;
		var objCheckBoxes = document.getElementById(AreaID).getElementsByTagName('input');
		if(!objCheckBoxes) return;
		var countCheckBoxes = objCheckBoxes.length;
		if(!countCheckBoxes)objCheckBoxes.checked = CheckValue;
		else for(var i = 0; i < countCheckBoxes; i++) objCheckBoxes[i].checked = CheckValue;}";
	echo '</script>';
	echo '<input type="checkbox" onclick="SetAllCheckBoxes(\'SelectedItems\',\'drvr_add\',this.checked);" /> All<br>';
	echo '<input type="submit" name="add_current" value="Add">';				
echo '</form></fieldset>';// Check form vége

// II. Checkform (tesztpilóták) - kényelmesebb
echo '<fieldset><legend>Test drivers</legend>';
echo '<form method="post">';
	$test_drvrs = mysqli_query($f1db,
		"SELECT team.fullname, driver.no, LEFT(driver.first, 1) AS first, driver.de, driver.last, driver.sr
		FROM f1_active_driver AS drvr
		INNER JOIN driver
		ON drvr.no = driver.no
		INNER JOIN team
		ON drvr.team = team.no
		WHERE drvr.status != 'R'
		ORDER BY driver.last ASC");
	while ($row = mysqli_fetch_array($test_drvrs)) {
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		echo '<input type="checkbox" name="add_driver[]" value="'.$row['no'].'">'.$name.'<br>';
	}
	echo '<hr>';
	echo '<input type="submit" name="add_current" value="Add">';				
echo '</form></fieldset>';

echo '</div>';
}

// Év többi versenye
$yr_races = mysqli_query($f1db,
	"SELECT *
	FROM f1_gp AS gp
	INNER JOIN country
	ON gp.gp = country.gp
	WHERE gp.yr = $race_yr
	ORDER BY gp.no ASC");

echo '<div style="float:right;"><fieldset>';
echo '<legend style="font-weight:bold;">'.$race_yr.'</legend>';
while ($row = mysqli_fetch_array($yr_races)) {
	echo '<a href="/admin/race/'.$row['no'].'/entrants">'.$row['name'].' GP</a><br>';
}
echo '</fieldset></div>';

// Cím
echo '<h1 class="title">';
echo '<a href="/admin/race/'.($race_no-1).'/entrants">&lt;</a>&nbsp;';
echo 'Entrants of '.$race['yr'].' '.$race['name'].' GP';
echo '&nbsp;<a href="/admin/race/'.($race_no+1).'/entrants">&gt;</a>';
echo '</h1>';
echo '<p><b>Back</b>: <a href="/admin/race/'.$race_no.'">This race</a> | <a href="/admin/race/'.$race_no.'/results">Results</a> | <a href="/admin/race/">Races</a></p>';

// MEGLÉVŐ NEVEZŐK
$entrants = mysqli_query($f1db,
	"SELECT driver.first, driver.de, driver.last, driver.sr,
		team.fullname AS team,
		ch_cons.fullname AS ch_cons, chassis.type AS ch_type,
		en_cons.fullname AS en_cons, engine.type AS en_type,
		race.tyre, race.car_no, race.no AS entr_no
	FROM f1_race AS race
	INNER JOIN driver
		ON race.driver = driver.no
	INNER JOIN team
		ON race.team = team.no
	INNER JOIN chassis
		ON race.chassis = chassis.no
	INNER JOIN team AS ch_cons
		ON chassis.cons = ch_cons.no
	INNER JOIN engine
		ON race.engine = engine.no
	INNER JOIN team AS en_cons
		ON engine.cons = en_cons.no
	WHERE rnd = $race_no");
echo '<table>';
echo '<th>#</th><th>Driver</th><th>Team</th><th>Chassis</th><th>Engine</th><th>Tyres</th>';
while ($entr = mysqli_fetch_array($entrants)) {
	echo '<tr>';
	echo '<td>'.$entr['car_no'].'</td>';
	echo '<td>'.name($entr['first'], $entr['de'], $entr['last'], $entr['sr']).'</td>';
	echo '<td>'.$entr['team'].'</td>';
	echo '<td>'.$entr['ch_cons'].' '.$entr['ch_type'].'</td>';
	echo '<td>'.$entr['en_cons'].' '.$entr['en_type'].'</td>';
	echo '<td>'.$entr['tyre'].'</td>';
	echo '<td><a href="/admin/race/entrants/'.$entr['entr_no'].'">Edit</a></td>';
	echo '</tr>';
}
echo '</table>';
if (mysqli_num_rows($entrants) == 0) {
	echo 'No entrants added yet';
}
echo '<p><a href="/admin/race/entrants/add/'.$race_no.'">Add</a></p>';
?>