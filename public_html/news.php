<?php
if (isset($_GET['entry'])) {
	require_once('included/database.php');
	$entry_id = $_GET['entry'];
	
	$entry = mysqli_query($f1db,
		"SELECT *
		FROM blog
		WHERE id = '$entry_id'");
	$entry = mysqli_fetch_array($entry);
	$pagetitle = $entry['title'];
}
else {
	$pagetitle = 'News';
}
require_once('included/head.php');
if (isset($_GET['entry'])) {
	echo '
	<div id="fb-root"></div>
	<script>(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/hu_HU/all.js#xfbml=1";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, "script", "facebook-jssdk"));</script>'; // Facebook comment
	
	echo '<span style="color:grey;">'.$entry['time'].'</span><br><br>';
	echo '<b>'.$entry['head'].'</b><br>';
	echo $entry['text'];
	
	echo '<hr>';
	echo
	'<div class="fb-comments" data-href="http://race-data.net/news/'.$entry_id.'" data-width="600" data-numposts="5" data-colorscheme="light"></div>';
}
else {
	$all = mysqli_query($f1db,
		"SELECT *
		FROM blog
		ORDER BY time DESC");
}
require_once('included/foot.php');
?>