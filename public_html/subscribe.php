<?php
$pagetitle = 'Subscribe';
$maintitle = 'Stay up to date!';
require_once('included/head.php');

/*if (isset($_POST['subscribe'])) {
	$mail = $_POST['mail'];
	$ip = $_SERVER['REMOTE_ADDR'];
	mysqli_query($f1db,
		"INSERT INTO subscriber(mail, ip)
		VALUES('$mail', '$ip')");
	
	echo '<p style="color:red;">You have succesfully subscribed! :)</p>';
}
?>
			<form method="post" action="">
				<input name="mail"><br />
				<input type="submit" name="subscribe" value="Subscribe!">
			</form>*/


?>
<p>My site is in beta phase, so I'm constantly adding many-many features to it. If you've found some bugs, mistakes in the datas os just have a suggestion, please let me know of it.<br>
Subscribe for updates on Facebook, Twitter or Rss. Thanks!</p>
<a href="https://www.facebook.com/racedata"><img src="/images/social/facebook_1.png" style="border-radius:2px; margin-right:4px;"></a>
<a href="https://twitter.com/racedatanet"><img src="/images/social/twitter_1.png" style="border-radius:2px; margin-right:4px;"></a>
<a href="/rss"><img src="/images/social/rss.png" style="border-radius:2px; margin-right:4px;"></a>

<?php /*		
<div class="fb-like-box" data-href="https://www.facebook.com/racedata" data-colorscheme="light" data-show-faces="false" data-header="false" data-stream="false" data-show-border="true"></div>
*/
require_once('included/foot.php');
?>