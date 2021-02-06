<?php
// ADD SCHEDULE
if (isset($_POST['schedule_add'])) {
	$num  = $_POST['eventnum'];
	$time = $_POST['tme'];
	
	mysqli_query($f1db,
		"INSERT INTO f1_test_schedule(test, num, tme)
		VALUES($test_no, $num, '$time')");
}

// SAVE/DELETE SCHEDULE
if (isset($_POST['schedule_save'])) {
	$del = 0;
	foreach ($_POST['no'] as $key => $event_no) {
		if (isset($_POST['schedule_delete'][$del]) && $_POST['schedule_delete'][$del] == $event_no) {
			mysqli_query($f1db,
				"DELETE FROM f1_test_schedule
				WHERE no = $event_no"
			);
			$del++;
		}
		else {
			$num  = $_POST['eventnum'][$key];
			$time = $_POST['tme'][$key];
			
			mysqli_query($f1db,
				"UPDATE f1_test_schedule
				SET num = $num,
				tme = '$time'
				WHERE no = '$event_no'"
			);
		}
	}
}

// Év többi versenye
$yr_tests = mysqli_query($f1db,
	"SELECT *
	FROM f1_test
	WHERE yr = $test_yr
	ORDER BY no_yr ASC");

echo '<div style="float:right;"><fieldset>';
echo '<legend>'.$test_yr.'</legend>';
while ($row = mysqli_fetch_array($yr_tests)) {
	echo '<a href="/admin/test/'.$row['no'].'/results">'.$row['name'].' GP</a><br>';
}
echo '</fieldset></div>';

// Cím
echo '<h1 class="title">';
echo $test_yr.' '.$test['name'].' results';
echo '</h1>';
echo '<a href="/admin/test/'.$test_no.'">Back to test menu</a><br>';
	
	echo '<ul>';
	echo '<li><a href="/admin/test/'.$test_no.'/entrants">Entrants</a></li>';
	echo '<br>';
	
	
	
	// Schedule
	$events = mysqli_query($f1db,
		"SELECT *
		FROM f1_test_schedule
		WHERE test = $test_no
		ORDER BY tme, no, num ASC"
	);
	
	while ($row = mysqli_fetch_array($events)) {
		if ($row['shown'] == 1) {
			$shown = '<span style="background-color:green; color:white; margin-left:5px; padding-left:3px; padding-right:3px;">shown</span>';
		}
		else {
			$shown = '<span style="background-color:red; color:white; margin-left:5px; padding-left:3px; padding-right:3px;">hidden</span>';
		}
		
		$link = '/admin/test/'.$test_no.'/results/session/'.$row['no'];
		$text = 'Test day '.$row['num'];

		echo '<li><a href="'.$link.'">'.$text.'</a>'.$shown.'</li>';
	}
	echo '</ul>';
	
	// tests.php-ból másolva, a többi része fent van
	echo '<h2>Schedule</h2>';
	
	// Schedule
	$schedule = mysqli_query($f1db,
		"SELECT *
		FROM f1_test_schedule
		WHERE test = $test_no
		ORDER BY tme, no ASC");
	
	echo '<form method="post">';
	while ($row = mysqli_fetch_array($schedule)) {
		echo '<input type="hidden" name="no[]" value="'.$row['no'].'">';
		echo 'Test day <input type="number" name="eventnum[]" value="'.$row['num'].'"> ';
		//echo '<input type="text" name="eventname[]" value="'.$row['name'].'" size="10"> ';
		$time = substr($row['tme'], 0, -3);
		$time[10] = 'T';
		echo '<input type="datetime-local" name="tme[]" value="'.$time.'">';
		echo '<input type="checkbox" name="schedule_delete[]" value="'.$row['no'].'"><br>';	
	}
	echo '<input type="submit" name="schedule_save" value="Save">';
	echo '</form>';
	
	echo '<hr width="200" align="left">';
	echo '<form method="post">';
	echo 'Test day <input type="number" name="eventnum" value="0"> ';
	//echo '<input type="text" name="eventname" value="'.$row['name'].'" size="10"> ';
	echo '<input type="datetime-local" name="tme"> ';
	echo '<input type="Submit" name="schedule_add" value="Add">';
	echo '</form>';
?>