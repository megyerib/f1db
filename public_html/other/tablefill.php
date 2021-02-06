<!DOCTYPE html>
<html>
<head>
<style>
	.percentbar {
		background:#CCCCCC;
		border:1px solid #666666;
		height:10px; width:100px;
		display:inline-block;
	}
	.percentbar div {
		background: #28B8C0;
		height: 10px;
	}
	table#dbTables td {
		padding-right:10px;
	}
</style>
</head>
<body>
<?php

function percentbar($percent, $color = 0) {
	$scale = 1.0;

	if ( $percent > 100 ) { $percent = 100; }
	
	return "<div class='percentbar' style='width:".round(100 * $scale)."px;'>".
		"<div style='width:".round($percent * $scale)."px;".($color?" background:$color;":'')."'></div>".
	"</div>";
}


$db = new mysqli('localhost', 'root', '', 'f1_new');
if ($db->connect_error) {
	die("Error<br>Database connection failed<br>".$db->connect_error);
}

$typeRegEx = '/(\w+)\((\d+)\)[ ]?(.*)/';

$intTypes = array(
	'tinyint' => 8,
	'smallint' => 16,
	'mediumint' => 24,
	'int' => 32,
	'bigint' => 63
);

$tables = array(); // Eredménytömb

$tbls = $db->query("SHOW TABLES");

while ($row = $tbls->fetch_array()) {
	$tblName = $row[0];
	
	// 1. Max méret kiszámítása
	$table = $db->query("DESCRIBE $tblName");
	while ($row = $table->fetch_assoc())
		if ($row['Key'] == 'PRI') {
			$pkType = $row['Type'];
			break;
		}
	
	preg_match($typeRegEx, $pkType, $pkAttr);

	$type = $pkAttr[1];
	$len  = $pkAttr[2];
	$uns  = $pkAttr[3];
	
	$bits = $intTypes[$type] - ($uns == 'unsigned' ? 0 : 1); // Nem hülyebiztos
	if (pow(2, $bits) > pow(10, $len))
		$max = pow(10, $len) - 1;
	else
		$max = pow(2, $bits) - 1;
	
	// 2. Legnagyobb érték lekérdezése
	$maxval = $db->query("SELECT MAX(no) FROM $tblName");
	$row = $maxval->fetch_array();
	$maxInserted = $row[0];
	
	$full = sprintf("%.1f" ,$maxInserted/$max*100);
	
	//echo "$tblName ($full%)<br>";
	array_push($tables, array($tblName, $full, $pkType, $maxInserted));
}

// Táblázat kirjzolása
echo "<h1>Adatbázistáblák kihasználtsága</h1>";
echo "<table id='dbTables'>\n";
foreach ($tables as $table) {
	$full = $table[1] > 75;
	$percentbar = percentbar($table[1], $full?'red':0);
	
	echo "<tr>\n";
	echo "\t<td>$table[0]</td>\n";
	echo "\t<td>$percentbar $table[1]%</td>\n";
	echo "\t<td>$table[2]</td>\n";
	echo "\t<td style='color:grey;'>$table[3]</td>\n";
	echo "</tr>\n";
}
echo "</table>\n";

$db->close();
?>
</body>
</html>