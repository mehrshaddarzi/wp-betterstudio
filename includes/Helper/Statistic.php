<?php
namespace Helper;

class Statistic
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
     * Get Number View Of Post
     */
    public function get_link_static($post_id, $type ="all")
    {
        global $wpdb;
        $table = $wpdb->prefix.'shortlink';

        /*
         * Get All View Post
         */
        if($type =="all") {
            $result = $wpdb->get_var("SELECT count(*) FROM `$table` WHERE `post_id` = $post_id");
        }

        /*
         * Get Unique View Post
         */
        if($type =="unique") {
            $result = count($wpdb->get_results("SELECT `ID` FROM `$table` WHERE `post_id` = $post_id GROUP BY `ip`"));
        }

        /*
         * Get Real Views
         */
        if($type =="real") {
            $result = $wpdb->get_var("SELECT count(*) FROM `$table` WHERE `post_id` = $post_id AND `type` = 1");
        }

        /*
         * Get Robots Views
         */
        if($type =="robot") {
            $result = $wpdb->get_var("SELECT count(*) FROM `$table` WHERE `post_id` = $post_id AND `type` = 2");
        }

        /*
         * Get Last Date time Views
         */
        if($type =="last_view") {
            $date_format = "Y/m/d H:i";

            /*
             * apply date Format
             */
            $date_format = apply_filters( 'wp_shortlink_change_dateformat', $date_format );
            $result = date_i18n( $date_format , strtotime( $wpdb->get_var("SELECT `date` FROM `$table` WHERE `post_id` = $post_id ORDER BY `ID` DESC LIMIT 1") ) );
        }


        return $result;
    }





}