<?php
$pagetitle = 'engines';
require_once('included/head.php');

/*/ Jobb oldali táblázat
echo '<div class="right">';
echo '</div>';
// Motortípusok
?>
<div class="right">
<center><b style="font-size:20px;">Engine concepts</b>
	<p>
	<img src="<?php echo root; ?>images/engine/v.png" width="80%">
	</br>V
	</p>

	<p>
	<img src="<?php echo root; ?>images/engine/l.png" width="80%">
	</br>Straight
	</p>

	<p>
	<img src="<?php echo root; ?>images/engine/b.png" width="80%">
	</br>Flat/Boxer
	</p>

	<p>
	<img src="<?php echo root; ?>images/engine/h.png" width="80%">
	</br>H
	</p>

	<p>
	<img src="<?php echo root; ?>images/engine/w.png" width="80%">
	</br>W
	</p>
</center>
</div>
<?php*/
// Betűk
$query_letters = mysqli_query($f1db,
	"SELECT DISTINCT UPPER(LEFT(fullname, 1)) AS letter
	FROM team
	WHERE engine = 1
	ORDER BY fullname ASC");

$letters = array();
$prev = 0;

while ($row = mysqli_fetch_array($query_letters)) {
	$letter = $row['letter'];
	$link = '<a href="#' . $letter . '">' . $letter . '</a>';
	array_push($letters, $link);

	$prev = $letter;
}
echo implode(' &middot; ', $letters);

// Aktív
echo '<h2>Current engine constructors</h2>';
	
// Lista
$query = mysqli_query($f1db,
	"SELECT * 
	FROM team
	WHERE engine = 1
	ORDER BY fullname ASC");
	
while ($row = mysqli_fetch_array($query)) {
	$letter = ucfirst(substr($row['fullname'], 0, 1));
	if (!isset($prev) || $prev != $letter) {
		echo '<h2 id="' . $letter . '">' . $letter . '</h2>';
	}
	$prev = $letter;

	echo engine_cons_link($row['id'], $row['fullname']).'</br>';
}

echo '<br><a href="#">Up</a> &middot; ' . implode(' &middot; ', $letters);

require_once('included/foot.php');
?>