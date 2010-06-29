<?php

$nzshpcrt_gateways[$num]['name'] = 'Sagepay';
$nzshpcrt_gateways[$num]['internalname'] = 'sagepay';
$nzshpcrt_gateways[$num]['function'] = 'gateway_sagepay';
$nzshpcrt_gateways[$num]['form'] = "form_sagepay";
$nzshpcrt_gateways[$num]['submit_function'] = "submit_sagepay";
$nzshpcrt_gateways[$num]['payment_type'] = "credit_card";

if ( !function_exists('gateway_sagepay') ) {
	function gateway_sagepay($seperator, $sessionid) {
		
		global $wpdb;
		
		$purchase_log_sql = "SELECT * FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `sessionid`= " . $sessionid . " LIMIT 1";
		$purchase_log = $wpdb->get_results($purchase_log_sql, ARRAY_A) ;
		
		$cart_sql = "SELECT * FROM `" . WPSC_TABLE_CART_CONTENTS . "` WHERE `purchaseid`='" . $purchase_log[0]['id'] . "'";
		$cart = $wpdb->get_results($cart_sql, ARRAY_A) ;
		// exit('<pre>' . print_r($cart, true) . '</pre>');
		foreach ( (array)$cart as $item ) {
			$product_data = $wpdb->get_results("SELECT * FROM `" . WPSC_TABLE_PRODUCT_LIST . "` WHERE `id`='" . $item['prodid'] . "' LIMIT 1", ARRAY_A);
			$product_data = $product_data[0];
			// $data['Basket']
		}
		
		//exit('<pre>' . print_r($purchase_log, true) . '</pre>');
		$data['VendorTxCode'] = $sessionid;
		$data['Amount'] = number_format($purchase_log[0]['totalprice'], 2, '.', '');
		$data['Currency'] = get_option('protx_cur');
		$data['Description'] = "wpEcommerce";
		$transact_url = get_option('transact_url');
		$site_url = get_option('shopping_cart_url');
		$data['SuccessURL'] = $transact_url . $seperator . "protx=success";
		$data['FailureURL'] = $site_url;
		
		// exit('<pre>' . print_r($_POST, true) . '</pre>');
		// $data['FailureURL'] = urlencode($transact_url);
		
		if ( $_POST['collected_data'][get_option('protx_form_last_name')] != '' ) {
			$data['BillingSurname'] = urlencode($_POST['collected_data'][get_option('protx_form_last_name')]);
		}
		
		if ( $_POST['collected_data'][get_option('protx_form_post_code')] != '' ) {
			$data['BillingPostCode'] = $_POST['collected_data'][get_option('protx_form_post_code')];
		}
		
		if ( $_POST['collected_data'][get_option('protx_form_address')] != '' ) {
			$data['BillingAddress1'] = $_POST['collected_data'][get_option('protx_form_address')];
		}
		
		if ( $_POST['collected_data'][get_option('protx_form_city')] != '' ) {
			$data['BillingCity'] = $_POST['collected_data'][get_option('protx_form_city')]; 
		}
		
		if ( $_POST['collected_data'][get_option('protx_form_first_name')] != '' ) {
			$data['BillingFirstnames'] = urlencode($_POST['collected_data'][get_option('protx_form_first_name')]);
		}
		
		if ( $_POST['collected_data'][get_option('protx_form_country')] != '' ) {
			$result = $wpdb->get_results("SELECT * FROM `" . WPSC_TABLE_CURRENCY_LIST . "` WHERE isocode='" . $_POST['collected_data'][get_option('protx_form_country')][0] . "'", ARRAY_A);
			if ( $result[0]['isocode'] == 'UK' ) {
				$data['BillingCountry'] = 'GB';
			} else {
				$data['BillingCountry'] = $result[0]['isocode'];
			}
		}
		
		if ( $_POST['collected_data'][get_option('protx_form_last_name')] != '' ) {
			$data['DeliverySurname'] = urlencode($_POST['collected_data'][get_option('protx_form_last_name')]);
		}
		
		if ( $_POST['collected_data'][get_option('protx_form_post_code')] != '' ) {
			$data['DeliveryPostCode'] = $_POST['collected_data'][get_option('protx_form_post_code')];
		}
		
		if ( $_POST['collected_data'][get_option('protx_form_address')] != '' ) {
			$data['DeliveryAddress1'] = $_POST['collected_data'][get_option('protx_form_address')];
		}
	
		if ( $_POST['collected_data'][get_option('protx_form_city')] != '' ) {
			$data['DeliveryCity'] = $_POST['collected_data'][get_option('protx_form_city')]; 
		}
		
		if ( $_POST['collected_data'][get_option('protx_form_first_name')] != '' ) {
			$data['DeliveryFirstnames'] = urlencode($_POST['collected_data'][get_option('protx_form_first_name')]);
		}
		
		if ( preg_match("/^[a-zA-Z]{2}$/", $_SESSION['selected_country']) ) {
			$result = $wpdb->get_results("SELECT * FROM `" . WPSC_TABLE_CURRENCY_LIST . "` WHERE isocode='" . $_SESSION['selected_country'] . "'", ARRAY_A);
			if ( $result[0]['isocode'] == 'UK' ) {
				$data['DeliveryCountry'] = 'GB';
			} else {
				$data['DeliveryCountry'] = $result[0]['isocode'];
			}
		}
		if ( $data['DeliveryCountry'] == '' ) {
			$data['DeliveryCountry'] = 'GB';
		}
		
		
		
		// Start Create Basket Data
		
		$basket_productprice_total = 0;
		$basket_rows = (count($cart) + 1);
		if ( !empty($purchase_log[0]['discount_value']) ) {
			$basket_rows += 1;
		}
		
		$data['Basket'] = $basket_rows . ':';
		
		foreach ( (array)$cart as $item ) {
			$product_data = $wpdb->get_results("SELECT * FROM `" . WPSC_TABLE_PRODUCT_LIST . "` WHERE `id`='" . $item['prodid'] . "' LIMIT 1", ARRAY_A);
			$product_data = $product_data[0];
			$basket_productprice_total += ($item['price'] * $item['quantity']);
			$data['Basket'] .= $product_data['name'] . ":" . $item['quantity'] . ":" . $item['price'] . ":---:" . ($item['price'] * $item['quantity']) . ":" . ($item['price'] * $item['quantity']) . ":";
		}
		
		$basket_delivery = $data['Amount'] - $basket_productprice_total;
		if ( !empty($purchase_log[0]['discount_value']) ) {
			$basket_delivery += $purchase_log[0]['discount_value'];
		}
		$data['Basket'] .= "Delivery:---:---:---:---:" . $basket_delivery;
		
		if ( !empty($purchase_log[0]['discount_value']) ) {
			$data['Basket'] .= ":Discount (" . $purchase_log[0]['discount_data'] . "):---:---:---:---:-" . $purchase_log[0]['discount_value'];
		}
		
		// End Create Basket Data
		
		
		
		$postdata = "";
		$i = 0;
		// exit("<pre>" . print_r($data, true) . "</pre>");
		foreach ( $data as $key => $da ) {
			if ( $i == 0 ) {
				$postdata .= "$key=$da";
			} else {
				$postdata .= "&$key=$da";
			}
			$i++;
		}
		$servertype = get_option('protx_server_type');
		if ( $servertype == 'test' ) {
			$url = 'https://test.sagepay.com/gateway/service/vspform-register.vsp';
		} elseif ( $servertype == 'sim' ) {
			$url = 'https://test.sagepay.com/Simulator/VSPFormGateway.asp';
		} elseif ( $servertype == 'live' ) {
			$url = 'https://live.sagepay.com/gateway/service/vspform-register.vsp';
		}
		$crypt = base64_encode(SimpleXor($postdata, get_option('protx_enc_key')));
		$postdata1['VPSProtocol'] = get_option("protx_protocol");
		$postdata1['TxType'] = "PAYMENT";
		$postdata1['Vendor'] = get_option("protx_name");
		//$postdata1['VendorTxCode'] = $sessionid;
		$postdata1['Crypt'] = $crypt;
		
		$j = 0;
		$postdata2 = "";
		foreach ( $postdata1 as $key=>$dat ) {
			if ( $j == 0 ) {
				$postdata2 .= "$key=$dat";
			} else {
				$postdata2 .= "&$key=$dat";
			}
			$j++;
		}
		
		$output = "<form id=\"sagepay_form\" name=\"sagepay_form\" method=\"post\" action=\"$url\">\n";
		$output .= "<input type='text' value ='2.23' name='VPSProtocol' />";
		$output .= "<input type='text' value ='PAYMENT' name='TxType' />";
		$output .= "<input type='text' value ='" . get_option("protx_name") . "' name='Vendor' />";
		$output .= "<input type='text' value ='" . $crypt . "' name='Crypt' />";
		$output .= "</form>";
		$output .= "<script language=\"javascript\" type=\"text/javascript\">document.getElementById('sagepay_form').submit();</script>";
		
		echo $output;
		exit();
		
	}
	
	function submit_sagepay() {
		if ( $_POST['protx_name'] != null ) {
			update_option('protx_name', $_POST['protx_name']);
		}
		
		if ( $_POST['protx_protocol'] != null ) {
			update_option('protx_protocol', $_POST['protx_protocol']);
		}
		
		if ( $_POST['protx_enc_key'] != null ) {
			update_option('protx_enc_key', $_POST['protx_enc_key']);
		}
		
		if ( $_POST['protx_cur'] != null) {
			update_option('protx_cur', $_POST['protx_cur']);
		}
		
		if ( $_POST['protx_server_type'] != null ) {
			update_option('protx_server_type', $_POST['protx_server_type']);
		}
		
		foreach( (array)$_POST['protx_form'] as $form => $value ) {
			update_option(('protx_form_'.$form), $value);
		}
		return true;
	}
	
	function form_sagepay() {
		global $wpdb;
		$servertype = get_option('protx_server_type');
		$servertype1 = "";
		$servertype2 = "";
		$servertype3 = "";
		
		if ( $servertype == 'test' ){
			$servertype1 = 'selected="selected"';
		} elseif ( $servertype == 'sim' ) {
			$servertype2 = 'selected="selected"';				
		} elseif ( $servertype == 'live' ) {
			$servertype3 = 'selected="selected"';			
		}
		$query = "SELECT DISTINCT code FROM `" . WPSC_TABLE_CURRENCY_LIST . "` ORDER BY code";
		$result = $wpdb->get_results($query, ARRAY_A);
		$output = "<table>
			<tr>
				<td>
					Protx Vendor name:
				</td>
				<td>
					<input type='text' size='40' value='".get_option('protx_name')."' name='protx_name' />
				</td>
			</tr>
			<tr>
				<td>
					Protx VPS Protocol:
				</td>
				<td>
				<input type='text' size='20' value='".get_option('protx_protocol')."' name='protx_protocol' /> e.g. 2.22
				</td>
			</tr>
			<tr>
				<td>
				Protx Encryption Key:
				</td>
				<td>
					<input type='text' size='20' value='".get_option('protx_enc_key')."' name='protx_enc_key' />
				</td>
			</tr>
			<tr>
				<td>
					Server Type:
				</td>
				<td>
					<select name='protx_server_type'>
						<option $servertype1 value='test'>Test Server</option>
						<option $servertype2 value='sim'>Simulator Server</option>
						<option $servertype3 value='live'>Live Server</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>
					Select your currency
				</td>
				<td>
					<select name='protx_cur'>";
						$current_currency = get_option('protx_cur');
						//exit($current_currency);
						foreach ( (array)$result as $currency ) {
							if ( $currency['code'] == $current_currency ) {
								$selected = "selected = 'true'";
							} else {
								$selected = "";
							}
							$output.= "<option $selected value='" . $currency['code'] . "'>" . $currency['code'] . "</option>";
						}
						$output .= "</select>
				</td>
			</tr>
		</table>";
		
		$output .= "<h2>Forms Sent to Gateway</h2>
		<table>
			<tr>
				<td>
					First Name Field
				</td>
				<td>
					<select name='protx_form[first_name]'>
					" . nzshpcrt_form_field_list(get_option('protx_form_first_name')) . "
					</select>
				</td>
			</tr>
			<tr>
				<td>
					Last Name Field
				</td>
				<td>
					<select name='protx_form[last_name]'>
					".nzshpcrt_form_field_list(get_option('protx_form_last_name'))."
					</select>
				</td>
			</tr>
			<tr>
				<td>
					Address Field
				</td>
				<td>
					<select name='protx_form[address]'>
					".nzshpcrt_form_field_list(get_option('protx_form_address'))."
					</select>
				</td>
			</tr>
			<tr>
				<td>
					City Field
				</td>
				<td>
					<select name='protx_form[city]'>
					".nzshpcrt_form_field_list(get_option('protx_form_city'))."
					</select>
				</td>
			</tr>
			<tr>
				<td>
					State Field
				</td>
				<td>
					<select name='protx_form[state]'>
					".nzshpcrt_form_field_list(get_option('protx_form_state'))."
					</select>
				</td>
			</tr>
			<tr>
				<td>
					Postal code/Zip code Field
				</td>
				<td>
					<select name='protx_form[post_code]'>
					".nzshpcrt_form_field_list(get_option('protx_form_post_code'))."
					</select>
				</td>
			</tr>
			<tr>
				<td>
					Country Field
				</td>
				<td>
					<select name='protx_form[country]'>
					".nzshpcrt_form_field_list(get_option('protx_form_country'))."
					</select>
				</td>
			</tr>
		</table> ";
		return $output;
	}
	
	function simpleXor($InString, $Key) {
		// Initialise key array
		$KeyList = array();
		// Initialise out variable
		$output = "";
		
		// Convert $Key into array of ASCII values
		for ( $i = 0; $i < strlen($Key); $i++ ) {
			$KeyList[$i] = ord(substr($Key, $i, 1));
		}
		
		// Step through string a character at a time
		for ( $i = 0; $i < strlen($InString); $i++ ) {
			// Get ASCII code from string, get ASCII code from key (loop through with MOD), XOR the two, get the character from the result
			// % is MOD (modulus), ^ is XOR
			$output .= chr(ord(substr($InString, $i, 1)) ^ ($KeyList[$i % strlen($Key)]));
		}
		
		// Return the result
		return $output;
	}
	
}

function nzshpcrt_sagepay_decryption() {
	if ( get_option('permalink_structure') != '' ) {
		$seperator = "?";
	} else {
		$seperator = "&";
	}
	$crypt = str_replace(" ", "+", $_GET['crypt']);
	$uncrypt = SimpleXor(base64_decode($crypt), get_option('protx_enc_key'));
	parse_str($uncrypt, $unencrypted_values);
	// exit('<pre>' . print_r($unencrypted_values, true) . '</pre>');
	$transact_url = get_option('transact_url') . $seperator . "sessionid=" . $unencrypted_values['VendorTxCode'];
	// exit( "<pre>" . print_r($transact_url, true) . "</pre>");
	header("Location: $transact_url");
	exit();
}

if ( isset($_GET['protx']) && $_GET['protx'] == 'success' && ($_GET['crypt'] != '') ) {
	add_action('init', 'nzshpcrt_sagepay_decryption');
}



?>