<?php
include('resources/head.php');
$all = mysqli_query($f1db,
	"SELECT *
	FROM driver
	ORDER BY last ASC"
);

// Betűk
$query_letters = mysqli_query($f1db,
	"SELECT DISTINCT LEFT(last, 1) AS letter
	FROM driver
	ORDER BY last ASC"
);

$letters = array();

while ($row = mysqli_fetch_array($query_letters)) {
	$link = '<a href="#'.$row['letter'].'">'.$row['letter'].'</a>';
	array_push($letters, $link);
}

ob_start();

echo '<h3 style="margin:0">'.implode(' &middot ', $letters).'</h3>';

$letters = ob_get_contents();
ob_clean();

// Mindenki
ob_start();

$mainq = mysqli_query($f1db,
	"SELECT *
	FROM driver
	ORDER BY last ASC"
);
echo '<h2 style="margin-top:0">A</h2>'; // Bugot használok ki, nem rak A betűt
$prev = 0;
while ($row = mysqli_fetch_array($mainq)) {
	$letter = substr($row['last'], 0, 1);
	if ($prev != $letter) {
		echo '<h2 id="'.$letter.'" style="margin-top:15px;">'.$letter.'</h2>';
	}
	$prev = $letter;
	
	$name = nameReverse($row['first'], $row['de'], $row['last'], $row['sr']);
	
	echo flag($row['country']).linkDriver($row['id'], $name).'<br>';
}
$Wdrivers = ob_get_contents();
ob_clean();

// Akt. csapatok
ob_start();
	$teams = mysqli_query($f1db,
		"SELECT active.no, team.id, team.fullname, team.country
		FROM f1_active_team AS active
		INNER JOIN team
		ON active.no = team.no
		ORDER BY ordering ASC"
	);
	$active = array();
	while ($row = mysqli_fetch_array($teams)) {
		$active[$row['no']]['id']      = $row['id'];
		$active[$row['no']]['name']    = $row['fullname'];
		$active[$row['no']]['country'] = $row['country'];
	}

	// Pilóták
	$drivers = mysqli_query($f1db,
		"SELECT active.team, active.car_no, driver.country, driver.id, driver.first, driver.de, driver.last, driver.sr, active.status
		FROM f1_active_driver AS active
		INNER JOIN driver
		ON active.no = driver.no
		ORDER BY ordering ASC");
	while ($row = mysqli_fetch_array($drivers)) {
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		$active[$row['team']][$row['status']][$row['id']]['name']    = $name;
		$active[$row['team']][$row['status']][$row['id']]['country'] = $row['country'];
		$active[$row['team']][$row['status']][$row['id']]['car_no'] = $row['car_no'];
	}

	// Kiírás
	foreach ($active as $no => $team) {
		echo '<div class="single '.$team['id'].'">';
		// Logo
		echo '<img src="/img/team/icon/'.$team['id'].'.png" style="width:60px; height:60px; float:left; padding: 0 10px 0 0;">';
		echo '<span style="display:block; overflow:hidden;"><h3 style="margin-top:0px; margin-bottom:5px;">'.linkTeam($team['id'], $team['name']).'</h3>';
		foreach ($team['R'] as $id => $driver) {
			echo flag($driver['country']).'<span style="display: inline-block; width:15px; font-weight:bold; text-align:right; padding-right:5px;">'.$driver['car_no'].'</span>'.linkDriver($id, $driver['name']).'<br>';
		}
		if (isset($team['T'])) { // Mertvoltmárrápélda (a másikra sztem nem) (de, arra is, pl. év elején [#előrelátás] )
			echo '<p style="font-weight:bold;">Test drivers</p>';
			foreach ($team['T'] as $id => $driver) {
				echo flag($driver['country']).'<span style="display: inline-block; width:15px; font-weight:bold; text-align:right; padding-right:5px;">'.$driver['car_no'].'</span>'.linkDriver($id, $driver['name']).'<br>';
			}
		}
		echo '</span></div>';
	}
$current = ob_get_contents();
ob_clean();

?>
<h1>Drivers</h1>
<div class="triple">
	<?php echo $letters; ?>
</div>
<div style="float:right;">
	<h2 style="margin-top:0;">Current drivers</h2>
	<?php echo $current; ?>
</div>
<div class="double">
	<?php echo $Wdrivers; ?>
</div>
<div class="triple">
	<?php echo $letters; ?>
</div>
<?php
include('resources/foot.php');
?>