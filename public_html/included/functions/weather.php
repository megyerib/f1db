<?php
/* Időjárás scriptek
   
   * Aktuális                  current_weather($lat, $lon);
   * Rövid előrejelzés         short_forecast($lat, $lon);
     5 napos, 3 óránként
   * Hosszú előrejelzés        long_forecast($lat, $lon, $days);
     16 napos (állítható)
	 Részletes min/max hőm.,
	 páratartalom, stb.
   
   http://openweathermap.org/api */ 

function wind_direction($deg) {
	switch ($deg) {
	case                 $deg <=  22.5: return 'N';
	case  22.5 < $deg && $deg <=  67.5: return 'NW';
	case  67.5 < $deg && $deg <= 112.5: return 'W';
	case 112.5 < $deg && $deg <= 157.5: return 'SW';
	case 157.5 < $deg && $deg <= 202.5: return 'S';
	case 202.5 < $deg && $deg <= 247.5: return 'SE';
	case 247.5 < $deg && $deg <= 292.5: return 'E';
	case 292.5 < $deg && $deg <= 337.5: return 'NE';
	case 337.5 < $deg                 : return 'N';
	}
}
define ('img_dir', 'icons/');
function weather_icon($no, $alt = '') {
	return '<img src="'.img_dir.'weather/'.$no.'.png" alt="'.$alt.'" title="'.$alt.'">';
}
function wind_icon($dir) {
	return '<img src="'.img_dir.'wind/'.$dir.'.png" alt="'.$dir.'" title="'.$dir.'">';
}
$lat = 46.63;
$lon = 21.29;
// AKTUÁLIS IDŐJÁRÁS
function current_weather($lat, $lon) {
	$json = 'http://api.openweathermap.org/data/2.5/weather?lat='.$lat.'&lon='.$lon;

	$data = json_decode(file_get_contents($json));
	$weather = $data->{'weather'};
	$weather = $weather[0];

	$icon = $weather->{'icon'};
	$descr = $weather->{'description'};
	$temp = round(($data->{'main'}->{'temp'})-273.15); // K -> °C
	$wind_speed = round(($data->{'wind'}->{'speed'})*3.6); // m/s -> km/h
	$wind_deg = $data->{'wind'}->{'deg'};

	echo weather_icon($icon, $descr).'<br>';
	echo $temp.'&deg;C<br>';
	echo wind_icon(wind_direction($wind_deg));
	echo $wind_speed.' km/h ';
}
// 5 NAPOS
function short_forecast($lat, $lon) {
	$json = 'http://api.openweathermap.org/data/2.5/forecast?lat='.$lat.'&lon='.$lon;
	$data = json_decode(file_get_contents($json));

	$week = $data->{'list'};
	foreach ($week as $day) {
		$weather = $day->{'weather'};
		$weather = $weather[0];
		
		$time = $day->{'dt_txt'};
		$icon = $weather->{'icon'};
		$descr = $weather->{'description'};
		$temp = round(($day->{'main'}->{'temp'})-273.15); // K -> °C
		$wind_speed = round(($day->{'wind'}->{'speed'})*3.6); // m/s -> km/h
		$wind_deg = $day->{'wind'}->{'deg'};
		
		echo $time.'<br>';
		echo weather_icon($icon, $descr).'<br>';
		echo $temp.'&deg;C<br>';
		echo wind_icon(wind_direction($wind_deg));
		echo $wind_speed.' km/h<br><br>';
		
	}
}



// 14 NAPOS
$days = 3;
function long_forecast($lat, $lon, $days) {
	$json = 'http://api.openweathermap.org/data/2.5/forecast/daily?lat='.$lat.'&lon='.$lon.'&cnt='.$days;
	$data = json_decode(file_get_contents($json));
	$week = $data->{'list'};
	
	foreach ($week as $day) {
		$weather = $day->{'weather'};
		$weather = $weather[0];
		$icon = $weather->{'icon'};
		$descr = $weather->{'description'};
		$time = gmdate("Y-m-d", $day->{'dt'});
		$temp_day = round(($day->{'temp'}->{'day'})-273.15); // K -> °C
		$temp_min = round(($day->{'temp'}->{'min'})-273.15); // K -> °C
		$temp_max = round(($day->{'temp'}->{'max'})-273.15); // K -> °C
		$wind_speed = $day->{'speed'};
		$wind_deg = $day->{'deg'};
		
		echo weather_icon($icon, $descr).'<br>';
		echo $time.'<br>';
		echo $temp_min.'&deg;C / '.$temp_max.'&deg;C<br>';
		echo $wind_speed.' ';
		echo wind_direction($wind_deg).'<br><br>';
	}
	// Sok adat, érdemes egyszer bővíteni (pl. éjszakai futamok - esti időjárás, stb.)
	//echo "<hr><pre>"; print_r($data); echo "</pre>";
}
?>