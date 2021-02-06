<?php
include 'resources/head.php';
include 'resources/lib/driver.php';

$driver = new driver;
if (!$driver->fillFromID($_GET['id'])) {
    header("Location: ".url('driver'));
    die;
}

echo "<h1>$driver->name</h1>\n";

include 'resources/foot.php';
?>
