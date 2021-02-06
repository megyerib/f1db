<?php
include('resources/head.php');
$all = mysqli_query($f1db,
	"SELECT *
	FROM team
	ORDER BY fullname ASC"
);

// Betűk
$query_letters = mysqli_query($f1db,
	"SELECT DISTINCT UPPER(LEFT(fullname, 1)) letter
	FROM team
	ORDER BY fullname ASC"
);

$letters = array();

while ($row = mysqli_fetch_array($query_letters)) {
	$link = '<a href="#'.$row['letter'].'">'.$row['letter'].'</a>';
	array_push($letters, $link);
}

ob_start();

echo '<h3 style="margin:0">'.implode(' &middot ', $letters).'</h3>';

$letters = ob_get_contents();
ob_clean();

// Mindenki
ob_start();

$mainq = mysqli_query($f1db,
	"SELECT *
	FROM team
	ORDER BY fullname ASC"
);
//echo '<h2 style="margin-top:0">3</h2>'; // Bugot használok ki, nem rak A betűt
$prev = '';
while ($row = mysqli_fetch_array($mainq)) {
	$letter = strtoupper(substr($row['fullname'], 0, 1)); // Mert van kisbetűs
	if ($prev != $letter) {
		echo '<h2 id="'.$letter.'" class="list">'.$letter.'</h2>';
	}
	$prev = $letter;
	
	$name = $row['fullname'];
	
	echo flag($row['country']).linkTeam($row['id'], $name).'<br>';
}
$Wteams = ob_get_contents();
ob_clean();

// Akt. csapatok
ob_start();
	include('resources/activeTeams.php');
$current = ob_get_contents();
ob_clean();

?>
<h1>Teams</h1>
<div class="triple">
	<?php echo $letters; ?>
</div>
<div style="float:right;">
	<h2 style="margin-top:0;">Current teams</h2>
	<?php echo $current; ?>
</div>
<div class="double">
	<?php echo $Wteams; ?>
</div>
<div class="triple">
	<?php echo $letters; ?>
</div>
<?php
include('resources/foot.php');
?>
