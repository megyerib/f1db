<?php
////////////
// LINKEK //
////////////
// Zászló linkkel
function flag($country) {
	if (!empty($country)) {
		return "\n".'<a href="/country/'.$country.'"><img src="/img/flag/icon/'.$country.'.png" class="flagicon" style="width:22px; height:14px;" alt="'.$country.'"></a>';
	}
	//return '<a href=""><img src="/img/blank.png" class="flagicon" style="width:22px; height:14px;></a>';
	return '<img src="/img/blank.png" class="flagicon" style="width:22px; height:14px;">';
}

// Pilóta link
function linkDriver($id, $name) {
	return '<a href="/driver/'.$id.'">'.$name.'</a>';
}
// Szezon
function linkSeason($yr) {
	return '<a href="/f1/'.$yr.'">'.$yr.'</a>';
}
// Nagydíj
function linkGP($gp, $text) {
	return '<a href="/f1/'.$gp.'">'.$text.'</a>';
}
// Futam
function linkRace($yr, $gp, $text) {
	return '<a href="/f1/'.$yr.'/'.$gp.'">'.$text.'</a>';
}
// Csapat
function linkTeam($teamid, $name) {
	return '<a href="/team/'.$teamid.'">'.$name.'</a>';
}
// Ország
function linkCountry($id, $name) {
	return '<a href="/country/'.$id.'">'.$name.'</a>';
}
// Motorgyártó
function linkEngineCons($id, $name) {
	return '<a href="/engine/'.$id.'">'.$name.'</a>';
}
// Kasztnigyártó
function linkChassisCons($id, $name) {
	return '<a href="/chassis/'.$id.'">'.$name.'</a>';
}
// Gumi
function linkTyre($id, $name) {
	return '<a href="/tyre/'.$id.'">'.$name.'</a>';
}
// Motor
function linkEngine($cons, $no, $name) {
	return '<a href="/engine/'.$cons.'/'.$no.'">'.$name.'</a>';
}
// Kasztni
function linkChassis($cons, $no, $name) {
	return '<a href="/chassis/'.$cons.'/'.$no.'">'.$name.'</a>';
}
// Pálya
function linkCircuit($circuit, $name) {
	return '<a href="/circuit/'.$circuit.'">'.$name.'</a>';
}
// Teszt
function linkTest($year, $no, $name) {
	return '<a href="/f1/test/'.$year.'/'.$no.'">'.$name.'</a>';
}
?>