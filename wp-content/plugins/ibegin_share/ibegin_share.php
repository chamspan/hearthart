<?php
/*
Plugin Name: iBegin Share
Plugin URI: http://www.ibegin.com/labs/share/
Description: Adds a "Share" button to your posts.
Author: David Cramer
Version: 2.6 (1608)
Author URI: http://www.ibegin.com/labs/
*/

define('IBEGIN_SHARE_TABLE_PREFIX', $wpdb->prefix);

define('IBEGIN_SHARE_STATE_DEFAULT', 0);
define('IBEGIN_SHARE_STATE_CONTENTPAGE', 1);

// button
define('IBEGIN_SHARE_LINK_STYLE_DEFAULT', 1);
// blue
define('IBEGIN_SHARE_LINK_SKIN_DEFAULT', 1);
define('IBEGIN_SHARE_SKIN_DEFAULT', 1);

$ibegin_share_link_styles = array(
    1   =>  'button',
    2   =>  'text',
);
$ibegin_share_link_styles_captions = array(
    1   =>  'Button',
    2   =>  'Text Link',
);
$ibegin_share_link_skins = array(
    1   =>  'blue',
    2   =>  'red',
    3   =>  'green',
    4   =>  'orange',
);
$ibegin_share_link_skins_captions = array(
    1   =>  'Blue/Default',
    2   =>  'Red',
    3   =>  'Green',
    4   =>  'Orange',
);
$ibegin_share_skins =& $ibegin_share_link_skins;
$ibegin_share_skins_captions =& $ibegin_share_link_skins_captions;

$ibegin_share_state = IBEGIN_SHARE_STATE_DEFAULT;

$ibegin_share_options = array(
    'ibegin_share_add_to_post'      => __('Add iBegin Share to posts.', 'ibegin_share'),
    'ibegin_share_add_to_page'      => __('Add iBegin Share to pages.', 'ibegin_share'),
    'ibegin_share_add_to_feed'      => __('Add iBegin Share to feeds.', 'ibegin_share'),
    'ibegin_share_enable_context'   => __('Allow the use of [ibeginshare] in posts and pages.', 'ibegin_share'),
    'ibegin_share_log_stats'        => __('Enable statistics logging.', 'ibegin_share'),
    'ibegin_share_link_type'        => __('Share Link Style:', 'ibegin_share'),
    'ibegin_share_link_skin'        => __('Share Link Skin:', 'ibegin_share'),
    'ibegin_share_skin'             => __('Share Box Skin:', 'ibegin_share'),
);
$ibegin_share_options_default_values = array(
    'ibegin_share_add_to_post'      => '1',
    'ibegin_share_add_to_page'      => '0',
    'ibegin_share_add_to_feed'      => '1',
    'ibegin_share_enable_context'   => '1',
    'ibegin_share_link_type'        => IBEGIN_SHARE_LINK_STYLE_DEFAULT,
    'ibegin_share_link_skin'        => IBEGIN_SHARE_LINK_SKIN_DEFAULT,
    'ibegin_share_skin'             => IBEGIN_SHARE_SKIN_DEFAULT,
    'ibegin_share_log_stats'        => '1',
);
$ibegin_share_options_choices = array(
    'ibegin_share_add_to_post'      => 1,
    'ibegin_share_add_to_page'      => 1,
    'ibegin_share_add_to_feed'      => 1,
    'ibegin_share_enable_context'   => 1,
    'ibegin_share_link_type'        => $ibegin_share_link_styles_captions,
    'ibegin_share_link_skin'        => $ibegin_share_link_skins_captions,
    'ibegin_share_skin'             => $ibegin_share_skins_captions,
    'ibegin_share_log_stats'        => 1,
);
$ibegin_share_plugins = array(
    'bookmarks' =>  'Bookmarks',
    'email'     =>  'Email',
    'mypc'      =>  'My Computer',
    'printer'   =>  'Printer',
);
$ibegin_stats_times = array(
    'all'       => 'All Time',
    'today'     => 'Today',
    'week'      => '7 Days',
    'month'     => '30 Days',
);
$ibegin_stats_times_values = array(
    'all'       => 0,
    'today'     => time()-60*60*24,
    'week'      => time()-60*60*24*7,
    'month'     => time()-60*60*24*30,
);

$ibegin_share_path = get_settings('siteurl') . '/wp-content/plugins/' . basename(rtrim(dirname(__FILE__), '/'));


/**
 * Adds/updates the options on plug-in activation.
 */
function iBeginShare_Install()
{
    global $ibegin_share_options_default_values, $ibegin_share_plugins;
    foreach ($ibegin_share_options_default_values as $option=>$value)
    {
        if (get_option($option) == '') update_option($option, $value);
    }
    foreach (array_keys($ibegin_share_plugins) as $plugin)
    {
        $option = 'ibegin_share_plugins_enable_' . $plugin;
        if (get_option($option) == '') update_option($option, '1');
    }
    // read in the sql database
    iBeginShare_InstallDatabase();
}
/**
 * Initializes the database if it's not already present.
 */
function iBeginShare_InstallDatabase()
{
    global $wpdb;
    
    $wpdb->query("CREATE TABLE IF NOT EXISTS `".IBEGIN_SHARE_TABLE_PREFIX."log` (
      `id` int(11) NOT NULL auto_increment,
      `action` varchar(32) NOT NULL,
      `label` varchar(64) default NULL,
      `link` varchar(200) NOT NULL,
      `ipaddress` int(11) NOT NULL,
      `agent` varchar(128) NOT NULL,
      `timestamp` int(11) NOT NULL,
      PRIMARY KEY  (`id`),
      KEY `action` (`action`,`label`),
      KEY `action_2` (`link`,`action`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
}
if (isset($_GET['activate']) && $_GET['activate'] == 'true') {
    iBeginShare_Install();
}

// Includes CSS/JS.
add_action('wp_head', 'iBeginShare_Header');

if (get_option('ibegin_share_add_to_post') == '1' || get_option('ibegin_share_add_to_page') == '1')
{
    // Adds share button to content.
    add_action('the_content', 'iBeginShare_Widget');
}

// Admin options.
add_action('admin_menu', 'iBeginShare_Menu');

// Handles requests and option changes.
add_action('init', 'iBeginShare_Pages', 9999);

if (get_option('ibegin_share_enable_context') == '1')
{
    // Adds the [ibeginshare] context tag.
    add_filter('the_content', 'iBeginShare_ContextFilter');
}

/**
 * Includes iBegin Share's CSS and JavaScript files in the header.
 */
function iBeginShare_Header()
{
    global $ibegin_share_plugins, $ibegin_share_path;
    
    echo '<link rel="stylesheet" href="' . $ibegin_share_path . '/share/share/share.css" media="screen" type="text/css"/>';
    echo '<script type="text/javascript" src="' . $ibegin_share_path . '/share/share/share.js"></script>';
    if (get_option('ibegin_share_log_stats'))
    {
        echo '<script type="text/javascript">iBeginShare.script_handler = "' . get_option('siteurl') . '/?ibsa=log";</script>';
    }
    $disabled = array();
    foreach (array_keys($ibegin_share_plugins) as $plugin)
    {
        $option = 'ibegin_share_plugins_enable_'.$plugin;
        if (!get_option($option)) $disabled[] = 'iBeginShare.plugins.builtin.'.$plugin;
    }
    if (count($disabled))
    {
        echo '<script type="text/javascript">iBeginShare.plugins.unregister('.implode(',', $disabled).');</script>';
    }
}

/**
 * Handles the [ibeginshare] template filter.
 * @param {string} $content HTML content.
 * @param {string} $url URL to pass as link parameter.
 * @param {string} $title Title to pass as title parameter.
 * @param {string} $content_url Content url to pass as content parameter.
 */
function iBeginShare_ContextFilter($content, $title=null, $url=null, $content_url=null)
{
    $content = str_replace('[ibeginshare]', iBeginShare__renderLink($url, $title, $content_url, null, null, null, true), $content);
    return $content;
}

/**
 * Adds an iBegin Share button to the content.
 * @param {string} $content HTML Content.
 * @return {string} Altered HTML Content.
 */
function iBeginShare_Widget($content)
{
    global $ibegin_share_state;

    // If we're not in the default state don't draw the button
    if ($ibegin_share_state != IBEGIN_SHARE_STATE_DEFAULT) return $content;
    
    // Only draw the button if it's enabled
    if (is_feed())
    {
        if (!get_option('ibegin_share_add_to_feed')) return $content;
        return $content.'<p>'.iBeginShare_StaticLink(null, null, null, true).'</p>';
    }
    elseif ((is_page() && get_option('ibegin_share_add_to_page')) || (!is_page() && get_option('ibegin_share_add_to_post')))
    {
        return $content.'<p>'.iBeginShare__renderLink(null, null, null, null, null, null, true).'</p>';
    }
    else {
        return $content;
    }
}
/**
 * Creates the share text link. All arguments are optional.
 * @param {string} $url URL to pass as link parameter.
 * @param {string} $title Title to pass as title parameter.
 * @param {string} $content_url Content url to pass as content parameter.
 * @param {bool} $return_value If this is set it will return the output instead of printing.
 */
function iBeginShare_TextLink($url=null, $title=null, $content_url='', $return_value=false)
{
    return iBeginShare__renderLink($url, $title, $content_url, 2, null, null, $return_value);
}
/**
 * Creates the share button. All arguments are optional.
 * @param {string} $url URL to pass as link parameter.
 * @param {string} $title Title to pass as title parameter.
 * @param {string} $content_url Content url to pass as content parameter.
 * @param {bool} $return_value If this is set it will return the output instead of printing.
 */
function iBeginShare_Button($url=null, $title=null, $content_url='', $return_value=false)
{
    return iBeginShare__renderLink($url, $title, $content_url, 1, null, null, $return_value);
}

/**
 * Creates a static share link. Used in for feeds.
 * @param {string} $url URL to pass as link parameter.
 * @param {string} $title Title to pass as title parameter.
 * @param {string} $content_url Content url to pass as content parameter.
 * @param {bool} $return_value If this is set it will return the output instead of printing.
 */
function iBeginShare_StaticLink($url=null, $title=null, $content_url='', $return_value=false)
{
    global $post;
    
    if ($post)
    {
        if (!$title && !$url) $content_url = get_option('siteurl') . '/?ibsa=get_content&id=' . $post->ID;
        if (!$title) $title = get_the_title();
        if (!$url) $url = get_permalink($post->ID);
    }
    else
    {
        if (!$title) $title = get_option('blogname');
        if (!$url) $url = get_option('siteurl');
    }
    $share_link = get_option('siteurl') . '/?ibsa=share&id=' . $post->ID;
    
    $output = '<a href="'.$share_link.'" id="share-link-'.$id.'">Share</a>';

    if ($return_value) return $output;
    else echo $output;
}

/**
 * The private method which handles rendering all share links.
 * @param {string} $url URL to pass as link parameter.
 * @param {string} $title Title to pass as title parameter.
 * @param {string} $content_url Content url to pass as content parameter.
 * @param {bool} $return_value If this is set it will return the output instead of printing.
 */
function iBeginShare__renderLink($url=null, $title=null, $content_url='', $link_style=null, $link_skin=null, $box_skin=null, $return_value=false)
{
    global $post, $ibegin_share_link_skins, $ibegin_share_link_styles, $ibegin_share_skins;

    if ($post)
    {
        if (!$title && !$url) $content_url = get_option('siteurl') . '/?ibsa=get_content&id=' . $post->ID;
        if (!$title) $title = get_the_title();
        if (!$url) $url = get_permalink($post->ID);
    }
    else
    {
        if (!$title) $title = get_option('blogname');
        if (!$url) $url = get_option('siteurl');
    }
    
    $id = rand(0,100000000000);
        
    $title = str_replace('\'', "\'", htmlspecialchars($title));
    $url = str_replace('\'', "\'", $url);
    if ($content_url) $content_url = str_replace('\'', "\'", $content_url);
    
    $link_skin = $ibegin_share_link_skins[$link_skin ? $link_skin : get_option('ibegin_share_link_skin')];
    $link_style = $ibegin_share_link_styles[$link_style ? $link_style : get_option('ibegin_share_link_type')];
    $box_skin = $ibegin_share_skins[$box_skin ? $box_skin : get_option('ibegin_share_skin')];
    $share_link = get_option('siteurl') . '/?ibsa=share&id=' . $post->ID;
    
    
    $output = array();
    $output[] = '<span class="share-link-wrapper share-link-'.$link_style.' share-link-'.$link_style.'-'.$link_skin.'">';
    $output[] = '<a href="'.$share_link.'" class="share-link" id="share-link-'.$id.'" onclick="iBeginShare.handleLink(event);return false;">Share</a>';
    $output[] = '<script type="text/javascript">';
    $output[] = 'var el = document.getElementById(\'share-link-'.$id.'\');';
    $output[] = 'el.params = {';
    $output[] = 'title: \'' . str_replace("'", "\'", $title) . '\', ';
    $output[] = 'link: \'' . str_replace("'", "\'", $url) . '\', ';
    $output[] = 'skin: \'' . str_replace("'", "\'", $box_skin) . '\'';
    if ($content_url) $output[] = ', content: \'' . str_replace("'", "\'", $content_url) . '\'';
    $output[] = '};';
    $output[] = '</script>';
    $output[] = '</span>';
    
    $output = implode('', $output);
        
    if ($return_value) return $output;
    else echo $output;
}
/**
 * Renders the stats page in Dashboard->iBegin Share Stats
 */
function iBeginShare_StatsPage()
{
    global $wpdb, $ibegin_stats_times_values, $ibegin_stats_times, $ibegin_share_path;
    if ($_GET['fy'] && $_GET['ty'])
    {
        // using date range
        $start = mktime(0, 0, 0, $_GET['fm'] ? $_GET['fm'] : 0, $_GET['fd'] ? $_GET['fd'] : 0, $_GET['fy']);
        $end = mktime(0, 0, 0, $_GET['tm'] ? $_GET['tm'] : 0, $_GET['td'] ? $_GET['td'] : 0, $_GET['ty']);
        if ($_GET['td']) $end += 60*60*24;
        $from = array($_GET['fm'], $_GET['fd'], $_GET['fy']);
        $to = array($_GET['tm'], $_GET['td'], $_GET['ty']);
    }
    else
    {
        $time = $_GET['time'];
        if (!array_key_exists($time, $ibegin_stats_times)) $time = 'all';
        $start = $ibegin_stats_times_values[$time];
        $end = time();
        if ($start == 0) $from = array();
        else $from = explode('/', date('n/j/Y', $start));
        $to = explode('/', date('n/j/Y', $end));
    }

    $months_array = array();
    for ($i=1; $i<=12; $i++)
    {
        $months_array[$i] = date('F', mktime(0, 0, 0, $i));
    }
    list($current_month, $current_day, $current_year) = explode('/', date('F/j/Y'));

    ob_start();
    $result = $wpdb->get_row(sprintf("SELECT COUNT(*) as `total` FROM `".IBEGIN_SHARE_TABLE_PREFIX."log` WHERE `timestamp` > '%d' AND `timestamp` < '%d'", $start, $end));
    if ($result)
    {
        $total = $result->total;
    }
    else
    {
        $total = 0;
    }
    $total = $total[0];
    ?>
    <div class="wrap">
        <script type="text/javascript">
        function ibsToggleDate() {
            var el = document.getElementById('timestampdiv');
            if (el.style.display == 'block') el.style.display = 'none';
            else el.style.display = 'block';
        }
        </script>
        <link rel="stylesheet" href="<?php echo $ibegin_share_path; ?>/dashboard.css" type="text/css" />
        <link rel="stylesheet" href="css/dashboard.css?version=2.5" type="text/css" />
        <div class="stats_selector"><div class="selector">
            <p><strong>View Stats:</strong> <?php
            $i = 0;
            foreach ($ibegin_stats_times as $key=>$value)
            {
                if ($i != 0) echo ' | ';
                if ($key == $time) echo '<strong>'.htmlspecialchars($value).'</strong>';
                else echo '<a href="?page=ibegin-share-stats&amp;time='.$key.'">'.htmlspecialchars($value).'</a>';
                $i += 1;
            }
            ?> | <span id="date_wrap"><a href="javascript:ibsToggleDate();">Select Dates</a><form id="timestampdiv" method="get" action=".">
                    <input type="hidden" name="page" value="ibegin-share-stats" />
                    <label>From:</label> <select name="fm"><?php
                    foreach ($months_array as $key=>$month_name)
                    {
                        echo '<option value="'.$key.'"';
                        if ($from[0] == $key || (!$from[0] && $month_name == $current_month)) echo ' selected="selected"';
                        echo '>'.$month_name.'</option>';
                    }
                    ?></select>, <input type="text" autocomplete="off" name="fd" maxlength="2" size="2" value="<?php echo $from[1] ? $from[1] : $current_day; ?>" class="text" /> <input type="text" name="fy" maxlength="5" size="4" value="<?php echo $from[2] ? $from[2] : $current_year; ?>" class="text"/><br />
                    <label>To:</label> <select name="tm"><?php
                    foreach ($months_array as $key=>$month_name)
                    {
                        echo '<option value="'.$key.'"';
                        if ($to[0] == $key || (!$to[0] && $month_name == $current_month)) echo ' selected="selected"';
                        echo '>'.$month_name.'</option>';
                    }
                    ?></select>, <input type="text" autocomplete="off" name="td" maxlength="2" size="2" value="<?php echo $to[1] ? $to[1] : $current_day; ?>" class="text" /> <input type="text" name="ty" maxlength="5" size="4" value="<?php echo $to[2] ? $to[2] : $current_year; ?>" class="text"/><br />
                    <div style="text-align: right; margin-top: 5px;"><input type="submit" value="Show Me" class="button" /></div>
                </form></span>
            </p>
        </div>
        <h2>iBegin Share Statistics</h2></div>
        <?php if ($total) { ?>
            <div class='dashboard-widget-holder'>
        		<div class='dashboard-widget'>
        			<h3 class='dashboard-widget-title'><span>Overview</span><br class='clear' /></h3>
        			<div class='dashboard-widget-content'>
        				<table class="stats" width="100%" cellspacing="3" cellpadding="3">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th style="width: 50px;" class="tc">Hits</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $results = $wpdb->get_results(sprintf("SELECT `action`, COUNT(*) as `total` FROM `".IBEGIN_SHARE_TABLE_PREFIX."log` WHERE `timestamp` > '%d' AND `timestamp` < '%d' GROUP BY `action` ORDER BY `total` DESC", $start, $end));
                            foreach ($results as $row)
                            {
                                $percent = round($row->total/$total*100);
                                ?><tr>
                                    <td class="progress_bar"><strong style="width: <?php echo $percent; ?>%"><?php echo htmlspecialchars(ucfirst($row->action)); ?></strong></td>
                                    <td class="tc"><?php echo $row->total; ?><br /><small>(<?php echo $percent; ?>%)</small></td>
                                </tr><?php
                            }
                            ?>
                            </tbody>
                        </table>
        		    </div>
        	    </div>
    	    </div>
            
            
            <div class='dashboard-widget-holder'>
        		<div class='dashboard-widget'>
        			<h3 class='dashboard-widget-title'><span>Top Pages</span><br class='clear' /></h3>
        			<div class='dashboard-widget-content'>
        				<table class="stats" width="100%" cellspacing="3" cellpadding="3">
                            <thead>
                                <tr>
                                    <th>Page</th>
                                    <th style="width: 50px;" class="tc">Hits</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $results = $wpdb->get_results(sprintf("SELECT `link`, COUNT(*) as `total` FROM `".IBEGIN_SHARE_TABLE_PREFIX."log` WHERE `timestamp` > '%d' AND `timestamp` < '%d' GROUP BY `link` ORDER BY `total` DESC LIMIT 0, 10", $start, $end));
                            foreach ($results as $row)
                            {
                                $percent = round($row->total/$total*100);
                                ?><tr>
                                    <td class="progress_bar"><strong style="width: <?php echo $percent; ?>%"><?php echo ($row->link ? '<a href="'.$row->link.'">'.htmlspecialchars(wordwrap(str_replace(get_option('siteurl'), '', $row->link), 30, ' ', true)).'</a>' : '<em>No Link</em>'); ?></strong></td>
                                    <td class="tc"><?php echo $row->total; ?><br /><small>(<?php echo $percent; ?>%)</small></td>
                                </tr><?php
                            }
                            ?>
                            </tbody>
                        </table>
        		    </div>
        	    </div>
    	    </div>
            <br class="clear"/><br />
            <h2>Plugin Usage</h2>
            <?php
            $results = $wpdb->get_results(sprintf("SELECT `action`, COUNT(*) as `total` FROM `".IBEGIN_SHARE_TABLE_PREFIX."log` WHERE `timestamp` > '%d' AND `timestamp` < '%d' GROUP BY `action` ORDER BY `total` DESC", $start, $end));
            foreach ($results as $row)
            {
                $sresults = $wpdb->get_results(sprintf("SELECT `label`, COUNT(*) as `total` FROM `".IBEGIN_SHARE_TABLE_PREFIX."log` WHERE `timestamp` > '%d' AND `timestamp` < '%d' AND `action` = '%s' GROUP BY `label` ORDER BY `total` DESC", $start, $end, $wpdb->escape($row->action)));
                if (count($sresults) === 1 && empty($sresults[0]->label)) continue;
                ?>
                <div class='dashboard-widget-holder'>
            		<div class='dashboard-widget'>
            			<h3 class='dashboard-widget-title'><span><?php echo htmlspecialchars(ucfirst($row->action)); ?></span><br class='clear' /></h3>
            			<div class='dashboard-widget-content'>
            				<table class="stats" width="100%" cellspacing="3" cellpadding="3">
                                <thead>
                                    <tr>
                                        <th>Label</th>
                                        <th style="width: 50px;" class="tc">Hits</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                
                                foreach ($sresults as $srow)
                                {
                                    $percent = round($srow->total/$row->total*100);
                                    ?><tr>
                                        <td class="progress_bar"><strong style="width: <?php echo $percent; ?>%"><?php echo ($srow->label ? htmlspecialchars($srow->label) : '<em>Default Action</em>'); ?></strong></td>
                                        <td class="tc"><?php echo $srow->total; ?><br /><small>(<?php echo $percent; ?>%)</small></td>
                                    </tr><?php
                                }
                                ?>
                                </tbody>
                            </table>
            		    </div>
            	    </div>
        	    </div>
                <?php
            }
            ?>
            <br class='clear' />
        <?php } else { ?>
            <p>There are no statistics available for these dates.</p>
        <?php } ?>
        
    </div>
    <?php
    ob_end_flush();
}
/**
 * Renders the options form in Plugins->iBegin Share
 */
function iBeginShare_OptionsForm()
{
    global $ibegin_share_options, $ibegin_share_options_choices, $ibegin_share_plugins;
    
    if (isset($_POST['save']))
    {
        foreach (array_keys($ibegin_share_options) as $option)
        {
            if (!empty($_POST[$option])) update_option($option, $_POST[$option]);
            else update_option($option, '0');
        }
        foreach (array_keys($ibegin_share_plugins) as $plugin)
        {
            $option = 'ibegin_share_plugins_enable_'.$plugin;
            if (!empty($_POST[$option])) update_option($option, $_POST[$option]);
            else update_option($option, '0');
        }
        if ($_POST['ibegin_share_log_stats']) iBeginShare_InstallDatabase();
    }

    ob_start();
    ?>
    <?php if (!empty($_POST['save'])) { ?>
    <div id="message" class="updated fade"><p><strong><?php _e('Options saved.') ?></strong></p></div>
    <?php } ?>
    <form action="" method="post" id="ibegin_share-conf" name="ibegin_share">
        <div class="wrap">
            <h2><?php echo __('Configuration', 'ibegin_share');?></h2>
            <?php foreach ($ibegin_share_options as $option=>$description) { ?>
                <?php $current_value = get_option($option); ?>
                <p>
                    <?php if (!is_array($ibegin_share_options_choices[$option])) { ?>
                        <label><input type="checkbox" value="<?php echo $ibegin_share_options_choices[$option];?>"<?php if ($current_value == $ibegin_share_options_choices[$option]) echo ' checked="checked"'; ?> name="<?php echo $option;?>" /> <?php echo htmlspecialchars($description);?></label>
                    <?php } else { ?>
                        <label for="id_<?php echo $option;?>"><?php echo htmlspecialchars($description);?></label>
                        <select name="<?php echo $option;?>">
                        <?php foreach ($ibegin_share_options_choices[$option] as $choice=>$label) { ?>
                            <option value="<?php echo $choice;?>"<?php if ($current_value == $choice) echo ' selected="selected"'; ?>><?php echo htmlspecialchars($label);?></option>
                        <?php } ?>
                        </select>
                    <?php } ?>
                </p>
            <?php } ?>
            <p>You may also directly embed the share link through either <code>&lt;? iBeginShare_Button(); ?&gt;</code> or <code>&lt;? iBeginShare_TextLink(); ?&gt;</code>.</p>
			<p>By default, we can automatically figure out the title and URL of the page. If you wish to override it, the two variables would be <code>(title, url)</code> in the function call. Eg <code>&lt;? iBeginShare_Button('My Page','http://www.mypage.com/'); ?&gt;</p>
            <h2><?php echo __('Plug-ins', 'ibegin_share');?></h2>
            <p>Below are a list of the available plug-ins. You may disable any of these.</p>
            <?php foreach ($ibegin_share_plugins as $plugin=>$description) { ?>
                <?php $option = 'ibegin_share_plugins_enable_'.$plugin; ?>
                <?php $current_value = get_option($option); ?>
                <p>
                    <label><input type="checkbox" value="1"<?php if ($current_value == '1') echo ' checked="checked"'; ?> name="<?php echo $option;?>" /> Enable the <?php echo htmlspecialchars($description);?> plug-in.</label>
                </p>
            <?php } ?>
            <p>You can create your own plug-in for iBegin Share. More details at the <a href="http://www.ibegin.com/labs/share/">iBegin Share website</a>.</p>
            <p class="submit">
                <input type="submit" name="save" value="<?php echo __('Save Changes', 'ibegin_share');?>" />
            </p>
        </div>
    </form>
    <?php
    ob_end_flush();
}

function iBeginShare_Menu()
{
    add_submenu_page('plugins.php', __('iBegin Share', 'ibegin_share'), __('iBegin Share', 'ibegin_share'), 'manage_options', 'ibegin-share-options', 'iBeginShare_OptionsForm');
    if (get_option('ibegin_share_log_stats'))
    {
        add_submenu_page('index.php', __('iBegin Share Stats', 'ibegin_share'), __('iBegin Share Stats', 'ibegin_share'), 'manage_options', 'ibegin-share-stats', 'iBeginShare_StatsPage');        
    }
}
function iBeginShare_RenderContentPage($post)
{
    global $ibegin_share_state;
    $ibegin_share_state = IBEGIN_SHARE_STATE_CONTENTPAGE;
    header('Content-Type: text/html');
    ob_start();
    ?>
        <h1><?php echo htmlspecialchars($post->post_title);?></h1>
        <?php echo apply_filters('the_content', $post->post_content);?>
    <?php
    ob_end_flush();
    $ibegin_share_state = IBEGIN_SHARE_STATE_DEFAULT;
    exit;
}
function iBeginShare_RenderSharePage($post)
{
    header('Content-Type: text/html');
    ob_start();
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>iBegin Share 2.6</title>
        <?php iBeginShare_Header(); ?>
        <script type="text/javascript">
        iBeginShare.addEvent(window, 'load', function() {
            var params = {
                link: '<?php echo get_permalink($post->ID); ?>',
                title: '<?php echo htmlspecialchars($post->post_title); ?>',
                content: '<?php echo get_option('siteurl') . '/?ibsa=get_content&id=' . $post->ID; ?>'
            };
            iBeginShare.show(document.getElementById('share_container'), params);
        })
        </script>
    </head>
    <body>
        <noscript>You must be using a JavaScript-enabled browser to render this plug-in.</noscript>
        <div id="share_container"></div>
    </body>
    </html>
    <?php
    ob_end_flush();
    exit;
}
/**
 * Logs an action for statistics usage.
 * @param {String} $action The action name (usually the plugin name)
 * @param {String} $link The URL which this action represents.
 * @param {String} $label The label of this log action (e.g. 'Delicious').
 */
function iBeginShare_LogAction($action, $link, $label=null)
{
    global $wpdb;
    $wpdb->query(sprintf("INSERT INTO `".IBEGIN_SHARE_TABLE_PREFIX."log` (`action`, `label`, `link`, `ipaddress`, `agent`, `timestamp`) VALUES ('%s', '%s', '%s', '%s', '%s', '%d')", $wpdb->escape($action), $wpdb->escape($label), $wpdb->escape(urldecode($link)), ip2long(getenv('REMOTE_ADDR')), $wpdb->escape(getenv('HTTP_USER_AGENT')), time()));
}
function iBeginShare_Pages()
{
    global $ibegin_share_options;
    
    if (empty($_REQUEST['ibsa'])) return;
    switch ($_REQUEST['ibsa'])
    {
        case 'get_content':
            $id = $_REQUEST['id'];
            if (empty($id)) header('Location: '.get_bloginfo('wpurl'));
            if (!$post =& get_post($id)) header('Location: '.get_bloginfo('wpurl'));
            iBeginShare_RenderContentPage($post);
        break;
        case 'share':
            $id = $_REQUEST['id'];
            if (empty($id)) header('Location: '.get_bloginfo('wpurl'));
            if (!$post =& get_post($id)) header('Location: '.get_bloginfo('wpurl'));
            iBeginShare_RenderSharePage($post);
        break;
        case 'log':
            if ($_GET['plugin'] && $_GET['link'])
            {
                iBeginShare_LogAction($_GET['plugin'], $_GET['link'], $_GET['name']);
            }
            header("Location: " . $_GET['to']);
            exit;
        break;
    }
}

?>