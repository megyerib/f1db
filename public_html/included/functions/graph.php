<?php
// Pilóta eredményei
function driver_places($driver_no) {
	global $f1db;
	
	$places = array();
	
	// Legjobb, legrosszabb (célbaérés)
	$minmax = mysqli_query($f1db,
		"SELECT MIN(finish) AS min, MAX(finish) AS max
		FROM f1_race
		WHERE driver = ".$driver_no."
		AND status = 1");
		
	$mm = mysqli_fetch_array($minmax);

		$min = $mm['min'];
		$max = $mm['max'];
	
	$places = array();
	
	for ($i = $min; $i <= $max; $i++) {
		$places[$i] = 0;
	}
		
	// Célba ért
	$allfinish = mysqli_query($f1db,
		"SELECT finish,
		COUNT(*) AS count
		FROM f1_race
		WHERE driver = ".$driver_no."
		AND status = 1
		GROUP BY finish
		ORDER BY finish ASC"
	);
	
	while ($fnshs = mysqli_fetch_array($allfinish)) {
		$places[$fnshs['finish']] = $fnshs['count'];
	}

	// Nem ért célba
	$nonfinish = mysqli_query($f1db,
		"SELECT status,
		COUNT(*) AS count
		FROM f1_race
		WHERE driver = ".$driver_no."
		AND status > 1
		GROUP BY status
		ORDER BY status ASC"); // status a 0 kizárása miatt nagyobb 1-nél
			
	if (mysqli_num_rows($nonfinish) > 0) {
		while ($fnshs = mysqli_fetch_array($nonfinish)) {
			$places[status($fnshs['status'])] = $fnshs['count'];
		}
	}
	return $places;
}

function driver_results_graph($driver_no) {
	// https://google-developers.appspot.com/chart/interactive/docs/gallery/barchart
	$array = driver_places($driver_no);
	$height = 15*count($array)+10*(count($array)+1)+30;
	$rand = rand();
	
	echo '<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable(['."
          ['Place', 'earned', { role: 'annotation' }, { role: 'style' }]";
          foreach ($array as $place => $times) {
			  if (is_numeric($place)) {
				  switch ($place) {
					case 1:
						$color = 'gold';
						break;
					case 2:
						$color = 'silver';
						break;
					case 3:
						$color = '#b87333';
						break;
					default:
						$color = '';
				  }
			  }
			  else {
				  $color = 'gray';
			  }
			  echo ",['".$place."',  ".$times.", ".$times.", '".$color."']";
		  }
        echo "]);

        var options = {
		  legend: { position: \"none\" }
        };
		options.chartArea = { top: '8%', height: \"100%\" };

        var chart = new google.visualization.BarChart(document.getElementById('chart_div_".$rand."'));".'

        chart.draw(data, options);
      }
    </script>
	<div id="chart_div_'.$rand.'" style="width: 900px; height: '.$height.'px;"></div>';
}

// Pilóta időmérős eredményei
function driver_qual_places($driver_no) {
	global $f1db;
	
	$places = array();
	
	// Legjobb, legrosszabb (célbaérés)
	$minmax = mysqli_query($f1db,
		"SELECT MIN(q.place) AS min, MAX(q.place) AS max
		FROM f1_q AS q
		INNER JOIN f1_race AS race
		ON q.entr_no = race.no
		WHERE race.driver = $driver_no
		AND q.dnq = 0
		AND q.dsq = 0");
		
	$mm = mysqli_fetch_array($minmax);

		$min = $mm['min'];
		$max = $mm['max'];
	
	$places = array();
	
	for ($i = $min; $i <= $max; $i++) {
		$places[$i] = 0;
	}
		
	// Célba ért
	$allfinish = mysqli_query($f1db,
		"SELECT q.place,
		COUNT(*) AS count
		FROM f1_q AS q
		INNER JOIN f1_race AS race
		ON q.entr_no = race.no
		WHERE race.driver = $driver_no
		AND q.dnq = 0
		AND q.dsq = 0
		GROUP BY q.place
		ORDER BY q.place ASC"
	);
	
	while ($fnshs = mysqli_fetch_array($allfinish)) {
		$places[$fnshs['place']] = $fnshs['count'];
	}

	/*// Nem ért célba
	$nonfinish = mysqli_query($f1db,
		"SELECT status,
		COUNT(*) AS count
		FROM f1_race
		WHERE driver = ".$driver_no."
		AND status > 1
		GROUP BY status
		ORDER BY status ASC"); // status a 0 kizárása miatt nagyobb 1-nél
			
	if (mysqli_num_rows($nonfinish) > 0) {
		while ($fnshs = mysqli_fetch_array($nonfinish)) {
			$places[status($fnshs['status'])] = $fnshs['count'];
		}
	}*/
	return $places;
}

function driver_qual_results_graph($driver_no) {
	// https://google-developers.appspot.com/chart/interactive/docs/gallery/barchart
	$array = driver_qual_places($driver_no);
	$height = 19*count($array)+12*(count($array)+1)+70;
	$rand = rand();
	
	echo '<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable(['."
          ['Place', 'earned', { role: 'annotation' }, { role: 'style' }]";
          foreach ($array as $place => $times) {
			  if (is_numeric($place)) {
				  switch ($place) {
					case 1:
						$color = 'gold';
						break;
					default:
						$color = '';
				  }
			  }
			  else {
				  $color = 'gray';
			  }
			  echo ",['".$place."',  ".$times.", ".$times.", '".$color."']";
		  }
        echo "]);

        var options = {
          title: 'Qualifying results',
		  legend: { position: \"none\" }
        };
		options.chartArea = { left: '8%', top: '8%', width: \"70%\", height: \"70%\" };

        var chart = new google.visualization.BarChart(document.getElementById('chart_div_".$rand."'));".'

        chart.draw(data, options);
      }
    </script>
	<div id="chart_div_'.$rand.'" style="width: 900px; height: '.$height.'px;"></div>';
}

/* Ötletek

https://google-developers.appspot.com/chart/interactive/docs/gallery/geochart

*/
?>