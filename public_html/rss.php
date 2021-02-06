<?php
header('Content-Type: text/xml');
echo '<?xml version="1.0" encoding="utf-8"?>';
echo '<?xml-stylesheet type="text/css" href="/css/rss.css"?>';
?>
<rss version="2.0">
<channel>
<title>Race-data.net - News</title>
<link>http://race-data.net/</link>
<description>F1 Database</description>
<language>en-us</language>
<?php
require_once('included/database.php');
$news = mysqli_query($sdb,
	"SELECT *
	FROM blog
	WHERE public = 1
	ORDER BY time DESC
	LIMIT 25");

while ($row = mysqli_fetch_array($news)) {
	echo '<item>';
	
	echo '<title>'.$row['title'].'</title>';
	echo '<link>http://race-data.net/news/'.$row['id'].'/</link>';
	echo '<pubDate>'.date('r', strtotime($row['time'])).'</pubDate>';
	echo '<description>'.$row['head'].'</description>';
	
	echo '</item>';
}
?>
</channel>
</rss>