<?php
function social_media($social_media_type, $subj_no) {
	global $f1db;
	echo '<h2 style="margin-bottom:0px;">Social media</h2>';
	
	// Edit / Delete
	if (isset($_POST['edit_media'])) {		
		// Delete
		if (isset($_POST['delete'])) {
			foreach ($_POST['delete'] as $media_no) {			
				mysqli_query($f1db,
				"DELETE FROM social_media
				WHERE no = $media_no");
			}
		}
		
		// Edit
		foreach ($_POST['media_no'] as $no => $m_no) {
			$media_no   = $m_no;
			$media_type = $_POST['media_type'][$no];
			$media_name = $_POST['media_name'][$no];
			
			mysqli_query($f1db,
			"UPDATE social_media
			SET name = '$media_name',
			type = $media_type
			WHERE no = $media_no");
		}
	}
	// Add
	if (isset($_POST['add_media'])) {
		$type = $_POST['media_type'][0];
		$media_name = $_POST['media_name'];
		
		mysqli_query($f1db,
			"INSERT INTO social_media(subj_type, subj, type, name)
			VALUES('$social_media_type', $subj_no, $type, '$media_name')");
	}
	
	// Edit forms
		$medias = mysqli_query($f1db,
			"SELECT *
			FROM social_media
			WHERE subj_type='$social_media_type'
			AND subj = $subj_no
			ORDER BY type ASC");
		
		if (mysqli_num_rows($medias) > 0) {
			echo '<form method="post">';
			while ($md = mysqli_fetch_array($medias)) {
					echo '<br><input type="hidden" name="media_no[]" value="'.$md['no'].'">';
					media_dropdown('media_type[]', $md['type']);
					echo '<input type="text" name="media_name[]" value="'.$md['name'].'">';
					echo ' <input type="checkbox" name="delete[]" value="'.$md['no'].'">';
			}
			echo ' <input type="submit" name="edit_media" value="Save">';
			echo '</form>';
		}
	
	// Add form
	echo '<hr width="200" align="left">';
	echo '<form method="post">';
		media_dropdown('media_type', 0);
		echo '<input type="text" name="media_name"> ';
		echo '<input type="submit" name="add_media" value="Add">';
	echo '</form>';
}
?>