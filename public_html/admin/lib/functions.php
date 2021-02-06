<?php

// Teljes név kiírása
function name($f, $de, $l, $sr) {
	if ($de != '') {
		$de = $de . ' ';
	}
	if ($sr != '') {
		$sr = ' ' . $sr . '.';
	}
	
	return $f . ' ' . $de . $l . $sr;
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
function engine_name($cons, $type, $volume, $concept, $cylinders, $turbo) {
	if (empty($type)) {
		$unknown = 'Unknown ';
		$volume = $volume > 0 ? ' '.$volume : '';
		$param = $volume.' '.$concept.$cylinders.$turbo;
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
// Idő
function dectotime($dec) {
	if (is_numeric($dec)) {
		if ($dec == 0) {return 0;}
		// Órák
		$hours = floor($dec/3600);
		
		// Percek
		$dec = $dec - ($hours*3600);
		$mins = floor($dec/60);
		
		// Másodpercek
		$dec = $dec - ($mins*60);
		$secs = $dec;
		
		// Formázás
		if ($hours > 0) {
			$hours = $hours.':';
		}
		else {
			 $hours = '';
		}
		
		if ($mins >= 10) {
			$mins = $mins.':';
		}
		if ($mins < 10 && $mins > 0) {
			$mins = '0'.$mins.':';
		}
		if ($mins == 0) {
			if ($hours > 0) {
				$mins = '00:';
			}
			else {
				$mins = '';
			}
		}
		
		$secs = number_format($secs, 3, '.', '');
		
		if (($secs < 10 && $secs > 0) && ($hours > 0 || $mins > 0)) {
			$secs = number_format('0'.$secs, 3, '.', '');
		}
		if ($secs == 0) {
			if ($hours > 0 || $mins > 0) {
				$secs = '00.000';
			}
			else {
				$secs = '';
			}
		}
		
		return $hours.$mins.$secs;
	}
	else {
		return 0;
	}
}
function timetodec($time) {
	if ($time == '') {return 0;}
	
	$time = explode(':', $time);
	$count = count($time);
	if ($count == 3) {
		$dec = $time[0]*3600+$time[1]*60+$time[2];
	}
	if ($count == 2) {
		$dec = $time[0]*60+$time[1];
	}
	if ($count == 1) {
		$dec = $time[0];
	}
	return $dec;
}

$statuses = array(
	 1 => 'c',
	 2 => 'NC',
	 3 => 'ret',
	 4 => 'DNS',
	 5 => 'DSQ',
	 6 => 'DNQ',
	 7 => 'DNPQ',
	 8 => 'DNP',
	 9 => 'WD',
	10 => 'EX',
	11 => 'PO'
);

// Státusz számból szövegbe
function status($status) {
	global $statuses;
	foreach ($satauses as $no => $stat) {
		if ($status == $no) {
			return $stat;
		}
	}
	return '';
}

// Státusz szövegből számba (nem betűállás- érzékeny)
function status2num($status) {
	global $statuses;
	foreach ($statuses as $no => $stat) {
		if (strtolower($status) == strtolower($stat)) {
			return $no;
		}
	}
	return 0;
}

function vardump($var) {
	echo '<pre>';
	var_dump($var);
	echo '</pre>';
}

function alert($text) {
	echo '<div class="alert">'.$text.'</div>';
}
function msg($text) {
	echo '<div class="msg">'.$text.'</div>';
}
?>