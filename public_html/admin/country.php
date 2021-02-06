<?php
require_once('included/head_admin.php');
$table = 'country';
$list_order = "(country = ''), country, name ASC";
$name_display = 'countryname|country,name';
function countryname($ctry, $gpname) {
	if (!empty($ctry)) {
		return $ctry;
	}
	return $gpname.' GP';
}
$entry_link = '/admin/country/|gp';
$key_field = 'gp';
$key_length = 3;
$backlink = '/admin/country';
$list_parameters=
'title Countries
new_link /admin/newcountry';
$entry_parameters =
'title countryname2|country,name,gp
back_link /admin/country';
function countryname2($ctry, $gpname, $gp) {
	if (!empty($ctry)) {
		return $ctry.' ('.$gp.')';
	}
	return $gpname.' GP ('.$gp.')';
}
$fields = 'label,class=input,inside=Country name
input,type=text,name=country
br
label,class=input,inside=GP name
input,type=text,name=name
br
label,class=input,inside=Region
dropdown,type=region,name=region
image,folder=/img/flag/icon/,ext=png,style=width:22px; height:14px;,w=22,h=14,ratio=1,title=Upload small flag
image,folder=/img/flag/big/,ext=png,style=width:200px;,w=300,h=0,ratio=1,title=Upload big flag';

simple_editor($table, $list_order, $name_display, $entry_link, $key_field, $fields, $key_length, $backlink, $list_parameters, $entry_parameters);
require_once('included/foot_admin.php');
?>
