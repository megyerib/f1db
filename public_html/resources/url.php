<?php
function url($type, $p1 = 0, $p2 = 0, $p3 = 0) {
	if ($p1) // Elég az elsőt vizsgálni
		switch ($type) {
			case 'seasons':	return "/seasons.php?id=$p1"; // Sorozat
			case 'driver':	return "/driver.php?id=$p1"; // Pilóta
			case 'cons':	return "/cons.php?id=$p1"; // Konstruktőr
			case 'country':	return "/country.php?id=$p1"; // Országkód
			case 'season':	return "/season.php?series=$p1&year=$p2"; // Sorozat, év
			case 'event':   return "/event.php?series=$p1&year=$p2&gp=$p3"; // Sorozat, év, nagydíj
			case 'tyre':    return "/tyre.php?id=$p1"; // Gumi

			default:		return "/";
		}
	else
		switch ($type) {
			case 'season':
			case 'series':	return "/series.php";
			case 'driver':	return "/drivers.php";
			case 'country':	return "/countries.php";

			default:		return "/";
		}
}

function a($type, $text, $p1 = 0, $p2 = 0, $p3 = 0) {
	$url = url($type, $p1, $p2, $p3);
	return "<a href='$url'>$text</a>";
}

function a_title($type, $text, $title, $p1 = 0, $p2 = 0, $p3 = 0) {
	$url = url($type, $p1, $p2, $p3);
	return "<a href='$url' title='$title'>$text</a>";
}

function flag($short, $long = 0) {
	$long = $long ? $long : $short;
	$img = "<img src='/img/flagIcon/$short.png' class='flagIcon'>";
	return a_title('country', $img, $long, $short);
}
?>
