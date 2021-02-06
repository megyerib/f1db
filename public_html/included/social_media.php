<?php
if (isset($social_media_type)) {
	$medias = mysqli_query($f1db,
		"SELECT *
		FROM social_media
		WHERE subj_type = '$social_media_type'
		AND subj = $tyre_no
		ORDER BY type ASC");
	if (mysqli_num_rows($medias) > 0) {
		echo '<div class="right">';
		echo '<div class="thead">Social media</div>';
		while ($row = mysqli_fetch_array($medias)) {
			media_list($row['type'], $name = $row['name']);
		}
		echo '</div>';
	}
}
?>