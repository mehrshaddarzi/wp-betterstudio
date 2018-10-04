<?php
namespace Admin;


use Helper\Statistic;

class MetaBox
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


    public function Create_Meta_box()
    {
        global $pagenow;
        $t_d = \WP_BETTERSTUDIO_TEST::text_doamin;

        /*
         * Url Setting MetaBox
         */
        add_meta_box('betterstudio_test_metabox_url', __('URL Setting', $t_d), [$this , 'url_metabox'], \WP_BETTERSTUDIO_TEST::post_type, 'normal', 'high');

        /*
         * Show Report MetaBox
         */
        if($pagenow =="post.php" and "publish" === get_post_status( $_GET['post'] ) ) {
            add_meta_box('betterstudio_test_metabox_report', __('Static Report', $t_d), [$this , 'static_metabox'], \WP_BETTERSTUDIO_TEST::post_type, 'normal', 'high');
        }

    }



    /*
     * Url Setting Meta Box
     */
    public function url_metabox()
    {
        global $post, $wpdb;

        //Nounce Security
        wp_nonce_field( basename( __FILE__ ), 'short_fields_security' );

        //Type of redirect
        $type_of_redirect = get_post_meta($post->ID, 'type_redirect', true);

        $t_d = \WP_BETTERSTUDIO_TEST::text_doamin;
        echo '
        <table class="form-table">
	    <tbody>
	    <tr>
		<th scope="row">'.__('Redirect to', $t_d).'</th>
		<td>
		<input type="text" name="redirect_to" value="'.get_post_meta($post->ID, 'redirect_to', true).'" class="widefat" required>
        </td>
        </tr>
        <tr>
		<th scope="row">'.__('Redirect Type', $t_d).'</th>
		<td>
		<select name="type_redirect">
		    <option '.selected( $type_of_redirect, 301 , false).'>301</option>
		    <option '.selected( $type_of_redirect, 302 , false).'>302</option>
        </select>
        </td>
        </tr>
        </tbody>
        </table>
        ';
    }


    /*
     * Save MetaBox Url
     */
    public function Save_MetaBox( $post_id, $post )
    {
        global $wpdb;


      /*
       * Check User Not Permission
       */
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }


        /*
         * check Isset Post Requet
         */
        if ( ! isset( $_POST['redirect_to'] ) || ! wp_verify_nonce( $_POST['short_fields_security'], basename(__FILE__) ) ) {
            return $post_id;
        }

        /*
         * Update Or Add To Post Meta
         */
        $url = sanitize_text_field($_POST['redirect_to']);

        /*
         * Set default Domain Protocol if not valid
         */
        $protocol = "http";
        $protocol = apply_filters( 'wp_shortlink_change_default_protocol', $protocol );
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) $url = $protocol . "://" . $url;

        /*
        * Set default Domain extension
        */
        $extension = "com";
        $extension = apply_filters( 'wp_shortlink_change_default_domain_extension', $extension );
        if(!stristr($url, ".")) $url = $url.".".$extension;

        update_post_meta( $post_id, 'redirect_to', $url);
        update_post_meta( $post_id, 'type_redirect', $_POST['type_redirect'] );

    }
    
    
    /*
     * Remove Shortlink Detail in Deleting Complete post
     */
    public function Remove_Shortlink_Row( $postid  )
    {
        global $wpdb, $post_type;
        if ( $post_type == \WP_BETTERSTUDIO_TEST::post_type ) {
            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}shortlink WHERE post_id = %d", $postid));
        }
    }



    /*
     * Static MetaBox
     */
    public function static_metabox()
    {
        global $post;


        $data = array(
            __("Total View : ", \WP_BETTERSTUDIO_TEST::text_doamin) => number_format(Statistic::get()->get_link_static($post->ID, $type ="all")),
            __("Unique View : ", \WP_BETTERSTUDIO_TEST::text_doamin) => number_format(Statistic::get()->get_link_static($post->ID, $type ="unique")),
            __("Real View : ", \WP_BETTERSTUDIO_TEST::text_doamin) => number_format(Statistic::get()->get_link_static($post->ID, $type ="real")),
            __("Robot View : ", \WP_BETTERSTUDIO_TEST::text_doamin) => number_format(Statistic::get()->get_link_static($post->ID, $type ="robot")),
            __("Last Viewed : ", \WP_BETTERSTUDIO_TEST::text_doamin) => Statistic::get()->get_link_static($post->ID, $type ="last_view"),
        );

        /*
         * Apply filter To Show
         */
        $data = apply_filters( 'wp_shortlink_show_static_metabox', $data );

       echo '<table class="form-table static_report"><tbody>';
       foreach($data as $k => $v) {
            echo '
            <tr>
            <th scope="row">'.$k.'</th>
            <td>'.$v.'</td>
            </tr>
            ';
       }
        echo '</tbody></table> ';
        echo '<style>table.static_report tr th { line-height: 0.3 !important; }</style>';
    }

}