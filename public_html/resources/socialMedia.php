<?php
function mediaList($type, $name) {
	switch ($type) {
		case 1:
			$tp = 'website'; $tag = 'Website';
			$url = 'http://www.'.$name;
			$name = explode('/', $name);
			$name = $name[0];
			break;
		case 2:
			$tp = 'facebook'; $tag = 'Facebook';
			$url = 'https://www.facebook.com/'.$name;
			break;
		case 3:
			$tp = 'twitter'; $tag = 'Twitter';
			$url = 'https://twitter.com/'.$name;
			$name = '@'.$name;
			break;
		case 4:
			$tp = 'instagram'; $tag = 'Instagram';
			$url = 'http://instagram.com/'.$name;
			break;
		case 5:
			$tp = 'youtube'; $tag = 'YouTube';
			$url = 'http://www.youtube.com/user/'.$name;
			break;
		case 6:
			$tp = 'googleplus'; $tag = 'Google+';
			$url = 'https://plus.google.com/'.$name;
			break;
		case 7:
			$tp = 'flickr'; $tag = 'Flickr';
			$url = 'http://www.flickr.com/photos/'.$name;
			break;
		case 8:
			$tp = 'linkedin'; $tag = 'LinkedIn';
			$url = 'http://www.linkedin.com/'.$name;
			$name = explode('/', $name);
			$name = $name[0].'/'.$name[1];
			break;
		case 9:
			$tp = 'pinterest'; $tag = 'Pinterest';
			$url = 'http://pinterest.com/'.$name;
			break;
		case 10:
			$tp = 'weibo'; $tag = 'Weibo';
			$url = 'http://www.weibo.com/'.$name;
			break;
		case 11:
			$tp = 'rss'; $tag = 'RSS';
			$url = $name;
			$name = 'RSS Feed';
			break;
		default: $noshow = true;
	}
	if (!isset($noshow)) {
		echo '<a href="'.$url.'" target="_blank" title="'.$tag.'">';
		echo '<img src="/img/social/'.$tp.'.png" height="24" width="24"
			style="margin-right:8px;">';
		echo '<span style="position:relative; bottom:7px; ">'.$name.'</span>';
		echo '</a><br>';
	}
}

function socialMedia($no, $type) {
	global $f1db;
	$medias = mysqli_query($f1db,
		"SELECT *
		FROM social_media
		WHERE subj_type = '$type'
		AND subj = $no
		ORDER BY type ASC"
	);
	
	if (mysqli_num_rows($medias) > 0) {
		while ($row = mysqli_fetch_array($medias)) {
			mediaList($row['type'], $name = $row['name']);
		}
	}
}
?>