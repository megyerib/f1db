<?php
$pagetitle = 'chassises';
require_once('included/head.php');

/*/ Jobb oldali táblázat
echo '<div class="right">';
echo '</div>';*/

// Betűk
$query_letters = mysqli_query($f1db,
	"SELECT DISTINCT UPPER(LEFT(fullname, 1)) AS letter
	FROM team
	WHERE chassis = 1
	ORDER BY fullname ASC");

$letters = array();
$prev = 0;

while ($row = mysqli_fetch_array($query_letters)) {
	$letter = $row['letter'];
	$link = '<a href="#' . $letter . '">' . $letter . '</a>';
	array_push($letters, $link);

	$prev = $letter;
}
echo '<p style="margin-top:0px;">' . implode(' &middot; ', $letters) . '</p>';

// Aktív
echo '<h2>Current chassis constructors</h2>';
	
// Lista
$query = mysqli_query($f1db,
	"SELECT * 
	FROM team
	WHERE chassis = 1
	ORDER BY fullname ASC");
	
while ($row = mysqli_fetch_array($query)) {
	$letter = ucfirst(substr($row['fullname'], 0, 1));
	if (!isset($prev) || $prev != $letter) {
		echo '<h2 id="' . $letter . '">' . $letter . '</h2>';
	}
	$prev = $letter;
	
	echo chassis_cons_link($row['id'], $row['fullname']).'</br>';
}

echo '<p><a href="#">Up</a> &middot; ' . implode(' &middot; ', $letters) . '</p>';

require_once('included/foot.php');
?>