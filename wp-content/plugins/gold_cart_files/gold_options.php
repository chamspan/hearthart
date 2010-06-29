<?php
if($_POST != null)
  {
  if($_POST['activation_name'] != null) {
    update_option('activation_name', $_POST['activation_name']);
    }

  if(isset($_POST['activation_key'])) {
    update_option('activation_key', $_POST['activation_key']);
    }

  if($_POST['sox_path'] != null) {
    update_option('sox_path', $_POST['sox_path']);
    }
  $target = "http://instinct.co.nz/wp-goldcart-api/api_register.php?name=".$_POST['activation_name']."&key=".$_POST['activation_key']."&url=".get_option('siteurl')."";
  //exit($target);
  $remote_access_fail = false;
	$useragent = 'WP e-Commerce plugin';
  if(function_exists("curl_init")) {
    ob_start();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    curl_setopt($ch, CURLOPT_URL,$target);
    curl_exec($ch);
    $returned_value = ob_get_contents();
    ob_end_clean();
	} else {
	  $activation_name = urlencode($_POST['activation_name']);
	  $activation_key = urlencode($_POST['activation_key']);
	  $siteurl = urlencode(get_option('siteurl'));
	  $request = '';
	  $http_request  = "GET /wp-goldcart-api/api_register.php?name=$activation_name&key=$activation_key&url=$siteurl HTTP/1.0\r\n";
		$http_request .= "Host: instinct.co.nz\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded; charset=" . get_option('blog_charset') . "\r\n";
		$http_request .= "Content-Length: " . strlen($request) . "\r\n";
		$http_request .= "User-Agent: $useragent\r\n";
		$http_request .= "\r\n";
		$http_request .= $request;
		$response = '';
		if( false != ( $fs = @fsockopen('instinct.co.nz', 80, $errno, $errstr, 10) ) ) {
			fwrite($fs, $http_request);
			while ( !feof($fs) )
				$response .= fgets($fs, 1160); // One TCP-IP packet
			fclose($fs);
		}
		$response = explode("\r\n\r\n", $response, 2);
		$returned_value = (int)trim($response[1]);
	}
      
  if($returned_value == 1) {
		if(get_option('activation_state') != "true") {
			update_option('activation_state', "true");
			gold_shpcrt_install();
		}
		echo "<div class='updated'><p align='center'>".TXT_WPSC_THANKSACTIVATED."</p></div>";
	} else {
		update_option('activation_state', "false");
		echo "<div class='updated'><p align='center'>".TXT_WPSC_NOTACTIVATED."</p></div>";
	}
  //echo $target . "<br />";
  }

do_action('wpsc_gold_module_activation');
		
?>
<div class='wrap'>
	<div class='metabox-holder wpsc_gold_side'>
		<?php
		/* ADDITIONAL GOLD CART MODULES SECTION
		 * ADDED 18-06-09
		 */
		?>
		<strong><?php _e('WP e-Commerce Modules'); ?></strong><br />
		<span><?php _e('Add more functionality to your e-Commerce site'); ?><input type='button' class='button-primary' onclick='window.open ("http://www.instinct.co.nz/shop/","mywindow"); ' value='Go to Shop' id='visitInstinct' name='visitInstinct' /></span>
		
		<br />
		<div class='wpsc_gold_module'>
			<br />
			<strong><?php _e('Pure Gold'); ?></strong>
			<p class='wpsc_gold_text'>Add Products search &amp; additional payment gateways to your e-Commerce install</p>
			<span class='wpsc_gold_info'>$25</span>
		</div>
		<div class='wpsc_gold_module'>
			<br />
			<strong><?php _e('DropShop'); ?></strong>
			<p class='wpsc_gold_text'>Impress your customers with a sliding DropShop </p>
			<span class='wpsc_gold_info'>$75</span>
		</div>
		<div class='wpsc_gold_module'>
			<br />
			<strong><?php _e('Grid View'); ?> </strong>
			<p class='wpsc_gold_text'>Change the layout of your shop with this 960 inspired grid view.</p>
			<span class='wpsc_gold_info'>$15</span>
		</div>
		<div class='wpsc_gold_module'>
			<br />
			<strong><?php _e('MP3 Player'); ?></strong>
			<p class='wpsc_gold_text'>Selling music? Then this is the module for you!</p>
			<span class='wpsc_gold_info'>$10</span>
		</div>
		<div class='wpsc_gold_module'>
			<br />
			<strong><?php _e('Members Only Module'); ?> </strong>
			<p class='wpsc_gold_text'>Private Articles and Images are your business? Sell them with ease using this module.</p>
			<span class='wpsc_gold_info'>$10</span>
		</div>
		<div class='wpsc_gold_module'>
			<br />
			<strong><?php _e('Product Slider'); ?> </strong>
			<p class='wpsc_gold_text'>Display your products in a new and fancy way using the "Product Slider" module.</p>
			<span class='wpsc_gold_info'>$25</span>
		</div>
		<div class='wpsc_gold_module'>
			<br />
			<strong><?php _e('NextGen Gallery Buy Now Buttons'); ?> </strong>
			<p class='wpsc_gold_text'>Make your Online photo gallery into an e-Commerce solution.</p>
			<span class='wpsc_gold_info'>$10</span>
		</div>
	</div>

<div class='wpsc_gold_float'>
<div class='metabox-holder'>
  <h2><?php echo TXT_WPSC_GOLD_OPTIONS;?></h2>
  <form method='post' id='gold_cart_form' action=''>
     <div class='postbox'>
     	<h3 class='hndle'><?php echo TXT_WPSC_ACTIVATE_SETTINGS;?></h3>
		  <?php 
			if(get_option('activation_state') == "true"){
		  ?>
		  		<p><img align='middle' src='../wp-content/plugins/<?php echo WPSC_DIR_NAME; ?>/images/tick.png' alt='' title='' />
		  		&nbsp;The gold cart is currently activated.</p>
		  <?php
		    } else{ 
		  ?>
		    	<p><img align='middle' src='../wp-content/plugins/<?php echo WPSC_DIR_NAME; ?>/images/cross.png' alt='' title=''/>
		    	&nbsp;The gold cart is currently deactivated.</p>
		  <?php
		   }
		  ?>
			<p>
			      <label for='activation_name'><?php echo TXT_WPSC_NAME;?>:</label>
			      <input class='text' type='text' size='40' value='<?php echo get_option('activation_name'); ?>' name='activation_name' id='activation_name' />
			</p>
			<p>
			      <label for='activation_key'><?php echo TXT_WPSC_ACTIVATION_KEY;?>:</label>
			
			      <input class='text' type='text' size='40' value='<?php echo get_option('activation_key'); ?>' name='activation_key' id='activation_key' />
			</p>
			<p>
			      <input type='submit' class='button-primary' value='<?php echo TXT_WPSC_SUBMIT;?>' name='submit_values' />
			      <input type='submit' class='button' value='<?php echo TXT_WPSC_RESET_API;?>' name='reset_values' onclick='document.getElementById("activation_key").value=""' />
			</p>
    </div>
<?php
do_action('wpsc_gold_module_activation_forms');
?>
</form>


</div> 
</div>
</div>
