<?php
/**
 * Plugin Name: Login by Criipto
 * Plugin URL: https://docs.criipto.com/wordpress
 * Description: Login by Criipto Verify provides DK NemId login, NO/SE BankId login, FI Tupas, FI Mobiilivarmenne, FI All for all your sites.
 * Version: 1.0.0
 * Author: Criipto
 * Author URI: https://criipto.com
 * Text Domain: wp-criipto
 */
session_start();
require_once 'functions.php';

include('includes/settingsPage.php'); 
$pluginUrl = plugin_dir_url(__FILE__);
$find = array( 'http://', 'https://' );
$replace = '';
$domain = str_replace( $find, $replace, home_url());
$output = str_replace( $find, $replace, $pluginUrl );
$url =  str_replace( $domain, $replace, $output );
define('CRIIPTO_VERIFY_MAIN_PLUGIN_URL', $url);
define('CRIIPTO_VERIFY_CLIENT_SECRET', get_option('criipto-verify-client-secret'));


function criipto_plugin_scripts()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('criipto-scripts', plugin_dir_url( __FILE__ ) . 'js/script.js');
}
add_action('wp_enqueue_scripts', 'criipto_plugin_scripts');
function criipto_admin_scripts()
{
    wp_enqueue_script('criipto-scripts', plugin_dir_url( __FILE__ ) . 'js/admin-script.js');
}
add_action('admin_enqueue_scripts', 'criipto_admin_scripts');
function criipto_plugin_styles()
{
    wp_enqueue_style('criipto-style', plugin_dir_url( __FILE__ ) . 'css/style.css');
}
add_action('wp_enqueue_scripts', 'criipto_plugin_styles');
function criipto_admin_styles()
{
     wp_enqueue_style('criipto-admin-style', plugin_dir_url( __FILE__ ) . 'css/admin-style.css');
}
add_action('admin_enqueue_scripts','criipto_admin_styles');