<?php
//////////////
// DROPDOWN //
//////////////

// custom_dropdown($dropdown_name, $query, $id_field, $name_function, $chosen);

// JQuery 
$jq_counter = 0;
function jq_counter() {
	global $jq_counter;
	$cur = $jq_counter;
	$jq_counter++;
	return $cur;
}

$dropdown_lists = array();

// Alap függvény
function custom_dropdown($dropdown_name, $query, $id_field, $name_function, $chosen) {
	global $f1db, $dropdown_lists;
	$list_id = 'dropdown_'.jq_counter();
	
	// Névfüggvény
	$func = explode(' ', $name_function);
	if (count($func) == 2) {
		$name_f = $func[0];
		$name_e = explode(',',$func[1]);
	}
	else {
		$name_e = explode(',',$func[0]);
	}
	
	// Ha még nincs felépítve a lista, felépíti
	if (empty($dropdown_lists[$dropdown_name]) || !isset($dropdown_lists[$dropdown_name])) {
		$dropdown_lists[$dropdown_name] = '<option value="0">---</option>';
		if (!is_array($query)) {
			$q = mysqli_query($f1db,
				"$query"
			);

			while ($row = mysqli_fetch_array($q)) {
				$name_a = array();
				foreach ($name_e as $e) {
					array_push($name_a, $row[$e]);
				}
				if (isset($name_f)) {
					$name = call_user_func_array($name_f, $name_a);
				}
				else {
					$name = implode(' ', $name_a);
				}
				$dropdown_lists[$dropdown_name] .= '<option value="'.$row[$id_field].'">'.$name.'</option>';
			}
		}
		else {
			foreach ($query as $key => $val) {
				$dropdown_lists[$dropdown_name] .= '<option value="'.$key.'">'.$val.'</option>';
			}
		}
	}
	
	// Kiír
	echo '<select name="'.$dropdown_name.'" id="'.$list_id.'">';
	echo $dropdown_lists[$dropdown_name];
	echo "</select>\n";
	echo '<script>$("#'.$list_id.'").val("'.$chosen.'");</script>';
}

// Pilóta
function driver_dropdown($dropdown_name, $chosen) {
	$query = "SELECT *
		FROM driver
		ORDER BY last ASC";
	$id_field = 'no';
	$name_function = 'name_2 first,de,last,sr';
	
	custom_dropdown($dropdown_name, $query, $id_field, $name_function, $chosen);
}

// Csapat
function team_dropdown($dropdown_name, $chosen) {
	$query = "SELECT *
		FROM team
		ORDER BY fullname ASC";
	$id_field = 'no';
	$name_function = 'fullname';
	
	custom_dropdown($dropdown_name, $query, $id_field, $name_function, $chosen);
}

// Kasztnigyártó
function chassis_cons_dropdown($dropdown_name, $chosen) {
	$query = "SELECT *
		FROM team
		WHERE chassis = 1
		ORDER BY fullname ASC";
	$id_field = 'no';
	$name_function = 'fullname';
	
	custom_dropdown($dropdown_name, $query, $id_field, $name_function, $chosen);
}

// Motorgyártó
function engine_cons_dropdown($dropdown_name, $chosen) {
	$query = "SELECT *
		FROM team
		WHERE engine = 1
		ORDER BY fullname ASC";
	$id_field = 'no';
	$name_function = 'fullname';
	
	custom_dropdown($dropdown_name, $query, $id_field, $name_function, $chosen);
}

// Kasztni
function chassis_dropdown($dropdown_name, $chosen) {
	$query = "SELECT *, chassis.no AS chassis_no
		FROM chassis
		INNER JOIN team
		ON chassis.cons = team.no
		ORDER BY team.fullname, chassis.type ASC";
	$id_field = 'chassis_no';
	$name_function = 'chassis_name fullname,type';
	
	custom_dropdown($dropdown_name, $query, $id_field, $name_function, $chosen);
}

// Motor
function engine_dropdown($dropdown_name, $chosen) {
	$query = "SELECT *, engine.no AS engine_no
		FROM engine
		INNER JOIN team
		ON engine.cons = team.no
		ORDER BY team.fullname, engine.type ASC";
	$id_field = 'engine_no';
	$name_function = 'engine_name fullname,type,volume,concept,cylinders,turbo';
	
	custom_dropdown($dropdown_name, $query, $id_field, $name_function, $chosen);
}

// Aktív csapat
function active_team_dropdown($dropdown_name, $chosen) {
	$query = "SELECT active.no, team.fullname
		FROM f1_active_team AS active
		INNER JOIN team
		ON active.no = team.no
		ORDER BY fullname ASC";
	$id_field = 'no';
	$name_function = 'fullname';
	
	custom_dropdown($dropdown_name, $query, $id_field, $name_function, $chosen);
}

// Gumi
function tyre_dropdown($dropdown_name, $chosen) {
	$query = "SELECT *
		FROM tyre
		ORDER BY fullname ASC";
	$id_field = 'id';
	$name_function = 'fullname';
	
	custom_dropdown($dropdown_name, $query, $id_field, $name_function, $chosen);
}

// Nemzetiség
function nationality_dropdown($dropdown_name, $chosen) {
	$query = "SELECT gp, name
		FROM country
		WHERE country != ''
		ORDER BY name ASC";
	$id_field = 'gp';
	$name_function = 'name';
	
	custom_dropdown($dropdown_name, $query, $id_field, $name_function, $chosen);
}

// Ország
function country_dropdown($dropdown_name, $chosen) {
	$query = "SELECT gp, country
		FROM country
		WHERE country != ''
		ORDER BY name ASC";
	$id_field = 'gp';
	$name_function = 'country';
	
	custom_dropdown($dropdown_name, $query, $id_field, $name_function, $chosen);
}

// GP (pl. British, German, ...)
function gp_dropdown($dropdown_name, $chosen) {
	$query = "SELECT gp, name
		FROM country
		ORDER BY name ASC";
	$id_field = 'gp';
	$name_function = 'name';
	
	custom_dropdown($dropdown_name, $query, $id_field, $name_function, $chosen);
}

// Circuit
function circuit_dropdown($dropdown_name, $chosen) {
	$query = "SELECT no, fullname
		FROM circuit
		ORDER BY fullname ASC";
	$id_field = 'no';
	$name_function = 'fullname';
	
	custom_dropdown($dropdown_name, $query, $id_field, $name_function, $chosen);
}

// Race
function race_dropdown($dropdown_name, $chosen) {
	$query = "SELECT no, gp.yr, country.name
		FROM f1_gp AS gp
		INNER JOIN country
		ON gp.gp = country.gp
		ORDER BY no DESC";
	$id_field = 'no';
	$name_function = 'yr,name';
	
	custom_dropdown($dropdown_name, $query, $id_field, $name_function, $chosen);
}

// Status
function status_dropdown($dropdown_name, $chosen) {
	$query = array(
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
	$id_field = '';
	$name_function = '';
	
	custom_dropdown($dropdown_name, $query, $id_field, $name_function, $chosen);
}

// Media
function media_dropdown($dropdown_name, $chosen) {
	$query = array(
		1 => 'Website',
		2 => 'Facebook',
		3 => 'Twitter',
		4 => 'Instagram',
		5 => 'YouTube',
		6 => 'Google+',
		7 => 'Flickr',
		8 => 'LinkedIn',
		9 => 'Pinterest',
		10 => 'Weibo',
		11 => 'RSS Feed'
	);
	$id_field = '';
	$name_function = '';
	
	custom_dropdown($dropdown_name, $query, $id_field, $name_function, $chosen);
}

// Nem
function gender_dropdown($dropdown_name, $chosen) {
	$query = array(
		'M' => 'Male',
		'F' => 'Female'
	);
	$id_field = '';
	$name_function = '';
	
	custom_dropdown($dropdown_name, $query, $id_field, $name_function, $chosen);
}

// Régió
function region_dropdown($dropdown_name, $chosen) {
	$query = array(
		'EU' => 'Europe',
		'NA' => 'Northern America',
		'SA' => 'Southern America',
		'AS' => 'Asia',
		'OC' => 'Oceania',
		'AF' => 'Africa'
	);
	$id_field = '';
	$name_function = '';
	
	custom_dropdown($dropdown_name, $query, $id_field, $name_function, $chosen);
}

// Teszt
function test_dropdown($name, $chosen) {
	$query = "SELECT *
		FROM f1_test
		ORDER BY yr, no_yr ASC";
		
	$id_field = 'no';
	$name_function = 'yr,name';
	
	custom_dropdown($name, $query, $id_field, $name_function, $chosen);
}
?>