<?php
// Version: 4.5
// Date: 19/1/2010
/** this is the file that converts the normal shopping cart to the gold shopping cart */

$gold_shpcrt_active = get_option('activation_state');
define("WPSC_GOLD_MODULE_PRESENT", true);
define('WPSC_GOLD_FILE_PATH', dirname(__FILE__));
define('WPSC_GOLD_DIR_NAME', basename(WPSC_GOLD_FILE_PATH));

$wp_upload_directory_data = wp_upload_dir();
$wp_upload_directory = $wp_upload_directory_data['baseurl'];

if(is_ssl()) {
	$wp_upload_directory = str_replace("http://", "https://", $wp_upload_directory);
}

$wpsc_gold_cart_subpath = str_replace(ABSPATH,'', WPSC_GOLD_FILE_PATH);

define( 'WPSC_GOLD_FILE_URL', $wp_upload_directory."/wpsc/upgrades/gold_cart_files/");

require(dirname(__FILE__)."/upgrade_panel.php");

function gold_shpcrt_install() {
	global $wpdb, $user_level, $wp_rewrite;
}
    
if($gold_shpcrt_active === 'true') {
  function gold_shpcrt_javascript() {
    $siteurl = get_option('siteurl');
		if(is_ssl()) {
			$siteurl = str_replace("http://", "https://", $siteurl);
		}

    if ((get_option('show_search') == 1) && (get_option('show_live_search') == 1)) {
			?>
			<script language="JavaScript" type="text/javascript" src="<?php echo $siteurl;?>/wp-content/plugins/<?php echo WPSC_DIR_NAME; ?>/js/iautocompleter.js"></script>
			<?php
		}
		?>
    <script language='JavaScript' type='text/javascript'>
    <?php
    echo "var TXT_WPSC_PRODUCTIMAGE = '".TXT_WPSC_PRODUCTIMAGE."';\n\r";
    echo "var TXT_WPSC_USEDEFAULTHEIGHTANDWIDTH = '".TXT_WPSC_USEDEFAULTHEIGHTANDWIDTH."';\n\r";
    echo "var TXT_WPSC_USE = '".TXT_WPSC_USE."';\n\r";
    echo "var TXT_WPSC_PXHEIGHTBY = '".TXT_WPSC_PXHEIGHTBY."';\n\r";
    echo "var TXT_WPSC_PXWIDTH = '".TXT_WPSC_PXWIDTH."';\n\r";
		if ((get_option('show_search') == 1) && (get_option('show_live_search') == 1)) {
			?>
			jQuery(document).ready( function() {
				jQuery('#wpsc_search_autocomplete').Autocomplete({
					source: 'index.php?wpsc_live_search=true',
					delay: 500,
					fx: {
						type: 'slide',
						duration: 200
					},
					autofill: false,
					helperClass: 'autocompleter',
					selectClass: 'selectAutocompleter',
					minchars: 1
				});
		});
		<?php
		}
    ?>
    </script>
    <script src="<?php echo WPSC_GOLD_FILE_URL; ?>gold_cart.js" language='JavaScript' type="text/javascript"></script>
      <link href='<?php echo WPSC_GOLD_FILE_URL; ?>gold_cart.css' rel="stylesheet" type="text/css" />
    <?php
    if(function_exists('product_display_grid')) {
      ?>      
      <link href='<?php echo WPSC_GOLD_FILE_URL; ?>grid_view.css' rel="stylesheet" type="text/css" />
      <?php
    }
  }
  
  
  function wpsc_gold_shpcrt_ajax($id) {
		global $wpdb;
	
		//exit('<pre>'.print_r($_POST, true).'</pre>');
		if(($_POST['wpsc_live_search']==true) && (get_option('show_live_search') == 1) && !empty($_POST['keyword'])){
			$keyword=$_POST['keyword'];
					
			$search_sql = gold_shpcrt_search_sql($keyword);
			$product_list = $wpdb->get_results("SELECT DISTINCT `".WPSC_TABLE_PRODUCT_LIST."`.* FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `".WPSC_TABLE_PRODUCT_LIST."`.`active`='1' $search_sql ORDER BY `".WPSC_TABLE_PRODUCT_LIST."`.`name` ASC",ARRAY_A) ;
			//exit("SELECT DISTINCT `".WPSC_TABLE_PRODUCT_LIST."`.* FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `".WPSC_TABLE_PRODUCT_LIST."`.`active`='1' $search_sql ORDER BY `".WPSC_TABLE_PRODUCT_LIST."`.`name` ASC");
			$output =  "<ul>";
			if ($product_list != null) {
				foreach($product_list as $product) {
				   //filter out the HTML, otherwise we get partial tags and everything breaks
				  $product['description'] = wp_kses($product['description'], false);
				  
				  // shorten the description;
					if (strlen($product['description'])>68) {
						$product_description = substr($product['description'], 0, 68)."...";
					} else {
						$product_description = $product['description'];
					}
					//generate the HTML
					$output .= "<li>\n\r";
					$output .= "	<a href='".wpsc_product_url($product['id'])."'>\n\r";
					if ($product['image'] != '') {
						$output .= "				<img class='live-search-image' src='index.php?productid=".$product['id']."&amp;width=50&amp;height=50'>\n\r";
					} else {
						$output .= "				<img class='live-search-image' src='".get_option('siteurl')."/wp-content/plugins/".WPSC_DIR_NAME."/no-image-uploaded.gif' style='height: 50px; width: 50px;'>\n\r";
					}
					$output .= "				<div class='live-search-text'>\n\r";
					$output .= "					<strong>".$product['name']."</strong>\n\r";
					$output .= "					<div class='description'>".stripslashes($product_description)."</div>\n\r";
					$output .= "				</div>\n\r";
					$output .= "		    <br clear='both' />\n\r";
					$output .= "		</a>\n\r";
					$output .= "</li>\n\r";					
				}
			}
			$output .= "</ul>";
			$_SESSION['live_search_results'] = $product_list;
			exit($output);
		}
		
		if ($_POST['affiliate']==true) {
		  if(!function_exists('affiliate_text')) {
				function affiliate_text($id, $user) {
					$output = "<a target='_blank' title='Your Shopping Cart' href='".get_option('siteurl')."/?action=affiliate&p=$id&user_id=".$user."&height=400&width=600' class='thickbox'><img src='".WPSC_URL."/images/buynow.jpg'></a>";
					return $output;
				}
			}
	
		$id = $_POST['prodid'];
		$product = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE id='$id' LIMIT 1",ARRAY_A);
		$product = $product[0];
		$link = affiliate_text($id,$_POST['uid']);
		echo "<textarea class='affiliate_text' onclick='this.select();' >$link</textarea>";
		exit();
	}
	if ($_POST['log_affiliate']==true) {
		$uid = $_POST['uid'];
		$amount = $_POST['amount'];
		$product = $wpdb->query("UPDATE {$wpdb->prefix}wpsc_affiliates SET paid=paid+$amount  WHERE user_id='$uid'");
		echo "uid=".$uid;
		exit();
	}
}
  
  
  
  function edit_submit_extra_images($id) {
    global $wpdb;
    foreach($_FILES['extra_image']['name'] as $key => $name) {
      if(($name != '') && ($_FILES['extra_image']['size'][$key] >= 0)) {
        $time_data = explode(" ",microtime());
        $microtime = str_replace("0.", ".", $time_data[0]);
        $fulltime = $time_data[1].$microtime;
        $name = basename($name);
        //  test to see if the image already exists
        if(file_exists(WPSC_IMAGE_DIR.$name)) {
					$name_parts = explode('.',basename($name));
					$extension = array_pop($name_parts);
					$name_base = implode('.',$name_parts);
					$dir = glob(WPSC_IMAGE_DIR."$name_base*");
					
					foreach($dir as $file) {
						$matching_files[] = basename($file);
					}
					$name = null;
					$num = 2;
					//  loop till we find a free file name, first time I get to do a do loop in yonks
 					do {
 						$test_name = "{$name_base}-{$num}.{$extension}";
						if(!file_exists(WPSC_IMAGE_DIR.$test_name)) {
							$name = $test_name;
						} 						
						$num++;
 					} while ($name == null);
        }
        
        
        
        //echo("<pre>".print_r($name,true)."</pre>");
        //exit("<pre>".print_r($matching_files,true)."</pre>");
        $new_image_path = (WPSC_IMAGE_DIR.$name);
        $type = $_FILES['extra_image']['type'][$key];
        $tmp_name = $_FILES['extra_image']['tmp_name'][$key];
        $resize_state = $_POST['extra_image_resize'][$key]; 
        $extra_height = $_POST['extra_height'][$key];
        $extra_width = $_POST['extra_width'][$key];
        move_uploaded_file($tmp_name, $new_image_path);
        if(function_exists("getimagesize")) {
					//image_processing($tmp_name,$new_image_path);
          $imagetype = @getimagesize($new_image_path);
        }
				$stat = stat( dirname( $new_image_path ));
				$perms = $stat['mode'] & 0000666;
				@ chmod( $new_image_path, $perms );	
        $insert_query = "INSERT INTO `".WPSC_TABLE_PRODUCT_IMAGES."` ( `product_id` , `image` , `width` , `height` ) VALUES ( '$id', '$name', '".(int)$imagetype['width']."', '".(int)$imagetype['height']."');";
        $wpdb->query($insert_query);
      }
    }
    return $output;
  }
    
	function edit_extra_images($id) {
		global $wpdb;
		//exit("<pre>".print_r($_POST,true)."</pre>");
		if($_POST['extra_image_id'] != null) {
			foreach($_POST['extra_image_id'] as $num => $value) {
				if($_POST['extra_deleteimage'][$num] == 1) {
					$wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id` = '".$value."' LIMIT 1");
				}
			}
		}
		return $output;
	}
    
    
  function edit_multiple_image_form($id) {
    global $wpdb;
    $siteurl = get_option('siteurl');
    $values = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `product_id` = '$id'",ARRAY_A);
    $num = 0;
    if($values != null) {
      foreach($values as $image) {
        if(function_exists("getimagesize")) {
          if($image['image'] != '') {
            $num++;
            $imagepath = WPSC_IMAGE_DIR . $image['image'];
            include('getimagesize.php');
            
            $output .= "          <tr>\n\r";
            $output .= "            <td colspan='2'>\n\r";
            $output .= "            <hr class='image_seperator' />";
            $output .= "            </td>\n\r";
            $output .= "          </tr>\n\r";
            
            $output .= "          <tr>\n\r";
            $output .= "            <td>\n\r";
            $output .= TXT_WPSC_ADDITIONAL_IMAGE.": <br />";
            
            if(is_file(WPSC_IMAGE_DIR.$image['image'])) {
              $image_size = @getimagesize(WPSC_IMAGE_DIR.$image['image']);
            }
            if(($image_size[0] != '') && ($image_size[1] != '')) {
              $output .= "<span class='image_size_text'>".$image_size[0]."x".$image_size[1]."</span>";
            }
            
            $output .= "            </td>\n\r";  
            
            $output .= "            <td>\n\r";
            $output .= "<table>\n\r";
            $output .= "  <tr>\n\r";
            $output .= "    <td>\n\r";
            
            $output .= "<table>\n\r";
            
            $output .= "  <tr>\n\r";
            $output .= "    <td>\n\r";
            $output .= "<input type='hidden' name='extra_image_id[$num]' value='".$image['id']."' />";
            $output .= "<p><input type='checkbox' name='extra_deleteimage[$num]' value='1' /> ".TXT_WPSC_DELETEIMAGE."</p>";
  
            $output .= "    </td>\n\r";
            $output .= "  </tr>\n\r";
            $output .= "</table>\n\r";
            $output .= "    </td>\n\r";
      
            $output .= "    <td>";
            $output .= "<a id='extra_preview_link_".$image['id']."' href='".WPSC_IMAGE_URL.$image['image']."' rel='product_extra_image_".$image['id']."' class='thickbox'><img id='previewimage' src='".WPSC_IMAGE_URL.$image['image']."' alt='".TXT_WPSC_PREVIEW."' title='".TXT_WPSC_PREVIEW."' /></a>";
            $output .= "    </td>";
            $output .= "  </tr>";
      
            $output .= "</table>\n\r";
            $output .= "            </td>\n\r";
            $output .= "          </tr>\n\r";
          }
        }
      }
    }
    
    $output .= "          <tr>\n\r";
    $output .= "            <td colspan='2'>\n\r";
    $output .= "            <div id='additional_images'></div>\n\r";
    $output .= "            </td>\n\r";
    $output .= "          </tr>\n\r";
      
    $output .= "          <tr>\n\r";
    $output .= "            <td>\n\r";
    $output .= "            </td>\n\r";
    $output .= "            <td>\n\r";
    $output .= "<a href='' onclick='add_image_upload_forms();return false;' class='add_additional_image'>".TXT_WPSC_ADD_ADDITIONAL_IMAGE."</a>";
    $output .= "            </td>\n\r";
    $output .= "          </tr>\n\r";
    
    $output .= "          <tr>\n\r";
    $output .= "            <td colspan='2'>\n\r";
    $output .= "            <hr class='image_seperator' />";
    $output .= "            </td>\n\r";
    $output .= "          </tr>\n\r";
    
    return $output;
  }
        
  function add_multiple_image_form($state = '') {
    $output .= "    <tr>\n\r";
    $output .= "      <td colspan='2'>\n\r";
    $output .= "      <div id='".$state."additional_images'></div>\n\r";
    $output .= "      </td>\n\r";
    $output .= "    </tr>\n\r";
      
    $output .= "    <tr>\n\r";
    $output .= "      <td>\n\r";
    $output .= "      </td>\n\r";
    $output .= "      <td>\n\r";
    $output .= "<a href='' onclick='add_image_upload_forms(\"".$state."\");return false;' class='add_additional_image'>".TXT_WPSC_ADD_ADDITIONAL_IMAGE."</a>\n\r";
    $output .= "      </td>\n\r";
    $output .= "    </tr>\n\r";
    return $output;
	}
    
  function gold_shpcrt_preview_image() {
    global $wpdb;
    if(($_GET['view_preview'] == 'true') && is_numeric($_GET['imageid'])) {
      if(function_exists("getimagesize")) {

        $imagesql = "SELECT `image` FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id`='".$_GET['imageid']."' LIMIT 1";
        $imagedata = $wpdb->get_results($imagesql,ARRAY_A);

        $imagepath = WPSC_IMAGE_DIR . $imagedata[0]['image'];

        if(is_numeric($_GET['height']) && is_numeric($_GET['width'])) {
          $height = $_GET['height'];
          $width = $_GET['width'];
				} else {
					$image_size = @getimagesize($imagepath);
					$width .= $image_size[0];
					$height .= $image_size[1];
				}
        if(($height > 0) && ($height <= 1024) && ($width > 0) && ($width <= 1024)) {
					include("image_preview.php");
        }
			}
		}
	}
    
  function gold_shpcrt_display_extra_images($product_id,$product_name, $display = false) {
    global $wpdb;
    $siteurl = get_option('siteurl');
    $images = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `product_id` = '$product_id' ORDER BY `id` DESC",ARRAY_A);
    $output = "";
    if($images != null) {
      foreach($images as $image) {
        $image_link = WPSC_IMAGE_URL.$image['image']."";
        $display_style = '';
        if($display == false) {
          $display_style = "style='display: none;'";
				}
        $output .= "<a $display_style href='".$image_link."' class='thickbox preview_link'  rel='".str_replace(" ", "_",$product_name)."'><img src='$image_link' alt='$product_name' title='$product_name' /></a>";
			}
		}
    return $output;
	}
    
  function gold_shpcrt_display_gallery($product_id, $invisible = false) {
    global $wpdb;
    $siteurl = get_option('siteurl');
    if(get_option('show_gallery') == 1 && !isset($_GET['range'])) {
      /* No GD? No gallery. */
      if(function_exists("getimagesize")) {
        /* get data about the base product image */
        $product = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='".$product_id."' LIMIT 1",ARRAY_A);
        $image_link = WPSC_IMAGE_URL.$product['image']."";
        
				$image_file_name = $product['image'];
				   
        $imagepath = WPSC_THUMBNAIL_DIR.$image_file_name;
        $base_image_size = @getimagesize($imagepath);
        
        /* get data about the extra product images */
        $images = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `product_id` = '$product_id' AND `id` NOT IN('$image_file_name')  ORDER BY `image_order` ASC",ARRAY_A);
        $output = "";      
						//echo "SELECT * FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `product_id` = '$product_id' AND `id` NOT IN('$image_file_name')  ORDER BY `image_order` ASC";
				$new_height = get_option('wpsc_gallery_image_height');
				$new_width = get_option('wpsc_gallery_image_width'); 
				if(count($images) > 0) {
					/* display gallery */
					if($invisible == true) { 
						foreach($images as $image) {         
							$extra_imagepath = WPSC_IMAGE_DIR.$image['image']."";    
							$extra_image_size = @getimagesize($extra_imagepath); 
							$thickbox_link = WPSC_IMAGE_URL.$image['image']."";
							$image_link = "index.php?image_id=".$image['id']."&amp;width=".$new_width."&amp;height=".$new_height."";
							$output .= "<a href='".$thickbox_link."' class='thickbox hidden_gallery_link'  rel='".str_replace(array(" ", '"',"'", '&quot;','&#039;'), array("_", "", "", "",''), $product['name'])."' rev='$image_link'>&nbsp;</a>";
						}
					} else {
						$output .= "<h2 class='prodtitles'>".__("Gallery")."</h2>";
						$output .= "<div class='wpcart_gallery'>";
						if($images != null) {
							foreach($images as $image) {         
								$extra_imagepath = WPSC_IMAGE_DIR.$image['image']."";    
								$extra_image_size = @getimagesize($extra_imagepath); 
								$thickbox_link = WPSC_IMAGE_URL.$image['image']."";
								$image_link = "index.php?image_id=".$image['id']."&amp;width=".$new_width."&amp;height=".$new_height."";       
								$output .= "<a href='".$thickbox_link."' class='thickbox'  rel='".str_replace(array(" ", '"',"'", '&quot;','&#039;'), array("_", "", "", "",''), $product['name'])."'><img src='$image_link' alt='$product_name' title='$product_name' /></a>";
							}
						}		
						$output .= "</div>";
					}
        }
			}
		}
    return $output;
	}
    

  function gold_shpcrt_search_sql($search_string = '') {
    global $wpdb;
    $output = "";
    if($search_string == '') {
      $search_string = $_GET['product_search'];
    }
    if($search_string != '') {
      $brand_sql = '';
      $category_sql = '';
      $search_string_title = "%".$wpdb->escape(stripslashes($search_string))."%";
      $search_string_description = "%".$wpdb->escape(stripslashes($search_string))."%";


      $category_list = $wpdb->get_col("SELECT `id` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `name` LIKE '".$search_string_title."'");
      
      $meta_list = $wpdb->get_col("SELECT DISTINCT `product_id` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_value` REGEXP '".$wpdb->escape(stripslashes($search_string))."' AND `custom` IN ('1')");
      
      //echo "SELECT `product_id` FROM `".$wpdb->prefix."wpsc_productmeta` WHERE `meta_value` LIKE '".$wpdb->escape(stripslashes($_GET['product_search']))."' AND `custom` IN ('1')";


      if($category_list != null) {
				$category_assoc_list = $wpdb->get_col("SELECT DISTINCT `product_id` FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` WHERE `category_id` IN ('".implode("', '", $category_list)."')");
				$category_sql = "OR `".WPSC_TABLE_PRODUCT_LIST."`.`id` IN ('".implode("', '", $category_assoc_list)."')";
      }  
      // this cannot currently list products that are associated with no categories      
      $output = "AND (`".WPSC_TABLE_PRODUCT_LIST."`.`name` LIKE '".$search_string_title."' OR `".WPSC_TABLE_PRODUCT_LIST."`.`description` LIKE '".$search_string_description."' OR `".WPSC_TABLE_PRODUCT_LIST."`.`id` IN ('".implode("','",$meta_list)."') OR `".WPSC_TABLE_PRODUCT_LIST."`.`additional_description` LIKE '".$search_string_description."' $category_sql )";
      //echo $output;
    }
    return $output;
  }


  function gold_shpcrt_search_form(){
		$siteurl = get_option('siteurl'); 
    $output = '';
    if(get_option('permalink_structure') != '') {
    	$seperator ="?";
    } else {
    	$seperator ="&amp;";
    }
    $output .= "<div class='wpsc_product_search'>";

    if($seperator == "&amp;") {
      $output .= "<form action='".get_option('product_list_url')."' method='GET' name='product_search'  class='product_search'>\n\r";
      $url_parameters = explode("&",$_SERVER['QUERY_STRING']);
      foreach($url_parameters as $url_parameter) {
        $split_parameter = explode("=",$url_parameter);
        if(($split_parameter[0] != "product_search") && ($split_parameter[0] != "search")) {
					if (isset($_GET['page_number']) || $split_parameter[0]=='page_id') {
						$output .= "  <input type='hidden' value='".$split_parameter[1]."' name='".$split_parameter[0]."' />\n\r";
					}
				}
			}
		} else {
			$output .= "<form action='".get_option('product_list_url')."' method='GET' name='product_search' class='product_search'>\n\r";
		}
		//written by allen
		if (!isset($_GET['view_type'])){
			if(get_option('product_view')=='grid'){
				$_SESSION['customer_view'] = 'grid';
			} else {
				$_SESSION['customer_view'] = 'default';
			}
		} else {
			$_SESSION['customer_view'] = $_GET['view_type'];
		}
		$output .= "<div style='float:left;padding-top:2px; padding-right:10px;'>";
		$output .= "<div id='out_view_type' ><input type='hidden' id='view_type' name='view_type' value='".$_SESSION['customer_view']."'></div>";
		if (get_option('show_advanced_search')=='1') {
			if($_SESSION['customer_view'] =='grid'){
				$output .= "&nbsp;&nbsp;";

				
				$output .= "<a href='".add_query_arg('view_type', 'default', wpsc_this_page_url())."' id='out_default_pic'><img style='cursor:pointer;border:0px;' id='default_pic' src='".WPSC_URL."/images/default-off.gif'></a>";
				$output .= "  ";
				$output .= "<span id='out_grid_pic'><img id='grid_pic' style='border:0px;' src='".$siteurl."/wp-content/plugins/".WPSC_DIR_NAME."/images/grid-on.gif'></span>";
			} else {
				$output .= "&nbsp;&nbsp;";
				$output .= "<span  id='out_default_pic'><img id='default_pic' style='border:0px;' src='".$siteurl."/wp-content/plugins/".WPSC_DIR_NAME."/images/default-on.gif'></span>";
				$output .= "  ";
				$output .= "<a href='".add_query_arg('view_type', 'grid', wpsc_this_page_url())."' id='out_grid_pic'><img style='cursor:pointer;border:0px;' id='grid_pic' src='".WPSC_URL."/images/grid-off.gif'></a>";
			}
		}
		if ($_GET['order']!=null) {
			$order = $_GET['order'];
		} else {
			$order = "ASC";
		}
		//$output.="<a style='cursor:pointer;' onclick='change_order(\"$order\")'>A</a>";
		
		$output.="</div>";
		$output.="<div style='float:left;''>";
		$output.="<div style='float:left;top:3px;'>Sort:&nbsp;</div> <div style='float:left;cursor:pointer;'><img style='border:0px;' onclick='jQuery(\"#wpsc_sort\").toggle();jQuery(\"#wpsc_show\").hide();' src='".get_option('siteurl')."/wp-content/plugins/".WPSC_DIR_NAME."/images/arrowdown.gif'>";
		$output.="<div id='wpsc_sort' style='border:2px groovy black;background-color:#fff;margin-top:5px;margin-left:0px;position:absolute;display:none;'>";
		$output.="<div class='search_drop_down'>\n\r";
		$output.="<ul>\n\r";
		$output.="  <li>&raquo;&nbsp;<a href='".add_query_arg('product_order', 'ASC', wpsc_this_page_url())."' style='cursor:pointer'>Ascending</a></li>\n\r";
		$output.="  <li>&raquo;&nbsp;<a href='".add_query_arg('product_order', 'DESC', wpsc_this_page_url())."' style='cursor:pointer'>Descending</a></li>\n\r";
		$output.="</ul>\n\r";
		$output.="</div>\n\r";
		
		$output.="</div></div>";
		$output.="</div>";
		
		if (isset($_GET['item_per_page'])){
			if ($_GET['item_per_page'] == 10){
				$selected1 = "selected = true";
			} else if($_GET['item_per_page'] == 20) {
				$selected2 = "selected = true";
			} else if($_GET['item_per_page'] == 50) {
				$selected3 = "selected = true";
			} else if($_GET['item_per_page'] == 0) {
				$selected4 = "selected = true";
			}
		}
		$output .= "<div style='float:left;'>";
		$output .= "<div style='float:left;top:3px;'>&nbsp;&nbsp;Show:&nbsp;&nbsp; </div>";
		$output .= "<img style='cursor:pointer; border:0px;' onclick='jQuery(\"#wpsc_show\").toggle();jQuery(\"#wpsc_sort\").hide();' src='".$siteurl."/wp-content/plugins/".WPSC_DIR_NAME."/images/arrowdown.gif'>";
		$output.="<div id='wpsc_show' style='background-color:#fff;margin-top:5px;margin-left:58px;position:absolute;display:none;'>";
		$output.="<div class='search_drop_down'>\n\r";
		$output.="<ul>\n\r";
		$output.="  <li>&raquo;&nbsp;<a href='".add_query_arg('items_per_page', '10', wpsc_this_page_url())."' style='cursor:pointer;'>10 per page</a></li>\n\r";
		$output.="  <li>&raquo;&nbsp;<a href='".add_query_arg('items_per_page', '20', wpsc_this_page_url())."' style='cursor:pointer;'>20 per page</a></li>\n\r";
		$output.="  <li>&raquo;&nbsp;<a href='".add_query_arg('items_per_page', '50', wpsc_this_page_url())."' style='cursor:pointer;'>50 per page</a></li>\n\r";
		$output.="  <li>&raquo;&nbsp;<a href='".add_query_arg('items_per_page', 'all', wpsc_this_page_url())."' style='cursor:pointer;'>Show All</a></li>\n\r";
		$output.="</ul>\n\r";
		$output.="</div>\n\r";
		
		$output .="</div></div>";

		if(get_option('show_live_search') == 1) {
			$output .= "  <input type='text' value='".$_GET['product_search']."' onkeyup='autocomplete(event)' name='product_search' class='wpsc_product_search' id='wpsc_search_autocomplete' />\n\r";
		} else {
			$output .= "  <input type='text' value='".$_GET['product_search']."' name='product_search' class='wpsc_product_search' id='wpsc_search_autocomplete' />\n\r";
		}
		
		//$output .= "  <input type='submit' value='Search' name='product_search' class='submit' />\n\r";
		$output .= "</form>\n\r";
		$output .="<div id='blind_down'></div>"; //This div is for live searching, Please don't remove this line.
		$output .= "</div>";
		echo $output;
	}
  
  
  function product_display_list($product_list, $group_type, $group_sql = '', $search_sql = '')
    {
    global $wpdb;
    $siteurl = get_option('siteurl');
    
      
    if(get_option('permalink_structure') != '') {
      $seperator ="?";
		} else {
			$seperator ="&amp;";
		}
    
    $product_listing_data = wpsc_get_product_listing($product_list, $group_type, $group_sql, $search_sql);
    
    $product_list = $product_listing_data['product_list'];
    $output .= $product_listing_data['page_listing'];
		if($product_listing_data['category_id']) {
			$category_nice_name = $wpdb->get_var("SELECT `nice-name` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id` ='".(int)$product_listing_data['category_id']."' LIMIT 1");
		} else {
			$category_nice_name = '';
		}
      
    if($product_list != null) {
      $output .= "<table class='list_productdisplay $category_nice_name'>";
			$i=0;
      foreach($product_list as $product) {
	
        $num++;
				if ($i%2 == 1) {
					$output .= "    <tr class='product_view_{$product['id']}'>";
				} else {
					$output .= "    <tr class='product_view_{$product['id']}' style='background-color:#EEEEEE'>";
				}
				$i++;
				$output .= "      <td style='width: 9px;'>";
        if($product['description'] != null) {
          $output .= "<a href='#' class='additional_description_link' onclick='return show_additional_description(\"list_description_".$product['id']."\",\"link_icon".$product['id']."\");'>";
          $output .= "<img style='margin-top:3px;' id='link_icon".$product['id']."' src='$siteurl/wp-content/plugins/".WPSC_DIR_NAME."/images/icon_window_expand.gif' title='".$product['name']."' alt='".$product['name']."' />";
          $output .= "</a>";
				}
        $output .= "      </td>\n\r";
        $output .= "      <td width='55%'>";
        
        if($product['special'] == 1) {
          $special = "<strong class='special'>".TXT_WPSC_SPECIAL." - </strong>";
				} else {
					$special = "";
				}


        $output .= "<a href='".wpsc_product_url($product['id'])."' class='wpsc_product_title' ><strong>" . stripslashes($product['name']) . "</strong></a>";

        $output .= "      </td>";
        $variations_procesor = new nzshpcrt_variations;

        $variations_output = $variations_procesor->display_product_variations($product['id'],false, false, true);
        if($variations_output[1] !== null) {
          $product['price'] = $variations_output[1];
				}
				$output .= "      <td width='10px' style='text-align: center;'>";
        if(($product['quantity'] < 1) && ($product['quantity_limited'] == 1)) {
          $output .= "<img style='margin-top:5px;' src='$siteurl/wp-content/plugins/".WPSC_DIR_NAME."/images/no_stock.gif' title='No' alt='No' />";
				} else {
					$output .= "<img style='margin-top:4px;' src='$siteurl/wp-content/plugins/".WPSC_DIR_NAME."/images/yes_stock.gif' title='Yes' alt='Yes' />";
				}
        $output .= "      </td>";
        $output .= "      <td width='10%'>";
        if(($product['special']==1) && ($variations_output[1] === null)) {
          $output .= nzshpcrt_currency_display(($product['price'] - $product['special_price']), $product['notax'],false,$product['id']) . "<br />";
				} else {
					$output .= "<span id='product_price_".$product['id']."'>".nzshpcrt_currency_display($product['price'], $product['notax'])."</span>";
				}
        $output .= "      </td>";

        $output .= "      <td width='20%'>";
				if (get_option('addtocart_or_buynow') == '0'){
					$output .= "<form name='$num'  id='product_".$product['id']."'  method='POST' action='".get_option('product_list_url').$seperator."category=".$_GET['category']."' onsubmit='submitform(this);return false;' >";
				}
				if(get_option('list_view_quantity') == 1) {
					$output .= "<input type='text' name='quantity' value='1' size='3' maxlength='3'>&nbsp;";
				}
				$output .= $variations_output[0];
				$output .= "<input type='hidden' name='item' value='".$product['id']."' />";
				$output .= "<input type='hidden' name='prodid' value='".$product['id']."'>";
				if (get_option('wpsc_selected_theme')=='iShop') {
					if (get_option('addtocart_or_buynow') == '0') {
						if(($product['quantity_limited'] == 1) && ($product['quantity'] < 1)) {
							$output .= "<input disabled='true' type='submit' value='' name='Buy' class='wpsc_buy_button'/>";
						} else {
							$output .= "<input type='submit' name='Buy' value='' class='wpsc_buy_button'/>";
						}
					} else {
						if(!(($product['quantity_limited'] == 1) && ($product['quantity'] < 1))){
							$output .= google_buynow($product['id']);
						}
					}
				} else {
					if (get_option('addtocart_or_buynow') == '0') {
						if(($product['quantity_limited'] == 1) && ($product['quantity'] < 1)) {
							$output .= "<input disabled='true' type='submit' name='Buy' class='wpsc_buy_button'  value='".TXT_WPSC_ADDTOCART."'  />";
						} else {
							$output .= "<input type='submit' name='Buy' class='wpsc_buy_button'  value='".TXT_WPSC_ADDTOCART."'  />";
						}
					} else {
						if(!(($product['quantity_limited'] == 1) && ($product['quantity'] < 1))){
							$output .= google_buynow($product['id']);
						}
					}
				}
        $output .= "</form>";
        $output .= "      </td>\n\r";
        $output .= "    </tr>\n\r";
        
        $output .= "    <tr class='list_view_description'>\n\r";
        $output .= "      <td colspan='5'>\n\r";
        $output .= "        <div id='list_description_".$product['id']."'>\n\r";
        $output .= $product['description'];
        $output .= "        </div>\n\r";
        $output .= "      </td>\n\r";
        $output .= "    </tr>\n\r";
        
        }
      $output .= "</table>";
		} else {
			$output .= "<p>".TXT_WPSC_NOITEMSINTHIS." ".$group_type.".</p>";
		}
    return $output;
    }
  
  
  
    //written by allen
  function  gold_shpcrt_xmlmaker(){
		$keyword = $_POST['value'];
		header("Content-type: text/xml");
		$siteurl = get_option('siteurl');
		global $wpdb;
		$sql = "SELECT DISTINCT `".WPSC_TABLE_PRODUCT_LIST."`.* FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `".WPSC_TABLE_PRODUCT_LIST."`.`active`='1' AND ".$wpdb->prefix."product_list.name LIKE '$keyword%'";
		$product_list = $wpdb->get_results($sql,ARRAY_A) ;
		echo "<?xml version='1.0'?>\n\r";
		//you can choose any name for the starting tag
		echo "<ajaxresponse>\n\r";
		if ($product_list != null) {
			foreach($product_list as $product)	{
				echo $product['image'];
				echo "<item>\n\r";
				echo "<text>\n\r";
				echo "&lt;a href='#' onClick='window.location=\"".$siteurl."/?page_id=3&amp;product_id=".$product['id']."\"'&gt;\n\r";
				echo "&lt;table cellspacing='2' border='0' class='products'&gt;\n\r";
				echo "&lt;tr&gt;\n\r";
				echo "&lt;td class='product_img' rowspan='2'&gt;\n\r";
				if($product['image']!=""){
					echo "&lt;img src='".WPSC_IMAGE_URL.$product['image']."' width='35' height='35' /&gt;\n\r";
				} else {
					echo "&lt;img src='./wp-content/plugins/".WPSC_DIR_NAME."/no-image-uploaded.gif' width='35' height='35'/&gt;\n\r";
				}
				echo "&lt;/td&gt;\n\r";
				echo "&lt;td width='5px' rowspan='2'&gt;\n\r";
				echo "&lt;/td&gt;\n\r";
				
				echo "&lt;td align='left'&gt;\n\r";
				echo "&lt;strong&gt;".$product['name']."&lt;/strong&gt;\n\r";
				echo "&lt;/td&gt;\n\r";
				echo "&lt;tr&gt;\n\r";
				echo "&lt;td&gt;\n\r";
				if (strlen($product['description'])>34){
					$product['description'] = substr($product['description'],0,33)."...";
				}
				echo $product['description'];
				echo "&lt;/td&gt;\n\r";
				echo "&lt;/tr&gt;\n\r";
				echo "&lt;/table&gt;\n\r";
				echo "&lt;/a&gt;";
				echo "</text>\n\r";
				
				echo "<value>\n\r";
				echo $product['name'];
				echo "</value>\n\r";
				echo "</item>";
			}
		}
		echo "</ajaxresponse>";
		exit();
	}
		//end of written by allen



   
  $gold_gateway_directory = dirname(__FILE__).'/merchants/';
  $gold_nzshpcrt_merchant_list = nzshpcrt_listdir($gold_gateway_directory);
  foreach($gold_nzshpcrt_merchant_list as $gold_nzshpcrt_merchant) {
    if(!is_dir($gold_gateway_directory.$gold_nzshpcrt_merchant)) {
      include_once($gold_gateway_directory.$gold_nzshpcrt_merchant);
    }
    $num++;
  }
  
  
	
	if(count((array)get_option('custom_gateway_options')) == 1) { 
	  // if there is only one active gateway, and it has form fields, append them to the end of the checkout form.
	  $active_gateway = implode('',(array)get_option('custom_gateway_options'));
	  if((count((array)$gateway_checkout_form_fields) == 1) && ($gateway_checkout_form_fields[$active_gateway] != '')) {
			$gateway_checkout_form_field =  $gateway_checkout_form_fields[$active_gateway];	
		}
	}
  //exit("<pre>".print_r($gateway_checkout_form_field,true)."</pre>");
  
  if(file_exists(dirname(__FILE__).'/mp3_functions/mp3_functions.php')) {
    require_once(dirname(__FILE__).'/mp3_functions/mp3_functions.php');
	}
  
  if(file_exists(dirname(__FILE__).'/dropshop/drag_and_drop_cart.php')) {
    require_once(dirname(__FILE__).'/dropshop/drag_and_drop_cart.php');
	}
  
  if(file_exists(dirname(__FILE__).'/grid_display_functions.php')) {
    require_once(dirname(__FILE__).'/grid_display_functions.php');
	}
  
  if(file_exists(dirname(__FILE__).'/members/members.php')) {
   require_once(dirname(__FILE__).'/members/members.php');
	}

    
   if(file_exists(dirname(__FILE__).'/product_slider/product_slider.php')) {
    require_once(dirname(__FILE__).'/product_slider/product_slider.php');
	}
	
   if(file_exists(dirname(__FILE__).'/api_key_generator/api_key_generator.php')) {
    require_once(dirname(__FILE__).'/api_key_generator/api_key_generator.php');
	}
   
   /* re-added by dev.xiligroup 090701 */
   if(file_exists(dirname(__FILE__).'/touchShop/touchShopCore.php')) {
     require_once(dirname(__FILE__).'/touchShop/touchShopCore.php');
   }


  
  if(isset($_GET['activate']) && $_GET['activate'] == 'true') {
    add_action('init', 'gold_shpcrt_install');
	}
   
	if(get_option('show_search') == 1) {
		add_action('wpsc_top_of_products_page', 'gold_shpcrt_search_form');
	}
  add_action('admin_head', 'gold_shpcrt_javascript');
  add_action('wp_head', 'gold_shpcrt_javascript');
  add_action('init', 'wpsc_gold_shpcrt_ajax');
	//exit(get_option('show_live_search'));
  add_action('init', 'gold_shpcrt_preview_image');
}
?>
