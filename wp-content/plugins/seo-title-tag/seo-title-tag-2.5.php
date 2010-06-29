<?php
// this will create the DB table if needed.
function seo_title_tag_install()
{
    global $wpdb;

    $charset_collate = '';

    if (version_compare(mysql_get_server_info(), '4.1.0', '>=') ) {
        if (!empty($wpdb->charset)) {
            $charset_collate .= "DEFAULT CHARACTER SET $wpdb->charset";
        }

        if (!empty($wpdb->collate)) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }
    }

    foreach ($wpdb->get_col("SHOW TABLES", 0) as $table ) {
        $tables[$table] = $table;
    }

    $table_name = $wpdb->prefix . "seo_title_tag_url";
    // the URL table
    if (isset($tables[$table_name]) && !empty($charset_collate)) {
        $sql = "ALTER TABLE $table_name
                CHANGE url url VARCHAR(255) NOT NULL,
                CHANGE title title VARCHAR(255) NOT NULL,
                $charset_collate";
    } else {
        $sql = "CREATE TABLE $table_name (
                  id bigint(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                  url varchar(255) NOT NULL,
                  title varchar(255) NOT NULL
                ) $charset_collate;";
    }

    $q = $wpdb->query($sql);

    $table_name = $wpdb->prefix . "seo_title_tag_category";
    // the category table
    if (isset($tables[$table_name]) && !empty($charset_collate)) {
        $sql = "ALTER TABLE $table_name
                CHANGE category_id category_id VARCHAR(255) NOT NULL,
                CHANGE title title VARCHAR(255) NOT NULL,
                $charset_collate";
    } else {
        $sql = "CREATE TABLE $table_name (
                  id bigint(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                  category_id varchar(255) NOT NULL,
                  title varchar(255) NOT NULL
                ) $charset_collate;";
    }

    $q = $wpdb->query($sql);

    $table_name = $wpdb->prefix . "seo_title_tag_tag";
    // the tag table
    if (isset($tables[$table_name]) && !empty($charset_collate)) {
        $sql = "ALTER TABLE $table_name
                CHANGE tag_id tag_id VARCHAR(255) NOT NULL,
                CHANGE title title VARCHAR(255) NOT NULL,
                $charset_collate";
    } else {
        $sql = "CREATE TABLE $table_name (
                  id bigint(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                  tag_id varchar(255) NOT NULL,
                  title varchar(255) NOT NULL
                ) $charset_collate;";
    }

    $q = $wpdb->query($sql);

    if (!get_option("custom_title_key")) {
        update_option('custom_title_key', "title_tag");
    }

    if (!get_option("use_category_description_as_title")) {
        update_option('use_category_description_as_title', false);
    }

    if (!get_option('include_blog_name_in_titles')) {
        update_option('include_blog_name_in_titles', false);
    }

    if (!get_option('manage_elements_per_page')) {
        update_option("manage_elements_per_page", 20);
    }
}

function seo_title_tag_get_taxonomy($taxonomy)
{
    global $wpdb, $wp_version;

    $results = $wpdb->get_results("
        SELECT
            tt.term_id,
            t.name,
            t.slug,
            tt.description,
            tt.parent,
            tt.count
        FROM
            " . $wpdb->term_taxonomy . " tt
            INNER JOIN " . $wpdb->terms . " t
            ON tt.term_id = t.term_id
        WHERE
            tt.taxonomy = '$taxonomy'
        ORDER BY
            t.name"
    );

    $terms = array();

    foreach ($results as $term) {
        $terms[$term->term_id] = $term;
    }

    return $terms;
}

function seo_title_tag_get_tags()
{
    global $wpdb, $wp_version;

    $results = $wpdb->get_results("
        SELECT
            tt.term_id,
            t.name,
            t.slug,
            tt.description,
            tt.parent,
            tt.count
        FROM
            " . $wpdb->term_taxonomy . " tt
            INNER JOIN " . $wpdb->terms . " t
            ON tt.term_id = t.term_id
        WHERE
            tt.taxonomy = 'post_tag'
        ORDER BY
            t.name"
    );

    return $results;
}

// this is called on plugin activation.
add_action('activate_seo-title-tag/seo-title-tag.php','seo_title_tag_install');
add_action('admin_head','seo_admin_head');

function seo_admin_head() {
    print '<link rel="stylesheet" type="text/css" href="'.get_option('siteurl').'/wp-content/plugins/seo-title-tag/admin-2.5.css" />';
}

// function that matches $url against the DB including optimizations. Returns the matching title.
function get_seo_url_title($url)
{
    global $wpdb; //, $wp_rewrite;

    $table_name = $wpdb->prefix . "seo_title_tag_url";
    $condition = "(url LIKE '".$wpdb->escape($url)."'";

    if (preg_match('/^\/(.+)$/',$url,$matches)) {
        $condition .= " OR url LIKE '".$wpdb->escape($matches[1])."'";
    }

    if (preg_match('/^\/{0,1}index.php\?{0,1}(.+)$/',$url,$matches)) {
        $condition .= " OR url LIKE '".$wpdb->escape($matches[1])."'";
    }

    $condition .= ')';

    $sql = "SELECT title from ".$table_name." WHERE ".$condition;
    $temp = $wpdb->get_row($sql);

    return $temp->title;
}

// main function. Prints out the title for the page.
function seo_title_tag()
{
    global $cat, $cache_categories, $wp_query, $wp_version, $tabletags, $wpdb;

    // the url to match against
    $local_url = $_SERVER['REQUEST_URI'];
    // IIS on Windows fix
    if (! $local_url)
    {
        $local_url = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];
    }

    // the title to print
    $title = '';
    // if the title is a url match.
    $is_url_title = false;

    if (get_option("custom_title_key")) {
        $custom_title_key                  = get_option("custom_title_key");
        $home_page_title                   = get_option("home_page_title");
        $home_page_title                   = htmlspecialchars(stripslashes($home_page_title));
        $error_page_title                  = get_option("error_page_title");
        $error_page_title                  = htmlspecialchars(stripslashes($error_page_title));
        $separator                         = get_option("separator");
        $separator                         = htmlspecialchars(stripslashes($separator));
        $use_category_description_as_title = get_option("use_category_description_as_title");
        $include_blog_name_in_titles       = get_option("include_blog_name_in_titles");
        $short_blog_name                   = get_option("short_blog_name");
        $short_blog_name                   = htmlspecialchars(stripslashes($short_blog_name));
        $manage_elements_per_page          = get_option("manage_elements_per_page");
    } else {
        $custom_title_key                  = "title_tag";
        $use_category_description_as_title = false;
        $include_blog_name_in_titles       = false;
        $manage_elements_per_page          = 20;
    }

    if (empty($separator)) {
        $separator = "&raquo;";
    }

    // check if we are on the home page / Posts Page.
    // Note WP 2.1.x thinks that a static homepage (i.e. the Front Page) is not is_home
    if (is_home()) {
        if ($home_page_title) {
            $title =  $home_page_title;
        } elseif ($temp = get_seo_url_title($local_url)) {
            $is_url_title = true;
            $title = $temp;
        } else {
            $title = get_bloginfo('name');
        }
    } else {
        if (is_single() || is_page()) {
            $post = $wp_query->post;
            $post_custom = get_post_custom($post->ID);
            $custom_title_value = $post_custom["$custom_title_key"][0];
            $custom_title_value = trim(strip_tags($custom_title_value));

            if ($custom_title_value) {
                $title = $custom_title_value;
                if ($post->ID == get_option('page_on_front')) {
                    $include_blog_name_in_titles = false;
                }
            } elseif ($temp = get_seo_url_title($local_url)) {
                $is_url_title = true;
                $title = $temp;
            } else {
                $title = wp_title(' ', false);

                if (wp_title(' ', false) == ' ') {
                    $separator = "";
                }
            }
        } elseif (is_category()) {
            $category = $wp_query->get_queried_object();
            $temp_category_title = '';

            if ($use_category_description_as_title) {
                $temp_category_title = trim(strip_tags($category->category_description));
            } else {
                if (!empty($category->cat_ID)) {
                    $table_name = $wpdb->prefix . "seo_title_tag_category";
                    $temp = $wpdb->get_results("SELECT title from ".$table_name." where category_id = ".$category->cat_ID);

                    if (is_array($temp) && isset($temp[0])) {
                        $temp_category_title = $temp[0]->title;
                    }
                }
            }

            if ($temp_category_title) {
                $title = $temp_category_title;
            } elseif ($temp = get_seo_url_title($local_url)) {
                $is_url_title = true;
                $title = $temp;
            } else {
                $title = single_cat_title('',false);
            }

        } elseif (is_search()) {
            if ($temp = get_seo_url_title($local_url)) {
                $is_url_title = true;
                $title = $temp;
            } else {
                $title = "Search results";
                if (isset($_GET['s'])) { $title .= " for " . $_GET['s']; }
            }
        } elseif (function_exists('is_tag') && is_tag()) {
            $tags = explode(' ', $wp_query->query_vars['tag']);
            foreach (array_keys($tags) as $k) {
                $sql = "SELECT
                            t.term_id
                        FROM
                            " . $wpdb->terms . " t INNER JOIN " . $wpdb->term_taxonomy . " tt
                            ON t.term_id = tt.term_id
                        WHERE
                            t.slug = '" . $wpdb->escape($tags[$k]) . "' AND
                            tt.taxonomy = 'post_tag'
                        LIMIT 1";

                $temp = $wpdb->get_results($sql);
                if (is_array($temp) && isset($temp[0])) {
                    $tags[$k] = $temp[0]->term_id;
                } else {
                    unset($tags[$k]);
                }
            }

            $temp_tag_title = '';

            if (is_array($tags) && 1 == count($tags)) {
                $table_name = $wpdb->prefix . "seo_title_tag_tag";
                $temp = $wpdb->get_results("SELECT title from " . $table_name . " where tag_id = " . $tags[0]);

                if (is_array($temp) && isset($temp[0]))
                {
                    $temp_tag_title = $temp[0]->title;
                }
            }

            if (!empty($temp_tag_title)) {
                $title = $temp_tag_title;
            } elseif ($temp = get_seo_url_title($local_url)) {
                $is_url_title = true;
                $title = $temp;
            } else {
                $title = single_tag_title('',false);
            }
        } elseif (is_404()) {
            if ($error_page_title) {
                $title = $error_page_title;
            } elseif ($temp = get_seo_url_title($local_url)) {
                $is_url_title = true;
                $title = $temp;
            } else {
                $title = get_bloginfo('name');
            }
        } elseif ($temp = get_seo_url_title($local_url)) {
            $is_url_title = true;
            $title = $temp;
        } else {
            $title = wp_title(' ', false);
            if (wp_title(' ', false) == ' ') {
                $separator = "";
            }
        }

        if ($include_blog_name_in_titles) {
            if ($separator) {
                $title .= " $separator ";
            }

            if ($short_blog_name) {
                $title .= $short_blog_name;
            } else {
                $title .= get_bloginfo('name');
            }
        } elseif (empty($title)) {
            $title = get_bloginfo('name');  // this is so the page has a title, because otherwise it would have been untitled
        }
    }

    // if this is no url matched title we check if we are in paging mode and add the page number.
    if (!$is_url_title) {
        if (preg_match('/(paged=|page=|page\/)(\d+)/',$local_url,$matches)) {
            $title .= ' ('.$matches[2].')';
        }
    }

    echo wp_specialchars(trim($title), true);
}

function seo_title_tag_options_page()
{
    if (function_exists('add_options_page')) {
        add_options_page('SEO Title Tag', 'SEO Title Tag', 10, 'seo-title-tag', 'seo_title_tag_options_subpanel');
        add_management_page('Title Tags', 'Title Tags', 10, 'manage_seo_title_tags', 'manage_seo_title_tags');
    }
}

add_action('admin_menu', 'seo_title_tag_options_page');

function seo_title_tag_options_subpanel()
{
    global $wp_version;

    if (isset($_POST['info_update'])) {
        if ( function_exists('check_admin_referer') ) {
            check_admin_referer('seo-title-tag-action_options');
        }

        if ($_POST['custom_title_key'] != "")  {
            update_option('custom_title_key', stripslashes(strip_tags($_POST['custom_title_key'])));
        }

        update_option('home_page_title', stripslashes(strip_tags($_POST['home_page_title'])));
        update_option('error_page_title', stripslashes(strip_tags($_POST['error_page_title'])));
        update_option('separator', stripslashes(strip_tags($_POST['separator'])));
        update_option('use_category_description_as_title', stripslashes(strip_tags($_POST['use_category_description_as_title'])));
        update_option('include_blog_name_in_titles', stripslashes(strip_tags($_POST['include_blog_name_in_titles'])));
        update_option('short_blog_name', stripslashes(strip_tags($_POST['short_blog_name'])));
        update_option("manage_elements_per_page", intval($_POST['manage_elements_per_page']));

        echo '<div class="updated"><p>Options saved.</p></div>';
    }

    if (get_option("custom_title_key")) {
        // the name of the custom title
        $custom_title_key                  = get_option("custom_title_key");
        $home_page_title                   = get_option("home_page_title");
        $home_page_title                   = htmlspecialchars(stripslashes($home_page_title));
        $error_page_title                  = get_option("error_page_title");
        $error_page_title                  = htmlspecialchars(stripslashes($error_page_title));
        $separator                         = get_option("separator");
        $separator                         = htmlspecialchars(stripslashes($separator));
        $use_category_description_as_title = get_option("use_category_description_as_title");

        // shall we always print out the blog name at the end of the title?
        $include_blog_name_in_titles       = get_option("include_blog_name_in_titles");
        $short_blog_name                   = get_option("short_blog_name");
        $short_blog_name                   = htmlspecialchars(stripslashes($short_blog_name));

        // how many elements do we show per page in the manage page
        $manage_elements_per_page          = get_option("manage_elements_per_page");
    } else {
        $custom_title_key                  = "title_tag";
        $use_category_description_as_title = false;
        $include_blog_name_in_titles       = false;
        $manage_elements_per_page          = 20;
    };
 ?>

    <div class="wrap">
    <h2>SEO Title Tag Options</h2>
    <a href="http://www.netconcepts.com"><img width="233" height="36" alt="visit netconcepts" align="right" src="<?php echo get_option('siteurl'); ?>/wp-content/plugins/seo-title-tag/nclogo.jpg"/></a>
    <form name="stto_main" method="post">
    <?php
    if (function_exists('wp_nonce_field')) {
        wp_nonce_field('seo-title-tag-action_options');
    }
    ?>
    <table class="form-table">
    <tr valign="top">
    <th scope="row"><label for="custom_title_key">Key name for custom field</label></th>
    <td><input name="custom_title_key" type="text" id="custom_title_key" readonly="readonly" value="<?php echo $custom_title_key; ?>" size="40" /></td>
    </tr>
    <tr valign="top">
    <th scope="row">Number of Posts per page in mass mode edit</th>
    <td><input name="manage_elements_per_page" value="<?php echo $manage_elements_per_page; ?>" size="5" class="code" /></td>
    </tr>
    <tr valign="top">
    <?php if ('page' == get_option('show_on_front')) { ?>
        <th scope="row"><a href="<?php echo get_permalink(get_option('page_for_posts')); ?>">Posts Page</a> title tag (leave blank to use blog name)</th>
    <?php } else { ?>
        <th scope="row">Home page title tag (leave blank to use blog name)</th>
    <?php } ?>
    <td><input name="home_page_title" value="<?php echo $home_page_title; ?>" size="60" class="code" /></td>
    </tr>
    <tr valign="top">
    <th scope="row">404 Error title tag (leave blank to use blog name)</th>
    <td><input name="error_page_title" value="<?php echo $error_page_title; ?>" size="60" class="code" /></td>
    </tr>
    <tr valign="top">
    <th scope="row">Use category descriptions as titles on category pages</th>
    <td>
    <label><input name="use_category_description_as_title"  type="radio" value="0"  <?php if (!$use_category_description_as_title) { echo 'checked="checked"'; } ?> /> No</label><br />
    <label><input name="use_category_description_as_title" type="radio" value="1" <?php if ($use_category_description_as_title) { echo 'checked="checked"'; } ?> /> Yes</label>
    </td>
    </tr>
    <tr valign="top">
    <th scope="row">Include blog name in titles</th>
    <td>
    <label><input name="include_blog_name_in_titles"  type="radio" value="0"  <?php if (!$include_blog_name_in_titles) { echo 'checked="checked"'; } ?> /> No</label><br />
    <label><input name="include_blog_name_in_titles" type="radio" value="1" <?php if ($include_blog_name_in_titles) { echo 'checked="checked"'; } ?> /> Yes</label>
    </td>
    </tr>
    </table>

    <h3>Complete the following if "Yes" selected above:</h3>
    <table class="form-table">
    <tr valign="top">
    <th scope="row">Separator (leave blank to use "&raquo;")</th>
    <td><input name="separator" value="<?php echo $separator; ?>" size="10" class="code" /></td>
    </tr>
    <tr valign="top">
    <th scope="row">Short blog name (overrides blog name in title tags)</th>
    <td><input name="short_blog_name" value="<?php echo $short_blog_name; ?>" size="60" class="code" /></td>
    </tr>
    </table>

    <p class="submit">
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="page_options" value="custom_title_key, home_page_title,separator,use_category_description_as_title,include_blog_name_in_titles,short_blog_name"/>
    <input type="submit" name="info_update" class="button" value="<?php _e('Save Changes', 'Localization name') ?> &raquo;" />
    </p>

    </form>
    </div>
<?php
}

function seo_edit_page_form()
{
    global $post;

    $custom_title_value = get_post_meta($post->ID, get_option("custom_title_key"), true);

    ?>
    <div id="seodiv" class="postbox">
    <h3>Title Tag (optional)</h3>
    <div class="inside">
    <input type="text" name="<?php echo get_option("custom_title_key") ?>" value="<?php echo wp_specialchars($custom_title_value, true) ?>" id="<?php echo get_option("custom_title_key") ?>" size="80" />
    </div>
    </div>
    <?php
}


function seo_update_title_tag($id)
{
    $value = $_POST[get_option("custom_title_key")];
    $value = stripslashes(strip_tags($value));

    if (!empty($value)) {
        delete_post_meta($id, get_option("custom_title_key"));
        add_post_meta($id, get_option("custom_title_key"), $value);
    }
}

add_action('edit_post', 'seo_update_title_tag');
add_action('save_post', 'seo_update_title_tag');
add_action('publish_post', 'seo_update_title_tag');
add_action('edit_page_form', 'seo_edit_page_form');
add_action('edit_form_advanced', 'seo_edit_page_form');
add_action('simple_edit_form', 'seo_edit_page_form');

// This fixes how wordpress 2.3 only shows the first tag name when you view
// Taxonomy Intersections and Unions
function seo_title_tag_filter_single_tag_title($prefix = '', $display = true )
{
    global $wp_query, $wpdb;

    $tags = explode(' ', str_replace(',', ' ,', $wp_query->query_vars['tag']));

    $tag_title = '';
    foreach (array_keys($tags) as $k) {
        if (0 == $k) {
            $prefix = '';
        } elseif (',' == $tags[$k][0]) {
            $prefix = ' or ';
            $tags[$k] = substr($tags[$k], 1);
        } else {
            $prefix = ' and ';
        }

        $sql = "SELECT
                    t.name
                FROM
                    " . $wpdb->terms . " t INNER JOIN " . $wpdb->term_taxonomy . " tt
                    ON t.term_id = tt.term_id
                WHERE
                    t.slug = '" . $wpdb->escape($tags[$k]) . "' AND
                    tt.taxonomy = 'post_tag'
                LIMIT 1";

        $temp = $wpdb->get_results($sql);
        if (is_array($temp) && isset($temp[0])) {
            $tag_title .= $prefix . $temp[0]->name;
        }
    }

    return $tag_title;
}

add_filter('single_tag_title', 'seo_title_tag_filter_single_tag_title', 1, 2);

function manage_seo_title_tags()
{
    global $wpdb, $tabletags, $tablepost2tag, $install_directory, $wp_version;

    $search_value = '';
    $search_query_string = '';

    // Save Pages Form
    if (isset($_POST['action']) && (($_POST['action'] == 'pages') || ($_POST['action'] == 'posts'))) {
        if ( function_exists('check_admin_referer') ) {
            check_admin_referer('seo-title-tag-action_posts-form');
        }

        foreach ($_POST as $name => $value) {
            // Update Title Tag
            if (preg_match('/^tagtitle_(\d+)$/', $name, $matches)) {
                $value = stripslashes(strip_tags($value));

                delete_post_meta($matches[1], get_option("custom_title_key"));
                add_post_meta($matches[1], get_option("custom_title_key"), $value);
            }

            // Update Slug
            if (preg_match('/^post_name_(\d+)$/', $name, $matches)) {
                $postarr = get_post($matches[1], ARRAY_A);
                $old_post_name = $postarr['post_name'];
                $postarr['post_name'] = sanitize_title($value, $old_post_name);
                $postarr['post_category'] = array();
                $cats = get_the_category($postarr['ID']);
                if (is_array($cats)) {
                    foreach ($cats as $cat) {
                        $postarr['post_category'][] = $cat->term_id;
                    }
                }
                $tags_input = array();
                $tags = get_the_tags($postarr['ID']);
                if (is_array($tags)) {
                    foreach ($tags as $tag) {
                        $tags_input[] = $tag->name;
                    }
                }
                $postarr['tags_input'] = implode(', ', $tags_input);
                wp_insert_post($postarr);
            }
        }

        echo '<div class="updated"><p>The custom ' . ('pages' == $_POST['action'] ? 'page' : 'post') . ' titles have been updated.</p></div>';

    // Save Category and Tag Forms
    } elseif (isset($_POST['action']) && (($_POST['action'] == 'categories') || ($_POST['action'] == 'tags'))) {
        if ( function_exists('check_admin_referer') ) {
            check_admin_referer('seo-title-tag-action_taxonomy-form');
        }

        $singular = ('tags' == $_POST['action'] ? 'tag' : 'category');

        foreach ($_POST as $name => $value) {
            // Update Title Tag
            if (preg_match('/^title_(\d+)$/',$name,$matches)) {
                $title = stripslashes(strip_tags($_POST['title_'.$matches[1]]));
                $title = $wpdb->escape($title);

                if (get_option("use_category_description_as_title")) {
                    $temp = $wpdb->get_row('SELECT term_id FROM ' . $wpdb->term_taxonomy . ' where term_id = ' . $matches[1]);

                    if ($temp->term_id == $matches[1]) {
                        $wpdb->query('UPDATE ' . $wpdb->term_taxonomy . ' SET description = \'' . $title . '\' where term_id = ' . $matches[1]);
                    }
                } else {
                    $table_name = $wpdb->prefix . 'seo_title_tag_' . $singular;
                    $temp = $wpdb->get_results('SELECT ' . $singular . '_id as term_id from ' . $table_name . ' WHERE ' . $singular . '_id = ' . $matches[1]);
                    if (isset($temp[1])) {
                        $wpdb->query('DELETE FROM ' . $table_name . ' WHERE ' . $singular . '_id = ' . $matches[1]);
                        unset($temp);
                    } elseif (isset($temp[0])) {
                        $temp = $temp[0];
                    }

                    if ((isset($temp)) && ($temp->term_id == $matches[1]) && (!empty($title))) {
                        $wpdb->query('UPDATE ' . $table_name . ' SET title = \'' . $title . '\' WHERE ' . $singular . '_id = ' . $matches[1]);
                    } elseif (!empty($title)) {
                        $wpdb->query('INSERT INTO ' . $table_name . ' (' . $singular . '_id,title) values(\'' . $matches[1] . '\',\'' . $title . '\')');
                    } else {
                        $wpdb->query('DELETE FROM ' . $table_name . ' where ' . $singular . '_id = ' . $matches[1]);
                    }
                }
            }
        }

        echo '<div class="updated"><p>The custom ' . $singular . ' titles have been saved.</p></div>';

    // Save URLs Form
    } elseif (isset($_POST['action']) and ($_POST['action'] == 'urls')) {
        if ( function_exists('check_admin_referer') ) {
            check_admin_referer('seo-title-tag-action_urls-form');
        }

        $table_name = $wpdb->prefix . "seo_title_tag_url";

        foreach ($_POST as $name => $value) {
            // Update Title Tag
            if (preg_match('/^url_(\d+)$/',$name,$matches)) {
                $url = stripslashes($value);
                $url = $wpdb->escape($url);

                $title = stripslashes(strip_tags($_POST['title_'.$matches[1]]));
                $title = $wpdb->escape($title);

                if ((!empty($url)) and (!empty($title))) {
                    $wpdb->query('UPDATE ' . $table_name . ' SET url = \'' . $url . '\', title = \'' . $title . '\' WHERE id = ' . $matches[1]);
                } elseif (empty($url) and empty($title)) {
                    $wpdb->query('DELETE FROM ' . $table_name . ' WHERE id = ' . $matches[1]);
                }
            } elseif (preg_match('/^url_new_(\d+)$/',$name,$matches)) {
                $url = stripslashes($value);
                $url = $wpdb->escape($url);

                $title = stripslashes(strip_tags($_POST['title_new_'.$matches[1]]));
                $title = $wpdb->escape($title);

                if ((!empty($url)) and (!empty($title))) {
                    $wpdb->query('INSERT INTO ' . $table_name . ' (url,title) VALUES (\'' . $url . '\',\'' . $title . '\')');
                }
            }
        }

        echo '<div class="updated"><p>The custom URLs and URL titles have been saved.</p></div>';

    // Filter by Search Value
    } elseif (isset($_POST['search_value'])) {
        $search_value = stripslashes(strip_tags($_POST['search_value']));
    }

    // If no search value from POST check for value in GET
    if (!isset($_POST['search_value']) && isset($_GET['search_value'])) {
        $search_value = stripslashes(strip_tags($_GET['search_value']));
    }

    $title_tags_type          = stripslashes(strip_tags($_GET['title_tags_type']));
    $page_no                  = intval($_GET['page_no']);
    $manage_elements_per_page = get_option("manage_elements_per_page");
    $element_count            = 0;

    if (empty($title_tags_type)) {
        $title_tags_type = 'pages';
    }

    if (empty($manage_elements_per_page)) {
        $manage_elements_per_page = 15;
    }

    $_SERVER['QUERY_STRING'] = preg_replace('/&title_tags_type=[^&]+/','',$_SERVER['QUERY_STRING']);
    $_SERVER['QUERY_STRING'] = preg_replace('/&page_no=[^&]+/','',$_SERVER['QUERY_STRING']);
    $_SERVER['QUERY_STRING'] = preg_replace('/&search_value=[^&]*/','',$_SERVER['QUERY_STRING']);
    $search_query_string = '&search_value='.$search_value;

    if (!$page_no) {
        $page_no = 0;
    }
    ?>

    <div class="wrap">

    <form  id="posts-filter" action="" method="post">
    <h2>SEO Title Tags</h2>

    <p id="post-search">
        <label class="hidden" for="search_value">Search Title Tags:</label>
        <input type="text" id="search_value" name="search_value" value="<?php if (isset($search_value)) { echo wp_specialchars($search_value, true); } ?>" />
        <input type="submit" value="Search Title Tags" class="button" />

    </p>

    <div><a href="http://www.netconcepts.com"><img width="233" height="36" alt="visit netconcepts" align="right" src="<?php echo get_option('siteurl'); ?>/wp-content/plugins/seo-title-tag/nclogo.jpg" /></a></div>
    <p><a href="options-general.php?page=seo-title-tag">Edit main SEO Title Tag plugin options &raquo;</a></p>

    <br class="clear" />

    </form>
    <?php

    //do the nav menu items for the subsubmenu
    if (empty($_REQUEST['title_tags_type'])) { $_REQUEST['title_tags_type'] = 'pages'; }
    echo '<ul id="subsubmenu">' . "\n";
    echo '<li ' . is_current($_REQUEST['title_tags_type'],'pages') . '><a href="?' . $_SERVER['QUERY_STRING'] . '&title_tags_type=pages">Pages</a></li>' . "\n";
    echo '<li ' . is_current($_REQUEST['title_tags_type'],'posts') . '><a href="?' . $_SERVER['QUERY_STRING'] . '&title_tags_type=posts">Posts</a></li>' . "\n";
    echo '<li ' . is_current($_REQUEST['title_tags_type'],'categories') . '><a href="?' . $_SERVER['QUERY_STRING'] . '&title_tags_type=categories">Categories</a></li>' . "\n";
    echo '<li ' . is_current($_REQUEST['title_tags_type'],'tags') . '><a href="?' . $_SERVER['QUERY_STRING'] . '&title_tags_type=tags">Tags</a></li>' . "\n";
    echo '<li ' . is_current($_REQUEST['title_tags_type'],'urls') . '><a href="?' . $_SERVER['QUERY_STRING'] . '&title_tags_type=urls">URLs</a></li>' . "\n";
    echo '</ul>' . "\n";

    // Render Page and Post Tabs
    if ($title_tags_type == 'pages' || $title_tags_type == 'posts') {
        $post_type = substr($title_tags_type, 0, -1); // Database table uses singular version
        ?>
        <p>Use the form below to enter or update a custom <?php echo $post_type; ?> title.<br /></p>
        <?php

        if (empty($search_value)) {
            if ($page_no > 0) {
                $limit = ' LIMIT ' . ($page_no * $manage_elements_per_page) . ', ' . $manage_elements_per_page;
            } else {
                $limit = ' LIMIT ' . $manage_elements_per_page;
            }

            $posts = $wpdb->get_results('SELECT * FROM ' . $wpdb->posts . ' WHERE post_type = \'' . $post_type . '\' ORDER BY menu_order ASC' . ('posts' == $title_tags_type ? ', post_date DESC' : ', ID ASC') . $limit);
        } else {

            $posts = $wpdb->get_results('SELECT * FROM ' . $wpdb->posts . ' WHERE post_type = \'' . $post_type . '\' ORDER BY menu_order ASC' . ('posts' == $title_tags_type ? ', post_date DESC' : ', ID ASC'));
            $new_posts;

            foreach ($posts as $post) {
                if (isset($post->post_type) and ($post->post_type != $post_type)) {
                    continue;
                }

                if (empty($search_value)) {
                    // No search value, add all
                    $new_posts[] = $post;
                } else {
                    // Filter based on search value
                    if (preg_match('/'.$search_value.'/i', $post->post_title)) {
                        $new_posts[] = $post;
                    } else {
                        $post_custom = get_post_custom($post->ID);

                        if (
                            preg_match('/'.$search_value.'/i', $post_custom[get_option("custom_title_key")][0]) ||
                            preg_match('/'.$search_value.'/i', $post->post_content) ||
                            preg_match('/'.$search_value.'/i', $post->post_excerpt)
                        ) {
                            $new_posts[] = $post;
                        }
                    }
                }
            }

            $posts = $new_posts;

            $element_count = count($posts);

            if (($element_count > $manage_elements_per_page) and (($page_no != 'all') or empty($page_no))) {
                if ($page_no > 0) {
                    $posts = array_splice($posts, ($page_no * $manage_elements_per_page));
                }

                $posts = array_slice($posts, 0, $manage_elements_per_page);
            }

        }

        if ($posts) {
            ?>
            <form name="posts-form" action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
            <?php
            if ( function_exists('wp_nonce_field') ) {
                wp_nonce_field('seo-title-tag-action_posts-form');
            }
            ?>
            <input type="hidden" name="action" value="<?php echo $title_tags_type; ?>" />
            <table class="widefat">
            <thead>
            <tr>
            <th scope="col">ID</th>
            <th scope="col">Title</th>
            <th scope="col">Custom Title</th>
            <th scope="col">Slug</th>
            </tr>
            </thead>
            <tbody>
            <?php
            manage_seo_title_tags_recursive($title_tags_type, $posts);

            echo '</table><br /><input type="submit" class="button" value="Submit" /></form>';
        } else {
            echo '<p><b>No ' . $title_tags_type . ' found!</b></p>';
        }

    // Render Categories Tab
    } elseif ($title_tags_type == 'categories' || $title_tags_type == 'tags') {
        $singular = ('tags' == $title_tags_type ? 'tag' : 'category');
        $taxonomy = ('tags' == $title_tags_type ? 'post_tag' : 'category');
        ?>
        <p>Use the form below to enter or update a custom <?php echo $singular; ?> title.<br /></p>
        <?php

        $terms       = seo_title_tag_get_taxonomy($taxonomy);
        $table_name  = $wpdb->prefix . "seo_title_tag_" . $singular;
        $term_titles = array();

        if (get_option("use_category_description_as_title") && 'categories' == $title_tags_type) {
            foreach ($terms as $category) {
                $term_titles[$category->term_id] = $category->category_description;
            }
        } else {
            // defult filling of the category titles field.
            $sql = 'SELECT ' . $singular . '_id as term_id, title FROM ' . $table_name;

            $results = $wpdb->get_results($sql);
            $term_titles = array();
            foreach ($results as $term) {
                $term_titles[$term->term_id] = $term->title;
            }

            $terms_new = array();

            if ($terms) {
                foreach ($terms as $term) {
                    $term->title = (isset($term_titles[$term->term_id]) ? $term_titles[$term->term_id] : '');
                    if (empty($search_value)) {
                        $terms_new[] = $term;
                    } else {
                        if (
                            preg_match('/' . $search_value.'/i', $term->title) ||
                            preg_match('/' . $search_value . '/i', $term->name)
                        ) {
                            $terms_new[] = $term;
                        }
                    }
                }

                $terms = $terms_new;
            }
        }

        $element_count = count($terms);

        if (($element_count > $manage_elements_per_page) and (($page_no != 'all') or empty($page_no))) {
            if ($page_no > 0) {
                $terms = array_splice($terms, ($page_no * $manage_elements_per_page));
            }

            $terms = array_slice($terms, 0, $manage_elements_per_page);
        }

        if ($terms) {
            ?>
            <form name="categories-form" action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
            <?php
            if (function_exists('wp_nonce_field')) {
                wp_nonce_field('seo-title-tag-action_taxonomy-form');
            }
            ?>
            <input type="hidden" name="action" value="<?php echo $title_tags_type; ?>" />
            <table class="widefat">
            <thead>
            <tr>
            <th scope="col">ID</th>
            <th scope="col"><?php echo ucfirst($singular); ?></th>
            <th scope="col">Custom Title</th>
            </tr>
            </thead>
            <tbody>
            <?php

            foreach ($terms as $term) {
                $term_href = ('tags' == $title_tags_type ? get_tag_link($term->term_id) : get_category_link($term->term_id));
                ?>
                <tr>
                <td><a href="<?php echo $term_href ?>"><?php echo $term->term_id ?></a></td>
                <td><?php echo $term->name ?></td>
                <td><input type="text" name="title_<?php echo $term->term_id ?>" value="<?php echo wp_specialchars($term->title, true); ?>" size="70" /></td>
                <?php
            }

            echo '</table><br /><input type="submit" class="button" value="Submit" /></form>';
        } else { //End of check for terms
            print "<b>No " . ucfirst($title_tags_type) . " found!</b>";
        }
    } elseif ($title_tags_type == 'urls') {
            ?>
            <p>Use the form below to enter or update a title tag for any URL, including archives pages, tag conjunction pages, etc.</p><p>In the URL field, leave off the http:// and your domain and your blog's directory (if you have one). e.g. <i>tag/seo+articles</i> is okay; <i>http://www.netconcepts.com/tag/seo+articles</i> is NOT.<br /></p>
            <?php
            $table_name = $wpdb->prefix . "seo_title_tag_url";
            $urls;

            $sql = 'SELECT id, url, title from '. $table_name;

            if (!empty($search_value)) {
                $sql .= ' WHERE url LIKE "%' . $wpdb->escape($search_value) . '%" OR title LIKE "%' . $wpdb->escape($search_value) . '%"';
            }

            $sql .= ' ORDER BY title';

            $urls = $wpdb->get_results($sql);

            $element_count = count($urls);

            if (($element_count > $manage_elements_per_page) and (($page_no != 'all') or empty($page_no))) {
                if ($page_no > 0) {
                    $urls = array_splice($urls, ($page_no * $manage_elements_per_page));
                }

                $urls = array_slice($urls, 0, $manage_elements_per_page);
            }

            ?>
            <form name="urls-form" action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
            <?php
            if ( function_exists('wp_nonce_field') ) {
                wp_nonce_field('seo-title-tag-action_urls-form');
            }
            ?>
            <input type="hidden" name="action" value="urls" />
            <table class="widefat">
            <thead>
            <tr>
            <th scope="col">ID</th>
            <th scope="col">URL</th>
            <th scope="col">Custom Title</th>
            </tr>
            </thead>
            <tbody>
            <?php

            if (is_array($urls)) {
                foreach ($urls as $url) {
                    $url_value = $url->title;

                    if (get_magic_quotes_runtime()) {
                        $url_value = stripslashes($url_value);
                    }
                    ?>
                    <tr>
                    <td><a href="/<?php echo preg_replace('/^\//','',$url->url) ?>"><?php echo $url->id ?></a></td>
                    <td><input type="text" title="<?php echo wp_specialchars($url->url, true) ?>" name="url_<?php echo $url->id ?>" value="<?php echo wp_specialchars($url->url, true) ?>" size="40" /></td>
                    <td><input type="text" title="<?php echo wp_specialchars($url->title, true) ?>" name="title_<?php echo $url->id ?>" value="<?php echo wp_specialchars($url_value, true); ?>" size="70" /></td>
                    </tr>
                    <?php
                }
            }

            for ($n = 0; $n < 5; $n++) {
                ?>
                <tr>
                <td>New (<?php echo ($n + 1) ?>)</td>
                <td><input type="text" name="url_new_<?php echo $n ?>" value="" size="40" /></td>
                <td><input type="text" name="title_new_<?php echo $n ?>" value="" size="70" /></td>
                </tr>
                <?php
            }

            echo '</table><br /><input type="submit" class="button" value="Submit" /></form>';
    } else {
        echo '<p>unknown title tags type!</p>';
    }
    ?>

    <?php
    if ($element_count > $manage_elements_per_page) {
        if (($page_no == 'all') and (!empty($page_no))) {
            echo 'View All&nbsp;&nbsp;';
        } else {
            echo '<a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&page_no=all&title_tags_type='.$title_tags_type.$search_query_string.'">View All</a>&nbsp;&nbsp;';
        }
    }

    if ($element_count > $manage_elements_per_page) {
        for ($p = 0; $p < (int) ceil($element_count / $manage_elements_per_page); $p++) {
            if ($page_no == $p) {
                echo ($p + 1).'&nbsp;';
            } else {
                echo '<a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&page_no='.$p.'&title_tags_type='.$title_tags_type.$search_query_string.'">'.($p + 1).'</a> ';
            }
        }
    }
    ?>
    </div>
    <?php
}

function manage_seo_title_tags_recursive($type, $elements = 0)
{
    if (!$elements) {
        return;
    }

    $cache = array();

    foreach($elements as $element) {
        $level = 0;

        /*
        This is really slow and doesn't really add much value
        if (0 < $element->post_parent) {
            if (!isset($cache[$element->post_parent])) {
                $cache[$element->post_parent] = 0;
                do {
                    $cache[$element->post_parent]++;
                    $parent_post = get_post($element->post_parent);
                } while (0 < $parent_post->post_parent);
            }
            $level = $cache[$element->post_parent];
        }
        */

        $element_custom = get_post_custom($element->ID);

        $pad = str_repeat( '&#8212; ', $level );
        $element_value = $element_custom[get_option("custom_title_key")][0];

        if (get_magic_quotes_runtime()) {
            $element_value = stripslashes($element_value);
        }

        ?>
        <tr>
        <td><a href="<?php echo get_permalink($element->ID) ?>"><?php echo $element->ID ?></a></td>
        <td><?php echo $pad.$element->post_title ?></td>
        <td><input type="text" title="<?php echo wp_specialchars($element->post_title, true) ?>" name="tagtitle_<?php echo $element->ID ?>" id="tagtitle_<?php echo $element->ID ?>" value="<?php echo wp_specialchars($element_value, true); ?>" size="80" /></td>
        <?php if ('pages' == $type || 'posts' == $type): ?>
        <td><input type="text" title="<?php echo wp_specialchars($element->post_title, true) ?>" name="post_name_<?php echo $element->ID ?>" id="post_name_<?php echo $element->ID ?>" value="<?php echo wp_specialchars($element->post_name, true); ?>" size="20" /></td>
        <?php endif; ?>
        <?php
    }
}

// returns class=current if the strings exist and match else nothing.
// Used down on the top nav to select which page is selected.
function is_current($aRequestVar,$aType)
{
    if (!isset($aRequestVar) || empty($aRequestVar)) {
        return;
    }

    //do the match
    if ($aRequestVar == $aType) {
        return 'class=current';
    }
}
