<?php
include 'resources/head.php';
include 'resources/lib/series.php';

echo "<h1>Series</h1>";

$series = $rdb->query(
    "SELECT *
     FROM series
     ORDER BY seriesName ASC"
);

$s = new series;

while ($row = $series->fetch_assoc()) {
    $s->fillFromRow($row);
    echo "<p>".$s->a()."</p>";
}

include 'resources/foot.php';
?>
