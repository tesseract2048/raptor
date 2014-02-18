<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

define('TICKET_NUMBER_MAGIC', 42949);

function new_ticket_number($product) {
    $alpha = mt_rand(0, 99999);
    $beta = mt_rand(0, 99999);
    $chk1 = (($alpha + $beta) ^ TICKET_NUMBER_MAGIC) % 100;
    $chk2 = (($product * $alpha) ^ TICKET_NUMBER_MAGIC) % 100;
    $number = sprintf("T%04d%05d%02d%05d%02d", $product, $alpha, $chk1, $beta, $chk2);
    return $number;
}

function valid_ticket_number($number) {
    if (strlen($number) != 19) {
        return FALSE;
    }
    $prefix = substr($number, 0, 1);
    if ($prefix != 'T') {
        return FALSE;
    }
    $product = substr($number, 1, 4);
    $alpha = intval(substr($number, 5, 5));
    $chk1 = intval(substr($number, 10, 2));
    $beta = intval(substr($number, 12, 5));
    $chk2 = intval(substr($number, 17, 2));
    $real_chk1 = (($alpha + $beta) ^ TICKET_NUMBER_MAGIC) % 100;
    $real_chk2 = (($product * $alpha) ^ TICKET_NUMBER_MAGIC) % 100;
    if ($real_chk1 != $chk1) {
        return FALSE;
    }
    if ($real_chk2 != $chk2) {
        return FALSE;
    }
    return TRUE;
}

/* End of file */