<?php
include('resources/head.php');
$all = mysqli_query($f1db,
	"SELECT *
	FROM driver
	ORDER BY last ASC"
);

// Betűk
$query_letters = mysqli_query($f1db,
	"SELECT DISTINCT LEFT(last, 1) AS letter
	FROM driver
	ORDER BY last ASC"
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
	FROM driver
	ORDER BY last ASC"
);

$prev = '';
while ($row = mysqli_fetch_array($mainq)) {
	$letter = substr($row['last'], 0, 1);
	if ($prev != $letter) {
		echo '<h2 id="'.$letter.'" class="list">'.$letter.'</h2>';
	}
	$prev = $letter;
	
	$name = nameReverse($row['first'], $row['de'], $row['last'], $row['sr']);
	
	echo flag($row['country']).linkDriver($row['id'], $name).'<br>';
}
$Wdrivers = ob_get_contents();
ob_clean();

// Akt. csapatok
ob_start();
	include('resources/activeTeams.php');
$current = ob_get_contents();
ob_clean();

?>
<h1>Drivers</h1>
<div class="triple">
	<?php echo $letters; ?>
</div>
<div style="float:right;">
	<h2 style="margin-top:0;">Current drivers</h2>
	<?php echo $current; ?>
</div>
<div class="double">
	<?php echo $Wdrivers; ?>
</div>
<div class="triple">
	<?php echo $letters; ?>
</div>
<?php
include('resources/foot.php');
?>