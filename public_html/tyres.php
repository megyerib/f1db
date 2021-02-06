<?php
$pagetitle = 'Tyres';
require_once('included/head.php');
// Jobb oldali táblázat
/*echo '<div class="right" id="tyre">' .
	'' .
	'</div>';*/

// Aktív
echo '<h2 class="top">Current tyre suppliers</h2>';

$query_current = mysqli_query($f1db,
	"SELECT DISTINCT DISTINCT active.tyre, tyre.id, tyre.fullname, tyre.country
	FROM f1_active_team AS active
	INNER JOIN tyre
	ON active.tyre = tyre.id");
	
while ($row = mysqli_fetch_array($query_current)) {
	echo flag($row['country']).tyre_link($row['id'], $row['fullname']).'</br>';
}
	
// Lista
echo '<h2>Tyre suppliers</h2>';

$query = mysqli_query($f1db,
	"SELECT *
	FROM tyre
	ORDER BY fullname ASC");
	
while ($row = mysqli_fetch_array($query)) {
	echo flag($row['country']).tyre_link($row['id'], $row['fullname']).'</br>';
}

require_once('included/foot.php');
?>