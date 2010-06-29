<?php
require_once(preg_replace('|wp-content.*$|','', __FILE__) . 'wp-config.php');

header('Content-type: text/javascript; charset='.get_settings('blog_charset'), true);
header('Cache-control: max-age=2600000, must-revalidate', true);

function error(){ die( "alert('Что-то не заработало :(')" ); }

if(!isset($_POST['action'])){ error(); }

global $wpdb, $dsfaq;
$escape      = "\\'\\\/\x08\x0C\n\r\x09";
$table_name  = $wpdb->prefix."dsfaq_name";
$table_quest = $wpdb->prefix."dsfaq_quest";

switch($_POST['action']) {
    case 'add_faq':
        if(!isset($_POST['input_faq'])){ error(); }
        if($_POST['input_faq'] == ""){ error(); }
        if(get_magic_quotes_gpc()){ $input_faq = $_POST['input_faq']; }
        else{ $input_faq = addslashes($_POST['input_faq']); }

        // Надо будет избавиться от двух одинаковых обращений к БД. Хватит и одного.
        if(!$dsfaq->get_faq_book($input_faq, false, false)){
            $sql = "INSERT INTO `".$table_name."` ( `id` , `name_faq` , `mode` ) VALUES ('', '".$input_faq."', '0');";
            $results = $wpdb->query( $sql );
            if($results){
                $results = $dsfaq->get_faq_book($input_faq, false, false);

                $results = addcslashes($results, "\\'");
                die( "document.getElementById('faqbook').innerHTML += '$results';\n
                document.getElementById(\"s1\").innerHTML = '';" );
            }
        }else{
            die( "alert('".__('The FAQ with such name already exists!', 'wp-ds-faq')."');\n
            document.getElementById(\"s1\").innerHTML = '';" );
        }
        break;

    case 'delete_faqbook':
        if(!isset($_POST['id'])){ error(); }
        $id = $_POST['id'];

        $quest_from_faq = $dsfaq->get_quest_from_faq($id, false, false, true);

        if($quest_from_faq != false){
            $count = count($quest_from_faq);
            $i = 0; $wr = '';
            foreach ($quest_from_faq as $s) {
                $i = $i + 1;
                if($i == $count){$or = "";}else{$or = ' OR ';}
                $wr .= "`id` = '".$s['id']."'".$or;
            }
            $sql = "DELETE FROM `".$table_quest."` WHERE ".$wr;
            $results = $wpdb->query( $sql );
        }

        $sql = "DELETE FROM `".$table_name."` WHERE `id` = ".$id;
        $results = $wpdb->query( $sql );
        if($results){ die( "idElem = document.getElementById('dsfaq_id_".$id."');\n idElem.parentNode.removeChild(idElem);"); }
        break;

    case 'save_quest':
        if(!isset($_POST['id'])){ error(); }
        $id = $_POST['id'];

        if(!isset($_POST['dsfaq_quest'])){ error(); }
        if(get_magic_quotes_gpc()){ $dsfaq_quest = $_POST['dsfaq_quest']; }
        else{ $dsfaq_quest = addslashes($_POST['dsfaq_quest']); }
        if($dsfaq_quest == ""){ error(); }

        if(!isset($_POST['dsfaq_answer'])) error();
        if(get_magic_quotes_gpc()){ $dsfaq_answer = $_POST['dsfaq_answer']; }
        else{ $dsfaq_answer = addslashes($_POST['dsfaq_answer']); }

        $sql = "SELECT * FROM `".$table_quest."` WHERE `id_book` = '".$id."' ORDER BY `sort` DESC LIMIT 1";
        $results = $wpdb->get_results($sql, ARRAY_A);
        if($results){
            foreach ($results as $s) {
                (int)$sortnum = $s['sort']+1;
            }
        }else{
            (int)$sortnum = 1;
        }


        $sql = "INSERT INTO `".$table_quest."` ( `id` , `id_book` , `date` ,             `quest` ,           `answer` ,          `sort` )
                                        VALUES ( ''   , '".$id."' , '".date("Y-m-d-H-i-s")."', '".$dsfaq_quest."', '".$dsfaq_answer."', '".$sortnum."');";
        $results = $wpdb->query( $sql );

        if($results){
            $results = $dsfaq->get_quest_from_faq($id, false, $dsfaq_quest, false);

            $results = addcslashes($results, "\\'");
            die( "idElem = document.getElementById('dsfaq_add_q_".$id."');\n
            idElem.parentNode.removeChild(idElem);\n
            document.getElementById('dsfaq_id_".$id."').innerHTML += '".$results."';" );
        }
        break;

    case 'delete_quest':
        if(!isset($_POST['id'])){ error(); }
        $id = $_POST['id'];
        if(isset($_POST['front'])){
            (int)$front = $_POST['front'];
        }

        $sql = "DELETE FROM `".$table_quest."` WHERE `id` = ".$id;
        $results = $wpdb->query( $sql );

        if(isset($front) and $front == 1){
            $die = "if(document.getElementById('dsfaq_qa_block_".$id."')){
                        dsfaq_front_bg_color('dsfaq_qa_block_".$id."', function (){
                            idElem = document.getElementById('dsfaq_qa_block_".$id."');
                            idElem.parentNode.removeChild(idElem);
                            idElem = document.getElementById('dsfaq_li_".$id."');
                            idElem.parentNode.removeChild(idElem); })
                    }else{
                        dsfaq_front_bg_color('dsfaq_answer_".$id."', function (){
                            idElem = document.getElementById('dsfaq_quest_".$id."');
                            idElem.parentNode.removeChild(idElem);
                            idElem = document.getElementById('dsfaq_tools_".$id."');
                            idElem.parentNode.removeChild(idElem);
                            idElem = document.getElementById('dsfaq_answer_".$id."');
                            idElem.parentNode.removeChild(idElem); })
                    }";
        }else{
            $die = "idElem = document.getElementById('dsfaq_idquest_".$id."');
                    idElem.parentNode.removeChild(idElem);";
        }

        if($results){ die($die); }
        break;

    case 'edit_quest':
        if(!isset($_POST['id'])){ error(); }
        (int)$id = $_POST['id'];

        $sql = "SELECT * FROM `".$table_quest."` WHERE `id` = '".$id."'";
        $select = $wpdb->get_results($sql, ARRAY_A);

        $results = '';
        foreach ($select as $s) {
            $results .= '<div id="dsfaq_idquest_edit_'.$id.'" class="dsfaq_idquest_edit">';
            $results .= '<br>';
            $results .= '<p>'.__('Question:', 'wp-ds-faq').'</p>';
            $results .= '<input id="dsfaq_quest" type="text" value="'.str_replace('"', '&quot;', $s['quest']).'" />';
            $results .= '<p>'.__('Answer:', 'wp-ds-faq').'</p>';
            $results .= '<textarea id="dsfaq_answer" rows="10" cols="45" name="text">'.$s['answer'].'</textarea><br>';
            $results .= '<p class="dsfaq_drv">';
            $results .= '<a href="#_" onclick="this.innerHTML=\'<img src='.$dsfaq->plugurl.'img/ajax-loader.gif>\'; update_quest('.$s['id'].', '.$s['id_book'].');"><span class="button">'.__('Save', 'wp-ds-faq').'</span></a>';
            $results .= ' &nbsp; ';
            $results .= '<a href="#_" onclick="cancel_edit(\''.$id.'\', \'dsfaq_idquest_edit_'.$id.'\');" class="button">'.__('Cancel', 'wp-ds-faq').'</a>';
            $results .= '</p>';
            $results .= '</div>';
        }

        $results = addcslashes($results, $escape);
        die( "document.getElementById('dsfaq_edit_link_".$id."').innerHTML = '';\n
        document.getElementById('dsfaq_idquest_".$id."').style.backgroundColor = '#fdfdef';\n
        document.getElementById('dsfaq_idquest_".$id."').innerHTML += '".$results."';" );
        break;

    case 'front_edit_quest':
        if(!isset($_POST['id'])){ error(); }
        (int)$id = $_POST['id'];

        $sql = "SELECT * FROM `".$table_quest."` WHERE `id` = '".$id."'";
        $select = $wpdb->get_results($sql, ARRAY_A);

        foreach ($select as $s) {
            $front_input_quest = '<input style="width: 100%;" id="dsfaq_inp_quest_'.$id.'" type="text" value="'.str_replace('"', '&quot;', $s['quest']).'" />';
            $front_textarea_answer = '<textarea style="width: 100%;" id="dsfaq_txt_answer_'.$id.'" rows="10" cols="45">'.$s['answer'].'</textarea>';
            $front_tools  = '[ <a href="#_" onclick="this.innerHTML=\'<img src='.$dsfaq->plugurl.'img/ajax-loader.gif>\'; dsfaq_front_update_quest('.$s['id'].');">'.__('Save', 'wp-ds-faq').'</a> ]';
            $front_tools .= '[ <a href="#_" onclick="dsfaq_front_cancel_edit(\''.$id.'\');">'.__('Cancel', 'wp-ds-faq').'</a> ]';
        }

        $front_input_quest     = addcslashes($front_input_quest, $escape);
        $front_textarea_answer = addcslashes($front_textarea_answer, $escape);
        $front_tools           = addcslashes($front_tools, $escape);
        die( "document.getElementById('dsfaq_quest_".$id."').innerHTML = '".$front_input_quest."';\n
        document.getElementById('dsfaq_answer_".$id."').innerHTML = '".$front_textarea_answer."';\n
        document.getElementById('dsfaq_tools_".$id."').innerHTML = '".$front_tools."';" );
        break;

    case 'front_cancel_edit':
        if(!isset($_POST['id'])){ error(); }
        (int)$id = $_POST['id'];

        $sql = "SELECT * FROM `".$table_quest."` WHERE `id` = '".$id."'";
        $select = $wpdb->get_results($sql, ARRAY_A);

        $results = '';
        foreach ($select as $s) {
            $front_input_quest = $s['quest'];
            $front_textarea_answer = '<div class="dsfaq_answer">'.$s['answer'];
            if(current_user_can('level_10')){
                $front_tools  = '[ <a href="#_" onclick="dsfaq_front_edit_quest('.$s['id'].');">'.__('Edit', 'wp-ds-faq').'</a> ]';
                $front_tools .= '[ <a href="#_" onclick="this.innerHTML=\'<img src='.$dsfaq->plugurl.'img/ajax-loader.gif>\'; dsfaq_front_delete_quest('.$s['id'].');">'.__('Delete&nbsp;question', 'wp-ds-faq').'</a> ]';
            }
        }
        $front_input_quest     = addcslashes($front_input_quest, $escape);
        $front_textarea_answer = addcslashes($front_textarea_answer, $escape);
        $front_tools           = addcslashes($front_tools, $escape);
        die( "if(document.getElementById('dsfaq_qa_block_".$id."')){
                  document.getElementById('dsfaq_quest_".$id."').innerHTML = '".$front_input_quest."';\n
                  document.getElementById('dsfaq_answer_".$id."').innerHTML = '".$front_textarea_answer."</div>';\n
              }else{
                  document.getElementById('dsfaq_quest_".$id."').innerHTML = '<a href=\"#_\" onclick=\"dsfaq_open_quest(".$id.");\">".$front_input_quest."</a>';\n
                  document.getElementById('dsfaq_answer_".$id."').innerHTML = '".$front_textarea_answer."<br><span class=\"dsfaq_tools\">[&nbsp;<a href=\"#_\" onclick=\"dsfaq_close_quest(".$id.");\">".__('Close', 'wp-ds-faq')."</a>&nbsp;]</span></div>';\n
              }
        document.getElementById('dsfaq_tools_".$id."').innerHTML = '".$front_tools."';" );
        break;

    case 'update_quest':
        if(!isset($_POST['id'])){ error(); }
        $id = $_POST['id'];

        if(!isset($_POST['id_book'])){ error(); }
        $id_book = $_POST['id_book'];

        if(!isset($_POST['dsfaq_quest'])){ error(); }
        if(get_magic_quotes_gpc()){ $dsfaq_quest = $_POST['dsfaq_quest']; }
        else{ $dsfaq_quest = addslashes($_POST['dsfaq_quest']); }
        if($dsfaq_quest == ""){ error(); }

        if(!isset($_POST['dsfaq_answer'])) error();
        if(get_magic_quotes_gpc()){ $dsfaq_answer = $_POST['dsfaq_answer']; }
        else{ $dsfaq_answer = addslashes($_POST['dsfaq_answer']); }
        $sql = "UPDATE ".$table_quest." SET date='".date("Y-m-d-H-i-s")."', quest='".$dsfaq_quest."', answer='".$dsfaq_answer."', id_book=1 WHERE id='".$id."'";
        $results = $wpdb->query( $sql );

        if($results){
            $res = $dsfaq->get_quest_from_faq($id_book, $id, false, true);

            $results = '';
            foreach ($res as $s) {
                $results .= '<table border="0" width="690"><tr><td width="12">';
                $results .= '<a href="#_" onclick="dsfaq_q_change(\'up\', \''.$s['id_book'].'\', \''.$s['id'].'\');"><img src="'.$dsfaq->plugurl.'img/up.gif" width="8" height="8"></a>';
                $results .= '<br><img src="'.$dsfaq->plugurl.'img/1x1.gif" width="1" height="6"><br>';
                $results .= '<a href="#_" onclick="dsfaq_q_change(\'down\', \''.$s['id_book'].'\', \''.$s['id'].'\');"><img src="'.$dsfaq->plugurl.'img/down.gif" width="8" height="8"></a>';
                $results .= '</td>';
                $results .= '<td>';
                $results .= $s['quest'];
                $results .= '</td><td width="120" align="center" id="dsfaq_edit_link_'.$s['id'].'">';
                $results .= '<a href="#_" onclick="this.innerHTML=\'<img src='.$dsfaq->plugurl.'img/ajax-loader.gif>\'; edit_quest('.$s['id'].');"><span class="button">'.__('Edit', 'wp-ds-faq').'</span></a>';
                $results .= '</td><td width="120" align="center">';
                $results .= '<a href="#_" onclick="this.innerHTML=\'<img src='.$dsfaq->plugurl.'img/ajax-loader.gif>\'; delete_quest('.$s['id'].');"><span class="button">'.__('Delete&nbsp;question', 'wp-ds-faq').'</span></a>';
                $results .= '</td></tr></table>';
            }

            $results = addcslashes($results, "\\'");
            die( "idElem = document.getElementById('dsfaq_idquest_edit_".$id."');\n
            idElem.parentNode.removeChild(idElem);\n
            document.getElementById('dsfaq_idquest_".$id."').style.backgroundColor = '#FFFFFF';\n
            document.getElementById('dsfaq_idquest_".$id."').innerHTML = '".$results."';" );
        }

        break;

    case 'front_update_quest':
        if(!isset($_POST['id'])){ error(); }
        $id = $_POST['id'];

        if(!isset($_POST['dsfaq_quest'])){ error(); }
        if(get_magic_quotes_gpc()){ $dsfaq_quest = $_POST['dsfaq_quest']; }
        else{ $dsfaq_quest = addslashes($_POST['dsfaq_quest']); }
        if($dsfaq_quest == ""){ error(); }

        if(!isset($_POST['dsfaq_answer'])) error();
        if(get_magic_quotes_gpc()){ $dsfaq_answer = $_POST['dsfaq_answer']; }
        else{ $dsfaq_answer = addslashes($_POST['dsfaq_answer']); }

        $sql = "UPDATE ".$table_quest." SET date='".date("Y-m-d-H-i-s")."', quest='".$dsfaq_quest."', answer='".$dsfaq_answer."', id_book=1 WHERE id='".$id."'";
        $results = $wpdb->query( $sql );

        if($results){
            $sql = "SELECT * FROM `".$table_quest."` WHERE `id` = '".$id."'";
            $select = $wpdb->get_results($sql, ARRAY_A);

            $results = '';
            foreach ($select as $s) {
                $front_input_quest = $s['quest'];
                $front_textarea_answer = '<div class="dsfaq_answer">'.$s['answer'];
                if(current_user_can('level_10')){
                    $front_tools  = '[ <a href="#_" onclick="dsfaq_front_edit_quest('.$s['id'].');">'.__('Edit', 'wp-ds-faq').'</a> ]';
                    $front_tools .= '[ <a href="#_" onclick="this.innerHTML=\'<img src='.$dsfaq->plugurl.'img/ajax-loader.gif>\'; dsfaq_front_delete_quest('.$s['id'].');">'.__('Delete&nbsp;question', 'wp-ds-faq').'</a> ]';
                }
            }

            $front_input_quest     = addcslashes($front_input_quest, $escape);
            $front_textarea_answer = addcslashes($front_textarea_answer, $escape);
            $front_tools           = addcslashes($front_tools, $escape);
            die( "if(document.getElementById('dsfaq_qa_block_".$id."')){
                  document.getElementById('dsfaq_quest_".$id."').innerHTML = '".$front_input_quest."';\n
                  document.getElementById('dsfaq_answer_".$id."').innerHTML = '".$front_textarea_answer."</div>';\n
                  document.getElementById('dsfaq_li_".$id."').innerHTML = '<a href=\"#".$id."\">".$front_input_quest."</a>';
              }else{
                  document.getElementById('dsfaq_quest_".$id."').innerHTML = '<a href=\"#_\" onclick=\"dsfaq_open_quest(".$id.");\">".$front_input_quest."</a>';\n
                  document.getElementById('dsfaq_answer_".$id."').innerHTML = '".$front_textarea_answer."<br><span class=\"dsfaq_tools\">[&nbsp;<a href=\"#_\" onclick=\"dsfaq_close_quest(".$id.");\">".__('Close', 'wp-ds-faq')."</a>&nbsp;]</span></div>';\n
              }
            document.getElementById('dsfaq_tools_".$id."').innerHTML = '".$front_tools."';" );
        }

        break;

    case 'q_change':
        if(!isset($_POST['to'])){ error(); }
        $to = $_POST['to'];
        if(!isset($_POST['id_book'])){ error(); }
        $id_book = $_POST['id_book'];
        if(!isset($_POST['id'])){ error(); }
        $id = $_POST['id'];

        $sql = "SELECT `sort` FROM `".$table_quest."` WHERE `id` = '".$id."'";
        $select = $wpdb->get_results($sql, ARRAY_A);
        $sort = $select['0']['sort'];

        if($to == "up"){  $sql = "SELECT * FROM `".$table_quest."` WHERE `id_book` = '".$id_book."' AND `sort` < ".$sort." ORDER BY `sort` DESC LIMIT 1";}
        if($to == "down"){$sql = "SELECT * FROM `".$table_quest."` WHERE `id_book` = '".$id_book."' AND `sort` > ".$sort." ORDER BY `sort` ASC  LIMIT 1";}

        $results = $wpdb->get_results($sql, ARRAY_A);

        if($results){
            $q_id_curent   = $id;
            $q_sort_curent = $sort;
            $q_id_change   = $results['0']['id'];
            $q_sort_change = $results['0']['sort'];

            $sql = "UPDATE ".$table_quest." SET sort='".$q_sort_change."' WHERE id='".$q_id_curent."'";
            $results = $wpdb->query( $sql );
            $sql = "UPDATE ".$table_quest." SET sort='".$q_sort_curent."' WHERE id='".$q_id_change."'";
            $results = $wpdb->query( $sql );

            if($to == 'up'){
                die( "da = document.getElementById('dsfaq_idquest_".$q_id_curent."');\n
                db = document.getElementById('dsfaq_idquest_".$q_id_change."');\n
                da.parentNode.insertBefore(da, db);\n
                dsfaq_bg_color ('dsfaq_idquest_".$q_id_curent."', 'dsfaq_idquest_".$q_id_change."');" );
            }
            if($to == 'down'){
                die( "da = document.getElementById('dsfaq_idquest_".$q_id_curent."');\n
                db = document.getElementById('dsfaq_idquest_".$q_id_change."');\n
                db.parentNode.insertBefore(db, da);\n
                dsfaq_bg_color ('dsfaq_idquest_".$q_id_curent."', 'dsfaq_idquest_".$q_id_change."');" );
            }

        }else{
            die("dsfaq_nahStep('dsfaq_idquest_".$id."');");
        }
        break;

    case 'save_settings':
        if(!isset($_POST['dsfaq_h1'])){ error(); }
        if(get_magic_quotes_gpc()){ $dsfaq_h1 = $_POST['dsfaq_h1']; }
        else{ $dsfaq_h1 = addslashes($_POST['dsfaq_h1']); }

        if(!isset($_POST['dsfaq_h2'])){ error(); }
        if(get_magic_quotes_gpc()){ $dsfaq_h2 = $_POST['dsfaq_h2']; }
        else{ $dsfaq_h2 = addslashes($_POST['dsfaq_h2']); }

        if(!isset($_POST['dsfaq_css'])){ error(); }
        if(get_magic_quotes_gpc()){ $dsfaq_css = $_POST['dsfaq_css']; }
        else{ $dsfaq_css = addslashes($_POST['dsfaq_css']); }

        if(!isset($_POST['dsfaq_copyr'])){ error(); }
        $dsfaq_copyr = $_POST['dsfaq_copyr'];

        if($dsfaq_copyr == 'true'){
            $wp_ds_faq_array['wp_ds_faq_showcopyright'] = true;
        }else{
            $wp_ds_faq_array['wp_ds_faq_showcopyright'] = false;
        }
        $wp_ds_faq_array['wp_ds_faq_h1']  = $dsfaq_h1;
        $wp_ds_faq_array['wp_ds_faq_h2']  = $dsfaq_h2;

        $wp_ds_faq_array['wp_ds_faq_css'] = $dsfaq_css;

        update_option('wp_ds_faq_array', $wp_ds_faq_array);

        die("document.getElementById('dsfaq_progress').innerHTML = '<span style=\'color:green;\'>".__('Settings&nbsp;have&nbsp;been&nbsp;saved:', 'wp-ds-faq')."&nbsp;".date("Y-m-d H:i:s")."</span>';");

        break;

    case 'edit_name_book':
        if(!isset($_POST['id'])){ error(); }
        $id = $_POST['id'];

        $select = $dsfaq->get_faq_book(false, $id, true);

        $name_faq = $select[0]['name_faq'];
        $name_faq = addcslashes($name_faq, $escape);
        $name_faq = str_replace('"', '&quot;', $name_faq);

        die("document.getElementById('dsfaq_namebook_".$id."').innerHTML = '<input style=\"width:415px;\" id=\"dsfaq_input_bookname_".$id."\" value=\"".$name_faq."\"/>';\n
        document.getElementById('dsfaq_toolnamebook_".$id."').innerHTML = '<a href=\"#_\" onclick=\"this.innerHTML=\'<img src=".$dsfaq->plugurl."img/ajax-loader.gif>\'; dsfaq_save_name_book(".$id.");\"><span class=\"button\">".__('Save', 'wp-ds-faq')."</span></a>';");

        break;

    case 'save_name_book':
        if(!isset($_POST['id'])){ error(); }
        $id = $_POST['id'];

        if(!isset($_POST['name_book'])){ error(); }
        if(get_magic_quotes_gpc()){ $name_book = $_POST['name_book']; }
        else{ $name_book = addslashes($_POST['name_book']); }

        $sql = "UPDATE ".$table_name." SET name_faq='".$name_book."' WHERE id='".$id."'";
        $results = $wpdb->query( $sql );

        die("document.getElementById('dsfaq_namebook_".$id."').innerHTML = '<span class=\"dsfaq_title\">".$name_book."</span>';\n
        document.getElementById('dsfaq_toolnamebook_".$id."').innerHTML = '<a href=\"#_\" onclick=\"this.innerHTML=\'<img src=".$dsfaq->plugurl."img/ajax-loader.gif>\'; dsfaq_edit_name_book(".$id.");\"><span class=\"button\">".__('Change&nbsp;title', 'wp-ds-faq')."</span></a>';");

        break;

    case 'change_faqdisplay':
        if(!isset($_POST['id'])){ error(); }
        $id = $_POST['id'];

        if(!isset($_POST['mode'])){ error(); }
        $mode = $_POST['mode'];

        $sql = "UPDATE ".$table_name." SET mode='".$mode."' WHERE id='".$id."'";
        $results = $wpdb->query( $sql );

        if($results){
            $results = '';
            $results .= '<input type="radio" name="dsfaq_mode_'.$id.'" onclick="dsfaq_change_faqdisplay(\''.$id.'\', \'0\');" '.(($mode == 0)?"checked":"").'> '.(($mode == 0)?"<b>":"").__('deployed', 'wp-ds-faq').(($mode == 0)?"</b>":"");
            $results .= ' &nbsp; ';
            $results .= '<input type="radio" name="dsfaq_mode_'.$id.'" onclick="dsfaq_change_faqdisplay(\''.$id.'\', \'1\');" '.(($mode == 1)?"checked":"").'> '.(($mode == 1)?"<b>":"").__('minimized', 'wp-ds-faq').(($mode == 1)?"</b>":"");
        }

        $results = addcslashes($results, $escape);
        die("document.getElementById('dsfaq_display_mode_".$id."').innerHTML = '".$results."';");
        break;

    case 'open_quest':
        if(!isset($_POST['id'])){ error(); }
        $id = $_POST['id'];

        $sql = "SELECT `answer` FROM `".$table_quest."` WHERE `id` = '".$id."'";
        $select = $wpdb->get_results($sql, ARRAY_A);
        $answer = $select[0]['answer'];

        $results = '';
        $results .= '<div class="dsfaq_answer">';
        $results .= apply_filters('the_content', $answer, 'dsfaq_filters');
        $results .= '<br><span class="dsfaq_tools">[&nbsp;<a href="#_" onclick="dsfaq_close_quest('.$id.');">'.__('Close', 'wp-ds-faq').'</a>&nbsp;]</span>';
        $results .= '</div>';

        if(current_user_can('level_10')){
            $tools .= '[ <a href="#_" onclick="dsfaq_front_edit_quest('.$id.');">'.__('Edit', 'wp-ds-faq').'</a> ]';
            $tools .= '[ <a href="#_" onclick="this.innerHTML=\'<img src='.$dsfaq->plugurl.'img/ajax-loader.gif>\'; dsfaq_front_delete_quest('.$id.');">'.__('Delete&nbsp;question', 'wp-ds-faq').'</a> ]';
        }

        $results = addcslashes($results, $escape);
        $tools   = addcslashes($tools, $escape);
        die("document.getElementById('dsfaq_answer_".$id."').innerHTML = '".$results."';\n
        document.getElementById('dsfaq_tools_".$id."').innerHTML = '".$tools."';");

        break;

    case 'restore_settings':
        update_option('wp_ds_faq_array', $dsfaq->wp_ds_faq_default_array);

        die("document.getElementById('dsfaq_h1').value = '".addcslashes($dsfaq->wp_ds_faq_default_array['wp_ds_faq_h1'], $escape)."';\n
        document.getElementById('dsfaq_h2').value = '".addcslashes($dsfaq->wp_ds_faq_default_array['wp_ds_faq_h2'], $escape)."';\n
        document.getElementById('dsfaq_css').value = '".addcslashes($dsfaq->wp_ds_faq_default_array['wp_ds_faq_css'], $escape)."';\n
        document.getElementById('dsfaq_copyr').checked = 'true';\n
        document.getElementById('dsfaq_progress').innerHTML = '<span style=\'color:green;\'>".__('Settings&nbsp;have&nbsp;been&nbsp;restore:', 'wp-ds-faq')."&nbsp;".date("Y-m-d H:i:s")."</span>';");
        break;

    default:
        error();
        break;
}

die( "document.getElementById(\"s1\").innerHTML = '<span style=\"color:red;\">[Error id: 1000]</span>';" );

?>