<?php
/*
Plugin Name: Remote Database Backup
Plugin URI: http://www.bin-co.com/blog/2008/10/remote-database-backup-wordpress-plugin/
Description: Enables remote backuping of your wordpress database.
Author: Binny V A
Author URI: http://binnyva.com/
Version: 1.00.1
*/
require_once('wpframe.php');

/**
 * Add a new menu item under Manage
 */
add_action( 'admin_menu', 'remote_database_backup_add_menu_links' );
function remote_database_backup_add_menu_links() {
	global $wp_version;
	$view_level= 2;
	$page = 'edit.php';
	if($wp_version >= '2.7') $page = 'tools.php';
	
	add_submenu_page($page, t('DB Backups'), t('DB Backups'), $view_level, 'remote-database-backup/backup.php' );
}
