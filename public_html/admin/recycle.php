<?php
$recycleBinDir = "/img/deleted/";

require_once('included/head_admin.php');

echo '<h1 class="title">Recycle bin (images)</h1>';

if (isset($_POST['delete']) && !empty($_POST['to_del'])) {
	foreach ($_POST['to_del'] as $filename) {
		@unlink($_SERVER['DOCUMENT_ROOT'].$recycleBinDir.$filename);
	}
}

$files = scandir($_SERVER['DOCUMENT_ROOT'].$recycleBinDir);

if (count($files) > 2) {
	echo '<form method="post">';
	echo '<input type="submit" name="delete" value="Delete">';
	echo '<table>';
	echo '<tr><td><input type="checkbox" onchange="checkAll(this)"></td><td>All</td></tr>';
	for ($i = 2; $i < count($files); $i++) { // az első 2 elem . és ..
		echo '<tr>';
		echo '<td><input type="checkbox" name="to_del[]" value="'.$files[$i].'"></td>';
		echo '<td><img src="'.$recycleBinDir.$files[$i].'" style="max-width:200px; max-height:200px;"></td>';
		echo '<td>';
		echo '<b>'.$files[$i].'</b><br>';
		list($width, $height, $type, $attr) = getimagesize($_SERVER['DOCUMENT_ROOT'].$recycleBinDir.$files[$i]);
		echo htmlspecialchars($attr).'<br>';
		echo number_format(filesize($_SERVER['DOCUMENT_ROOT'].$recycleBinDir.$files[$i])/1024, 2, '.', "").' kB<br>';
		echo '</td>';
		echo '</tr>';
	}
	echo '<tr><td><input type="checkbox" onchange="checkAll(this)"></td><td>All</td></tr>';
	echo '</table>';
	echo '<input type="submit" name="delete" value="Delete">';
	// Mindent bejelölő script (király, de mindenütt működik)
	echo '<script type="text/javascript" language="javascript">';
	echo "function checkAll(ele) {
		 var checkboxes = document.getElementsByTagName('input');
		 if (ele.checked) {
			 for (var i = 0; i < checkboxes.length; i++) {
				 if (checkboxes[i].type == 'checkbox') {
					 checkboxes[i].checked = true;
				 }
			 }
		 } else {
			 for (var i = 0; i < checkboxes.length; i++) {
				 console.log(i)
				 if (checkboxes[i].type == 'checkbox') {
					 checkboxes[i].checked = false;
				 }
			 }
		 }
	 }";
	echo '</script>';
	echo '</form>';
}
else {
	echo 'The recycle bin is empty';
}

require_once('included/foot_admin.php');
?>
