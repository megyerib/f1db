<?php
include 'resources/head.php';
include 'resources/lib/series.php';

$s = new series;
if (!$s->fillFromID($_GET['id']))
    header("Location: ".url('series'));

echo "<h1>$s->name seasons</h1>";

// Szezonok kiírása
$results = $rdb->query(
    "SELECT DISTINCT eventYear
     FROM event
     WHERE eventSeries = $s->no
     ORDER BY eventYear DESC"
);

if ($results)
while ($row = $results->fetch_array()) {
    $year = $row[0];
    echo "<p>".a('season', $year, $s->id, $year)."</p>";
}

include 'resources/foot.php';
?>
