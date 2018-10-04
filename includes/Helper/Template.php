<?php
namespace Helper;


class Template
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
     * Set Template Show List
     */
    function shortlink_locate_template( $template_name, $template_path = '', $default_path = '' ) {

        // Set variable to search in betterstudio folder of theme.
        if ( ! $template_path ) :
            $template_path = 'betterstudio/';
        endif;

        // Set default plugin templates path.
        if ( ! $default_path ) :
            $default_path = \WP_BETTERSTUDIO_TEST::get_instance()->plugin_path . 'template/'; // Path to the template folder
        endif;

        // Search template file in theme folder.
        $template = locate_template( array(
            $template_path . $template_name,
            $template_name
        ) );

        // Get plugins template file.
        if ( ! $template ) :
            $template = $default_path . $template_name;
        endif;

        return apply_filters( 'shortlink_locate_template', $template, $template_name, $template_path, $default_path );
    }



    /*
     * Get Template File
     */
    function shortlink_get_template( $template_name, $args = array(), $tempate_path = '', $default_path = '' ) {

        if ( is_array( $args ) && isset( $args ) ) :
            extract( $args );
        endif;

        $template_file = $this->shortlink_locate_template( $template_name, $tempate_path, $default_path );

        if ( ! file_exists( $template_file ) ) :
            _doing_it_wrong( __FUNCTION__, __("Template Shortlink Not Found", \WP_BETTERSTUDIO_TEST::text_doamin) , '1.0.0' );
            return;
        endif;

        include $template_file;
    }
    
    
    /*
     * Add Css in Paged Shortcode Use
     */
    public function wp_enqueue_style()
    {
        /*
         * Check Style in Plugin or theme
         */
        $link = $this->shortlink_locate_template( 'table.css' );
        if(stristr($link,\WP_BETTERSTUDIO_TEST::get_instance()->plugin_path)) { $link = \WP_BETTERSTUDIO_TEST::get_instance()->plugin_url.'/template/table.css'; } else { $link = get_template_directory_uri().'/betterstudio/table.css'; }

        /*
         * Set Css
         */
        wp_register_style( 'listlink-style', $link, array(), '1.0.0', 'all' );
    }





}