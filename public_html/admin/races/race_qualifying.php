<?php
// Fő tábla mentés
if (isset($_POST['save_res'])) {
	$dnq_i = 0; $dsq_i = 0;
	foreach ($_POST['entr_no'] as $key => $entr_no) {
		// entr_no
			// foreach kritériumaiban meghatározva
		
		// place
			$place = $_POST['place'][$key] != '' ? $_POST['place'][$key] : 0;
			
		// laps
			$laps = $_POST['laps'][$key] != '' ? $_POST['laps'][$key] : 0;
			
		// q1
			switch ($_POST['q1'][$key]) {
				case '':
					$q1 = 0;
					break;
				case 0:
					$q1 = -1;
					break;
				default:
					$q1 = timetodec($_POST['q1'][$key]);
					break;
			}
		// q2
			switch ($_POST['q2'][$key]) {
				case '':
					$q2 = 0;
					break;
				case 0:
					$q2 = -1;
					break;
				default:
					$q2 = timetodec($_POST['q2'][$key]);
					break;
			}
		// q3
			switch ($_POST['q3'][$key]) {
				case '':
					$q3 = 0;
					break;
				case 0:
					$q3 = -1;
					break;
				default:
					$q3 = timetodec($_POST['q3'][$key]);
					break;
			}
			
		// DNQ
		if (isset($_POST['dnq'][$dnq_i]) && $_POST['dnq'][$dnq_i] == $entr_no) {
			$dnq = 1;
			$dnq_i++;
		}
		else {
			$dnq = 0;
		}
		
		// DSQ
		if (isset($_POST['dsq'][$dsq_i]) && $_POST['dsq'][$dsq_i] == $entr_no) {
			$dsq = 1;
			$dsq_i++;
		}
		else {
			$dsq = 0;
		}
		
		$save = mysqli_query($f1db,
			"UPDATE f1_q
			SET place = $place,
			q1 = $q1,
			q2 = $q2,
			q3 = $q3,
			laps = $laps,
			dnq = $dnq,
			dsq = $dsq
			WHERE entr_no = $entr_no"
		);
	}
}

// Hozzáadogató

// Mentés
if (isset($_POST['save_adder'])) {
	$add = array();
	// 1. Add form beolvasása
	$q1 = $q2 = $q3 = 0;
	foreach ($_POST['entr_no'] as $entr_no) {
		$level = 0;
		if (isset($_POST['q1'][$q1]) && $_POST['q1'][$q1] == $entr_no) {
			$level = 1;
			$q1++;
		}
		if (isset($_POST['q2'][$q2]) && $_POST['q2'][$q2] == $entr_no) {
			$level = 2;
			$q2++;
		}
		if (isset($_POST['q3'][$q3]) && $_POST['q3'][$q3] == $entr_no) {
			$level = 3;
			$q3++;
		}
		$add[$entr_no]['level'] = $level;
		$add[$entr_no]['q']     = 0;
	}
	// Eddigi időmérősök beolvasása
	$qual = mysqli_query($f1db,
		"SELECT entr_no, q1, q2, q3
		FROM f1_q AS q
		INNER JOIN f1_race AS race
		ON q.entr_no = race.no
		WHERE race.rnd = $race_no"
	);
	if (mysqli_num_rows($qual) > 0) {
		while ($row = mysqli_fetch_array($qual)) {
			$q = $row['q1'] != 0 ? 1 : 0;
			$q = $row['q2'] != 0 ? 2 : $q;
			$q = $row['q3'] != 0 ? 3 : $q;
			
			$add[$row['entr_no']]['q'] = $q;
		}
	}
	// Végleges feldolgozás
	foreach ($add as $entr_no => $dat) {
		$prev = $dat['q'];
		$next = $dat['level'];
		
		if ($prev != $next) { // Hogy is mondta? Overkill? Hát igen, az.
			if ($next == 0) { // Töröl
				mysqli_query($f1db,
					"DELETE FROM f1_q
					WHERE entr_no = $entr_no"
				);
			}
			else if ($prev == 0) { // Hozzáad
				$q1 = $next >= 1 ? -1 : 0; // Biztos -1
				$q2 = $next >= 2 ? -1 : 0;
				$q3 = $next >= 3 ? -1 : 0;
				mysqli_query($f1db,
					"INSERT INTO f1_q(entr_no, q1, q2, q3)
					VALUES ($entr_no, $q1, $q2, $q3)"
				);
			}
			else if ($prev == 1 && $next == 2) {
				mysqli_query($f1db,
					"UPDATE f1_q
					SET q2 = -1
					WHERE entr_no = $entr_no"
				);
			}
			else if ($prev == 1 && $next == 3) {
				mysqli_query($f1db,
					"UPDATE f1_q
					SET q2 = -1,
					    q3 = -1
					WHERE entr_no = $entr_no"
				);
			}
			else if ($prev == 2 && $next == 1) {
				mysqli_query($f1db,
					"UPDATE f1_q
					SET q2 = 0
					WHERE entr_no = $entr_no"
				);
			}
			else if ($prev == 2 && $next == 3) {
				mysqli_query($f1db,
					"UPDATE f1_q
					SET q3 = -1
					WHERE entr_no = $entr_no"
				);
			}
			else if ($prev == 3 && $next == 1) {
				mysqli_query($f1db,
					"UPDATE f1_q
					SET q2 = 0,
					    q3 = 0
					WHERE entr_no = $entr_no"
				);
			}
			else if ($prev == 3 && $next == 2) {
				mysqli_query($f1db,
					"UPDATE f1_q
					SET q3 = 0
					WHERE entr_no = $entr_no"
				);
			}
		}
	}
}

echo '<div style="float:right;"><fieldset><legend style="font-weight:bold;">Add drivers</legend>';
$q_results = mysqli_query($f1db,
	"SELECT LEFT(first, 1) AS first, last, q1, q2, q3, entr_no
	FROM f1_q AS q
	INNER JOIN f1_race as race
	ON q.entr_no = race.no
	INNER JOIN driver
	ON race.driver = driver.no
	WHERE race.rnd = $race_no
	ORDER BY place = 0, place, q1 = 0, q2 = 0, q3 = 0, last, first"
);
$entr = array();
if (mysqli_num_rows($q_results) > 0) {
	while ($row = mysqli_fetch_array($q_results)) {
		$entr_no = $row['entr_no'];
		$entr[$entr_no]['name'] = $row['first'].' '.$row['last'];
		$entr[$entr_no]['q1'] = $row['q1'] != 0 ? 1 : 0;
		$entr[$entr_no]['q2'] = $row['q2'] != 0 ? 1 : 0;
		$entr[$entr_no]['q3'] = $row['q3'] != 0 ? 1 : 0;
	}
}
$entrants = mysqli_query($f1db,
	"SELECT LEFT(first, 1) AS first, last, race.no AS entr_no
	FROM f1_race AS race
	INNER JOIN driver
	ON race.driver = driver.no
	WHERE race.rnd = $race_no
	ORDER BY last, first"
);
if (mysqli_num_rows($entrants) > 0) {
	while ($row = mysqli_fetch_array($entrants)) {
		$entr_no = $row['entr_no'];
		if (!isset($entr[$entr_no])) {
			$entr[$entr_no]['name'] = $row['first'].' '.$row['last'];
			$entr[$entr_no]['q1'] = 0;
			$entr[$entr_no]['q2'] = 0;
			$entr[$entr_no]['q3'] = 0;
		}
	}
}

// Kiválasztó kiírása
echo '<form method="post">';
echo '<table>';
echo '<tr>
	<th></th>
	<th>Q1</th>
	<th>Q2</th>
	<th>Q3</th>
</tr>';
foreach ($entr as $entr_no => $entr) {
	echo '<tr>';
	// Entr_no (hidden)
	echo '<input type="hidden" name="entr_no[]" value="'.$entr_no.'">';
	echo '<td>'.$entr['name'].'</td>';
	// q1
	$checked = $entr['q1'] ? ' checked' : '';
	echo '<td><input type="checkbox" name="q1[]" value="'.$entr_no.'"'.$checked.'></td>';
	// q2
	$checked = $entr['q2'] ? ' checked' : '';
	echo '<td><input type="checkbox" name="q2[]" value="'.$entr_no.'"'.$checked.'></td>';
	// q3
	$checked = $entr['q3'] ? ' checked' : '';
	echo '<td><input type="checkbox" name="q3[]" value="'.$entr_no.'"'.$checked.'></td>';
	echo '</tr>';
}
echo '</table>';
echo '<input type="submit" name="save_adder" value="Save">';
echo '</form>';
	
echo '</fieldset></div>';
//vardump($entr);

// Fejléc
echo '<h1 class="title">';
echo '<a href="/admin/race/'.($race_no-1).'/results/qualifying">&lt;</a> ';
echo $race_yr.' '.$race_name.' GP Qualifying';
echo ' <a href="/admin/race/'.($race_no+1).'/results/qualifying">&gt;</a> ';
echo '</h1>';

echo '<p><form method="post">'; // Itt kezdődik a form, hogy egy sorban maradjon
echo '<b>Back</b>: ';
echo '<a href="/admin/race/'.$race_no.'/results">Results</a> | ';
echo '<a href="/admin/race/'.$race_no.'">This race</a> | ';
echo '<a href="/admin/race">Races</a> | ';
echo '<a href="/admin/race/'.$race_no.'/results/qualifying/fastadd" style="font-weight:bold;">Fast- add</a> | ';

// Shown
{if (isset($_POST['show_event'])) {
	$ev_no = $_POST['ev_no'];
	mysqli_query($f1db,
		"UPDATE f1_gp_schedule
		SET shown = 1
		WHERE no = $ev_no");
}
if (isset($_POST['hide_event'])) {
	$ev_no = $_POST['ev_no'];
	mysqli_query($f1db,
		"UPDATE f1_gp_schedule
		SET shown = 0
		WHERE no = $ev_no");
}

// Gomb
$shown = mysqli_query($f1db,
	"SELECT no, shown
	FROM f1_gp_schedule
	WHERE rnd = $race_no
	AND type = 'Q'
	LIMIT 1");
	
if (mysqli_num_rows($shown) > 0) {
	$shwn = mysqli_fetch_array($shown);
	$shown = $shwn['shown'];
	$event_no = $shwn['no'];
	
	echo '<input type="hidden" name="ev_no" value="'.$event_no.'">';
	if ($shown == 0) {echo '<input type="submit" name="show_event" value="Hidden - SHOW" style="background-color:red; color:white;">';}
	else {echo '<input type="submit" name="hide_event" value="Shown - HIDE" style="background-color:green; color:white;">';}
	echo '</form>';
	echo '</p>';
}
else {
	echo 'Event can\'t be hide. Create it first in the <a href="/admin/race/'.$race_no.'">schedule tab</a>!</p>';
}}

// 6. Szerkesztő form
$q_results = mysqli_query($f1db,
	"SELECT LEFT(first, 1) AS first, last, place, q1, q2, q3, q.laps, entr_no, dnq, dsq
	FROM f1_q AS q
	INNER JOIN f1_race as race
	ON q.entr_no = race.no
	INNER JOIN driver
	ON race.driver = driver.no
	WHERE race.rnd = $race_no
	ORDER BY place = 0, place"
);
echo '<table>';
echo '<tr>
	<th></th>
	<th></th>
	<th>Q1</th>
	<th>Q2</th>
	<th>Q3</th>
	<th>Laps</th>
	<th>DNQ</th>
	<th>DSQ</th>
</tr>';
echo '<form method="post">';
while ($row = mysqli_fetch_array($q_results)) {
	echo '<tr>';
	// Név
	echo '<td>'.$row['first'].' '.$row['last'].'</td>';
	// Entr_no (hidden)
	echo '<input type="hidden" name="entr_no[]" value="'.$row['entr_no'].'">';
	
	//Place
	echo '<td><input type="number" name="place[]" value="'.$row['place'].'"></td>';
	
	// Q1
	echo '<td>';
	if ($row['q1'] != 0) {
		$time = $row['q1'] != -1 ? $row['q1'] : 0 ;
		echo '<input type="text" name="q1[]" value="'.dectotime($time).'" size="6">';
	}
	else {
		echo '<input type="hidden" name="q1[]" value="">';
	}
	echo '</td>';
	
	// Q2
	echo '<td>';
	if ($row['q2'] != 0) {
		$time = $row['q2'] != -1 ? $row['q2'] : 0 ;
		echo '<input type="text" name="q2[]" value="'.dectotime($time).'" size="6">';
	}
	else {
		echo '<input type="hidden" name="q2[]" value="">';
	}
	echo '</td>';
	
	// Q3
	echo '<td>';
	if ($row['q3'] != 0) {
		$time = $row['q3'] != -1 ? $row['q3'] : 0 ;
		echo '<input type="text" name="q3[]" value="'.dectotime($time).'" size="6">';
	}
	else {
		echo '<input type="hidden" name="q3[]" value="">';
	}
	echo '</td>';
	
	// Laps
	echo '<td><input type="number" name="laps[]" value="'.$row['laps'].'" min="0"></td>';
	
	// DNQ
	$chckd = $row['dnq'] == 1 ? ' checked' : '';
	echo '<td><input type="checkbox" name="dnq[]" value="'.$row['entr_no'].'"'.$chckd.'></td>';
	// DSQ
	$chckd = $row['dsq'] == 1 ? ' checked' : '';
	echo '<td><input type="checkbox" name="dsq[]" value="'.$row['entr_no'].'"'.$chckd.'></td>';
	
	echo '</tr>';
}
echo '</table>';
echo '<input type="submit" name="save_res" value="Save">';
echo '</form>';
?>