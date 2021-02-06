<?php
require_once('included/head_admin.php');

echo '<h1>Allowed IP adresses (closed beta)</h1>';

//Szerkesztő
if (isset($_POST['submit'])) {
	$name = $_POST['name'];
	$no = $_POST['no'];
	if ($_POST['submit'] == 'Allow') {
		$valid = 1;
	}
	else {
		$valid = 0;
	}
	
	mysqli_query($sdb,
		"UPDATE ip
		SET name = '$name',
		valid = $valid
		WHERE no = $no");
}

//Törlő
if (isset($_POST['delete'])) {
	$no = $_POST['no'];
	
	mysqli_query($sdb,
		"DELETE FROM ip
		WHERE no = $no");
}

$query = mysqli_query($sdb,
	"SELECT *
	FROM ip
	ORDER BY no DESC");

echo '<table>';
while ($row = mysqli_fetch_array($query)) {
	echo '<tr><form method="post" action="/admin/ips">';
	echo '<input type="hidden" name="no" value="'.$row['no'].'">';
	
	echo '<td><input type="text" name="name" value="'.$row['name'].'"></td>';
	echo '<td>'.$row['ip'].'</td>';
	if ($row['valid'] > 0) {
		echo '<td><input type="submit" name="submit" value="X"></td>';
	}
	else {
		echo '<td><input type="submit" name="submit" value="Allow"></td>';
	}
	echo '<td><input type="submit" name="delete" value="Delete"></td>';
	echo '</form></tr>';
}
echo '</table>';

require_once('included/foot_admin.php');
?>