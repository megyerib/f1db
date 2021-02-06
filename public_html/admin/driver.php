<?php
require_once('included/head_admin.php');
$table = 'driver';
$list_order = 'last ASC';
$name_display = 'name|first,de,last,sr';
$entry_link = '/admin/driver/|id';
$key_field = 'id';
$key_length = 8;
$backlink = '/admin/driver';
$list_parameters=
'title Drivers
separated_by_letters 1
new_link /admin/newdriver';
$entry_parameters =
'title name|first,de,last,sr
back_link /admin/driver';
$fields = 'label,class=input,inside=Name
input,type=text,name=first,style=width:90px;
input,type=text,name=de,style=width:30px;
input,type=text,name=last,style=width:90px;
input,type=text,name=sr,style=width:30px;
br
label,class=input,inside=Nationality
dropdown,type=nationality,name=country
br
br
label,class=input,inside=Born
input,type=date,name=birth
input,type=text,name=birthplace
br
label,class=input,inside=Died
input,type=date,name=death
input,type=text,name=deathplace
br
br
label,class=input,inside=Gender
dropdown,type=gender,name=gender
br
br
label,class=input,inside=Indy 500 only
input,type=checkbox,name=i500
image,folder=/img/driver/,ext=jpg JPG,style=width:200px;,w=300,h=0,ratio=1.67,title=Upload image
image,folder=/img/driverLogo/,ext=png PNG,style=width:200px;,w=300,h=0,ratio=1.67,title=Upload logo
social_media,type=D';

simple_editor($table, $list_order, $name_display, $entry_link, $key_field, $fields, $key_length, $backlink, $list_parameters, $entry_parameters);

require_once('included/foot_admin.php');
?>
