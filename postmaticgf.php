<?php
/*
Plugin Name: Postmatic for Gravity Forms
Description: Gravity Forms integration for Postmatic
Author: Jeff Matson
Version: 1.0.0
Author URI: http://jeffmatson.net
*/

function postmatic_gf_check_dependencies() {
    if (!class_exists("Prompt_Core")) {
        add_action('admin_notices', 'postmatic_not_installed');
    }
    if (!class_exists("GFForms")) {
        add_action('admin_notices', 'gf_not_installed');
    }
}
add_action( 'plugins_loaded', 'postmatic_gf_check_dependencies' );


function gf_not_installed() {
    $class = "error";
    $message = "Gravity Forms is not installed or activated!  You will need to install and activate Gravity Forms before using this plugin!";
    echo"<div class=\"$class\"> <p>$message</p></div>";
}

function postmatic_not_installed() {
    $class = "error";
    $message = "Postmatic is not installed or activated!  You will need to install and activate Postmatic before using this plugin!";
    echo"<div class=\"$class\"> <p>$message</p></div>";
}

if (class_exists("GFForms")) {

    GFForms::include_feed_addon_framework();

    class PostmaticGF extends GFFeedAddOn {
        // The following class variables are used by the Framework.
        // They are defined in GFAddOn and should be overridden.

        // The version number is used for example during add-on upgrades.
        protected $_version = '1.0';

        // The Framework will display an appropriate message on the plugins page if necessary
        protected $_min_gravityforms_version = '1.8.7';

        // A short, lowercase, URL-safe unique identifier for the add-on.
        // This will be used for storing options, filters, actions, URLs and text-domain localization.
        protected $_slug = 'postmatic';

        // Relative path to the plugin from the plugins folder.
        protected $_path = 'postmaticgf/postmaticgf.php';

        // Full path the the plugin.
        protected $_full_path = __FILE__;

        // Title of the plugin to be used on the settings page, form settings and plugins page.
        protected $_title = 'Postmatic';

        // Short version of the plugin title to be used on menus and other places where a less verbose string is useful.
        protected $_short_title = 'Postmatic';

        public function feed_list_columns() {
            return array(
                'feed_name'            => esc_html__( 'Name', 'postmatic' )
            );
        }

        function feed_settings_fields() {

            if ( is_numeric($_GET['id'])){
                $feed_form_id = $_GET['id'];
                $form = GFAPI::get_form( $feed_form_id );
            }

            $args = array(
                    'field_types'    => array(),
                    'input_types'    => array('text', 'name', 'email'),
                    'callback'       => false
            );

            $fields = GFAddOn::get_form_fields_as_choices( $form, $args );

            return array(
                array(
                    'title'  => 'Postmatic',
                    'fields' => array(
                        array(
                            'name' => 'feed_name',
                            'label'       => 'Feed Name',
                            'type'        => 'text',
                        ),
                        array(
                            'name'     => 'first_name',
                            'label'    => 'First Name Field',
                            'type'     => 'select',
                            'choices'  => $fields,
                        ),
                        array(
                            'name'     => 'last_name',
                            'label'    => 'Last Name Field',
                            'type'     => 'select',
                            'choices'  => $fields,
                        ),
                        array(
                            'name'     => 'email',
                            'label'    => 'Email Field',
                            'type'     => 'select',
                            'choices'  => $fields,
                            'required' => true
                        ),
                        array(
                            'name'           => 'condition',
                            'tooltip'        => "Configure the event that riggers the user to be subscribed to your Postmatic feed.",
                            'label'          => 'Condition',
                            'type'           => 'feed_condition',
                            'checkbox_label' => 'Enable condition for this subscription',
                            'instructions'   => 'Subscribe this user if',
                        ),
                    )
                ),
            );
        }

        function process_feed( $feed, $entry, $form ) {

            $first_name = $this->get_field_value( $form, $entry, $feed['meta']['first_name'] );
            $last_name = $this->get_field_value( $form, $entry, $feed['meta']['last_name'] );
            $email = $this->get_field_value( $form, $entry, $feed['meta']['email'] );

            $subscriber_data = array(
                'email_address' => $email,
                'first_name' => $first_name,
                'last_name' => $last_name,
            );

            Prompt_Api::subscribe( $subscriber_data );
        }

    }
    new PostmaticGF();
}


