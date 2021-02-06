<?php
// Session start
session_start();

// Include ...
include 'lib/db.php';
include 'login.php';

// HTML fejléc
?>
<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html" charset="windows-1252">
	<link rel="stylesheet" type="text/css" href="/css/admin.css">
	<link rel="stylesheet" type="text/css" href="/css/checkbox.css">
	<link rel="shortcut icon" href="/images/admin/favicon.png">
	<script src="/script/jquery.js"></script>
	
	<?php if (isset($cke)) {echo '<script src="script/ckeditor_adv/ckeditor.js"></script>';} ?>
</head>
<body>
<?php
	// Ezeket minek mindig betölteni?
	
	/*include 'lib/vars.php';
	include 'lib/functions.php';
	include 'lib/dropdown.php';
	include 'lib/entry_editor.php';
	include 'lib/image.php';
	include 'lib/social_media.php';
	include 'lib/db.php';*/
	
	// Message
	if (isset($_SESSION['alert'])) {
		alert($_SESSION['alert']);
		unset($_SESSION['alert']);
	}
	if (isset($_SESSION['msg'])) {
		  msg($_SESSION['msg']);
		unset($_SESSION['msg']);
	}
	
	echo '<span style="position:absolute; right:0px; padding-right:10px; text-align:right;">';
	echo 'Logged in as ' . $_SESSION['user'] . '<br />';
	echo '<a href="/admin/included/logout.php">Log out</a>';
	echo '</span>';
	
	// Unread messages
	$msgs = mysqli_query($sdb,
		"SELECT count(*) AS count
		FROM message
		WHERE readed = 0");
	$msgs = mysqli_fetch_array($msgs);
	$msgs = $msgs['count'];
	if ($msgs > 0) {
		echo '<div style="position:absolute; left:20px; top:20px;">';
		echo '<a href="/admin/messages">';
		echo '<img src="/images/admin/message.png" height="32">';
		echo '<span style="position:relative; right:5px;
			background-color:red; padding:2px; color:white; font-weight:bold; border-radius:3px;">'.$msgs.'</span>';
		echo '</a>';
		echo '</div>';
	}
?>
<p style="text-align:center; margin-top:0px; font-weight:bold; font-size:32px;">Admin</p>
<div style="text-align:center;">
<a href="/admin/">Home</a>
|
<a href="/admin/races">Races</a>
|
<a href="/admin/driver">Drivers</a>
|
<a href="/admin/team">Teams</a>
|
<a href="/admin/circuit">Circuits</a>
|
<a href="/admin/tyre">Tyres</a>
|
<a href="/admin/messages">Messages</a>
|
<a href="/admin/blog">Blog</a>
</div><hr>
<div style="overflow:auto;">