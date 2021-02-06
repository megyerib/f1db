<?php
$num = mysqli_query($f1db,
	"SELECT MAX(no) AS max
	FROM f1_test_entrants"
);
$num = mysqli_fetch_array($num);
$num = $num['max']+1;

mysqli_query($f1db,
	"INSERT INTO f1_test_entrants(no, test)
	VALUES($num, $test_no)"
);

header('Location: /admin/test/entrants/'.$num);
?>