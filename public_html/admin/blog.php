<?php
$cke = true;
require_once('included/head_admin.php');

// FŐOLDAL
if (isset($_GET['main'])) {
	echo '<h1>Blog articles</h1>';
	echo '<p><a href="/admin/blog/new">New</a></p>';
	
	$articles = mysqli_query($sdb,
		"SELECT *
		FROM blog
		ORDER BY time DESC");
	
	echo '<table>';
	while ($row = mysqli_fetch_array($articles)) {
		echo '<tr>';
		echo '<td><a href="/admin/blog/'.$row['no'].'">'.$row['id'].'</a></td>';
		echo '<td><b>'.$row['title'].'</b></td>';
		if ($row['public'] != 0) {
			$public = '<img src="/images/admin/green.png" width="14" height="14">';
		}
		else {
			$public = '<img src="/images/admin/red.png" width="14" height="14">';
		}
		echo '<td>'.$public.'</td>';
		echo '<td>'.$row['time'].'</td>';
		echo '</tr>';
	}
	echo '</table>';
}

// EGY BEJEGYZÉS
if (isset($_GET['entry'])) {
	echo '<h1>Edit article</h1>';
	echo '<p><a href="/admin/blog">Back</a></p>';
	
	$entry_no = $_GET['entry'];
	
	if (isset($_POST['save'])) {
		$title = $_POST['title'];
		$id    = $_POST['id'];
		$time  = $_POST['time'];
		$head  = $_POST['head'];
		$text  = $_POST['text'];
		if ($_POST['public'] == 1) {
			$public = 1;
		}
		else {
			$public = 0;
		}
		
		if (!empty($title)) { // Minden fasza
			if (empty($id)) {
				$id = preg_replace('/[^a-zA-Z0-9\s]/', '', $title);
				$id = str_replace(' ', '_', $id);
				$id = strtolower(substr($id, 0, 25));
			}
			
			$edit_entry = mysqli_query($sdb,
				"UPDATE blog
				SET title = '$title',
				id = '$id',
				time = '$time',
				head = '$head',
				text = '$text',
				public = $public
				WHERE no = $entry_no");
		}
		else { // Nem az, mert nincs cím
			$edit_entry = false;
		}
	}
	
	if ($edit_entry || !isset($edit_entry)) {	
		$article = mysqli_query($sdb,
			"SELECT *
			FROM blog
			WHERE no = $entry_no");
		if (mysqli_num_rows($article) == 0) {
			header('Location: /admin/blog');
		}
		$article = mysqli_fetch_array($article);
		
		$title  = $article['title'];
		$id     = $article['id'];
		$time   = $article['time'];
		$head   = $article['head'];
		$text   = $article['text'];
		$public = $article['public'];
	}
	else {
		echo '<p style="color:red; font-weight:bold;">No title!</p>';
	}
	
	echo '<form method="post" action="/admin/blog/'.$entry_no.'">';
	echo 'Title: <input type="text" name="title" value="'.$title.'"><br>';
	echo 'ID: <input type="text" name="id" value="'.$id.'"><br>';
	$time = substr($time, 0, -3); // Idő átalakítása
	$time[10] = 'T';
	echo 'Date: <input type="datetime-local" name="time" value="'.$time.'"><br>';
	
	if ($public == 1) {$checked = ' checked';}
	else {$checked = '';}
	echo '<input type="checkbox" name="public" value="1"'.$checked.'> Public<br><br>';
	
	echo 'Head<br>';
	echo '<textarea name="head">'.$head.'</textarea><br>';
	echo 'Article<br>';
	echo '<textarea name="text" id="main_editor">'.$text.'</textarea><br>';
	echo '<script> CKEDITOR.replace( "main_editor" ); </script>';
	echo '<input type="submit" name="save" value="Save">';
		
	echo '</form>';
	echo '<form method="post" action="/admin/blog/delete/'.$entry_no.'"><input type="submit" value="Delete"></form>';
}

// ÚJ
if (isset($_GET['new'])) {
	$biggest = mysqli_query($sdb,
		"SELECT no
		FROM blog
		ORDER BY no DESC
		LIMIT 1");
	$biggest = mysqli_fetch_array($biggest);
	$next = $biggest['no'] + 1;
	
	$add_new = mysqli_query($sdb,
		"INSERT INTO blog(no, public, time)
		VALUES($next, 0, NOW())");
		
	header('Location: /admin/blog/'.$next);
}

// TÖRLÉS
if (isset($_GET['delete'])) {
	$entry_no = $_GET['delete'];
	
	if (isset($_POST['yes'])) {
		mysqli_query($sdb,
			"DELETE FROM blog
			WHERE no = $entry_no");
		header('Location: /admin/blog');
	}
	if (isset($_POST['no'])) {
		header('Location: /admin/blog/'.$entry_no);
	}
	
	$article = mysqli_query($sdb,
		"SELECT title
		FROM blog
		WHERE no = $entry_no");
	$article = mysqli_fetch_array($article);
	$title = $article['title'];
	
	echo '<h1>Delete article</h1>';
	echo '<p>Do you really delete this article? ('.$title.')</p>';
	echo '<form method="post" action="/admin/blog/delete/'.$entry_no.'">';
	echo '<input type="submit" name="yes" value="Yes">';
	echo '<input type="submit" name="no" value="No">';
	echo '</form>';
}
require_once('included/foot_admin.php');
?>