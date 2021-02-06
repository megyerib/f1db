<?php
$url = $_GET['url'];
$lan = $_GET['lang'];
header('Location: /included/language/language.php?url='.$url.'&lang='.$lan);
// Simán átirányít, hogy kívülről ne lehessen látni a mappa címét
?>