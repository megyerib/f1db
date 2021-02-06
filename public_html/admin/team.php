<?php
require_once('included/head_admin.php');
$table = 'team';
$list_order = "fullname";
$name_display = 'fullname';
$entry_link = '/admin/team/|id';
$key_field = 'id';
$key_length = 8;
$backlink = '/admin/team';
$list_parameters=
'title Teams
separated_by_letters 1
new_link /admin/newteam';
$entry_parameters =
'title fullname
back_link /admin/team';
$fields = 'label,class=input,inside=Name
input,type=text,name=fullname
br
label,class=input,inside=Long name
input,type=text,name=longname
br
label,class=input,inside=Nationality
dropdown,type=nationality,name=country
br
br
input,type=checkbox,name=isteam
label,inside=Team
br
input,type=checkbox,name=entrant
label,inside=Entrant
br
input,type=checkbox,name=chassis
label,inside=Chassis constructor
br
input,type=checkbox,name=engine
label,inside=Engine constructor
image,folder=/img/team/,ext=png PNG,style=width:200px;,w=300,h=0,ratio=0,title=Upload logo
image,folder=/img/team/icon,ext=png PNG,style=width:128px; color:black;,w=128,h=128,ratio=1,title=Upload icon
social_media,type=T';

simple_editor($table, $list_order, $name_display, $entry_link, $key_field, $fields, $key_length, $backlink, $list_parameters, $entry_parameters);
require_once('included/foot_admin.php');
?>
