<?php

if( !class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class SUC_simple_cap_users extends WP_List_Table{
    public function __construct(){
        $this->screen = get_current_screen();
    }



    public function get_columns(){
        $columns = array(
            'user_login' => 'Username',
            'user_email' => 'Email',
            'user_registered' => 'Registered',
            'user_roles' => 'Role',
            'permission_menu' => 'Action'
        );
        return $columns;
    }

    public function prepare_items(){
        $per_page = 10;
        $columns = $this->get_columns();
        $shortable = array();
        $this->_column_headers = array($columns, $shortable);
        $data = array();
        $users = get_users();

        foreach($users as $user){
            if(count($user->roles)>0){
                $user_roles =   array_map('ucfirst', $user->roles);
                $user_roles = ucfirst(implode(', ', $user_roles));
            }
            else{
                $modifiedRoles = $this->getModifiedRole($user->ID);
                if(count($modifiedRoles)>0){
                    $user_roles =   array_map('ucfirst', $modifiedRoles);
                    $user_roles = ucfirst(implode(', ', $user_roles));
                }
                else{
                    $user_roles = "None";
                }
            }
            $data[] = array(
                'ID' => $user->ID,
                'user_login' => $user->user_login,
                'user_email' => $user->user_email,
                'user_roles' => $user_roles,
                'user_registered' => $user->user_registered
            );
        }

        $current_page = $this->get_pagenum();
        $total_items = count($users);
        $data = array_slice($data, (($current_page-1)*$per_page), $per_page);

        $this->items = $data;
        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page' => $per_page
            )
        );
    }

    public function getModifiedRole($user_id){
        global $wpdb, $table_prefix;
        
        // Ensure that $user_id is an integer
        $user_id = intval($user_id);

        $table = $table_prefix."simple_user_cap";
        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY id DESC LIMIT 1",
            $user_id
        ));
    
        if ($row) {
            return json_decode($row->capabilities_log, true);
        }
    
        return null; // In case no row is found

    }

    // display the columns for each item
    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'user_login':
            case 'user_email':
            case 'user_registered':
            case 'user_roles':
                return $item[$column_name];
            case 'permission_menu':
                $url = admin_url('admin-ajax.php').'?showonly_databb=1';
                //return '<a user_id="'.$item['ID'].'" class="more-info thickbox" href="'.$url.'&amp;TB_iframe=true&amp;width=450&amp;height=350">Menu Permissions</a>';
                return '<a data-toggle="tooltip" data-placement="bottom" title="Click to modify user permission" user_id="'.$item['ID'].'" class="manage_user_capabilities" href="'.$url.'">Manage</a> &nbsp; <a data-toggle="tooltip" data-placement="bottom" title="Click to reset Wordpress default permission" href="'. $url.'" user_id="'.$item['ID'].'" class="reset_restricts" >Reset</a>';
            default:
                return print_r($item, true);
        }
    }
    
}
?>