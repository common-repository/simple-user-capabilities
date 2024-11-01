<?php
/*
Plugin Name: Simple User Capabilities
Description: A plugin that will give menu's access for the user
Version: 1.0
Author: Md Tanvir Ahamed
License: GPLv2 or later
*/

// Prevent direct access to this file
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}


require_once(plugin_dir_path(__FILE__).'user_grid.php');
require_once(plugin_dir_path(__FILE__).'capabilities.php');

class simple_user_cap{
  public function __construct(){
    add_action('admin_menu', array($this, 'suc_admin_menu'));
    add_action('wp_ajax_user_capabilities', array($this, 'suc_get_user_capabilities'));
    add_action('wp_ajax_nopriv_user_capabilities',  array($this, 'suc_get_user_capabilities'));

    add_action('wp_ajax_submit_capabilities', array($this,'suc_submit_capabilities'));
    add_action('wp_ajax_nopriv_submit_capabilities', array($this,'suc_submit_capabilities'));

    add_action('wp_ajax_reset_capability', array($this, 'suc_reset_capability'));
    add_action('wp_ajax_nopriv_reset_capability', array($this, 'suc_reset_capability'));

    add_action('admin_enqueue_scripts',  array($this, 'suc_scripts'));

    register_activation_hook(__FILE__, array($this,'suc_user_capabilities_record'));

  }

  public function suc_user_capabilities_record(){

    global $table_prefix, $wpdb;

    $tableName = $table_prefix.'simple_user_cap';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $tableName(
      id INT(11) NOT NULL AUTO_INCREMENT,
      user_id INT(22) NOT NULL DEFAULT '0',
      capabilities_log TEXT NULL,
      created DATETIME NULL,
      PRIMARY KEY (id) 
    ) $charset_collate;";

    require_once(ABSPATH.'wp-admin/includes/upgrade.php');
    dbDelta( $sql );

  }

  public function suc_submit_capabilities(){

    global $wpdb;
    $user_id = isset($_REQUEST['targeted_user']) ? absint($_REQUEST['targeted_user']) : 0;
    $user = get_user_by("ID", $user_id);
    //echo "<pre>"; print_r(get_userdata($user_id));exit;
    
    if($user->roles){
      $data = array(
        'user_id' => $user_id,
        'capabilities_log' => wp_json_encode($user->roles),
        'created' => "now()"
      );
      $wpdb->insert($wpdb->prefix.'simple_user_cap',$data );
    }
    
    $user->remove_all_caps();
    
    if (isset($_POST['user_caps'])) {
      $selected_caps = isset($_POST['user_caps']) ? array_map('sanitize_text_field', wp_unslash($_POST['user_caps'])) : array();
      // Further sanitize or process $selected_caps as needed
    } else {
        $selected_caps = array(); // Default to an empty array if 'user_caps' is not set
    }
  
    foreach($selected_caps as $cap){
      $user->add_cap($cap);
    }

    echo wp_json_encode(array('message'=>"Successfully submited", 'error'=>false));
    exit;

    $userallcaps = get_user_meta( $user_id, $wpdb->get_blog_prefix() . 'capabilities' );
    $userLevel = get_user_meta( $user_id, $wpdb->get_blog_prefix() . 'user_level' );
    //echo "<pre>"; print_r($user->allcaps);exit;
    //rlr_capabilities
    echo esc_html($wpdb->get_blog_prefix());exit;
    exit;
  }

  public function suc_reset_capability(){
    global $table_prefix, $wpdb;

    $user_id = 0;
    if(isset($_REQUEST['user_id'])){
      $user_id = intval(wp_unslash($_REQUEST['user_id'])); // Ensure it's an integer
    }
   
    $user = get_user_by("ID", $user_id);
    
    $table = $table_prefix."simple_user_cap";
    $user->remove_all_caps();
    
    // Secure SQL query using wpdb::prepare()
    $existing_data = $wpdb->get_row( $wpdb->prepare(
      "SELECT * FROM $table WHERE user_id = %d",
      $user_id
    ));

    if($existing_data){
      $user_roles = json_decode($existing_data->capabilities_log);
      foreach($user_roles as $role){
        $user->add_role($role);
      }
    }
    echo wp_json_encode(array('message'=>"Successfully reseted", 'error'=>false));
    exit;
  }

  public function suc_scripts($hook) {
    // Check if the current admin page is the correct one
    if ($hook === 'users_page_simple_user_cap' && isset($_GET['page']) && $_GET['page'] === 'simple_user_cap') {

        // Register and enqueue admin styles
        wp_register_style('user-cap-admin-styles', plugins_url('assets/css/admin-styles.css', __FILE__), [], '1.0');
        wp_enqueue_style('user-cap-admin-styles');

        // Register and enqueue Bootstrap styles
        wp_register_style('bootstrap-styles', plugins_url('assets/css/bootstrap.min.css', __FILE__), [], '4.6.0');
        wp_enqueue_style('bootstrap-styles');

        // Register and enqueue Bootstrap Select styles
        wp_register_style('bootstrap-select-styles', plugins_url('assets/css/bootstrap-select.min.css', __FILE__), [], '1.14.0');
        wp_enqueue_style('bootstrap-select-styles');

        // Register and enqueue Popper.js
        wp_register_script('popper-scripts', plugins_url('assets/js/popper.min.js', __FILE__), ['jquery'], '2.9.3', true);
        wp_enqueue_script('popper-scripts');

        // Register and enqueue Bootstrap JS (bundle includes Popper)
        wp_register_script('bootstrap-scripts', plugins_url('assets/js/bootstrap.bundle.min.js', __FILE__), ['jquery'], '5.0.2', true);
        wp_enqueue_script('bootstrap-scripts');

        // Register and enqueue Bootstrap Select scripts
        wp_register_script('bootstrap-select-scripts', plugins_url('assets/js/bootstrap-select.min.js', __FILE__), ['jquery'], '1.14.0', true);
        wp_enqueue_script('bootstrap-select-scripts');

        // Register and enqueue custom admin scripts
        wp_register_script('user-cap-scripts', plugins_url('assets/js/admin-scripts.js', __FILE__), ['jquery'], '3.7', true);
        wp_enqueue_script('user-cap-scripts');
    }
}




  public function suc_get_user_capabilities(){
    $capabilities  = new SUC_capabilities();
    $capabilities->suc_custom_capabilities_settings_callback(); 
  }

  public function suc_admin_menu(){
    
    add_submenu_page(
      'users.php',
      'Simple User Capabilities',
      'Simple Capabilities',
      'manage_options',
      'simple_user_cap',
      array($this,'suc_render_admin_page')
      );
  }

  public function suc_render_admin_page(){
    if(isset($_REQUEST['user_id'])){
      $user_id = intval(wp_unslash($_REQUEST['user_id'])); // Ensure it's an integer
      $user = get_user_by("ID", $user_id);
      $roles = array('author','subscriber');
      foreach($roles as $role){
        //$user->add_role($role);
      }
      $user_data = get_userdata($user_id);
      echo "<pre>"; print_r($user_data); exit;
    }
    ?>
    <!-- The Modal -->
    <div id="myModal" class="userAccess-modal">
      <!-- Modal content -->
      <div class="userAccess-modal-content">
        <div class="userAccess-modal-header">
          <span class="close">&times;</span>
          <h2>Modal Header</h2>
        </div>
        <div class="userAccess-modal-body">
          <p>Some text in the Modal Body</p>
          <p>Some other text...</p>
        </div>
      </div>
    </div>

    <!-- Modal for only simple informational messages (success or error)-->
    <div class="modal fade" id="modal_alert" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Message</h5>
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p>Modal Body</p>
          </div>
        </div>
      </div>
    </div>

    <div id="success-popup" class="alert alert-success alert-dismissible fade show in flash_message">
      <h4 class="alert-heading"><b>Message</b></h4>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
      <hr>
      <div id="success-view-message">OK Done</div>
    </div>

    <div class="wrap">
        <h2>User List</h2>
        <?php 
          $user_grid = new SUC_simple_cap_users();
          $user_grid->prepare_items();
          $user_grid->display(); 
        ?>
    </div>
    <?php
  }

}

new simple_user_cap();

