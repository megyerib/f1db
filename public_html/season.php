<?php
include 'resources/head.php';
include 'resources/lib/series.php';
include 'resources/lib/event.php';

$s = new series;
$s->fillFromID($_GET['series']);

$seasonYear   = $_GET['year'];

// Van ilyen szezon?
$results = $rdb->query(
    "SELECT eventNo, eventYear, countryID, countryGP
     FROM event
     JOIN country
     ON eventGP = countryNo
     WHERE eventYear = $seasonYear
     AND eventSeries = $s->no" // Azért csináltam így, hogy ne kelljen minden sorhoz joinolni
);
if ($results->num_rows == 0) {
    header("Location: ".url('seasons', $s->id));
    die();
}

echo "<h1>$seasonYear $s->name season</h1>";

$e = new event;

while ($row = $results->fetch_assoc()) {
    $e->fillFromRow($row);
    $e->seriesID = $s->id;
    echo "<p>".$e->a()."</p>";
}

include 'resources/foot.php';
?>
