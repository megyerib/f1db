<?php
// Frissítés
if (isset($_POST['save']) || isset($_POST['savequit'])) {
	$id = $_GET[$maintitle];
	
	$query = "UPDATE $maintable SET ";
	$set = array();
		
	foreach ($values as $name => $props) {
		// Állapotjelzők
		$break = $props['genre'] == 'break';
		$checkbox = isset($props['params']['type']) && $props['params']['type'] == 'checkbox';
		
		if (!$break && !$checkbox) {
			array_push($set, $name." = '".$_POST[$name]."'");
		}
		// Checkbox, radio, stb. kivételek hozzáadása
		if ($checkbox) { // Háááát...
			$cbox_value = 0;
			if (isset($_POST['checkbox'])) {
				foreach ($_POST['checkbox'] as $field) {
					if ($name == $field) {
						$cbox_value = 1;
						break;
					}
				}
			}
			array_push($set, $name." = '".$cbox_value."'");
		}
	}
	$query .= implode(', ', $set).' ';
	$query .= "WHERE id = '$id'";
	
	$mod = mysqli_query($f1db, $query);
	
	if ($mod) {
		echo alert('Saved!');
	}
	
	if (isset($_POST['savequit']) && $mod) {
		header('Location: /admin/'.$maintitle.'s');
	}
}
// Hozzáadás
if (isset($_GET['add'])) {
	echo '<h2 style="margin-top:0px;">Add a new '.$maintitle.'</h2>';
	echo '<p><a href="/admin/'.$maintitle.'s">Back</a></p>';
	
	if (isset($_POST['add']) && !empty($_POST['newid'])) {
		$id = $_POST['newid'];
		
		$exists = mysqli_query($f1db,
			"SELECT id
			FROM $maintable
			WHERE id = '$id'");
		
		if (mysqli_num_rows($exists) == 0) {
			mysqli_query($f1db,
				"INSERT INTO $maintable(id)
				VALUES('$id')");
			header('Location: /admin/'.$maintitle.'s/'.$id);
		}
		else {
			echo '<p style="color:red;">'.ucfirst($maintitle).' with id "'.$id.'" already exists</p>';
		}
	}
	
	echo '<form method="post" action="/admin/new'.$maintitle.'">';
	echo 'id: <input name="newid" type="text" maxlength="'.$id_maxlength.'">';
	echo '<input type="submit" name="add" value="Add">';
	echo '<form>';
}

// Összes egy listában
if (count($_GET) == 0) {	
	// Add link	
	echo '<h2 style="margin-top:0px;">'.ucfirst($maintitle).'s</h2>';
	echo '<p><a href="/admin/new'.$maintitle.'">Add new</a></p>';	
	
	$query_order = !isset($query_order) ? 'fullname' : $query_order;
	$list_order = !isset($list_order) ? 'fullname' : $list_order;
	
	$all = mysqli_query($f1db,
		"SELECT *, LEFT($list_order , 1) AS o_char
		FROM $maintable
		ORDER BY $query_order");
	while ($row = mysqli_fetch_array($all)) {
		if (!isset($list_name)) { // Ha nem csak a fullname mezőt kell kiírni, hanem pl. nevet/motortípust
			$name = $row['fullname'];
		}
		else {
			$name = array();
			foreach ($list_name as $name_part) {
				array_push($name, $row[$name_part]);
			}
			$name = implode(' ', $name);
		}
		if ($separated_by_letters) {
			$letter = substr($row['o_char'], 0, 1);
			if (!isset($prev) || $prev != $letter) {
				echo '<h2>'.$letter.'</h2>';
			}
			$prev = $letter;
		}
		echo '<a href="/admin/'.$maintitle.'s/'.$row['id'].'">'.$name.'</a><br>'."\n";
	}
}
// Egy megjelenítése
if (isset($_GET[$maintitle])) {
	$subj_id = $_GET[$maintitle];
	
	$tm = mysqli_query($f1db,
		"SELECT *
		FROM $maintable
		WHERE id = '$subj_id'");
		
	if (mysqli_num_rows($tm) != 1) {
		header('Location: /admin/'.$maintitle.'s/');
	}
	
	$row = mysqli_fetch_array($tm);
	$subj_no = $row['no'];
	if (!isset($list_name)) { // Ha nem csak a fullname mezőt kell kiírni, hanem pl. nevet/motortípust
		$name = $row['fullname'];
	}
	else {
		$name = array();
		foreach ($list_name as $name_part) {
			array_push($name, $row[$name_part]);
		}
		$name = implode(' ', $name);
	}
	
	// Fejlécek
	echo '<h2 style="margin:0px;">'.$name.'</h2>';
	echo '<a href="/admin/'.$maintitle.'s">Back</a> | 
	<a href="http://race-data.net/'.$maintitle.'/'.$subj_id.'" target="_blank">View</a><br><br>';
	
	// Form
	echo '<form method="post" action="/admin/'.$maintitle.'s/'.$subj_id.'">';
	
	echo '<table>';
	foreach ($values as $name => $props) {
		$field_genre = $props['genre'];
		$field_type  = isset($props['params']['type']) ? $props['params']['type'] : '';
		
		// Szöveg
		if ($field_genre == 'input' && $field_type == 'text') {
			$field = '<input name="'.$name.'"';
			foreach ($props['params'] as $param => $value) {
				$field .= ' '.$param.'="'.$value.'"';
			}
			$field .= ' value="'.$row[$name].'"><br>'; // Miért van utána sortörés?
			if ($props['label']) {
				$rw_title = $props['label'];
			}
			else {
				$rw_title = '';
			}
			echo '<tr><td>'.$rw_title.'</td>';
			echo '<td>'.$field.'</td></tr>';
		}
		
		// Checkbox
		else if ($field_genre == 'input' && $field_type == 'checkbox') {
			// name a checkbox tömbre vonatkozik
			$field = '<input name="checkbox[]" value="'.$name.'"';
			foreach ($props['params'] as $param => $value) {
				$field .= ' '.$param.'="'.$value.'"';
			}
			if ($row[$name] == 1) {
				$field .= ' checked';
			}
			$field .= '><br>';
			if ($props['label']) {
				$rw_title = $props['label'];
			}
			else {
				$rw_title = '';
			}
			echo '<tr><td style="text-align:right;">'.$field.'</td>';
			echo '<td>'.$rw_title.'</td></tr>';
		}
		
		// Dátum
		else if ($field_genre == 'input' && $field_type == 'date') {
			$field = '<input name="'.$name.'"';
			foreach ($props['params'] as $param => $value) {
				$field .= ' '.$param.'="'.$value.'"';
			}
			$field .= ' value="'.$row[$name].'"><br>'; // Miért van utána sortörés?
			if ($props['label']) {
				$rw_title = $props['label'];
			}
			else {
				$rw_title = '';
			}
			echo '<tr><td>'.$rw_title.'</td>';
			echo '<td>'.$field.'</td></tr>';
		}
		
		// Dropdown
		else if ($field_genre == 'dropdown') {
			if ($props['label']) {
				$rw_title = $props['label'];
			}
			else {
				$rw_title = '';
			}
			echo '<tr><td>'.$rw_title.'</td><td>';
			switch ($name) { // Kiegészíthető
				case 'country': country_dropdown($row[$name], $f1db); break;
				case 'gender':   gender_dropdown($row[$name]); break;
			}
			echo '</td></tr>';
		}
		
		// Üres sor
		else if ($field_genre == 'break') {
			echo '<tr><td>&nbsp;</td></tr>';
		}
	}
	echo '</table>';
	
	// Gombok
	echo '<br><input type="submit" name="save" value="Save"> ';
	echo '<input type="submit" name="savequit" value="Save & quit">';
	echo ' <a href="/admin/'.$maintitle.'s">Back</a> | ';
	echo '<a href="/admin/'.$maintitle.'s/delete/'.$subj_id.'">Delete</a>';
	
	// KÉPFELTÖLTÉS
	if (isset($img)) {
		echo '<h2>Picture</h2>';
		require_once('img_upload.php');
	}
	
	require_once('included/social_media.php');
}
// Törlés
if (isset($_GET['delete'])) {
	if (isset($_POST['yes'])) {
		$id = $_POST['id'];
		$del = mysqli_query($f1db,
			"DELETE FROM $maintable
			WHERE id = '$id'");
		if ($del) {
			$_SESSION['alert'] = 'Deleted!'; // Kell, ha utána átirányít
		}
		header('Location: /admin/'.$maintitle.'s');
	}
	if (isset($_POST['no'])) {
		header('Location: /admin/'.$maintitle.'s/'.$_POST['id']);
	}
	
	$subj_id = $_GET['delete'];
	
	$tm = mysqli_query($f1db,
		"SELECT *
		FROM $maintable
		WHERE id = '$subj_id'");
		
	if (mysqli_num_rows($tm) != 1) {
		header('Location: /admin/'.$maintitle.'s/');
	}
	
	$row = mysqli_fetch_array($tm);
	
	echo 'Do you really delete <b>'.$row['fullname'].'</b>?';
	echo '<form method="post" action ="/'.$maintitle.'s/delete/'.$subj_id.'">';
	echo '<input type="hidden" name="id" value="'.$subj_id.'">';
	echo '<input type="submit" name="yes" value="Yup">';
	echo '<input type="submit" name="no" value="Not today">';
	echo '</form>';
}
?>