<?php
$pagetitle = 'Circuits';
require_once('included/head.php');
echo '<h1>'.$lang['circuits'].'</h1>';
// Betűk
$query_letters = mysqli_query($f1db,
	"SELECT DISTINCT UPPER(LEFT(fullname, 1)) AS letter
	FROM circuit
	ORDER BY fullname ASC");

$letters = array();
$prev = 0;

while ($row = mysqli_fetch_array($query_letters)) {
	$letter = $row['letter'];
	$link = '<a href="#' . $letter . '">' . $letter . '</a>';
	array_push($letters, $link);

	$prev = $letter;
}
echo '<p>' . implode(' &middot; ', $letters) . '</p>';

// Aktív
echo '<h2>Circuits in current calendar</h2>';

	
// Lista
$query = mysqli_query($f1db,
	"SELECT * 
	FROM circuit"
);

$circuits = array(); // egy rendezés lehet, hogy hatásosabb lenne
$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
for ($i = 0; $i < strlen($alphabet); $i++) {
	$circuits[$alphabet[$i]] = array();
}	
while ($row = mysqli_fetch_array($query)) {
	if (!empty($row['shortname'])) {
		$cname = $row['shortname'];
	}
	else {
		$cname = $row['fullname'];
	}
	
	$letter = ucfirst(substr($cname, 0, 1));
	
	array_push($circuits[$letter], array('id' => $row['id'], 'name' => $cname)); // Ezt a tömböt is rendezni kell majd
	
	/*if (!isset($prev) || $prev != $letter) {
		echo '<h2 id="' . $letter . '">' . $letter . '</h2>';
	}
	$prev = $letter;
	
	$circname = !empty($row['shortname']) ? $row['shortname'] : $row['fullname'];
	echo circuit_link($row['id'], $circname).'</br>';*/
}

foreach ($circuits as $letter => $circs) {
	if (count($circs) > 0) {
		echo "<h2>$letter</h2>";
		// Először rendezni kell a tömböt
		foreach ($circs as $circ) {
			echo circuit_link($circ['id'], $circ['name']).'<br>';
		}
	}
}

echo '<p><a href="#">Up</a> &middot; ' . implode(' &middot; ', $letters) . '</p>';
require_once('included/foot.php');
?>