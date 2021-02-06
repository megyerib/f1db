<?php
require_once('included/head_admin.php');

// Főoldal
if (!isset($_GET['user']) && !isset($_GET['add']) && !isset($_GET['delete'])) {
	echo '<h2>Users</h2>';
	echo '<a href="/admin/user/add">Add new</a>';
	
	$users = mysqli_query($sdb,
		"SELECT *
		FROM users");
	
	echo '<table>';
	while ($row = mysqli_fetch_array($users)) {
		echo '<tr>';
		echo '<td>'.$row['user'].'</td>';
		echo '<td>';
		$valid = $row['valid'];
		if ($valid == 1) {
			echo '<input type="checkbox" checked disabled>';
		}
		else {
			echo '<input type="checkbox" disabled>';
		}
		echo '</td>';
		echo '<td><a href="/admin/user/'.$row['no'].'">Edit</a></td>';
		echo '</tr>';
	}
	echo '</table>';
}

// User szerkesztés
if (isset($_GET['user'])) {
	$no = $_GET['user'];
	$user = mysqli_query($sdb,
		"SELECT *
		FROM users
		WHERE no = $no");
	
	if (mysqli_num_rows($user) == 0) {
		header('Location: /admin/user');
	}
	$row = mysqli_fetch_array($user);
	
	echo '<h2>'.$row['user'].'</h2>';
	echo '<a href="/admin/user">Back</a>';
}

// Új
if (isset($_GET['add'])) {
	if (isset($_POST['submit'])) {
		$msg = '';
		$user = $_POST['username'];
		$pw1 = $_POST['password1'];
		$pw2 = $_POST['password2'];
		
		if ($_POST['valid'] == 'on') {
			$valid = 'checked';
		}
		
		if ($pw1 != $pw2) {
			$msg = 'Two passwords don\'t match';
		}
		
		if (empty($user) ||
			empty($pw1) ||
			empty($pw2)) {
			$msg = 'Fill all fields!';		
		}
	}
	else {
		$user = '';
		$valid = '';
	}
	
	// Küld
	if ($msg = '') {
		if ($_POST['valid'] == 'on') {
			$valid = 1;
		}
		else {
			$valid = 0;
		}
		
		
		mysqli_query($sdb,
			"INSERT INTO users(user, password, valid)
			VALUES ('$user', SHA('$pw1'), $valid)");
		
		header('Location: /admin/');
	}
	
	echo '<h2>Add new user</h2>';
	echo '<a href="/admin/user">Back</a>';
	
	if (isset($msg)) {
		echo '<p style="color:red;">'.$msg.'</p>';
	}
	
	echo '<form method="post" action="/admin/user/add">';
	echo '<table>';
	echo '<tr><td>Username</td><td><input name="username" value="'.$user.'"></td></tr>';
	echo '<tr><td>Password</td><td><input type="password" name="password1"></td></tr>';
	echo '<tr><td>Password</td><td><input type="password" name="password2"></td></tr>';
	echo '<tr><td>Valid</td><td><input type="checkbox" name="valid" '.$valid.'></td></tr>';
	echo '</table>';
	echo '<input type="submit" name="submit"><br />';
	echo '</form>';
}

require_once('included/foot_admin.php');
?>