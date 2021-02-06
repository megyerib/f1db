<?php
require_once('included/head_admin.php');
// Nem írtam meg hozzá az egységes rendszert, két oldal nem ér annyit

// 1. Mind
if (!isset($_GET['mode'])) {	
	echo '<h1 style="margin-top:0px;">Chassis constructors</h1>';
	
	$conss = mysqli_query($f1db,
		"SELECT *, LEFT(fullname, 1) AS o_char
		FROM team
		WHERE chassis = 1
		ORDER BY fullname ASC");
	
	while ($row = mysqli_fetch_array($conss)) {
		$letter = strtoupper(substr($row['o_char'], 0, 1)); // Van egy kisbetűs is és ronda, ha külön szedi :s
			if (!isset($prev) || $prev != $letter) {
				echo '<h2>'.$letter.'</h2>';
			}
			$prev = $letter;
		echo '<a href="/admin/chassis/cons/'.$row['id'].'">'.$row['fullname'].'</a><br>';
	}
}

// 2. Gyártó
else if ($_GET['mode'] == 'cons') {
	$id = $_GET['cons'];
	$cons = mysqli_query($f1db,
		"SELECT *
		FROM team
		WHERE id = '$id'");
	
	if (mysqli_num_rows($cons) == 0) {header('Location: /admin/chassises');}
	
	$row = mysqli_fetch_array($cons);
	$no = $row['no'];
	
	echo '<h1 style="margin-top:0px;">'.$row['fullname'].' chassises</h1>';
	echo '<p><a href="/admin/chassis">Back</a><br>
	<a href="/admin/team/'.$id.'">Edit</a><br>
	<a href="/admin/chassis/new/'.$id.'">Add new</a></p>
	<hr style="width:100px; margin-left:0px;">';
	
	$chassises = mysqli_query($f1db,
		"SELECT *
		FROM chassis
		WHERE cons = $no");
	
	while ($ch = mysqli_fetch_array($chassises)) {
		if (!empty($ch['type'])) {
			$type = $ch['type'];
		}
		else {
			$type = 'Unknown';
		}
		echo '<a href="/admin/chassis/'.$ch['no'].'">'.$type.'</a><br>';
	}
}

// 3. 1 kasztni
else if ($_GET['mode'] == 'edit') {
	// Mentés
	if (isset($_POST['save']) || isset($_POST['savequit'])) {
		$no   = $_GET['chassis'];
		$cons = $_POST['cons'];
		$type = $_POST['type'];
		
		mysqli_query($f1db,
			"UPDATE chassis
			SET cons = $cons,
			type = '$type'
			WHERE no = $no"
		);
		
		if (isset($_POST['savequit'])) {
			$cons_id = mysqli_query($f1db,
				"SELECT id
				FROM team
				WHERE no = $cons"
			);
			$cons_id = mysqli_fetch_array($cons_id);
			$cons_id = $cons_id['id'];
			
			$_SESSION['msg'] = 'Saved';
			header('Location: /admin/chassis/cons/'.$cons_id);
		}
		
		msg('Saved');
	}
	
	$no = $_GET['chassis'];
	$chassis = mysqli_query($f1db,
		"SELECT *, chassis.no AS chassis_no, team.no AS cons_no
		FROM chassis
		INNER JOIN team
		ON chassis.cons = team.no
		WHERE chassis.no = $no");
	
	$row = mysqli_fetch_array($chassis);
	
	if ($row['type'] != '') {
		$type = $row['type'];
	}
	else {
		$type = '(Unknown)';
	}
	echo '<h1 style="margin-top:0px;">'.$row['fullname'].' '.$type.' chassis</h1>';
	echo '<p><b>Back to:</b> <a href="/admin/chassis/cons/'.$row['id'].'">'.$row['fullname'].'</a> ';
	echo '| <a href="/admin/chassis">Chassises</a></p>';
	
	//Cons
	echo '<form method="post">';
	echo '<label class="input">Constructor</label>';
	chassis_cons_dropdown('cons', $row['cons_no']);
	echo '<br><label class="input">Type</label>';
	echo '<input type="text" name="type" value="'.$row['type'].'"><br>';
	
	echo '<br><input type="submit" name="save" value="Save"> ';
	echo '<input type="submit" name="savequit" value="Save & quit">';
	echo ' <a href="/admin/chassis/delete/'.$no.'">Delete</a>';
	echo '</form>';
	
	echo '<h2>Upload image</h2>';
	img_upload('/img/chassis/', $no, '', 'width:200px;', 500, 200, 1);
}

// 4. Add chassis (csak meglévő gyártónál)
else if ($_GET['mode'] == 'add') {
	$teamid = $_GET['cons'];
	$cons = mysqli_query($f1db,
		"SELECT *
		FROM team
		WHERE id = '$teamid'");
	
	if (mysqli_num_rows($cons) == 0) {
		header('Location: /admin/chassis');
	}
	
	$row = mysqli_fetch_array($cons);
	$team_no = $row['no'];
	
	mysqli_query($f1db,
		"INSERT INTO chassis(cons, type)
		VALUES('$team_no', 'new')");
	
	$new = mysqli_query($f1db,
		"SELECT no
		FROM chassis
		WHERE cons = $team_no
		AND type = 'new'
		LIMIT 1");
	
	if (mysqli_num_rows($new) > 0) {
		$new = mysqli_fetch_array($new);
		$new_no = $new['no'];
		$_SESSION['msg'] = 'New chassis succesfully added';
		header('Location: /admin/chassis/'.$new_no);
	}
	else {
		$_SESSION['alert'] = 'MySQL Error!';
		header('Location: /admin/chassis');
	}
}
// 5. Törlés
else if ($_GET['mode'] == 'delete') {
	$ch_no = $_GET['chassis'];
	
	if (isset($_POST['yes'])) {		
		$cons_id = mysqli_query($f1db, // Törlés előtt meghatározza a gyártót
			"SELECT team.id AS teamid
			FROM chassis
			INNER JOIN team
			ON chassis.cons = team.no
			WHERE chassis.no = $ch_no"
		);
		$cons_id = mysqli_fetch_array($cons_id);
		$cons_id = $cons_id['teamid'];
		
		$delete = mysqli_query($f1db,
			"DELETE
			FROM chassis
			WHERE no = $ch_no"
		);
		
		if ($delete) {
			$_SESSION['msg'] = 'Deleted';
			header('Location: /admin/chassis/cons/'.$cons_id);
			// Miért nem irányít át rendesen? Ha kiveszem a törlés parancsot, minden faszán működik.
		}
		else {
			$_SESSION['alert'] = 'MySQL Error!';
			header('Location: /admin/chassis/'.$ch_no);
		}
	}
	if (isset($_POST['no'])) {
		header('Location: /admin/chassis/'.$ch_no);
	}
	
	$ch = mysqli_query($f1db,
		"SELECT *
		FROM chassis
		WHERE no = $ch_no");
		
	if (mysqli_num_rows($ch) == 0) {
		header('Location: /admin/chassis');
	}
	
	echo '<h1 style="margin-top:0px;">Do you really delete this chassis?</h1>';
	echo '<form method="post">';
	echo '<input type="submit" name="yes" value="Yup"> ';
	echo '<input type="submit" name="no" value="Not today">';
	echo '</form>';
}
require_once('included/foot_admin.php');
?>
