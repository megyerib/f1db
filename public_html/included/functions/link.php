<?php
////////////
// LINKEK //
////////////
// Zászló linkkel
function flag($country) {
	return '<a href="/country/'.$country.'"><img src="/images/flag/icon/'.$country.'.png" class="flagicon" width="22" height="14" alt="'.$country.'"></a>';
}

// Pilóta link
function driver_link($id, $name) {
	return '<a href="/driver/'.$id.'">'.$name.'</a>';
}
// Szezon
function season_link($yr) {
	return '<a href="/f1/'.$yr.'">'.$yr.'</a>';
}
// Nagydíj
function gp_link($gp, $text) {
	return '<a href="/f1/'.$gp.'">'.$text.'</a>';
}
// Futam
function race_link($yr, $gp, $text) {
	return '<a href="/f1/'.$yr.'/'.$gp.'">'.$text.'</a>';
}
// Csapat
function team_link($teamid, $name) {
	return '<a href="/team/'.$teamid.'">'.$name.'</a>';
}
// Ország
function country_link($id, $name) {
	return '<a href="/country/'.$id.'">'.$name.'</a>';
}
// Motorgyártó
function engine_cons_link($id, $name) {
	return '<a href="/engine/'.$id.'">'.$name.'</a>';
}
// Kasztnigyártó
function chassis_cons_link($id, $name) {
	return '<a href="/chassis/'.$id.'">'.$name.'</a>';
}
// Gumi
function tyre_link($id, $name) {
	return '<a href="/tyre/'.$id.'">'.$name.'</a>';
}
// Motor
function engine_link($cons, $no, $name) {
	return '<a href="/engine/'.$cons.'/'.$no.'">'.$name.'</a>';
}
// Kasztni
function chassis_link($cons, $no, $name) {
	return '<a href="/chassis/'.$cons.'/'.$no.'">'.$name.'</a>';
}
// Pálya
function circuit_link($circuit, $name) {
	return '<a href="/circuit/'.$circuit.'">'.$name.'</a>';
}
// Teszt
function test_link($year, $no, $name) {
	return '<a href="/f1/test/'.$year.'/'.$no.'">'.$name.'</a>';
}
?>