<?php
$entr_no = $_GET['entrant'];
// SAVE
if (isset($_POST['save'])) {
	$rnd     = $_POST['race'];
	
	$rce = mysqli_query($f1db,
		"SELECT yr, gp
		FROM f1_gp
		WHERE no = $rnd");
	$rce = mysqli_fetch_array($rce);
	$yr = $rce['yr'];
	$gp = $rce['gp'];
	
	$car_no  = $_POST['car_no'];
	$driver  = $_POST['driver'];
	$team    = $_POST['team'];
	$chassis = $_POST['chassis'];
	$engine  = $_POST['engine'];
	$tyre    = $_POST['tyre'];
	
	$update = mysqli_query($f1db,
		"UPDATE f1_race
		SET rnd = $rnd,
		yr = $yr,
		gp = '$gp',
		car_no = '$car_no',
		driver = $driver,
		team = $team,
		chassis = $chassis,
		engine = $engine,
		tyre = '$tyre'
		WHERE no = $entr_no");
	
	if ($update) {
		echo '<div class="alert">SAVED!</div>';
	}
	else {
		echo '<div class="alert">ERROR!</div>';
	}
	
}

$entrant = mysqli_query($f1db,
	"SELECT *
	FROM f1_race
	WHERE no = $entr_no");
$entr = mysqli_fetch_array($entrant);
$race_no = $entr['rnd'];

// DELETE
if (isset($_POST['delete'])) {
	if (isset($_POST['del_subm'])) { // Delete command (submitted)
		mysqli_query($f1db,
			"DELETE
			FROM f1_race
			WHERE no = $entr_no");
		$race_of = $entr['rnd'];
		header('Location: /admin/race/'.$race_of.'/entrants');
	}
	echo '<h1 class="title">Delete entrant</h1>';
	echo '<p>Do you really delete it?</p>';
	echo '<form method="post" action="/admin/race/entrants/'.$entr_no.'">';
	echo '<input type="hidden" name="del_subm" value="del">';
	echo '<input type="submit" name="delete" value="Delete"> ';
	echo '<input type="submit" value="Nope">';
	echo '</form>';
}
	
if (!isset($_POST['delete'])) {
echo '<h1 style="margin-top:0px;">Edit entrant</h1>';
echo '<p><b>Back</b>: ';
echo '<a href="/admin/race/'.$race_no.'/entrants">Entrants</a> | ';
echo '<a href="/admin/race/'.$race_no.'">This race</a> | ';
echo '<a href="/admin/race">Races</a></p>';

	echo '<form method="post" action="/admin/race/entrants/'.$entr_no.'">';
	
	echo '<label class="input">Race</label>';
	race_dropdown('race', $entr['rnd']); echo ' <a href="/admin/race/'.$entr['rnd'].'/results">Go</a><br><br>';

	echo '<label class="input">Driver</label>';	 driver_dropdown('driver', $entr['driver']);
	echo '<br><label class="input">Car #</label>';
	echo '<input type="text" name="car_no" value="'.$entr['car_no'].'">';
	echo '<br><label class="input">Team</label>';	 team_dropdown('team', $entr['team']);
	
	echo '<br><br><label class="input">Chassis</label>'; chassis_dropdown('chassis', $entr['chassis']);
	echo '<br><label class="input">Engine</label>';  engine_dropdown('engine', $entr['engine']);
	echo '<br><label class="input">Tyre</label>';    tyre_dropdown('tyre', $entr['tyre']);
	
	echo '<p><input type="submit" name="save" value="Save">';
	echo ' <input type="submit" name="delete" value="Delete"></p>';
	echo '</form>';
}
?>