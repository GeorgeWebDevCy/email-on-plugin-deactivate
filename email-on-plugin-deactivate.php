<?php
/*
Plugin Name: Email on Plugin Deactivate
Description: Sends an email to specified recipients when any plugin is deactivated.
Version: 1.0
Author: George Nicolaou
*/

// Add a settings link to the plugin on the Plugins page
function eopd_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=email-on-plugin-deactivate">' . __('Settings') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'eopd_settings_link');

// Create the settings page
function eopd_settings_page() {
    add_options_page(
        'Email on Plugin Deactivate',
        'Email on Plugin Deactivate',
        'manage_options',
        'email-on-plugin-deactivate',
        'eopd_settings_page_html'
    );
}
add_action('admin_menu', 'eopd_settings_page');

// Render the settings page HTML
function eopd_settings_page_html() {
    ?>
    <div class="wrap">
        <h1>Email on Plugin Deactivate Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('eopd_settings');
            do_settings_sections('eopd_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register the settings
function eopd_register_settings() {
    register_setting('eopd_settings', 'eopd_recipients');
    add_settings_section(
        'eopd_section',
        __('Settings', 'email-on-plugin-deactivate'),
        'eopd_section_callback',
        'eopd_settings'
    );
    add_settings_field(
        'eopd_recipients',
        __('Email Recipients', 'email-on-plugin-deactivate'),
        'eopd_recipients_callback',
        'eopd_settings',
        'eopd_section'
    );
}
add_action('admin_init', 'eopd_register_settings');

function eopd_section_callback() {
    echo __('Enter the email addresses of the recipients, separated by commas.', 'email-on-plugin-deactivate');
}

function eopd_recipients_callback() {
    $recipients = get_option('eopd_recipients');
    echo '<input type="text" name="eopd_recipients" value="' . esc_attr($recipients) . '" size="50">';
}

// Function to send the email when any plugin is deactivated
function eopd_send_email($plugin, $network_deactivating) {
    $recipients = get_option('eopd_recipients');
    if ($recipients) {
        $recipients_array = explode(',', $recipients);
        $subject = 'Plugin Deactivated';
        $message = 'The plugin ' . $plugin . ' has been deactivated.';
        foreach ($recipients_array as $recipient) {
            wp_mail(trim($recipient), $subject, $message);
        }
    }
}
add_action('deactivated_plugin', 'eopd_send_email', 10, 2);
