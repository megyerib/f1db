<?php
$dir = 'ad/skyscraper/';
$images = scandir($dir);
$i = rand(2, sizeof($images)-1);
echo '<img id="skyscraper" src="'.root.'ad/skyscraper/'.$images[$i].'" width="120" height="600">';
?>