<?php
// Paramétertömb - generátor
function param_array($string) {
	if ($string == '') {
		return array();
	}
	
	$params = array();
	$string = str_replace("\r\n", "\n", $string); // Többféle módon értelmezi a stringeket
	$lines = explode("\n", $string);
	foreach ($lines as $line) {
		$oneline = explode(' ', $line);
		$params[$oneline[0]] = $oneline[1];
	}
	return $params;
}

function field_array($string) {
	if ($string == '') {
		return array();
	}
	
	$params = array();
	$string = str_replace("\r\n", "\n", $string);
	$lines = explode("\n", $string);
	foreach ($lines as $no => $line) {
		$thisline = explode(',', $line);
		$params[$no]['element'] = 0;
		$params[$no]['params'] = array();
		foreach ($thisline as $thisno => $val) {
			$value = explode("=", $val);
			if ($thisno == 0) {
				$params[$no]['element'] = $value[0];
			}
			else {
				$params[$no]['params'][$value[0]] = $value[1];
			}
		}
	}
	return $params;
}

// 1. Listázó
function listaz($table, $order, $kiir, $link, $param) {
	// Paraméterlista beolvasása stringből
	$param = param_array($param);
	
	// Cím
	if (!empty($param['title'])) {
		echo '<h1 style="margin-top:0px;">'.$param['title'].'</h1>';
	}
	
	// Új
	if (!empty($param['new_link'])) {
		echo '<p><a href="'.$param['new_link'].'">+ Add new</a></p>';
	}
	
	global $f1db;
	$db = $f1db;
	
	$criteria = isset($param['criteria']) ? $param['criteria'] : 1;
	
	if (!empty($param['separated_by_letters'])) {	
		$separator = explode(' ', $order);
		$separator = $separator[0];
		
		$query = mysqli_query($db,
			"SELECT *, LEFT($separator , 1) AS o_char
			FROM $table
			WHERE $criteria
			ORDER BY $order"
		);
	}
	else {
		$query = mysqli_query($db,
			"SELECT *
			FROM $table
			WHERE $criteria
			ORDER BY $order"
		);
	}
	
	// Kiíró fgv. szerkesztő
	$kiir0 = explode('|', $kiir);
	$kiir_mezok0 = $kiir0[0];
	if (count($kiir0) == 2) {
		$kiir_fgv = $kiir0[0];
		$kiir_mezok0 = $kiir0[1];
	}
	$kiir_mezok = explode(',', $kiir_mezok0);
	
	while ($row = mysqli_fetch_array($query)) {
		// Betűk
		if (!empty($param['separated_by_letters'])) {
			$letter = substr($row['o_char'], 0, 1);
			if (!isset($prev) || $prev != $letter) {
				echo '<h2>'.$letter.'</h2>';
			}
			$prev = $letter;
		}
		
		// Név generálása
		foreach ($kiir_mezok as $key => $val) { // Paraméterek egyenként
			$params[$key] = $row[$val];
		}
		if (isset($kiir_fgv)) { // Saját függvény
			$text = call_user_func_array($kiir_fgv, $params);
		}
		else { // Fgv nélkül, simán space-cel összeolvasztva
			$text = implode(' ', $params);
		}
		
		// Link generálása
		$href0 = explode('|', $link);
		$href = '';
		foreach ($href0 as $id => $val) {
			if ($id % 2 == 0) {
				$href .= $val;
			}
			else {
				$href .= $row[$val];
			}
		}
		
		// Kiírás
		echo '<a href="'.$href.'">'.$text.'</a><br>';
	}
}

// 2. Szerkesztő
function edit($table, $criteria, $fields, $param) {
	// Paraméterlista beolvasása stringből
	$param = param_array($param);
	$fields = field_array($fields);
	
	global $f1db;
	$db = $f1db;
	
	// MENTÉS
	if (isset($_POST['save']) || isset($_POST['savequit'])) {
		$tosave = array();
		foreach ($fields as $element) {
			if ($element['element'] == 'input' || $element['element'] == 'textarea' || $element['element'] == 'dropdown') {
				switch ($element['params']['type']) {
					case 'checkbox':
						if (isset($_POST[$element['params']['name']])) {
							// Igen
							//echo $element['params']['name'].' = 1<br>';
							$tosave[$element['params']['name']] = 1;
						}
						else {
							// Nem
							//echo $element['params']['name'].' = 0<br>';
							$tosave[$element['params']['name']] = 0;
						}
					break;
					default:
						//echo $element['params']['name'].' = '.addslashes($_POST[$element['params']['name']]).'<br>';
						$tosave[$element['params']['name']] = addslashes($_POST[$element['params']['name']]);
				}
			}
		}
		$set = array();
		foreach ($tosave as $label => $val) {
			array_push($set, $label.' = \''.$val.'\'');
		}
		$set = implode(', ', $set);
		$query = mysqli_query($db,
			"UPDATE $table
			SET $set
			WHERE $criteria
			LIMIT 1"
		);
		
		// Vége
		if (isset($_POST['save']) && $query) {
			msg('Saved');
		}
		else if (isset($_POST['savequit']) && $query) {
			$_SESSION['msg'] = 'Saved';
			header('Location: '.$param['back_link']);
		}
		else {
			alert('Error!');
		}
	}
	
	// Képfeltöltő, socialmedia tömb
	$img_upload   = array();
	$social_media = array();
	
	// SZERKESZTÉS
	echo '<form method="post" target="_self">';
	
	$query = mysqli_query($db,
		"SELECT *
		FROM $table
		WHERE $criteria 
		LIMIT 1"
	);
	
	if (mysqli_num_rows($query) == 0) {
		if (!empty($param['back_link'])) {
			header('Location: '.$param['back_link']);
		}
	}
	
	$data = mysqli_fetch_array($query);
	
	// Cím (dinamikus)
	if (!empty($param['title'])) {
		// Kiíró fgv. szerkesztő
		$kiir0 = explode('|', $param['title']);
		$kiir_mezok0 = $kiir0[0];
		if (count($kiir0) == 2) {
			$kiir_fgv = $kiir0[0];
			$kiir_mezok0 = $kiir0[1];
		}
		$kiir_mezok = explode(',', $kiir_mezok0);
		
		foreach ($kiir_mezok as $key => $val) { // Paraméterek egyenként
			$params[$key] = $data[$val];
		}
		if (isset($kiir_fgv)) { // Saját függvény
			$title = call_user_func_array($kiir_fgv, $params);
		}
		else {
			$title = implode(' ', $params);
		}
		echo '<h1 style="margin-top:0px;">'.$title.'</h1>';
	}
	
	// Cím alatti sor
	$top_row = array();
	// Vissza
	if (!empty($param['back_link'])) {
		array_push($top_row, '<a href="'.$param['back_link'].'">Back</a>');
	}
	echo '<p>'.implode(' | ', $top_row).'</p>';
	
	// Mezők
	foreach ($fields as $field) {
		switch ($field['element']) {
			// Mező
			case 'input':
				switch($field['params']['type']) {
					case 'checkbox':
						$checked = $data[$field['params']['name']] ? ' checked' : '';
						echo '<input type="checkbox" name="'.$field['params']['name'].'"'.$checked.'>';
					break;
					default:
						echo '<input';
						foreach ($field['params'] as $p_name => $p_value) {
							echo ' '.$p_name.'="'.$p_value.'"';
						}
						echo ' value="'.$data[$field['params']['name']].'">';
				}
			break;
			// Textarea
			case 'textarea':
			break;
			// Custom dropdown
			case 'dropdown':
				call_user_func($field['params']['type'].'_dropdown', $field['params']['name'], $data[$field['params']['name']]);
			break;
			// Képfeltöltő (Muszáj ezen a formon kívül csinálni)
			case 'image':
				$criteria_field = str_replace("'", "", $criteria); // Hülye módszer...
				$criteria_field = explode(' ', $criteria_field);
				$criteria_field = $criteria_field[count($criteria_field)-1];
				$title = !empty($field['params']['title']) ? $field['params']['title'] : '';
				$uploader = array(
					$field['params']['folder'], 
					$criteria_field, $field['params']['ext'],
					$field['params']['style'], $field['params']['w'],
					$field['params']['h'], $field['params']['ratio'],
					$title
				);
				array_push($img_upload, $uploader);
			break;
			// Social media (ezt is)
			case 'social_media':
				$sm = mysqli_query($f1db, // Még egy hülye módszer, de nem lehet egyszerűbben...
					"SELECT no
					FROM $table
					WHERE $criteria"
				);
				$sm = mysqli_fetch_array($sm);
				$sm = $sm['no'];
				array_push($social_media, array($field['params']['type'], $sm));
			break;
			// Más HTML elem
			default:
				if (!empty($field['params']['inside'])) { // Páros tag
					echo '<'.$field['element'];
					foreach ($field['params'] as $p_name => $p_value) {
						if ($p_name != 'inside') {
							echo ' '.$p_name.'="'.$p_value.'"';
						}
					}
					echo '>';
					echo $field['params']['inside'];
					echo '</'.$field['element'].'>';
				}
				else { // Nem páros tag
					echo '<'.$field['element'];
					foreach ($field['params'] as $p_name => $p_value) {
						echo ' '.$p_name.'="'.$p_value.'"';
					}
					echo '>';
				}
		}
	}
	
	$delete = explode(' ', $criteria);
	$delete = explode(',', $delete[0]);
	$delete = $delete[0];
	
	// GOMBOK
	echo '<p><input type="submit" name="save" value="Save"> ';
	echo '<input type="submit" name="savequit" value="Save & Quit">';
	echo ' <a href="'.$param['back_link'].'/delete/'.$data[$delete].'">Delete</a></p>';
	echo '</form>';
	
	// Képfeltöltő
	foreach ($img_upload as $form) {
		call_user_func_array('img_upload', $form);
	}
	
	// Social media
	foreach ($social_media as $form) {
		call_user_func_array('social_media', $form);
	}
}

// 3. Hozzáadó
function add($table, $field, $length, $title, $backlink) {
	global $f1db;
	$db = $f1db;
	
	echo '<h1 style="margin-top:0px;">Add a new '.$title.'</h1>';
	echo '<p><a href="'.$backlink.'">Back</a></p>';
	
	if (isset($_POST['add']) && !empty($_POST['newid'])) {
		$id = $_POST['newid'];
		
		$exists = mysqli_query($db,
			"SELECT $field
			FROM $table
			WHERE $field = '$id'");
		
		if (mysqli_num_rows($exists) == 0) {
			$add = mysqli_query($db,
				"INSERT INTO $table($field)
				VALUES('$id')");
			if ($add) {
				$_SESSION['msg'] = 'New entry succesfully added';
				header('Location: '.$backlink.'/'.$id);
			}
			else {
				alert('MySQL error!');
			}
		}
		else {
			alert(ucfirst($title).' with '.$field.' "'.$id.'" already exists');
		}
	}
	
	echo '<form method="post">';
	echo 'id: <input name="newid" type="text" maxlength="'.$length.'">';
	echo ' <input type="submit" name="add" value="Add">';
	echo '<form>';
}

// 4. Törlő
function delete($table, $field, $subj_id, $backlink, $title) {
	global $f1db;
	$db = $f1db;
	
	if (isset($_POST['yes'])) {
		$del = mysqli_query($db,
			"DELETE FROM $table
			WHERE $field = '$subj_id'");
		if ($del) {
			$_SESSION['msg'] = 'Deleted!';
			header('Location: '.$backlink);
		}
		else {
			$_SESSION['alert'] = 'MySQL error!';
			header('Location: '.$backlink.'/'.$subj_id);
		}
	}
	if (isset($_POST['no'])) {
		header('Location: '.$backlink.'/'.$subj_id);
	}
	
	$tm = mysqli_query($db,
		"SELECT *
		FROM $table
		WHERE $field = '$subj_id'");
		
	if (mysqli_num_rows($tm) != 1) {
		header('Location: '.$backlink);
	}
	
	$row = mysqli_fetch_array($tm);
	
	// Név generálása
	$title0 = explode('|', $title);
	if (count($title0) == 2) {
		$params = explode(',', $title0[1]);
		foreach ($params as $key => $val) {
			$params[$key] = $row[$val];
		}
		$text = call_user_func_array($title0[0], $params);
	}
	else {
		$params = explode('|', $title0[0]);
		$text = implode(' ', $params);
	}
	
	echo 'Do you really delete <b>'.$text.'</b>?';
	echo '<form method="post" target="_self">';
	echo '<input type="submit" name="yes" value="Yup">';
	echo ' <input type="submit" name="no" value="Not today">';
	echo '</form>';
}

// Main
function simple_editor($table, $list_order, $name_display, $entry_link, $key_field, $fields, $key_length, $backlink, $list_parameters, $entry_parameters) {
	global $_GET;
	
	// Lista
	if (!isset($_GET['mode'])) {
		listaz($table, $list_order, $name_display, $entry_link, $list_parameters);
	}
	// Szerkeszt
	else if ($_GET['mode'] == 'edit') {
		edit($table, $key_field." = '".$_GET[$table]."'", $fields, $entry_parameters);
	}
	// Hozzáad
	else if ($_GET['mode'] == 'add') {
		add($table, $key_field, $key_length, $table, $backlink);
	}
	// Töröl
	else if ($_GET['mode'] == 'delete') {
		delete($table, $key_field, $_GET[$table], $backlink, $name_display);
	}
}
?>
