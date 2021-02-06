<?php	
	// HEAD
	$pagetitle = 'Races';
	$maintitle = 'Formula One Races';
	require_once('included/head.php');
		
	/////////////
	// SEASONS //
	/////////////
	
	// Alap tömb a 2 határ alapján
	$first_season = 1950;
	$act = 2015;
	$seasons = array();
	
	while ($act >= $first_season) {
		$seasons[$act] = array();
		$act--;
	}
	
	// Világbajnokok
	$champs = mysqli_query($f1db,
		"SELECT tbl.yr, driver.first, driver.de, driver.last, driver.sr, driver.country, driver.id
		FROM f1_tbl AS tbl
		INNER JOIN driver
		ON (tbl.driver = driver.no)
		WHERE place = 1
		AND score > 0
		ORDER BY yr DESC");
	
	while ($row = mysqli_fetch_array($champs)) {
		$seasons[$row['yr']]['ch_id'] = $row['id'];
			$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		$seasons[$row['yr']]['ch_name'] = $name;
		$seasons[$row['yr']]['ch_country'] = $row['country'];
	}
	
	// Versenyek száma
	$races = mysqli_query($f1db,
		"SELECT yr, COUNT(no) AS count
		FROM f1_gp
		GROUP BY yr
		ORDER BY yr DESC");
		
	while ($row = mysqli_fetch_array($races)) {
		$seasons[$row['yr']]['races'] = $row['count'];
	}
	
	// Fő táblázat (2 oszlop)
	echo '<table width="100%"><tr><td width="50%" valign="top">';
	echo '<h2 style="margin-top:0px;">Seasons</h2>';
	
	// Táblázat
	echo '<table class="results">';
	echo '<th>Year</th><th>Champion</th><th>Races</th>';
	foreach ($seasons as $yr => $props) {
		echo '<tr>';
		echo '<td>'.season_link($yr).'</td>';
		// Bajnok
		if (isset($props['ch_id'])) {
			echo'<td>';
			echo flag($props['ch_country']);
			echo driver_link($props['ch_id'], $props['ch_name']);
			if ($yr == last) { echo ' *';}
			echo'</td>';
		}
		else {
			echo'<td></td>';
		}
		// Versenyek száma
		if(isset($props['races'])){echo'<td align="center">'.$props['races'].'</td>';}
			else{echo'<td></td>';}
		echo '</tr>';
	}
	echo '</table>';
	
	/////////////////
	// GRANDS PRIX //
	/////////////////
	
	// Oszlopváltás
	echo '</td><td width="50%" valign="top">';
	echo '<h2 style="margin-top:0px;">Grands Prix</h2>';
	
	$query = mysqli_query($f1db, 
		"SELECT DISTINCT gps.gp, country.name
		FROM f1_gp AS gps
		INNER JOIN country
		ON (gps.gp = country.gp)
		ORDER BY country.name ASC");
	
	echo '<table class="results">';
		
	while ($row = mysqli_fetch_array($query)) {		
		echo '<tr><td class="rnd">'.flag($row['gp']).'</td>
		<td>'.gp_link($row['gp'], $row['name']).'</td></tr>';
	}
	
	echo '</table>';
	
	// Nagy táblázat (2 oszlop) vége
	echo '</td></tr></table>';
	
	echo '<p>*: Current season</p>';
	
	require_once('included/foot.php');
?>