<?php
function isGSM7($text) {
    $gsm7_chars = '@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !"#$%&\'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ`abcdefghijklmnopqrstuvwxyzäöñüà';
    $gsm7_ext = '^{}\\[~]|€';

    for ($i = 0; $i < mb_strlen($text, 'UTF-8'); $i++) {
        $char = mb_substr($text, $i, 1, 'UTF-8');
        if (mb_strpos($gsm7_chars, $char) === false && mb_strpos($gsm7_ext, $char) === false) {
            return false;
        }
    }
    return true;
}

function textToUnicode($text) {
    $unicode = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $unicode .= sprintf("%04X", ord($text[$i]));
    }
    return $unicode;
}

function textToUnicode16($text) {
    $unicode = '';
    for ($i = 0; $i < mb_strlen($text, 'UTF-8'); $i++) {
        $char = mb_substr($text, $i, 1, 'UTF-8');
        $unicode .= sprintf("%04X", mb_ord($char, 'UTF-8'));
    }
    return $unicode;
}
