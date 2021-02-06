<?php
require_once('included/head_admin.php');
$table = 'tyre';
$list_order = "fullname";
$name_display = 'fullname';
$entry_link = '/admin/tyre/|id';
$key_field = 'id';
$key_length = 2;
$backlink = '/admin/tyre';
$list_parameters=
'title Tyres
new_link /admin/newtyre';
$entry_parameters =
'title fullname
back_link /admin/tyre';
$fields = 'label,class=input,inside=Name
input,type=text,name=fullname
br
label,class=input,inside=Country
dropdown,type=nationality,name=country
image,folder=/img/tyre/,ext=png,style=width:200px;,w=300,h=0,ratio=0,title=Upload logo
social_media,type=TR';

simple_editor($table, $list_order, $name_display, $entry_link, $key_field, $fields, $key_length, $backlink, $list_parameters, $entry_parameters);
require_once('included/foot_admin.php');
?>
