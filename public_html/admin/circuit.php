<?php
require_once('included/head_admin.php');
$table = 'circuit';
$list_order = "fullname";
$name_display = 'fullname';
$entry_link = '/admin/circuit/|id';
$key_field = 'id';
$key_length = 8;
$backlink = '/admin/circuit';
$list_parameters=
'title Circuits
separated_by_letters 1
new_link /admin/newcircuit';
$entry_parameters =
'title fullname
back_link /admin/circuit';
$fields = 'label,class=input,inside=Name
input,type=text,name=fullname
br
label,class=input,inside=Short name
input,type=text,name=shortname
br
br
label,class=input,inside=Country
dropdown,type=country,name=country
br
label,class=input,inside=City
input,type=text,name=place
br
br
label,class=input,inside=Latitude
input,type=number,name=lat,step=0.0000001,class=coord
br
label,class=input,inside=Longitude
input,type=number,name=lon,step=0.0000001,class=coord
br
image,folder=/img/circuit/,ext=png,style=width:200px;,w=450,h=0,ratio=0,title=Upload circuit diagram
image,folder=/img/circuit_logo/,ext=png,style=width:200px;,w=300,h=0,ratio=0,title=Upload logo
social_media,type=C';

simple_editor($table, $list_order, $name_display, $entry_link, $key_field, $fields, $key_length, $backlink, $list_parameters, $entry_parameters);
require_once('included/foot_admin.php');
?>
