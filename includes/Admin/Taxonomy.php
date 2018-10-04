<?php
namespace Admin;

class Taxonomy
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


    public function Create_taxonomy_shortlink()
    {

        $t_d = \WP_BETTERSTUDIO_TEST::text_doamin;

        /*
         * ShortLink Taxonomy
         */
        $labels = array(
            'name' =>  __( 'Link Categories', $t_d),
            'singular_name' => __( 'Link Cat List', $t_d),
            'search_items' => __( 'Search in Link Cat', $t_d),
            'all_items' => __( 'All Link Cat', $t_d),
            'parent_item' => __( 'The Link Cat Parent', $t_d),
            'parent_item_colon' => __( 'Current Link Cat', $t_d),
            'edit_item' => __( 'Edit Link Cat', $t_d),
            'update_item' => __( 'Update Link Cat', $t_d),
            'add_new_item' => __( 'Add New Link Cat', $t_d),
            'new_item_name' => __( 'New Link Cat', $t_d),
            'menu_name' => __( 'Link Categories', $t_d),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => false,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => false,
            'show_tagcloud'              => false,
            'rewrite'                    => false,
        );
        register_taxonomy( \WP_BETTERSTUDIO_TEST::taxonomy, \WP_BETTERSTUDIO_TEST::post_type, apply_filters( 'wp_shortlink_taxonomy_filter' , $args , $labels ) );

    }

    /*
     * Remove Slug column
     */
    public function remove_slug_column($columns)
    {
        unset( $columns['slug'] );
        return $columns;
    }

    /*
     * Remove Slug Input
     */
    public function remove_slug_input()
    {
        global $pagenow;
        if(($pagenow =="term.php" || $pagenow =="edit-tags.php") and $_GET['taxonomy'] ==\WP_BETTERSTUDIO_TEST::taxonomy) {
            echo '<style>.term-slug-wrap,.column-slug { display: none; }</style>';
        }
    }

}