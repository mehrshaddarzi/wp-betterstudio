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
	//add_action('add_meta_boxes', array(\Admin\PostType::get() ,'remove_extra_metabox') , 99, 2); //remove all extra post box
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
        add_action( 'manage_'.\WP_BETTERSTUDIO_TEST::post_type.'_posts_custom_column' , [\Admin\PostType::get(), 'column_post_table'] , 10, 2 );
        add_filter('manage_'.\WP_BETTERSTUDIO_TEST::post_type.'_posts_columns' , [\Admin\PostType::get(), 'column_shortlink']);
        add_filter( 'manage_edit-'.\WP_BETTERSTUDIO_TEST::post_type.'_sortable_columns', [\Admin\PostType::get(), 'sortable_column'] );
        add_action( 'admin_head-edit.php' , [ \Admin\PostType::get(), 'custom_jquery_table']  );
        add_action( 'pre_get_posts', [\Admin\PostType::get(), 'redirect_type_orderby'] );
        add_filter( 'post_row_actions',  [\Admin\PostType::get(), 'disable_quick_edit'], 10, 2 );
	//remove default item from Quick Edit => https://wordpress.stackexchange.com/questions/59871/remove-specific-items-from-quick-edit-menu-of-a-custom-post-type
	//add_action( 'quick_edit_custom_box', 'display_custom_quickedit_book', 10, 2 ); //https://codex.wordpress.org/Plugin_API/Action_Reference/quick_edit_custom_box [quick Action]
	//add_filter('bulk_actions-{Screen_id}','my_custom_bulk_actions'); => https://codex.wordpress.org/Plugin_API/Filter_Reference/bulk_actions
	//add_filter('default_hidden_meta_boxes','hide_meta_box',10,2); => https://developer.wordpress.org/reference/hooks/default_hidden_meta_boxes || https://gist.github.com/mehrshaddarzi/33cce4cd1c7ffdf2200ccb15e7375de6
	//Add Column for All post type wordpress
	/*$post_types = (array) get_post_types( array( 'show_ui' => true ), 'object' );
	foreach ( $post_types as $type ) {
		add_action( 'manage_' . $type->name . '_posts_columns', 'WP_Statistics_Admin::add_column', 10, 2 );
		add_action(
			'manage_' . $type->name . '_posts_custom_column',
			'WP_Statistics_Admin::render_column',
			10,
			2
		);
	}*/
	    
	//***********************************************Add Meta Box After Table edit.php
	https://wordpress.stackexchange.com/questions/140319/add-content-before-after-admin-post-wp-list-table
	    
	//****************************************************Add content after title field
	/*function ai_edit_form_after_title() {
	// get globals vars
	global $post, $wp_meta_boxes;

	// render the FM meta box in 'ai_after_title' context
	do_meta_boxes( get_current_screen(), 'ai_after_title', $post );

	// unset 'ai_after_title' context from the post's meta boxes
	unset( $wp_meta_boxes['post']['ai_after_title'] );
}
add_action( 'edit_form_after_title', 'ai_edit_form_after_title' );*/
	    
	    //***************************************change post update msg post type
	    /*
	    function aa_Tables_messages($messages)
{
	$messages[__( 'aa_tables', 'AA_Theme'  )] =
		array(
			// Unused. Messages start at index 1
			0 => '',
			// Change the message once updated
			1 => sprintf(__('Table Updated. <a href="%s">View Table</a>', 'AA_Theme' ), esc_url(get_permalink($post_ID))),
			// Change the message if custom field has been updated
			2 => __('Custom Field Updated.', 'AA_Theme' ),
			// Change the message if custom field has been deleted
			3 => __('Custom Field Deleted.', 'AA_Theme' ),
			// Change the message once portfolio been updated
			4 => __('Table Updated.', 'AA_Theme' ),
			// Change the message during revisions
			5 => isset($_GET['revision']) ? sprintf( __('Table Restored To Revision From %s', 'AA_Theme' ), wp_post_revision_title((int)$_GET['revision'],false)) : false,
			// Change the message once published
			6 => sprintf(__('Table Published. ', 'AA_Theme' ), esc_url(get_permalink($post_ID))),
			// Change the message when saved
			7 => __('Table Saved.', 'AA_Theme' ),
			// Change the message when submitted item
			8 => sprintf(__('Table Submitted. ', 'AA_Theme' ), esc_url( add_query_arg('preview','true',get_permalink($post_ID)))),
			// Change the message when a scheduled preview has been made
			9 => sprintf(__('Table Scheduled For: <strong>%1$s</strong>. ', 'AA_Theme' ),date_i18n( __( 'M j, Y @ G:i' , 'AA_Theme' ),strtotime($post->post_date)), esc_url(get_permalink($post_ID))),
			// Change the message when draft has been made
			10 => sprintf(__('Table Draft Updated. ', 'AA_Theme' ), esc_url( add_query_arg('preview','true',get_permalink($post_ID)))),
		);
	return $messages;
} // function: portfolio_messages END
add_filter('post_updated_messages', 'aa_tables_messages');*/
	    
	    
	    
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
	    

	    //Add Script only Per Page Admin
	    /*
		add_action( 'admin_print_scripts-post-new.php', 'portfolio_admin_script', 11 );
		add_action( 'admin_print_scripts-post.php', 'portfolio_admin_script', 11 );

		function portfolio_admin_script() {
		global $post_type;
		if( 'portfolio' == $post_type )
		wp_enqueue_script( 'portfolio-admin-script', get_stylesheet_directory_uri() . '/admin.js' );
		}
	*/

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
