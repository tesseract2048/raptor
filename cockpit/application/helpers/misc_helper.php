<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function make_dict($rows, $field = 'id') {
    $dict = array();
    foreach ($rows as $row) {
        $dict[$row[$field]] = $row;
    }
    return $dict;
}

/* End of file */