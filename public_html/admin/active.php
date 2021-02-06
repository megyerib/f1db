<?php
require_once('included/head_admin.php');
// Lassú, de alig kell használni

function driver_status_dropdown($name, $chosen) { // Csak itt használom
	$statuses = array(
		'R' => 'Racing',
		'T' => 'Test',
		'RS' => 'Reserve'
		);
	
	echo '<select name="'.$name.'">';
	echo '<option value="">---</option>';
	foreach ($statuses AS $id => $status) {
		$selected = '';
		if ($chosen == $id) {
			$selected = 'selected';
		}
		echo '<option value="'.$id.'" '.$selected.'>'.$status.'</option>';
	}
	echo '</select>';
}
	
// Szegély színe (sötétítés)
function border_color($hex) {
	$mult = 0.75; // Sötétítés mértéke
	$r = dechex(floor($mult*hexdec(substr($hex,0,2)))); $r = strlen($r) == 1 ? '0'.$r : $r;
	$g = dechex(floor($mult*hexdec(substr($hex,2,2)))); $g = strlen($g) == 1 ? '0'.$g : $g;
	$b = dechex(floor($mult*hexdec(substr($hex,4,2)))); $b = strlen($b) == 1 ? '0'.$b : $b;
	return $r.$g.$b;
	// 2. oszlop: if (strlen($red)   == 1) {$red   = '0'.$red;}
}

function driver_from_no($no) {
	global $f1db;
	$query = mysqli_query($f1db,
		"SELECT first, de, last, sr
		FROM driver
		WHERE no = $no"
	);
	$row = mysqli_fetch_array($query);
	return name_2($row['first'], $row['de'], $row['last'], $row['sr']);
}

function team_from_no($no) {
	global $f1db;
	$query = mysqli_query($f1db,
		"SELECT fullname
		FROM team
		WHERE no = $no"
	);
	$row = mysqli_fetch_array($query);
	return $row['fullname'];
}

// 1. Csapatok
if ($_GET['mode'] == 'team') {
	// Aktív csapatok
	echo '<h1 class="title">Active teams</h1>';
	echo '<p><a href="/admin/active/driver">Active drivers</a></p>';
	// Add
	if (isset($_POST['add_team'])) {
		$no          = $_POST['team'];
		$engine      = $_POST['engine'];
		$chassis     = $_POST['chassis'];
		$tyre        = $_POST['tyre'];
		$ordering    = !empty($_POST['ordering']) ? $_POST['ordering'] : '0';
		$bg_color    = substr($_POST['bg_color'], -6);
		$border_color= border_color($bg_color);
		$font_color  = substr($_POST['font_color'], -6);
	
		mysqli_query($f1db,
			"INSERT INTO f1_active_team(no, engine, chassis, tyre, ordering, bg_color, font_color, border_color)
			VALUES($no, $engine, $chassis, '$tyre', $ordering, '$bg_color', '$font_color', '$border_color')"
		);
	}
	
	// Ment, töröl (szar, mert minden egyes sor külön lekérdezés)
	if (isset($_POST['edit_team'])) {
		//vardump($_POST['ordering']);
		$del = 0; // Sorban jönnek a törlendő elemek, ez a változó lépteti a tömböt
		foreach ($_POST['no'] as $nr => $team) {
			// Ment
			if (!isset($_POST['delete'][$del]) || $_POST['delete'][$del] != $team) {
				$ordering = !empty($_POST['ordering'][$nr]) ? $_POST['ordering'][$nr] : '0';
				$q =
					"SET engine = ".$_POST['engine'][$nr].",
					chassis = ".$_POST['chassis'][$nr].",
					tyre = '".$_POST['tyre'][$nr]."',
					ordering = ".$ordering.",
					bg_color = '".substr($_POST['bg_color'][$nr], -6)."',
					font_color = '".substr($_POST['font_color'][$nr], -6)."',
					border_color = '".border_color(substr($_POST['bg_color'][$nr], -6))."'
					WHERE no = ".$_POST['no'][$nr];
					
				mysqli_query($f1db,
					"UPDATE f1_active_team
					$q"
				);
			}
			// Töröl
			else {
				mysqli_query($f1db,
					"DELETE FROM f1_active_team
					WHERE no = $team"
				);
				$del++;
			}
		}
	}
	
	// Kiír
	$teams = mysqli_query($f1db,
		"SELECT *
		FROM f1_active_team
		ORDER BY ordering = 0, ordering ASC");

	echo '<form method="post">';
	echo '<table>';
	echo '<tr>
		<th>Team</th>
		<th>Chassis</th>
		<th>Engine</th>
		<th>Tyre</th>
		<th>Ordering</th>
		<th>BG</th>
		<th>Font</th>
		<th>Del</th>
	</tr>';
	while ($row = mysqli_fetch_array($teams)) {
		echo '<tr>';
		echo '<input type="hidden" name="no[]" value="'.$row['no'].'">';
		echo '<td>'.team_from_no($row['no']).'</td>';
		echo '<td>'; chassis_dropdown('chassis[]', $row['chassis']); echo '</td>';
		echo '<td>'; engine_dropdown('engine[]', $row['engine']);    echo '</td>';
		echo '<td>'; tyre_dropdown('tyre[]', $row['tyre']);          echo '</td>';
		echo '<td><input type="number" name="ordering[]" value="'.$row['ordering'].'" size="4"></td>';
		echo '<td><input type="color" name="bg_color[]" value="#'.$row['bg_color'].'"></td>';
		echo '<td><input type="color" name="font_color[]" value="#'.$row['font_color'].'"></td>';
		echo '<td style="text-align:center;"><input type="checkbox" name="delete[]" value="'.$row['no'].'"></td>';
		echo '</tr>';
	}
	echo '<tr><td colspan="8"><input type="submit" name="edit_team" value="Save"></td></tr>';
	echo '</form>';
	
	// Hozzáadó
	echo '<tr><td colspan="8"><hr></td></tr>';
	echo '<tr>';
	echo '<form method="post">';
	echo '<td>'; team_dropdown('team', 0);       echo '</td>';
	echo '<td>'; chassis_dropdown('chassis', 0); echo '</td>';
	echo '<td>'; engine_dropdown('engine', 0);   echo '</td>';
	echo '<td>'; tyre_dropdown('tyre', 0);      echo '</td>';
	echo '<td><input type="number" name="ordering" value="0"></td>';
	echo '<td><input type="color" name="bg_color"></td>';
	echo '<td><input type="color" name="font_color"></td>';
	echo '<td><input type="submit" name="add_team" value="Add"></td>';
	echo '</tr>';
	echo '</form>';
	
	echo '</table>';
}

// 2. Pilóták
if ($_GET['mode'] == 'driver') {
	echo '<h1 class="title">Active drivers</h1>';
	echo '<p><a href="/admin/active/team">Active teams</a></p>';

	// Add
	if (isset($_POST['add_driver'])) {
		$no       = $_POST['driver'];
		$team     = $_POST['team'];
		$car_no   = $_POST['car_no'];
		$status   = $_POST['status'];
		$ordering = $_POST['ordering'];
		$short    = $_POST['short'];
		 
		mysqli_query($f1db,
			"INSERT INTO f1_active_driver(no, team, car_no, status, ordering, short)
			VALUES($no, $team, '$car_no', '$status', $ordering, '$short')");
	}

	// Ment, töröl (szar, mert minden egyes sor külön lekérdezés)
	if (isset($_POST['edit_driver'])) {
		//vardump($_POST['ordering']);
		$del = 0; // Sorban jönnek a törlendő elemek, ez a változó lépteti a tömböt
		foreach ($_POST['no'] as $nr => $driver) {
			// Ment
			if (!isset($_POST['delete'][$del]) || $_POST['delete'][$del] != $driver) {
				$ordering = !empty($_POST['ordering'][$nr]) ? $_POST['ordering'][$nr] : '0';
				$q =
					"SET team = ".$_POST['team'][$nr].",
					car_no = '".$_POST['car_no'][$nr]."',
					status = '".$_POST['status'][$nr]."',
					ordering = ".$ordering.",
					short = '".$_POST['short'][$nr]."'
					WHERE no = ".$_POST['no'][$nr];
					
				mysqli_query($f1db,
					"UPDATE f1_active_driver
					$q"
				);
			}
			// Töröl
			else {
				mysqli_query($f1db,
					"DELETE FROM f1_active_driver
					WHERE no = $driver"
				);
				$del++;
			}
		}
	}

	// Listáz
	$drivers = mysqli_query($f1db,
		"SELECT *
		FROM f1_active_driver
		ORDER BY ordering = 0, ordering ASC");

	echo '<form method="post" action="/admin/active">';
	echo '<table>';
	echo '<tr>
		<th>Car #</th>
		<th>Short</th>
		<th>Name</th>
		<th>Team</th>
		<th>Status</th>
		<th>Ordering</th>
		<th>Del</th>
	</tr>';
	while ($row = mysqli_fetch_array($drivers)) {
		echo '<tr>';
		echo '<input type="hidden" name="no[]" value="'.$row['no'].'"></td>';
		echo '<td><input type="text" name="car_no[]" size="4" value="'.$row['car_no'].'"></td>';
		echo '<td><input type="text" name="short[]" size="4" value="'.$row['short'].'" maxlength="3"></td>';
		//echo '<td>'; driver_dropdown('driver[]', $row['no']);            echo '</td>';
		echo '<td>'.driver_from_no($row['no']).'</td>';	
		echo '<td>'; active_team_dropdown('team[]', $row['team']);       echo '</td>'; 
		echo '<td>'; driver_status_dropdown('status[]', $row['status']); echo '</td>'; 
		echo '<td><input type="number" name="ordering[]" value="'.$row['ordering'].'"></td>';
		echo '<td style="text-align:center;"><input type="checkbox" name="delete[]" value="'.$row['no'].'">';
		echo '</tr>';
	}
	echo '<tr><td style="padding-top:4px;"><input type="submit" name="edit_driver" value="Save"></td></tr>';
	echo '</form>';

	// Hozzáadó
	echo '<tr><td colspan="7"><hr></td></tr>';
	echo '<tr>';
	echo '<form method="post">';
	echo '<td><input type="text" name="car_no" size="4"></td>';
	echo '<td><input type="text" name="short" size="4" value="'.$row['short'].'" maxlength="3"></td>';
	echo '<td>'; driver_dropdown('driver', 0);          echo '</td>';
	echo '<td>'; active_team_dropdown('team', 0);       echo '</td>';
	echo '<td>'; driver_status_dropdown('status', 'R'); echo '</td>';
	echo '<td><input type="number" name="ordering" value="0"></td>';
	echo '<td><input type="submit" name="add_driver" value="Add"></td>';
	echo '</form>';
	echo '</tr>';
	echo '</table>';
}
require_once('included/foot_admin.php');
?>