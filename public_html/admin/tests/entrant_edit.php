<?php

$entr_no = $_GET['entrant'];
// SAVE
if (isset($_POST['save'])) {
	$test     = $_POST['test'];
	
	$car_no  = $_POST['car_no'];
	$driver  = $_POST['driver'];
	$team    = $_POST['team'];
	$chassis = $_POST['chassis'];
	$engine  = $_POST['engine'];
	@$tyre    = $_POST['tyre'];
	
	$update = mysqli_query($f1db,
		"UPDATE f1_test_entrants
		SET test = $test,
		car_no = '$car_no',
		driver = $driver,
		team = $team,
		chassis = $chassis,
		engine = $engine,
		tyre = '$tyre'
		WHERE no = $entr_no");
	
	if ($update) {
		echo '<div class="alert">Saved</div>';
	}
	else {
		echo '<div class="alert">Error!</div>';
	}
	
}

$entrant = mysqli_query($f1db,
	"SELECT *
	FROM f1_test_entrants
	WHERE no = $entr_no"
);
$entr = mysqli_fetch_array($entrant);
$test_no = $entr['test'];

// DELETE
if (isset($_POST['delete'])) {
	if (isset($_POST['del_subm'])) { // Delete command (submitted)
		mysqli_query($f1db,
			"DELETE
			FROM f1_test_entrants
			WHERE no = $entr_no");
		$test_of = $entr['test'];
		header('Location: /admin/test/'.$test_of.'/entrants');
	}
	echo '<h1 class="title">Delete test entrant</h1>';
	echo '<p>Do you really delete it?</p>';
	echo '<form method="post" action="/admin/test/entrants/'.$entr_no.'">';
	echo '<input type="hidden" name="del_subm" value="del">';
	echo '<input type="submit" name="delete" value="Delete"> ';
	echo '<input type="submit" value="Nope">';
	echo '</form>';
}
	
if (!isset($_POST['delete'])) {
	echo '<h1 style="margin-top:0px;">Edit test entrant</h1>';
	echo '<p><b>Back</b>: ';
	echo '<a href="/admin/test/'.$test_no.'/entrants">Entrants</a> | ';
	echo '<a href="/admin/test/'.$test_no.'">This test</a> | ';
	echo '<a href="/admin/test">Tests</a></p>';

	echo '<form method="post" action="/admin/test/entrants/'.$entr_no.'">';
	
	echo '<label class="input">Test</label>';
	
	test_dropdown('test', $entr['test']); echo ' <a href="/admin/test/'.$entr['test'].'/results">Go</a><br><br>';

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