<?php
/*require_once('included/database.php');

$id = $_GET['circuit'];

$query = mysqli_query($f1db,
	"SELECT *, country.country AS countryname, circuit.country AS ctry
	FROM circuit
	INNER JOIN country
	ON circuit.country = country.gp
	WHERE id = '$id'
	LIMIT 1");
	
// Rossz link
if (mysqli_num_rows($query) == 0) { header('Location: '.root.'circuit');}

$circ = mysqli_fetch_array($query);

$name = $circ['fullname'];
$no = $circ['no'];
$pagetitle = $name;*/
require_once('included/head.php');
$id = $_GET['circuit'];

$query = mysqli_query($f1db,
	"SELECT *, country.country AS countryname, circuit.country AS ctry
	FROM circuit
	INNER JOIN country
	ON circuit.country = country.gp
	WHERE id = '$id'
	LIMIT 1");
	
// Rossz link
if (mysqli_num_rows($query) == 0) { header('Location: /circuit');}

$circ = mysqli_fetch_array($query);

$circ_name = $circ['fullname'];
$no = $circ['no'];

// Doboz
echo '<div class="right">';
	// Időjárás
	$local_id = $circ['weather_id'];
	if (!empty($local_id)) {
		echo '<b>Weather</b><hr>';
		require_once('included/weather.php');
	}
echo '</div>';

// SOCIAL MEDIA
$medias = mysqli_query($f1db,
	"SELECT *
	FROM social_media
	WHERE subj_type = 'C'
	AND subj = $no
	ORDER BY type ASC");
if (mysqli_num_rows($medias) > 0) {
	echo '<div class="right">';
	echo '<div class="thead">Social media</div>';
	while ($row = mysqli_fetch_array($medias)) {
		media_list($row['type'], $name = $row['name']);
	}
	echo '</div>';
} // Doboz vége

echo '<h1 class="title">'.$circ_name.'</h1>';

// Logo
$logo = 'images/circuit_logo/'.$id.'.png';
if (file_exists($logo)) {
	echo '<p><img src="/'.$logo.'" style="max-width:300px;"></p>';
}

// Pályarajz
$diag = 'images/circuit/'.$id.'.png';
if (file_exists($diag)) {
	echo '<p><img src="/'.$diag.'" style="max-width:500px; max-height:500px;"></p>';
}

echo '<b>Place</b>: '.$circ['place'].', '.country_link($circ['ctry'], $circ['countryname']).'</br>';


// Maps
if ($circ['lat'] > 0 && $circ['lon'] > 0) {
	echo '<p><a href="https://www.google.com/maps/@'.$circ['lat'].','.$circ['lon'].',15z" target="_blank">On the map</a></p>';
}

require_once('included/foot.php');
?>