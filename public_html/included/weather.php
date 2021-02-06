<?php	
$weather_exists = false;	

if (isset($local_id)) {
	$file = 'http://api.theweather.com/index.php?api_lang=eu&localidad='.$local_id.'&affiliate_id=4c7c1jz3rbmh';
	if ($xml = simplexml_load_file($file)){
		if (!isset($xml->error)) {$weather_exists = true;}
	}
}

if ($weather_exists) {
	$var1  = array(0=>"Temp min."); 
	$var2  = array(0=>"Temp max."); 
	$var3  = array(0=>"Wind."); 
	$var4  = array(0=>"Symbol.");
	$var5  = array(0=>"Day."); 
	$array = array();

	$file = 'http://api.theweather.com/index.php?api_lang=eu&localidad='.$local_id.'&affiliate_id=4c7c1jz3rbmh'; 
	// We recover the file data to treat
	if($xml = simplexml_load_file($file)){
		$i=0;
		$nday=7; // Hány napos előrejelzés? (max 7)
		$url= $xml->location->interesting->url;
		$array=explode('-', $url);
		
		foreach ($xml->location->var as $var) {
			switch ($i) {
				case 0: 
					$j=0;
					for($j=0; $j<$nday; $j++){$var1 = $var1 + array($j+1=>htmlentities($xml->location->var[$i]->data->forecast[$j]->attributes()->value,ENT_COMPAT,'UTF-8'));}
				break;
				case 1:
					$j=0;
					for($j=0; $j<$nday; $j++){$var2 = $var2 + array($j+1=>htmlentities($xml->location->var[$i]->data->forecast[$j]->attributes()->value,ENT_COMPAT,'UTF-8'));}
				break;
				case 2:
					$j=0;
					for($j=0; $j<$nday; $j++){$var3 = $var3 + array($j+1=>htmlentities($xml->location->var[$i]->data->forecast[$j]->attributes()->value,ENT_COMPAT,'UTF-8'));}
				break;
				case 3: 
					$j=0;
					for($j=0; $j<$nday; $j++){$var4 = $var4 + array($j+1=>htmlentities($xml->location->var[$i]->data->forecast[$j]->attributes()->value,ENT_COMPAT,'UTF-8'));}
				break;
				case 4: 
					$j=0;
					for($j=0; $j<$nday; $j++){$var5 = $var5 + array($j+1=>htmlentities($xml->location->var[$i]->data->forecast[$j]->attributes()->value,ENT_COMPAT,'UTF-8'));}
				break;
			}//switch
			$i++;
		}//foreach
	}//if

// Táblázat
echo '<table>';
$i=1;
for($i=1; $i<$nday+1; $i++){

	// Nap
	echo '<tr>';
	echo '<td colspan="2" style="font-weight:bold;">';
	echo $var5[$i];
	echo '</td>';
	
	// Időjárás kép
	if (isset($var4[$i])){		
			switch ($var4[$i]) {
				case 'Sunny':
					$path  = '1';	break;
				case 'Cloudy intervals':
					$path  = '2';	break;
				case 'Cloudy skies':
					$path  = '3';	break;
				case 'Overcast':
					$path  = '4';	break;
				case 'Cloudy intervals with light rain':
					$path  = '5';	break;
				case 'Cloudy with light rain':
					$path  = '6';	break;
				case 'Overcast with light rain':
					$path  = '7';	break;
				case 'Cloudy intervals with moderate rain':
					$path  = '8';	break;
				case 'Cloudy with moderate rain':
					$path  = '9';	break;
				case 'Overcast with moderate rain':
					$path  = '10';	break;
				case 'Cloudy intervals with thunderstorms':
					$path  = '11';	break;
				case 'Cloudy with thunderstorms':
					$path  = '12';	break;
				case 'Overcast with thunderstorms':
					$path  = '13';	break;
				case 'Cloudy intervals, thunderstorms and hail':
					$path  = '14';	break;
				case 'Cloudy, thunderstorms and hail':
					$path  = '15';	break;
				case 'Overcast, thunderstorms and hail':
					$path  = '16';	break;
				case 'Cloudy intervals and snow':
					$path  = '17';	break;
				case 'Cloudy and snow':
					$path  = '18';	break;
				case 'Overcast an snow':
					$path  = '19';	break;
				default:
					$path = '';	break;
			}
			
			$path  = '/images/weather/weather/'.$path.'.png';
			$title = $var4[$i];
			echo '<td rowspan="2">';
			echo '<img src="'.$path.'" alt="'.$title.'" title="'.$title.'" width="42" height="42">';
			echo '</td>';
	} //fi if $var4[$i]
	
	if (isset($var3[$i])){
			// Szél kép
	switch ($var3[$i]) {
		case 'Light N wind': $path = 'n_1'; break;
		case 'Light NE wind': $path = 'ne_1'; break;
		case 'Light E wind': $path = 'e_1'; break;
		case 'Light SE wind': $path = 'se_1'; break;
		case 'Light S wind': $path = 's_1'; break;
		case 'Light SW wind': $path = 'sw_1'; break;
		case 'Light W wind': $path = 'w_1'; break;
		case 'Light NW wind': $path = 'nw_1'; break;

		case 'Moderate N wind': $path = 'n_2'; break;
		case 'Moderate NE wind': $path = 'ne_2'; break;
		case 'Moderate E wind': $path = 'e_2'; break;
		case 'Moderate SE wind': $path = 'se_2'; break;
		case 'Moderate S wind': $path = 's_2'; break;
		case 'Moderate SW wind': $path = 'sw_2'; break;
		case 'Moderate W wind': $path = 'w_2'; break;
		case 'Moderate NW wind': $path = 'nw_2'; break;

		case 'Wind N wind': $path = 'n_3'; break;
		case 'Wind NE wind': $path = 'ne_3'; break;
		case 'Wind E wind': $path = 'e_3'; break;
		case 'Wind SE wind': $path = 'se_3'; break;
		case 'Wind S wind': $path = 's_3'; break;
		case 'Wind SW wind': $path = 'sw_3'; break;
		case 'Wind W wind': $path = 'w_3'; break;
		case 'Wind NW wind': $path = 'nw_3'; break;

		case 'Strong N wind': $path = 'n_4'; break;
		case 'Strong NE wind': $path = 'ne_4'; break;
		case 'Strong E wind': $path = 'e_4'; break;
		case 'Strong SE wind': $path = 'se_4'; break;
		case 'Strong S wind': $path = 's_4'; break;
		case 'Strong SW wind': $path = 'sw_4'; break;
		case 'Strong W wind': $path = 'w_4'; break;
		case 'Strong NW wind': $path = 'nw_4'; break;
		
		case 'Variable wind': $path = 'var'; break;
	}
		
		$path  = '/images/weather/wind/'.$path.'.png';
		$title = $var3[$i];
		echo '<td rowspan="2">';
		echo '<img src="'.$path.'" alt="'.$title.'" title="'.$title.'" width="42" height="42">';
		echo '</td>';
		echo '</tr>';
	
	}//fin if $var3
	
	// Min hőm.
	echo '<tr>';
	echo '<td>';
	echo $var1[$i].'&deg;C';
	echo '</td>';

	// Max hőm.
	echo '<td>';
	echo $var2[$i].'&deg;C';
	echo '</td>';
	echo '</tr>';
}
echo '</table>';
echo '<a href="http://theweather.com/" target="_blank"><img src="/images/weather/theweather.png" width="150" align="right"></a>';
} // Egész vége
?>