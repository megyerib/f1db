<?php
require_once('included/head_admin.php');
$colors = mysqli_query($f1db,
	"SELECT active.font_color, active.bg_color, team.id
	FROM f1_active_team AS active
	INNER JOIN team
	ON active.no = team.no");
echo "\n";
while ($row = mysqli_fetch_array($colors)) {
	echo '.'.$row['id']." {\n";
	echo 'background-color:#'.$row['bg_color'].";\n";
	echo 'color:#'.$row['font_color'].";\n";
	echo "}\n";
}
require_once('included/foot_admin.php');
?>