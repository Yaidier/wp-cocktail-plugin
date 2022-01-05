<?php
/**
 * @package Wn Cocktails
 */


if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

//Delete drinks in local db
$letters = range('a', 'z');
foreach( $letters as $letter ) {

    delete_option('wn_ckt_drinks_letter_' . $letter);

}


