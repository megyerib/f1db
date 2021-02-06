<?php
require_once('included/head.php');
echo '<h1 style="margin-top:0px;">'.$lang['countries'].'</h1>';

$countries = mysqli_query($f1db,
	"SELECT *
	FROM country
	WHERE country != ''");
	
while ($row = mysqli_fetch_array($countries)) {
	echo flag($row['gp']).country_link($row['gp'], $row['country']).'</br>';
}

/*function small_map($code, $region) {
	global $f1db;
	$rand = rand();
echo '<script type="text/javascript">
      google.load("visualization", "1", {packages:["geochart"]});
      google.setOnLoadCallback(drawRegionsMap);

      function drawRegionsMap() {

        var data = google.visualization.arrayToDataTable(['."
          ['Country', 'Drivers']";
          $cnt = mysqli_query($f1db,
			"SELECT country.country, COUNT(*) as cnt
			FROM driver
			INNER JOIN country
			ON driver.country = country.gp
			WHERE i500 = 0
			AND country.region = '$code'
			GROUP BY country"
		  );
		  while ($row = mysqli_fetch_array($cnt)) {
			  echo ",\n['".$row['country']."', ".$row['cnt']."]";
		  }
    echo"]);

        var options = {
			region: '".$region."',
			colorAxis: {colors: ['#FFFF00', '#FF0000']}
		};
		
		options.colorAxis.maxValue = 60;

        var chart = new google.visualization.GeoChart(document.getElementById('regions_div_".$rand."'));

        chart.draw(data, options);
      }
    </script>".'
    <div id="regions_div_'.$rand.'" style="width: 300px; height: 300px; margin:auto;"></div>';
}

$countries = array(
	'EU' => array('name' => 'Europe',           'countries' => array(), 'region' => '150'),
	'NA' => array('name' => 'Northern America', 'countries' => array(), 'region' => '021'),
	'SA' => array('name' => 'Southern America', 'countries' => array(), 'region' => '005'),
	'AS' => array('name' => 'Asia',             'countries' => array(), 'region' => '142'),
	'OC' => array('name' => 'Oceania',          'countries' => array(), 'region' => '009'),
	'AF' => array('name' => 'Africa',           'countries' => array(), 'region' => '002')
);

$c_query = mysqli_query($f1db,
	"SELECT *
	FROM country
	WHERE country != ''
	ORDER BY country ASC");
	
while ($row = mysqli_fetch_array($c_query)) {
	$countries[$row['region']]['countries'][$row['gp']] = $row['country'];
	//echo flag($row['gp']).country_link($row['gp'], $row['country']).'</br>';
}

foreach ($countries as $reg => $region) {
	echo '<div style="overflow:auto;">';
	//echo '<div style="float:right; width:340px; height:340px;">';
	//small_map($reg, $region['region']);
	//echo '</div>';
	echo '<h2>'.$region['name'].'</h2>';
	foreach ($region['countries'] as $code => $cname) {
		echo flag($code).country_link($code, $cname).'</br>';
	}
	echo '</div>';
}*/

require_once('included/foot.php');
?>