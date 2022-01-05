<?php

/**
 * @package Wn Cocktails
 */
/*
Plugin Name: WN Cocktails
Plugin URI: https://wirenomads.com
Description: 
Author: Yaidier Perez
Version: 1.0
Author URI: 
License: GPLv2 or later
*/
/*
Copyright (C) 2020  Yaidier Perez

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/
if (!defined('ABSPATH')) {
    exit;
}

define('WN_COCKTAILS_DIR', __DIR__);
define('WN_COCKTAILS_URL', plugin_dir_url(__FILE__));


class WnCocktails
{
    public  $plugin_name;
    function __construct()
    {

        $this->plugin_name = plugin_basename(__FILE__);

    }

    function register()
    {

        add_action('wp_ajax_nopriv_get_letter_cocktails', array($this, 'get_all_cocktails'));
        add_action('wp_ajax_get_letter_cocktails', array($this, 'get_all_cocktails'));

        add_action('wp_ajax_nopriv_get_single_letter_cocktails', array($this, 'get_single_cocktails'));
        add_action('wp_ajax_get_single_letter_cocktails', array($this, 'get_single_cocktails'));

        add_action('admin_menu', array($this, 'add_admin_pages'));
        add_action('wp_enqueue_scripts', array($this, 'load_fe_scripts'));

        add_shortcode('cocktail', array($this, 'cocktail_call'));

    }

    //Check if external API need to be called again
    function check_in_refresh_rate() {

        $minutes_to_add = get_option( 'wn_ckt_refresh_rate', 0 );
        $last_api_call = get_option( 'wn_ckt_options_last_call', false);

        if( !$last_api_call ) return true;

        $current_time = new DateTime('NOW');

        $last_api_call->add(new DateInterval('PT' . $minutes_to_add . 'M'));

        if($current_time >= $last_api_call) {

            return true;

        }
        else {

            return false;

        }
    }

    //Connects with external API
    function connect_rest_api($letter){

        $response = wp_remote_get( 'https://www.thecocktaildb.com/api/json/v1/1/search.php?f=' . $letter );
    
        if ( !is_array( $response ) && is_wp_error( $response ) ) {

            return $response;

        }
        
        $body = json_decode($response['body']); 
        return $body->drinks;       

    }

    //Get all cocktails
    function get_all_cocktails() {

        $letters = range('a', 'z');
        $all_drinks = [];

        //Cehck if the API was called before the refresh rate [get_option( 'wn_ckt_refresh_rate' )]
        if (!$this->check_in_refresh_rate()) {

            //Retirieve a local copy of the data
            foreach( $letters as $letter ) {

                $cocktails = get_option('wn_ckt_drinks_letter_' . $letter, false);
                $all_drinks[$letter] = $cocktails; 

            }

        }
        else {

            //Delete drinks in local db
            foreach( $letters as $letter ) {

                delete_option('wn_ckt_drinks_letter_' . $letter);

            }

            foreach ($letters as $letter) {

                $cocktails = $this->connect_rest_api($letter);

                if (is_wp_error($cocktails)) {

                    wp_send_json($cocktails);

                }

                $all_drinks[$letter] = $cocktails;
                update_option('wn_ckt_drinks_letter_' . $letter, $all_drinks[$letter]);
                usleep( 1 * 1000 );

            }

            //Register last time the API was called
            update_option('wn_ckt_options_last_call', new DateTime('NOW'));
            
        }

        wp_send_json($all_drinks);

    }

    function get_single_cocktails() {

        $letter  = isset( $_POST['letter'] ) ? $_POST['letter'] : false;

        //Retirieve a local copy of the data
        $cocktails = get_option('wn_ckt_drinks_letter_' . $letter, false);        

        //If not local copy then call the External API 
        if ($cocktails == false) {

            $cocktails = $this->connect_rest_api($letter);

        }

        wp_send_json($cocktails);
        
    }

    //Draws the shortcode
    function cocktail_call() {

        if (!is_admin()) {   

            wp_enqueue_style('wn_ckt_output_styles');
            wp_enqueue_script('wn_ckt_output_script');            
            wp_localize_script('wn_ckt_output_script', 'the_ajax_script', array('ajaxurl' => admin_url('admin-ajax.php')));
            
            $file = strip_tags(WN_COCKTAILS_DIR . '/templates/output.php');
            ob_start();
            include $file;
            $buffer = ob_get_clean();
            return $buffer;

        }
        
    }

    function load_fe_scripts() {
        
        wp_register_style('wn_ckt_output_styles', WN_COCKTAILS_URL . 'assets/css/output.css', array(), current_time('mysql'));
        wp_register_script('wn_ckt_output_script', WN_COCKTAILS_URL . 'assets/scripts/output.js', array('jquery'), current_time('mysql'), true);
        
    }

    public function add_admin_pages() { 

        add_menu_page(

            'WN Cocktails Settings',
            'WN Cocktails',
            'manage_options',
            'wn_cocktails',
            array($this, 'admin_index'),
            WN_COCKTAILS_URL . 'assets/icons/cocktail-solid.svg',
            110

        );

        add_action('admin_init', array($this, 'settings_page'));
        
    }

    function settings_page() {

        register_setting('wn-ckt-settings-group', 'wn_ckt_load_images');
        register_setting('wn-ckt-settings-group', 'wn_ckt_refresh_rate');
        add_settings_section('wn-ckt-options', 'General Options', array($this, 'generalOptions'), 'wn_cocktails');
        add_settings_field('load-images-field', 'Display Images', array($this, 'load_image_field'), 'wn_cocktails', 'wn-ckt-options');
        add_settings_field('refresh-rate-field', 'Refresh Database Call', array($this, 'refresh_db_call'), 'wn_cocktails', 'wn-ckt-options');

    }

    function generalOptions() {
        
    }

    function load_image_field() {

        $value = get_option( 'wn_ckt_load_images' );
        $html = '<input type="checkbox" name="wn_ckt_load_images" value="1"' . checked( 1, $value, false ) . '/>';
        echo $html;

    }
    function refresh_db_call() {

        $value = get_option( 'wn_ckt_refresh_rate' );
        $html = '<input type="number" name="wn_ckt_refresh_rate" value="' . $value . '"/>';
        echo $html;

    }

    public function admin_index() {

        require_once plugin_dir_path(__FILE__) . '/templates/admin.php';

    }


    function activate() {

    }

    function deactivate() {

    }

}

$wn_cocktails = new WnCocktails();
$wn_cocktails->register();

//activation
register_activation_hook(__FILE__, array($wn_cocktails, 'activate'));
//deactivation
register_deactivation_hook(__FILE__, array($wn_cocktails, 'deactivate'));
//uninstall
//handled by uninstall file uninstall.php
