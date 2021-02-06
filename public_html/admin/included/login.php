<?php
  //Doctype, adatbázis
  require_once('vars.php');
    
  $message = ""; // Üzenet törlése

    // If the user isn't logged in, try to log them in
    if (!isset($_SESSION['id'])) {
			
            if (isset($_POST['submit'])) {

            // Grab the user-entered log-in data
			$user     = mysqli_real_escape_string($sdb, trim($_POST['user']));
			$password = mysqli_real_escape_string($sdb, trim($_POST['password']));
			
            if (!empty($user) &&
			    !empty($password)) {
				
                // Adatok megvannak
				$query = "SELECT *
				          FROM users
						  WHERE user = '$user' AND password = SHA('$password')";
				$data   = mysqli_query($sdb, $query);

                if (mysqli_num_rows($data) == 1) { // user, jelszó ok
				
				    $queryvalid = "SELECT *
					               FROM users
								   WHERE user = '$user' AND password = SHA('$password') AND valid = '1'";
		            $valid      = mysqli_query($sdb, $queryvalid);
					
		            // Captcha
		            require_once('included/recaptchalib.php');
					$privatekey = '6LdffOsSAAAAAJo8Y2vbt5BpWYzx0NKWvtWea2Vr';
					$resp = recaptcha_check_answer ($privatekey,
				 	$_SERVER["REMOTE_ADDR"],
				 	$_POST["recaptcha_challenge_field"],
				 	$_POST["recaptcha_response_field"]);
		            
		            if (mysqli_num_rows($valid) == 1 && $resp->is_valid) { // Captcha
					// Login ok
		                        $row            = mysqli_fetch_array($data);
		                        $_SESSION['id'] = $row['no'];
								$_SESSION['user'] = $row['user'];
														
		                       header('Location: ' . $_SERVER['PHP_SELF']);
		               }
					else {
					$message = 'Invalid login!';
					}
					
                }
				else {
				// Helytelen felhasználónév/jelszó
				$message = 'Invalid login!';
				}
            }
			else {
			// Üres mező
			$message = 'You must enter your username and password!';
			}
            }
	}
		
  // Nincs session
        if (empty($_SESSION['id'])) {
?>
<head>
	<title>Race-data.net admin page</title>
</head>
<body>
	<div style="text-align:center;">
	  <form target="_self" method="post">
	  
	   <h1><img src="/images/admin/logo.png"><br />Log in</h1>
	   <?php if ($message != '') {echo '<font color="red">' . $message . '</font></br>';} ?>
	   
	   Username<br />
	   <input type="text" name="user"><br />
	   
	   Password<br />
	   <input type="password" name="password"><br />
	   
	   <br><center>
	   <?php
		require_once('included/recaptchalib.php');
		$publickey = '6LdffOsSAAAAAPh82Ci9gP0QNeom4DpsiocgNcSW'; // you got this from the signup page
		echo recaptcha_get_html($publickey);
	   ?>
	   </center>
	   	   
	   <p><input type="submit" name="submit" value="Log in"></p>
	
	  </form>
	<center><a href="http://race-data.net">Home</a></center>
	</div>
</body>
<?php
	die();
        }
?>