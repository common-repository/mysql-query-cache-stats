<?php 
// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

// delete plugin saved options
delete_option('md_widget_qcache'); 

// For site options in Multisite
delete_site_option('md_widget_qcache');  
 
// Drop a custom db table
// global $wpdb;
// $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mytable" );

?>