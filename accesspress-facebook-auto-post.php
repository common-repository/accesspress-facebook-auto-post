<?php defined('ABSPATH') or die('No script kiddies please!');
/**
 * Plugin Name: Social Auto Poster
 * Plugin URI: https://accesspressthemes.com/wordpress-plugins/accesspress-facebook-auto-post/
 * Description: A plugin to publish your wordpress posts to your facebook profile and fan pages.
 * Version: 2.1.4
 * Author: AccessPress Themes
 * Author URI: http://accesspressthemes.com
 * Text Domain: accesspress-facebook-auto-post
 * Domain Path: /languages/
 * License: GPL2
 */
if (!class_exists('AFAP_Class')) {

    /**
     * Declaration of plugin main class
     * */
    class AFAP_Class {

        var $afap_settings;
        var $afap_extra_settings;

        /**
         * Constructor
         */
        function __construct() {
            $this->afap_settings = get_option('afap_settings');
            $this->afap_extra_settings = get_option('afap_extra_settings');
            $this->define_constants();
            register_activation_hook(__FILE__, array($this, 'activation_tasks')); //fired when plugin is activated
            add_action('admin_init', array($this, 'plugin_init')); //starts the session and loads plugin text domain on admin_init hook
            add_action('admin_menu', array($this, 'afap_admin_menu')); //For plugin admin menu
            add_action('admin_enqueue_scripts', array($this, 'register_admin_assets')); //registers js and css for plugin
            add_action('admin_post_afap_fb_authorize_action', array($this, 'fb_authorize_action')); //action to authorize facebook
            //add_action('admin_post_afap_callback_authorize', array($this, 'afap_callback_authorize')); //action to authorize facebook
            add_action('admin_post_afap_form_action', array($this, 'afap_form_action')); //action to save settings
            add_action('admin_init', array($this, 'auto_post_trigger')); // auto post trigger
            add_action('admin_post_afap_clear_log', array($this, 'afap_clear_log')); //clears log from log table
            add_action('admin_post_afap_delete_log', array($this, 'delete_log')); //clears log from log table
            add_action('admin_post_afap_restore_settings', array($this, 'restore_settings')); //clears log from log table
            add_action('add_meta_boxes', array($this, 'add_afap_meta_box')); //adds plugin's meta box
            add_action('save_post', array($this, 'save_afap_meta_value')); //saves meta value
            add_action('future_to_publish', array($this, 'auto_post_schedule'));
            add_action(  'transition_post_status',  array($this,'auto_post'), 10, 3 );

             // Facebook Mobile API: Ajax Action for generating Access Token from given Email and Password
            add_action('wp_ajax_asfap_access_token_ajax_action', array($this, 'asfap_access_token_ajax_action'));
            add_action('wp_ajax_nopriv_asfap_access_token_ajax_action', array($this, 'asfap_access_token_ajax_action'));
            // Ajax Action for getting the list of all the pages and groups associated with the email address
            add_action('wp_ajax_asfap_add_account_action', array($this, 'asfap_add_account_action'));
            add_action('wp_ajax_nopriv_asfap_add_account_action', array($this, 'asfap_add_account_action'));

            add_action( 'admin_init', array( $this, 'redirect_to_site' ), 1 );
            add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
            add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ) );

            add_action('wp_ajax_asfap_get_fbgraph_pages_action', array($this, 'asap_get_fbgraph_pages_action'));
            add_action('wp_ajax_nopriv_asfap_get_fbgraph_pages_action', array($this, 'asap_get_fbgraph_pages_action'));
        }

         function plugin_row_meta( $links, $file ){
            if ( strpos( $file, 'accesspress-facebook-auto-post.php' ) !== false ) {
                $new_links = array(
                    'doc' => '<a href="https://accesspressthemes.com/documentation/accesspress-facebook-auto-post/" target="_blank"><span class="dashicons dashicons-media-document"></span>Documentation</a>',
                    'support' => '<a href="http://accesspressthemes.com/support" target="_blank"><span class="dashicons dashicons-admin-users"></span>Support</a>',
                    'pro' => '<a href="https://accesspressthemes.com/wordpress-plugins/accesspress-social-auto-post/" target="_blank"><span class="dashicons dashicons-cart"></span>Premium version</a>'
                );
                $links = array_merge( $links, $new_links );
            }
            return $links;
        }

		
        function admin_footer_text( $text ){
            if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'afap') {
                $link = 'https://wordpress.org/support/plugin/accesspress-facebook-auto-post/reviews/#new-post';
                $pro_link = 'https://accesspressthemes.com/wordpress-plugins/accesspress-social-auto-post/';
                $text = 'Enjoyed Social Auto Poster? <a href="' . $link . '" target="_blank">Please leave us a ★★★★★ rating</a> We really appreciate your support! | Try premium version of <a href="' . $pro_link . '" target="_blank">AccessPress Social Auto Post</a> - more features, more power!';
                return $text;
            } else {
                return $text;
            }
        }

      function redirect_to_site(){
            if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'faposter-doclinks' ) {
                wp_redirect( 'https://accesspressthemes.com/documentation/accesspress-facebook-auto-post/' );
                exit();
            }
            if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'faposter-premium' ) {
                wp_redirect( 'https://accesspressthemes.com/wordpress-plugins/accesspress-social-auto-post/' );
                exit();
            }
        }
    public function asap_get_fbgraph_pages_action(){
            if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'apfap_backend_ajax_nonce')) {
                $g_appid = sanitize_text_field($_POST['fgraph_appid']);
                $g_appsecret = sanitize_text_field($_POST['fgraph_appsecret']);
                $g_usertoken = sanitize_text_field($_POST['fgraph_usertoken']);
                $ext_token_url = "https://graph.facebook.com/v12.0/oauth/access_token?grant_type=fb_exchange_token&client_id=".$g_appid."&client_secret=".$g_appsecret."&fb_exchange_token=".$g_usertoken;
                $ext_token_request = wp_remote_get($ext_token_url);
                if( is_wp_error( $ext_token_request ) ) {
                    $ext_token_response = wp_remote_retrieve_body($ext_token_request);
                    wp_send_json_error($ext_token_response);
                }
                $ext_token_response = wp_remote_retrieve_body($ext_token_request);
                $ext_token_array = json_decode($ext_token_response, true);
                $ext_token = $ext_token_array['access_token'];
                $me_url = "https://graph.facebook.com/v12.0/me/?access_token=".$ext_token;
                $me_request = wp_remote_get($me_url);
                if( is_wp_error( $me_request ) ) {
                    $me_response = wp_remote_retrieve_body($me_request);
                    wp_send_json_error($me_response);
                }
                $me_response = wp_remote_retrieve_body($me_request);
                $me_array = json_decode($me_response, true);
                $user_id = $me_array['id'];
                $accounts_url = "https://graph.facebook.com/v12.0/".$user_id."/accounts/?access_token=".$ext_token;
                $accounts_request = wp_remote_get($accounts_url);
                if( is_wp_error( $accounts_request ) ) {
                    $accounts_response = wp_remote_retrieve_body($accounts_request);
                    wp_send_json_error($accounts_response);
                }
                $accounts_response = wp_remote_retrieve_body($accounts_request);
                wp_send_json_success($accounts_response);
              }
        }     
        /**
         * Necessary constants define
         */
        function define_constants(){
           if (!defined('AFAP_CSS_DIR')) {
                define('AFAP_CSS_DIR', plugin_dir_url(__FILE__) . 'css');
            }
            if( !defined( 'FAUTOPOSTER_IMAGE_DIR' ) ) {
                define( 'FAUTOPOSTER_IMAGE_DIR', plugin_dir_url( __FILE__ ) . 'images' );
            }
            if (!defined('AFAP_IMG_DIR')) {
                define('AFAP_IMG_DIR', plugin_dir_url(__FILE__) . 'images');
            }
            if (!defined('AFAP_JS_DIR')) {
                define('AFAP_JS_DIR', plugin_dir_url(__FILE__) . 'js');
            }
            if (!defined('AFAP_VERSION')) {
                define('AFAP_VERSION', '2.1.4');
            }
            if (!defined('AFAP_TD')) {
                define('AFAP_TD', 'accesspress-facebook-auto-post');
            }
            if (!defined('AFAP_PLUGIN_FILE')) {
                define('AFAP_PLUGIN_FILE', __FILE__);
            }
            if (!defined('AFAP_PLUGIN_PATH')) {
                define('AFAP_PLUGIN_PATH', plugin_dir_path(__FILE__).'api/facebook-mobile');
            }

            if (!defined('AFAP_API_VERSION')) {
                define('AFAP_API_VERSION', 'v2.0');
            }

            if (!defined('AFAP_api')) {
                define('AFAP_api', 'https://api.facebook.com/' . AFAP_API_VERSION . '/');
            }
            if (!defined('AFAP_api_video')) {
                define('AFAP_api_video', 'https://api-video.facebook.com/' . AFAP_API_VERSION . '/');
            }

            if (!defined('AFAP_api_read')) {
                define('AFAP_api_read', 'https://api-read.facebook.com/' . AFAP_API_VERSION . '/');
            }

            if (!defined('AFAP_graph')) {
                define('AFAP_graph', 'https://graph.facebook.com/' . AFAP_API_VERSION . '/');
            }

            if (!defined('AFAP_graph_video')) {
                define('AFAP_graph_video', 'https://graph-video.facebook.com/' . AFAP_API_VERSION . '/');
            }
            if (!defined('AFAP_www')) {
                define('AFAP_www', 'https://www.facebook.com/' . AFAP_API_VERSION . '/');
            }
        }

        /**
         * Activation Tasks
         */
        function activation_tasks() {
            $afap_settings = $this->get_default_settings();
            $afap_extra_settings = array('authorize_status' => 0);
            if (!get_option('afap_settings')) {
                update_option('afap_settings', $afap_settings);
                update_option('afap_extra_settings', $afap_extra_settings);
            }

            global $wpdb;

            $charset_collate = $wpdb->get_charset_collate();
            $log_table_name = $wpdb->prefix . "afap_logs";


            $log_tbl_query = "CREATE TABLE IF NOT EXISTS $log_table_name (
                                log_id INT NOT NULL AUTO_INCREMENT,
                                PRIMARY KEY(log_id),
                                post_id INT NOT NULL,
                                log_status INT NOT NULL,
                                log_time VARCHAR(255),
                                log_details TEXT
                              ) $charset_collate;";
            //echo $log_tbl_query;
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta($log_tbl_query);
            //die();
        }

        /**
         * Starts session on admin_init hook
         */
        function plugin_init() {
            if (!session_id()) {
                session_start();
                session_write_close();
            }
            load_plugin_textdomain( 'afap', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        }

        /**
         * Returns Default Settings
         */
        function get_default_settings() {
            $default_settings = array('auto_publish' => 0,
                'application_id' => '',
                'application_secret' => '',
                'facebook_user_id' => '',
                'message_format' => '',
                'post_format' => 'simple',
                'include_image'=>0,
                'post_image' => 'featured',
                'custom_image_url' => '',
                'auto_post_pages' => array(),
                'post_types' => array(),
                'category' => array());
            return $default_settings;
        }

        /**
         * Registers Admin Menu
         */
        function afap_admin_menu() {
            add_menu_page(__('Social Auto Poster', 'accesspress-facebook-auto-post'), __('Social Auto Poster', 'accesspress-facebook-auto-post'), 'manage_options', 'afap', array($this, 'plugin_settings'),'dashicons-facebook-alt');
            add_submenu_page('afap', __( 'Documentation','accesspress-facebook-auto-post' ), __( 'Documentation', 'accesspress-facebook-auto-post'  ), 'manage_options', 'faposter-doclinks', '__return_false', null, 9 );
            add_submenu_page('afap', __( 'Check Premium Version', 'accesspress-facebook-auto-post'  ), __( 'Check Premium Version', 'accesspress-facebook-auto-post'  ), 'manage_options', 'faposter-premium', '__return_false', null, 9 );
        }

        /**
         * Plugin Settings Page
         */
        function plugin_settings() {
            include('inc/main-page.php');
        }

        /**
         * Registers Admin Assets
         */
        function register_admin_assets() {
            if (isset($_GET['page']) && $_GET['page'] == 'afap') {
                wp_enqueue_style('apsp-fontawesome-css', AFAP_CSS_DIR.'/font-awesome.min.css', AFAP_VERSION);
                wp_enqueue_style('afap-admin-css', AFAP_CSS_DIR . '/admin-style.css', array(), AFAP_VERSION);
                wp_enqueue_script('afap-admin-js', AFAP_JS_DIR . '/admin-script.js', array('jquery'), AFAP_VERSION);
               $ajax_js_obj = array('ajax_url' => admin_url('admin-ajax.php'),
                                'ajax_nonce' => wp_create_nonce('apfap_backend_ajax_nonce')
                               );
                wp_localize_script('afap-admin-js', 'asfap_backend_js_obj', $ajax_js_obj);
            }
        }

        /**
         * Returns all registered post types
         */
        function get_registered_post_types() {
            $post_types = get_post_types();
            unset($post_types['revision']);
            unset($post_types['attachment']);
            unset($post_types['nav_menu_item']);
            return $post_types;
        }

        /**
         * Prints array in pre format
         */
        function print_array($array) {
            echo "<pre>";
            print_r($array);
            echo "</pre>";
        }

        /**
         * Action to authorize the facebook
         */
        function fb_authorize_action() {
            if (!empty($_POST) && wp_verify_nonce($_POST['afap_fb_authorize_nonce'], 'afap_fb_authorize_action')) {
                include('inc/cores/fb-authorization.php');
            } else {
                die('No script kiddies please');
            }
        }

        /**
         * Action to save settings
         */
        function afap_form_action() {
            if (!empty($_POST) && wp_verify_nonce($_POST['afap_form_nonce'], 'afap_form_action')) {
                include('inc/cores/save-settings.php');
            } else {
                die('No script kiddies please!!');
            }
        }

        /**
         * Auto Post Trigger
         * */
        function auto_post_trigger() {
            $post_types = $this->get_registered_post_types();
            foreach ($post_types as $post_type) {
                $publish_action = 'publish_' . $post_type;
                $publish_future_action = 'publish_future_'.$post_type;
              //  add_action($publish_action, array($this, 'auto_post'), 10, 2);
              //  add_action($publish_action, array($this, 'auto_post_schedule'), 10, 2);

            }
        }

        /**
         * Auto Post Action
         * */
        function auto_post($new_status, $old_status, $post) {
            if($new_status == 'publish'){
                $auto_post = (isset($_POST['afap_auto_post']) && $_POST['afap_auto_post'] == 'yes')?'yes':'no';
                if ($auto_post == 'yes' || $auto_post == '') {
                    include_once('api/facebook.php'); // facebook api library
                    include_once( AFAP_PLUGIN_PATH . '/Facebook/Facebook_API.php' );
                    include('inc/cores/auto-post.php');
                    $check = update_post_meta($post->ID, 'afap_auto_post', 'no');
                    $_POST['afap_auto_post'] = 'no';
                }
            }
        }
		


        function auto_post_schedule($post){
            $auto_post = get_post_meta($post->ID,'afap_auto_post',true);
            if ($auto_post == 'yes' || $auto_post == '') {
                include_once('api/facebook.php'); // facebook api library
                include_once( AFAP_PLUGIN_PATH . '/Facebook/Facebook_API.php' );
                include('inc/cores/auto-post.php');
                $check = update_post_meta($post->ID, 'afap_auto_post', 'no');
                $_POST['afap_auto_post'] = 'no';
            }
        }

        /**
         * Clears Log from Log Table
         */
        function afap_clear_log() {
            if (!empty($_GET) && wp_verify_nonce($_GET['_wpnonce'], 'afap-clear-log-nonce')) {
                global $wpdb;
                $log_table_name = $wpdb->prefix . 'afap_logs';
                $wpdb->query("TRUNCATE TABLE $log_table_name");
                $_SESSION['afap_message'] = __('Logs cleared successfully.', 'accesspress-facebook-auto-post');
                wp_redirect(admin_url('admin.php?page=afap&tab=logs'));
                exit();
            } else {
                die('No script kiddies please!');
            }
        }

        /**
         *
         * Delete Log
         */
        function delete_log() {
            if (!empty($_GET) && wp_verify_nonce($_GET['_wpnonce'], 'afap_delete_nonce')) {
                $log_id = $_GET['log_id'];
                global $wpdb;
                $table_name = $wpdb->prefix . 'afap_logs';
                $wpdb->delete($table_name, array('log_id' => $log_id), array('%d'));
                $_SESSION['afap_message'] = __('Log Deleted Successfully', 'accesspress-facebook-auto-post');
                wp_redirect(admin_url('admin.php?page=afap'));
            } else {
                die('No script kiddies please!');
            }
        }

        /**
         * Plugin's meta box
         * */
        function add_afap_meta_box($post_type) {
            add_meta_box(
                    'afap_meta_box'
                    , __('Social Auto Poster', 'accesspress-facebook-auto-post')
                    , array($this, 'render_meta_box_content')
                    , $post_type
                    , 'side'
                    , 'high'
            );
        }

        /**
         * afap_meta_box html
         *
         * */
        function render_meta_box_content($post) {
            // Add an nonce field so we can check for it later.
            wp_nonce_field('afap_meta_box_nonce_action', 'afap_meta_box_nonce_field');
            $default_auto_post = in_array($post->post_status, array("future", "draft", "auto-draft", "pending"))?'yes':'no';
            // Use get_post_meta to retrieve an existing value from the database.
            $auto_post = get_post_meta($post->ID, 'afap_auto_post', true);
            //var_dump($auto_post);
            $auto_post = ($auto_post == '' || $auto_post == 'yes') ? $default_auto_post : 'no';

            // Display the form, using the current value.
            ?>
            <label for="afap_auto_post"><?php _e('Enable Auto Post For Facebook Profile or Fan Pages?', 'accesspress-facebook-auto-post'); ?></label>
            <p>
                <select name="afap_auto_post">
                    <option value="yes" <?php selected($auto_post, 'yes'); ?>><?php _e('Yes', 'accesspress-facebook-auto-post'); ?></option>
                    <option value="no" <?php selected($auto_post, 'no'); ?>><?php _e('No', 'accesspress-facebook-auto-post'); ?></option>
                </select>
            </p>
            <?php
        }

        /**
         * Saves meta value
         * */
        function save_afap_meta_value($post_id) {
            //$this->print_array($_POST);die('abc');
            /*
             * We need to verify this came from the our screen and with proper authorization,
             * because save_post can be triggered at other times.
             */

            // Check if our nonce is set.
            if (!isset($_POST['afap_auto_post']))
                return $post_id;
             $nonce = (isset($_POST['afap_meta_box_nonce_field']) && $_POST['afap_meta_box_nonce_field'] !='')?$_POST['afap_meta_box_nonce_field']:'';

            // Verify that the nonce is valid.
            if (!wp_verify_nonce($nonce, 'afap_meta_box_nonce_action'))
                return $post_id;

            // If this is an autosave, our form has not been submitted,
            //     so we don't want to do anything.
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
                return $post_id;

            // Check the user's permissions.
            if ('page' == $_POST['post_type']) {

                if (!current_user_can('edit_page', $post_id))
                    return $post_id;
            } else {

                if (!current_user_can('edit_post', $post_id))
                    return $post_id;
            }

            /* OK, its safe for us to save the data now. */

            // Sanitize the user input.
            $auto_post = sanitize_text_field($_POST['afap_auto_post']);

            // Update the meta field.
            update_post_meta($post_id, 'afap_auto_post', $auto_post);
        }

        /**
         * Restores Default Settings
         */
        function restore_settings(){
            $afap_settings = $this->get_default_settings();
            $afap_extra_settings = array('authorize_status'=>0);
            update_option('afap_extra_settings', $afap_extra_settings);
            update_option('afap_settings', $afap_settings);
            $_SESSION['afap_message'] = __('Default Settings Restored Successfully','accesspress-facebook-auto-post');
            wp_redirect('admin.php?page=afap');
            exit();
        }

		function graph_pages_and_groups() {
            $account_details = get_option('afap_settings');
			$page_details_arr=array();
            if ($account_details) {
                // $page_group_lists = (isset($account_details['page_group_lists']) && !empty($account_details['page_group_lists'])) ? $account_details['page_group_lists'] : array();
                $page_details = (isset($account_details['page_details'])) ? $account_details['page_details'] : array();
				if(!empty($page_details)){
					$page_details_arr = json_decode($page_details, TRUE);
				}
             }
            if (is_array($page_details_arr) && !empty($page_details_arr)) {
                foreach ($page_details_arr['data'] as $page_detail) {
                    $res_data[$page_detail['id']] = $page_detail['name'];
                }
            }
            if (!empty($res_data)) {
                return $res_data;
            }   
        }       
    }
    $afap_obj = new AFAP_Class();
}// class Termination
