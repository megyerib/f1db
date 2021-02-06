<?php
require_once('included/head_admin.php');

$mostcommon_404 = mysqli_query($sdb,
	"SELECT url, COUNT(*) as times
	FROM error
	WHERE type = 404
	GROUP BY url
	ORDER BY times DESC
	LIMIT 10");

echo '<h2>Most common <i>404</i> errors</h2>';
echo '<table class="data">';
echo '<th>URL</th><th>Times</th>';
while ($row = mysqli_fetch_array($mostcommon_404)) {
	echo '<tr>';
	echo '<td>'.$row['url'].'</td>';
	echo '<td>'.$row['times'].'</td>';
	echo '</tr>';
}
echo '</table>';

$last20_404 = mysqli_query($sdb,
	"SELECT *
	FROM error
	WHERE type = 404
	ORDER BY time DESC
	LIMIT 20");

echo '<h2>Latest <i>404</i> errors</h2>';
echo '<table class="data">';
echo '<th>Date</th><th>URL</th><th>IP</th>';
while ($row = mysqli_fetch_array($last20_404)) {
	echo '<tr>';
	echo '<td>'.$row['time'].'</td>';
	echo '<td>'.$row['url'].'</td>';
	echo '<td>'.$row['ip'].'</td>';
	echo '</tr>';
}
echo '</table>';

$last20_403 = mysqli_query($sdb,
	"SELECT *
	FROM error
	WHERE type = 403
	ORDER BY time DESC
	LIMIT 20");

echo '<h2>Latest <i>403</i> errors</h2>';
echo '<table class="data">';
echo '<th>Date</th><th>URL</th><th>IP</th>';
while ($row = mysqli_fetch_array($last20_403)) {
	echo '<tr>';
	echo '<td>'.$row['time'].'</td>';
	echo '<td>'.$row['url'].'</td>';
	echo '<td>'.$row['ip'].'</td>';
	echo '</tr>';
}
echo '</table>';

require_once('included/foot_admin.php');
?>