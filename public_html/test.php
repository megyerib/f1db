<?php
include('included/database.php');

$year = $_GET['year'];
$test = $_GET['test'];

$this_test = mysqli_query($f1db,
	"SELECT *
	FROM f1_test
	WHERE yr = $year
	AND no_yr = $test
	LIMIT 1"
);
$tst = mysqli_fetch_array($this_test);
$pagetitle = $tst['yr'].' '.$tst['name'];

include('included/head.php');
echo '<h1 class="title">'.$pagetitle.'</h1>';

// Schedule
$schedule = mysqli_query($f1db,
	"SELECT *
	FROM f1_test_schedule
	WHERE test = $test
	ORDER BY num ASC"
);
$testdays = array();
echo '<table class="results">';
echo '<th>Event</th><th>Time (CET)</th>';
while ($row = mysqli_fetch_array($schedule)) {
	echo '<tr>';
	echo '<td><a href="#day'.$row['num'].'">Test day '.$row['num'].'</a></td>';
	echo '<td>'.date('j F, H:i', strtotime($row['tme'])).'</td>';	
	echo '</tr>';
	if ($row['shown'] == 1) {
		$testdays[$row['num']] = $row['no'];
	}
}
echo '</table>';

foreach ($testdays as $day => $no) {
	echo '<h2 id="day'.$day.'">Test day '.$day.'</h2>';
	table_test($no);
}

include('included/foot.php');
?>