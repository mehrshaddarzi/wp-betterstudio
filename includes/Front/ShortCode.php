<?php
namespace Front;

use Admin\PostType;
use Helper\Statistic;
use Helper\Template;

class ShortCode
{

    // Get instance
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
     * Title Of Table Show ShortCode
     */
    public function title_of_table()
    {
        $textdomain = \WP_BETTERSTUDIO_TEST::text_doamin;

        $title_of_table = array(
            'title' => __("Link Name", $textdomain),
            'link_cat' => __("Link Categories", $textdomain),
            'link_redirect' => __("Link", $textdomain),
            'unique_view' => __("Unique View", $textdomain),
            'real_view' => __("Real View", $textdomain),
            'robot' => __("Robots View", $textdomain),
            'total' => __("Total View", $textdomain),
            'last_view' => __("Last Viewed", $textdomain),
        );

       return apply_filters( 'wp_shortlink_title_of_table', $title_of_table );
    }


    /*
     * Show Detail Of Table
     */
    public function content_of_table()
    {
        global $post;

        /*
         * Set Post_id
         */
        $post_id = $post->ID;

        /*
         * Get title id List
         */
        $content_of_table = array();
        foreach($this->title_of_table() as $id => $value) {

            //title
            if($id =="title") {
                $content_of_table[$id] = get_the_title($post_id);
            }

            //link_cat
            if($id =="link_cat") {
                $content_of_table[$id] = $this->show_list_term_of_postid($post_id, \WP_BETTERSTUDIO_TEST::taxonomy);
            }

            //link_redirect
            if($id =="link_redirect") {
                $content_of_table[$id] = '<a href="'.get_the_permalink($post_id).'" target="_blank">'.__('View Link', \WP_BETTERSTUDIO_TEST::text_doamin).'</a>';
            }

            //unique_view
            if($id =="unique_view") {
                $content_of_table[$id] = number_format(Statistic::get()->get_link_static($post_id, $type ="unique"));
            }

            //real_view
            if($id =="real_view") {
                $content_of_table[$id] = number_format(Statistic::get()->get_link_static($post_id, $type ="real"));
            }

            // robot
            if($id =="robot") {
                $content_of_table[$id] = number_format(Statistic::get()->get_link_static($post_id, $type ="robot"));
            }

            // total
            if($id =="total") {
                $content_of_table[$id] = number_format(Statistic::get()->get_link_static($post_id, $type ="all"));
            }

            // last_view
            if($id =="last_view") {
                $content_of_table[$id] = Statistic::get()->get_link_static($post_id, $type ="last_view");
            }

        }


        return apply_filters( 'wp_shortlink_content_of_table', $content_of_table, $post_id );
    }


    /*
     * Show Shortcode list Link
     */
    public function Shortlink_Component()
    {
        global $post, $wpdb;

        /*
         * Get Post List
         */
        $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
        $arg_shortlink_query = array (
            'post_type' => \WP_BETTERSTUDIO_TEST::post_type,
            'posts_per_page' => 10,
            'paged' => $paged,
        );

        /*
         * Apply filter for Custom Query
         */
        $arg_shortlink_query = apply_filters( 'wp_shortlink_custom_arg_query', $arg_shortlink_query );

        /*
         * Wp_Query run
         */
        $content = array();
        $query = new \WP_Query( $arg_shortlink_query );
        if ( $query->have_posts() ) :
        while ($query->have_posts()):
            $query->the_post();

            /*
             * Set Content For this Link
             */
            $content[] = $this->content_of_table();

        endwhile;
        endif;

        /*
         * Reset Post Data
         */
        wp_reset_postdata();

        /*
         * Adding Css only this page
         */
        wp_enqueue_style( 'listlink-style' );

        /*
         * Show Detail in Template File
         */
        return Template::get()->shortlink_get_template('table.php', ['title' => $this->title_of_table(), 'content' => $content, 'pagination' => $this->pagination( $paged, $query->max_num_pages)]);
    }


    /*
     * Show List Of Term From Post
     */
    public function show_list_term_of_postid($post_id, $term)
    {
        $text = '';
        $list = wp_get_post_terms( $post_id, $term );
        if(count($list) ==0) {
            $text = "-";
        } else {
            $i = 1;
            foreach($list as $term) {
                $text .= $term->name;
                if($i !=count($list)) { $text .= ' , '; }
                $i++;
            }
        }

        return $text;
    }



    /*
     * Show Pagination
     */
    public function pagination( $paged = '', $max_page = '' )
    {
        global $wp_query;

        $big = 999999999;
        if( ! $paged )
            $paged = get_query_var('paged');
        if( ! $max_page )
            $max_page = $wp_query->max_num_pages;

        return paginate_links( array(
            'base'       => str_replace($big, '%#%', esc_url(get_pagenum_link( $big ))),
            'format'     => '?paged=%#%',
            'current'    => max( 1, $paged ),
            'total'      => $max_page,
            'mid_size'   => 1,
            'prev_text'  => __('Prev Page', \WP_BETTERSTUDIO_TEST::text_doamin ),
            'next_text'  =>  __('Next Page', \WP_BETTERSTUDIO_TEST::text_doamin ),
            'type'       => 'list'
        ) );
    }
    
    
    
}