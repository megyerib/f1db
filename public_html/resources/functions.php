<?php
function sec2time($sec) {
    $min = floor($sec/60);
    return sprintf("%d:%2.3f", $min, $sec-60*$min);
}

function sec2timeNoMin($sec) {
    $min = floor($sec/60);
    if ($min)
        return sprintf("%d:%2.3f", $min, $sec-60*$min);
    else
        return sprintf("%.3f", $sec);
}

function sec2timeHr($sec) {
    $hr  = floor($sec/3600);
    $min = floor(($sec-$hr*3600)/60);
    return sprintf("%s%02d:%2.3f", $hr?"$hr:":"", $min, $sec-3600*$hr-60*$min);
}
?>
