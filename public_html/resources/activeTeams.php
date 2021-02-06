<?php
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
?>