<?php
$pagetitle = 'Search';
require_once('included/head.php');
$raw_term = $_GET['term'];
$term = $raw_term;

echo '<b>Term</b>: '.$term.'<br>';
echo '<hr>';

$chars = array('.', ':', ',', '?', '!', ';', '-', '_');
$term = str_replace($chars, ' ', $term);

$terms_0 = explode(' ', $term);
$terms = array();
foreach ($terms_0 as $term) {
	if (!empty($term)) {
		array_push($terms, $term);
	}
}

// Pilóták
$results = array();
$perfect = array();
foreach ($terms as $term) {
	$drivers = mysqli_query($f1db,
		"SELECT *
		FROM driver
		WHERE first LIKE '%$term%'
		OR last LIKE '%$term%'");
	
	while ($row = mysqli_fetch_array($drivers)) {
		$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
		if (strtolower($name) == strtolower($raw_term)) {
			$perfect[$row['id']] = $name;
		}
		else {
			$results[$row['id']] = $name;
		}	
	}
}
if ((count($results) + count($perfect)) > 0) {
	echo '<h2>Drivers</h2>';
	
	foreach ($perfect as $id => $name) {
		echo '<b>'.driver_link($id, $name).'</b><br />';
	}
	foreach ($results as $id => $name) {
		echo driver_link($id, $name).'<br />';
	}
}

// Konstruktőrök
$results = array();
$perfect = array();
foreach ($terms as $term) {
	$teams = mysqli_query($f1db,
		"SELECT *
		FROM team
		WHERE fullname LIKE '%$term%'");
	
	while ($row = mysqli_fetch_array($teams)) {
		$name = $row['fullname'];
		if (strtolower($name) == strtolower($raw_term)) {
			$perfect[$row['id']] = $name;
		}
		else {
			$results[$row['id']] = $name;
		}	
	}
}
if ((count($results) + count($perfect)) > 0) {
	echo '<h2>Constructors</h2>';
	
	foreach ($perfect as $id => $name) {
		echo '<b>'.team_link($id, $name).'</b><br />';
	}
	foreach ($results as $id => $name) {
		echo team_link($id, $name).'<br />';
	}
}

// Gumik
$results = array();
$perfect = array();
foreach ($terms as $term) {
	$tyres = mysqli_query($f1db,
		"SELECT *
		FROM tyre
		WHERE fullname LIKE '%$term%'");
	
	while ($row = mysqli_fetch_array($tyres)) {
		$name = $row['fullname'];
		if (strtolower($name) == strtolower($raw_term)) {
			$perfect[$row['id']] = $name;
		}
		else {
			$results[$row['id']] = $name;
		}	
	}
}
if ((count($results) + count($perfect)) > 0) {
	echo '<h2>Tyres</h2>';
	
	foreach ($perfect as $id => $name) {
		echo '<b>'.tyre_link($id, $name).'</b><br />';
	}
	foreach ($results as $id => $name) {
		echo tyre_link($id, $name).'<br />';
	}
}
require_once('included/foot.php');
?>