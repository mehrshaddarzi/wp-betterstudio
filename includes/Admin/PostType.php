<?php
namespace Admin;

use Helper\Statistic;

class PostType
{

    protected static $instance = NULL;


    /**
     * Singleton class instance.
     *
     * @return Class
     */
    public static function get() {
        if ( NULL === self::$instance )
            self::$instance = new self;
        return self::$instance;
    }


    /*
     * Create Shortlink Post type in Admin
     */
    public function create_shortlink_post_type()
    {
        $t_d = \WP_BETTERSTUDIO_TEST::text_doamin;
        $labels = array(
            'name' => __( 'Link', $t_d),
            'singular_name' => __( 'Link',  $t_d ),
            'add_new' => __( 'New Link', $t_d ),
            'add_new_item' => __( 'Add New Link', $t_d ),
            'edit_item' => __( 'Edit Link', $t_d ),
            'new_item' => __( 'New Link', $t_d ),
            'all_items' => __( 'All Links', $t_d ),
            'view_item' => __( 'Show Link', $t_d ),
            'search_items' => __( 'Search in Links', $t_d ),
            'not_found' => __( 'Not found Any Link', $t_d ),
            'not_found_in_trash' => __( 'Not found any Link in Trash', $t_d ),
            'parent_item_colon'  => __( 'Parent Links', $t_d ),
            'menu_name' => __( 'ShortLinks', $t_d ),
        );
        $args = array(
            'labels' => $labels,
            'description' => __( 'ShortLinks', $t_d ),
            'public' => true,
            'menu_position' => 5,
            'has_archive' => true,
            'show_in_admin_bar'   => false,
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'menu_icon'           => 'dashicons-admin-links',
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',

            /* support */
            'supports' => array( 'title'),

            /* Rewrite */
            'rewrite'  => array( 'slug' => \WP_BETTERSTUDIO_TEST::post_type ),

            /* Rest Api */
            'show_in_rest'       => true,
            'rest_base'          => 'shortlink_api',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
        );
        register_post_type( \WP_BETTERSTUDIO_TEST::post_type, $args );


    }


    /*
     * Column Table Post List
     */
    public function column_post_table($column, $post_id)
    {

        /*
         * unique view
         */
        if ($column == 'unique_view'){
            echo number_format(Statistic::get()->get_link_static($post_id, $type ="unique"));
        }

        /*
        * All view
        */
        if ($column == 'total_view'){
            echo __("Total : ", \WP_BETTERSTUDIO_TEST::text_doamin);
            echo number_format(Statistic::get()->get_link_static($post_id, $type ="all"));
            echo '<hr>';
            echo __("Robot View :  ", \WP_BETTERSTUDIO_TEST::text_doamin);
            echo number_format(Statistic::get()->get_link_static($post_id, $type ="robot"));
            echo '<hr>';
            echo __("Real View : ", \WP_BETTERSTUDIO_TEST::text_doamin);
            echo number_format(Statistic::get()->get_link_static($post_id, $type ="real"));
        }

        /*
         * Redirect column
         */
        if ($column == 'link_redirect') {
            if($this->show_redirect_to($post_id) !="-") {
                echo '<input style="width:100%;" type="text" value="'.$this->show_redirect_to($post_id).'" readonly>';
            } else {
                echo "-";
            }
        }

        /*
         * Link Address column
         */
        if ($column == 'link_address') {
                echo '<input style="width:100%;" type="text" class="input_edit" value="'.get_the_permalink($post_id).'" data-id="'.$post_id.'">';
        }

        /*
         * Redirect Type column
         */
        if ($column == 'redirect_type') {
            echo get_post_meta($post_id, 'type_redirect', true);
        }

    }


    /*
     * Column Shortlink Table Add
     */
    public function column_shortlink($columns)
    {

        /*
        * Add Redirect Type column
        */
        $columns['redirect_type'] = __('Redirect Type', \WP_BETTERSTUDIO_TEST::text_doamin);

        /*
         * Add Link Address column
         */
        $columns['link_address'] = __("Link Address", \WP_BETTERSTUDIO_TEST::text_doamin);

        /*
         * Add Redirect column
         */
        $columns['link_redirect'] = __("Redirect To", \WP_BETTERSTUDIO_TEST::text_doamin);

        /*
        * Add Uniqe View column
        */
        $columns['unique_view'] = __("Unique View", \WP_BETTERSTUDIO_TEST::text_doamin);

        /*
        * Add Total View column
        */
        $columns['total_view'] = __("Total View", \WP_BETTERSTUDIO_TEST::text_doamin);

        /*
         * change Title
         */
         $columns["title"] = __("Link Name", \WP_BETTERSTUDIO_TEST::text_doamin);

         /*
          * Remove Date
          */
         unset($columns['date']);

        return $columns;
    }


    /*
     * Change Title Enter Here
     */
    public function custom_enter_title( $input )
    {
        if ( \WP_BETTERSTUDIO_TEST::post_type === get_post_type() ) {
            return __( 'Please enter the name of the Link', \WP_BETTERSTUDIO_TEST::text_doamin );
        }

        return $input;
    }
    
    
    /*
     * Change Button in Add or Edit Short Link
     */
    public function change_button_admin($translation, $text)
    {
        if ( $text == 'Publish' ) return __( 'Add Link', \WP_BETTERSTUDIO_TEST::text_doamin );
        if ( $text == 'Update' ) return __( 'Edit Link', \WP_BETTERSTUDIO_TEST::text_doamin );

        return $translation;
    }


    /*
     * Add Sortable Column in Table
     */
    public function sortable_column($columns)
    {
        $columns['redirect_type'] = 'redirect_type';
        return $columns;
    }
    
    /*
     * Redirect Type Order Process
     */
    public function redirect_type_orderby($query)
    {
        if( ! is_admin() )
            return;

        $orderby = $query->get('orderby');

        if ('redirect_type' == $orderby) {
            $query->set('meta_key', 'redirect_type');
            $query->set('orderby', 'meta_value_num');
        }
        
        //https://stackoverflow.com/questions/26394593/wordpress-multiple-meta-key-in-pre-get-posts
    }

    /*
     * Redirect Process Post type Shortlink
     */
    public function shortlink_redirect()
    {
        global $post, $wpdb;

        /*
         * Check single is Short link
         */
        if( is_single() and \WP_BETTERSTUDIO_TEST::post_type === get_post_type() ) {

            /*
             * Adding Action For development
             */
            $post_id = $post->ID;
            do_action('wp_shortcode_before_start_redirect', $post_id);

            /*
             * Adding To Databse Detail
             */
            $ip = $_SERVER['HTTP_CLIENT_IP'] ? $_SERVER['HTTP_CLIENT_IP'] : ($_SERVER['HTTP_X_FORWARDE‌​D_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
            $type = 1; //Real
            if (preg_match('/bot|crawl|slurp|spider|mediapartners/i', $ip) ) $type = 2;

            /*
             * Set data For MySql
             */
            $arg = array(
                'post_id' => $post->ID,
                'ip' => $ip,
                'type' => $type,
                'date' => current_time('mysql'),
            );
            $arg = apply_filters( 'wp_shortlink_change_detail_to_mysql', $arg );

            /*
             * Add To MySQl
             */
            $wpdb->insert($wpdb->prefix.'shortlink', $arg, array('%d', '%s', '%d', '%s',));

            /*
             * Action After Added to database
             */
            do_action('wp_shortcode_after_added_detail', $post_id);

            /*
             * Redirect
             */
            wp_redirect( get_post_meta($post->ID, 'redirect_to', true), get_post_meta($post->ID, 'type_redirect', true) );
            exit;
        }
    }


    /*
     * Show Redirect Url by post id
     */
    public function show_redirect_to($post_id)
    {
        $meta = get_post_meta( $post_id, 'redirect_to', true );
        if ( ! empty( $meta ) ) {
            return $meta;
        } else {
            return "-";
        }
    }


    /*
     * Change Of Action Post Type
     */
    public function disable_quick_edit( $actions, $post )
    {
        global $current_screen;

        if ( $current_screen->post_type != \WP_BETTERSTUDIO_TEST::post_type ) return $actions;

        /*
         * Remove Inline Edit
         */
        unset( $actions[ 'inline hide-if-no-js' ] );

        /*
         * Add Target _blank for View
         */
        if(is_array($actions)) foreach($actions as $key => &$value){
            // For the right row_action
            if(($key === 'view') and is_string($value)){
                // Add the target="_blank" in the A tag's attributes
                $value = preg_replace('~<a[\s]+~i', '<a target="_blank" ', $value);
            }
        }


        return $actions;
    }


    /*
     * Custom jQuery tabe list Post Type
     */
    public function custom_jquery_table()
    {
        if($_GET['post_type'] ==\WP_BETTERSTUDIO_TEST::post_type) {
            ?>
            <script>
                jQuery(document).ready(function($){
                    $('input[data-id]').focus( function() {
                        $("<div class=\"action_edit_post\"><a href=\"post.php?post=" + $(this).attr("data-id") + "&action=edit\"><span class=\"dashicons dashicons-admin-links\"></span><?php _e( 'Edit Link', \WP_BETTERSTUDIO_TEST::text_doamin ); ?></a></div>").insertAfter(this);
                    });
                    $('input').blur( function() {$(".action_edit_post").hide();});
                });
            </script>
            <?php
        }
    }
    
    
    /*
     * Remove Extra MetaBox
     */
    public static function remove_extra_metabox()
    {
        global $wp_meta_boxes, $post_type;

        /** Check the post type (remove if you don't want/need) */
        if(!in_array($post_type, array(
            self::post_type
        ))) :
            return false;
        endif;

        /** Create an array of meta boxes exceptions, ones that should not be removed (remove if you don't want/need) https://codex.wordpress.org/Function_Reference/remove_meta_box */
        $exceptions = array(
            'submitdiv', //Date and Publish meta box
            //BR_Taxonomy::taxonomy.'div', //Custom Taxonomy For Review Post Type
        );

        if(!empty($wp_meta_boxes)) : foreach($wp_meta_boxes as $page => $page_boxes) :

            if(!empty($page_boxes)) : foreach($page_boxes as $context => $box_context) :

                if(!empty($box_context)) : foreach($box_context as $box_type) :

                    if(!empty($box_type)) : foreach($box_type as $id => $box) :

                        if(!in_array($id, $exceptions)) :
                            /** Remove the meta box */
                            remove_meta_box($id, $page, $context);
                        endif;

                    endforeach;
                    endif;

                endforeach;
                endif;

            endforeach;
            endif;

        endforeach;
        endif;
    }
    
    
    

}
