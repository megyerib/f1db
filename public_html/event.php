<?php
include 'resources/head.php';
include 'resources/lib/event.php';
include 'resources/lib/driver.php';
include 'resources/lib/cons.php';
include 'resources/lib/tyre.php';
include 'resources/lib/entry.php';
include 'resources/lib/session.php';

$e = new event;
if (!$e->fillFromData($_GET['series'], $_GET['year'], $_GET['gp'])) {
    header("Location: ".url('season', $_GET['series'], $_GET['year']));
    die;
}

echo "<h1>$e->year $e->gp $e->seriesName Grand Prix</h1>";

$results = $rdb->query(
    "SELECT *
     FROM session
     WHERE sessionEvent = $e->no
     ORDER BY sessionType ASC" // Csak ideiglenes, amíg be nem rakom az időt
);

$sessions = array();

while ($row = $results->fetch_assoc()) {
    $s = new session;
    $s->fillFromRow($row);
    $sessions[$s->no] = $s;
}

// Minden eredmény lekérdezése

$results = $rdb->query(
   "SELECT resultStatus, resultStart, resultFinish, resultLaps,
           resultTime, resultNote, resultScore, resultSession,
        entryCarNo,
        driverName, driverID, countryID, countryName,
        c.consID AS chassisID, c.consName AS chassisName,
        e.consID AS engineID,  e.consName AS engineName,
        tyreID
    FROM result
    JOIN session   ON resultSession = sessionNo
    JOIN entry     ON resultEntry   = entryNo
    JOIN driver    ON entryDriver   = driverNo
    JOIN country   ON driverNation  = countryNo
    JOIN chassis   ON entryChassis  = chassisNo
    JOIN cons AS c ON chassisCons   = c.consNo
    JOIN engine    ON entryEngine   = engineNo
    JOIN cons AS e ON engineCons    = e.consNo
    JOIN tyre      ON entryTyre     = tyreNo
    WHERE sessionEvent = $e->no"
);

while ($row = $results->fetch_assoc()) {
    $sessions[$row['resultSession']]->addToResults($row);
}

varDump($sessions);

include 'resources/foot.php';
?>
