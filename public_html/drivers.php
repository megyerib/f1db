<?php
$pagetitle = 'drivers';
require_once('included/head.php');

// Fő Lekérdezés
if (!isset($_GET['order'])) { // Nincs rendezés
	$mainq = mysqli_query($f1db,
		"SELECT * " .
		"FROM driver " . 
		"ORDER BY last ASC");
}
else { // Van rendezés
	switch ($_GET['order']) {                     // WHERE záradékok, normálisabb
		case 1: // Qualified
			$mainq = mysqli_query($f1db,
				"SELECT DISTINCT race.driver, driver.id, driver.country,
					driver.first, driver.de, driver.last, driver.sr
				FROM f1_race AS race
				INNER JOIN driver
				ON (race.driver = driver.no)
				WHERE status <= 4
				ORDER BY driver.last ASC");
			$ttl = 'Qualifiers';
			break;
		
		case 2: // Finished
			$mainq = mysqli_query($f1db,
				"SELECT DISTINCT race.driver, driver.id, driver.country,
					driver.first, driver.de, driver.last, driver.sr
				FROM f1_race AS race
				INNER JOIN driver
				ON (race.driver = driver.no)
				WHERE status = 1
				ORDER BY driver.last ASC");
			$ttl = 'Finishers';
			break;
						
		case 3: // Podium
			$mainq = mysqli_query($f1db,
				"SELECT DISTINCT race.driver, driver.id, driver.country,
					driver.first, driver.de, driver.last, driver.sr
				FROM f1_race AS race
				INNER JOIN driver
				ON (race.driver = driver.no)
				WHERE finish <= 3
				ORDER BY driver.last ASC");
			$ttl = 'Podium finishers';
			break;
			
		case 4: // Winner
			$mainq = mysqli_query($f1db,
				"SELECT DISTINCT race.driver, driver.id, driver.country,
					driver.first, driver.de, driver.last, driver.sr
				FROM f1_race AS race
				INNER JOIN driver
				ON (race.driver = driver.no)
				WHERE finish = 1
				ORDER BY driver.last ASC");
			$ttl = 'Winners';
			break;
			
		case 5: // Champions
			$mainq = mysqli_query($f1db,
				"SELECT DISTINCT tbl.driver, driver.id, driver.country,
					driver.first, driver.de, driver.last, driver.sr
				FROM f1_tbl AS tbl
				INNER JOIN driver
				ON (tbl.driver = driver.no)
				WHERE place = 1
				ORDER BY driver.last ASC");
			$ttl = 'World champions';
			break;
			
		case 6: // Nem 500
			$mainq = mysqli_query($f1db,
				"SELECT DISTINCT tbl.driver, driver.id, driver.country,
					driver.first, driver.de, driver.last, driver.sr
				FROM f1_tbl AS tbl
				INNER JOIN driver
				ON (tbl.driver = driver.no)
				WHERE i500 = 0
				ORDER BY driver.last ASC");
			$ttl = 'Excl. only Indy 500 participlers';
			break;
			
		default:
			header('Location: '.$_SERVER['PHP_SELF']);
			break;
	}
}

$num_drivers = mysqli_num_rows($mainq);

if (isset($ttl)) {
	echo '<h2>'.$ttl.' ('.$num_drivers.' drivers)</h2>';
}/*
?>
<div class="right" id="driver">

<h3>Sort drivers</h3>
<a href="?">All</a><br>
<a href="?order=1">Only qualifiers</a><br>
<a href="?order=2">Only finishers</a><br>
<a href="?order=3">Only podium finishers</a><br>
<a href="?order=4">Only winners</a><br>
<a href="?order=5">Only champions</a><br>
<a href="?order=6">Excluded Indy500</a><br>

</div>
<?php
// Aktív
*/
/*
echo '<h2>Current drivers</h2>';

$query_current = mysqli_query($f1db,
	"SELECT first, de, last, sr, id, country
	FROM f1_active_driver AS active
	INNER JOIN driver
	ON active.no = driver.no
	ORDER BY last ASC");
	
while ($row = mysqli_fetch_array($query_current)) {
	$name = name($row['first'], $row['de'], $row['last'], $row['sr']);
	echo flag($row['country']).driver_link($row['id'], $name).'<br>';
}

*/

active_teams($f1db);

// Betűk
$query_letters = mysqli_query($f1db,
	"SELECT DISTINCT LEFT(last, 1) AS letter
	FROM driver
	ORDER BY last ASC");

$letters = array();
$prev = 0;

while ($row = mysqli_fetch_array($query_letters)) {
	$letter = $row['letter'];

	$link = '<a href="#' . $letter . '">' . $letter . '</a>';
	array_push($letters, $link);
	$prev = $letter;
}
echo implode(' &middot; ', $letters) . '<br><br>';

while ($row = mysqli_fetch_array($mainq)) {
	$letter = substr($row['last'], 0, 1);
	if (!isset($prev) || $prev != $letter) {
		echo '<h2 id="' . $letter . '" style="margin-top:15px;">' . $letter . '</h2>';
	}
	$prev = $letter;
	
	//$name = name($row['first'], $row['de'], $row['last'], $row['sr'], 2);
	
	$f  = $row['first'];
	$la = $row['de'];
	$s  = $row['last'];
	$sr = $row['sr'];
	
	if ($la != '') {
		$la = $la . ' ';
	}
	if ($sr != '') {
		$sr = ', ' . $sr . '.';
	}
	if ($f != '') {
		$f = ', ' . $f;
	}
	
	$name = $la . $s . $sr . $f;
	
	echo flag($row['country']).driver_link($row['id'], $name).'<br>';
	
}

echo '<br><br><a href="#">Up</a> &middot; ' . implode(' &middot; ', $letters);

require_once('included/foot.php');
?>