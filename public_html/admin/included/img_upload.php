<?php
// PHP openssl-t engedélyezni kell

/*
További fícsörz:
- fájltípusok OK
- előnyös méret, 
- átméretező
*/
// URL-ből

// Formátum
$path_0 = $_SERVER['DOCUMENT_ROOT'].'/images/'.$maintitle.'/'.$subj_id;
$img_format = isset($img['format']) ? $img['format'] : array('jpg', 'png');

foreach ($img_format as $form) {
	$format = $img_format[0]; // elég gány, de működik
	if(file_exists($path_0.'.'.$form)) {
		$format = $form;
		break;
	}
}

$path = $path_0.'.'.$format;
$html_path = '/images/'.$maintitle.'/'.$subj_id.'.'.$format;
if (isset($_POST['submit_img'])) { // Feltöltő
	$url = $_POST['url'];
	$target = $path;
	copy($url, $target);
}

// Feltöltő
if (isset($_POST['upl_submit'])) {		
	move_uploaded_file($_FILES['upl_img']['tmp_name'], $path);
}

// Törlés
if (isset($_POST['deleteimg'])) {
	unlink($path);
}

// Kép
if (file_exists($path)) {
	echo '<img src="'.$html_path.'?s='.rand().'" width="200"></br>'; // Ha kicserélem, ne a gyorsítótárból töltse be
	// Info
	list($width, $height) = getimagesize($path);
		echo '<div style="width:198px; background:#DDDDDD; border:1px solid grey;">';
		echo 'Size: '.$width.'*'.$height.'px, '.number_format((filesize($path)/1024), 2).' kB</br>';
		if ($width < 300) {
			echo '<b>Too thin</b></br>';
		}
		if ($width > 500) {
			echo '<b>Too big</b></br>';
		}
		if ($width > $height || ($width * 1.8) < $height) {
			echo '<b>Disadvantageous ration</b></br>';
		}
		// Törlés form
		echo '<form method="post">
			<input type="submit" name="deleteimg" value="Delete">
			</form>';
		echo '</div>';		
}
else {
	echo '<span style="color:grey;">No picture uploaded yet.</span><br>';
}

echo '</br><b>Upload</b>
<table><tr><td>From URL</td><td>
<form method="post">
	<input type="text" name="url">
	<input type="submit" name="submit_img" value="Upload">
</form>
</tr><tr><td>From file</td><td>
<form enctype="multipart/form-data" method="post">
	<input type="file" name="upl_img">
	<input type="submit" name="upl_submit" value="Upload">
</form>
</td></tr></table>';
?>