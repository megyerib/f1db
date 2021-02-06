<?php
require_once('included/head_admin.php');
// Nem írtam meg hozzá az egységes rendszert, két oldal nem ér annyit

// 1. Mind
if (!isset($_GET['mode'])) {	
	echo '<h1 style="margin-top:0px;">Engine constructors</h1>';
	
	$conss = mysqli_query($f1db,
		"SELECT *, LEFT(fullname, 1) AS o_char
		FROM team
		WHERE engine = 1
		ORDER BY fullname ASC");
	
	while ($row = mysqli_fetch_array($conss)) {
		$letter = substr($row['o_char'], 0, 1);
			if (!isset($prev) || $prev != $letter) {
				echo '<h2>'.$letter.'</h2>';
			}
			$prev = $letter;
		echo '<a href="/admin/engine/cons/'.$row['id'].'">'.$row['fullname'].'</a><br>';
	}
}

// 2. Gyártó
else if ($_GET['mode'] == 'cons') {
	$id = $_GET['cons'];
	$cons = mysqli_query($f1db,
		"SELECT *
		FROM team
		WHERE id = '$id'");
	
	if (mysqli_num_rows($cons) == 0) {header('Location: /admin/engines');}
	
	$row = mysqli_fetch_array($cons);
	$no = $row['no'];
	
	echo '<h1 style="margin-top:0px;">'.$row['fullname'].' engines</h1>';
	echo '<p><a href="/admin/engine">Back</a><br>
	<a href="/admin/team/'.$id.'">Edit</a><br>
	<a href="/admin/engine/new/'.$id.'">Add new</a></p>
	<hr style="width:100px; margin-left:0px;">';
	
	$engines = mysqli_query($f1db,
		"SELECT *
		FROM engine
		WHERE cons = $no");
	
	// Módosított név fgv.
	function engine_name2($type, $volume, $concept, $cylinders, $turbo) {
		if (empty($type)) {
			$unknown = 'Unknown ';
			$volume = $volume > 0 ? ' '.$volume : '';
			$param = $volume.' '.$concept.$cylinders.$turbo;
			$type = '';
		} else {
			$unknown = '';
			$param = '';
			$type = $type;
		}
		return $unknown.$type.$param;
	}
	
	while ($en = mysqli_fetch_array($engines)) {
		echo '<a href="/admin/engine/'.$en['no'].'">'.engine_name2($en['type'], $en['volume'], $en['concept'], $en['cylinders'], $en['turbo']).'</a><br>';
	}
}

// 3. 1 motor
else if ($_GET['mode'] == 'edit') {
	// Mentés
	if (isset($_POST['save']) || isset($_POST['savequit'])) {
		$no   = $_GET['engine'];
		$cons = $_POST['cons'];
		$type = $_POST['type'];
		
		$volume = $_POST['volume'];
		$concept = $_POST['concept'];
		$cylinders = $_POST['cylinders'];
		$turbo = $_POST['turbo'];
		
		mysqli_query($f1db,
			"UPDATE engine
			SET cons = $cons,
			type = '$type',
			volume = $volume,
			concept = '$concept',
			cylinders = $cylinders,
			turbo = '$turbo'
			WHERE no = $no");
		
		if (isset($_POST['savequit'])) {
			$cons_id = mysqli_query($f1db,
				"SELECT id
				FROM team
				WHERE no = $cons"
			);
			$cons_id = mysqli_fetch_array($cons_id);
			$cons_id = $cons_id['id'];
			
			$_SESSION['msg'] = 'Saved';
			header('Location: /admin/engine/cons/'.$cons_id);
		}
		
		msg('Saved');
	}
	
	// Mentés
	
	$no = $_GET['engine'];
	$engine = mysqli_query($f1db,
		"SELECT *, engine.no AS engine_no, team.no AS cons_no
		FROM engine
		INNER JOIN team
		ON engine.cons = team.no
		WHERE engine.no = $no");
	
	$row = mysqli_fetch_array($engine);
	
	if ($row['type'] != '') {
		$type = $row['type'];
	}
	else {
		$type = '(Unknown)';
	}
	echo '<h1 style="margin-top:0px;">'.engine_name($row['fullname'], $row['type'], $row['volume'], $row['concept'], $row['cylinders'], $row['turbo']).' engine</h1>';
	echo '<p><b>Back to:</b> <a href="/admin/engine/cons/'.$row['id'].'">'.$row['fullname'].'</a> ';
	echo '| <a href="/admin/engine">engines</a></p>';
	
	// Parameters
	echo '<form method="post">';
	
	echo '<label class="input">Constructor</label>';
	engine_cons_dropdown('cons', $row['cons_no']);
	echo '<br><label class="input">Type</label>';
	echo '<input type="text" name="type" value="'.$row['type'].'"><br><br>';
	
	echo '<label class="input">Volume</label>';
	echo '<input type="text" name="volume" value="'.$row['volume'].'" size="3"><br>';
	echo '<label class="input">Concept</label>';
	echo '<input type="text" name="concept" value="'.$row['concept'].'" size="3"><br>';
	echo '<label class="input">Cylinders</label>';
	echo '<input type="text" name="cylinders" value="'.$row['cylinders'].'" size="3"><br>';
	echo '<label class="input">Turbo</label>';
	echo '<input type="text" name="turbo" value="'.$row['turbo'].'" size="3"><br>';
	
	echo '<br><input type="submit" name="save" value="Save"> ';
	echo '<input type="submit" name="savequit" value="Save & quit">';
	echo ' <a href="/admin/engine/delete/'.$no.'">Delete</a>';
	echo '</form>';
	
	echo '<h2>Upload image</h2>';
	img_upload('/images/engine/', $no, '', 'width:200px;', 300, 300, 1);
}

// 4. Add engine (csak meglévő gyártónál)
else if ($_GET['mode'] == 'add') {
	$teamid = $_GET['cons'];
	$cons = mysqli_query($f1db,
		"SELECT *
		FROM team
		WHERE id = '$teamid'");
	
	if (mysqli_num_rows($cons) == 0) {
		header('Location: /admin/engine');
	}
	
	$row = mysqli_fetch_array($cons);
	$team_no = $row['no'];
	
	mysqli_query($f1db,
		"INSERT INTO engine(cons, type)
		VALUES('$team_no', 'new')");
	
	$new = mysqli_query($f1db,
		"SELECT no
		FROM engine
		WHERE cons = $team_no
		AND type = 'new'
		LIMIT 1");
	
	if (mysqli_num_rows($new) > 0) {
		$new = mysqli_fetch_array($new);
		$new_no = $new['no'];
		$_SESSION['msg'] = 'New engine succesfully added';
		header('Location: /admin/engine/'.$new_no);
	}
	else {
		$_SESSION['alert'] = 'MySQL Error!';
		header('Location: /admin/engine');
	}
}
// 5. Törlés
else if ($_GET['mode'] == 'delete') {
	$en_no = $_GET['engine'];
	
	if (isset($_POST['yes'])) {		
		$cons_id = mysqli_query($f1db, // Törlés előtt meghatározza a gyártót
			"SELECT team.id AS teamid
			FROM engine
			INNER JOIN team
			ON engine.cons = team.no
			WHERE engine.no = $en_no"
		);
		$cons_id = mysqli_fetch_array($cons_id);
		$cons_id = $cons_id['teamid'];
		
		$delete = mysqli_query($f1db,
			"DELETE
			FROM engine
			WHERE no = $en_no"
		);
		
		if ($delete) {
			$_SESSION['msg'] = 'Deleted';
			header('Location: /admin/engine/cons/'.$cons_id);
			// Miért nem irányít át rendesen? Ha kiveszem a törlés parancsot, minden faszán működik.
		}
		else {
			$_SESSION['alert'] = 'MySQL Error!';
			header('Location: /admin/engine/'.$en_no);
		}
	}
	if (isset($_POST['no'])) {
		header('Location: /admin/engine/'.$en_no);
	}
	
	$en = mysqli_query($f1db,
		"SELECT *
		FROM engine
		WHERE no = $en_no");
		
	if (mysqli_num_rows($en) == 0) {
		header('Location: /admin/engine');
	}
	
	echo '<h1 style="margin-top:0px;">Do you really delete this engine?</h1>';
	echo '<form method="post">';
	echo '<input type="submit" name="yes" value="Yup"> ';
	echo '<input type="submit" name="no" value="Not today">';
	echo '</form>';
}
require_once('included/foot_admin.php');
?>