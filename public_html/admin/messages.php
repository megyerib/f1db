<?php
require_once('included/head_admin.php');

if (isset($_GET['mode']) && $_GET['mode'] == 'main') {
	echo '<h1>User messages</h1>';
	
	if (isset($_GET['page'])) {
		$page = $_GET['page'];
	}
	else {
		$page = 1;
	}
	
	$perpage = 10;
	$pg = ($page - 1)*$perpage;
	$msgs = mysqli_query($sdb,
		"SELECT *, LEFT(msg, 50) AS msg
		FROM message
		ORDER BY readed ASC, sent DESC
		LIMIT $pg, $perpage");
	
	echo '<table>';
	while ($row = mysqli_fetch_array($msgs)) {
		$msg = strip_tags($row['msg']);
		
		if ($row['readed'] == 0) {
			$read = ' style="background-color:#CCCCCC; font-weight:bold;"';
		}
		else {
			$read = '';
		}
		
		echo '<tr'.$read.'>';
		echo '<td>'.$row['name'].'</td>';
		echo '<td><b>'.$row['subject'].'</b></td>';
		echo '<td>'.$msg.'</td>';
		echo '<td>'.$row['sent'].'</td>';
		echo '<td><a href="/admin/messages/'.$row['no'].'">View</a></td>';
		echo '</tr>';
	}
	echo '</table>';
	
	$cnt = mysqli_query($sdb,
		"SELECT count(*) AS count
		FROM message");
	$cnt = mysqli_fetch_array($cnt);
	$cnt = $cnt['count'];
	$pages = ceil($cnt / $perpage);
	
	echo '<p>';
	for ($p = 1; $p <= $pages; $p++) {
		if ($page == $p) {echo '<span style="border:1px solid black; padding:3px;"><b>';}
		echo '<a href="/admin/messages/page'.$p.'">'.$p.'</a>';
		if ($page == $p) {echo '</b></span>';}
		if ($p != $pages) {echo ' &middot; ';}
	}
	echo '</p>';
}
if (isset($_GET['message'])) {
	$no = $_GET['message'];
	mysqli_query($f1db,
		"UPDATE message
		SET readed = 1
		WHERE no = $no");
		
	$msg = mysqli_query($sdb,
		"SELECT *
		FROM message
		WHERE no = $no
		LIMIT 1");
	$msg = mysqli_fetch_array($msg);
	
	echo '<h1>User message</h1>';
	echo '<a href="/admin/messages">&lt; Back</a><br>';
	
	echo '<div style="border:1px solid black; padding:25px; margin-top:25px; margin-bottom:25px;">';
	
	echo '<table>';
	echo '<tr><td width="70"><b>From</b></td><td>'.$msg['name'].'</td></tr>';
	echo '<tr><td></td><td><i>'.$msg['mail'].'</i></td></tr>';
	echo '<tr><td><b>Subject</b></td><td><b>'.$msg['subject'].'</b></td></tr>';
	echo '<tr><td><b>Sent</b></td><td>'.$msg['sent'].'</td></tr>';
	echo '</table><hr>';
	
	echo $msg['msg'];
	echo '</div>';
}
require_once('included/foot_admin.php');
?>