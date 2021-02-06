<?php
// Angol sorszámozó
function ordinal($num) {
	$add = 'th';
	
	$last = substr($num, -1);
		
	if (strlen($num > 1)) {
		$b_last = substr($num, -2, 1);
	}
	else {
		$b_last = 0;
	}
		
	if ($last <= 3 && $b_last != 1) {
		if ($last == 1) {$add = 'st';}
		if ($last == 2) {$add = 'nd';}
		if ($last == 3) {$add = 'rd';}
	}
	
	return $num . $add;
}
// Navigáció
function gp_nav($no, $gp, $gp_no, $yr, $dbc) {
	$prev = "no=" . ($no - 1);
	$next = "no=" . ($no + 1);
	
	$prev_gp = "no_gp=" . ($gp_no - 1) . ' AND gps.gp=\'' . $gp . '\'';
	$next_gp = "no_gp=" . ($gp_no + 1) . ' AND gps.gp=\'' . $gp . '\'';
	
	$array = array(
		$prev,
		$next,
		$prev_gp,
		$next_gp
	);
	$nav = array();
	
	foreach ($array as $element) {
		$query_nav = mysqli_query($dbc, 
				 "SELECT * FROM f1_gp AS gps
				 INNER JOIN country
				 ON (gps.gp = country.gp)
				 WHERE " . $element . " 
				 ORDER BY no ASC");
				 
		if (mysqli_num_rows($query_nav) == 1) {
			$row_nav = mysqli_fetch_array($query_nav);
			
			$array = array(
				'yr' => $row_nav['yr'],
				'gp' => $row_nav['gp'],
				'name' => $row_nav['name']
			);
			
			array_push($nav, $array);
		}
		else {
			array_push($nav, '');
		}
	}
	
	$gpname = mysqli_query($dbc,
		"SELECT name FROM country WHERE gp = '$gp'");
	$gpname = mysqli_fetch_array($gpname);
	$gpname = $gpname['name'];
	$gp_link = gp_link($gp, $gpname . ' GPs');
	
	echo '<div class="nav_big"><center><table style="text-align:center; padding:0px;">';
	$i = 0;
	$link = array();
	while ($i <= 3) {
		if (!empty($nav[$i])) {
			$link[$i] = race_link($nav[$i]['yr'], $nav[$i]['gp'], $nav[$i]['yr'].' '.$nav[$i]['name'].' GP');
		}
		else {$link[$i] = '';}
		$i++;
	}	
	echo '<tr><td width="25%" align="right">' . $link[0] . '</td>';
	echo '<td rowspan="2"><img src="/images/icon/left.png" style="width:16px; position:relative; top:2px;"></td>';
	echo '<td width="25%">'.season_link($yr).'</td>';
	echo '<td rowspan="2"><img src="/images/icon/right.png" style="width:16px; position:relative; top:2px;"></td>';
	echo '<td width="25%" align="left">' . $link[1] . '</td></tr>';
	echo '<tr><td align="right">' . $link[2] . '</td><td>' . $gp_link . '</td><td align="left">' . $link[3] . '</td></tr>';
	echo '</table></center></div>';
}
// Intervallumok tömbből
function intervals($array) {
		
		$result = array();
		array_push($array, ''); // Üres elemet adunk hozzá, hogy az utolsót is kiírja
	
		$prev = '';
		$year = '';
	
		foreach ($array as $next) {
	
			$i = 1;
		
			if ($prev == '' &&
				$year != '') { // Kiírja az első számot
			
				if ($year == actual) {
					array_push($result, $year . ' - ');
				}
				else {
					array_push($result, $year);
				}
			
			}
		
			if ($prev != '' &&
				$year != '' &&
				$year - 1 != $prev) { // Intervallum kezdő elemét kiírja
			
				if ($year == actual) {
					array_push($result, ', ' . $year . ' - ');
				}
				else {
					array_push($result, ', ' . $year);
				}
			
			}
		
			if ($prev == $year - 1 &&
				$year != $next - 1) { // Intervallum záró elemét kiírja (ha van)
		
				
				if ($year == actual) {
					array_push($result, ' - ');
				}
				else {
					array_push($result, ' - ' . $year);
				}
		
			}
			
			$prev = $year;
			$year = $next;
			$i++;
		}
		return implode($result, '');
	}
// Teljes név kiírása
function name($f, $la, $s, $sr) {
	if ($la != '') {
		$la = $la . ' ';
	}
	if ($sr != '') {
		$sr = ', ' . $sr . '.';
	}
	
	return $f . ' ' . $la . $s . $sr;
}
function name_2($f, $de, $l, $sr) {
	if ($de != '') {
		$de = $de . ' ';
	}
	if ($sr != '') {
		$sr = ' ' . $sr . '.';
	}
	
	return $de.$l.$sr.', '.$f;
}
function engine_name($cons, $type, $concept, $cylinders, $turbo) {
	if (empty($type)) {
		$unknown = 'Unknown ';
		$param = ' '.$concept.$cylinders.$turbo;
		$type = '';
	} else {
		$unknown = '';
		$param = '';
		$type = ' '.$type;
	}
	return $unknown.$cons.$type.$param;
}
function chassis_name($cons, $type) {
	if ($type != '') {
		$unknown = '';
		$type = ' '.$type;
	} else {
		$unknown = 'Unknown ';
		$type = '';
	}
	return $unknown.$cons.$type;
}
// Idő kiírása másodpercből
function racetime($secs) {
	if (is_numeric($secs)) {
	$hour = floor($secs / 3600);
	$min = floor(($secs - ($hour * 3600)) / 60);
	$sec = $secs - ($hour * 3600) - ($min * 60);
	
	if ($hour != 0) {
		$hour = $hour . ':';
	}
	else {
		$hour = '';
	}
	if ($min > 0) {
		if ($min < 10 && $hour > 0) {
			$min = '0' . $min . ':';
		}
		else {
			$min = $min . ':';
		}
	}
	else {
		$min = '';
	}
	
	if ($sec < 10 && $min > 0) {
		$sec = '0' . number_format($sec, 3);
	}
	else {
		$sec = number_format($sec, 3);
	}
	return $hour . $min . $sec;
	}
	else {// Nem szám
		return $secs;
	}
}
// Pontszerző-e
function scored($place, $yr) {
		$result = 'fshd';
		
		if ($yr >= 1950 && $yr <= 1959 && $place <= 5) {
			$result = 'scrd';
		}
		
		if ($yr >= 1960 && $yr <= 2002 && $place <= 6) {
			$result = 'scrd';
		}
		
		if ($yr >= 2003 && $yr <= 2009 && $place <= 8) {
			$result = 'scrd';
		}
		
		if ($yr >= 2010 && $place <= 10) {
			$result = 'scrd';
		}
		
		return $result;
		
}
// Cella osztálya (szín)
function cellclass($place, $yr) {
	switch ($place) {		
		case 1:
			return 'first';
			break;
			
		case 2:
			return 'second';
			break;
		
		case 3:
			return 'third';
			break;
			
		default: $place;
	}
	
	if (is_numeric($place)) {
		return scored($place, $yr);
	}
	else {
		return $place;
	}
}
// Státusz számból szövegbe
function status($status) {
	switch ($status) {
		case 1:  return 'c';    break;		
		case 2:  return 'NC';   break;
		case 3:  return 'ret';  break;
		case 4:  return 'DNS';	break;
		case 5:  return 'DSQ';	break;
		case 6:  return 'DNQ';	break;
		case 7:  return 'DNPQ';	break;
		case 8:	 return 'DNP';	break;
		case 9:  return 'WD';	break;
		case 10: return 'EX';	break;
		case 11: return 'PO';	break;
		
		default: return '';
	}
}

// Random hossz
function randomstring($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}
// Kor
function age($date1, $date2) {
	// FELDOLGOZÁS
	$d1 = explode('-', $date1);
	$d2 = explode('-', $date2);

	foreach ($d1 as $key => $value) {
		$d1[$key] = intval($value);
	}
	foreach ($d2 as $key => $value) {
		$d2[$key] = intval($value);
	}

	if (!( // NINCS HALÁL DÁTUM (v. érvénytelen)
		($d2[0] > $d1[0]) ||
		($d2[0] == $d1[0] && $d2[1] > $d1[1]) ||
		($d2[0] == $d1[0] && $d2[1] == $d1[1] && $d2[2] >= $d1[2])
		)) {
		$localtime = localtime(time(), true);
		
		$d2[0] = 1900 + $localtime['tm_year'];
		$d2[1] = $localtime['tm_mon'] + 1;
		$d2[2] = $localtime['tm_mday'];
	}
	
	// KÜLÖNBSÉG KISZÁMÍTÁSA
	if (
		($d2[1] > $d1[1]) ||
		($d2[1] == $d1[1] && $d2[2] >= $d1[2])
		) {
		$diff = $d2[0] - $d1[0];
	}
	else {
		$diff = $d2[0] - $d1[0]-1;
	}
	return $diff;
}
// Születésnap (ha érvényes)
function birthday($date) {
	// FELDOLGOZÁS
	$d1 = explode('-', $date);
	foreach ($d1 as $key => $value) {
		$d1[$key] = intval($value);
	}

	$localtime = localtime(time(), true);
	
	$d2[0] = 1900 + $localtime['tm_year'];
	$d2[1] = $localtime['tm_mon'] + 1;
	$d2[2] = $localtime['tm_mday'];
	
	if ($d1[1] == $d2[1] && $d1[2] == $d2[2]) {
		return $d2[0] - $d1[0];
	}
	else {
		return 0;
	}
}

// Kép
function picture($path, $style) {
	$ext = explode('.', $path);
	$ext = isset($ext[1]) ? true : false; // Van/nincs kiterjesztés (ne legyen benne pont!)
	
	if ($ext) {
		if (file_exists($path)) {
			return '<img src="/'.$path.'" style="'.$style.'"></br>';
		}
	}
	else {
		$xts = array('jpg', 'JPG', 'png', 'gif');
		foreach ($xts as $xt) {
			if (file_exists($path.'.'.$xt)) {
				return '<img src="/'.$path.'.'.$xt.'" style="'.$style.'"></br>';
			}
		}
	}
	return '';
}

// Motor tulajdonságok
function engine_param($vol, $conc, $cyl, $turbo) {
	if ($vol > 0) {$vol = number_format($vol, 1, '.', '').' ';} else {$vol = '';}
	return $vol.$conc.$cyl.$turbo;
}

// Dobogó
function podium($_1, $_2, $_3) {
	$_1 = $_1 != 0 ? $_1 : '';
	$_2 = $_2 != 0 ? $_2 : '';
	$_3 = $_3 != 0 ? $_3 : '';
	
	echo '<center><table style="text-align:center;"><tr>';
	echo '<td width="72" class="podium" style="top:15px;">' . $_2 . '</td>';
	echo '<td width="72" class="podium" >' . $_1 . '</td>';
	echo '<td width="72" class="podium" style="top:30px;">' . $_3 . '</td>';
	echo '</tr></table>';

	echo '<img src="/images/podium.png"></center>';
}

// Social media
function media_list($type, $name) {
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
		echo '<img src="/images/social/'.$tp.'.png" height="24" width="24"
			style="position:relative; top:7px; margin-right:8px;">';
		echo $name;
		echo '</a><br>';
	}
}

// 404 generátor
function marker_404($title) {
	return '<img src="'.$title.'.png" style="visibility:hidden; width:0px; height:0px;">';
}

// Tömb kiíró (szép)
function vardump($var) {
	echo "<pre>";
	print_r($var);
	echo "</pre>";
}
?>