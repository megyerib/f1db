<?php
require_once('included/head_admin.php');
?>

<!--a href="/admin/">Home</a><br>
<br>
<a href="/admin/races" style="font-weight:bold;">Races</a><br>
<a href="/admin/driver">Drivers</a><br>
<a href="/admin/team">Teams</a><br>
<a href="/admin/chassis">Chassises</a><br>
<a href="/admin/engine">Engines</a><br>
<a href="/admin/circuit">Circuits</a><br>
<a href="/admin/tyre">Tyres</a><br>
<a href="/admin/country">Countries</a><br-->
<?php
$links = array(
	'race'   => 'Races',
	'driver'  => 'Drivers',
	'team'    => 'Teams',
	'chassis' => 'Chassises',
	'engine'  => 'Engines',
	'circuit' => 'Circuits',
	'tyre'    => 'Tyres',
	'country' => 'Countries'
);
$i = 1;
echo '<table style="text-align:center;">';
foreach ($links as $link => $text) {
	if ($i % 4 == 1) {
		echo '<tr>';
	}
	
	echo '<td>';
	echo '<a href="/admin/'.$link.'">';
	echo '<img src="/images/admin/icon/'.$link.'.png"><br>'.$text.'<br><br>';
	echo '</a></td>';
	
	if ($i % 4 == 0) {
		echo '</tr>';
	}
	$i++;
}
echo '</table>';
?>
<br>
<a href="/admin/test">Tests</a><br>
<br>
<a href="/admin/users">Users</a><br>
<a href="/admin/ips">IPs</a><br>
<a href="/admin/messages">Messages</a><br>
<a href="/admin/active">Active</a><br>
<a href="/admin/errors">Errors</a><br>
<a href="/admin/recycle">Recycle bin (images)</a><br>
<br>
<a href="/admin/scorecalc.php">Refresh drivers' standing</a><br>
<a href="/admin/scorecalc_cons.php">Refresh constructors' standing</a><br>
<?php
require_once('included/foot_admin.php');
?>