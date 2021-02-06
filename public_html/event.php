<?php
include 'resources/head.php';
include 'resources/lib/event.php';
include 'resources/lib/driver.php';
include 'resources/lib/cons.php';
include 'resources/lib/tyre.php';
include 'resources/lib/entry.php';
include 'resources/lib/session.php';

$event = new event;
if (!$event->fillFromData($_GET['series'], $_GET['year'], $_GET['gp'])) {
    header("Location: ".url('season', $_GET['series'], $_GET['year']));
    die;
}

echo "<h1>$event->year $event->gp $event->seriesName Grand Prix</h1>";

// Session-ök lekérdezése
$results = $rdb->query(
    "SELECT *
     FROM session
     WHERE sessionEvent = $event->no
     ORDER BY sessionType ASC" // Csak ideiglenes, amíg be nem rakom az időt
);

$sessions = array();

while ($row = $results->fetch_assoc()) {
    switch ($row['sessionType']) {
        case 'practice':   $s = new practice;   break;
        case 'qualifying': $s = new qualifying; break;
        case 'race':       $s = new race;       break;
    }

    $s->fillFromRow($row);
    $sessions[$s->no] = $s;
}

// Entry-k lekérdezése
$results = $rdb->query(
   "SELECT
        entryNo, entryCarNo,
        driverName, driverID, countryID, countryName,
        c.consID AS chassisID, c.consName AS chassisName,
        e.consID AS engineID,  e.consName AS engineName,
        tyreID
    FROM entry
    JOIN driver    ON entryDriver   = driverNo
    JOIN country   ON driverNation  = countryNo
    JOIN chassis   ON entryChassis  = chassisNo
    JOIN cons AS c ON chassisCons   = c.consNo
    JOIN engine    ON entryEngine   = engineNo
    JOIN cons AS e ON engineCons    = e.consNo
    JOIN tyre      ON entryTyre     = tyreNo
    WHERE entryEvent = $event->no"
);

while ($row = $results->fetch_assoc()) {
    $e = new entry;
    $e->fillFromRow($row);
    $event->entries[$row['entryNo']] = $e;
}
$results->close();
//varDump($e->entries);

// Eredmények lekérdezése
$results = $rdb->query(
    "SELECT resultSession, resultStatus, resultEntry, resultStart, resultFinish,
        resultStatus, resultLaps, resultTime, resultNote, resultScore
    FROM result
    JOIN entry ON resultEntry = entryNo
    WHERE entryEvent = $event->no
    ORDER BY resultFinish, resultNo ASC"
);
while ($row = $results->fetch_assoc()) {
    $sessions[$row['resultSession']]->addToResults($event->entries[$row['resultEntry']], $row);
}

// Kiírás
foreach ($sessions as $session) {
    echo "<h2>$session->displayName</h2>";
    echo $session->table();
}

include 'resources/foot.php';
?>
