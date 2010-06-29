<?php
/*
Plugin Name: WP DS FAQ
Plugin URI: http://wp-plugins.diamondsteel.ru/wp-ds-faq/
Description: WP DS FAQ plugin is just a simple FAQ pages management tool for your web site.
Version: 1.3.2
Author: DiamondSteel
Author URI: http://diamondsteel.ru
*/

class dsfaq{
    var $plugurl;
    var $plugdir;
    var $wp_ds_faq_default_array;

    ##############################################################
    # dsfaq()                                                    #
    #   Конструктор                                              #
    ##############################################################------------------------------------------------------------#
    function dsfaq(){
        $this->plugurl = WP_PLUGIN_URL.'/'.str_replace(basename(__FILE__),"",plugin_basename(__FILE__));
        $this->plugdir = WP_PLUGIN_DIR.'/'.str_replace(basename(__FILE__),"",plugin_basename(__FILE__));

        $this->wp_ds_faq_default_array['wp_ds_faq_db_ver']        = '0.3';
        $this->wp_ds_faq_default_array['wp_ds_faq_showcopyright'] = true;
        $this->wp_ds_faq_default_array['wp_ds_faq_ver']           = '132';
        $this->wp_ds_faq_default_array['wp_ds_faq_h1']            = '<h3>';
        $this->wp_ds_faq_default_array['wp_ds_faq_h2']            = '</h3>';
        $this->wp_ds_faq_default_array['wp_ds_faq_css']           = "<style type='text/css'>\n".
                                                                    ".dsfaq_qa_block{ border-top: 1px solid #aaaaaa; margin-top: 20px; }\n".
                                                                    ".dsfaq_ol_quest{ }\n".
                                                                    ".dsfaq_ol_quest li{ }\n".
                                                                    ".dsfaq_ol_quest li a{ }\n".
                                                                    ".dsfaq_quest_title{ font-weight: bold; }\n".
                                                                    ".dsfaq_quest{ }\n".
                                                                    ".dsfaq_answer_title{ font-weight: bold; }\n".
                                                                    ".dsfaq_answer{ border: 1px solid #f0f0f0; padding: 5px 5px 5px 5px; }\n".
                                                                    ".dsfaq_tools{ text-align: right; font-size: smaller; }\n".
                                                                    ".dsfaq_copyright{ display: block; text-align: right; font-size: smaller; }\n".
                                                                    "</style>";

        add_action('init', array(&$this, 'enable_getext'));
        add_action('wp_head', array(&$this,'add_to_wp_head'));
        add_action('admin_menu', array(&$this, 'add_to_settings_menu'));
        add_action('admin_head', array(&$this, 'add_to_admin_head'));

        add_shortcode('dsfaq', array(&$this, 'faq_shortcode'));

        add_filter('the_content', array(&$this, 'faq_hook'), 10 ,2);

        register_activation_hook(__FILE__, array(&$this, 'installer'));
        register_deactivation_hook(__FILE__, array(&$this, 'deactivate'));
    }
    # END dsfaq ##################################################------------------------------------------------------------#

    ##############################################################
    # installer()                                                #
    #   Функция вызываемая при активации плагина                 #
    ##############################################################------------------------------------------------------------#
    public function installer(){
        global $wpdb;
        $wpdb->show_errors();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $table_name = $wpdb->prefix."dsfaq_name";
            $sql = ' CREATE TABLE '.$table_name.' (
                    `id` INT NOT NULL AUTO_INCREMENT ,
                    `name_faq` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
                    `mode` INT NOT NULL ,
                     PRIMARY KEY ( `id` )
                     ) ENGINE = MYISAM DEFAULT CHARSET=utf8 ';
            dbDelta($sql);

        $table_name = $wpdb->prefix."dsfaq_quest";
            $sql = ' CREATE TABLE '.$table_name.' (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `id_book` INT NOT NULL,
                    `date` DATETIME NOT NULL,
                    `quest` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                    `answer` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                    `sort` INT NOT NULL,
                     PRIMARY KEY ( `id` )
                     ) ENGINE = MYISAM DEFAULT CHARSET=utf8 ';
            dbDelta($sql);

        // Если плагин ставиться впервые то сохраняем настройки по умолчанию
        if(!get_option('wp_ds_faq_array')){
            add_option('wp_ds_faq_array', $this->wp_ds_faq_default_array);
        }

        $settings = get_option('wp_ds_faq_array');

        if($settings['wp_ds_faq_ver'] < 130){
            if(strpos($settings['wp_ds_faq_css'], ".dsfaq_tools") === false){
                $settings['wp_ds_faq_css']    = str_replace('</style>', ".dsfaq_tools{ display: block; text-align: right; font-size: smaller; }\n</style>", $settings['wp_ds_faq_css']);
                $settings['wp_ds_faq_db_ver'] = '0.2';
                $settings['wp_ds_faq_ver']    = '130';
                update_option('wp_ds_faq_array', $settings);
            }
        }

        if($settings['wp_ds_faq_ver'] < 132){
            $table_name = $wpdb->prefix."dsfaq_name";
            $sql = ' ALTER TABLE `'.$table_name.'` DEFAULT CHARSET=utf8, MODIFY COLUMN `name_faq` TEXT CHARACTER SET utf8 ';
            $wpdb->query( $sql );

            $table_name = $wpdb->prefix."dsfaq_quest";
            $sql = ' ALTER TABLE `'.$table_name.'` DEFAULT CHARSET=utf8, MODIFY COLUMN `quest` TEXT CHARACTER SET utf8, MODIFY COLUMN `answer` TEXT CHARACTER SET utf8 ';
            $wpdb->query( $sql );

            $settings['wp_ds_faq_db_ver'] = '0.3';
            $settings['wp_ds_faq_ver']    = '132';
            update_option('wp_ds_faq_array', $settings);
        }

    }
    # END installer ##############################################------------------------------------------------------------#

    ##############################################################
    # add_to_wp_head()                                           #
    #   Добавляет стили и скрипты в заголовок wp                 #
    ##############################################################------------------------------------------------------------#
    function add_to_wp_head(){

    }
    # END add_to_wp_head #########################################------------------------------------------------------------#

    ##############################################################
    # add_to_admin_head()                                        #
    #   Добавляет стили и скрипты в заголовок админ панели       #
    ##############################################################------------------------------------------------------------#
    function add_to_admin_head(){
        ?>
        <!-- WP DS FAQ -->
        <link rel="stylesheet" href="<?php echo $this->plugurl; ?>wp-ds-adminfaq.css" type="text/css" media="screen" />
        <!-- End WP DS FAQ -->
        <?php
    }
    # END add_to_admin_head ######################################------------------------------------------------------------#

    ##############################################################
    # deactivate()                                               #
    #   Функция вызываемая при деактивации плагина               #
    ##############################################################------------------------------------------------------------#
    function deactivate(){

    }
    # END deactivate #############################################------------------------------------------------------------#

    ##############################################################
    # enable_getext()                                            #
    #  Говорим WordPress-у что у нас многоязычие в плагине       #
    ##############################################################------------------------------------------------------------#
    function enable_getext() {
        load_plugin_textdomain('wp-ds-faq', '/'.str_replace(ABSPATH, '', dirname(__FILE__)));
    }
    # END enable_getext ##########################################------------------------------------------------------------#

    ##############################################################
    # faq_hook()                                                 #
    #   Функция нужна для отлова фильтра dsfaq_filters           #
    #   apply_filters('the_content', $filtered, 'dsfaq_filters') #
    #   Отлавливаем есть ли у нас FAQ внутри FAQ-а и изменяем    #
    #   шорткод, чтобы он не приминился                          #
    #                                                            #
    #   $content - текст к которому применяется фильтр           #
    #   $dsfaq_filters - Проверочный флаг. Нам нужен только тот  #
    #      фильтр который вызвался нашим обработчиком шорткодов  #
    ##############################################################------------------------------------------------------------#
    function faq_hook($content, $dsfaq_filters = false){
        if($dsfaq_filters){
            $content = str_replace('[dsfaq ', '<span>[</span>dsfaq ', $content);
        }
        return $content;
    }
    # END faq_hook ###############################################------------------------------------------------------------#

    ##############################################################
    # faq_shortcode()                                            #
    #   При нахождении строки [dsfaq] нужно вытащить ID и        #
    #   отобразить соответсвующую страницу с вопросами и ответами#
    ##############################################################------------------------------------------------------------#
    function faq_shortcode($atts){
        if (isset($atts)){
            $settings = get_option('wp_ds_faq_array');
            (int)$id  = $atts['id'];

            $book  = $this->get_faq_book(false, $id, true);
            $quest = $this->get_quest_from_faq($id, false, false, true);

            if($book[0]['mode'] == "0"){
                if(is_array($quest)){
                    $results = '<ol>';
                    foreach ($quest as $s) { $results .= '<li><a href="#">'.$s['quest'].'</a><p>'.$s['answer'].'</p></li>'; }
                    $results .= '</ol>';
                }
            }

            if($book[0]['mode'] == "1"){
                if(is_array($quest)){
                    $results = '<ol>';
                    foreach ($quest as $s) { $results .= '<li><a href="#">'.$s['quest'].'</a><p>'.$s['answer'].'</p></li>'; }
                    $results .= '</ol>';
                }
            }

            return $results;
        }

    }
    # END faq_shortcode ##########################################------------------------------------------------------------#

    ##############################################################
    # add_to_settings_menu()                                     #
    #  Добавляем страницу с настройками плагина в меню Параметры #
    ##############################################################------------------------------------------------------------#
    function add_to_settings_menu(){
        add_submenu_page('options-general.php', 'WP DS FAQ Settings', 'DS FAQ', 10, __FILE__, array(&$this, 'options_page'));
    }
    # END add_to_settings_menu ###################################------------------------------------------------------------#

    ##############################################################
    # options_page()                                             #
    #  Страница настроек плагина                                 #
    ##############################################################------------------------------------------------------------#
    function options_page(){
        global $wpdb;

        // use JavaScript SACK library for Ajax
        wp_print_scripts( array( 'sack' ));

?>
        <script>
        //<![CDATA[
        function dsfaq_add_input(){
            var inputText = document.getElementById("name_faq").value;
            document.getElementById("s1").innerHTML = '<img src="<?php echo $this->plugurl; ?>img/ajax-loader.gif" />';
            var mysack = new sack("<?php echo $this->plugurl; ?>ajax.php" );
            mysack.execute = 1;
            mysack.method = 'POST';
            mysack.setVar( 'action', 'add_faq' );
            mysack.setVar( 'input_faq', inputText );
            mysack.onError = function() { alert('Ajax error. [Error id: 1]' )};
            mysack.runAJAX();
            return true;
        }
        function delete_faqbook(id){
            var mysack = new sack("<?php echo $this->plugurl; ?>ajax.php" );
            mysack.execute = 1;
            mysack.method = 'POST';
            mysack.setVar( 'action', 'delete_faqbook' );
            mysack.setVar( 'id', id );
            mysack.onError = function() { alert('Ajax error. [Error id: 2]' )};
            mysack.runAJAX();
            return true;
        }
        function add_input_quest(id,numid){
            document.getElementById(id).style.backgroundColor = '#fdfdef';
            document.getElementById(id).innerHTML =  '<p><?php _e('Question:', 'wp-ds-faq') ?></p>';
            document.getElementById(id).innerHTML += '<input id="dsfaq_quest" type="text" value="" />';
            document.getElementById(id).innerHTML += '<p><?php _e('Answer:', 'wp-ds-faq') ?></p>';
            document.getElementById(id).innerHTML += '<textarea id="dsfaq_answer" rows="10" cols="45" name="text"></textarea><br>';
            document.getElementById(id).innerHTML += '<p class="dsfaq_drv"><a href="#_" onclick="this.innerHTML=\'<img src=<?php echo $this->plugurl; ?>img/ajax-loader.gif>\'; save_quest(' + numid + ');"><span class="button"><?php _e('Save', 'wp-ds-faq') ?></span></a> &nbsp; <a href="#_" onclick="cancel_quest(\'' + id + '\', \'' + numid + '\');" class="button"><?php _e('Cancel', 'wp-ds-faq') ?></a></p>';
            return true;
        }
        function cancel_quest(id,numid){
            document.getElementById(id).style.backgroundColor = '#FFFFFF';
            document.getElementById(id).innerHTML =  '<a href="#_" onclick="add_input_quest(\'' + id + '\', \'' + numid + '\');" class="button"><?php _e('Add&nbsp;question', 'wp-ds-faq') ?></a>';
            return true;
        }
        function save_quest(id){
            var dsfaq_quest = document.getElementById("dsfaq_quest").value;
            var dsfaq_answer = document.getElementById("dsfaq_answer").value;
            var mysack = new sack("<?php echo $this->plugurl; ?>ajax.php" );
            mysack.execute = 1;
            mysack.method = 'POST';
            mysack.setVar( 'action', 'save_quest' );
            mysack.setVar( 'id', id );
            mysack.setVar( 'dsfaq_quest', dsfaq_quest );
            mysack.setVar( 'dsfaq_answer', dsfaq_answer );
            mysack.onError = function() { alert('Ajax error. [Error id: 3]' )};
            mysack.runAJAX();
            return true;
        }
        function delete_quest(id){
            var mysack = new sack("<?php echo $this->plugurl; ?>ajax.php" );
            mysack.execute = 1;
            mysack.method = 'POST';
            mysack.setVar( 'action', 'delete_quest' );
            mysack.setVar( 'id', id );
            mysack.onError = function() { alert('Ajax error. [Error id: 4]' )};
            mysack.runAJAX();
            return true;
        }
        function edit_quest(id){
            var mysack = new sack("<?php echo $this->plugurl; ?>ajax.php" );
            mysack.execute = 1;
            mysack.method = 'POST';
            mysack.setVar( 'action', 'edit_quest' );
            mysack.setVar( 'id', id );
            mysack.onError = function() { alert('Ajax error. [Error id: 5]' )};
            mysack.runAJAX();
            return true;
        }
        function cancel_edit(id,obj){
            idElem = document.getElementById(obj);
            idElem.parentNode.removeChild(idElem);
            document.getElementById("dsfaq_edit_link_" + id).innerHTML = '<a href="#_" onclick="this.innerHTML=\'<img src=<?php echo $this->plugurl; ?>img/ajax-loader.gif>\'; edit_quest(' + id + ');"><span class="button"><?php _e('Edit', 'wp-ds-faq') ?></span></a>';
            document.getElementById("dsfaq_idquest_" + id).style.backgroundColor = '#FFFFFF';
            return true;
        }
        function update_quest(id, id_book){
            var dsfaq_quest = document.getElementById("dsfaq_quest").value;
            var dsfaq_answer = document.getElementById("dsfaq_answer").value;
            var mysack = new sack("<?php echo $this->plugurl; ?>ajax.php" );
            mysack.execute = 1;
            mysack.method = 'POST';
            mysack.setVar( 'action', 'update_quest' );
            mysack.setVar( 'id', id );
            mysack.setVar( 'id_book', id_book );
            mysack.setVar( 'dsfaq_quest', dsfaq_quest );
            mysack.setVar( 'dsfaq_answer', dsfaq_answer );
            mysack.onError = function() { alert('Ajax error. [Error id: 6]' )};
            mysack.runAJAX();
            return true;
        }
        function dsfaq_q_change(to, id_book, id){
            var mysack = new sack("<?php echo $this->plugurl; ?>ajax.php" );
            mysack.execute = 1;
            mysack.method = 'POST';
            mysack.setVar( 'action', 'q_change' );
            mysack.setVar( 'to', to );
            mysack.setVar( 'id_book', id_book );
            mysack.setVar( 'id', id );
            mysack.onError = function() { alert('Ajax error. [Error id: 7]' )};
            mysack.runAJAX();
            return true;
        }
        function dsfaq_nahStep(id){
            nahStep = function(x,id){
                          var m = parseInt(document.getElementById(id).style.marginLeft),nahStepTimeOut;
                          if(nahStepTimeOut){
                              clearTimeout(nahStepTimeOut);
                          }
                          l = (1 / (Math.pow (x, 1.25) / 20 + 5) - 0.08) * Math.sin (x/2);
                          document.getElementById(id).style.marginLeft = (m + l * 25) + 'px';
                          x++;
                          if(x < 82){
                              nahStepTimeOut = setTimeout(function() {nahStep(x, id)}, 10);
                          }else{
                              document.getElementById(id).style.marginLeft = m + 'px';
                          }
                      }
            nahStep(0,id);
            return true;
        }
        function dsfaq_bg_color(id1, id2){
            var count = 10;
            var timeout = 100;
            var hex = 205;
            var divNode1 = document.getElementById(id1);
            var divNode2 = document.getElementById(id2);
            var updataId = setInterval(function(){
                if(count > 0){
                    hex = hex + 5;
                    divNode1.style.backgroundColor = '#' + Number(hex).toString(16) + 'ff' + Number(hex).toString(16);
                    divNode2.style.backgroundColor = '#' + Number(hex).toString(16) + 'ff' + Number(hex).toString(16);
                    --count;
                }else{
                    clearInterval(updataId);
                }
            }, timeout);
            return true;
        }
        function dsfaq_save_settings(){
            var dsfaq_h1    = document.getElementById("dsfaq_h1").value;
            var dsfaq_h2    = document.getElementById("dsfaq_h2").value;
            var dsfaq_css   = document.getElementById("dsfaq_css").value;
            var dsfaq_copyr = document.getElementById("dsfaq_copyr").checked;
            document.getElementById("dsfaq_progress").innerHTML = '<img src="<?php echo $this->plugurl; ?>img/ajax-loader.gif" />';
            var mysack = new sack("<?php echo $this->plugurl; ?>ajax.php" );
            mysack.execute = 1;
            mysack.method = 'POST';
            mysack.setVar( 'action', 'save_settings' );
            mysack.setVar( 'dsfaq_h1', dsfaq_h1 );
            mysack.setVar( 'dsfaq_h2', dsfaq_h2 );
            mysack.setVar( 'dsfaq_css', dsfaq_css );
            mysack.setVar( 'dsfaq_copyr', dsfaq_copyr );
            mysack.onError = function() { alert('Ajax error. [Error id: 8]' )};
            mysack.runAJAX();
            return true;
        }
        function dsfaq_restore_settings(){
            document.getElementById("dsfaq_progress").innerHTML = '<img src="<?php echo $this->plugurl; ?>img/ajax-loader.gif" />';
            var mysack = new sack("<?php echo $this->plugurl; ?>ajax.php" );
            mysack.execute = 1;
            mysack.method = 'POST';
            mysack.setVar( 'action', 'restore_settings' );
            mysack.onError = function() { alert('Ajax error. [Error id: 9]' )};
            mysack.runAJAX();
            return true;
        }
         function dsfaq_edit_name_book(id){
            var mysack = new sack("<?php echo $this->plugurl; ?>ajax.php" );
            mysack.execute = 1;
            mysack.method = 'POST';
            mysack.setVar( 'action', 'edit_name_book' );
            mysack.setVar( 'id', id );
            mysack.onError = function() { alert('Ajax error. [Error id: 10]' )};
            mysack.runAJAX();
            return true;
        }
        function dsfaq_save_name_book(id){
            var dsfaq_name_book = document.getElementById("dsfaq_input_bookname_" + id).value;
            var mysack = new sack("<?php echo $this->plugurl; ?>ajax.php" );
            mysack.execute = 1;
            mysack.method = 'POST';
            mysack.setVar( 'action', 'save_name_book' );
            mysack.setVar( 'id', id );
            mysack.setVar( 'name_book', dsfaq_name_book );
            mysack.onError = function() { alert('Ajax error. [Error id: 11]' )};
            mysack.runAJAX();
            return true;
        }
        function dsfaq_change_faqdisplay(id, mode){
            document.getElementById("dsfaq_display_mode_" + id).innerHTML = '<img src="<?php echo $this->plugurl; ?>img/ajax-loader.gif" />';
            var mysack = new sack("<?php echo $this->plugurl; ?>ajax.php" );
            mysack.execute = 1;
            mysack.method = 'POST';
            mysack.setVar( 'action', 'change_faqdisplay' );
            mysack.setVar( 'id', id );
            mysack.setVar( 'mode', mode );
            mysack.onError = function() { alert('Ajax error. [Error id: 12]' )};
            mysack.runAJAX();
            return true;
        }
        //]]>
        </script>
<div class="wrap">
    <h2><?php _e('WP DS FAQ Settings:', 'wp-ds-faq') ?></h2>
    <br>
    <p><?php _e('Every FAQ book has its own <b>ID</b>.', 'wp-ds-faq') ?></p>
    <p><?php _e('To have the book viewed on a page you need to write a key word and specify book ID (for example: <b>[dsfaq id=1]</b>).', 'wp-ds-faq') ?></p>
    <br>
    <p><?php _e('You can create a new FAQ book:', 'wp-ds-faq') ?></p>
    <input id="name_faq" type="text" value="" />
    <a href="#_" onclick="dsfaq_add_input();" class="button"><?php _e('Add FAQ', 'wp-ds-faq') ?></a> <span id="s1"></span>
    <br><br>
    <div id="faqbook">
        <?php echo $this->get_faq_book(); ?>
    </div>
    <br><br>

<?php $settings = get_option('wp_ds_faq_array'); ?>

    <fieldset style="border:1px solid #777777; width: 695px; padding-left: 6px;">
        <legend><?php _e('Settings:', 'wp-ds-faq') ?></legend>
        <p><input id="dsfaq_h1" type="text" value="<?php if (isset($settings['wp_ds_faq_h1'])){echo $settings['wp_ds_faq_h1'];}; ?>" /> <?php _e('Text before the FAQ book name.', 'wp-ds-faq') ?></p>
        <p><input id="dsfaq_h2" type="text" value="<?php if (isset($settings['wp_ds_faq_h2'])){echo $settings['wp_ds_faq_h2'];}; ?>" /> <?php _e('Text after the FAQ book name.', 'wp-ds-faq') ?></p>
        <p>CSS</p>
        <textarea id="dsfaq_css" rows="10" cols="45"><?php if (isset($settings['wp_ds_faq_css'])){echo stripslashes($settings['wp_ds_faq_css']);}; ?></textarea>
        <p><input id="dsfaq_copyr" type="checkbox" name="copyright"<?php if ($settings['wp_ds_faq_showcopyright'] == true){echo " checked";}; ?>> <?php _e('Show a link to the plugin in the end of the page.', 'wp-ds-faq') ?></p>
        <p class="dsfaq_drv"><img src="<?php echo $this->plugurl; ?>img/1x1.gif" width="1" height="16"><span id="dsfaq_progress"></span> &nbsp; <a href="#_" onclick="dsfaq_restore_settings();" class="button"><?php _e('Restore settings', 'wp-ds-faq') ?></a> &nbsp; <a href="#_" onclick="dsfaq_save_settings();" class="button"><?php _e('Save', 'wp-ds-faq') ?></a></p>
        <br>
    </fieldset>
    <br><br>
</div>

<?php
    }
    # END options_page ###########################################------------------------------------------------------------#

    ##############################################################
    # get_faq_book()                                             #
    #  Получаем книгу вопросов и ответов либо в виде html-я либо #
    #  в виде массива                                            #
    #                                                            #
    #  $flag - текст вопроса                                     #
    #  $id   - id вопроса                                        #
    #  $raw  - переключатель html / массив                       #
    ##############################################################------------------------------------------------------------#
    function get_faq_book($flag = false, $id = false, $raw = false){
        global $wpdb;
        $table_name = $wpdb->prefix."dsfaq_name";

        if(isset($flag) and $flag != false){ $sql = "SELECT * FROM `".$table_name."` WHERE `name_faq` = '".$flag."'"; }
        elseif($id)                        { $sql = "SELECT * FROM `".$table_name."` WHERE `id` = '".$id."'"; }
        else                               { $sql = "SELECT * FROM `".$table_name."` ORDER BY `id` ASC"; }
        $select = $wpdb->get_results($sql, ARRAY_A);

        if($select){
            if($raw){return $select;}
            $results = '';
            foreach ($select as $s) {
                $results .= '<div id="dsfaq_id_'.$s['id'].'" class="dsfaq_curentbook"><div class="dsfaq_name_faq_book">';
                $results .= '<table border="0" width="690"><tr><td id="dsfaq_namebook_'.$s['id'].'">';
                $results .= '<span class="dsfaq_title">'.$s['name_faq'].'</span>';
                $results .= '</td><td align="center" width="140" id="dsfaq_toolnamebook_'.$s['id'].'">';
                $results .= '<a href="#_" onclick="this.innerHTML=\'<img src='.$this->plugurl.'img/ajax-loader.gif>\'; dsfaq_edit_name_book(\''.$s['id'].'\');"><span class="button">'.__('Change&nbsp;title', 'wp-ds-faq').'</span></a>';
                $results .= '</td><td width="120" align="center">';
                $results .= '<a href="#_" onclick="this.innerHTML=\'<img src='.$this->plugurl.'img/ajax-loader.gif>\'; delete_faqbook(\''.$s["id"].'\');"><span class="button">'.__('Delete&nbsp;FAQ', 'wp-ds-faq').'</span></a>';
                $results .= '</td></tr></table>';
                $results .= '</div>';
                $results .= '<div class="dsfaq_divshortcode">';
                $results .= '<fieldset style="border-top:1px solid #cccccc; width: 685px; margin-left: 5px; padding-left: 5px;">';
                $results .= '<legend class="dsfaq_shortcode">'.__('Options for this FAQ:', 'wp-ds-faq').'</legend>';
                $results .= '<br><table border="0" width="690"><tr>';
                $results .= '<td width="50%">';
                $results .= '<span class="dsfaq_shortcode">'.__('Short&nbsp;code:', 'wp-ds-faq').'&nbsp;<b>[dsfaq&nbsp;id="'.$s['id'].'"]</b></span>';
                $results .= '</td><td width="1"><img src="'.$this->plugurl.'img/1x1.gif" width="1" height="18"></td><td align="right">';
                $results .= '<span class="dsfaq_shortcode">'.__('Display:', 'wp-ds-faq').' </span> ';
                $results .= '</td><td width="300">';
                $results .= '<span class="dsfaq_shortcode" id="dsfaq_display_mode_'.$s['id'].'">';
                $results .= '<input type="radio" name="dsfaq_mode_'.$s['id'].'" onclick="dsfaq_change_faqdisplay(\''.$s['id'].'\', \'0\');" '.(($s['mode'] == 0)?"checked":"").'> '.(($s['mode'] == 0)?"<b>":"").__('deployed', 'wp-ds-faq').(($s['mode'] == 0)?"</b>":"");
                $results .= ' &nbsp; ';
                $results .= '<input type="radio" name="dsfaq_mode_'.$s['id'].'" onclick="dsfaq_change_faqdisplay(\''.$s['id'].'\', \'1\');" '.(($s['mode'] == 1)?"checked":"").'> '.(($s['mode'] == 1)?"<b>":"").__('minimized', 'wp-ds-faq').(($s['mode'] == 1)?"</b>":"");
                $results .= '</span>';
                $results .= '</td>';
                $results .= '</tr></table><br>';
                $results .= '</fieldset>';
                $results .= '</div>';
                $results .= $this->get_quest_from_faq($s['id'], false, false, false);
                $results .= '<div id="dsfaq_add_q_'.$s['id'].'" class="dsfaq_name_faq_quest_add">';
                $results .= '<a href="#_" onclick="add_input_quest(\'dsfaq_add_q_'.$s['id'].'\',\''.$s['id'].'\');" class="button">'.__('Add&nbsp;question', 'wp-ds-faq').'</a>';
                $results .= '</div>';
                $results .= '</div>';
            }
        }else{
            $results = false;
        }
        return $results;
    }
    # END get_faq_book ###########################################------------------------------------------------------------#

    ##############################################################
    # get_quest_from_faq()                                       #
    #  Получаем конкретный вопрос либо в виде html-я либо        #
    #  в виде массива                                            #
    #                                                            #
    #  $id_book  - id книги вопросов и ответов                   #
    #  $id_quest - id вопроса                                    #
    #  $quest    - текст вопроса                                 #
    #  $raw  - переключатель html / массив                       #
    ##############################################################------------------------------------------------------------#
    function get_quest_from_faq($id_book, $id_quest = false, $quest = false, $raw = false){
        global $wpdb;
        if(!isset($id_book) or $id_book == ""){
            $results = false;
            return $results;
        }

        $table_name = $wpdb->prefix."dsfaq_quest";

        if(isset($id_quest) and $id_quest != false){ $sql = "SELECT * FROM `".$table_name."` WHERE `id_book` = '".$id_book."' AND `id` = '".$id_quest."'"; }
        elseif(isset($quest) and $quest != false)  { $sql = "SELECT * FROM `".$table_name."` WHERE `id_book` = '".$id_book."' AND `quest` = '".$quest."'"; }
        else                                       { $sql = "SELECT * FROM `".$table_name."` WHERE `id_book` = '".$id_book."' ORDER BY `sort` ASC"; }
        $select = $wpdb->get_results($sql, ARRAY_A);

        if($select){
            if($raw){return $select;}
            $results = '';
            foreach ($select as $s) {
                $results .= '<div id="dsfaq_idquest_'.$s['id'].'" class="dsfaq_name_faq_quest" style="margin-left: 0;">';
                $results .= '<table border="0" width="690"><tr><td width="12">';
                $results .= '<a href="#_" onclick="dsfaq_q_change(\'up\', \''.$s['id_book'].'\', \''.$s['id'].'\');"><img src="'.$this->plugurl.'img/up.gif" width="8" height="8"></a>';
                $results .= '<br><img src="'.$this->plugurl.'img/1x1.gif" width="1" height="6"><br>';
                $results .= '<a href="#_" onclick="dsfaq_q_change(\'down\', \''.$s['id_book'].'\', \''.$s['id'].'\');"><img src="'.$this->plugurl.'img/down.gif" width="8" height="8"></a>';
                $results .= '</td>';
                $results .= '<td>';
                $results .= $s['quest'];
                $results .= '</td><td width="120" align="center" id="dsfaq_edit_link_'.$s['id'].'">';
                $results .= '<a href="#_" onclick="this.innerHTML=\'<img src='.$this->plugurl.'img/ajax-loader.gif>\'; edit_quest('.$s['id'].');"><span class="button">'.__('Edit', 'wp-ds-faq').'</span></a>';
                $results .= '</td><td width="120" align="center">';
                $results .= '<a href="#_" onclick="this.innerHTML=\'<img src='.$this->plugurl.'img/ajax-loader.gif>\'; delete_quest('.$s['id'].');"><span class="button">'.__('Delete&nbsp;question', 'wp-ds-faq').'</span></a>';
                $results .= '</td></tr></table>';
                $results .= '</div>';
                if(isset($quest) and $quest != false){
                    $results .= '<div id="dsfaq_add_q_'.$id_book.'" class="dsfaq_name_faq_quest_add"><a href="#_" onclick="add_input_quest(\'dsfaq_add_q_'.$s['id_book'].'\',\''.$s['id_book'].'\');" class="button">'.__('Add&nbsp;question', 'wp-ds-faq').'</a></div>';
                }
            }
        }else{
            $results = false;
        }
        return $results;
    }
    # END get_quest_from_faq #####################################------------------------------------------------------------#
}

$dsfaq = new dsfaq();

?>