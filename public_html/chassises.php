<?php
include('resources/head.php');
$all = mysqli_query($f1db,
	"SELECT *
	FROM team
	WHERE chassis = 1
	ORDER BY fullname ASC"
);

// Betűk
$query_letters = mysqli_query($f1db,
	"SELECT DISTINCT UPPER(LEFT(fullname, 1)) letter
	FROM team
	WHERE chassis = 1
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
	WHERE chassis = 1
	ORDER BY fullname ASC"
);

$prev = '';
while ($row = mysqli_fetch_array($mainq)) {
	$letter = strtoupper(substr($row['fullname'], 0, 1)); // Mert van kisbetűs
	if ($prev != $letter) {
		echo '<h2 id="'.$letter.'" class="list">'.$letter.'</h2>';
	}
	$prev = $letter;
	
	$name = $row['fullname'];
	
	echo flag($row['country']).linkChassisCons(!empty($row['id'])?$row['id']:'Unknown', $name).'<br>';
}
$Wteams = ob_get_contents();
ob_clean();

?>
<h1>Teams</h1>
<div class="triple">
	<?php echo $letters; ?>
</div>
<div class="triple">
	<?php echo $Wteams; ?>
</div>
<div class="triple">
	<?php echo $letters; ?>
</div>
<?php
include('resources/foot.php');
?>
