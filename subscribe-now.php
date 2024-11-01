<?php
/*
Plugin Name: Subscribe Now
Plugin URI: https://bitbucket.org/petarvasilev/subscribe-now
Description: A plugin to allow users to subscribe to a mailing list.
Version: 1.3.2
Author: Petar Vasilev
Author URI: https://www.petarvasilev.com/
License: MIT
Text Domain: subscribe-now
Domain Path: /languages
*/

include_once 'widgets/subscribe-widget.php';
include_once 'widgets/unsubscribe-widget.php';

function subscribe_now_config()
{
    return [
        'tableName' => 'wp_sn_subscribers',
        'textDomain' => 'subscribe-now',
    ];
}

/**
 * Run installation stuff on plugin activation
 */
function subscribe_now_install()
{
    global $wpdb;

    $wpdb->get_results("
            CREATE TABLE IF NOT EXISTS `" . subscribe_now_config()['tableName'] . "` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `email` varchar(512) NOT NULL UNIQUE,
              `created_at` datetime NOT NULL,
              `modified_at` datetime NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
}

/**
 * Run uninstallation stuff on plugin deactivation
 */
function subscribe_now_uninstall()
{
    if (get_option('subscribe_now_remove_table') == 1) // if set to delete
    {
        global $wpdb;

        $wpdb->get_results("DROP TABLE " . subscribe_now_config()['tableName'] . ";");

        update_option('subscribe_now_remove_table', 0);
    }
}

/**
 * Registers and load the two widgets
 */
function subscribe_now_load_widgets() {
    register_widget( 'SubscribeNow_Subscribe_Widget' );
    register_widget( 'SubscribeNow_Unsubscribe_Widget' );
}

/**
 * Return all subscribers
 *
 * @return array|null|object
 */
function subscribe_now_get_subscribers()
{
    global $wpdb;
    return $wpdb->get_results('SELECT * FROM ' . subscribe_now_config()['tableName'], ARRAY_A);
}

/**
 * Checks if subscriber already exists in database
 *
 * @return bool
 */
function subscribe_now_check_if_subscriber_exists($email)
{
    global $wpdb;

    $query = $wpdb->prepare('SELECT * FROM ' . subscribe_now_config()['tableName'] . ' WHERE email = %s', $email);
    $result = $wpdb->get_results($query);

    if ($result)
    {
        return true;
    }

    return false;
}

/**
 * Remove subscriber
 *
 * @param $subscriber_id
 * @return false|int
 */
function subscribe_now_remove_subscriber($subscriber_id)
{
    global $wpdb;

    if (preg_match('/^[0-9]+$/', $subscriber_id)) // validate input
    {
        return $wpdb->delete(subscribe_now_config()['tableName'], ['id' => $subscriber_id]);
    }

    return false;
}

/**
 * Returns true if user is admin otherwise false
 *
 * @return bool
 */
function subscribe_now_is_user_admin()
{
    $user = wp_get_current_user();

    if ( in_array( 'administrator', (array) $user->roles ) ) {
        return true;
    }

    return false;
}

/**
 * Turns array into a CSV
 *
 * @param array $array
 * @return null|string
 */
function subscribe_now_array2csv(array &$array)
{
    if (count($array) == 0) {
        return null;
    }
    ob_start();
    $df = fopen("php://output", 'w');
    fputcsv($df, array_keys(reset($array)));
    foreach ($array as $row) {
        fputcsv($df, $row);
    }
    fclose($df);
    return ob_get_clean();
}

/**
 * Sent download headers
 *
 * @param $filename
 */
function subscribe_now_download_send_headers($filename) {
    // disable caching
    $now = gmdate("D, d M Y H:i:s");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

    // force download
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");

    // disposition / encoding on response body
    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");
}

/**
 * Add menu, in the admin area, do display subscribers and settings
 */
function subscribe_now_add_menu()
{
    add_menu_page( esc_html__('Subscribe Now', subscribe_now_config()['textDomain']), esc_html__('Subscribe Now', subscribe_now_config()['textDomain']), 'manage_options', 'subscribe-now', 'subscribe_now_settings_page', 'dashicons-admin-users', 25  );

    add_submenu_page( 'subscribe-now', esc_html__('Subscribers', subscribe_now_config()['textDomain']), esc_html__('Subscribers', subscribe_now_config()['textDomain']), 'manage_options', 'subscribe-now-subscribers', 'subscribe_now_subscribers_page' );
}

/**
 * The subscribers page where all subscribers are listed
 */
function subscribe_now_subscribers_page()
{
    $flashMessage = false;

    if (isset($_POST['subscriber-id']))
    {
        check_admin_referer('subscribe-now-remove-subscriber');

        if (subscribe_now_remove_subscriber($_POST['subscriber-id']))
        {
            $flashMessage = esc_html__('Subscriber successfully removed.', subscribe_now_config()['textDomain']);
        }
        else
        {
            $flashMessage = esc_html__('There was a problem removing the subscriber. Please try again.', subscribe_now_config()['textDomain']);
        }
    }

    $subscribers = subscribe_now_get_subscribers();

    include_once plugin_dir_path(__FILE__) . '/views/subscribers.php';
}

/**
 * The settings page of the plugin
 */
function subscribe_now_settings_page()
{
    if (isset($_POST['remove-table']))
    {
        check_admin_referer('subscribe-now-save-settings'); // check nonce and referrer

        if ($_POST['remove-table'] == 1)
        {
            update_option('subscribe_now_remove_table', 1);
        }
        elseif ($_POST['remove-table'] == 0)
        {
            update_option('subscribe_now_remove_table', 0);
        }
    }

    include_once plugin_dir_path(__FILE__) . '/views/settings.php';
}

/**
 * Export all subscribers as a CSV file given certain things
 * are true like the url is /subscribe-now/export and the
 * current user is administrator
 */
function subscribe_now_export()
{
    if($_SERVER["REQUEST_URI"] == '/subscribe-now/export') {
        if (is_user_logged_in() AND subscribe_now_is_user_admin())
        {
            $subscribers = subscribe_now_get_subscribers();

            if ($subscribers)
            {
                $csv = subscribe_now_array2csv($subscribers);
                subscribe_now_download_send_headers('subscribers.csv');
                echo $csv;
            }
            else
            {
                echo 'There are no subscribers.';
            }

            exit;
        }
    }
}

/**
 * Subscribe a new user
 */
function subscribe_now_subscribe_user() {
    $msg = 'There was a problem with your request. Please try again.';

    if (!isset($_POST['email']) OR !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
    {
        $msg = esc_html__('Valid email address is required.', subscribe_now_config()['textDomain']);
    }
    elseif (subscribe_now_check_if_subscriber_exists(sanitize_email($_POST['email'])))
    {
        $msg = esc_html__('You are already subscribed.', subscribe_now_config()['textDomain']);
    }
    else
    {
        global $wpdb;

        $now = date('Y-m-d H:i:s');
        if ($wpdb->insert(subscribe_now_config()['tableName'],
                [
                'email' => sanitize_email($_POST['email']),
                'created_at' => $now,
                'modified_at' => $now,
                ]))
        {
            $msg = esc_html__('You have been subscribed.', subscribe_now_config()['textDomain']);
        }
    }


    echo json_encode(['message' => $msg]);

    wp_die();
}

/**
 * Unsubscribe a user
 */
function subscribe_now_unsubscribe_user() {
    $msg = esc_html__('There was a problem with your request. Please try again.', subscribe_now_config()['textDomain']);

    if (!isset($_POST['email']) OR !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
    {
        $msg = esc_html__('Valid email address is required.', subscribe_now_config()['textDomain']);
    }
    elseif (!subscribe_now_check_if_subscriber_exists(sanitize_email($_POST['email'])))
    {
        $msg = esc_html__("You weren't subscribed in the first place.", subscribe_now_config()['textDomain']);
    }
    else
    {
        global $wpdb;

        if ($wpdb->delete(subscribe_now_config()['tableName'], ['email' => $_POST['email']]))
        {
            $msg = esc_html__('You have been unsubscribed.', subscribe_now_config()['textDomain']);
        }
    }


    echo json_encode(['message' => $msg]);

    wp_die();
}

function subscribe_now_subscribe_shortcode()
{
    ob_start();
    include plugin_dir_path(__FILE__) . '/views/subscribe-form.php';

    return ob_get_clean();
}

function subscribe_now_unsubscribe_shortcode()
{
    ob_start();
    include plugin_dir_path(__FILE__) . '/views/unsubscribe-form.php';

    return ob_get_clean();
}

/**
 * Make sure jQuery is loaded
 */
function subscribe_now_scripts() {
    wp_enqueue_script( 'subscribe-now-script', plugin_dir_url(__FILE__) . 'js/subscribe-unsubscribe.js', array( 'jquery' ), '1.0.0', true );
}

/**
 * Enqueue main stylesheet
 */
function subscribe_now_styles()
{
    wp_enqueue_style('subscribe-now-main-style', plugin_dir_url(__FILE__) . 'css/main.css',false,'1.0.0');
}

/**
 * Enqueue the admin css stylesheet
 */
function subscribe_now_enqueue_admin_style()
{
    wp_register_style('subscribe-now-admin-style', plugin_dir_url(__FILE__) . '/css/admin.css', false, '1.0.0');
    wp_enqueue_style('subscribe-now-admin-style');
}

register_activation_hook('subscribe-now/subscribe-now.php', 'subscribe_now_install');
register_uninstall_hook('subscribe-now/subscribe-now.php', 'subscribe_now_uninstall');

add_action('admin_menu', 'subscribe_now_add_menu');
add_action('parse_request', 'subscribe_now_export');


add_action( 'widgets_init', 'subscribe_now_load_widgets' );

add_action( 'wp_ajax_nopriv_subscribe_user', 'subscribe_now_subscribe_user' );
add_action( 'wp_ajax_subscribe_user', 'subscribe_now_subscribe_user' );
add_action( 'wp_ajax_unsubscribe_user', 'subscribe_now_unsubscribe_user' );
add_action( 'wp_ajax_nopriv_unsubscribe_user', 'subscribe_now_unsubscribe_user' );

add_shortcode('subscribe-now-subscribe-form', 'subscribe_now_subscribe_shortcode');
add_shortcode('subscribe-now-unsubscribe-form', 'subscribe_now_unsubscribe_shortcode');

add_action('wp_enqueue_scripts', 'subscribe_now_scripts');
add_action('wp_enqueue_scripts', 'subscribe_now_styles');
add_action('admin_enqueue_scripts', 'subscribe_now_enqueue_admin_style');

/**
 * Load plugin textdomain.
 */
function subscribe_now_load_textdomain() {
    load_plugin_textdomain( subscribe_now_config()['textDomain'], false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_action( 'init', 'subscribe_now_load_textdomain' );