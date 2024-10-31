<?php
/*
Plugin Name: MySQL query cache stats 
Description: Dashboard widget for MySQL query cache statistics
Author: Moris Dov
Plugin URI: https://wordpress.org/plugins/mysql-query-cache-stats
Author URI: https://profiles.wordpress.org/morisdov
Version: 1.0.4

*/
if ( ! defined('ABSPATH') ) {
	die( 'WP not found  invalid request' );
}

if ( is_admin() ) {
    add_action('wp_dashboard_setup', function () {
        if (current_user_can('manage_options') ){
            wp_add_dashboard_widget('dashboard_widget_qcache', 'MySQL Query cache stats', 'md_render_widget_qcache', 'md_configure_widget_qcache');
            add_action( 'admin_enqueue_scripts', 'md_enqueue_admin_script' );
        }
    });
}

function md_enqueue_admin_script( $hook ) {
    if ( 'index.php' == $hook ) {
        wp_register_style( 'md-widget-qcache-css', plugin_dir_url( __FILE__ ) . 'widget.css', false, '1.0' );
        wp_enqueue_style( 'md-widget-qcache-css' );
        wp_localize_script('jquery', 'md_widget_qcache', array(
            'ajax_url'   => admin_url('admin-ajax.php'),
            'nonce'      => wp_create_nonce('md-widget-qcache')
        ) );
    }
}

function md_render_widget_qcache() {
    try {
        if ( ! current_user_can('manage_options')) {
            return;
        }
		global $wpdb;
		$wpdb->show_errors(true);
		$start_time = microtime(true);
		$sql = "SELECT * FROM " . $wpdb->prefix . "options WHERE autoload='yes' ";
		$result = $wpdb->get_results($sql, ARRAY_A);
		$end_time = microtime(true);
		$wpdb->show_errors(false);
		
		$execution_time = ($end_time - $start_time) * 1000;  // Convert to milliseconds
		//$execution_time = number_format($execution_time, 1);  // Format to 1 decimal place
		$execution_time = number_format($execution_time);  // Format to 0 decimal place
		//error_log("Query took " . $execution_time . " milliseconds to execute");
		
        $myArr = [];
        $myArr['Options Autoload Query Time'] = $execution_time . ' ms';
		
        $sql = "SELECT 'Options Autoload Size', (SUM(LENGTH(option_name)) + SUM(LENGTH(option_value)) + (count(option_name)*32)) 
                AS 'value' FROM " . $wpdb->prefix . "options WHERE autoload='yes' 
                UNION SELECT variable_name, variable_value FROM information_schema.global_status WHERE variable_name like 'Q%'
                UNION SELECT variable_name, variable_value FROM information_schema.global_variables WHERE variable_name like 'query_cache_%'
                UNION SELECT 'Database Size', sum(DATA_LENGTH+INDEX_LENGTH)AS 'value' FROM information_schema.tables where TABLE_SCHEMA='". $wpdb->dbname ."'";
        $wpdb->show_errors(true);
        $results = $wpdb->get_results($sql, ARRAY_A);
        if (count($results) > 0 && empty($wpdb->last_error)) {
            foreach ($results as $result){
                $myArr[ucwords(strtolower(array_values($result)[0]))] = (is_numeric(array_values($result)[1]) ? number_format(array_values($result)[1]) : array_values($result)[1]);
            }
        } elseif ( ! empty($wpdb->last_error) ) {
            echo $wpdb->print_error();
        }
        unset($results);
        $wpdb->hide_errors();

        $myOrder = array('Queries','Qcache_total_blocks','Qcache_free_blocks','Query_cache_min_res_unit','Query_cache_wlock_invalidate');
        foreach ($myOrder as $order){
            if (array_key_exists($order , $myArr) ){
                unset($myArr[$order]);
            }
        }               
        $myOrder = array('Questions','Qcache_hits','Query_cache_type','Database Size','Query_cache_size','Qcache_free_memory','Query_cache_limit','Options Autoload Size','Options Autoload Query Time',
                        'Qcache_lowmem_prunes','Qcache_inserts','Qcache_queries_in_cache','Qcache_not_cached','Qcache_total_blocks');
        $tmp = [];
        foreach ($myOrder as $order){
            if (array_key_exists($order , $myArr) ){
                $tmp[$order] = $myArr[$order];
                unset($myArr[$order]);
            }
        }
        $myArr = array_merge($tmp,$myArr);

        $html = '<table id="md_qcache_table" class="md_qcache_table">';
        foreach ($myArr as $key => $value){
            $html .= '<tr><td>'. $key .'</td><td>'. $value;
        }
        $html .= '</table>';
        $html .= '<form method="post" action="'.admin_url().'">';
        $html .= '<p id="md_qcache_p" class="submit md_qcache_p">
                  <input type="submit" value="Refresh" class="button "></p> 
		          <input type="hidden" name="widget-qcache-refresh" value="true" />';
        $html .= '</form>';

        echo $html;

    } catch (exception $e) {
        error_log($e->getMessage());
    }
}

function md_configure_widget_qcache() {
    $defaults = array('items' => 2);
    $options = wp_parse_args( get_option( 'md_widget_qcache' ), $defaults );

    if (isset($_POST['submit'])) {
        //error_log('$_POST[submit] '. print_r($_POST, 1));
        if (isset($_POST['items']) && intval($_POST['items']) > 0) {
            $new_options['items'] = intval($_POST['items']);
        }
        if (! empty($new_options) && ! empty(array_diff($options, $new_options))) {
            update_option('md_widget_qcache', $new_options);
        }
    } elseif ( isset($_POST['reset']) ) {
        //error_log('$_POST[reset] '. print_r($_POST,1));
        global $wpdb;
        $wpdb->show_errors(true);
        try {
            if ("RESET QUERY CACHE" == $_POST['reset']) {
                $wpdb->get_var("RESET QUERY CACHE");
            } elseif ("FLUSH STATUS" == $_POST['reset']) {
                $wpdb->get_var("FLUSH STATUS");
            } elseif ("FLUSH TABLE STATISTICS" == $_POST['reset']) {
                $wpdb->get_var("FLUSH TABLE STATISTICS");
            }           
        } catch (exception $e) {
            error_log($e->getMessage());
        }
        $wpdb->show_errors(false);
	} else {
        ?>
        <!-- <p><label>Number of Items ?
            <input type="text" name="items" value="<?php echo esc_attr( $options['items'] ); ?>" /></label><br/>
            <label>Database Commands</label><br/>
        </p> -->
        <p>
            <input type="submit" name="reset" id="reset" class="button " value="RESET QUERY CACHE"><br/>
            <!-- <input type="submit" name="reset" id="reset" class="button " value="FLUSH STATUS"><br/>
            <input type="submit" name="reset" id="reset" class="button " value="FLUSH TABLE STATISTICS"><br/> -->
        </p>
        <?php    
    }
}

// add_filter( 'plugin_row_meta', 'donate_link', 10, 2 );
// function donate_link( $links, $file ) {
//     if ( plugin_basename( __FILE__ ) === $file ) {
//         $donate_link = '<a href="https://ko-fi.com/A236CEN/">Donate</a>';
//         $links[]     = $donate_link;
//     }
//     return $links;
// }