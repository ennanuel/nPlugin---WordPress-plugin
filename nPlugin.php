<?php

/**
 * Plugin Name: nPlugin
 * Plugin URI: http://google.com
 * Description: Handles submissions made in your site.
 * Version: 1.0.0
 * Text Domain: nPlugin
 * Author: Emmanuel Ezema
 * Author URI: http://google.com
*/

//Exit if accessed directly

if(!defined('ABSPATH')) {
    echo "You shouldn't be here";
}

if(!class_exists("NPlugin")) {
    
    class NPlugin {

        function __construct()
        {
            define('MY_PLUGIN_PATH', plugin_dir_path(__FILE__));
            
            define('MY_PLUGIN_URL', plugin_dir_url(__FILE__));

            require_once(MY_PLUGIN_PATH .'/vendor/autoload.php');
        }

        public function initialize()
        {
            include_once MY_PLUGIN_PATH .'includes/utilities.php';

            include_once MY_PLUGIN_PATH .'includes/options-page.php';

            include_once MY_PLUGIN_PATH .'includes/form-fields.php';
        }

    }

    $nPlugin = new NPlugin;

    $nPlugin->initialize();

}