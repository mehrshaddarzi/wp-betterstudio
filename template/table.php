<?php
/**
 * Show Table List OF Short Link
 *
 * This template can be overriden by copying this file to your-theme/betterstudio/table.php
 *
 * @author 		Mehrshad Darzi
 * @package 	Shortlink Plugin Test
 * @version     1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Don't allow direct access
?>
<div class='shortlink_table'>
    <table>
        <tr>
            <?php
            /*
             * Show title Of Table
             */
            foreach($title as $title_key => $title_val) {
                echo '<th>'.$title_val.'</th>';
            }
            ?>
        </tr>

        <?php
            /*
             * Show Content Table
             */
            foreach($content as $content_item) {
                echo '<tr>';
                    foreach($content_item as $content_key => $content_val) {
                        echo '<td>'.$content_val.'</td>';
                    }
                echo '</tr>';
            }
        ?>
    </table>

    <!-- Show Pagination -->
    <div class="pagination"><?php echo $pagination; ?></div>

</div>



