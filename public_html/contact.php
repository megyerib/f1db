<?php
$pagetitle = 'Contact';
$maintitle = 'Contact with me';
$cke = true;
require_once('included/head.php');
?>
<?php
// Küldés
if (isset($_POST['submit'])) {
	$mail = $_POST['mail'];
	$name = $_POST['name'];
	$subj = $_POST['subject'];
	$mssg = $_POST['ckeditor'];
	
	// Rakj be e-mail ellenőrzőt
	
	require_once('included/recaptchalib.php');
	  $privatekey = '6LdffOsSAAAAAJo8Y2vbt5BpWYzx0NKWvtWea2Vr';
	  $resp = recaptcha_check_answer ($privatekey,
	                                $_SERVER["REMOTE_ADDR"],
	                                $_POST["recaptcha_challenge_field"],
	                                $_POST["recaptcha_response_field"]);
	
	// Rendezd el logikus sorrendben
	if (empty($mail) ||
		empty($name) ||
		empty($subj) ||
		empty($mssg)) {
		echo '<p class="alert" style="margin-top:0px;">Please fill all fields!</p>';
	}
	else if ($resp->is_valid) {
		echo '<p class="info" style="margin-top:0px;">Message sent!</p>';
		
		$ip = '';
		$ip = $_SERVER['REMOTE_ADDR'];
		
		mysqli_query($f1db,
			"INSERT INTO message(mail, name, subject, msg, sent, ip)
			VALUES('$mail', '$name', '$subj', '$mssg', NOW(), '$ip')");
		$mail = '';
		$name = '';
		$subj = '';
		$mssg = '';
		$ip = '';
	}
	else {
		echo '<p class="alert" style="margin-top:0px;">CAPTCHA was incorrect</p>';
	}
}
else {
	$mail = '';
	$name = '';
	$subj = '';
	$mssg = '';
}
?>
<form class="contact" method="post" target="_self">
<table style="margin-bottom:25px;">
	<tr><td width="100"><label>E-mail</label></td>
	<td><input type="text" name="mail" value="<?php echo $mail; ?>"></td></tr>
	
	<tr><td><label>Name</label></td>
	<td><input type="text" name="name" value="<?php echo $name; ?>"></td></tr>
	
	<tr><td><label>Subject</label></td>
	<td><input type="text" name="subject" value="<?php echo $subj; ?>"></td></tr>
</table>
<textarea name="ckeditor"><?php echo $mssg; ?></textarea></br>
<script>
	CKEDITOR.replace('ckeditor');
</script>
<?php
	require_once('included/recaptchalib.php');
	$publickey = '6LdffOsSAAAAAPh82Ci9gP0QNeom4DpsiocgNcSW'; // you got this from the signup page
	echo recaptcha_get_html($publickey);
?>
<input type="submit" value="Send" name="submit">
</form>
<?php
require_once('included/foot.php');
?>