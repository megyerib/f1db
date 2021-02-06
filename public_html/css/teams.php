<?php
header("Content-Type: text/css");
include('../resources/db.php');
$colors = mysqli_query($f1db,
	"SELECT id, font_color, bg_color, border_color
	FROM f1_active_team AS act
	INNER JOIN team
	ON act.no = team.no"
);
while ($col = mysqli_fetch_array($colors)) {
	echo "div.".$col['id']." {\n";
	echo "\tcolor: #".$col['font_color'].";\n";
	echo "\tbackground-color: #".$col['bg_color'].";\n";
	echo "\tborder-color: #".$col['border_color'].";\n}\n\n";
	
	echo ".".$col['id']." h2, .".$col['id']." h3, .".$col['id']." a {\n";
	echo "\tcolor: #".$col['font_color'].";\n}\n\n";
}
mysqli_close($f1db);
?>
