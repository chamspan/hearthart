<?php
require_once('wpframe.php');

//This is taken from remote-database-backup.php plugin - for compactability with that one.
$rand = substr( md5( md5( DB_PASSWORD ) ), -5 );
global $wpdbb_content_dir, $wpdbb_content_url, $wpdbb_plugin_dir;
$wpdbb_content_dir = ( defined('WP_CONTENT_DIR') ) ? WP_CONTENT_DIR : ABSPATH . 'wp-content';
$wpdbb_content_url = ( defined('WP_CONTENT_URL') ) ? WP_CONTENT_URL : get_option('siteurl') . '/wp-content';
$wpdbb_plugin_dir = ( defined('WP_PLUGIN_DIR') ) ? WP_PLUGIN_DIR : $wpdbb_content_dir . '/plugins';

if ( ! defined('WP_BACKUP_DIR') ) {
	define('WP_BACKUP_DIR', $wpdbb_content_dir . '/backup-' . $rand . '/');
}

if ( ! defined('WP_BACKUP_URL') ) {
	define('WP_BACKUP_URL', $wpdbb_content_url . '/backup-' . $rand . '/');
}

class Remote_DB_Backup {
	var $sql = '';
	var $backup_filename;
	var $tables_to_backup;
	var $core_tables;
	var $other_tables;
	var $errors = array();
	var $status = 'success';
	
	function Remote_DB_Backup() {
		global $wpdb;
		
		//Check necessary stuff...
		if ( ! file_exists(WP_BACKUP_DIR) && ! @mkdir(WP_BACKUP_DIR) ) 
			$this->error('Backup directory is not created. Please create the directory('.WP_BACKUP_DIR.') with 777 premission using FTP.');
		elseif ( !is_writable(WP_BACKUP_DIR) && ! @chmod(WP_BACKUP_DIR, 0777) ) 
			$this->error('Backup directory is not writeable. Please give the directory "'.WP_BACKUP_DIR.'" 777 permission using FTP.');
		elseif (!file_exists(WP_BACKUP_DIR . 'index.php')) touch(WP_BACKUP_DIR . 'index.php');
		
		if(!function_exists('gzopen')) $this->error("Cannot backup to gzip format. Falling back to less efficient plain text format.", 'warning');
		
		//Initalizations
		$table_prefix = ( isset( $table_prefix ) ) ? $table_prefix : $wpdb->prefix;
		$datum = date("Ymd_B");
		$this->backup_filename = DB_NAME . "_$table_prefix$datum.sql";
		if(function_exists('gzopen')) $this->backup_filename .= '.gz';
		
		//Get the list of tables
		$core_tables = array_map(create_function('$a', 'return "'.$wpdb->prefix.'$a";'), $wpdb->tables);
		
		// Get complete db table list	
		$all_tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);
		$all_tables = array_map(create_function('$a', 'return $a[0];'), $all_tables);
		// Get list of WP tables that actually exist in this DB (for 1.6 compat!)
		$this->core_tables = array_intersect($core_tables, $all_tables);
		// Get list of non-WP tables
		$this->other_tables = array_diff($all_tables, $this->core_tables);
		
		$this->tables_to_backup = $this->core_tables;
	}
	
	function error($message, $level='fatal') {
		if($level == 'fatal') $this->status = 'failed';
		$this->errors[] = t($message);
	}
	
	function appendSQL($query) {
		$this->sql .= $query;
	}
	
	function backup() {
		global $table_prefix, $wpdb;
		
		$tables = $this->core_tables;
		if($_REQUEST['other_tables']) {
			foreach($_REQUEST['other_tables'] as $table_name) {
				$tables[] = $table_name;
			}
		}
				
		//Set options
		$exc_revisions = (array) $_REQUEST['exclude-revisions'];
		$exc_spam = (array) $_REQUEST['exclude-spam'];
		update_option('wp_db_backup_excs', array('revisions' => $exc_revisions, 'spam' => $exc_spam));
		
		//Using same format as WordPress Database Backup plugin
		$this->appendSQL("# " . t('WordPress MySQL database backup') . "\n");
		$this->appendSQL("#\n");
		$this->appendSQL("# " . sprintf(t('Generated: %s'),date("l j. F Y H:i T")) . "\n");
		$this->appendSQL("# " . sprintf(t('Hostname: %s'),DB_HOST) . "\n");
		$this->appendSQL("# " . sprintf(t('Database: %s'),DB_NAME) . "\n");
		$this->appendSQL("# --------------------------------------------------------\n");
		

		foreach ($tables as $table) {
			if(!trim($table)) continue;
			// Increase script execution time-limit to 15 min for every table.
			if ( !ini_get('safe_mode')) @set_time_limit(15*60);
			
			$data_query = '';
			if($table == $wpdb->comments and isset($_REQUEST['exclude-spam'])) $data_query = "SELECT * FROM $table WHERE comment_approved != 'spam'";
			else if($table == $wpdb->posts and isset($_REQUEST['exclude-revisions'])) $data_query = "SELECT * FROM $table WHERE post_type != 'revision'";
			
			$this->appendSQL($this->backup_table($table, $data_query));
		}
				
		$this->saveBackup();
		
		if (count($this->errors)) {
			return false;
		} else {
			return $this->backup_filename;
		}
	}
	
	/**
	 * Taken from WordPress Database Backup plugin
	 * Taken partially from phpMyAdmin and partially from
	 * Alain Wolf, Zurich - Switzerland
	 * Website: http://restkultur.ch/personal/wolf/scripts/db_backup/
	 * Modified by Scott Merrill (http://www.skippy.net/) 
	 * to use the WordPress $wpdb object
	 */
	function backup_table($table, $data_query='') {
		global $wpdb;

		$table_structure = $wpdb->get_results("DESCRIBE $table");
		if (! $table_structure) {
			$this->error('Error getting table details: '.$table, 'warning');
			return false;
		}
	
		// Add SQL statement to drop existing table
		$this->appendSQL("\n\n");
		$this->appendSQL("#\n");
		$this->appendSQL("# " . sprintf(t('Delete any existing table %s'),$this->backquote($table)) . "\n");
		$this->appendSQL("#\n");
		$this->appendSQL("\n");
		$this->appendSQL("DROP TABLE IF EXISTS " . $this->backquote($table) . ";\n");
		
		// Table structure
		// Comment in SQL-file
		$this->appendSQL("\n\n");
		$this->appendSQL("#\n");
		$this->appendSQL("# " . sprintf(t('Table structure of table %s'),$this->backquote($table)) . "\n");
		$this->appendSQL("#\n");
		$this->appendSQL("\n");
		
		$create_table = $wpdb->get_results("SHOW CREATE TABLE $table", ARRAY_N);
		if (false === $create_table) {
			$err_msg = sprintf('Error with SHOW CREATE TABLE for %s.', $table);
			$this->error($err_msg, 'warning');
			$this->appendSQL("#\n# $err_msg\n#\n");
		}
		$this->appendSQL($create_table[0][1] . ' ;');
		
		if (false === $table_structure) {
			$err_msg = sprintf('Error getting table structure of %s', $table);
			$this->error($err_msg, 'warning');
			$this->appendSQL("#\n# $err_msg\n#\n");
		}
	
		// Comment in SQL-file
		$this->appendSQL("\n\n");
		$this->appendSQL("#\n");
		$this->appendSQL('# ' . sprintf(t('Data contents of table %s'),$this->backquote($table)) . "\n");
		$this->appendSQL("#\n");
			
			
		$defs = array();
		$ints = array();
		foreach ($table_structure as $struct) {
			if ( (0 === strpos($struct->Type, 'tinyint')) ||
				(0 === strpos(strtolower($struct->Type), 'smallint')) ||
				(0 === strpos(strtolower($struct->Type), 'mediumint')) ||
				(0 === strpos(strtolower($struct->Type), 'int')) ||
				(0 === strpos(strtolower($struct->Type), 'bigint')) ) {
					$defs[strtolower($struct->Field)] = ( null === $struct->Default ) ? 'NULL' : $struct->Default;
					$ints[strtolower($struct->Field)] = "1";
			}
		}
		
		if ( !ini_get('safe_mode')) @set_time_limit(15*60);
		
		if(!$data_query) $data_query = "SELECT * FROM $table ";
		$table_data = $wpdb->get_results($data_query, ARRAY_A);

		$entries = 'INSERT INTO ' . $this->backquote($table) . ' VALUES (';	
		//    \x08\\x09, not required
		$search = array("\x00", "\x0a", "\x0d", "\x1a");
		$replace = array('\0', '\n', '\r', '\Z');
		if($table_data) {
			foreach ($table_data as $row) {
				$values = array();
				foreach ($row as $key => $value) {
					if ($ints[strtolower($key)]) {
						// make sure there are no blank spots in the insert syntax,
						// yet try to avoid quotation marks around integers
						$value = ( null === $value || '' === $value) ? $defs[strtolower($key)] : $value;
						$values[] = ( '' === $value ) ? "''" : $value;
					} else {
						$values[] = "'" . str_replace($search, $replace, $this->sql_addslashes($value)) . "'";
					}
				}
				$this->appendSQL(" \n" . $entries . implode(', ', $values) . ') ;');
			}
		}
		
		// Create footer/closing comment in SQL-file
		$this->appendSQL("\n");
		$this->appendSQL("#\n");
		$this->appendSQL("# " . sprintf(t('End of data contents of table %s'),$this->backquote($table)) . "\n");
		$this->appendSQL("# --------------------------------------------------------\n");
		$this->appendSQL("\n");
	} // end backup_table()
	
	/**
	 * Better addslashes for SQL queries. Taken from phpMyAdmin.
	 */
	function sql_addslashes($a_string = '', $is_like = false) {
		if ($is_like) $a_string = str_replace('\\', '\\\\\\\\', $a_string);
		else $a_string = str_replace('\\', '\\\\', $a_string);
		return str_replace('\'', '\\\'', $a_string);
	} 

	/**
	 * Add backquotes to tables and db-names in SQL queries. Taken from phpMyAdmin.
	 */
	function backquote($a_name) {
		if (!empty($a_name) && $a_name != '*') {
			if (is_array($a_name)) {
				$result = array();
				reset($a_name);
				while(list($key, $val) = each($a_name)) 
					$result[$key] = '`' . $val . '`';
				return $result;
			} else {
				return '`' . $a_name . '`';
			}
		} else {
			return $a_name;
		}
	}
	
	function saveBackup() {
		$backup_file = WP_BACKUP_DIR . $this->backup_filename;
	
		if(function_exists('gzopen')) $this->fp = gzopen($backup_file, 'w');
		else $this->fp = fopen($backup_file, 'w');
		if(!$this->fp) {
			$this->error(t('Could not open the backup file for writing!'));
			return false;
		}
		
		if(function_exists('gzopen')) {
			gzwrite($this->fp, $this->sql);
			gzclose($this->fp);
		} else {
			fwrite($this->fp, $this->sql);
			fclose($this->fp);
		}
		
		return WP_BACKUP_URL . $this->backup_filename;
	}

	function showErrors() {
		if(!$this->errors) return;
		?>
		<div class="updated wp-database-backup-updated error"><p><strong><?php e('The following errors were reported:') ?></strong></p>
			<?php foreach($this->errors as $err) {
			print '<p>' . t($err) . '</p>';
			} ?>
		</div>
	<?php }
	
}

$remote_db_backup = new Remote_DB_Backup();
?>
<h2><?php e('WordPress Database Backup') ?></h2>

<?php

$remote_db_backup->showErrors();

if($_REQUEST['action'] == 'Backup') {
	$remote_db_backup->backup();
	
	if($remote_db_backup->status == 'success') { ?>
		<h4><?php e("Backup Completed") ?></h4>
		<a id="download-url" href="<?=WP_BACKUP_URL . $remote_db_backup->backup_filename?>"><?php e("Download Database Dump") ?></a>
	<?php
	}
} else {
?>

<style type="text/css">
.tables-list {
	width:45%;
	float:left;
}
.tables-list ul {
	list-style:none;
}
</style>

<form method="post" action="">

<fieldset class="options">
<div class="tables-list core-tables">
<h4><?php e('These core WordPress tables will be backed up:') ?></h4><ul><?php

$excs = (array) get_option('wp_db_backup_excs');
foreach ($remote_db_backup->core_tables as $table) {
	if ( $table == $wpdb->comments ) {
		$checked = ( is_array($excs['spam'] ) && in_array($table, $excs['spam']) ) ? ' checked=\'checked\'' : '';
		echo "<li><code>$table</code> <span class='instructions'> <input type='checkbox' id='exclude-spam' name='exclude-spam[]' value='$table' $checked /> <label for='exclude-spam'>" . t('Exclude spam comments') . '</label></span></li>';
	} elseif ( function_exists('wp_get_post_revisions') && $table == $wpdb->posts ) {
			$checked = ( is_array($excs['revisions'] ) && in_array($table, $excs['revisions']) ) ? ' checked=\'checked\'' : '';
		echo "<li><code>$table</code> <span class='instructions'> <input type='checkbox' id='exclude-revisions' name='exclude-revisions[]' value='$table' $checked /> <label for='exclude-revisions'>" . t('Exclude post revisions') . '<label></span></li>';
	} else {
		echo "<li><code>$table</code></li>";
	}
}
?></ul>
</div>

<div class="tables-list extra-tables" id="extra-tables-list">
<?php if (count($remote_db_backup->other_tables) > 0) { ?>
	<h4><?php e('Backup Extra Tables(these may be needed for your plugins):'); ?></h4>
	<ul>
	<?php foreach ($remote_db_backup->other_tables as $table) { ?>
		<li><label><input type="checkbox" name="other_tables[]" value="<?php echo $table; ?>" /> <code><?php echo $table; ?></code></label>
	<?php  } ?></ul>
<?php } ?>
</div>
</fieldset><br />
<input type="submit" value="<?php e("Backup") ?>" name="action" />
</form>
<?php } ?>
