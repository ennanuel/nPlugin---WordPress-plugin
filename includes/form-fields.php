<?php


if(!defined('ABSPATH')) {
    echo "You shouldn't be here";
}


add_shortcode('contact', 'show_form_fields');

add_action('rest_api_init', 'create_rest_endpoint');

add_action('init', 'create_submissions_page');

add_action('add_meta_boxes', 'create_meta_box');

add_filter('manage_submission_posts_columns', 'custom_submission_columns');

add_action('manage_submission_posts_custom_column', 'fill_submission_columns', 10, 2);

add_action('admin_init', 'setup_search');

add_action('wp_enqueue_scripts', 'nPlugin_enqueue_scripts');


function nPlugin_enqueue_scripts()
{
    wp_enqueue_style('nPlugin-style', MY_PLUGIN_URL ."css/style.css");
}


function setup_search()
{
    global $typenow;

    if($typenow == 'submission')
    {
        add_filter('posts_search', 'submission_search_override', 10, 2);
    }
}

function submission_search_override($search, $query)
{

    global $wpdb;

    if($query->is_main_query() && !empty($query->query['s']))
    {
        $sql = "
            or exists (
                select * from " .$wpdb->postmeta ." where post_id = " .$wpdb->posts .".ID
                and meta_key in ('name', 'email', 'phone')
                and meta_value like %s
            )";
        $like = '%' .$wpdb->esc_like($query->query['s']) .'%';
        $search = preg_replace("#\(" .$wpdb->posts .".post_title LIKE [^)]+\)\K#",
                $wpdb->prepare($sql, $like), $search);
    }

    return $search;

}

function fill_submission_columns ($column, $post_id)
{

    switch($column)
    {
        case 'name':
            echo esc_html(get_post_meta($post_id, 'name', true));
        break;

        case 'email':
            echo esc_html(get_post_meta($post_id, 'email', true));
        break;

        case 'phone':
            echo esc_html(get_post_meta($post_id, 'phone', true));
        break;

        case 'message':
            echo esc_html(get_post_meta($post_id, 'message', true));
        break;
    }

}

function custom_submission_columns ($columns)
{

    $columns = array(
        'cb' => $columns['cb'],
        'name' => __('Name', 'nPlugin'),
        'email' => __('Email', 'nPlugin'),
        'phone' => __('Phone', 'nPlugin'),
        'message' => __('Message', 'nPlugin')
    );

    return $columns;

}

function show_form_fields()
{
    include MY_PLUGIN_PATH .'/includes/templates/form.php';
}

function create_rest_endpoint()
{
    register_rest_route('v1/form', 'submit', array(
        'methods' => 'POST',
        'callback' => 'handle_enquiry'
    ));
}

function handle_enquiry($data)
{
    $params = $data->get_params();

    $field_name = sanitize_text_field( $params['name'] );
    $field_email = sanitize_email( $params['email'] );
    $field_phone = sanitize_text_field( $params['phone'] );
    $field_message = sanitize_textarea_field( $params['message'] );
    
    if( !wp_verify_nonce($params['_wpnonce'], 'wp_rest') )
    {
        return new wp_rest_response( 'Message not sent', 422 );
    }

    unset($params['_wpnonce']);
    unset($params['_wp_http_referer']);

    $admin_email = get_bloginfo('admin_email');
    $admin_name = get_bloginfo('name');

    $recipient_email = get_plugin_options('n_plugin_recipients');

    if(!$recipient_email) 
    {
        $recipient_email = $admin_email;
    }

    $headers = [];
    $headers[] = 'from: ' .$admin_name .' <' .$admin_email .'>';
    $headers[] = 'reply-to: ' .$params['name'] .'<' .$field_email .'>';
    $headers[] = 'Content-Type: text/html';

    $subject = 'New enquiry from ' .$params['name'];

    $message = '';
    $message .= '<h1>Message has been sent from ' .$params['name'] .'</h1>';

    $post_arr = [
        'post_title' => $params['name'],
        'post_type' => 'submission',
        'post_status' => 'publish'
    ];

    $post_id = wp_insert_post($post_arr);


    foreach($params as $label => $value) 
    {
        $label = sanitize_text_field($label);

        switch ($label)
        {
            case 'message': 
                $value = sanitize_textarea_field($value);
                break;
            case 'email':
                $value = sanitize_email($value);
                break;
            default:
                $value = sanitize_text_field($value);
                break;
        }

        $message .= '<strong>' .ucfirst($label) .'</strong>: ' .$value .'<br />';

        add_post_meta($post_id, $label, $value);
    }

    wp_mail($recipient_email, $subject, $message, $headers);

    $confirmation_message = "Message was sent successfully.";

    if(get_plugin_options('n_plugin_message'))
    {
        $confirmation_message = str_replace('{name}', $field_name, get_plugin_options('n_plugin_message'));
    }

    return new wp_rest_response( $confirmation_message, 200 );
}

function create_submissions_page()
{
    $args = array(
        'public' => true,
        'has_archive' => true,
        'menu_position' => 30,
        'publicly_queryable' => false,
        'labels' => [
            'name' => 'Submissions',
            'singular_name' => 'Submission',
            'edit_item' => 'View Submission'
        ],
        'supports' => false,
        'capability_type' => 'post',
        'capabilities' => [
            'create_posts' => false
        ],
        'map_meta_cap' => true
    );

    register_post_type('submission', $args);
}

function create_meta_box() 
{

    add_meta_box('custom_contact_form', 'Submission', 'display_submission');

}

function display_submission()
{

    $postmetas = get_post_meta( get_the_ID() );
    unset($postmetas['_edit_lock']);

    echo '<ul>';

    foreach($postmetas as $key => $value) 
    {
        echo '<li><strong>' .ucfirst($key) .'</strong>:<br />' .esc_html($value[0]) .'</li>';
    }

    echo '</ul>';

}