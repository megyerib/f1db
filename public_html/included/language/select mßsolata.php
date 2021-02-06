<?php
// NYELVVÁLASZTÓ
echo '<form method="post" target="_self" id="lng_select">';
echo '<div class="lng"><ul><li><img src="/lang/'.$nyelv.'.png" style="margin:3px;" alt="'.$nyelv.'"><img src="/images/icon/down.png" style="margin:3px; width:12px;" alt="down"><ul>';
foreach ($lngs as $short => $long) {
	echo '<li><input type="image" src="/lang/'.$short.'.png" name="lng_'.$short.'" alt="'.$long.'"></li>';
}
echo '</ul></li></ul></div>';
echo '</form>';
?>