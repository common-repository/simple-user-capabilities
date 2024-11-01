<?php 
// Prevent direct access to this file
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SUC_capabilities{
    public function __construct(){
        
    }

    
    public function suc_get_core_capabilities(){

        $wp_version = get_bloginfo('version');
        
        $caps = array();
        $caps['switch_themes'] = array('core', 'themes');
        $caps['edit_themes'] = array('core', 'themes');
        $caps['activate_plugins'] = array('core', 'plugins');
        $caps['edit_plugins'] = array('core', 'plugins');
        $caps['edit_users'] = array('core', 'users');
        $caps['edit_files'] = array('core', 'deprecated');
        $caps['manage_options'] = array('core', 'general');
        $caps['moderate_comments'] = array('core', 'posts', 'general');
        $caps['manage_categories'] = array('core', 'posts', 'general');
        $caps['manage_links'] = array('core', 'general');
        $caps['upload_files'] = array('core', 'general'); 
        $caps['import'] = array('core', 'general');
        $caps['unfiltered_html'] = array('core','general');
    
        $caps['edit_posts'] = array('core', 'posts');
        $caps['edit_others_posts'] = array('core', 'posts');
        $caps['edit_published_posts'] = array('core', 'posts');
        $caps['publish_posts'] = array('core', 'posts');
        $caps['edit_pages'] = array('core', 'pages');
        $caps['read'] = array('core', 'general');
        $caps['level_10'] = array('core', 'deprecated');
        $caps['level_9'] = array('core', 'deprecated');
        $caps['level_8'] = array('core', 'deprecated');
        $caps['level_7'] = array('core', 'deprecated');
        $caps['level_6'] = array('core', 'deprecated');
        $caps['level_5'] = array('core', 'deprecated');
        $caps['level_4'] = array('core', 'deprecated');
        $caps['level_3'] = array('core', 'deprecated');
        $caps['level_2'] = array('core', 'deprecated');
        $caps['level_1'] = array('core', 'deprecated');
        $caps['level_0'] = array('core', 'deprecated');
        $caps['edit_others_pages'] = array('core', 'pages');
        $caps['edit_published_pages'] = array('core', 'pages');
        $caps['publish_pages'] = array('core', 'pages');
        $caps['delete_pages'] = array('core', 'pages');
        $caps['delete_others_pages'] = array('core', 'pages');
        $caps['delete_published_pages'] = array('core', 'pages');
        $caps['delete_posts'] = array('core', 'posts');
        $caps['delete_others_posts'] = array('core', 'posts');
        $caps['delete_published_posts'] = array('core', 'posts');
        $caps['delete_private_posts'] = array('core', 'posts');
        $caps['edit_private_posts'] = array('core', 'posts');
        $caps['read_private_posts'] = array('core', 'posts');
        $caps['delete_private_pages'] = array('core', 'pages');
        $caps['edit_private_pages'] = array('core', 'pages');
        $caps['read_private_pages'] = array('core', 'pages');
        $caps['unfiltered_upload'] = array('core', 'general');
        $caps['edit_dashboard'] = array('core', 'general');
        $caps['update_plugins'] = array('core', 'plugins');
        $caps['delete_plugins'] = array('core', 'plugins');
        $caps['install_plugins'] = array('core', 'plugins');
        $caps['update_themes'] = array('core', 'themes');
        $caps['install_themes'] = array('core', 'themes');
        $caps['update_core'] = array('core', 'general');
        $caps['list_users'] = array('core', 'users');
        $caps['remove_users'] = array('core', 'users');
                
        if (version_compare($wp_version, '4.4', '<')) {
            $caps['add_users'] = array('core', 'users');  // removed from WP v. 4.4.
        }
        
        $caps['promote_users'] = array('core', 'users');
        $caps['edit_theme_options'] = array('core', 'themes');
        $caps['delete_themes'] = array('core', 'themes');
        $caps['export'] = array('core', 'general');
        $caps['delete_users'] = array('core', 'users');
        $caps['create_users'] = array('core', 'users');
        
        return $caps;
        
            
    }

        
    function suc_cap_details($cap_id){
        $core_capabilities = $this->suc_get_core_capabilities();
        if($cap_id){
            $cap_view = str_replace('_', " ", $cap_id);
            $cap_view = ucfirst($cap_view);
            $cap = array();
            $cap['id'] = $cap_id;
            $cap['html'] = $cap_view;
            if(isset($core_capabilities[$cap_id])){
                $cap['core'] = true;
                $cap['group'] = $core_capabilities[$cap_id][1];
            }
            else{
                $cap['core'] = false;
                $cap['group'] = 'custom';
            }

        }
        
        return $cap;
    }

    // Function to retrieve all available capabilities
    function suc_get_all_capabilities() {
        global $wp_roles;
        $capabilities = array();

        foreach ($wp_roles->role_objects as $role) {
            foreach ($role->capabilities as $capability => $value) {
                if (!in_array($capability, $capabilities)) {
                    $capabilities[] = $capability;
                }
            }
        }

        return $capabilities;
    }

    // Function to retrieve the capabilities for a specific user
    function suc_get_user_capabilities($user_id) {
        $user = get_user_by('ID', $user_id);

        return array_keys($user->allcaps);
    }

    // Callback function to render the settings page content
    function suc_custom_capabilities_settings_callback() {
        
        
        // Get all available capabilities
        $all_capabilities = $this->suc_get_all_capabilities();
        $all_proced_caps = array();
        
        foreach($all_capabilities as $cap_item){
            $all_proced_caps[$cap_item] = $this->suc_cap_details($cap_item);
        }
        
        $groppedCaps = array();
        foreach( $all_proced_caps as $cap){
            $capGroup = $cap['group'];
            if(!isset($groppedCaps[$capGroup])){
                $groppedCaps[$capGroup] = [];
            }
            $groppedCaps[$capGroup][] = $cap;
        }
        
        // Custom sort the array by key name
        uksort($groppedCaps, function ($key1, $key2) {
            if ($key1 === 'general') {
                return -1; // Keep 'general' as the first key
            } elseif ($key2 === 'general') {
                return 1; // Move 'general' to a higher position
            }
            elseif ($key1 === 'posts') {
                return -1; // Keep 'posts' as the first key
            } elseif ($key2 === 'posts') {
                return 1; // Move 'posts' to a higher position
            } 
            elseif ($key1 === 'pages') {
                return -1; // Keep 'pages' as the first key
            } elseif ($key2 === 'pages') {
                return 1; // Move 'pages' to a higher position
            } 
            elseif ($key1 === 'plugins') {
                return -1; // Keep 'plugins' as the first key
            } elseif ($key2 === 'plugins') {
                return 1; // Move 'plugins' to a higher position
            } 
            elseif ($key1 === 'themes') {
                return -1; // Keep 'themes' as the first key
            } elseif ($key2 === 'themes') {
                return 1; // Move 'themes' to a higher position
            } 
            else {
                return strcasecmp($key2, $key1); // Sort the rest of the keys
            }
        });

        // Get the selected capabilities for the user
        // Get the user ID if editing a specific user
        $user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : get_current_user_id();
        $users = get_users();

        //$user_id = 4;
        $selected_capabilities = $this->suc_get_user_capabilities($user_id);
        
        //echo "<pre>"; print_r($groppedCaps );exit;
        $currentGroup = '';
        $html = '<div class="container">';
        $html .= '<form method="post" id="submit_user_cap" href="'. esc_url( admin_url( 'admin-ajax.php' ) ) .'?showonly_databb=1">';

        
        $html .= '<div class="row"><div class="col-md-12"><div class="form-group mb-3">';
        $html .= '<label class="form-label" for="admin_menu_list"><b>Menus List:</b></label>';
        $html .= '<select id="admin_menu_list" name="user_caps[]" class="selectpicker" multiple data-live-search="true" data-dropup-auto="false">';
        
        foreach( $groppedCaps as $key => $groppedCap ) {
            if ( $key != $currentGroup ) {
                $html .= "<optgroup label='" . esc_html( ucfirst( $key ) ) . "'>"; 
            }
            $currentGroup = $key;
            foreach ( $groppedCap as $item ) {
                $selected = in_array( $item['id'], $selected_capabilities ) ? 'selected' : '';
                $html .= "<option value='" . esc_attr( $item['id'] ) . "' " . esc_attr( $selected ) . ">" . esc_html( $item['html'] ) . "</option>";
            }
            $html .= "</optgroup>";
        }
        $html .= '</select>';
        $html .= '</div></div></div>';
        
        $html .= '<div class="row"><div class="col-md-12"><div class="form-group">';
        $html .= "<label class='form-label' for='targeted_user'><b>Targeted User:</b></label>";
        $html .= '<select class="selectpicker" data-live-search="true" id="targeted_user" name="targeted_user">';
        
        foreach ( $users as $user ) {
            $selected = ( $user->ID == $user_id ) ? 'selected' : '';
            $html .= '<option ' . esc_attr( $selected ) . ' value="' . esc_attr( $user->ID ) . '">' . esc_html( $user->display_name ) . '</option>';
        }
        $html .= '</select>';
        $html .= '</div></div></div>';
        
        $html .= '<div class="row"><div class="col-md-12"><div class="form-group">';
        $html .= '<input type="submit" class="button button-primary float-end" id="save_user_access" value="' . esc_attr( 'Save Settings' ) . '">';
        $html .= '</div></div></div>';
        $html .= '</form>';
        $html .= '</div>';
        
        // Use wp_kses() to allow only safe HTML tags
        $allowed_tags = [
            'div' => [ 'class' => [], 'id' => [] ],
            'form' => [ 'method' => [], 'id' => [], 'href' => [] ],
            'script' => [],
            'style' => [],
            'select' => [ 'id' => [], 'name' => [], 'class' => [], 'multiple' => [], 'data-live-search' => [], 'data-dropup-auto' => [] ],
            'option' => [ 'value' => [], 'selected' => [] ],
            'optgroup' => [ 'label' => [] ],
            'label' => [ 'class' => [], 'for' => [] ],
            'input' => [ 'type' => [], 'class' => [], 'id' => [], 'value' => [] ],
        ];
        
        echo wp_kses( $html, $allowed_tags );
        exit;
        
    }
    
    
}


?>