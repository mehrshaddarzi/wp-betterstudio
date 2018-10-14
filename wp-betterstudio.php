<?php
/**
 * Plugin Name: Wp BetterStusio Test
 * Description: A Sample WordPress Plugin For Test My Skill in wordpress developer 
 * Plugin URI:  https://betterstudio.com
 * Version:     1.0
 * Author:      Mehrshad Darzi
 * Author URI:  https://realwp.net
 * License:     MIT
 * Text Domain: wp-betterstudio
 * Domain Path: /languages
 */

/*
 * Plugin Loaded Action
 */
add_action('plugins_loaded', array(WP_BETTERSTUDIO_TEST::get_instance(), 'plugin_setup'));

/*
 * Register Activation Hook
 */
register_activation_hook(__FILE__, ['WP_BETTERSTUDIO_TEST' , 'activate'] );


class WP_BETTERSTUDIO_TEST
{
    /**
     * Plugin instance.
     *
     * @see get_instance()
     * @type object
     */
    protected static $instance = NULL;


    /**
     * URL to this plugin's directory.
     *
     * @type string
     */
    public $plugin_url = '';

    /**
     * Path to this plugin's directory.
     *
     * @type string
     */
    public $plugin_path = '';


    /**
     * TextDomain Name Plugin
     *
     * @type string
     */
    const text_doamin = 'wp-betterstudio';

    /*
     * Post Type ShortLink Slug
     * @type string
     */
    const post_type = 'shortlink';

    /*
     * Taxonomy ShortLink Slug
     * @type string
     */
    const taxonomy = 'linkcat';

    /**
     * Access this plugin’s working instance
     *
     * @wp-hook plugins_loaded
     * @return  object of this class
     */
    public static function get_instance()
    {
        if ( NULL === self::$instance )
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * Used for regular plugin work.
     *
     * @wp-hook plugins_loaded
     * @return  void
     */
    public function plugin_setup()
    {

        $this->plugin_url = plugins_url('/', __FILE__);
        $this->plugin_path = plugin_dir_path(__FILE__);


        /*
         * Set Text Domain
         */
         $this->load_language(self::text_doamin);

        /*
         * PSR Autoload
         */
        spl_autoload_register(array($this, 'autoload'));

        /*
         * Admin Action Load
         */
        $this->admin_action();

        /*
        * Public Action Load
        */
        $this->public_action();

    }


    /*
     * List Admin Action Wordpress
     */
    public function admin_action()
    {
        global $pagenow;

        /*
         * Create ShortLink Post Type
         */
        add_action( 'init', [\Admin\PostType::get(), 'create_shortlink_post_type'] );
        add_action( 'wp', [\Admin\PostType::get(), 'shortlink_redirect'] );


        /*
         * Add Taxonomies For Shortlink
         */
        add_action( 'init', [\Admin\Taxonomy::get(), 'Create_taxonomy_shortlink'] );
        add_filter('manage_edit-'.WP_BETTERSTUDIO_TEST::taxonomy.'_columns', [ \Admin\Taxonomy::get(), 'remove_slug_column']);
        add_action( 'admin_head' , [ \Admin\Taxonomy::get(), 'remove_slug_input']  );


        /*
         * New MetaBox For Shortlink PostType
         */
        add_action( 'add_meta_boxes', [\Admin\MetaBox::get(), 'Create_Meta_box'] );
        add_action( 'save_post_'.\WP_BETTERSTUDIO_TEST::post_type, [\Admin\MetaBox::get(), 'Save_MetaBox'] , 10, 2 );


        /*
         * Remove Shortlink Detail From Table in Complete Deleting a Link
         */
        add_action( 'before_delete_post', [\Admin\MetaBox::get(), 'Remove_Shortlink_Row'] );


        /*
         * Add Column Shortlink PosType Table
         */
        add_action( 'manage_posts_custom_column' , [\Admin\PostType::get(), 'column_post_table'] , 10, 2 );
        add_filter('manage_'.\WP_BETTERSTUDIO_TEST::post_type.'_posts_columns' , [\Admin\PostType::get(), 'column_shortlink']);
        add_filter( 'manage_edit-'.\WP_BETTERSTUDIO_TEST::post_type.'_sortable_columns', [\Admin\PostType::get(), 'sortable_column'] );
        add_action( 'admin_head-edit.php' , [ \Admin\PostType::get(), 'custom_jquery_table']  );
        add_action( 'pre_get_posts', [\Admin\PostType::get(), 'redirect_type_orderby'] );
        add_filter( 'post_row_actions',  [\Admin\PostType::get(), 'disable_quick_edit'], 10, 2 );
	//add_filter('bulk_actions-{Screen_id}','my_custom_bulk_actions'); => https://codex.wordpress.org/Plugin_API/Filter_Reference/bulk_actions
	//add_filter('default_hidden_meta_boxes','hide_meta_box',10,2); => https://developer.wordpress.org/reference/hooks/default_hidden_meta_boxes || https://gist.github.com/mehrshaddarzi/33cce4cd1c7ffdf2200ccb15e7375de6

        /*
         * Flush Rewrite in Not finding Post Type
         */
        add_action( 'init', [$this, 'flush_rewrite'] , 999 );

        /*
         * Change View Add Link Page
         */
        if (($pagenow =="post-new.php" and isset($_GET['post_type']) and $_GET['post_type'] ==\WP_BETTERSTUDIO_TEST::post_type) || ($pagenow =="post.php" and \WP_BETTERSTUDIO_TEST::post_type === get_post_type( $_GET['post'] ) )) {
            add_filter( 'enter_title_here', [\Admin\PostType::get(), 'custom_enter_title'] );
            add_filter( 'gettext', [\Admin\PostType::get(), 'change_button_admin'], 10, 2 );
        }


    }

    /*
     * Public Action Wordpress
     */
    public function public_action()
    {

        /*
         * Add Shortcode Shortlink Component
         */
        add_shortcode( 'linklist', [ \Front\ShortCode::get() , 'Shortlink_Component' ] );
        add_action( 'wp_enqueue_scripts', [ \Helper\Template::get(), 'wp_enqueue_style' ] );

    }


    /*
     * Flush Rewrite
     */
    public function flush_rewrite()
    {

            if ( get_option( 'wp_betterstudio_flush' ) ) {
                /*
                 * Flush Rewrite
                 */
                flush_rewrite_rules();

                /*
                 * Remove Option
                 */
                delete_option( 'wp_betterstudio_flush' );
            }
    }

    /*
     * Activation Hook
     */
    public static function activate() {
        global $wpdb;

        /*
         * Create Base Table in mysql
         */
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix.'shortlink';
        $sql = "CREATE TABLE $table_name (
                `ID` bigint(20) NOT NULL,
                `post_id` bigint(20) NOT NULL,
                `ip` varchar(100) NOT NULL,
                `type` INT(2) NOT NULL,
                `date` datetime NOT NULL,
                PRIMARY KEY  (ID)) {$charset_collate};";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        /*
         * Register Flush Rewrite Accept
         */
        if ( ! get_option( 'wp_betterstudio_flush' ) ) {
            add_option( 'wp_betterstudio_flush', true );
        }

    }

    /**
     * Loads translation file.
     *
     * Accessible to other classes to load different language files (admin and
     * front-end for example).
     *
     * @wp-hook init
     * @param   string $domain
     * @return  void
     */
    public function load_language($domain)
    {
        load_plugin_textdomain( $domain, false, basename( dirname( __FILE__ ) ) . '/languages' );
    }


    /**
     * Constructor. Intentionally left empty and public.
     *
     * @see plugin_setup()
     */
    public function __construct(){}


    /**
     * @param $class
     *
     */
    public function autoload($class)
    {
        $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

        if (!class_exists($class)) {
            $class_full_path = $this->plugin_path . 'includes/' . $class . '.php';

            if (file_exists($class_full_path)) {
                require $class_full_path;
            }
        }
    }
}
