<?php
// 0. INIT
$includeResources = "config functionsMisc functionsLink vars";
foreach (explode(' ',$includeResources) as $php) {
	include("resources/$php.php");
}

?>
<!DOCTYPE html>
<html>
<head>
<?php
// 1. HTML HEAD

	// Css loader
	
	$cssLoad = 'font main div menu specific table';
	foreach (explode(' ', $cssLoad) as $sheet) {
		echo '<link rel="stylesheet" type="text/css" href="/css/'.$sheet.'.css">'."\n";
	}
	$cssOther = 'teams.php';
	foreach (explode(' ', $cssOther) as $sheet) {
		echo '<link rel="stylesheet" type="text/css" href="/css/'.$sheet.'">'."\n";
	}
?>	
</head>
<body>
<?php
// 2. HTML BODY
?>
<div id="divHead"><!-- HEADER BEGIN -->
<?php
// 3. HEAD
echo 'Header';
?>
</div><!-- HEADER END -->
<div id="divMenu"><!-- MENU BEGIN -->
<?php
// 4. MENÜ
require_once('menu.php');
?>
</div><!-- MENU END -->
<div id="divMain"><!-- CONTENT BEGIN -->
<?php
// 5. TARTALOM
?>
