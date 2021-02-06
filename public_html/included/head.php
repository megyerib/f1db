<?php
//error_reporting(0);
//require_once('included/maintenance.php'); // Betölt egy oldalt, majd megszakítja a betöltést
ob_start();
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html><html>
<head>
	<meta charset="UTF-8">
<?php	
	$require = 'vars database functions/functions functions/link functions/tables language/language';
	$require = explode(' ', $require);
	foreach ($require as $file) { require_once($file.'.php'); }
?>
	<title><?php // TITLE
		if (isset($pagetitle)) {
			if(isset($lang[$pagetitle])){ echo $pagetitle = $lang[$pagetitle]; }
			else { echo $pagetitle.' - race-data.net'; }
		} else{	echo 'Race-Data.net F1 database';	} // Alap oldalcím
	?></title>
	
	<!-- Auto -->
	<meta name="description" content="">
	<meta name="keywords" content="">
	
<?php
	// CSS-ek betöltése
	$css = 'main message menu table div tyre score search';
	$css = explode(' ', $css);
	foreach ($css as $sheet) {
		echo '<link rel="stylesheet" type="text/css" href="/css/'.$sheet.'.css">';
	}
	
	// CKEditor betöltése (ha kell)
	if (isset($cke)) {
		echo '<script src="/scripts/ckeditor/ckeditor.js"></script>';
	}
	// Favicon
	echo '<link rel="shortcut icon" href="/images/favicon.png">';
	// ???
	echo '<script type="text/javascript" src="https://www.google.com/jsapi"></script>';
?>
</head>
<body>			
<?php
	// Analytics
	include_once("analyticstracking.php");
	// Nyelvválasztó -> Át fogom rakni máshova
	require_once('language/select.php');
?>
		
<!-- HEAD -->
<div class="main">
<div class="header">

<!-- Right part -->
<div style="float:right; height:auto;"><form method="get" action="/search" id="search">
	<input name="term" type="search" size="40" placeholder="Search" style="position:relative; top:0px;"><!-- Miért nem tetszik neki, ha lejjebb tolom? -->
</form></div>

<!-- Logo -->
<img src="/images/logo.png" style="width:200px;">

</div>
	<!-- RÉGI
	<div class="head"><div>
	<table width="100%"><tr><td>	
		<a href="/"><img src="/images/logo.png" height="70" alt="logo"></a>
	</td><td align="right">
		<form id="searchthis" action="/search" style="display:inline;" method="get">
		<input type="search" id="namanyay-search-box" name="term" size="40" type="text" placeholder="Search"/>
		<input id="namanyay-search-btn" value=" " type="submit"/></form><br>
		
		<a class="facebook_icon social_icon" href="https://www.facebook.com/racedata"></a>
		<a class="twitter_icon social_icon" href="https://twitter.com/racedatanet"></a>
		<a class="rss_icon social_icon" href="http://race-data.net/rss"></a>
	</td></tr></table>
	
	
	
	</div></div>-->
	
<!-- MENU -->	
<div class="menu">
<?php require_once('menu.php'); ?>
</div>


<!-- MAIN -->
<div class="main_content">
	
<?php
/*if (isset($maintitle) || isset($pagetitle)) {
	echo '<!-- TITLE -->';
	if (!isset($maintitle)) {
		$maintitle = $pagetitle;
	}
	
	echo '<div class="title">';
		$text = strpos($maintitle, '<');
		if ($text === false) {
			$maintitle = '<h1>'.$maintitle.'</h1>';
		}
		echo $maintitle;
	echo '</div>';
}*/

?>