<?php
ob_start();
error_reporting(0);
//require_once('included/maintenance.php'); // Betölt egy oldalt, majd megszakítja a betöltést
?>
<!DOCTYPE html><html>
<head>
	<meta charset="windows-1252">
<?php	
	$require = array (
		'vars',
		'database',
		'functions/functions',
		'functions/link',
		'functions/tables',
		'language/language'
	);
	foreach ($require as $file) { require_once($file.'.php'); }
?>
	<title><?php // TITLE
		if (isset($pagetitle)) {
			if(isset($lang[$pagetitle])){ echo $pagetitle = $lang[$pagetitle]; }
			else { echo $pagetitle.' - race-data.net'; }
		} else{	echo 'Race-Data.net F1 database';	} // Alap oldalcím
	?></title>
	
	<meta name="description" content="
		Race-data.net - Detailed Formula One statistics from 1950 till today. Race results, driver, team, constructor, circuit infos and much more.
	">
	
	<meta name="keywords" content="
		Formula One, Formula 1, F1, Forma 1, Formel 1,
		statistics, statistic, stat, stats,
		data,
		archive,
		vettel, raikkonen, alonso, hamilton,
		mclaren, ferrari, lotus, red bull, mercedes, renault
		schumacher, senna, prost, lauda, hunt, piquet, fangio, ascari,
		brabham, benetton, tyrrell,
		driver, chassis, engine, sponsor, season, race, grand prix,
		tyre, pirelli, bridgestone, michelin,
	">
	
<?php
	$css = array(
		'main',
		'message',
		'menu',
		'table',
		'div',
		'tyre',
		'score'
	);
	
	foreach ($css as $sheet) {
		echo '<link rel="stylesheet" type="text/css" href="/css/'.$sheet.'.css">';
	}
	
	echo '<link rel="shortcut icon" href="/images/favicon.png">';
	if (isset($cke)) {
	echo '<script src="/scripts/ckeditor/ckeditor.js"></script>';
	}
	echo '<script type="text/javascript" src="https://www.google.com/jsapi"></script>';
?>
</head>
<?php
// BODY
?>
<body>
<div id="borders" style="width:920px;margin:auto;">		
<?php
	// Analytics
	include_once("analyticstracking.php");
	// Nyelvválasztó
	require_once('language/select.php');
?>
		
<!-- HEAD -->
	<div class="head"><div>
	<table width="100%"><tr><td>	
		<a href="/"><img src="/images/logo.png" height="70" alt="logo"></a>
	</td><td align="right">
		<form id="searchthis" action="/search" style="display:inline;" method="get">
		<input type="search" id="namanyay-search-box" name="term" size="40" type="text" placeholder="Search"/>
		<input id="namanyay-search-btn" value=" " type="submit"/></form><br>
		
		<!--<a class="facebook_icon social_icon" href="https://www.facebook.com/racedata"></a>
		<a class="twitter_icon social_icon" href="https://twitter.com/racedatanet"></a>-->
		<a class="rss_icon social_icon" href="http://race-data.net/rss"></a>
	</td></tr></table>
	
	
	
	</div></div>
	
<!-- MENU -->	
<?php
	require_once('menu.php');
?>	
		<!-- MAIN -->
		<div class="shadow_bot"></div>
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