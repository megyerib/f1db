<?php
// Névkiíró függvények
function name($f, $de, $s, $sr) {
	return "$f ".($de?"$de ":"").$s.($sr?", $sr.":"");
}

function nameShort($f, $de, $s, $sr) { // sr: argumentumlista miatt
	return "$f[0] ".($de?"$de ":"").$s;
}

function nameReverse($f, $de, $s, $sr) {
	return "$s, ".($de?"$de ":"").$f.($sr?", $sr.":"");
}

function leapYear($y) { // Szökőév
	return !($y%4)&&($y%100||!($y%400));
}

function dayOfYear($year, $month, $day) { // Hanyadik nap az évben?
	// A szökőévet lekezeli, de nem hülyebiztos (érvénytelen értékre)
	$mday = array(31,leapYear($year)?29:28,31,30,31,30,31,31,30,31,30,31);
	
	$res = $day;
	for ($i=0; $i<$month; $i++) {
		$res += $i > 0 ? $mday[$i-1] : 0;
	}
	return $res;
}

function passed($from, $to = 0) { // YYYY-MM-DD, nem hülyebiztos (from > to)
	$f = explode("-", $from);
	$t = explode("-", $to?$to:date("Y-m-d"));
	
	// From később van az évben
	if ($f[1]>$t[1] || ($f[1] == $t[1]&&$f[2]>$t[2])) { // Nincs +1 év
		$rem1 = (leapYear($f[0])?366:365) - dayOfYear($f[0],$f[1],$f[2]);
		$psd2 = dayOfYear($t[0],$t[1],$t[2]);
		
		$diffY = $t[0]-$f[0]-1;
		$diffD = $rem1+$psd2;
	}
	// To később van az évben
	else { // Van +1 év
		$diffY = $t[0]-$f[0];
		$diffD = dayOfYear($t[0],$t[1],$t[2])-dayOfYear($t[0],$f[1],$f[2]);
	}
	return "$diffY years, $diffD days";
}

function interval($yrs, $cur = 0) {
	$res = array();
	$str = "";
	
	for ($i = 0; $i < count($yrs); $i++) {
		$last = $i == count($yrs)-1;
		
		// Elkezd
		if (empty($str)) {
			$str = $yrs[$i];
		}
		
		// Folytat
		if (!$last&&$yrs[$i]==$yrs[$i+1]-1) {
			continue;
		}
		
		// Lezár
		else {
			if ($str!=$yrs[$i]) {
				$str .= (!empty($str)?" -":"").
						($last&&$yrs[$i]==$cur?"":" $yrs[$i]");
			}
			array_push($res, $str.($last&&$yrs[$i]==$cur?" -":""));
			$str = "";
		}
	}
	return $res; // Tömböt ad vissza, implode-dal érdemes kiíratni
}

function ordinal($num) {
	$suf = array('th', 'st', 'nd', 'rd');
	for ($i = 1; $i <= 3; $i++) {
		if ($num % 10 == $i && $num % 100 != 10 + $i) {
			return "$num$suf[$i]";
		}
	}
	return "$num$suf[0]";
}

function timeToSec($time) { // H:m:s -> s (nem hülyebiztos)
	$time = explode(':', $time);
	$res = 0;
	for($i = count($time)-1; $i >= 0; $i--) {
		$res += $time[$i] * pow(60, count($time)-$i-1);
	}
	return $res;
}

function secToTime($sec) { // Másodperc időbe (max. 3 tizedes)
	$hours = floor($sec/3600);
	$mins = floor($sec%3600/60);
	$secs = round($sec%60+fmod($sec,1), 3);
	
	$mins = $mins<10?0 .$mins:$mins;
	$secs = $secs<10?0 .$secs:$secs;
	
	return "$hours:$mins:$secs";
}

function randomString($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

function img($path, $style='') {
	$ext = explode('.', $path);
	$ext = isset($ext[1]) ? true : false; // Van/nincs kiterjesztés
	
	if ($ext) {
		if (file_exists("$path")) { // Csak így működik 1
			return '<img src="/'.$path.'" style="'.$style.'">'; // 2
		}
	}
	else {
		$xts = array('jpg', 'JPG', 'png', 'gif');
		foreach ($xts as $xt) {
			if (file_exists("$path.$xt")) {
				return '<img src="/'.$path.'.'.$xt.'" style="'.$style.'">';
			}
		}
	}
	return false;
}
function engineName($cons, $type, $concept, $cylinders, $turbo) {
	if (empty($type)) {
		/*$unknown = 'Unknown ';
		$param = ' '.$concept.$cylinders.$turbo;
		$type = '';*/
		return "Unknown $cons $concept$cylinders$turbo";
	} else {
		$unknown = '';
		$param = '';
		$type = ' '.$type;
		return "$cons $type";
	}
	//return $unknown.$cons.$type.$param;
}

function chassisName($cons, $type) {
	if (!empty($type)) {
		/*$unknown = '';
		$type = ' '.$type;*/
		return "$cons $type";
	} else {
		/*$unknown = 'Unknown ';
		$type = '';*/
		return "Unknown $cons";
	}
	//return $unknown.$cons.$type;
}
?>