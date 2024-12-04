<?php
function countSMSParts($text, $isGSM7) {
    if ($isGSM7) {
        $limit = 160;
        $part_limit = 153;
    } else {
        $limit = 70;
        $part_limit = 67;
    }

    $length = mb_strlen($text, 'UTF-8');

    if ($length <= $limit) {
        return 1;
    }

    return ceil($length / $part_limit);
}

function getSMSTime() {
    return date('y;m;d;H;i;s;+0');
}
