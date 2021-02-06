<?php
$num = mysqli_query($f1db,
	"SELECT MAX(no) AS max
	FROM f1_race"
);
$num = mysqli_fetch_array($num);
$num = $num['max']+1;

mysqli_query($f1db,
	"INSERT INTO f1_race(no, rnd, yr, gp)
	VALUES($num, $race_no, $race_yr, '$race_gp')"
);

header('Location: /admin/race/entrants/'.$num);
?>