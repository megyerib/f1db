<?php
include 'resources/url.php';
include 'resources/config.php';
include 'resources/lib/driver.php';

$d = new driver;
$d->fillFromID('m_schuma');
echo $d->url();
?>
