<?php
function img_upload($folder, $id, $extension, $display_style, $opt_width, $opt_height, $opt_ratio, $title = '') {	
	$uploader_id = substr(sha1($folder),0,8); // Hash a mappanévből (második betöltésre is ugyanaz legyen)
	// Azért, hogy egy oldalra többet is lehessen tenni
	
	if (!empty($title)) {
		echo '<h2>'.$title.'</h1>';
	}	
	
	// Mappa címének átalakítása (csak abszolut hivatkozással működik)
	$folder = '/'.rtrim(ltrim($folder, '/'), '/').'/'; // Mindkét végén van /
	
	if ($extension != '') {
		$xt = explode(' ', $extension);
	}
	else {
		$xt = array('jpg', 'JPG', 'png', 'gif', 'jpeg', 'JPEG', 'PNG', 'GIF');
	}
	
	$exists = false;
	foreach ($xt as $ext) {
		$try = $_SERVER['DOCUMENT_ROOT'].$folder.$id.'.'.$ext;
		if (file_exists($try)) {
			$path     = $folder.$id.'.'.$ext;
			$php_path = $try;
			$exists   = true;
			break;
		}
	}
	
	// Törlés
	if (isset($_POST['deleteimg_'.$uploader_id])) {
		$new = $_SERVER['DOCUMENT_ROOT'].'/images/deleted/'.date("ymdHis").'_'.$id.'.'.$ext;
		rename($php_path, $new);
		$exists = false; // Ne kezdje el kiírni
	}
	
	if ($exists) {
		echo '<img src="'.$path.'?s='.rand().'" style="'.$display_style.'"></br>'; // Ha kicserélem, ne a gyorsítótárból töltse be
		// Info
		list($width, $height) = getimagesize($php_path);
		echo '<div style="width:194px; background:#DDDDDD; border:1px solid grey; padding:2px;">';
		echo 'Size: '.$width.'*'.$height.'px, '.number_format((filesize($php_path)/1024), 2).' kB</br>';
		
		// Méret ellenőrzése
		if ($opt_width > 0) {
			if ($width < $opt_width) {
				echo '<b>Too thin</b></br>';
			}
			if ($width > ($opt_width * 1.67)) { // 300 -> 500
				echo '<b>Too big</b></br>';
			}
		}
		if ($opt_height > 0) {
			if ($height < $opt_height) {
				echo '<b>Too small</b></br>';
			}
			if ($height > ($opt_height * 1.67)) { // 300 -> 500
				echo '<b>Too tall</b></br>';
			}
		}
		if ($opt_ratio > 0 && ($width * $opt_ratio) < $height) {
			echo '<b>Disadvantageous ration</b></br>';
		}
		// Törlés form
		echo '<form method="post">
			<input type="submit" name="deleteimg_'.$uploader_id.'" value="Delete">
			</form>';
		echo '</div>';		
	}
	else {
		echo '<span style="color:grey;">No picture uploaded yet.</span><br>';
	}
	
	// Feltöltés
	if (isset($_POST['upl_submit_'.$uploader_id])) {
		if (!empty($_POST['upl_url'])) {
			$tmp_path = $_POST['upl_url'];
			$upl_ext  = explode('.', $_POST['upl_url']);
			$offset   = count($upl_ext)-1;
			$upl_ext  = $upl_ext[$offset];
		}
		else if (!empty($_FILES)) {
			$tmp_path = $_FILES['upl_img']['tmp_name'];
			$upl_ext  = explode('.', $_FILES['upl_img']['name']);
			$offset   = count($upl_ext)-1;
			$upl_ext  = $upl_ext[$offset];
		}
		$noxt = true;
		foreach ($xt as $uxt) {
			if ($uxt == $upl_ext) {
				$noxt = false;
				break;
			}
		}
		if (!$noxt) {
			// Régi áthelyezése a lomtárba
			$delete = $_SERVER['DOCUMENT_ROOT'].'/images/deleted/'.date("ymdHis").'_'.$id.'.'.$ext;
			rename($php_path, $delete);
			
			$new_path = $_SERVER['DOCUMENT_ROOT'].$folder.$id.'.'.$upl_ext;
			if (!empty($_POST['upl_url'])) { // URL-ből
				$success = copy($_POST['upl_url'], $new_path);
			}
			else if (!empty($_FILES)) { // Fájlból
				$success = move_uploaded_file($tmp_path, $new_path);
			}
			if ($success) { // Régi áthelyezése a lomtárba
				$delete = $_SERVER['DOCUMENT_ROOT'].'/images/deleted/'.date("ymdHis").'_'.$id.'.'.$ext;
				rename($php_path, $new);
			}
			if (!$success) { // Ha nem sikerült feltölteni, visszarakja
				rename($delete, $php_path);
			}
			header('Location: '.$_SERVER['REQUEST_URI']); // Muszáj frissíteni
		}
		else {
			echo '<p style="color:red;">'.$upl_ext.' is not supported</p>';
		}
	}
	
	// Upload form
	echo '<p><b>Upload</b><br>
	<form enctype="multipart/form-data" method="post">
	<label class="input">From URL</label>
	<input type="text" name="upl_url"><br>
	<label class="input">From file</label>
	<input type="file" name="upl_img"><br>
	<input type="submit" name="upl_submit_'.$uploader_id.'" value="Upload">
	</form></p>';
}
//img_upload('/images/driver/', 'f_alonso', 'jpg JPG', 'width:200px;', 300, 0, 1.67);
?>