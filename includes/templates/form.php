<?php


if(!defined('ABSPATH')) {
    echo "You shouldn't be here";
}

if(get_plugin_options('n_plugin_active')) :

?>

<div id="form_success" style="background: green; color: white;"></div>
<div id="form_error" style="background: red; color: white;"></div>

<form id="enquiry_form">

    <?php wp_nonce_field('wp_rest'); ?>

    <label for="name">Name:</label><br />
    <input type="text" id="name" name="name" /><br /><br />

    <label for="email">Email:</label><br />
    <input type="email" id="email" name="email" /><br /><br />

    <label for="phone">Phone:</label><br />
    <input type="tel" id="phone" name="phone" /><br /><br />

    <label for="Message">Message:</label><br />
    <textarea name="message"></textarea><br /><br />

    <button type="submit">Submit Form</button>

</form>

<script>

    jQuery(document).ready(function($) {
        
        $('#enquiry_form').submit( function (event) {
            event.preventDefault()

            const form = $(this)

            $.ajax({
                type: "POST",
                url: "<?php echo get_rest_url(null, 'v1/form/submit'); ?>",
                data: form.serialize(),
                success: function(res) {
                    form.hide();

                    $('#form_success').html(res).fadeIn();
                },
                error: function() {
                    $('#form_error').html('There was an error submitting your form.').fadeIn();
                }
            })
        })

    })

</script>

<?php

else: echo "The plugin is not active!";

endif;

?>