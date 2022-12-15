<?php


if(!defined('ABSPATH')) {
    echo "You shouldn't be here";
}

use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action('after_setup_theme', 'load_carbon_fields');
add_action('carbon_fields_register_fields', 'create_options_page');


function load_carbon_fields()
{
    \Carbon_Fields\Carbon_Fields::boot();
}

function create_options_page()
{
    Container::make('theme_options', __( 'nTheme Hero Field' ))
    ->set_page_menu_position(30)
    ->set_icon( 'dashicons-media-text')
    ->add_fields(   array(
        field::make( 'checkbox', 'n_plugin_active', __( 'Active' ) ),

        field::make( 'text', 'n_plugin_recipients', __( 'Recipient Email' ) )
        ->set_attribute( 'placeholder', 'eg. your@gmail.com' )
        ->set_help_text( 'The email that the form is submitted to.' ),

        field::make( 'textarea', 'n_plugin_message', __( 'Confirmation Message' ) )
        ->set_attribute( 'placeholder', 'Email Confirmation Message.' )
        ->set_help_text( 'Type the message you want the submitter to receive.' )
    )   );
}

