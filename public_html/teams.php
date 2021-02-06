<?php
$pagetitle = 'teams';
require_once('included/head.php');

// Jobb oldali táblázat
echo '<div class="right">';
echo '<a href="?">All</a><br>';
echo '<a href="?mode=1">Non-private</a><br>';
echo '<a href="?mode=2">Private</a><br>';
echo '</div>';

// Aktív
active_teams($f1db);

// Betűk
$query_letters = mysqli_query($f1db,
	"SELECT DISTINCT UPPER(LEFT(fullname, 1)) AS letter
	FROM team
	WHERE entrant = 1
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
	
// Lista
if (!isset($_GET['mode'])) {
	$mode = 0;
}
else {
	$mode = $_GET['mode'];
}

switch ($mode) {
	case 1:
		$query = mysqli_query($f1db,
			"SELECT *
			FROM team
			WHERE isteam = 1
			ORDER BY fullname ASC");
		break;
		
	case 2:
		$query = mysqli_query($f1db,
			"SELECT *
			FROM team
			WHERE entrant = 1
			AND isteam = 0
			ORDER BY fullname ASC");
		break;
	
	default:
		$query = mysqli_query($f1db,
			"SELECT * 
			FROM team
			WHERE entrant = 1
			ORDER BY fullname ASC");
}
	
while ($row = mysqli_fetch_array($query)) {
	$letter = ucfirst(substr($row['fullname'], 0, 1));
	if (!isset($prev) || $prev != $letter) {
		echo '<h2 id="' . $letter . '">' . $letter . '</h2>';
	}
	$prev = $letter;
	
	$name = $row['fullname'];
	echo team_link($row['id'], $name).'<br>';
}

echo '<p><a href="#">Up</a> &middot; ' . implode(' &middot; ', $letters) . '</p>';

require_once('included/foot.php');
?>