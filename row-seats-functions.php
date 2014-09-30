<?php
function widgets_init() {
	register_widget('allevents_widget');
}
	
function rst_transaction_details()
{
global $wpdb;

//Displaying transaction details on admin liftOff

					if($_GET['action']=="rst-transaction-details")
					{

						if (isset($_GET["id"]) && !empty($_GET["id"])) {
							$id = intval($_GET["id"]);
							$transaction_details = $wpdb->get_row("SELECT * FROM rst_payment_transactions WHERE id = '".$id."' AND deleted = '0'", ARRAY_A);
							if (intval($transaction_details["id"]) != 0) {
								echo '
								<html>
								<head>
									<title>'.__('Transaction Details', 'rst').'</title>
								</head>
								<body>
									<table style="width: 100%;">';
								$details = explode("&", $transaction_details["details"]);
								foreach ($details as $param) {
									$data = explode("=", $param, 2);
									echo '
								<tr>
									<td style="width: 170px; font-weight: bold;">'.esc_attr($data[0]).'</td>
									<td>'.esc_attr(urldecode($data[1])).'</td>
								</tr>';
								}
								echo '
								</table>
							</body>
							</html>';

							} else echo __('No data found!', 'row_seats');
						} else echo __('No data found!', 'row_seats');
						die();

					}
//Deleting a transaction from admin side		
					if($_GET['action']=="rst-delete-transaction")
					{

						$id = intval($_GET["id"]);
						$transaction_details = $wpdb->get_row("SELECT * FROM rst_payment_transactions WHERE id = '".$id."' AND deleted = '0'", ARRAY_A);
						if (empty($transaction_details)) {
							setcookie("rst_error", __('Selected record not found.', 'rst'), time()+30, "/", ".".str_replace("www.", "", $_SERVER["SERVER_NAME"]));
							header('Location: '.admin_url('admin.php').'?page=rst-transactions');
							die();
						}

						$sql = "UPDATE rst_payment_transactions SET deleted = '1' WHERE id = '".$id."'";
						if ($wpdb->query($sql) !== false) {
							setcookie("rst_info", __('Selected record successfully removed.', 'rst'), time()+30, "/", ".".str_replace("www.", "", $_SERVER["SERVER_NAME"]));
							header('Location: '.admin_url('admin.php').'?page=rst-transactions');
							die();
						} else {
							setcookie("rst_error", __('Internal error occured.', 'rst'), time()+30, "/", ".".str_replace("www.", "", $_SERVER["SERVER_NAME"]));
							header('Location: '.admin_url('admin.php').'?page=rst-transactions');
							die();
						}
					}
//Setting an offline registration as paid from admin side		
					if($_GET['action']=="rst-paid-transaction")
					{

						$id = intval($_GET["id"]);
						$transaction_details = $wpdb->get_row("SELECT * FROM rst_payment_transactions WHERE id = '".$id."' AND deleted = '0'", ARRAY_A);
						if (intval($transaction_details["id"]) != 0) {	
						$gross_total=$transaction_details["gross"];		
						$sql = "select * from $wpdb->rst_bookings rstbk,$wpdb->rst_shows rsts where  rsts.id = rstbk.show_id and rstbk.booking_id =" . $transaction_details["tx_str"];  
        $sql = "select * from $wpdb->rst_bookings rstbk,$wpdb->rst_booking_seats_relation bsr,$wpdb->rst_shows rsts

        where  bsr.booking_id = rstbk.booking_id

        and rsts.id = bsr.show_id

        and bsr.booking_id =" . $transaction_details["tx_str"];		
						
						if ($results = $wpdb->get_results($sql, ARRAY_A)) {
							$booking_details = $wpdb->get_results($sql, ARRAY_A);       
							$data = $booking_details;
							$show_name = $booking_details[0]['show_name'];
							
							$show_date= $booking_details[0]['show_date'];
							$booking_details = $booking_details[0]['booking_details'];
							$ticketno = $rst_options['rst_ticket_prefix'] . $_POST['x_invoice_num'];
							$booking_details = unserialize($booking_details);
							$ticket_seat_no=array();
							for ($row = 0; $row < count($booking_details); $row++) {
								$seats = $booking_details[$row]['seatno'];
								$rowname = $booking_details[$row]['row_name'];
								$ticket_seat_no[]=$rowname . $seats;								
							}
							$ticket_seat_no=implode(",",$ticket_seat_no);						
							sendrstmail($data, "TXYN".$transaction_details["tx_str"]); //Sending tickets to customer
						}		

						$payment_status="Completed";
						$sql = "UPDATE rst_payment_transactions SET transaction_type = 'Offline Payment:Paid',payment_status='".$payment_status."' WHERE id = '".$id."'";
						$wpdb->query($sql);	
						//sending notification to payer.
						$tags = array('{payer_name}', '{payer_email}', '{payment_status}', '{show_name}', '{show_date}', '{seats}','{amount}');
						$vals = array($transaction_details["payer_name"], $transaction_details["payer_email"], $payment_status,$show_name ,$show_date,$ticket_seat_no,$gross_total );
						$body = str_replace($tags, $vals, get_option('rst_success_email_body'));

						$mail_headers = "Content-Type: text/plain; charset=utf-8\r\n";
						$mail_headers .= "From: ".get_option('rst_from_name')." <".get_option('rst_from_email').">\r\n";
						$mail_headers .= "X-Mailer: PHP/".phpversion()."\r\n";
						wp_mail($transaction_details["payer_email"], get_option('rst_success_email_subject'), $body, $mail_headers);				
							
						}
						header('Location: '.admin_url('admin.php').'?page=rst-transactions');
						exit;
					}			
					

					if($_GET['action']=="rst-export-transactions")
					{

						$sql = "SELECT * FROM rst_payment_transactions WHERE deleted = '0' ORDER BY created DESC";
						$rows = $wpdb->get_results($sql, ARRAY_A);
						if (sizeof($rows) > 0) {
							if (strstr($_SERVER["HTTP_USER_AGENT"],"MSIE")) {
								header("Pragma: public");
								header("Expires: 0");
								header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
								header("Content-type: application-download");
								header("Content-Disposition: attachment; filename=\"transactions.csv\"");
								header("Content-Transfer-Encoding: binary");
							} else {
								header("Content-type: application-download");
								header("Content-Disposition: attachment; filename=\"transactions.csv\"");
							}
							$separator = ",";
							if ($separator == 'tab') $separator = "\t";					

							$transaction_columns = array(
								'bookingid' => __('Booking ID', 'row_seats'),
								'payer_name' => __('Payer Name', 'row_seats'),
								'payer_email' => __('Payer E-mail', 'row_seats'),
								'amount' => __('Amount', 'row_seats'),
								'currency' => __('Currency', 'row_seats'),
								'status' => __('Status', 'row_seats'),
								'paymentmethod' => __('Payment Method', 'row_seats'),
								'created' => __('Created', 'row_seats'),
								'first_name' => __('First Name', 'row_seats'),
								'last_name' => __('Last Name', 'row_seats'),
								'address' => __('Address', 'row_seats'),
								'city' => __('City', 'row_seats'),
								'state' => __('State', 'row_seats'),
								'zip' => __('ZIP/Postal Code', 'row_seats'),
								'country' => __('Country', 'row_seats'),
								'phone' => __('Phone', 'row_seats')							
							);


							$i = 0;
							foreach($transaction_columns as $value) {
								echo ($i > 0 ? $separator : '').'"'.str_replace('"', '', $value).'"';
								$i++;

							}
							echo PHP_EOL;
							foreach ($rows as $row) {
								$transaction_column_values = array(
									'certificates' => $row['id'],
									'payer_name' => $row['payer_name'],
									'payer_email' => $row['payer_email'],
									'amount' => number_format($row['gross'], 2, ".", ""),
									'currency' => $row['currency'],
									'status' => $row["payment_status"],
									'paymentmethod' => $row["transaction_type"],
									'created' => date("Y-m-d H:i:s", $row["created"]),
									'first_name' =>$row['first_name'],
									'last_name' =>$row['last_name'],
									'address' =>$row['address'],
									'city' =>$row['city'],
									'state' =>$row['state'],
									'zip' =>$row['zip'],
									'country' =>$row['country'],
									'phone' =>$row['phone']							
								);

								$i = 0;
								foreach($transaction_column_values as $value) {
									echo ($i > 0 ? $separator : '').'"'.str_replace('"', '', $value).'"';
									$i++;
								}
								echo PHP_EOL;
							}
							exit;
						}
						header("Location: ".admin_url('admin.php')."?page=rst-transactions");
						exit;
					}


}
function wp_row_seats_signup_call()
{
	$rst_options = get_option(RSTPLN_OPTIONS);

	$rst_paypal_options = get_option(RSTPLN_PPOPTIONS);
	
	$wplanguagesoptions = get_option(RSTLANGUAGES_OPTIONS);
	$event_warning_message="Attention! Please correct the errors below and try again.";
	$event_enter_name="Please enter name";
    $event_enter_email="Please enter email";
    $event_enter_phone="Please enter phone";
    $event_enter_terms="Please agree to our Terms and Conditions to continue";
	$event_customer_name="Name";
	$event_customer_email="Email";
	$event_customer_phone="Phone";
	$event_seat="Seat";
	$event_item_cost="Cost";
	$offline_purchase="Purchase";
	$offline_edit_info="Edit info";
    
	if($wplanguagesoptions['rst_enable_languages']=="on")
	{
		if($wplanguagesoptions['languages_event_enter_name'])
		{
			$event_enter_name=$wplanguagesoptions['languages_event_enter_name'];
		}
		if($wplanguagesoptions['languages_event_enter_email'])
		{
			$event_enter_email=$wplanguagesoptions['languages_event_enter_email'];
		}
		if($wplanguagesoptions['languages_event_enter_phone'])
		{
			$event_enter_phone=$wplanguagesoptions['languages_event_enter_phone'];
		}
		if($wplanguagesoptions['languages_event_enter_terms'])
		{
			$event_enter_terms=$wplanguagesoptions['languages_event_enter_terms'];
		}
        if($wplanguagesoptions['languages_event_customer_name'])
		{
			$event_customer_name=$wplanguagesoptions['languages_event_customer_name'];
		}			
        if($wplanguagesoptions['languages_event_customer_email'])
		{
			$event_customer_email=$wplanguagesoptions['languages_event_customer_email'];
		}			
        if($wplanguagesoptions['languages_event_customer_phone'])
		{
			$event_customer_phone=$wplanguagesoptions['languages_event_customer_phone'];
		}		
        if($wplanguagesoptions['languages_event_warning_message'])
		{
			$event_warning_message=$wplanguagesoptions['languages_event_warning_message'];
		}	
        if($wplanguagesoptions['languages_event_seat'])
		{
			$event_seat=$wplanguagesoptions['languages_event_seat'];
		}			
        if($wplanguagesoptions['languages_event_item_cost'])
		{
			$event_item_cost=$wplanguagesoptions['languages_event_item_cost'];
		}
		if($wplanguagesoptions['languages_offline_purchase'])
		{
			$offline_purchase=$wplanguagesoptions['languages_offline_purchase'];
		}
		if($wplanguagesoptions['languages_offline_edit_info'])
		{
			$offline_edit_info=$wplanguagesoptions['languages_offline_edit_info'];
		}
			
	}	
	
	
    $symbol = $rst_paypal_options['currencysymbol'];
	$symbol = get_option('rst_currencysymbol');
	$currency = get_option('rst_currency');
    $symbols = array(
        "0" => "$",
        "1" => "&pound;",
        "2" => "&euro;",
        "3" => "&#3647;",
        "4" => "&#8362;",
        "5" => "&yen;",
        "6" => "&#8377;",
        "7" => "R$",
        "8" => "kr",
        "9" => "zł",
        "10" => "Ft",
        "11" => "Kč",
        "12" => "&#1088;&#1091&#1073;",
        "13" => "&#164;",
        "14" => "&#x20B1;",
        "15" => "Fr",
        "16" => "RM");

    $symbol = $symbols[$symbol];
    //proccess the form offline if there is no active payment modules available 
	if($_POST['action']=="row_seats_default_offlinepayment")
	{
	offline_payment_form_process();
	exit;
	}

	if($_POST['action']=="wp_row_seats-signup")
	{

		header ('Content-type: text/html; charset=utf-8');
		print "<html><body>";
		$errors = array();
		$customfield_data= apply_filters('row_seats_custom_field_data',$_REQUEST['parameter']);
		if(!$_POST['contact_name'])		{
			$errors['enter_name'] = '<li>'.__($event_enter_name, 'row_seats').'</li>';		}
		if(!$_POST['contact_email'])
		{
			$errors['contact_email'] = '<li>'.__($event_enter_email, 'row_seats').'</li>';
		}
if (!filter_var($_POST['contact_email'], FILTER_VALIDATE_EMAIL)) {
   // echo "This ($email_a) email address is considered valid.";
	$errors['contact_email'] = '<li>'.__('Please enter a valid email', 'row_seats').'</li>';
}

		
		if(!$_POST['contact_phone'])
		{
			$errors['contact_phone'] = '<li>'.__($event_enter_phone, 'row_seats').'</li>';
		}
$errors= apply_filters('row_seats_custom_field_error',$errors);
		
		if(!$_POST['rstterms'])
		{
			$errors['rstterms'] = '<li>'.__($event_enter_terms, 'row_seats').'</li>';
		}
		
		if(count($errors)>0)
		{
			print "
			<div class='row_seats_error_message'>".__($event_warning_message, 'row_seats')."
				<ul class='row_seats_error_messages'>".implode('', $errors)."</ul>
			</div>";

		}else{ 

			print "<div class='row_seats_confirmation_info'>";
			$payment_method = trim(stripslashes($_POST["payment_method"]));
            $data=array();
			foreach( $_POST as $key=>$value)
            {
            $data[$key]=$value;
            }	
			
			$data['currency']=$currency;
					$checkout_summary = array(					
						'contact_name' => array(
							'title' => __($event_customer_name, 'row_seats'),
							'value' => $_POST["contact_name"]
						),
						'contact_email' => array(
							'title' => __($event_customer_email, 'row_seats'),
							'value' => $_POST["contact_email"]
						),

						'contact_phone' => array(
							'title' => __($event_customer_phone, 'row_seats'),
							'value' => $_POST["contact_phone"]
						)
					);	
			//Select offline mode if Admin or if there is no active payment		
			if($_POST['payment_method']=="offlinepayment_force")
			{	
						$checkout_summary['payment_method'] = array(
							'title' => __('Payment Gateway', 'rst'),
							'value' => 'Offline payment'
						);							
			}else{

						$checkout_summary['payment_method'] = array(
							'title' => __('Payment Gateway', 'rst'),
							'value' => apply_filters('row_seats_payment_logo', '', $payment_method)
						);	


			}			
			//Fetching cart products
			$cartitems = unserialize(base64_decode($_POST['mycartitems']));
//print "<br><br><br>";
			//print_r($cartitems);
			//print "<br><br><br>";
			
    $showid = $cartitems[0]['show_id'];
    $showdata1['vmid'] = $showid;
    $showdata1 = rst_shows_operations('byid', $showdata1, '');

    $showdata1 = $showdata1[0];
    $eventname1 = $showdata1['show_name'];
	$mytseats=array();
	$myitems=array();
	
			$subtotal=0; 		
			for ($i = 0; $i < count($cartitems); $i++) {
				$name ="Seat:".$cartitems[$i]['row_name'] . $cartitems[$i]['seatno'];
				$mytseats[]=$cartitems[$i]['row_name'] . $cartitems[$i]['seatno'];
				$price=$cartitems[$i]['price'];
				if($rst_options['rst_enable_special_pricing']=="on" and row_seats_special_pricing_verification())
				{
					$myproductsarraytemp=split("#",$_POST['special_pricing'.$i]);
					$name.=" - ".$myproductsarraytemp[0];
					$price=$myproductsarraytemp[1];
				}
$itesmname=$eventname1." Seat:".$cartitems[$i]['row_name'] . $cartitems[$i]['seatno'];		
$myitems[]=array('name'=>$itesmname,'price'=>$price);			
	
				$subtotal+=$price;
				$price=$price;
				$checkout_summary['cartproducts'.$i] = array(
											'title' => __($name, 'row_seats'),
											'value' => $symbol.number_format($price, 2, ".", "")
										);
			}
$data['myitems']=$myitems;			
		$data['event_name_display']=$eventname1." ".implode(",",$mytseats);
		
					if($_POST['fee_name'] && $_POST['rst_fees'])	
			{
			$data['feeifany']=$_POST['rst_fees'];
			$data['feeifany_name']=$_POST['fee_name'];
			}
			if($_POST['coupondiscount'] && $_POST['appliedcoupon'] && $_POST['statusofcouponapply']=="success")	
			{
             $data['discountifany']=$_POST['coupondiscount'];
			}			
			

			echo '<table class="row_seats__confirmation_table">';

			foreach($checkout_summary as $info) {

				echo '	<tr>
						<td class="row_seats__confirmation_title">'.$info['title'].':</td>
						<td class="row_seats__confirmation_data">'.$info['value'].'</td>
					</tr>';

			}


			
			if($_POST['fee_name'] && $_POST['rst_fees'])	
			{
				echo '	<tr>
						<td class="row_seats__confirmation_title">'.$_POST['fee_name'].':</td>
						<td class="row_seats__confirmation_data">'.$symbol.number_format($_POST['rst_fees'], 2, ".", "").'</td>
					</tr>';

			}
			if($_POST['coupondiscount'] && $_POST['appliedcoupon'] && $_POST['statusofcouponapply']=="success")	
			{
				echo '	<tr>
						<td class="row_seats__confirmation_title">Coupon ('.$_POST['appliedcoupon'].'):</td>
						<td class="row_seats__confirmation_data">'.$symbol.number_format($_POST['coupondiscount'], 2, ".", "").'</td>
					</tr>';

			}
			if($_POST['amount'] && $_POST['amount'])	
			{
				echo '	<tr>
						<td class="row_seats__confirmation_title"><b>Grand Total:</b></td>
						<td class="row_seats__confirmation_data"><b>'.$symbol.number_format($_POST['amount'], 2, ".", "").'</b></td>
					</tr>';

			}			

			echo '</table>';					
			//Force offline method on 3 conditions 1. If admin, 2. If there is no active payment gateway 3. If total amount is ZERO					
			if($_POST['payment_method']=="offlinepayment_force" || $_POST['amount']==0)
			{
			$data['payment_method']="offlinepayment_force";
			offline_payment_form($data);
			}else{
			do_action('row_seats_echo_payment_form', $data);
			}			
			

		    //Genrating checkout buttons
			$buttons = array(					

				'purchase' => array(
					'title' => __($offline_purchase, 'row_seats'),
					'onclick' => "savebookingcustom();"
				),
				'edit' => array(
					'title' => __($offline_edit_info, 'row_seats'),
					'onclick' => "row_seats_edit();"
				)
			);

			echo '
			<div class="row_seats_signup_buttons">';
					foreach($buttons as $key => $button) {
						echo '&nbsp;&nbsp;<input type="button" id="'.$prefix.$key.'" class="row_seats_submit" value="'.esc_attr($button['title']).'" '.(!empty($button['onclick']) ? ' onclick="'.$button['onclick'].'"' : '').'>';
					}
					echo '<img id="'.$prefix.'loading2" class="row_seats_loading" src="'.plugins_url('/images/loading.gif', __FILE__).'" alt=""></div>';
			
			print "</div>";
		}
		print "</body></html>";
		exit;
	}
}
function row_seats_special_pricing_verification()
{
    $installedplugins = get_option('active_plugins');
    $found = false;
    foreach ($installedplugins as $key => $value) {
        $pos = strpos($value, 'row-seats-special-pricing.php');
        if ($pos === false) {
        } else {
            $found = true;
        }
    }
    return $found;
}

function registerOptions()
{
    global $wpdb;

    rst_create_tables();
    if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb->rst_booking_seats_relation'") == $wpdb->rst_booking_seats_relation) {
        $sql = "ALTER TABLE " . $wpdb->rst_booking_seats_relation . " ADD COLUMN booking_status varchar(10) NULL";

			$sqlexist="SHOW columns from rst_booking_seats_relation where field='booking_status'";
			$sqlexist_details = $wpdb->get_results($sqlexist, ARRAY_A);
			if(count($sqlexist_details)==0)
			{
            $sql = "ALTER TABLE " . $wpdb->rst_booking_seats_relation . " ADD COLUMN booking_status varchar(10) NULL";
            $wpdb->query($sql);
			}	

			
		
        //mysql_query($sql);
		//$wpdb->query($sql);

    }
    if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb->rst_booking_seats_relation'") == $wpdb->rst_booking_seats_relation) {
        $sql = "ALTER TABLE " . $wpdb->rst_booking_seats_relation . " ADD COLUMN date_of_cancel TIMESTAMP  NULL";

			$sqlexist="SHOW columns from rst_booking_seats_relation where field='date_of_cancel'";
			$sqlexist_details = $wpdb->get_results($sqlexist, ARRAY_A);
			if(count($sqlexist_details)==0)
			{
            $sql = "ALTER TABLE " . $wpdb->rst_booking_seats_relation . " ADD COLUMN date_of_cancel TIMESTAMP  NULL";
            $wpdb->query($sql);
			}	

		//$wpdb->query($sql);

    }
    if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb->rst_booking_seats_relation'") == $wpdb->rst_booking_seats_relation) {
        $sql = "ALTER TABLE " . $wpdb->rst_booking_seats_relation . " ADD COLUMN b_seatid int(11)  NULL";
		
		
			$sqlexist="SHOW columns from rst_booking_seats_relation where field='b_seatid'";
			$sqlexist_details = $wpdb->get_results($sqlexist, ARRAY_A);
			if(count($sqlexist_details)==0)
			{
            $sql = "ALTER TABLE " . $wpdb->rst_booking_seats_relation . " ADD COLUMN b_seatid int(11)  NULL";
            $wpdb->query($sql);
			}
        //mysql_query($sql);
		//$wpdb->query($sql);

    }
    if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb->rst_booking_seats_relation'") == $wpdb->rst_booking_seats_relation) {
        $sql = "ALTER TABLE " . $wpdb->rst_booking_seats_relation . " ADD COLUMN comments TEXT  NULL";

			$sqlexist="SHOW columns from rst_booking_seats_relation where field='comments '";
			$sqlexist_details = $wpdb->get_results($sqlexist, ARRAY_A);
			if(count($sqlexist_details)==0)
			{
             $sql = "ALTER TABLE " . $wpdb->rst_booking_seats_relation . " ADD COLUMN comments TEXT  NULL";
            $wpdb->query($sql);
			}		
		
        //mysql_query($sql);
		//$wpdb->query($sql);

    }
	$rst_options = get_option(RSTPLN_OPTIONS);
	
	
}


/*
 * Adds extra submenus and menu options to the admin panel's menu structure
 */
function adminMenu()
{
    if (function_exists('rstCheckAccessPermissions') && rstCheckAccessPermissions()) {
        // available for Contributors
        $capability = 'edit_posts';
    } else {
        // available only for Administrators
        $capability = 'manage_options';
    }

    // available only for Administrators
    $capability_for_settings = 'manage_options';

    add_menu_page(__('Row Seats', 'menu-test'), __('Row Seats', 'menu-test'), $capability, 'rst-intro', 'rst_intro_page', RSTPLN_URL . 'images/row-seat-ico.png');
    add_submenu_page('rst-intro', __('Row Seats Settings', 'menu-test'), __('Row Seats Settings', 'menu-test'), $capability_for_settings, 'rst-settings', 'rst_settings');
	add_submenu_page('rst-intro', __('Payment Settings', 'menu-test'), __('Payment Settings', 'menu-test'), $capability_for_settings, 'rst-pay-settings', 'rst_pay_settings');
    add_submenu_page('rst-intro', __('Manage Seats', 'menu-test'), __('Manage Seats', 'menu-test'), $capability, 'rst-manage-seats', 'rst_manage_seats');
	///add_submenu_page('rst-intro', __('Special Price', 'menu-test'), __('Special Price', 'menu-test'), $capability, 'rst-special-price', 'rst_special_price');
	add_submenu_page('rst-intro', __('Transactions', 'menu-test'), __('Transactions', 'menu-test'), $capability, 'rst-transactions', 'rst_transactions');
    add_submenu_page('rst-intro', __('Add an Event', 'menu-test'), __('Add an Event', 'menu-test'), $capability, 'rst-manage-seats-moncal', 'rst_manage_seats_moncalender');
	add_submenu_page('rst-intro', __('Reports', 'menu-test'), __('Reports', 'menu-test'), $capability, 'rst-reports', 'rst_reports');
    //add_submenu_page('rst-intro', __('Wpuser Access', 'menu-test'), __('Wpuser Access', 'menu-test'), $capability, 'wpuser-access', 'wpuser_access');
	//add_submenu_page('rst-intro', __('Seat color', 'menu-test'), __('Seat color', 'menu-test'), $capability, 'seat-color', 'seat_color');
	
		//wp_register_script('jscolor.js', plugin_dir_url(__FILE__) . 'js/jscolor/jscolor.js', array('jquery'));
		//wp_enqueue_script('jscolor.js');

		
}


/*
 * Generates seat chart html-content that is used to replace the shortcode
 */
function gettheseatchart($showid, $type = '')
{

global $screenspacing,$wpdb;
    if ($type == 'offline') {
        $offlineAdmin = 'admin';
    }
    if ($offlineAdmin != 'admin') {
        $type = apply_filters('rst_paypal_payment_type', '');
    }
    if (!$type) {
        $type = 'offline';
    }
    if ($type == 'paypal') {
        $type = '';
    }

	
	
$rst_options = get_option(RSTPLN_OPTIONS);	
if($rst_options['rst_idle_time'])	
{
$rstidlecounter=$rst_options['rst_idle_time'];
}else{
$rstidlecounter=10;
}

if($rst_options['rst_idle_clear_cart'])	
{
$rst_idle_clear_cart=$rst_options['rst_idle_clear_cart'];
}else{
$rst_idle_clear_cart=7;
}		
	
    $currenturl = curPageURL();

    $rst_paypal_options = get_option(RSTPLN_PPOPTIONS);
	$wplanguagesoptions = get_option(RSTLANGUAGES_OPTIONS);
	$event_name_title="Event name";
	$event_booking_closed="*Online Booking is Closed, We Appreciate Your Patronage!";
	$booking_warning="Online booking will close {DURATION} prior to engagement";
	$event_booking_warning1="Online booking will close";
	$event_booking_warning2="prior to engagement";
	$event_datetime="Event Date & Time";
	$event_venue="Venue";
	$event_empty_warning="YOUR CART WILL EMPTY IF IDLE FOR ".$rst_idle_clear_cart."MIN.";
	$event_view_cart="View Cart";
	$event_seat_processing="Processing.....";
	$coupon_apply_memberid="Apply Member ID";
	$coupon_apply_coupon="Apply Coupon";
	$coupon_enter_memberid="Enter Member ID";
	$wpuser_onlyloggedin="Sorry, Only logged in users can access this show.";
	$event_double_booking="Sorry this seat is already booked by someone else";





	
	if($wplanguagesoptions['rst_enable_languages']=="on")
	{
		if($wplanguagesoptions['languages_event_double_booking'])
		{
			$event_double_booking=$wplanguagesoptions['languages_event_double_booking'];
		}
		
		if($wplanguagesoptions['languages_wpuser_onlyloggedin'])
		{
			$wpuser_onlyloggedin=$wplanguagesoptions['languages_wpuser_onlyloggedin'];
		}	
		if($wplanguagesoptions['languages_event_name'])
		{
			$event_name_title=$wplanguagesoptions['languages_event_name'];
		}
		if($wplanguagesoptions['languages_booking_closed'])
		{
			$event_booking_closed=$wplanguagesoptions['languages_booking_closed'];
		}		
		if($wplanguagesoptions['languages_booking_warning1'])
		{
			//$event_booking_closed=$wplanguagesoptions['languages_booking_warning1'];
		}		
		if($wplanguagesoptions['languages_booking_warning2'])
		{
			//$event_booking_closed=$wplanguagesoptions['languages_booking_warning2'];
		}		
		if($wplanguagesoptions['languages_event_datetime'])
		{
			$event_datetime=$wplanguagesoptions['languages_event_datetime'];
		}	
		if($wplanguagesoptions['languages_event_venue'])
		{
			$event_venue=$wplanguagesoptions['languages_event_venue'];
		}	
		if($wplanguagesoptions['languages_event_empty_warning'])
		{
			$event_empty_warning=$wplanguagesoptions['languages_event_empty_warning'];
		}		
		if($wplanguagesoptions['languages_event_view_cart'])
		{
			$event_view_cart=$wplanguagesoptions['languages_event_view_cart'];
		}	
		if($wplanguagesoptions['languages_booking_warning'])
		{
			$booking_warning=$wplanguagesoptions['languages_booking_warning'];
		}	

		if($wplanguagesoptions['languages_event_seat_processing'])
		{
			$event_seat_processing=$wplanguagesoptions['languages_event_seat_processing'];
		}	
	
		if($wplanguagesoptions['languages_coupon_apply_memberid'])
		{
			$coupon_apply_memberid=$wplanguagesoptions['languages_coupon_apply_memberid'];
		}			
		if($wplanguagesoptions['languages_coupon_apply_coupon'])
		{
			$coupon_apply_coupon=$wplanguagesoptions['languages_coupon_apply_coupon'];
		}		
	    if($wplanguagesoptions['languages_coupon_enter_memberid'])
		{
			$coupon_enter_memberid=$wplanguagesoptions['languages_coupon_enter_memberid'];
		}			
		
			
	}

    $return_page = $rst_paypal_options['custom_return'];
    if ($return_page != '') {
        $currenturl = $return_page;
    }

    

    $stylecss = $rst_options['rst_theme'];
    if ($stylecss == '') {

        $stylecss = 'lite.css';

    }

    $rst_h_msg = $rst_options['rst_h_msg'];

    $paymentsuccess = "";

    //if (isset($_POST) && $_POST['custom'] != '') {
     //   $paymentsuccess = succesrstsmessage();
   // }
	

    $screenspacing=1;
    if($rst_options['rst_zoom'])
    {
    $screenspacing=$rst_options['rst_zoom'];
    }
	
$seatsize=$wpdb->get_var("SELECT LENGTH( seatno ) AS fieldlength FROM rst_seats where show_id=".$showid['id']." ORDER BY fieldlength DESC LIMIT 1 ");
//print "<br>1-".$seatsize;
if($seatsize>2)
{
$seatsize=5*($seatsize-2);
}else
{
$seatsize=0;
}

	
    $seats = rst_seats_operations('list', '', $showid['id']);
    $divwidth = (($seats[0]['total_seats_per_row']) + 2) * (24+$seatsize);
//print $divwidth;
  
    $divwidth=$divwidth * $rst_options['rst_zoom'];
	

	
    $mindivwidth = 640;
    if ($divwidth < $mindivwidth) {
        $divwidth = $mindivwidth;
    }
	
	if($rst_options['rst_fixed_width'])
	{
	$divwidth =$rst_options['rst_fixed_width'];
	}	

    if ($rst_options['rst_alignment'] == 3) {
        $style = 'margin-left: auto;';
    }

    if ($rst_options['rst_alignment'] == 2) {
        $style = 'margin-right: auto;';
    }

    if ($rst_options['rst_alignment'] == 1) {
        $style = 'margin: auto;';
    }

	
	
	
    ?>
<!--Row Seats v2.42 starts-->
   
    <div style="width: <?php echo $divwidth;?>px; <?php echo $style; ?>">
<?php
apply_filters('rowseats-addtocalendar-js',$showdata);

?>	
	
    <script type="text/javascript">
        var RSTPLN_CKURL = '<?php echo RSTPLN_CKURL?>';
        var RSTAJAXURL = '<?php echo RSTPLN_URL?>ajax.php';
    </script>
    <script type='text/javascript' src='<?php echo RSTPLN_JALURL ?>jquery.alerts.js'></script>
    <script type='text/javascript' src='<?php echo RSTPLN_URL ?>js/jquery.blockUI.js'></script>
<script type='text/javascript' src='<?php echo RSTPLN_URL ?>js/row_seats.js'></script>
    <link rel="stylesheet" type="text/css" media="all" href="<?php echo RSTPLN_JALURL ?>jquery.alerts.css"/>
    <?php
   // if ($type == 'offline') {
    //    $stylecss = 'lite.css';

   // }
    ?>
   
    
    <link rel="stylesheet" type="text/css" media="all" href="<?php echo RSTPLN_CSSURL . $stylecss ?>"/>
	<link rel="stylesheet" type="text/css" media="all" href="<?php echo RSTPLN_CSSURL . 'common.css' ?>"/>
 

<?php
$seatsize=$wpdb->get_var("SELECT LENGTH( seatno ) AS fieldlength FROM rst_seats where show_id=".$showid['id']." ORDER BY fieldlength DESC LIMIT 1 ");
//print "<br>9-".$seatsize;
if($seatsize>2)
{
$seatsize=5*($seatsize-2);
}else
{
$seatsize=0;
}

?> 
    <style>

	
	
	

ul.r li {



    font-size: <?php echo (int)(10 * $rst_options['rst_zoom']);?>px !important;

    height: <?php echo (int)(24 * $rst_options['rst_zoom']);?>px !important;

    line-height: <?php echo (int)(24 * $rst_options['rst_zoom']);?>px !important;


    width: <?php echo (int)((21+$seatsize) * $rst_options['rst_zoom']);?>px !important;



  

}



</style> 

<?php
apply_filters('row_seats_color_selection_css',$showid['id']);
?>

    <script type='text/javascript' src='<?php echo RSTPLN_COKURL ?>jquery.cookie.js'></script>
   <!-- <script type="text/javascript" src="<?php echo RSTPLN_IDLKURL ?>jquery.countdown.js"></script>
    <script type="text/javascript" src="<?php echo RSTPLN_IDLKURL ?>idle-timer.js"></script>-->
 <?
wp_enqueue_script('jquery');
?> 
    <!--<link rel="stylesheet" type="text/css" media="all" href="<?php echo RSTPLN_IDLKURL ?>jquery.countdown.css"/>-->

    <input type="hidden" name="startedcheckout" id="startedcheckout" value=""/>
    <input type="hidden" name="numberoffreecoupons" id="numberoffreecoupons" value=""/>
    <input type="hidden" name="redirecturl" id="redirecturl" value="<?php echo $currenturl; ?>"/>

    <?php
    $showid = $showid['id'];
    $showdata['vmid'] = $showid;
    $showdata = rst_shows_operations('byid', $showdata, '');
    $closebooking = $rst_options['rst_close_bookings'];
    if ($closebooking == '') {
        $closebooking = 60;
    }
    $showdata = $showdata[0];
    $eventname = $showdata['show_name'];
	$_SESSION['views']=  $showdata['id'];
    $venue = $showdata['venue'];
    $eventdate = $showdata['show_start_time'];
    $eventdate = date('Y-m-d H:i:s', strtotime($showdata['show_start_time']));
    $eventdate1 = strtotime($eventdate);
    $currentdate = strtotime(current_time('mysql', 0));
    $eventdate1 = $eventdate1 - (60 * $closebooking);
    $stopsonlinebooking = $closebooking / 60;
    if ($closebooking < 60) {
        $prefixh = 'mins';
        $stopsonlinebooking = $closebooking;
    } else {
        $prefixh = 'hr';
    }
	//$checking_string="stopsonlinebooking =".$stopsonlinebooking." and type=".$type."<br><br>";
    if ($stopsonlinebooking == 0) {
        $stopsonlinebooking = '';
    } else {
	     $booking_warning=str_replace('{DURATION}',$stopsonlinebooking." ".$prefixh,$booking_warning);
        $stopsonlinebooking = " ($booking_warning)";
    }
    if ($type == 'offline') {
      //  $stopsonlinebooking = '';
    }
    $currenttime = current_time('mysql', 0);

	
$vairablename="row_seats_wpuser_access_".$showdata['id'];
$vairablenamevalue=$rst_options[$vairablename];
//if($vairablenamevalue=="on")
//{	
	
	//if ($currentdate >= $eventdate1 && $type != 'offline') {
	if($_REQUEST['stoprefresh']=="yes")
	{
	
if($rst_options['rst_idle_message'])	
{
$rstidlemsg=$rst_options['rst_idle_message'];
}else{
$rstidlemsg='{showname} : Sorry this page is idle for long. To continue <a href="{returnurl}">click here</a>';
}
$tags = array('{showname}', '{showdate}', '{returnurl}');
$vals = array($eventname, $eventdate, $_REQUEST['returnurl']);
$rstidlemsg = str_replace($tags, $vals, $rstidlemsg);
$rstidlemsg = stripslashes($rstidlemsg);
echo '<div><br/><strong>'.$rstidlemsg.'</strong></div></div><br>';
	} elseif(!is_user_logged_in() && $vairablenamevalue=="on")
	{
	
	echo   '<div><br/><strong>'.$wpuser_onlyloggedin.'</strong></div></div>';
	
	
	}
	
	
	  elseif ($currentdate >= $eventdate1) {

        echo   '<div><br/><strong>'.$event_booking_closed.'</strong></div></div>';

    } else {

        $html = '';


        // showcart ----->
        $html .= "<a name='show_top'></a><div class='showchart'><div style='width:".$divwidth."px; margin: 0 auto;'><div class='showchart paymentsucess'>$paymentsuccess</div>

        <div style='float:left;color:#f21313;'>$event_empty_warning &nbsp;&nbsp;</div>

        <div id='defaultCountdown' ></div>
        <div id='idleCountdown' style='display: none;'></div>
        </div><div id='eventdetails' >

        $event_name_title:$eventname <br/>

        $event_datetime: $eventdate  ".$stopsonlinebooking.".<br/>

        $event_venue:$venue<br/>
        <a style='margin-left:0px;' href='#view_cart'>$event_view_cart</a> <div style='float:left'>

        </div></div></div>";
        // <----- showcart
		apply_filters('row_seats_generel_admission_form',$showid);
		$html .= apply_filters('rowseats-addtocalendar-showbutton',$showdata);
        $html .= "<div id='showprview' class='localcss' align='center' style='width:100%; margin-left: auto;margin-right: auto;' >";

		
	
        ?>
        
        <script>
	
        var regtype = '<?php echo $type?>';
        var offlineAdmin = '<?php echo $offlineAdmin?>';
		var rstidlecounter = '<?php echo $rstidlecounter?>';

        ///////////////////////// idle time code /////////////////
		
function addQueryParam( url, key, val ){
    var parts = url.match(/([^?#]+)(\?[^#]*)?(\#.*)?/);
    var url = parts[1];
    var qs = parts[2] || '';
    var hash = parts[3] || '';

    if ( !qs ) {
        return url + '?' + key + '=' + encodeURIComponent( val ) + hash;
    } else {
        var qs_parts = qs.substr(1).split("&");
        var i;
        for (i=0;i<qs_parts.length;i++) {
            var qs_pair = qs_parts[i].split("=");
            if ( qs_pair[0] == key ){
                qs_parts[ i ] = key + '=' + encodeURIComponent( val );
                break;
            }
        }
        if ( i == qs_parts.length ){
            qs_parts.push( key + '=' + encodeURIComponent( val ) );
        }
        return url + '?' + qs_parts.join('&') + hash;
    }
}

		
        function idleforlong() {
		var newurl=addQueryParam( window.location.href, 'stoprefresh', 'yes' );
		var newurl=addQueryParam( newurl, 'returnurl', window.location.href );
		window.location.href=newurl;
		//alert(newurl);
		}
        function liftOff() {

            jQuery.post("<?php echo RSTAJAXURL?>",
                {
                    action: 'releasecurrentcart',
                    details:<?php echo $showid?>,
                    redirecturl: document.getElementById('redirecturl').value,
                    cartiterms: jQuery.cookie("rst_cart_<?php echo $showid?>")
                },

                function (msg) {

                    jQuery("#showprview").html((msg));
                    document.getElementById('return').value = document.getElementById('redirecturl').value;
                    jQuery(".seatplan .showseats").each(function (i) {

                        if (jQuery(this).attr("rel") == "Y") {
                            jQuery(this).click(function () {
                                getupdatedshow(jQuery(this).attr("id"));
                            });
                        }

                        if (jQuery(this).attr("rel") == "H") {

                            jQuery(this).click(function () {

                                id = jQuery(this).attr("id");

                                jConfirm('<?php echo $rst_h_msg;?>', 'Wheelchair Access', function (r) {
                                    if (r == true) {
                                        getupdatedshow(id);
                                    }
                                    else {
                                    }
                                });
                            });
                        }
                    });
                });
        }

		
<?php
$rstidlecounter=$rstidlecounter*60;
$rst_idle_clear_cart=$rst_idle_clear_cart*60;

?>
var IDLE_TIMEOUT = <?php echo $rstidlecounter;?>; //seconds
var IDLE_TIMEOUT2 = <?php echo $rst_idle_clear_cart;?>; //seconds
var _idleSecondsCounter = 0;
var _idleSecondsCounter2 = 0;
document.onclick = function() {
    _idleSecondsCounter = 0;
	_idleSecondsCounter2 = 0;
};
document.onmousemove = function() {
    _idleSecondsCounter = 0;
	_idleSecondsCounter2 = 0;
};
document.onkeypress = function() {
    _idleSecondsCounter = 0;
	_idleSecondsCounter2 = 0;
};
window.setInterval(CheckIdleTime, 1000);

function CheckIdleTime() {
    _idleSecondsCounter++;
	 _idleSecondsCounter2++;
	 
    var austDay = new Date();
    jQuery.cookie("rst_cart_time_<?php echo $showid?>", austDay.getTime());

    var oPanel = document.getElementById("idleCountdown");
	var mytime=(IDLE_TIMEOUT - _idleSecondsCounter);
	var minutes = Math.floor(mytime / 60);
	var seconds = mytime - minutes * 60;	
	var minutes = ("0" + minutes).slice(-2);
	var seconds = ("0" + seconds).slice(-2);
	var oPanel2 = document.getElementById("defaultCountdown");
	var mytime2=(IDLE_TIMEOUT2 - _idleSecondsCounter2);
	var minutes2 = Math.floor(mytime2 / 60);
	var seconds2 = mytime2 - minutes2 * 60;	
	var minutes2 = ("0" + minutes2).slice(-2);
	var seconds2 = ("0" + seconds2).slice(-2);	

    if (_idleSecondsCounter2 == IDLE_TIMEOUT2) {
		_idleSecondsCounter2=0;
		//oPanel2.innerHTML = "00:00";
		oPanel2.innerHTML = "<blink><font color=red class=blink><b>Clearing cart....</b></font></blink>";
        liftOff();
		

    }
	else
	{
	
	if((IDLE_TIMEOUT2-_idleSecondsCounter2)<30)
	{
	oPanel2.innerHTML = "<blink><font color=red class=blink><b>"+minutes2 +":"+seconds2+"</b></font></blink>";
	}else {	
	oPanel2.innerHTML = minutes2 +":"+seconds2;
	}
	}		
	
    if (_idleSecondsCounter == IDLE_TIMEOUT) {
        //alert("Time expired!");
		oPanel.innerHTML = "00:00";
		clearInterval(CheckIdleTime);
        idleforlong();
		return false;
		//exit;
    }
	else
	{

	oPanel.innerHTML = minutes +":"+seconds;

	}
	

	
	
	
	
	
}

/*		
		
		
        IdleTimer.subscribe("idle", function () {
		
		  // alert('idle');

            var status = document.getElementById("status");

            jQuery('#defaultCountdown').countdown('destroy');

            var austDay = new Date();

            austDay.setMinutes(austDay.getMinutes() + 7);

            jQuery('#defaultCountdown').countdown({until: austDay, onExpiry: liftOff, format: 'MS', compact: true,

                description: ''});
				
            jQuery('#idleCountdown').countdown('destroy');

            var austDay1 = new Date();

            austDay1.setMinutes(austDay1.getMinutes() + <?php echo $rstidlecounter?>);

            jQuery('#idleCountdown').countdown({until: austDay1, onExpiry: idleforlong, format: 'MS', compact: true,

                description: ''});
				

        });


        IdleTimer.subscribe("active", function () {

            var status = document.getElementById("status");

            var austDay = new Date();

            jQuery.cookie("rst_cart_time_<?php echo $showid?>", austDay.getTime());

            jQuery('#defaultCountdown').countdown('destroy');

            austDay.setMinutes(austDay.getMinutes() + 7);

            jQuery('#defaultCountdown').countdown({until: austDay, onExpiry: liftOff, format: 'MS', compact: true,

                description: ''});

            jQuery('#defaultCountdown').countdown('pause');
			
			var austDay1 = new Date();
			
            jQuery('#idleCountdown').countdown('destroy');

            austDay1.setMinutes(austDay1.getMinutes() + <?php echo $rstidlecounter?>);

            jQuery('#idleCountdown').countdown({until: austDay1, onExpiry: idleforlong, format: 'MS', compact: true,

                description: ''});

            jQuery('#idleCountdown').countdown('pause');			

        });

        IdleTimer.start(1000);
		
*/		
        ///////////////////////// idle time code ended /////////////////

        function getupdatedshow(id) {

            if (jQuery.cookie("rst_cart_time_<?php echo $showid?>") == null) {

                var dat = new Date();

                jQuery.cookie("rst_cart_time_<?php echo $showid?>", dat.getTime());

            } else {

                var dat = new Date();

                diff = dat.getTime() - jQuery.cookie("rst_cart_time_<?php echo $showid?>");

                if (diff > 600000) {
                    jQuery.cookie("rst_cart_<?php echo $showid?>", null);
                }

            }

            jQuery('#showprview').block({

                message: '<?php echo $event_seat_processing;?>',
                css: { border: '3px solid #a00' }

            });

            jQuery.post("<?php echo RSTAJAXURL?>",
                {
                    action: 'booking',
                    details: id,
                    redirecturl: document.getElementById('redirecturl').value,
                    cartiterms: jQuery.cookie("rst_cart_<?php echo $showid?>"),
                    offline: (regtype == 'offline') ? 'offline' : ''
                },

                function (msg) {

                    jQuery("#showprview").html((msg));

                    jQuery('#showprview').unblock();

                    document.getElementById('return').value = document.getElementById('redirecturl').value;

                    jQuery(".seatplan .showseats").each(function (i) {

                        if (jQuery(this).attr("rel") == "Y") {

                            // seat onclick ----->
                            jQuery(this).click(function () {
                                getupdatedshow(jQuery(this).attr("id"));
                            });
                            // <----- seat onclick
                        }

                        if (jQuery(this).attr("rel") == "H") {

                            jQuery(this).click(function () {

                                id = jQuery(this).attr("id");

                                jConfirm('<?php echo $rst_h_msg;?>', 'Wheelchair Access', function (r) {

                                    if (r == true) {
                                        getupdatedshow(id);
                                    }
                                    else {
                                    }
                                });
                            });
                        }
                    });
                });
        }

		
		
        function getupdatedshow(id) {

            if (jQuery.cookie("rst_cart_time_<?php echo $showid?>") == null) {

                var dat = new Date();

                jQuery.cookie("rst_cart_time_<?php echo $showid?>", dat.getTime());

            } else {

                var dat = new Date();

                diff = dat.getTime() - jQuery.cookie("rst_cart_time_<?php echo $showid?>");

                if (diff > 600000) {
                    jQuery.cookie("rst_cart_<?php echo $showid?>", null);
                }

            }

            jQuery('#showprview').block({

                message: '<?php echo $event_seat_processing;?>',
                css: { border: '3px solid #a00' }

            });

            jQuery.post("<?php echo RSTAJAXURL?>",
                {
                    action: 'booking',
                    details: id,
                    redirecturl: document.getElementById('redirecturl').value,
                    cartiterms: jQuery.cookie("rst_cart_<?php echo $showid?>"),
                    offline: (regtype == 'offline') ? 'offline' : ''
                },

                function (msg) {

                    jQuery("#showprview").html((msg));

                    jQuery('#showprview').unblock();

                    document.getElementById('return').value = document.getElementById('redirecturl').value;

                    jQuery(".seatplan .showseats").each(function (i) {

                        if (jQuery(this).attr("rel") == "Y") {

                            // seat onclick ----->
                            jQuery(this).click(function () {
                                getupdatedshow(jQuery(this).attr("id"));
                            });
                            // <----- seat onclick
                        }

                        if (jQuery(this).attr("rel") == "H") {

                            jQuery(this).click(function () {

                                id = jQuery(this).attr("id");

                                jConfirm('<?php echo $rst_h_msg;?>', 'Wheelchair Access', function (r) {

                                    if (r == true) {
                                        getupdatedshow(id);
                                    }
                                    else {
                                    }
                                });
                            });
                        }
                    });
                });
        }		
        function refreshshow(id) {

            jQuery.post('<?php echo RSTAJAXURL?>',
                {
                    action: 'refresh',
                    details: id,
                    redirecturl: document.getElementById('redirecturl').value,
                    cartiterms: jQuery.cookie("rst_cart_<?php echo $showid?>")
                },

                function (msg) {

                    document.getElementById('return').value = document.getElementById('redirecturl').value;

                    resp = jQuery(msg);

                    chart = resp.filter('.seatplan');

                    jQuery(".seatplan").html((chart));

                    jQuery(".seatplan .showseats").each(function (i) {

                        if (jQuery(this).attr("rel") == "Y") {

                            // seat onclick ----->
                            jQuery(this).click(function () {
                                getupdatedshow(jQuery(this).attr("id"));
                            });
                            // <----- seat onclick
                        }

                        if (jQuery(this).attr("rel") == "H") {

                            jQuery(this).click(function () {

                                id = jQuery(this).attr("id");

                                jConfirm('<?php echo $rst_h_msg;?>', 'Wheelchair Access', function (r) {

                                    if (r == true) {
                                        getupdatedshow(id);
                                    }
                                    else {
                                    }
                                });
                            });
                        }
                    });
                });
        }
<?php
if($rst_options['rst_refresh_time'])
{
$refresh_time=(int)$rst_options['rst_refresh_time']*1000;
if($refresh_time<=0)
{
$refresh_time=5000;
}
} else {
$refresh_time=5000;
}

?>
        jQuery(document).ready(function () {
            getupdatedshow("<?php echo $showid?>");
            var interval = setInterval(increment, <?php echo $refresh_time?>);
        });

        function releaseseats() {
            if (document.getElementById('startedcheckout').value == '') {
                releasenow("<?php echo $showid?>");
            }
        }

        releasenow("<?php echo $showid?>");

        function releasenow(id) {

            action = 'releasenow';

            jQuery.post('<?php echo RSTAJAXURL?>',
                {
                    action: 'releasenow',
                    details: id,
                    redirecturl: document.getElementById('redirecturl').value,
                    cartiterms: jQuery.cookie("rst_cart_<?php echo $showid?>")
                },

                function (msg) {
                    if (msg == 'no') {
                    } else {
                        jQuery.cookie("rst_cart_<?php echo $showid?>", null);
                        window.location.reload();
                        //exit();
                    }
                });

            var interval = setInterval(releaseseats, 300000);
        }

        function increment() {
            if (document.getElementById('startedcheckout').value == '') {
                refreshshow("<?php echo $showid?>");
            }
        }
//Javascript function to format currency - start

function formatCurrency(num) {
num = num.toString().replace(/\$|\,/g, '');
if (isNaN(num)) num = "0";
sign = (num == (num = Math.abs(num)));
num = Math.floor(num * 100 + 0.50000000001);
cents = num % 100;
num = Math.floor(num / 100).toString();
if (cents < 10) cents = "0" + cents;
for (var i = 0; i < Math.floor((num.length - (1 + i)) / 3); i++)
num = num.substring(0, num.length - (4 * i + 3)) + ',' + num.substring(num.length - (4 * i + 3));
return (((sign) ? '' : '-') + '' + num + '.' + cents);
}
//Javascript function to format currency - end


function savebookingcustom()

{

<?php



	
if($rst_options['rst_enable_special_pricing']=="on" and row_seats_special_pricing_verification())
{

?>	
	//Sending special price to ajax call for saving....	
	var totalitems=document.getElementById('totalrecords').value;
	var mycartproducts;
	for (var i=0; i<totalitems; i++)
	{
	var dropboxvalue=document.getElementById('special_pricing'+i).value;
	if(i==0)
	{
	mycartproducts=dropboxvalue;
	}else{
	mycartproducts=mycartproducts+"__"+dropboxvalue;
	}
	}

<?php }else{?>

var mycartproducts;

<?php } ?>

                document.getElementById('couponprogress').innerHTML = '<img src="<?php echo RSTPLN_URL;?>images/couponwait.gif" width="20" style="border:none !important;"/>';
				jQuery('.row_seats_loading').show();

                if (regtype == 'offline') {
                    regstatus = 'offline_registration';
                } else if (regtype == 'zero') {
                    regstatus = 'free_booking';
                }
                else {
                    regstatus = 'pending_paypal';
                }



                jQuery.post('<?php echo RSTAJAXURL?>',
                    {
                        action: 'savebooking',
                        name: document.getElementById('contact_name').value,
                        email: document.getElementById('contact_email').value,
                        phone: document.getElementById('contact_phone').value,
						bookingid: document.getElementById('bookingid').value,
                        status: regstatus,
						myproducts: mycartproducts,
                        redirecturl: document.getElementById('redirecturl').value,
                        cartiterms: jQuery.cookie("rst_cart_<?php echo $showid?>")
                    },

                    function (rmsg) {
						var finalstring;
						
						var index = rmsg.indexOf("error");
						if (index != -1)
						{
						var pieces = rmsg.split(/[\s_]+/);
					    var npieces = pieces[pieces.length-1];
						alert("<?php echo $event_double_booking;?> "+npieces);
						window.location.reload();
						return false;	
						
						}						
						
						
						document.getElementById('x_invoice_num').value = rmsg;	
						document.getElementById('item_number').value = rmsg;	
						document.getElementById('bookingid').value = rmsg;
                            if (document.getElementById('statusofcouponapply').value == 'success')
                                finalstring = rmsg + '__' + document.getElementById('couponcode').value + '__' + document.getElementById('coupondiscount').value + '__' + document.getElementById('numberoffreecoupons').value + '__' + document.getElementById('rst_fees').value;
                            else
                                finalstring = rmsg + '__'+' '+'__0__0__' + document.getElementById('rst_fees').value;	
						   document.getElementById('x_custom').value = finalstring;
					  jQuery.cookie("rst_cart_<?php echo $showid?>", null);  
                      jQuery('#rsbuynow').click();						   
		  
		  return;
			
                    });
		    
		    

}


    function savecheckoutdata(id) {
var mycartproducts;	
<?php
	
if($rst_options['rst_enable_special_pricing']=="on" and row_seats_special_pricing_verification())
{

?>	
	//Sending special price to ajax call for saving....	
	var totalitems=document.getElementById('totalrecords').value;
	
	for (var i=0; i<totalitems; i++)
	{
	var dropboxvalue=document.getElementById('special_pricing'+i).value;
	if(i==0)
	{
	mycartproducts=dropboxvalue;
	}else{
	mycartproducts=mycartproducts+"__"+dropboxvalue;
	}
	}

<?php }else{?>

var mycartproducts;

<?php } ?>

	

            if (document.getElementById('contact_name').value == "") {

                alert('please enter your name');

                return false;

            }

            if ((document.getElementById('contact_name').value).length < 4) {

                alert('Name should have minimum 4 characters..');

                return false;

            }


            filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

            if (filter.test(document.getElementById('contact_email').value)) {

            }
            else {
                alert('please enter proper email address');
                return false;
            }

            if ((document.getElementById('contact_phone').value) == "") {

                alert('Please enter your contact phone number');

                return false;

            }

            if ((document.getElementById('contact_phone').value).length < 7) {

                alert('Phone number should have minimum 7 characters..');

                return false;
            }

            if ((document.getElementById('rstterms').checked) == false) {

                alert('please agree the terms and conditions');

                return false;
            }
			
	

            if (id == 'placeordernew') {

                document.getElementById('couponprogress').innerHTML = '<img src="<?php echo RSTPLN_URL;?>images/couponwait.gif" width="20" style="border:none !important;"/>';

               
	       //jQuery(".QTPopup").css('display', 'none');

                jQuery('#showprview').block({

                    message: '<?php echo $event_seat_processing;?>',
                    css: { border: '3px solid #a00' }

                });
                if (regtype == 'offline') {
                    regstatus = 'offline_registration';
                } else if (regtype == 'zero') {
                    regstatus = 'free_booking';
                }
                else {
                    regstatus = 'pending_paypal';
                }



                jQuery.post('<?php echo RSTAJAXURL?>',
                    {
                        action: 'savebooking',
                        name: document.getElementById('contact_name').value,
                        email: document.getElementById('contact_email').value,
                        phone: document.getElementById('contact_phone').value,
                        status: regstatus,
						myproducts: mycartproducts,
                        redirecturl: document.getElementById('redirecturl').value,
                        cartiterms: jQuery.cookie("rst_cart_<?php echo $showid?>")
                    },

                    function (msg) {
		    
		    //document.getElementById('stripe_code').value =msg;
		    alert('Successfully Booked! eTicket Sent To Your Email.');
			return;
                    });
return;	    
}		    

            if (id == 'placeorder') {

                document.getElementById('couponprogress').innerHTML = '<img src="<?php echo RSTPLN_URL;?>images/couponwait.gif" width="20" style="border:none !important;"/>';

                jQuery(".QTPopup").css('display', 'none');

                jQuery('#showprview').block({

                    message: '<?php echo $event_seat_processing;?>',
                    css: { border: '3px solid #a00' }

                });
                if (regtype == 'offline') {
                    regstatus = 'offline_registration';
                } else if (regtype == 'zero') {
                    regstatus = 'free_booking';
                }
                else {
                    regstatus = 'pending_paypal';
                }



                jQuery.post('<?php echo RSTAJAXURL?>',
                    {
                        action: 'savebooking',
                        name: document.getElementById('contact_name').value,
                        email: document.getElementById('contact_email').value,
                        phone: document.getElementById('contact_phone').value,
                        status: regstatus,
			            myproducts: mycartproducts,
                        redirecturl: document.getElementById('redirecturl').value,
                        cartiterms: jQuery.cookie("rst_cart_<?php echo $showid?>")
                    },

                    function (msg) {

                        document.getElementById('return').value = document.getElementById('redirecturl').value;

                        if (msg != '' && msg != 0) {

                            if (document.getElementById('statusofcouponapply').value == 'success')
                                document.getElementById('custom').value = msg + '__' + document.getElementById('couponcode').value + '__' + document.getElementById('coupondiscount').value + '__' + document.getElementById('numberoffreecoupons').value + '__' + document.getElementById('rst_fees').value;
                            else
                                document.getElementById('custom').value = msg;


                            if (regtype != 'offline' && regtype != 'zero') {
                                jQuery.cookie("rst_cart_<?php echo $showid?>", null);
                                document.checkoutform.submit();
                            }
                            else if (regtype == 'zero') {
                                jQuery.post('<?php echo RSTAJAXURL?>',
                                    {
                                        action: 'zerobooking',
                                        bookingid: document.getElementById('custom').value,
                                        name: document.getElementById('contact_name').value,
                                        email: document.getElementById('contact_email').value,
                                        phone: document.getElementById('contact_phone').value,
                                        cartiterms: jQuery.cookie("rst_cart_<?php echo $showid?>")
                                    },

                                    function (msg) {
                                        jQuery.cookie("rst_cart_<?php echo $showid?>", null);
                                        alert('Successfully Booked! eTicket Sent To Your Email.');
                                        window.location.reload();
                                    });
                            }
                            else if (regtype == 'offline') {
                                jQuery.post('<?php echo RSTAJAXURL?>',
                                    {
                                        action: 'offlinereg',
                                        bookingid: document.getElementById('custom').value,
                                        name: document.getElementById('contact_name').value,
                                        email: document.getElementById('contact_email').value,
                                        phone: document.getElementById('contact_phone').value,
                                        freeseats: document.getElementById('numberoffreecoupons').value,
                                        cartiterms: jQuery.cookie("rst_cart_<?php echo $showid?>")
                                    },

                                    function (msg) {
                                        jQuery.cookie("rst_cart_<?php echo $showid?>", null);

                                        alert('Successfully Booked! eTicket Sent To Your Email.');
                                        if (offlineAdmin === 'admin') {
                                            window.location = '<?php echo get_option('siteurl')?>/wp-admin/admin.php?page=rst-off-reg';
                                        } else {
                                            window.location.reload()/* = '<?php //echo get_option('siteurl')?>/wp-admin/admin.php?page=rst-off-reg'*/;
                                        }
                                    });
                            }
                        } else {
                            alert("Something Went wrong errorcode:".msg);
                        }
                    });
            } else {
                document.getElementById('numberoffreecoupons').value = '';
                var isChecked = jQuery('#rstmem').attr('checked') ? true : false;
                if (( document.getElementById('couponcode').value) == "" && !isChecked) {

                    alert('please enter couponcode');
                    return false;

                }
                if (( document.getElementById('couponcode').value) == "Enter Member ID" || (( document.getElementById('couponcode').value) == '' && isChecked )) {

                    alert('please enter member id');
                    return false;

                }

                if (( document.getElementById('couponcode').value) == document.getElementById('appliedcoupon').value && document.getElementById('statusofcouponapply').value == 'success') {

                    document.getElementById('coupondiscount').value = '';
                    return false;

                }

                document.getElementById('couponprogress').innerHTML = '<img src="<?php echo RSTPLN_URL;?>images/couponwait.gif" width="20" style="border:none !important;"/>';
                if (isChecked) {
                    jQuery.post('<?php echo RSTAJAXURL?>',
                        {
                            action: 'applymember',
                            memberid: document.getElementById('couponcode').value,
                            showid:<?php echo $showid?>,
                            cartiterms: jQuery.cookie("rst_cart_<?php echo $showid?>")
                        }, function (msg) {
                            document.getElementById('couponprogress').innerHTML = '';

                            var couponresult = msg.split('_');

                            if (couponresult[0] == 'error') {

                                document.getElementById('amount').value = document.getElementById('totalbackup').value;

                                jQuery('#aftercoupongrand').hide();

                                jQuery('#aftercoupondis').hide();

                                document.getElementById('couponmsg').innerHTML = couponresult[1];

                                document.getElementById('appliedcoupon').value = document.getElementById('couponcode').value;

                                document.getElementById('coupondiscount').value = '';

                                document.getElementById('statusofcouponapply').value = 'fail';

                            } else {
                                if (couponresult[1] == '0.00') {
                                    regtype = 'zero';
									document.getElementById('numberoffreecoupons').value = couponresult[3];
                                } else if (regtype != 'offline') {
                                    regtype = 'applyedfreecoupons';
                                    document.getElementById('numberoffreecoupons').value = couponresult[3];

                                } else if (regtype == 'offline') {
                                    document.getElementById('numberoffreecoupons').value = couponresult[3];
                                }

                                document.getElementById('couponmsg').innerHTML = '';

                                document.getElementById('couponprogress').innerHTML = '';

                                document.getElementById('appliedcoupon').value = document.getElementById('couponcode').value;

                                document.getElementById('statusofcouponapply').value = 'success';

                                document.getElementById('coupondiscount').value = couponresult[2];

                                document.getElementById('discountamount').innerHTML = couponresult[2];

                                document.getElementById('Grandtotal').innerHTML = '<strong>' + couponresult[1] + '</strong>';

                                jQuery('#aftercoupongrand').show();

                                jQuery('#aftercoupondis').show();

                                document.getElementById('amount').value = couponresult[1];

                            }
                        });
                } else {
                    jQuery.post('<?php echo RSTAJAXURL?>',
                        {
                            action: 'applycoupon',
                            email: document.getElementById('contact_email').value,
                            couponcode: document.getElementById('couponcode').value,
                            showid:<?php echo $showid?>,
                            total: document.getElementById('amount').value
                        },

                        function (msg) {

                            document.getElementById('couponprogress').innerHTML = '';

                            var couponresult = msg.split('_');

                            if (couponresult[0] == 'error') {

                                document.getElementById('amount').value = document.getElementById('totalbackup').value;

                               // jQuery('#aftercoupongrand').hide();

                                jQuery('#aftercoupondis').hide();

                                document.getElementById('couponmsg').innerHTML = couponresult[1];

                                document.getElementById('appliedcoupon').value = document.getElementById('couponcode').value;

                                document.getElementById('coupondiscount').value = '';

                                document.getElementById('statusofcouponapply').value = 'fail';

                            } else {

                                document.getElementById('couponmsg').innerHTML = '';

                                document.getElementById('couponprogress').innerHTML = '';

                                document.getElementById('appliedcoupon').value = document.getElementById('couponcode').value;

                                document.getElementById('statusofcouponapply').value = 'success';

                                document.getElementById('coupondiscount').value = couponresult[2];

                                document.getElementById('discountamount').innerHTML = couponresult[2];

                                document.getElementById('Grandtotal').innerHTML = '<strong>' + couponresult[1] + '</strong>';

                                jQuery('#aftercoupongrand').show();

                                jQuery('#aftercoupondis').show();

                                document.getElementById('amount').value = couponresult[1];
                            }
                        });
                }
            }
        }

        function deleteitem(obj) {

            action = 'deletebooking';

            if (obj.id == 'deleteall') {
                action :'deleteall';
            }

            jQuery.post('<?php echo RSTAJAXURL?>',
                {
                    action: action,
                    details: obj.id,
                    redirecturl: document.getElementById('redirecturl').value,
                    cartiterms: jQuery.cookie("rst_cart_<?php echo $showid?>")
                },

                function (msg) {

                    jQuery("#showprview").html((msg));

                    document.getElementById('return').value = document.getElementById('redirecturl').value;

                    jQuery(".seatplan .showseats").each(function (i) {

                        if (jQuery(this).attr("rel") == "Y") {

                            jQuery(this).click(function () {
                                getupdatedshow(jQuery(this).attr("id"));
                            });

                        }

                        if (jQuery(this).attr("rel") == "H") {

                            jQuery(this).click(function () {

                                id = jQuery(this).attr("id");

                                jConfirm('<?php echo $rst_h_msg;?>', 'Wheelchair Access', function (r) {

                                    if (r == true) {
                                        getupdatedshow(id);
                                    }
                                    else {
                                    }
                                });
                            });
                        }
                    });
                });
        }

        function deleteitemall(obj) {

            jQuery.post('<?php echo RSTAJAXURL?>',
                {
                    action: 'deleteall',
                    details: obj.id,
                    redirecturl: document.getElementById('redirecturl').value,
                    cartiterms: jQuery.cookie("rst_cart_<?php echo $showid?>")
                },

                function (msg) {

                    jQuery.cookie("rst_cart_<?php echo $showid?>", null);

                    jQuery("#showprview").html((msg));

                    document.getElementById('return').value = document.getElementById('redirecturl').value;

                    jQuery(".seatplan .showseats").each(function (i) {

                        if (jQuery(this).attr("rel") == "Y") {

                            jQuery(this).click(function () {
                                getupdatedshow(jQuery(this).attr("id"));
                            });
                        }

                        if (jQuery(this).attr("rel") == "H") {

                            jQuery(this).click(function () {

                                id = jQuery(this).attr("id");

                                jConfirm('<?php echo $rst_h_msg;?>', 'Wheelchair Access', function (r) {

                                    if (r == true) {
                                        getupdatedshow(id);
                                    }
                                    else {
                                    }
                                });
                            });
                        }
                    });
                });
        }

        function checkformember(obj) {
            var isChecked = jQuery('#rstmem').attr('checked') ? true : false;
            if (isChecked) {
                jQuery('#couponapplybtn').html('<?php echo $coupon_apply_memberid;?>');
                jQuery('#couponcode').val('<?php echo $coupon_enter_memberid;?>');
            } else {
                jQuery('#couponapplybtn').html('<?php echo $coupon_apply_coupon;?>');
                jQuery('#couponcode').val('');
            }
        }
        </script>

        <?php

        $html .= '</div></div>'; //asd

        return $html;

    }

}

/*
 * Returns url of the current page
 */
function curPageURL()
{
    $pageURL = 'http';
    if ($_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}


/*
 * Creates operations with shows for AJAX requests
 */
function rst_shows_operations($action, $data, $currentcart)
{
    global $wpdb;

    $modby = $current_user->user_login;

    switch ($action) {
        case 'releasecurrentcart':
            $totalseatstorelase = array();
            $showid = $data;

            for ($i = 0; $i < count($currentcart); $i++) {
                $row = $currentcart[$i]['row_name'];
                $showid = $currentcart[$i]['show_id'];
                $seat = $currentcart[$i]['seatno'];
				if($seat && $showid && $row)
				{
                $sql = "SELECT * FROM $wpdb->rst_seats st,$wpdb->rst_shows sh
                    WHERE
                    sh.id=st.show_id AND
                    st.row_name = '$row' AND
                    seattype<>'' AND
                    seatno = '$seat' AND
                    sh.id =" . $showid;
                $found = 0;
                $data = Array();
                if ($results = $wpdb->get_results($sql, ARRAY_A)) {

                    foreach ($results as $value) {
                        $found++;
                    }
                    if ($found == 0) {
                        return $data;
                    } else {
                        $data = $wpdb->get_results($sql, ARRAY_A);
                        $data = $data[0];
                        $dicount = 0;
                        if ($data['seattype'] == 'T') {

                            $dicount = $data['seat_price'];
                            $originaltype = $data['originaltype'];

                            $sql = "UPDATE  $wpdb->rst_seats SET seattype='$originaltype',discount_price=$dicount,status='not blocked' WHERE show_id=" . $showid . " AND row_name='$row' AND seattype<>'' AND seatno='".$seat."'";

                            $wpdb->query($sql);

                            rst_session_operations('deletebookingseat', $currentcart[$i]);

                        }
                    }
                }
				}

            }
            return array();
            break;

        case 'releaseall':
            $currentcartremain = array();
            $currentcartremain['released'] = 'false';
            $showid = $data;
            $rst_options = get_option(RSTPLN_OPTIONS);
            $rst_release_min = $rst_options['rst_release_min'];
            if ($rst_release_min == '') {
                $rst_release_min = 15;
            }
            ////////////////////////////
            $sql = "SELECT * FROM  $wpdb->rst_seats  WHERE show_id=$showid AND status='blocked' AND blocked_time < (now()- INTERVAL $rst_release_min MINUTE)";
            if ($results = $wpdb->get_results($sql, ARRAY_A)) {
                $allblockedseats = $wpdb->get_results($sql, ARRAY_A);
                for ($i = 0; $i < count($allblockedseats); $i++) {
                    $allblockedseat = $allblockedseats[$i];
                    $originaltype = $allblockedseat['originaltype'];
                    $dicount = $allblockedseat['seat_price'];
                    $row = $allblockedseat['row_name'];
                    $seatno = $allblockedseat['seatno'];
                    $seatid = $allblockedseat['seatid'];
                    if ($allblockedseat['seattype'] == 'T') {
                        $sql = "UPDATE  $wpdb->rst_seats SET seattype='$originaltype',discount_price=$dicount,status='not blocked' WHERE show_id=" . $showid . " AND row_name='$row' AND seattype<>'' AND seatno=$seatno AND seatid=" . $seatid;
                        $wpdb->query($sql);

                        for ($k = 0; $k < count($currentcart); $k++) {
                            if ($currentcart[$k]['seatno'] == $seatno) {
                                $currentcartremain['released'] = 'true';
                                unset($currentcart[$k]);
                            }

                        }
                    }

                }
            }
            if (!is_array($currentcart)) $currentcart = array();
            $currentcartremain['cartitems'] = array_values($currentcart);
            return $currentcartremain;
            break;

        case 'savebooking':
            $rst_session_id = session_id();

            $cartitems = unserialize($currentcart);
			$rst_options = get_option(RSTPLN_OPTIONS);	
			//updating ticket price with special price - start
			if($rst_options['rst_enable_special_pricing']=="on" and row_seats_special_pricing_verification())
			{

							
					$myproducts = $data['myproducts'];
					$myproductsarray=split("__",$myproducts);
					for ($i = 0; $i < count($myproductsarray); $i++) {
						$myproductsarraytemp=split("#",$myproductsarray[$i]);
						$myproductsarrayfinal[]=array($myproductsarraytemp[0],$myproductsarraytemp[1]);
					}
					
					$tempcartitems=$cartitems;
					for ($i = 0; $i < count($tempcartitems); $i++) {
						if($myproductsarrayfinal[$i][0]!='normal')
						{
							$tempcartitems[$i]['price'] = $myproductsarrayfinal[$i][1];					
						}
					}

					$cartitems=$tempcartitems;
					$currentcart = serialize($cartitems);    
					
			} 	
			//updating ticket price with special price - end
            
            $booking_details = $cartitems;
            $paypal_vars = '';
            $booking_time = date('Y-m-d H:i:s');
            $showid = $cartitems[0]['show_id'];
            $booking_detail = $currentcart;
            $username = $data['name'];
            $useremail = $data['email'];
            $phone = $data['phone'];
            $status = $data['status'];
            $rst_pp_options = get_option(RSTPLN_PPOPTIONS);
            $papalmode = '';
            if ($rst_pp_options['paypal_url'] == 'https://www.sandbox.paypal.com/cgi-bin/webscr') {
                $papalmode = 'Test';
            } else {
                $papalmode = 'Live';
            }
			
			if ( is_user_logged_in() ) {
				global $current_user;
				get_currentuserinfo();
				$user_id=$current_user->ID;				
			
			} else {			
				$user_id=0;
			}

			$currentcart = unserialize($currentcart);
            for ($i = 0; $i < count($currentcart); $i++) {
                $rowname = $currentcart[$i]['row_name'];
                $showid = $currentcart[$i]['show_id'];
                $seatno = $currentcart[$i]['seatno'];
				if($seatno && $showid && $rowname)
				{				
                $sql = "SELECT * FROM $wpdb->rst_seats st,$wpdb->rst_shows sh
                    WHERE
                    sh.id=st.show_id AND
                    st.row_name = '$rowname' AND
                    st.seatno = '$seatno' AND
                    st.seattype <> '' AND
                    sh.id =" . $showid;

                $seatdata = $wpdb->get_results($sql, ARRAY_A);
                $seatid = $seatdata[0]['seatid'];
                $seattype = $seatdata[0]['seattype'];
                if ($seatid != "") {
				
				if($seattype=="B")
				{
                    return 'error_'.$rowname.$seatno;
					exit;
					
				}

                }
				}

            }			
            if($data['bookingid'])
	    {
	    $wpdb->query("UPDATE  $wpdb->rst_bookings SET rst_session_id='$rst_session_id',paypal_vars='$paypal_vars',booking_time=booking_time,payment_status='$status',name='$username',email='$useremail',phone=$phone,paypal_mode=$papalmode,user_id=$user_id WHERE booking_id=" . $data['bookingid']);
		$customfield_query= apply_filters('row_seats_custom_field_query',$data['bookingid']);
		$_SESSION['mybookingsess']= $data['bookingid'];
		setcookie("mybookingsesscook", $data['bookingid'], time()+900); 
	    return $data['bookingid'];
	    }else{
            $sql = "INSERT INTO $wpdb->rst_bookings (show_id,rst_session_id,paypal_vars,booking_time,booking_details,payment_status,name,email,phone,paypal_mode,ticket_no,user_id)
            VALUES ($showid, '$rst_session_id', '$paypal_vars',now(),'$booking_detail','$status','$username','$useremail','$phone','$papalmode','','$user_id')";
            // ,rst_session_id,paypal_vars,booking_time,booking_details,payment_status,name,email,phone
            $wpdb->query($sql);			
			$customfield_query= apply_filters('row_seats_custom_field_query',$wpdb->insert_id);
			$_SESSION['mybookingsess']= $wpdb->insert_id;
			setcookie("mybookingsesscook", $wpdb->insert_id, time()+900); 
            return $wpdb->insert_id;
	    }

            break;

        case 'deleteall':

            for ($row = 0; $row < count($data); $row++) {
                $seatno = $data[$row]['seatno'];
                $showid = $data[$row]['show_id'];
                $rowname = $data[$row]['row_name'];
				if($seatno && $showid && $rowname)
				{				
                $sql = "SELECT * FROM $wpdb->rst_seats st,$wpdb->rst_shows sh
                    WHERE
                    sh.id=st.show_id AND
                    st.row_name = '$rowname' AND
                    st.seatno = '$seatno' AND
                    st.seattype <> '' AND
                    sh.id =" . $showid;

                $seatdata = $wpdb->get_results($sql, ARRAY_A);
                $seatid = $seatdata[0]['seatid'];
                $seattype = $seatdata[0]['originaltype'];
                if ($seatid != "") {
                    $wpdb->query("UPDATE  $wpdb->rst_seats SET seattype='$seattype',status='not blocked' WHERE seatid=" . $seatid);

                }
				}

            }
            return $showid;
            break;

        case 'deletebooking':
            $details = $data['details'];
            $data1 = explode('_', $details);
            if (count($data1) == 1) {
                return $data1[0];
            }
            $showid = $data1[1];
            $row = $data1[2];
            $seat = $data1[3];
            $bookings['show_id'] = $showid;
            $bookings['row_name'] = $row;
            $bookings['seatno'] = $seat;
				if($seat && $showid && $row)
				{
            $sql = "SELECT * FROM $wpdb->rst_seats st,$wpdb->rst_shows sh
                    WHERE
                    sh.id=st.show_id AND
                    st.row_name = '$row' AND
                    seattype<>'' AND
                    seatno = '$seat' AND
                    sh.id =" . $showid;
            $found = 0;
            $data = Array();
            if ($results = $wpdb->get_results($sql, ARRAY_A)) {

                foreach ($results as $value) {
                    $found++;
                }
                if ($found == 0) {
                    return $data;
                } else {
                    $data = $wpdb->get_results($sql, ARRAY_A);
                    $data = $data[0];
                    $dicount = 0;
                    if ($data['seattype'] == 'T') {

                        $dicount = $data['seat_price'];
                        $originaltype = $data['originaltype'];

                        $sql = "UPDATE  $wpdb->rst_seats SET seattype='$originaltype',discount_price=$dicount,status='not blocked' WHERE show_id=" . $showid . " AND row_name='$row' AND seattype<>'' AND seatno='".$seat."'";

                        $wpdb->query($sql);

                    }

                    $finalbookings[] = $bookings;
                    return $finalbookings;
                }

            }
			}

            break;

        case 'booking':
            $finalbookings = array();
            $details = $data['details'];
            $data1 = explode('_', $details);
            if (count($data1) == 1) {
                return $data1[0];
            }

            $showid = $data1[1];
            $row = $data1[2];
            $seat = $data1[3];
            $bookings['show_id'] = $showid;
            $bookings['row_name'] = $row;
            $bookings['seatno'] = $seat;

            $sql = "SELECT *, st.status AS seat_status FROM $wpdb->rst_seats st,$wpdb->rst_shows sh
                    WHERE
                    sh.id=st.show_id AND
                    st.row_name = '$row' AND
                    seattype!='' AND seattype!='T' AND seattype!='B' AND
                    seatno = '$seat' AND
                    sh.id =" . $showid;
					//print $sql;
            $found = 0;
            $data = Array();
            if ($results = $wpdb->get_results($sql, ARRAY_A)) {

                foreach ($results as $value) {

                    $found++;
                }
                if ($found == 0) {
                    return $data;
                } else {

                    if ($results[0]['seat_status'] != 'not blocked') {
                        return 'blocked';
                    }

                    $data = $wpdb->get_results($sql, ARRAY_A);

                    $data = $data[0];
                    $dicount = 0;

                    if ($data['seattype'] == 'Y') {

                        $dicount = $data['seat_price'];
                        $bookings['price'] = $dicount;
                        $sql = "UPDATE  $wpdb->rst_seats SET seattype='T',status='blocked',blocked_time=now() WHERE show_id=" . $showid . " AND row_name='$row' AND seattype<>'' AND seatno='".$seat."'";

                        $wpdb->query($sql);

                        $finalbookings[] = $bookings;
                    } else if ($data['seattype'] == 'H') {

                        $rst_options = get_option(RSTPLN_OPTIONS);
                        $dicount = $rst_options['rst_h_disc'];
                        if ($dicount != '') {
                            $dicount = $data['seat_price'] - ($data['seat_price'] * ($dicount / 100));
                            $dicount = round($dicount, 2);

                        } else {
                            $dicount = $data['seat_price'];
                        }
                        $bookings['price'] = $dicount;
                        $sql = "UPDATE  $wpdb->rst_seats SET seattype='T',discount_price=$dicount,status='blocked',blocked_time=now() WHERE show_id=" . $showid . " AND row_name='$row' AND seattype<>'' AND seatno='".$seat."'";

                        $wpdb->query($sql);
                        $finalbookings[] = $bookings;
                    }

                    return $finalbookings;
                }

            }

            break;

        case 'delete':
            $id = $data['id'];
            // print_r($data);
            $wpdb->query("DELETE FROM $wpdb->rst_shows WHERE id='$id'");
            return true;

            break;

        case 'insert':
            $name = $data['title'];
            $venue = $data['body'];
            $showstart = $data['start'];
            $showend = $data['end'];
            $status = $data['status'];
            $curdate = date('Y-m-d H:i:s');
            $showdate = date('Y-m-d H:i:s', strtotime($showstart));
            $allday = $data['allday'];
            $orient = $data['orient'];

            if (($name == ''))
                return 'Please fill all the fields';
            $wpdb->query("INSERT INTO $wpdb->rst_shows (show_name,show_start_time,show_end_time,show_date,venue,allday,status,orient,created_date,mod_date,mod_by) VALUES ('$name', '$showstart', '$showend','$showdate','$venue',$allday,'$status','$orient','$curdate','$curdate','$modby')");
            return mysql_insert_id();

            break;

        case 'update':
            $name = $data['title'];
            $venue = $data['body'];
            $showstart = $data['start'];
            $showend = $data['end'];
            $status = $data['status'];
            $curdate = date('Y-m-d H:i:s');
            $showdate = date('Y-m-d H:i:s', strtotime($showstart));
            $allday = $data['allday'];
            $orient = $data['orient'];

            $id = $data['id'];
            $wpdb->query("UPDATE  $wpdb->rst_shows SET show_name='$name',show_start_time='$showstart',show_end_time='$showend',show_date='$showdate',venue='$venue',allday=$allday,orient='$orient',mod_date='$curdate',mod_by='$modby' WHERE id=" . $id);
            return '<div class="updated"><p><strong>Testimonial Updated..</strong></p></div>';

        case 'updatestatus':

            $status = $data['status'];
            $curdate = date('Y-m-d H:i:s');
            $id = $data['id'];
            $wpdb->query("UPDATE  $wpdb->rst_shows SET status='$status',mod_date='$curdate',mod_by='$modby' WHERE id=" . $id);

            break;

        case 'list':

            $sql = "SELECT * FROM $wpdb->rst_shows ";
            $found = 0;
            $data = Array();
            if ($results = $wpdb->get_results($sql, ARRAY_A)) {

                foreach ($results as $value) {
                    $found++;
                }
                if ($found == 0) {
                    return $data;
                } else {
                    $data = $wpdb->get_results($sql, ARRAY_A);
                    return $data;
                }

            }

            break;

        case 'byid':

            $id = $data['vmid'];
            $sql = "SELECT * FROM $wpdb->rst_shows where id=" . $id;
            $found = 0;
            $data = Array();
            if ($results = $wpdb->get_results($sql, ARRAY_A)) {

                foreach ($results as $value) {
                    $found++;
                }
                if ($found == 0) {
                    return $data;
                } else {
                    $data = $wpdb->get_results($sql, ARRAY_A);
                    return $data;
                }

            }

            break;

        default:

            break;
    }

}


/*
 * Links scripts files to a page
 */
function rst_scripts_method()
{
    wp_enqueue_script('jquery');
}


/*
 * Creates handlers for AJAX requests
 */
function rst_ajax_callback()
{
    global $wpdb; // this is how you get access to the database

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'applycoupon':
                $couponcode = $_POST['couponcode'];
                $email = $_POST['email'];
                $total = $_POST['total'];
                $showid = $_POST['showid'];
                $data1 = validatecoupon($couponcode, $email, $total, $showid);

                $return = '';
                if ($data1['error'] != '') {
                    $return .= 'error_' . $data1['error'];
                } else {
                    $return .= 'success_' . $data1['total'] . '_' . $data1['discount'];
                }

                echo  $return;
                break;

            case 'applymember':
                $memberid = $_POST['memberid'];
                $cartitems = $_POST['cartiterms'];
                $showid = $_POST['showid'];
                $data1 = validatemember($memberid, $cartitems);

                $return = '';
                if ($data1['error'] != '') {
                    $return .= 'error_' . $data1['error'];
                } else {
                    $return .= 'success_' . $data1['total'] . '_' . $data1['discount'] . '_' . $data1['freeseats'];
                }

                echo  $return;
                break;

            case 'savebooking': // {
                $details = base64_decode($_POST['cartiterms']);
                $bookingid = rst_shows_operations('savebooking', $_POST, $details);
                echo $bookingid;

                break;

            case 'deleteall': // {
                $details = $_POST['cartiterms'];
                $details = base64_decode($_POST['cartiterms']);
                $currentcart = unserialize($details);
                $showid = rst_shows_operations('deleteall', $currentcart, '');

                $data['id'] = $showid;

                echo gettheseatchartAjax($data, $_POST['redirecturl'], array());
                exit();
                break;

            case 'releasenow': // {
                $showid = $_POST['details'];
                $currentcart = $_POST['cartiterms'];
                $currentcart = base64_decode($currentcart);
                $currentcart = unserialize($currentcart);
                $currentcart = rst_shows_operations('releaseall', $showid, $currentcart);
                $data['id'] = $showid;
                if ($currentcart['released'] == 'true') {
                    echo gettheseatchartAjax($data, $_POST['redirecturl'], $currentcart['cartitems']);
                    exit();
                }
                echo 'no';


                break;

            case 'releasecurrentcart':
                $showid = $_POST['details'];
                $currentcart = $_POST['cartiterms'];
                $currentcart = base64_decode($currentcart);
                $currentcart = unserialize($currentcart);
                $currentcart = rst_shows_operations('releasecurrentcart', $showid, $currentcart);
                $data['id'] = $showid;
                echo gettheseatchartAjax($data, $_POST['redirecturl'], $currentcart);

                exit();
                break;

            case 'deletebooking': // {

                $details = $_POST['details'];

                $data1 = explode('_', $details);
                if (count($data1) == 1 && $_POST['cartiterms'] == null) {
                    echo gettheseatchartAjax($data1[0]);

                    exit();
                } else if ($_POST['cartiterms'] != '' && count($data1) == 1) {
                    $currentcart = $_POST['cartiterms'];
                    $currentcart = base64_decode($currentcart);
                    $currentcart = unserialize($currentcart);
                    $showid['id'] = $_POST['details'];

                    echo gettheseatchartAjax($showid, $_POST['redirecturl'], $currentcart);
                    exit();
                }
                $currentcart = $_POST['cartiterms'];

                $bookingtodelete = rst_shows_operations('deletebooking', $_POST, $currentcart);
                $cartplusbooking = rst_shows_set_session($bookingtodelete, 'delete', $currentcart);
                $showid['id'] = $data1[1];
                echo gettheseatchartAjax($showid, $_POST['redirecturl'], $cartplusbooking);
                exit();
                break;

            case 'refresh': // {

                if ($_POST['cartiterms'] != null) {
                    $currentcart = $_POST['cartiterms'];
                    $currentcart = base64_decode($currentcart);

                } else {
                    $currentcart = array();
                }
                $details = $_POST['details'];
                $data1 = explode('_', $details);
                echo gettheseatchartAutoRefresh($data1[0], $_POST['redirecturl'], $currentcart);
                exit();
                break;

            case 'booking': // {
                $details = $_POST['details'];

                $data1 = explode('_', $details);
                if (count($data1) == 1 && $_POST['cartiterms'] == null) {
                    $showid['id'] = $data1[0];
                    echo gettheseatchartAjax($showid, '', array(), $_POST['offline']);
                    exit();

                } else if ($_POST['cartiterms'] != '' && count($data1) == 1) {
                    $currentcart = $_POST['cartiterms'];
                    $currentcart = base64_decode($currentcart);
                    $currentcart = unserialize($currentcart);
                    $showid['id'] = $_POST['details'];

                    echo gettheseatchartAjax($showid, $_POST['redirecturl'], $currentcart, $_POST['offline']);
                    exit();
                }

                $currentcart = $_POST['cartiterms'];
				

                $bookings = rst_shows_operations('booking', $_POST, $currentcart);
                $cartplusbooking = rst_shows_set_session($bookings, 'add', $currentcart);

                $showid['id'] = $data1[1];
                echo gettheseatchartAjax($showid, $_POST['redirecturl'], $cartplusbooking, $_POST['offline']);
                exit();
                break;

            case 'save': // {
                echo rst_shows_operations('insert', $_POST, '');
				
                exit();

                break;

            case 'update': // {
                return rst_shows_operations('update', $_POST, '');

                break;

            case 'delete': // {
                return rst_shows_operations('delete', $_POST);

                break;

            case 'get_events_back':

                $arr = array();
                $data = rst_shows_operations('list', '', '');
                for ($i = 0; $i < count($data); $i++) {
                    $arr[] = array(
                        'id' => $data[$i]['id'],
                        'title' => $data[$i]['show_name'],
                        'start' => date('Y-m-d H:i:s', strtotime($data[$i]['show_start_time'])),
                        'end' => date('Y-m-d H:i:s', strtotime($data[$i]['show_end_time'])),
                        'allday' => false,
                        'orient' => $data[$i]['orient'],
                        'body' => $data[$i]['venue']
                    );
                }
                echo json_encode($arr);
                exit;
                break;
				
            case 'get_events':				
				$arr = array();

				
                $data = rst_shows_operations('list', '', '');
                for ($i = 0; $i < count($data); $i++) {	

				if($data[$i]['allday']==1)
				{
				$allday1=true;
				}else{
				$allday1=false;				
				}		
	            $arr[]=array(id=>$data[$i]['id'],title=>addslashes($data[$i]['show_name']),start=>date('Y-m-d H:i:s', strtotime($data[$i]['show_start_time'])),end=>date('Y-m-d H:i:s', strtotime($data[$i]['show_end_time'])),allDay=>$allday1,orient=>$data[$i]['orient'],body=>addslashes($data[$i]['venue']));
				}
				echo json_encode($arr);
                exit;
                break;
				

            default:
                break;
        }
    }

    die();
}


/*
 * Generates seat chart html-content that is used to auto refresh chart via Ajax request
 */
function gettheseatchartAutoRefresh($showid, $data, $currentcart)
{

global $screenspacing,$wpdb;
    $rst_paypal_options = get_option(RSTPLN_PPOPTIONS);
    $rst_options = get_option(RSTPLN_OPTIONS);

	


	$wplanguagesoptions = get_option(RSTLANGUAGES_OPTIONS);
	$event_seat="Seat";
	$event_price="Price-";
	$event_seat_available="Available";
	$event_seat_booked="Booked";
	$event_seat_handicap="Wheelchair Access";
	$event_stall="STALL";
	$event_balcony="BALCONY";
	$event_circle="CIRCLE";
	$event_seat_blocked="Blocked";
	
	if($wplanguagesoptions['rst_enable_languages']=="on")
	{
		if($wplanguagesoptions['languages_event_seat'])
		{
			$event_seat=$wplanguagesoptions['languages_event_seat'];
		}
		if($wplanguagesoptions['languages_event_price'])
		{
			$event_price=$wplanguagesoptions['languages_event_price'];
		}	
		if($wplanguagesoptions['languages_event_seat_available'])
		{
			$event_seat_available=$wplanguagesoptions['languages_event_seat_available'];
		}
		if($wplanguagesoptions['languages_event_seat_blocked'])
		{
			$event_seat_blocked=$wplanguagesoptions['languages_event_seat_blocked'];
		}		
		if($wplanguagesoptions['languages_event_seat_booked'])
		{
			$event_seat_booked=$wplanguagesoptions['languages_event_seat_booked'];
		}	
		if($wplanguagesoptions['languages_event_seat_handicap'])
		{
			$event_seat_handicap=$wplanguagesoptions['languages_event_seat_handicap'];
		}	
		if($wplanguagesoptions['languages_event_stall'])
		{
			$event_stall=$wplanguagesoptions['languages_event_stall'];
		}			
		if($wplanguagesoptions['languages_event_balcony'])
		{
			$event_balcony=$wplanguagesoptions['languages_event_balcony'];
		}
		if($wplanguagesoptions['languages_event_circle'])
		{
			$event_circle=$wplanguagesoptions['languages_event_circle'];
		}		
	}


	
	

    $symbol = $rst_paypal_options['currencysymbol'];
	
$symbol = get_option('rst_currencysymbol');
    $symbols = array(
        "0" => "$",
        "1" => "&pound;",
        "2" => "&euro;",
        "3" => "&#3647;",
        "4" => "&#8362;",
        "5" => "&yen;",
        "6" => "&#8377;",
        "7" => "R$",
        "8" => "kr",
        "9" => "zł",
        "10" => "Ft",
        "11" => "Kč",
        "12" => "&#1088;&#1091&#1073;",
        "13" => "&#164;",
        "14" => "&#x20B1;",
        "15" => "Fr",
        "16" => "RM");

    $symbol = $symbols[$symbol];

    //$showid = $showid['id'];

    $data = getshowbyid($showid);
    $showorder = $data[0]['orient'];

    if ($showorder == 0 || $showorder == '') {
        $seats = rst_seats_operations('list', '', $showid);
    } else {
        $seats = rst_seats_operations('reverse', '', $showid);
    }
$seatsize=$wpdb->get_var("SELECT LENGTH( seatno ) AS fieldlength FROM rst_seats where show_id=".$showid." ORDER BY fieldlength DESC LIMIT 1 ");
//print "<br>9-".$seatsize;
if($seatsize>2)
{
$seatsize=5*($seatsize-2);
}else
{
$seatsize=0;
}
    $divwidth = (($seats[0]['total_seats_per_row']) + 2) * (24+$seatsize);
     $divwidth=$divwidth * $rst_options['rst_zoom'];

    $mindivwidth = 640;
    if ($divwidth < $mindivwidth) {
        $divwidth = $mindivwidth;
    }	 
	if($rst_options['rst_fixed_width'])
	{
	$divwidth =$rst_options['rst_fixed_width'];
	}	 
    $showname = $data[0]['show_name'];

    $rst_bookings = $currentcart;

    $sessiondata = $rst_bookings;

    $sessiondata = base64_encode($sessiondata);

    $rst_bookings = unserialize($rst_bookings);

    $html = '<div class="seatplan" id="showid_' . $showid . '" style="width:' . $divwidth . 'px;">';

    $nextrow = '';

    $dicount = 0;

    for ($i = 0; $i < count($seats); $i++) {

        $data = $seats[$i];
        $nofsets = $data['total_seats_per_row'];
        $nofsets = floor($nofsets / 2);
		
//print "----------------------".strlen($event_stall);
//$event_stall=strrev($event_stall);
for ($z=0;$z<strlen($event_stall);$z++) {
$stall[$nofsets+$z]=$event_stall[$z];
}
		
        //$stall[$nofsets] = 'S';
        //$stall[$nofsets + 1] = 'T';
        //$stall[$nofsets + 2] = 'A';
       // $stall[$nofsets + 3] = 'L';
       // $stall[$nofsets + 4] = 'L';
        ///

        if ($showorder != 0) {
$event_stall=strrev($event_stall);
for ($z=0;$z<strlen($event_stall);$z++) {
$stall[$nofsets+$z]=$event_stall[$z];
}		
		
            //$stall[$nofsets] = 'L';
            //$stall[$nofsets + 1] = 'L';
            //$stall[$nofsets + 2] = 'A';
            //$stall[$nofsets + 3] = 'T';
            //$stall[$nofsets + 4] = 'S';
        }
//$event_stall=strrev($event_stall);
for ($z=0;$z<strlen($event_balcony);$z++) {
$balcony[$nofsets+$z]=$event_balcony[$z];
}	
		
        //$balcony[$nofsets] = 'B';
       // $balcony[$nofsets + 1] = 'A';
        //$balcony[$nofsets + 2] = 'L';
        //$balcony[$nofsets + 3] = 'C';
       // $balcony[$nofsets + 4] = 'O';
       // $balcony[$nofsets + 5] = 'N';
       // $balcony[$nofsets + 6] = 'Y';
        if ($showorder != 0) {
		
$event_balcony=strrev($event_balcony);
for ($z=0;$z<strlen($event_balcony);$z++) {
$balcony[$nofsets+$z]=$event_balcony[$z];
}			
           // $balcony[$nofsets] = 'Y';
            //$balcony[$nofsets + 1] = 'N';
            //$balcony[$nofsets + 2] = 'O';
            //$balcony[$nofsets + 3] = 'C';
            //$balcony[$nofsets + 4] = 'L';
            //$balcony[$nofsets + 5] = 'A';
            //$balcony[$nofsets + 6] = 'B';
        }
        //
//$event_balcony=strrev($event_balcony);
for ($z=0;$z<strlen($event_circle);$z++) {
$circle[$nofsets+$z]=$event_circle[$z];
}			
        //$circle[$nofsets] = 'C';
       // $circle[$nofsets + 1] = 'I';
       // $circle[$nofsets + 2] = 'R';
       // $circle[$nofsets + 3] = 'C';
       // $circle[$nofsets + 4] = 'L';
       // $circle[$nofsets + 5] = 'E';
        if ($showorder != 0) {
$event_circle=strrev($event_circle);
for ($z=0;$z<strlen($event_circle);$z++) {
$circle[$nofsets+$z]=$event_circle[$z];
}		
            //$circle[$nofsets] = 'E';
            //$circle[$nofsets + 1] = 'L';
           // $circle[$nofsets + 2] = 'C';
            //$circle[$nofsets + 3] = 'R';
           // $circle[$nofsets + 4] = 'I';
           // $circle[$nofsets + 5] = 'C';
        }

        $rowname = $data['row_name'];

        $seatno = $data['seatno'];

        $seatcost = $data['seat_price'];

        $seatdiscost = $data['discount_price'];

        if ($i == 0) {

            if ($rowname == '') {
                $html .= '<div style="float:left;"><ul class="r"><li class="stall showseats">' . $rowname . '</li>';
            } else {
                $html .= '<div style="float:left;"><ul class="r"><li class="ltr">' . $rowname . '</li>';
            }
        }
        if ($nextrow != $rowname && $i != 0) {
            if ($rowname == '') {
                $html .= '<li class="ltr">' . $nextrow . '</li></ul></div><div style="float:left;"><ul class="r"><li class="stall showseats">' . $rowname . '</li>';
            } else {
                if ($nextrow == '') {
                    $html .= '<li class="stall showseats">' . $nextrow . '</li></ul></div><div style="float:left;"><ul class="r"><li class="ltr">' . $rowname . '</li>';
                } else {
                    $html .= '<li class="ltr">' . $nextrow . '</li></ul></div><div style="float:left;"><ul class="r"><li class="ltr">' . $rowname . '</li>';
                }

            }

        }
        $rst_options = get_option(RSTPLN_OPTIONS);

        $dicount = $rst_options['rst_h_disc'];

        if ($dicount != '') {

            $dicount = $seatcost - ($seatcost * ($dicount / 100));

            $dicount = round($dicount, 2);

        } else {

            $dicount = $seatcost;

        }

        $seats_avail_per_row = unserialize($data['seats_avail_per_row']);

        $otherscart = false;


		$cssclassname="notbooked";
		$cssclassname=apply_filters('row_seats_color_selection_css_name',$cssclassname,$data['seatcolor']);
		
		
        if ($data['seattype'] == 'N') {

            $html .= '<li class="un showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '_' . $seatno . '" title="'.$event_seat.' ' . $rowname . ($seatno) . ' Unavailable" rel="' . $data['seattype'] . '"></li>';

        } else if ($data['seattype'] == 'Y') {

            $html .= '<li class="'.$cssclassname.' showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '_' . $seatno . '" title="'.$event_seat.' ' . $rowname . ($seatno) . ' '.$event_price.' ' . $symbol . $seatcost . ' '.$event_seat_available.'" rel="' . $data['seattype'] . '">' . ($seatno) . '</li>';

        } else if ($data['seattype'] == 'H') {

            $html .= '<li class="handy showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '_' . $seatno . '" title="'.$event_seat.' ' . $rowname . ($seatno) . ' '.$event_seat_handicap.' ' . $symbol . $dicount . ' " rel="' . $data['seattype'] . '">' . $seatno . '</li>';

        } else if ($data['seattype'] == 'B') {

            $html .= '<li class="booked showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '_' . $seatno . '" title="'.$event_seat.' ' . $rowname . ($seatno) . ' '.$event_seat_booked.'" rel="' . $data['seattype'] . '">' . $seatno . '</li>';

        } else if ($data['seattype'] == 'T') {

            for ($o = 0; $o < count($rst_bookings); $o++) {

                if ($rst_bookings[$o]['row_name'] == $rowname && $rst_bookings[$o]['seatno'] == $seatno) {

                    $otherscart = true;

                }

            }

            if ($otherscart) {

                $html .= '<li class="blocked showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '_' . $seatno . '" title="'.$event_seat.' ' . $rowname . ($seatno) . '" rel="' . $data['seattype'] . '">' . $seatno . '</li>';

            } else {

                $html .= '<li class="b showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '_' . $seatno . '" title="'.$event_seat.' ' . $rowname . ($seatno) . '  '.$event_seat_blocked.'" rel="' . $data['seattype'] . '">' . $seatno . '</li>';

            }

        } else if ($data['seattype'] == 'S')

            $html .= '<li class="s showseats" id="' . $showname . '' . $showid . $rowname . $seatno . '" title="" rel="">' . $stall[$seatno] . '</li>';

        else if ($data['seattype'] == 'L')

            $html .= '<li class="l showseats" id="' . $showname . '' . $showid . $rowname . $seatno . '" title="" rel="">' . $balcony[$seatno] . '</li>';

        else if ($data['seattype'] == 'C')

            $html .= '<li class="c showseats" id="' . $showname . '' . $showid . $rowname . $seatno . '" title="" rel="">' . $circle[$seatno] . '</li>';

        else {

            $html .= '<li class="un showseats" id="' . $showname . '' . $showid . $rowname . $seatno . '_' . $seatno . '" title="" rel=""></li>';

        }

        $nextrow = $rowname;

    }

    $html .= '<li class="ltr">' . $nextrow . '</li></ul></div>';
$html = apply_filters('row_seats_generel_admission_block_seatchart', $html);
    return $html;

}


/*
 * Returns details of the show by show id
 */
function getshowbyid($showid)
{

    global $wpdb;

    $sql = "SELECT * FROM $wpdb->rst_shows where id=" . $showid;

    $found = 0;

    $data = Array();

    if ($results = $wpdb->get_results($sql, ARRAY_A)) {

        foreach ($results as $value) {

            $found++;

        }

        if ($found == 0) {

            return $data;

        } else {

            $data = $wpdb->get_results($sql, ARRAY_A);

            return $data;

        }


    }

}


/*
 * Creates operations with seats for AJAX requests
 */
function rst_seats_operations($action, $finalseats, $showid)
{
    global $wpdb;

    $modby = $current_user->user_login;

    switch ($action) {
        case 'delete':
            return $wpdb->query("DELETE FROM $wpdb->rst_seats WHERE id='$showid'");
            return true;

            break;

        case 'insert':
            $showid = $showid;
            $sql = "SELECT * FROM $wpdb->rst_seats where show_id=" . $showid;
            $found = 0;

            if ($results = $wpdb->get_results($sql, ARRAY_A)) {

                foreach ($results as $value) {

                    $found++;
                }
                if ($found != 0) {
                    delete_seats('delete', '', $showid);

                }
            }

            $curdate = date('Y-m-d H:i:s');

            for ($i = 0; $i < count($finalseats); $i++) {
                $data = $finalseats[$i];
                $name = $data['row'];
                $seat_price = $data['price'];
                if ($seat_price == '') {
                    $seat_price = 0.00;
                }
                $discount_price = $data['price'];
                if ($discount_price == '') {
                    $discount_price = 0.00;
                }
                $seats = ($data['seats']);
                $total_seats_per_row = count($seats);
                $seatno = 0;

                for ($j = 0; $j < count($seats); $j++) {

                    $seattype = $seats[$j];
                    if ($seattype == 'S') {

                    }

                    if ($seattype != '') {
                        $seatno++;
                    }
                    //Code added for custom seat number -start
                    $customseatupdated = apply_filters('row_seats_custom_seat_number', array($seattype, $seatno));
					$cusseatnumber=$customseatupdated[1];
					$seattype=$customseatupdated[0];
					//Code added for custom seat number -end

					
                    $sql = "INSERT INTO $wpdb->rst_seats (show_id,row_name,total_seats_per_row,seatno,seattype,originaltype,seat_price,discount_price,status,created_date,mod_date,mod_by)
                    VALUES ($showid, '$name', '$total_seats_per_row','$cusseatnumber','$seattype','$seattype',$seat_price,$discount_price,'not blocked','$curdate','$curdate','$modby')";

                    $wpdb->query($sql);
                }
            }

            rst_shows_operations('updatestatus', array('status' => 'Seats Added', 'id' => $showid), '');

            return '<div class="updated"><p><strong>Seats Added..</strong></p></div>';


            break;

        case 'update':
            $name = $data['title'];
            $venue = $data['body'];
            $showstart = $data['start'];
            $showend = $data['end'];
            $status = $data['status'];
            $curdate = date('Y-m-d H:i:s');
            $showdate = date('Y-m-d H:i:s', strtotime($showstart));
            $id = $data['id'];
            $wpdb->query("UPDATE  $wpdb->rst_shows SET show_name='$name',show_start_time='$showstart',show_end_time='$showend',show_date='$showdate',venue='$venue',status='',mod_date='$curdate',mod_dy='$modby' WHERE id=" . $id);
            return '<div class="updated"><p><strong>Testimonial Updated..</strong></p></div>';


            break;

        case 'list':

            $sql = "SELECT * FROM $wpdb->rst_seats where show_id=" . $showid . " order by seatid ";
            $found = 0;
            $data = Array();
            if ($results = $wpdb->get_results($sql, ARRAY_A)) {

                foreach ($results as $value) {
                    $found++;
                }
                if ($found == 0) {
                    return $data;
                } else {
                    $data = $wpdb->get_results($sql, ARRAY_A);
                    return $data;
                }

            }


            break;

        case 'reverse':

            $sql = "SELECT * FROM $wpdb->rst_seats where show_id=" . $showid . " order by seatid ";
            $found = 0;
            $data = Array();
            if ($results = $wpdb->get_results($sql, ARRAY_A)) {

                foreach ($results as $value) {

                    $found++;
                }
                if ($found == 0) {
                    return $data;
                } else {
                    $data = $wpdb->get_results($sql, ARRAY_A);
                    $finaldata = array();
                    $reversed = array();
                    $initialrow = $data[0]['row_name'];
                    for ($i = 0; $i < count($data); $i++) {

                        if ($initialrow == $data[$i]['row_name']) {

                            $finaldata[] = $data[$i];
                            if ($i == count($data) - 1) {
                                $finaldata = array_reverse($finaldata);
                                for ($j = 0; $j < count($finaldata); $j++) {
                                    $reversed[] = $finaldata[$j];
                                }
                            }

                        } else {
                            $finaldata = array_reverse($finaldata);
                            for ($j = 0; $j < count($finaldata); $j++) {
                                $reversed[] = $finaldata[$j];
                            }

                            $finaldata = array();
                            $initialrow = $data[$i]['row_name'];
                            $finaldata[] = $data[$i];
                        }

                    }

                    return ($reversed);
                }

            }


            break;

        case 'byid':

            $id = $data['vmid'];
            $sql = "SELECT * FROM $wpdb->rst_shows where id=" . $id;
            $found = 0;
            $data = Array();
            if ($results = $wpdb->get_results($sql, ARRAY_A)) {

                foreach ($results as $value) {
                    $found++;
                }
                if ($found == 0) {
                    return $data;
                } else {
                    $data = $wpdb->get_results($sql, ARRAY_A);
                    return $data;
                }

            }

            break;

        default:
            break;
    }

    return true;
}


/*
 * Generates seat chart html-content. This function is called via Ajax request.
 */
function gettheseatchartAjax($showid, $currenturl, $bookings, $offline = '')
{

global $screenspacing,$wpdb;

			if ( is_user_logged_in() ) {
				global $current_user;
				get_currentuserinfo();
				$user_name=$current_user->user_firstname.' '.$current_user->user_lastname;	
				$user_email=$current_user->user_email;	
				$user_phone=$current_user->phone;					
			
			} else {			
				$user_name='';	
				$user_email='';	
				$user_phone='';				
			}	  


	  
	$wplanguagesoptions = get_option(RSTLANGUAGES_OPTIONS);

	$event_seat_available="Available";
	$event_seat_inyourcart="In Your Cart";
	$event_seat_inotherscart="In Other&#39;s Cart";
	$event_seat_booked="Booked";
	$event_seat_handicap="Wheelchair Access";
	$event_itemsincart="Items in Cart";
    $event_item_cost="Cost";
	$event_item_total="Total";
	$event_item_grand="Grand";
	$event_item_checkout="Checkout";
	$event_item_clearcart="Clear Cart";
	$event_bookingdetails="Booking Details ";
	$event_customer_name="First Name & Last Name";
	$event_customer_email="Email";
	$event_customer_phone="Phone";
	$event_terms="I Agree Terms & Conditions";
	$cart_is_empty="CART IS EMPTY";
	$event_seat="Seat";
	$event_seat_row="Row";
	$event_item_cost="Cost";
	$button_continue="Continue";
	$languages_added="Added";
	$event_stall="STALL";
	$event_balcony="BALCONY";
	$event_circle="CIRCLE";	
	$event_seat_stage="STAGE";	
	$coupon_vip_member="I am a VIP Member";

	
	if($wplanguagesoptions['rst_enable_languages']=="on")
	{
		if($wplanguagesoptions['languages_event_seat_row'])
		{
			$event_seat_row=$wplanguagesoptions['languages_event_seat_row'];
		}
		
		if($wplanguagesoptions['languages_event_seat_available'])
		{
			$event_seat_available=$wplanguagesoptions['languages_event_seat_available'];
		}
		if($wplanguagesoptions['languages_event_seat_inyourcart'])
		{
			$event_seat_inyourcart=$wplanguagesoptions['languages_event_seat_inyourcart'];
		}	
		if($wplanguagesoptions['languages_event_seat_inotherscart'])
		{
			$event_seat_inotherscart=$wplanguagesoptions['languages_event_seat_inotherscart'];
		}	
		if($wplanguagesoptions['languages_event_seat_booked'])
		{
			$event_seat_booked=$wplanguagesoptions['languages_event_seat_booked'];
		}	
		if($wplanguagesoptions['languages_event_seat_handicap'])
		{
			$event_seat_handicap=$wplanguagesoptions['languages_event_seat_handicap'];
		}	
		if($wplanguagesoptions['languages_event_itemsincart'])
		{
			$event_itemsincart=$wplanguagesoptions['languages_event_itemsincart'];
		}			
		if($wplanguagesoptions['languages_event_item_cost'])
		{
			$event_item_cost=$wplanguagesoptions['languages_event_item_cost'];
		}	
		if($wplanguagesoptions['languages_event_item_total'])
		{
			$event_item_total=$wplanguagesoptions['languages_event_item_total'];
		}
		if($wplanguagesoptions['languages_event_item_grand'])
		{
			$event_item_grand=$wplanguagesoptions['languages_event_item_grand'];
		}		
		if($wplanguagesoptions['languages_event_item_checkout'])
		{
			$event_item_checkout=$wplanguagesoptions['languages_event_item_checkout'];
		}
		if($wplanguagesoptions['languages_event_item_clearcart'])
		{
			$event_item_clearcart=$wplanguagesoptions['languages_event_item_clearcart'];
		}
		if($wplanguagesoptions['languages_event_bookingdetails'])
		{
			$event_bookingdetails=$wplanguagesoptions['languages_event_bookingdetails'];
		}			
        if($wplanguagesoptions['languages_event_customer_name'])
		{
			$event_customer_name=$wplanguagesoptions['languages_event_customer_name'];
		}			
        if($wplanguagesoptions['languages_event_customer_email'])
		{
			$event_customer_email=$wplanguagesoptions['languages_event_customer_email'];
		}			
        if($wplanguagesoptions['languages_event_customer_phone'])
		{
			$event_customer_phone=$wplanguagesoptions['languages_event_customer_phone'];
		}			
        if($wplanguagesoptions['languages_event_customer_terms'])
		{
			$event_terms=$wplanguagesoptions['languages_event_customer_terms'];
		}			
         if($wplanguagesoptions['languages_cart_is_empty'])
		{
			$cart_is_empty=$wplanguagesoptions['languages_cart_is_empty'];
		}	
        if($wplanguagesoptions['languages_event_seat'])
		{
			$event_seat=$wplanguagesoptions['languages_event_seat'];
		}			
        if($wplanguagesoptions['languages_event_item_cost'])
		{
			$event_item_cost=$wplanguagesoptions['languages_event_item_cost'];
		}	
        if($wplanguagesoptions['languages_button_continue'])
		{
			$button_continue=$wplanguagesoptions['languages_button_continue'];
		}	
        if($wplanguagesoptions['languages_added'])
		{
			$languages_added=$wplanguagesoptions['languages_added'];
		}	
		if($wplanguagesoptions['languages_event_stall'])
		{
			$event_stall=$wplanguagesoptions['languages_event_stall'];
		}			
		if($wplanguagesoptions['languages_event_balcony'])
		{
			$event_balcony=$wplanguagesoptions['languages_event_balcony'];
		}
		if($wplanguagesoptions['languages_event_circle'])
		{
			$event_circle=$wplanguagesoptions['languages_event_circle'];
		}		
		if($wplanguagesoptions['languages_event_seat_stage'])
		{
			$event_seat_stage=$wplanguagesoptions['languages_event_seat_stage'];
		}	
		if($wplanguagesoptions['languages_coupon_vip_member'])
		{
			$coupon_vip_member=$wplanguagesoptions['languages_coupon_vip_member'];
		}			
}	



    ?>

    <!-- OUR PopupBox DIV-->

    <?php

    $rst_options = get_option(RSTPLN_OPTIONS);

    $rst_paypal_options = get_option(RSTPLN_PPOPTIONS);

    $rst_tandc = $rst_options['rst_tandc'];

    $rst_email = $rst_options['rst_email'];

    $rst_etem = $rst_options['rst_etem'];

    $paypal_id = $rst_paypal_options['paypal_id'];

    $paypal_url = $rst_paypal_options['paypal_url'];

    $return_page = $rst_paypal_options['custom_return'];

    $symbol = $rst_paypal_options['currencysymbol'];
	$symbol = get_option('rst_currencysymbol');
	
	//print $symbol."------------".$rst_options['rst_currency'];

    $symbols = array(
        "0" => "$",
        "1" => "&pound;",
        "2" => "&euro;",
        "3" => "&#3647;",
        "4" => "&#8362;",
        "5" => "&yen;",
        "6" => "&#8377;",
        "7" => "R$",
        "8" => "kr",
        "9" => "zł",
        "10" => "Ft",
        "11" => "Kč",
        "12" => "&#1088;&#1091&#1073;",
        "13" => "&#164;",
        "14" => "&#x20B1;",
        "15" => "Fr",
        "16" => "RM");

    $symbol = $symbols[$symbol];

    $notifyURL = RST_PAYPAL_PAYMENT_URL . "ipn.php";

    if ($return_page == "") {

        $returnURL = get_option('siteurl') . "/?paypal_return='true'";

    } else {

        $returnURL = $return_page;

    }

    // Process

    // Send back the contact form HTML

    //  require_once('inc.checkout.form.php');

    ?>


    <script type="text/javascript" language="javascript">

        jQuery(document).ready(function () {

            jQuery(".QTPopup").css('display', 'none')

            jQuery(".contact").click(function () {
<?php
apply_filters('row_seats_seat_restriction_js_filter','');
?>
			
                document.getElementById('startedcheckout').value = "yes";

                jQuery(".QTPopup").animate({width: 'show'}, 'slow');
            })

            jQuery(".closeBtn").click(function () {

                document.getElementById('startedcheckout').value = "";


                jQuery(".QTPopup").css('display', 'none');

            })

        })

    </script>


    <?php

    $currenturl = $_REQUEST['redirecturl'];

    $rst_options = get_option(RSTPLN_OPTIONS);

    $rst_paypal_options = get_option(RSTPLN_PPOPTIONS);

    $rst_tandc = $rst_options['rst_tandc'];

    $rst_email = $rst_options['rst_email'];

    $rst_etem = $rst_options['rst_etem'];

    $paypal_id = $rst_paypal_options['paypal_id'];

    $paypal_url = $rst_paypal_options['paypal_url'];

    $return_page = $rst_paypal_options['custom_return'];
    if ($return_page != '') {
        $currenturl = $return_page;
    }
    $currency = $rst_paypal_options['currency'];

    $symbol = $rst_paypal_options['currencysymbol'];
	$symbol = get_option('rst_currencysymbol');

    $symbols = array(
        "0" => "$",
        "1" => "&pound;",
        "2" => "&euro;",
        "3" => "&#3647;",
        "4" => "&#8362;",
        "5" => "&yen;",
        "6" => "&#8377;",
        "7" => "R$",
        "8" => "kr",
        "9" => "zł",
        "10" => "Ft",
        "11" => "Kč",
        "12" => "&#1088;&#1091&#1073;",
        "13" => "&#164;",
        "14" => "&#x20B1;",
        "15" => "Fr",
        "16" => "RM");

    $symbol = $symbols[$symbol];

    $sesid = session_id();

    $notifyURL = RST_PAYPAL_PAYMENT_URL . "ipn.php";

    if ($return_page == "") {
        $returnURL = get_option('siteurl') . "/?paypal_return='true'";
    } else {
        $returnURL = $return_page;
    }

    ?>


    <div class="QTPopup">

    <div class="popupGrayBg"></div>

    <div id='elem'  class="QTPopupCntnr"   style="width: 750px; <?php echo apply_filters('row_seats_generel_admission_popupfix',$showid);?>">

    <div class="gpBdrLeftTop"></div>

    <div class="gpBdrRightTop"></div>

    <div class="gpBdrTop"></div>

    <div class="gpBdrLeft">

    <div class="gpBdrRight">

    <div class="caption">

        <?php echo $event_bookingdetails;?>

    </div>

    <a href="#" class="closeBtn" title="Close"></a>

    <div class="checkoutcontent">

    <form method='POST' action='' target="rsiframe"  onsubmit="row_seats_presubmit('<?php print $showid['id'];?>');"  enctype='multipart/form' name="checkoutform">
 <div class="row_seats_signup_form" id="rssignup_form">
    <table width="100%" cellpadding="0" cellspacing="0">

    <tr>
    <td class="tableft" width="50%">

        <table width="100%" cellpadding="0" cellspacing="0" border="1" bordercolor="red">
<?php
		$totalitems= count($bookings);

if($rst_options['rst_enable_special_pricing']=="on" and row_seats_special_pricing_verification())
{	
		
?>
<script language="javascript">
function updateprice()
{
	var totalitems=document.getElementById('totalrecords').value;
	var total=0;
	var gtotal=0;
	var htmlstring;
	for (var i=0; i<totalitems; i++)
	{
	var dropboxvalue=document.getElementById('special_pricing'+i).value;
	var dropboxvalues = dropboxvalue.split('#');
		document.getElementById("price"+i).innerHTML=""+dropboxvalues[1];
		total=parseFloat(total)+parseFloat(dropboxvalues[1]);
	}
	document.getElementById("total").innerHTML=formatCurrency(total);
	document.getElementById('amount').value=total;
	gtotal=total;

	if(document.getElementById('rst_fees').value!='')
	{
		gtotal=parseFloat(gtotal) + parseFloat(document.getElementById('rst_fees').value);
		document.getElementById('aftercoupongrand').style.visibility="visible";
	}

	if(document.getElementById('coupondiscount').value!='')
	{
		gtotal=parseFloat(gtotal) - parseFloat(document.getElementById('coupondiscount').value);
		document.getElementById('discountamount').innerHTML=document.getElementById('coupondiscount').value;
		document.getElementById('aftercoupondis').style.visibility="visible";
		document.getElementById('aftercoupongrand').style.visibility="visible";
	}
	document.getElementById('Grandtotal').innerHTML=formatCurrency(gtotal);
	document.getElementById('amount').value=gtotal;

}
</script>

            <?php
			
}			

            $rst_bookings = $bookings;
	    //print_r($bookings);
            $mycartitems = serialize($bookings);
            $description = $rst_bookings;

            $total = 0;

            $totalseats = 0;
				if($rst_options['rst_enable_special_pricing']=="on" and row_seats_special_pricing_verification())
				{				
		$rst_options['rst_special_pricing_count']=row_seats_special_number_of_special_price($_SESSION['views']);
		$totalspecialpricing=$rst_options['rst_special_pricing_count']+1;
	    if(!$rst_options['rst_special_pricing_count'])
	    $totalspecialpricing=1;
		}

           for ($i = 0; $i < count($rst_bookings); $i++) 
{

                $rst_booking = $rst_bookings[$i];
				//creating special price dropdown
				if($rst_options['rst_enable_special_pricing']=="on" and row_seats_special_pricing_verification())
				{			
					for($j=1;$j<$totalspecialpricing;$j++){
						$special_pricing_array=array();
						$special_pricing_array=row_seats_special_special_price_array($_SESSION['views'],$rst_booking['price']);
					}	
				}	

		?>


                <tr>
                    <td width=50%"><?php echo $event_seat;?>:<?php echo $rst_booking['row_name'] . $rst_booking['seatno'];?>-<?php echo $event_item_cost;?>:</td>
                    <td><table><tr><td><span style="color: maroon;font-size: small;"><?php echo $symbol;?></span><span style="color: maroon;font-size: small;" id="price<?php echo $i;?>"><?php echo $rst_booking['price'];?></span></td>
					<td>
					<?php
					//creating special price dropdown
					
					if($rst_options['rst_enable_special_pricing']=="on" and row_seats_special_pricing_verification())
					{	
					?>					
					<select name="special_pricing<?php echo $i;?>" id="special_pricing<?php echo $i;?>" onchange="updateprice();">		
					<option value="normal#<?php echo $rst_booking['price'];?>">Normal</option>
								<?php
					foreach($special_pricing_array as $key=>$value){
					print "<option value='".$key."#".$value."'>".$key."           ".$symbol.$value."</option>";
					}
					?>		
					</select>

					<?php
					}
					?>						
					</td></tr></table></td>
                </tr>


                <?php $total = $total + $rst_booking['price'];

                $total = number_format($total, 2, '.', '');

            }

            ?>


            <tr class="carttotclass" style="border-top:1px solid #e7e7e7 !important;">
                <td width="50%"><span style="color: maroon;font-size: larger;"><?php echo $event_item_total;?>:</span></td>
                <td width="50%" ><span style="color: maroon;font-size: larger;"><?php echo $symbol;?></span><span
                        style="color: maroon;font-size: larger;" id="total"><?php echo $total;?></span>
                </td>
            </tr>

		

            <?php
            $wpfeeoptions = get_option(RSTFEE_OPTIONS);
            $sercharge = 0;
            // print_r($wpfeeoptions);
            if ($wpfeeoptions['rst_enable_fee'] == 'on' && /*fees-----*/
                apply_filters('rst_fee_plugin_filter', '') /*-----fees*/
            ) {
                if ($wpfeeoptions['rst_fee_type'] == 'flate') {
                    $gtotal = $wpfeeoptions['fee_amt'] + $total;
                    $gtotal = number_format($gtotal, 2, '.', '');
                    $sercharge = number_format($wpfeeoptions['fee_amt'], 2, '.', '');
                } else {
                    $sercharge = number_format((($wpfeeoptions['fee_amt'] * $total) / 100), 2, '.', '');
                    $gtotal = number_format(($sercharge + $total), 2, '.', '');
                }
            } else {
                $gtotal = $total;
            }
            ?>

            <?php if ($wpfeeoptions['rst_enable_fee'] == 'on') { ?>

                <?php
                /* fees ----- */
                apply_filters('rst_fee_fields_filter', '', $wpfeeoptions, $symbol, $sercharge, $gtotal);
				$fee_name=$wpfeeoptions['fee_name'];
                /* ----- fees */
                ?>
				
            <tr >

                <td width="50%"><span style="color: green;font-size: larger;"><?php echo $fee_name;?>:</span></td>

                <td width="50%"><span style="color: green;font-size: larger;"><?php echo $symbol;?></span><span
                        style="color: green;font-size: larger;" id="fee_name"><?php echo esc_attr($sercharge); ?></span></td>

            </tr>				

            <?php } ?>


            <tr id="aftercoupondis" style="display: none;">

                <td width="50%"><span style="color: green;font-size: larger;">Discount:</span></td>

                <td width="50%"><span style="color: green;font-size: larger;"><?php echo $symbol;?></span><span
                        style="color: green;font-size: larger;" id="discountamount"></span></td>

            </tr>
            <tr id="aftercoupongrand" style="border-top:1px solid #e7e7e7 !important;"
                class="carttotclass">

                <td width="50%"><span style="color: maroon;font-size: larger;"><strong><?php echo $event_item_grand;?> :</strong></span></td>

                <td width="50%"><span style="color: maroon;font-size: larger;"><?php echo $symbol;?></span><span
                        style="color: maroon;font-size: larger;" id="Grandtotal"><?php echo $gtotal;?></span></td>

            </tr>

        </table>

    </td>
    <td class="tabright" width="60%" style="border-left:1px solid #e7e7e7 !important; ">

   
        <table>
        
        <?php 
			$row='Before Name';
			$contact_field = apply_filters('row_seats_custom_fieldname',$row);
			?>	
            <tr>
                <td colspan='2'><label for='contact-name'><span class='reqa'>*</span> <?php echo $event_customer_name;?>:</label>

                    <input type='text' id='contact_name' class='contact-input' name='contact_name'
                           value='<?php echo $user_name;?>'/></td>
            </tr>

 <?php 
			$row='After Name';
			$contact_field = apply_filters('row_seats_custom_fieldname',$row);
			 
			$row='Before Email';
			$contact_field = apply_filters('row_seats_custom_fieldname',$row);
			?>	
            <tr>
                <td colspan='2'><label for='contact-email'><span class='reqa'>*</span> <?php echo $event_customer_email;?>:</label>

                    <input type='text' id='contact_email' class='contact-input' name='contact_email'
                           value='<?php echo $user_email;?>'/></td>
            </tr>
          <?php 
			$row='After Email';
			$contact_field = apply_filters('row_seats_custom_fieldname',$row);
			 
			$row='Before Phone';
			$contact_field = apply_filters('row_seats_custom_fieldname',$row);
			?>	

            <tr>
                <td colspan='2'><label for='contact-email'><span class='reqa'>*</span> <?php echo $event_customer_phone;?>:</label>

                    <input type='text' id='contact_phone' class='contact-input' name='contact_phone'
                           value='<?php echo $user_phone;?>'/></td>
            </tr>
             <?php 
			$row='After Phone';
			$contact_field = apply_filters('row_seats_custom_fieldname',$row);
			?>	
            <tr>
                <td colspan='2'>
                    <div><input type='checkbox' id='rstterms' class='contact-input' name='rstterms'/>

                        <label class='termsclass'><span class='reqa'>*</span> <?php echo $event_terms;?>:</label><br/><label
                            style="float: left !important;width:100% !important"><?php echo stripslashes($rst_tandc); ?></label>
                    </div>
                </td>
            </tr>


            <!--            members and coupons ----- -->

            <?php $members = apply_filters('rst_apply_member_filter', ''); ?>

            <?php $coupons = apply_filters('rst_apply_coupon_filter', ''); ?>

            <?php
            if ($members && $coupons) {
                echo "
                <tr>
                    <td colspan='2'><input type='checkbox' id='rstmem' class='contact-input' name='rstmem' onclick=\"checkformember(this);\"/>
                    <label class='termsclass'> ".$coupon_vip_member.":</label></td>
                </tr>
                ";
                echo $coupons;
            } elseif ($members) {
                echo $members;
            } elseif ($coupons) {
                echo $coupons;
            }
            ?>

            <!--                    ----- members and coupons -->


            <tr>
                <td colspan='2'>


                    <!--                    memberapplybtn and couponapplybtn ----- -->

                    <?php $membersBtn = apply_filters('rst_apply_member_btn_filter', ''); ?>

                    <?php $couponsBtn = apply_filters('rst_apply_coupon_btn_filter', ''); ?>

                    <?php
                    if ($members && $coupons) {
                        echo $couponsBtn;
                    } elseif ($members) {
                        echo $membersBtn;
                    } elseif ($coupons) {
                        echo $couponsBtn;
                    }
                    ?>

                    <!--                    ----- memberapplybtn and couponapplybtn -->


                    <span id="couponprogress" style="color: #51020B;padding-left:10px;"></span><br/>


                </td>
            </tr>

            <tr>
                <td colspan='2'>&nbsp; </td>
            </tr>
	     <tr>
                <td colspan='2'>
<?php
$active_payment_methods = apply_filters('row_seats_active_payment_methods', array());
//print_r($active_payment_methods);
$available_payment_methods = apply_filters('row_seats_available_payment_methods', array());
//print_r($available_payment_methods);
$activecurrency = get_option('rst_currency');
//print $activecurrency;
$payment_methods = apply_filters('row_seats_currency_payment_methods', $active_payment_methods, $activecurrency);
//print_r($payment_methods);



            if(current_user_can('contributor') || current_user_can('administrator'))	
{
//print "Inside-------------";
			$form .= '<input type="hidden" value="offlinepayment_force" name="payment_method">';

}			
			elseif (sizeof($payment_methods) > 0) {

				$form .= '

				<div class="rst_form_row">';

				$checked = ' checked="checked"';

				foreach ($payment_methods as $key => $method) {

					$form .= '

					<div style="background: transparent url('.$method['logo'].') 25px '.$method['logo_vertical_shift'].'px no-repeat; height: 45px; width: '.($method['logo_width']+25).'px; float: left; margin-right: 30px;">

						<input type="radio" value="'.$key.'" name="payment_method" style="margin: 4px 0px;"'.$checked.'>

					</div>';

					$checked = '';

				}

				$form .= '

				</div>';

			} else {
			$form .= '<input type="hidden" value="offlinepayment_force" name="payment_method">';
			
			}
			
			print $form;

?>		
		
		</td>
            </tr>    
	    
            <!--added class-->
            <tr>
                <td colspan='2'><input type="submit" id="rssubmit" class="row_seats_submit" value="<?php echo $button_continue;?>" > <img id="rsloading" class="row_seats_loading" src="<?php echo plugins_url('/images/loading.gif', __FILE__);?>" alt="">
				   
	<!--<a href="javascript:void(0);" onclick="savecheckoutdata('placeorder')"
                                   class='srbutton srbutton-css'>Place Order</a><img id="rsloading" class="row_seats_loading" src="'.plugins_url('/images/loading.gif', __FILE__).'" alt="">-->			   
				   
				   </td>
            </tr>

            <input type="hidden" name="cmd" value="_xclick"/>

            <input type="hidden" name="notify_url" value="<?php echo $notifyURL; ?>"/>
			<input type="hidden" name="action" value="wp_row_seats-signup" />

            <input type="hidden" name="return" id="return" value="<?php echo $return_page ?>"/>

            <input type="hidden" name="business" value="<?php echo $paypal_id; ?>"/>

            <input type="hidden" name="amount" id="amount" value="<?php echo esc_attr($gtotal); ?>"/>


            <input type="hidden" id="item_name" name="item_name" value="Seats Booking"/>
	     <input type="hidden" id="bookingid" name="bookingid" value=""/>

            <input type="hidden" name="custom" id="custom" value=""/>

            <input type="hidden" name="no_shipping" value="0"/>
            <input type="hidden" name="currency_code" value="<?php echo $currency ?>"/>
	    <input type="hidden" name="mycartitems"  id="mycartitems" value="<?php echo $mycartitems; ?>"/>
	    
	    

        </table>
	    <div id="rsmessage5" class="row_seats_message"></div>
<iframe id="rsiframe" name="rsiframe" class="row_seats_iframe" onload="row_seats_load();"></iframe>
    
	

	

    </td>
    </tr>

    </table>
    </div>
    <input type="hidden" name="appliedcoupon" id="appliedcoupon" value=""/>

    <input type="hidden" name="totalbackup" id="totalbackup" value="<?php echo esc_attr($gtotal); ?>"/>

    <input type="hidden" name="statusofcouponapply" id="statusofcouponapply" value=""/>

    <input type="hidden" name="totalrecords" id="totalrecords" value="<?php echo count($bookings);?>"/>
    
    <input type="hidden" name="coupondiscount" id="coupondiscount" value=""/>
    <input type="hidden" name="rst_fees" id="rst_fees" value="<?php echo esc_attr($sercharge); ?>"/>
	<input type="hidden" name="fee_name" id="fee_name" value="<?php echo esc_attr($fee_name); ?>"/>

    </form>
    <div class="row_seats_confirmation_container" id="rsconfirmation_container2"></div>	



    </div>

    </div>

    </div>

    <div class="gpBdrLeftBottom"></div>

    <div class="gpBdrRightBottom"></div>

    <div class="gpBdrBottom"></div>

    </div>

    </div>


    <?php
	
	


    $html = '';

    $showid = $showid['id'];

    //print_r($seats);

    $data = getshowbyid($showid);

    $showorder = $data[0]['orient'];

    if ($showorder == 0 || $showorder == '') {
        $seats = rst_seats_operations('list', '', $showid);
    } else {
        $seats = rst_seats_operations('reverse', '', $showid);
    }
$seatsize=$wpdb->get_var("SELECT LENGTH( seatno ) AS fieldlength FROM rst_seats where show_id=".$showid." ORDER BY fieldlength DESC LIMIT 1 ");
//print "<br>12-".$seatsize;
if($seatsize>2)
{
$seatsize=5*($seatsize-2);
}else
{
$seatsize=0;
}
    $divwidth = (($seats[0]['total_seats_per_row']) + 2) * (24+$seatsize);
     $divwidth=$divwidth * $rst_options['rst_zoom'];
	 
    $mindivwidth = 640;
    if ($divwidth < $mindivwidth) {
        $divwidth = $mindivwidth;
    }
	if($rst_options['rst_fixed_width'])
	{
	$divwidth =$rst_options['rst_fixed_width'];
	}
	
    $showname = $data[0]['show_name'];

    $html .= '';
	$seat_help='<span class="handy showseats" ></span> <span class="show-text">'.$event_seat_handicap.'  </span>';
	if($rst_options['rst_seat_help']=="disable")
	{
	$seat_help='';
	
	
	}
$colorchat=apply_filters('row_seats_color_selection_css2',$colorchat,$showid);
	


    $html .= '<div id="currentcart"><div style="width: '.$divwidth.'px;">'.$colorchat.'<span class="notbooked showseats" ></span><span class="show-text">'.$event_seat_available.'  </span>

        <span class="blocked showseats" ></span> <span class="show-text">'.$event_seat_inyourcart.'  </span>

       <span class="un showseats" ></span> <span class="show-text">'.$event_seat_inotherscart.'  </span>

       <span class="booked showseats" ></span> <span class="show-text">'.$event_seat_booked.'  </span>'.$seat_help.'<br/><br/>';

$topheader="";
if($rst_options['rst_stage_alignment']=="top" or !$rst_options['rst_stage_alignment'])
{
$topheader='<div class="stage-hdng" style="margin-left: 0px; margin-top: 0px; margin-right: 0px; margin-bottom: 10px;width:' . $divwidth . 'px; border:1px solid;border-radius:5px;box-shadow: 5px 5px 2px #888888;" >'.$event_seat_stage.'</div>';
}		
    $html .= '</div></div><br><br><br>'.$topheader;

    $rst_bookings = $bookings;

    $sessiondata = base64_encode(serialize($rst_bookings));

    if ($sessiondata != "") {

        ?>


        <script>
            jQuery.cookie("rst_cart_<?php echo $showid?>", '<?php echo $sessiondata;?>');
        </script>


    <?php

    }

    $foundcartitems = 0;

    $html .= '<div class="seatplan" id="showid_' . $showid . '" style="width:' . $divwidth . 'px  !important;" >';

    $nextrow = '';

    $dicount = 0;

    for ($i = 0; $i < count($seats); $i++) {

        $data = $seats[$i];

        $nofsets = $data['total_seats_per_row'];
        $nofsets = floor($nofsets / 2);
//$event_stall=strrev($event_stall);
for ($z=0;$z<strlen($event_stall);$z++) {
$stall[$nofsets+$z]=$event_stall[$z];
}

        //$stall[$nofsets] = 'S';
        //$stall[$nofsets + 1] = 'T';
        //$stall[$nofsets + 2] = 'A';
        //$stall[$nofsets + 3] = 'L';
       // $stall[$nofsets + 4] = 'L';
        ///

        if ($showorder != 0) {
		
$event_stall=strrev($event_stall);
for ($z=0;$z<strlen($event_stall);$z++) {
$stall[$nofsets+$z]=$event_stall[$z];
}
		
            //$stall[$nofsets] = 'L';
            //$stall[$nofsets + 1] = 'L';
            //$stall[$nofsets + 2] = 'A';
           // $stall[$nofsets + 3] = 'T';
            //$stall[$nofsets + 4] = 'S';
        }
//$event_balcony=strrev($event_balcony);
for ($z=0;$z<strlen($event_balcony);$z++) {
$balcony[$nofsets+$z]=$event_balcony[$z];
}
        //$balcony[$nofsets] = 'B';
        //$balcony[$nofsets + 1] = 'A';
        //$balcony[$nofsets + 2] = 'L';
        //$balcony[$nofsets + 3] = 'C';
       // $balcony[$nofsets + 4] = 'O';
       // $balcony[$nofsets + 5] = 'N';
       // $balcony[$nofsets + 6] = 'Y';
        if ($showorder != 0) {
$event_balcony=strrev($event_balcony);
for ($z=0;$z<strlen($event_balcony);$z++) {
$balcony[$nofsets+$z]=$event_balcony[$z];
}		
            //$balcony[$nofsets] = 'Y';
           // $balcony[$nofsets + 1] = 'N';
           // $balcony[$nofsets + 2] = 'O';
           // $balcony[$nofsets + 3] = 'C';
           // $balcony[$nofsets + 4] = 'L';
           // $balcony[$nofsets + 5] = 'A';
           // $balcony[$nofsets + 6] = 'B';
        }
        //


//$event_circle=strrev($event_circle);
for ($z=0;$z<strlen($event_circle);$z++) {
$circle[$nofsets+$z]=$event_circle[$z];
}		
        //$circle[$nofsets] = 'C';
        //$circle[$nofsets + 1] = 'I';
       // $circle[$nofsets + 2] = 'R';
       // $circle[$nofsets + 3] = 'C';
        //$circle[$nofsets + 4] = 'L';
        //$circle[$nofsets + 5] = 'E';
        if ($showorder != 0) {


$event_circle=strrev($event_circle);
for ($z=0;$z<strlen($event_circle);$z++) {
$circle[$nofsets+$z]=$event_circle[$z];
}		
            //$circle[$nofsets] = 'E';
           // $circle[$nofsets + 1] = 'L';
           // $circle[$nofsets + 2] = 'C';
           // $circle[$nofsets + 3] = 'R';
           // $circle[$nofsets + 4] = 'I';
            //$circle[$nofsets + 5] = 'C';
        }

        $rowname = $data['row_name'];

        $seatno = $data['seatno'];

        $seatcost = $data['seat_price'];

        $seatdiscost = $data['discount_price'];

        if ($i == 0) {

            if ($rowname == '') {
                $html .= '<div style="float:left;"><ul class="r"><li class="stall showseats">' . $rowname . '</li>';
            } else {
                $html .= '<div style="float:left;"><ul class="r"><li class="ltr">' . $rowname . '</li>';
            }
        }
        if ($nextrow != $rowname && $i != 0) {
            if ($rowname == '') {
                $html .= '<li class="ltr">' . $nextrow . '</li></ul></div><div style="float:left;"><ul class="r"><li class="stall showseats">' . $rowname . '</li>';
            } else {
                if ($nextrow == '') {
                    $html .= '<li class="stall showseats">' . $nextrow . '</li></ul></div><div style="float:left;"><ul class="r"><li class="ltr">' . $rowname . '</li>';
                } else {
                    $html .= '<li class="ltr">' . $nextrow . '</li></ul></div><div style="float:left;"><ul class="r"><li class="ltr">' . $rowname . '</li>';
                }

            }

        }

        $rst_options = get_option(RSTPLN_OPTIONS);

        $dicount = $rst_options['rst_h_disc'];

        if ($dicount != '') {

            $dicount = $seatcost - ($seatcost * ($dicount / 100));

            $dicount = round($dicount, 2);


        } else {

            $dicount = $seatcost;

        }

        $dicount = number_format($dicount, 2, '.', '');

        $seats_avail_per_row = unserialize($data['seats_avail_per_row']);

        $otherscart = false;
		

		$cssclassname="notbooked";
		$cssclassname=apply_filters('row_seats_color_selection_css_name',$cssclassname,$data['seatcolor']);
		

        if ($data['seattype'] == 'N') {

            $html .= '<li class="un showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '_' . $seatno . '" title="Seat ' . $rowname . ($seatno) . ' Unavailable" rel="' . $data['seattype'] . '"></li>';

        } else if ($data['seattype'] == 'Y') {

            $html .= '<li class="'.$cssclassname.' showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '_' . $seatno . '" title="Seat ' . $rowname . ($seatno) . ' Price ' . $symbol . $seatcost . ' Available" rel="' . $data['seattype'] . '">' . ($seatno) . '</li>';

        } else if ($data['seattype'] == 'H') {

            $html .= '<li class="handy showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '_' . $seatno . '" title="Seat ' . $rowname . ($seatno) . ' Discount Price ' . $symbol . $dicount . ' " rel="' . $data['seattype'] . '">' . $seatno . '</li>';

        } else if ($data['seattype'] == 'B') {

            $html .= '<li class="booked showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '_' . $seatno . '" title="Seat ' . $rowname . ($seatno) . ' Booked" rel="' . $data['seattype'] . '">' . $seatno . '</li>';

        } else if ($data['seattype'] == 'T') {

            for ($o = 0; $o < count($rst_bookings); $o++) {

                if ($rst_bookings[$o]['row_name'] == $rowname && $rst_bookings[$o]['seatno'] == $seatno) {

                    $otherscart = true;

                }

            }

            if ($otherscart) {

                $foundcartitems++;

                $html .= '<li class="blocked showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '_' . $seatno . '" title="Seat ' . $rowname . ($seatno) . '" rel="' . $data['seattype'] . '">' . $seatno . '</li>';

            } else {

                $html .= '<li class="b showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '_' . $seatno . '" title="Seat ' . $rowname . ($seatno) . '  Blocked" rel="' . $data['seattype'] . '">' . $seatno . '</li>';

            }

        } else if ($data['seattype'] == 'S')

            $html .= '<li class="s showseats" id="' . $showname . '' . $showid . $rowname . $seatno . '" title="" rel="">' . $stall[$seatno] . '</li>';

        else if ($data['seattype'] == 'L')

            $html .= '<li class="l showseats" id="' . $showname . '' . $showid . $rowname . $seatno . '" title="" rel="">' . $balcony[$seatno] . '</li>';

        else if ($data['seattype'] == 'C')

            $html .= '<li class="c showseats" id="' . $showname . '' . $showid . $rowname . $seatno . '" title="" rel="">' . $circle[$seatno] . '</li>';

        else {

            $html .= '<li class="un showseats" id="' . $showname . '' . $showid . $rowname . $seatno . '_' . $seatno . '" title="" rel=""></li>';

        }

        $nextrow = $rowname;

    }

    if ($foundcartitems == 0) {

        ?>


        <script>
            jQuery.cookie("rst_cart_<?php echo $showid?>", null);
        </script>


    <?php

    }
	
	
    $html .= '<li class="ltr">' . $nextrow . '</li></ul></div>';

	
    $html .= '</div>';
//$html="";
$html = apply_filters('row_seats_generel_admission_block_seatchart', $html);
    // cartitems ----->
$bottomheader="";
if($rst_options['rst_stage_alignment']=="bottom")
{
$bottomheader='<br><br><br><br><div class="stage-hdng" style="width:' . $divwidth . 'px; border:1px solid;border-radius:5px;box-shadow: 5px 5px 2px #888888;clear:both;float:center;" >'.$event_seat_stage.'</div><br>';
}
    $html .= '<div id="gap" style="clear:both;float:left;">&nbsp;</div>'.$bottomheader.'<a NAME="view_cart"></a><div class="cartitems" style="width:' . $divwidth . 'px; border:1px solid;border-radius:5px;box-shadow: 5px 5px 2px #888888;"><div class="cart-hdng"align="center" style="border:0px solid;border-radius:5px;"><strong>'.$event_itemsincart.'</strong> <span style="float:right; width: 48px;"><a href="#show_top"><strong style="vertical-align: middle; float:left; color:#000;">Up</strong><img style="margin: 3px 0 0; float:right;" src="' . RSTPLN_URL . 'images/up.png" alt="Up" title="Up" /></a></span></div><table style="color:#51020b;">';

    if ($rst_bookings != '' && count($rst_bookings) > 0) {

        $total = 0;

        for ($i = 0; $i < count($rst_bookings); $i++) {

            $rst_booking = $rst_bookings[$i];

            $rst_booking['price'] = number_format($rst_booking['price'], 2, '.', '');

            $html .= '<tr><td>'.$event_seat_row.' ' . $rst_booking['row_name'] .' - '.$event_seat.' '. ($rst_booking['seatno']) . ' '.$languages_added.' - </td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>'.$event_item_cost.':' . $symbol . $rst_booking['price'] . '</td><td><img src="' . RSTPLN_URL . 'images/delete.png" class="deleteitem" id="' . $showname . '_' . $showid . '_' . $rst_booking['row_name'] . '_' . ($rst_booking['seatno']) . '" onclick="deleteitem(this);" style="cursor:pointer;border:none!important"/></td></tr>';

            $total = $total + $rst_booking['price'];
        //$html .= var_dump($rst_booking);
        }
		
		
		
	$html = apply_filters('row_seats_seat_restriction_check_filter',$html,$rst_bookings,$showid);			

        $html .= '<tr><td></td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td class="total_price">'.$event_item_total.':' . $symbol . number_format($total, 2, '.', '') . '</td></tr><tr><td><a class="contact rsbutton" href="javascript:void(0);" >'.$event_item_checkout.'</a></td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td><a class="rsbutton" href="javascript:void(0);"  id="' . $sessiondata . '" onclick="deleteitemall(this);">'.$event_item_clearcart.'</a></td></tr></table></div>';

    } else {

        $html .= '<tr><td><b><font size=2>'.$cart_is_empty.'</font></b><img src="' . RSTPLN_URL . 'images/emptycart.png" style="border:none !important;"/></td></tr></table></div>';

    }
    // <----- cartitems

    return $html;

}


/*
 * Includes introduction page to the plugin admin menu
 */
function rst_intro_page()
{
    require_once('inc/inc.info.php');
}


/*
 * Includes settings page to the plugin admin menu
 */
function rst_settings()
{
    require_once('inc/inc.rst-settings.php');
}

function rst_pay_settings()
{
    require_once('inc/inc.rst-pay-settings.php');
}

//function rst_special_price()
//{
  //  require_once('inc/inc.special-price.php');
//}


/*
 * Converts time to the appropriate time format
 */
function convertToHoursMins($time, $format = '%d:%d:%d')
{
    $hours = gmdate("H", $time);
    $minutes = gmdate("i", $time);
    $secs = gmdate("s", $time);
    return sprintf($format, $hours, $minutes, $secs);
}


/*
 * Includes manage seats page to the plugin admin menu
 */
function rst_manage_seats()
{
    require_once('inc/inc.manage-seats.php');
}


/*
 * Returns booking details for the appropriate show
 */
function getbookingdetailbyshow($showid)
{
    $seats = rst_seats_operations('list', '', $showid);
    $totalbooking = 0;
    for ($seat = 0; $seat < count($seats); $seat++) {
        if ($seats[$seat]['seattype'] == 'B') {
            $totalbooking++;
        }
    }
    return $totalbooking;
}


/*
 * Includes reports page to the plugin admin menu
 */
function rst_reports()
{
    require_once('inc/inc.reports.php');
}

function rst_transactions()
{

    require_once('inc/inc.transactions.php');
}
/*
function wpuser_access()
{

    require_once('inc/inc.wpuser.php');
}

function seat_color()
{

    require_once('inc/inc.seatcolor.php');
}
*/


/*
 * Returns list of booked tickets for the appropriate show and date range
 */
function bookedtickets($byshowid, $datefrom, $dateto)
{

    global $wpdb;

    $currentdate = date('Y-m-d');

    $bookedtickets = array();

    $sql = "select * from $wpdb->rst_bookings rstbk,$wpdb->rst_booking_seats_relation bsr,$wpdb->rst_shows rsts

        where (rstbk.payment_status ='ipn_verified' OR rstbk.payment_status ='offline_registration')

        and bsr.booking_id = rstbk.booking_id ";

    if ($byshowid != 0) {

        $sql .= "AND rstbk.show_id=" . $byshowid;

    }

    if ($datefrom != '' && $dateto != '') {

        $sql .= " and rstbk.booking_time between '$datefrom 00:00:00' AND '$dateto 23:59:59'";

    } else if ($datefrom != '') {

        $sql .= " and rstbk.booking_time >='$datefrom'";

    }

    if (($byshowid == '' || $byshowid == '0') && $datefrom == '' && $dateto == '') {

        $sql .= " and rstbk.booking_time between '$currentdate 00:00:00' AND '$currentdate 23:59:59'";

    }

    $sql .= " and rsts.id = bsr.show_id order by rstbk.booking_time desc";
//print $sql;
//exit;
    if ($results = $wpdb->get_results($sql, ARRAY_A)) {

        $bookedtickets = $wpdb->get_results($sql, ARRAY_A);

    }

    return $bookedtickets;

}


function bookedticketsdash()
{

    global $wpdb;

    $currentdate = date('Y-m-d');

    $bookedtickets = array();
	
			if ( is_user_logged_in() ) {
				global $current_user;
				get_currentuserinfo();
				$user_id=$current_user->ID;	
				$user_email=$current_user->user_email;				
			
			} else {			
				$user_id=0;
			}

			

    $sql = "select * from $wpdb->rst_bookings rstbk,$wpdb->rst_booking_seats_relation bsr,$wpdb->rst_shows rsts

        where (rstbk.payment_status ='ipn_verified' OR rstbk.payment_status ='offline_registration')

        and bsr.booking_id = rstbk.booking_id ";

 
        $sql .= " AND (rstbk.user_id=" . $user_id." OR LOWER(rstbk.email)='".strtolower($user_email)."')";


	


    $sql .= " and rsts.id = bsr.show_id order by rstbk.booking_time desc";
//print $sql;
//exit;
    if ($results = $wpdb->get_results($sql, ARRAY_A)) {

        $bookedtickets = $wpdb->get_results($sql, ARRAY_A);

    }

    return $bookedtickets;

}

function bookedticketsnew($byshowid, $datefrom, $dateto,$bybookingid)
{

    global $wpdb;

    $currentdate = date('Y-m-d');

    $bookedtickets = array();

    $sql = "select * from $wpdb->rst_bookings rstbk,$wpdb->rst_booking_seats_relation bsr,$wpdb->rst_shows rsts

        where (rstbk.payment_status ='ipn_verified' OR rstbk.payment_status ='offline_registration')

        and bsr.booking_id = rstbk.booking_id ";

    if ($byshowid != 0) {

        $sql .= " AND rstbk.show_id=" . $byshowid;

    }
    if ($bybookingid) {

        $sql .= " AND rstbk.booking_id=" . $bybookingid;

    }	
	

    if ($datefrom != '' && $dateto != '') {

       // $sql .= " and rstbk.booking_time between '$datefrom 00:00:00' AND '$dateto 23:59:59'";

    } else if ($datefrom != '') {

        $sql .= " and rstbk.booking_time >='$datefrom'";

    }

    if (($byshowid == '' || $byshowid == '0') && $datefrom == '' && $dateto == '') {

       // $sql .= " and rstbk.booking_time between '$currentdate 00:00:00' AND '$currentdate 23:59:59'";

    }

    $sql .= " and rsts.id = bsr.show_id order by rstbk.booking_time desc";
//print $sql;
//exit;
    if ($results = $wpdb->get_results($sql, ARRAY_A)) {

        $bookedtickets = $wpdb->get_results($sql, ARRAY_A);

    }

    return $bookedtickets;

}


function bookedticketssearch($keywords)
{

    global $wpdb;

    $currentdate = date('Y-m-d');

    $bookedtickets = array();

    $sql = "select * from $wpdb->rst_bookings rstbk,$wpdb->rst_booking_seats_relation bsr,$wpdb->rst_shows rsts

        where (rstbk.payment_status ='ipn_verified' OR rstbk.payment_status ='offline_registration')

        and bsr.booking_id = rstbk.booking_id ";
    if($keywords)
	{
	$sql .= " and (show_name like '%".$keywords."%'  or name like '%".$keywords."%'  or email like '%".$keywords."%'   or phone like '%".$keywords."%'   or ticket_seat_no like '%".$keywords."%'   
	          or txn_id like '%".$keywords."%' or c_code like '%".$keywords."%' or status like '%".$keywords."%') ";
	}

    $sql .= " and rsts.id = bsr.show_id order by rstbk.booking_time desc";
	//print $sql;

    if ($results = $wpdb->get_results($sql, ARRAY_A)) {

        $bookedtickets = $wpdb->get_results($sql, ARRAY_A);

    }
  //print_r($bookedtickets);
    return $bookedtickets;

}


/*
 * Generates seat chart html-content that is used on backend
 */
function gettheadminseatchat($showid)
{

global $screenspacing,$wpdb;
    $rst_paypal_options = get_option(RSTPLN_PPOPTIONS);
    $symbol = $rst_paypal_options['currencysymbol'];
	$symbol = get_option('rst_currencysymbol');
    $symbols = array(
        "0" => "$",
        "1" => "&pound;",
        "2" => "&euro;",
        "3" => "&#3647;",
        "4" => "&#8362;",
        "5" => "&yen;",
        "6" => "&#8377;",
        "7" => "R$",
        "8" => "kr",
        "9" => "zł",
        "10" => "Ft",
        "11" => "Kč",
        "12" => "&#1088;&#1091&#1073;",
        "13" => "&#164;",
        "14" => "&#x20B1;",
        "15" => "Fr",
        "16" => "RM");
    $symbol = $symbols[$symbol];

    $rst_options = get_option(RSTPLN_OPTIONS);

    $stylecss = $rst_options['rst_theme'];

    if ($stylecss == '') {

        $stylecss = 'lite.css';

    }

    ?>


    <link rel="stylesheet" type="text/css" media="all" href="<?php echo RSTPLN_CSSURL ?>seats.css"/>


    <?php

    $showid = $showid['id'];

    $data = getshowbyid($showid);
    $showorder = $data[0]['orient'];

    if ($showorder == 0 || $showorder == '') {
        $seats = rst_seats_operations('list', '', $showid);
    } else {
        $seats = rst_seats_operations('reverse', '', $showid);
    }
$seatsize=$wpdb->get_var("SELECT LENGTH( seatno ) AS fieldlength FROM rst_seats where show_id=".$showid." ORDER BY fieldlength DESC LIMIT 1 ");
print "<br>15-".$seatsize;
if($seatsize>2)
{
$seatsize=5*($seatsize-2);
}else
{
$seatsize=0;
}
    $divwidth = (($seats[0]['total_seats_per_row']) + 2) * (24+$seatsize);
    $divwidth=$divwidth * $rst_options['rst_zoom'];
	

	
    $mindivwidth = 640;
    if ($divwidth < $mindivwidth) {
        $divwidth = $mindivwidth;
    }	
	if($rst_options['rst_fixed_width'])
	{
	$divwidth =$rst_options['rst_fixed_width'];
	}	
	
	
    $showname = $data[0]['show_name'];
	
$seatsize=$wpdb->get_var("SELECT LENGTH( seatno ) AS fieldlength FROM rst_seats where show_id=".$showid." ORDER BY fieldlength DESC LIMIT 1 ");
print "<br>2--------".$seatsize;
if($seatsize>2)
{
$seatsize=5*($seatsize-2);
}else
{
$seatsize=0;
}

	
    $html='<style>
ul.r li {
    font-size:'. (int)(10 * $rst_options['rst_zoom']).'px !important;
    height:'. (int)(24 * $rst_options['rst_zoom']).'px !important;
    line-height:'.(int)(24 * $rst_options['rst_zoom']).'>px !important;
    width:'.(int)((21+$seatsize) * $rst_options['rst_zoom']).'px !important;
}
</style>'; 
   // $html = '';

    $html .= "<h2>" . __('Preview of the Show:' . $showname, 'rst') . "</h2>";

    $html .= apply_filters('pricing_per_seat_message_filter', '');

    $html .= '<div id="currentcart" style="width:600px;"><span class="notbooked showseats" >&nbsp;&nbsp;&nbsp;&nbsp;</span> Available &nbsp;&nbsp;

        <span class="blocked showseats" >&nbsp;&nbsp;&nbsp;&nbsp;</span> In Your Cart &nbsp;&nbsp;

        <span class="booked showseats" >&nbsp;&nbsp;&nbsp;&nbsp;</span> Booked &nbsp;&nbsp;

        <span class="un showseats" >&nbsp;&nbsp;&nbsp;&nbsp;</span> In Other&#39;s Cart &nbsp;&nbsp;

        <span class="handy showseats" >&nbsp;&nbsp;&nbsp;&nbsp;</span> Wheelchair Access&nbsp;&nbsp;<br/><br/>

        <div id="stageshow"></div></div><div class="clear"></div>';

    $html .= '<div class="seatplan" id="showid_' . $showid . '" style="width:' . $divwidth . 'px;" >';

    $nextrow = '';
    //  echo '<pre>';
//print_r($seats);

    for ($i = 0; $i < count($seats); $i++) {

        $data = $seats[$i];
        $nofsets = $data['total_seats_per_row'];
        $nofsets = floor($nofsets / 2);
        $stall[$nofsets] = 'S';
        $stall[$nofsets + 1] = 'T';
        $stall[$nofsets + 2] = 'A';
        $stall[$nofsets + 3] = 'L';
        $stall[$nofsets + 4] = 'L';
        ///
        if ($showorder != 0) {
            $stall[$nofsets] = 'L';
            $stall[$nofsets + 1] = 'L';
            $stall[$nofsets + 2] = 'A';
            $stall[$nofsets + 3] = 'T';
            $stall[$nofsets + 4] = 'S';
        }

        $balcony[$nofsets] = 'B';
        $balcony[$nofsets + 1] = 'A';
        $balcony[$nofsets + 2] = 'L';
        $balcony[$nofsets + 3] = 'C';
        $balcony[$nofsets + 4] = 'O';
        $balcony[$nofsets + 5] = 'N';
        $balcony[$nofsets + 6] = 'Y';
        if ($showorder != 0) {
            $balcony[$nofsets] = 'Y';
            $balcony[$nofsets + 1] = 'N';
            $balcony[$nofsets + 2] = 'O';
            $balcony[$nofsets + 3] = 'C';
            $balcony[$nofsets + 4] = 'L';
            $balcony[$nofsets + 5] = 'A';
            $balcony[$nofsets + 6] = 'B';
        }
        //
        $circle[$nofsets] = 'C';
        $circle[$nofsets + 1] = 'I';
        $circle[$nofsets + 2] = 'R';
        $circle[$nofsets + 3] = 'C';
        $circle[$nofsets + 4] = 'L';
        $circle[$nofsets + 5] = 'E';
        if ($showorder != 0) {
            $circle[$nofsets] = 'E';
            $circle[$nofsets + 1] = 'L';
            $circle[$nofsets + 2] = 'C';
            $circle[$nofsets + 3] = 'R';
            $circle[$nofsets + 4] = 'I';
            $circle[$nofsets + 5] = 'C';
        }

        $rowname = $data['row_name'];

        $seatno = $data['seatno'];

        $seatcost = $data['seat_price'];

        $seatdiscost = $data['discount_price'];
        if ($i == 0) {
            if ($rowname == '') {
                $html .= '<div style="float:left;"><ul class="r"><li class="stall showseats">' . $rowname . '</li>';
            } else {
                $html .= '<div style="float:left;"><ul class="r"><li class="ltr">' . $rowname . '</li>';
            }

        }
        if ($nextrow != $rowname && $i != 0) {
            if ($rowname == '') {
                $html .= '<li class="ltr">' . $nextrow . '</li></ul></div><div style="float:left;"><ul class="r"><li class="stall showseats">' . $rowname . '</li>';
            } else {
                if ($nextrow == '') {
                    $html .= '<li class="stall showseats">' . $nextrow . '</li></ul></div><div style="float:left;"><ul class="r"><li class="ltr">' . $rowname . '</li>';
                } else {
                    $html .= '<li class="ltr">' . $nextrow . '</li></ul></div><div style="float:left;"><ul class="r"><li class="ltr">' . $rowname . '</li>';
                }

            }

        }

        $rst_options = get_option(RSTPLN_OPTIONS);

        $dicount = $rst_options['rst_h_disc'];

        if ($dicount != '') {

            $dicount = $seatcost - ($seatcost * ($dicount / 100));

            $dicount = round($dicount, 2);


        } else {

            $dicount = $seatcost;

        }

        $dicount = number_format($dicount, 2, '.', '');


        if ($data['seattype'] == 'N')

            $html .= '<li class="un showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '" title="Seat ' . $rowname . ($seatno) . ' Unavailable" rel="' . $seats_avail_per_row[$k] . '"></li>';

        else if ($data['seattype'] == 'Y')

            $html .= '<li class="notbooked showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '" title="Seat ' . $rowname . ($seatno) . ' Price ' . $symbol . $seatcost . ' Available" data-seat-row="'.$rowname.'" data-seat-no="'.$seatno.'" data-price="' . $seatcost . '" rel="' . $data['seattype'] . '">' . ($seatno) . '</li>';

        else if ($data['seattype'] == 'H')

            $html .= '<li class="handy showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '" title="Seat ' . $rowname . ($seatno) . ' Price ' . $symbol . $seatcost . ' Discount Price '.$symbol.$dicount.'" data-seat-row="'.$rowname.'" data-seat-no="'.$seatno.'" data-price="' . $seatcost . '" rel="' . $data['seattype'] . '">' . ($seatno) . '</li>';

        else if ($data['seattype'] == 'B')

            $html .= '<li class="booked showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '" title="Seat ' . $rowname . ($seatno) . '  Booked" rel="' . $seats_avail_per_row[$k] . '">' . ($seatno) . '</li>';

        else if ($data['seattype'] == 'T')

            $html .= '<li class="blocked showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '" title="Seat ' . $rowname . ($seatno) . '  Booked" rel="' . $seats_avail_per_row[$k] . '">' . ($seatno) . '</li>';

        else if ($data['seattype'] == 'S')

            $html .= '<li class="s showseats" id="' . $showname . '' . $showid . $rowname . $seatno . '" title="" rel="">' . $stall[$seatno] . '</li>';

        else if ($data['seattype'] == 'L')

            $html .= '<li class="l showseats" id="' . $showname . '' . $showid . $rowname . $seatno . '" title="" rel="">' . $balcony[$seatno] . '</li>';

        else if ($data['seattype'] == 'C')

            $html .= '<li class="c showseats" id="' . $showname . '' . $showid . $rowname . $seatno . '" title="" rel="">' . $circle[$seatno] . '</li>';

        else

            $html .= '<li class="un showseats" id="' . $showname . '' . $showid . $rowname . $seatno . '" title="" rel=""></li>';

        $nextrow = $rowname;

    }

    $html .= '<li class="ltr">' . $nextrow . '</li></ul></div>';

// Pricing per seat -----
    $currenturl = curPageURL();
    $html .= '
<input type="hidden" name="redirecturl" id="redirecturl" value="' . $currenturl . '"/>

<script type="text/javascript">
var pricing_per_seat_showid="' . $showid . '";
var pricing_per_seat_rstajaxurl="' . RSTAJAXURL . '";
</script>
    ';
// ----- Pricing per seat

    return $html;

}


/*
 * Includes month calendar page to the plugin admin menu
 */
function rst_manage_seats_moncalender()
{
    require_once('inc/inc.fullcalendar.php');
}


/*
 * Set session params for the show
 */
function rst_shows_set_session($data, $action, $currentcart)
{
    if ($action == 'add') {

        $bookingadded = '';

        $currentbooking = array();

        if (isset($currentcart) && $currentcart != null) {

            $currentcart = base64_decode($currentcart);

            $currentbooking = unserialize($currentcart);

        }

        if (count($data) > 0) {

            $currentbooking[] = $data[0];

            rst_session_operations('blocktheseat', $data[0]);

        }

        return $currentbooking;

    } else {

        $showid = $data[0]['show_id'];

        $row = $data[0]['row_name'];

        $seat = $data[0]['seatno'];

        rst_session_operations('deletebookingseat', $data[0]);

        $currentcart = base64_decode($currentcart);

        $currentcart = unserialize($currentcart);

        $finalbookings = array();

        for ($i = 0; $i < count($currentcart); $i++) {

            if ($currentcart[$i]['show_id'] == $showid && $currentcart[$i]['row_name'] == $row && $currentcart[$i]['seatno'] == $seat) {

            } else {

                $finalbookings[] = $currentcart[$i];

            }

        }

        return $finalbookings;

    }
}


/*
 * Creates operations with session params
 */
function  rst_session_operations($action, $data)
{
    global $wpdb;

    switch ($action) {

        case 'blocktheseat':

            $showid = $data['show_id'];

            $rowname = $data['row_name'];

            $seatno = $data['seatno'];

            $price = $data['price'];

            $sesid = session_id();

            $wpdb->query("INSERT INTO $wpdb->rst_customer_session (rst_session_id,show_id,rowname,seatno,price,session_time,status) VALUES ('$sesid', $showid, '$rowname','$seatno',$price,now(),'blocked')");

            return true;

            break;

        case 'deletebookingseat':

            $showid = $data['show_id'];

            $sessiontime = date('Y-m-d H:i:s');

            $rowname = $data['row_name'];

            $seatno = $data['seatno'];

            $sesid = session_id();

            $wpdb->query("DELETE FROM $wpdb->rst_customer_session WHERE rst_session_id='$sesid' AND show_id = $showid AND  rowname='$rowname' AND seatno='$seatno'");

            return true;

            break;

        default:

            break;

    }
}


/*
 * Makes offline reservation
 */
function rst_offline_registration()
{
    if (isset($_POST)) {
        $bookid = explode('__', $_POST['bookingid']);
        $freeseats = $_POST['freeseats'];
        if ($freeseats == '') {
            $freeseats = 0;
        }
        $data['custom'] = $_POST['bookingid'];

        $data['txn_id'] = 'Offline Reservation';

        $cartitems = $_POST['cartiterms'];
        $cartitems = base64_decode($cartitems);
        $cartitems = unserialize($cartitems);
        $total = 0;
        for ($i = 0; $i < count($cartitems); $i++) {

            $total = $total + $cartitems[$i]['price'];
        }
        if (count($bookid) == 3) {
            $total = $total - $bookid[2];
        }
        $data['mc_gross'] = $total;

        rst_ipncall($data);

    }

    die;
}


/*
 * Adds payment information of the order to the database and sends emails to the customer and to the admin
 */
function rst_ipncall($data)
{
    global $wpdb;

    $bookingdetails = $data['custom'];
    //print "<br>Booking details=".$bookingdetails;
    $bookingdetails = explode("__", $bookingdetails);

    $booking_id = isset($bookingdetails[0]) ? $bookingdetails[0] : $data['custom'];

    $c_code = isset($bookingdetails[1]) ? $bookingdetails[1] : '';

    $c_discount = isset($bookingdetails[2]) ? $bookingdetails[2] : 0;
    $freeseatsavailed = isset($bookingdetails[3]) ? $bookingdetails[3] : 0;
    $rstfees = isset($bookingdetails[4]) ? $bookingdetails[4] : 0;
    if ($freeseatsavailed != 0) {
        $data1['seatsavailed'] = $freeseatsavailed;
        $data1['memid'] = $c_code;
        rst_member_operations('updateavail', $data1);
    }

    $rst_options = get_option(RSTPLN_OPTIONS);

    $rst_options['rst_ticket_prefix'];

    $ticketno = $rst_options['rst_ticket_prefix'] . $booking_id;

    $paypalvars = array();

    $txn_id = '';

    $totalpaid = 0;

    foreach ($data as $key => $value) {

        if ($key == 'txn_id') {

            $txn_id = $value;

        }

        if ($key == 'mc_gross') {

            $totalpaid = $value;

        }

    }
    $txn_id1 = $txn_id;
    $totalpaid1 = $totalpaid;
    $paypal_vars = print_r($data, true);

    $wpdb->query("UPDATE  $wpdb->rst_bookings SET paypal_vars='$paypal_vars',payment_status='ipn_verified',ticket_no='$ticketno',c_code='$c_code',c_discount=$c_discount,fees=$rstfees WHERE booking_id=" . $booking_id);
$customfield_query= apply_filters('row_seats_custom_field_query',$booking_id);
    $sql = "SELECT * FROM $wpdb->rst_bookings where booking_id=" . $booking_id;

    if ($results = $wpdb->get_results($sql, ARRAY_A)) {

        $booking_details = $wpdb->get_results($sql, ARRAY_A);

        $data = $booking_details[0];

        $booking_details = $booking_details[0]['booking_details'];

        $booking_details = unserialize($booking_details);
		//print_r($booking_details);

        for ($row = 0; $row < count($booking_details); $row++) {

            $seats = $booking_details[$row]['seatno'];

            $showid = $booking_details[$row]['show_id'];

            $rowname = $booking_details[$row]['row_name'];

            $price = $booking_details[$row]['price'];

            $sql = "SELECT * FROM $wpdb->rst_seats st,$wpdb->rst_shows sh

                    WHERE

                    sh.id=st.show_id AND

                    st.row_name = '$rowname' AND

                    st.seatno = '$seats' AND

                    st.seattype <>'' AND

                    sh.id =" . $showid;

            $seatdatatoupdate = $wpdb->get_results($sql, ARRAY_A);

            $seatdata = $seatdatatoupdate[0];

            $seatid = $seatdata['seatid'];

            if ($seatdata['seattype'] == 'T') {

                $wpdb->query("UPDATE  $wpdb->rst_seats SET seattype='B',status='paid' WHERE show_id=" . $showid . " AND row_name='$rowname' AND seatid=" . $seatid);

            }

            if ($row < $freeseatsavailed) {
                $txn_id = 'Free Booking';
                $totalpaid = 0;
            } else {
                $txn_id = $txn_id1;
                $totalpaid = $totalpaid1;
            }
            if ($txn_id == 'Free Booking') {
                $totalpaid = 0;
                $txn_id = base64_encode('Free Booking-' . $ticketno . $showid);
            }
            if ($txn_id == 'Offline Reservation') {
                $txn_id = base64_encode('Offline Reservation-' . $ticketno . $showid);
            }
            $ticket_seat_no = $ticketno . '-' . $rowname . $seats;

            $sql = "INSERT INTO $wpdb->rst_booking_seats_relation (ticket_no,ticket_seat_no,booking_id,show_id,b_seatid,total_paid,txn_id,seat_cost,booking_status,comments)

            VALUES ('$ticketno', '$ticket_seat_no', $booking_id,$showid,$seatid,$totalpaid,'$txn_id',$price,'','')";
            //print "<br>".$sql;
			//exit;
            $wpdb->query($sql);

        }

        $sql = "select * from $wpdb->rst_bookings rstbk,$wpdb->rst_booking_seats_relation bsr,$wpdb->rst_shows rsts

        where (rstbk.payment_status ='ipn_verified' OR rstbk.payment_status ='offline_registration')

        and bsr.booking_id = rstbk.booking_id

        and rsts.id = bsr.show_id

        and bsr.booking_id =" . $booking_id;

        if ($results = $wpdb->get_results($sql, ARRAY_A)) {

            $booking_details = $wpdb->get_results($sql, ARRAY_A);

            $data = $booking_details;

            sendrstmail($data, $txn_id);

        }

    }

    //exit();

}

function complete_offline_registration($data)
{




}

function rst_bookseatsfinal($data)
{
    global $wpdb;
    $sendemail = $data['sendemail'];  
    $bookingdetails = $data['custom'];
    //print "<br>Booking details=".$bookingdetails;
    $bookingdetails = explode("__", $bookingdetails);

    $booking_id = isset($bookingdetails[0]) ? $bookingdetails[0] : $data['custom'];

    $c_code = isset($bookingdetails[1]) ? $bookingdetails[1] : '';

    $c_discount = isset($bookingdetails[2]) ? $bookingdetails[2] : 0;
    $freeseatsavailed = isset($bookingdetails[3]) ? $bookingdetails[3] : 0;
    $rstfees = isset($bookingdetails[4]) ? $bookingdetails[4] : 0;
    if ($freeseatsavailed != 0) {
        $data1['seatsavailed'] = $freeseatsavailed;
        $data1['memid'] = $c_code;
        rst_member_operations('updateavail', $data1);
    }

    $rst_options = get_option(RSTPLN_OPTIONS);

    $rst_options['rst_ticket_prefix'];

    $ticketno = $rst_options['rst_ticket_prefix'] . $booking_id;

    $paypalvars = array();

    $txn_id = '';

    $totalpaid = 0;

    foreach ($data as $key => $value) {

        if ($key == 'txn_id') {

            $txn_id = $value;

        }

        if ($key == 'mc_gross') {

            $totalpaid = $value;

        }

    }
    $txn_id1 = $txn_id;
    $totalpaid1 = $totalpaid;
    $paypal_vars = print_r($data, true);

    $wpdb->query("UPDATE  $wpdb->rst_bookings SET paypal_vars='$paypal_vars',payment_status='ipn_verified',ticket_no='$ticketno',c_code='$c_code',c_discount=$c_discount,fees=$rstfees WHERE booking_id=" . $booking_id);
//$customfield_query= apply_filters('row_seats_custom_field_query',$booking_id);
    $sql = "SELECT * FROM $wpdb->rst_bookings where booking_id=" . $booking_id;

    if ($results = $wpdb->get_results($sql, ARRAY_A)) {

        $booking_details = $wpdb->get_results($sql, ARRAY_A);

        $data = $booking_details[0];

        $booking_details = $booking_details[0]['booking_details'];

        $booking_details = unserialize($booking_details);
		
if($booking_details['customfield'])
{
unset($booking_details['customfield']);
}

		
		//print_r($booking_details);
        $mytickets=array();
		$seatcosts=array();
        for ($row = 0; $row < count($booking_details); $row++) {

            $seats = $booking_details[$row]['seatno'];

            $showid = $booking_details[$row]['show_id'];

            $rowname = $booking_details[$row]['row_name'];

            $price = $booking_details[$row]['price'];

            $sql = "SELECT * FROM $wpdb->rst_seats st,$wpdb->rst_shows sh

                    WHERE

                    sh.id=st.show_id AND

                    st.row_name = '$rowname' AND

                    st.seatno = '$seats' AND

                    st.seattype <>'' AND

                    sh.id =" . $showid;

            $seatdatatoupdate = $wpdb->get_results($sql, ARRAY_A);

            $seatdata = $seatdatatoupdate[0];

            $seatid = $seatdata['seatid'];

            if ($seatdata['seattype'] == 'T' || $seatdata['seattype'] == 'Y') {

                $wpdb->query("UPDATE  $wpdb->rst_seats SET seattype='B',status='paid' WHERE show_id=" . $showid . " AND row_name='$rowname' AND seatid=" . $seatid);

            }

            if ($row < $freeseatsavailed) {
                $txn_id = 'Free Booking';
                $totalpaid = 0;
            } else {
                $txn_id = $txn_id1;
                $totalpaid = $totalpaid1;
            }
            if ($txn_id == 'Free Booking') {
                $totalpaid = 0;
                $txn_id = base64_encode('Free Booking-' . $ticketno . $showid);
            }
            if ($txn_id == 'Offline Reservation') {
                $txn_id = base64_encode('Offline Reservation-' . $ticketno . $showid);
            }
			$txn_id = $txn_id1;
            $ticket_seat_no = $ticketno . '-' . $rowname . $seats;
            $mytickets[]=$ticket_seat_no;
			$seatcosts[]=$ticket_seat_no.":".$price;
			
			$sqlexist="select * from $wpdb->rst_booking_seats_relation where ticket_no='$ticketno' and  ticket_seat_no='$ticket_seat_no' and booking_id=$booking_id and show_id=$showid and b_seatid=$seatid";
			$sqlexist_details = $wpdb->get_results($sqlexist, ARRAY_A);
			if(count($sqlexist_details)==0)
			{
            $sql = "INSERT INTO $wpdb->rst_booking_seats_relation (ticket_no,ticket_seat_no,booking_id,show_id,b_seatid,total_paid,txn_id,seat_cost,booking_status,comments)

            VALUES ('$ticketno', '$ticket_seat_no', $booking_id,$showid,$seatid,$totalpaid,'$txn_id',$price,'','')";
            $wpdb->query($sql);
			}

        }
         $myticketsstring=implode(",",$mytickets) ;
		 $seatcoststring=implode(",",$seatcosts) ;
		$_SESSION['mybookingsess']= $booking_id;
        $sql = "select * from $wpdb->rst_bookings rstbk,$wpdb->rst_booking_seats_relation bsr,$wpdb->rst_shows rsts

        where (rstbk.payment_status ='ipn_verified' OR rstbk.payment_status ='offline_registration')

        and bsr.booking_id = rstbk.booking_id

        and rsts.id = bsr.show_id

        and bsr.booking_id =" . $booking_id;

        if ($results = $wpdb->get_results($sql, ARRAY_A)) {

            $booking_details = $wpdb->get_results($sql, ARRAY_A);
			
			$show_name=$booking_details[0]['show_name'];
			$show_date=$booking_details[0]['show_date'];
			$wpdb->query("UPDATE  rst_payment_transactions SET show_name='$show_name',show_date='$show_date',ticket_no='$ticketno',coupon_code='$c_code',coupon_discount='$c_discount',special_fee='$rstfees',seat_numbers='$myticketsstring', seat_cost='$seatcoststring' WHERE tx_str=" . $booking_id);

            $data = $booking_details;
			if($sendemail!="no")
			{
            sendrstmail($data, $txn_id);
			}

        }

    }

    //exit();

}



/*
 * Sends emails to the customer and to the admin
 */
function sendrstmail($data, $txn_id)
{
//print_r($data);
    $rst_options = get_option(RSTPLN_OPTIONS);
    $stopadminemails = $rst_options['rst_disable_admin_email'];
    $rst_options = get_option(RSTPLN_OPTIONS);
    $rst_paypal_options = get_option(RSTPLN_PPOPTIONS);

    $symbol = $rst_paypal_options['currencysymbol'];
	
$symbol = get_option('rst_currencysymbol');
    $symbols = array(
        "0" => "$",
        "1" => "&pound;",
        "2" => "&euro;",
        "3" => "&#3647;",
        "4" => "&#8362;",
        "5" => "&yen;",
        "6" => "&#8377;",
        "7" => "R$",
        "8" => "kr",
        "9" => "zł",
        "10" => "Ft",
        "11" => "Kč",
        "12" => "&#1088;&#1091&#1073;",
        "13" => "&#164;",
        "14" => "&#x20B1;",
        "15" => "Fr",
        "16" => "RM");
    $symbol = $symbols[$symbol];
    $useremailtemp = $rst_options['rst_etemp'];
	$useremailtemp = html_entity_decode(stripslashes(apply_filters( 'the_content', $useremailtemp )));
	
//$wplanguagesoptions = get_option(RSTLANGUAGES_OPTIONS);
//if($wplanguagesoptions['rst_enable_languages']=="on" && $wplanguagesoptions['languages_event_user_email_template'])
//{
//$useremailtemp=$wplanguagesoptions['languages_event_user_email_template'];
//}	

    $adminemailtemp = $rst_options['rst_adminetemp'];
	$adminemailtemp = html_entity_decode(stripslashes(apply_filters( 'the_content', $adminemailtemp )));

    $search = array("<username>", "<showname>", "<showdate>", "<bookedseats>", "<downloadlink>", "<showtime>","[username]", "[showname]", "[showdate]", "[bookedseats]", "[downloadlink]", "[showtime]");
	
   // $downloadlink = RSTTICKETDOWNURL . '?id=' . $txn_id;
   $txn_id_enc=base64_encode($txn_id);
	$downloadlink = get_bloginfo("wpurl"). '/?mybookingticket=' . $txn_id_enc;
    $dlink = 'Please click <a href="' . $downloadlink . '">here</a> to download your tickets';
    $adminsearch = array("<blogname>", "<username>", "<showname>", "<showdate>", "<bookedseats>", "<availableseats>", "<showtime>", "<totalshowcount>", "<totalbookedcount>","[blogname]", "[username]", "[showname]", "[showdate]", "[bookedseats]", "[availableseats]", "[showtime]", "[totalshowcount]", "[totalbookedcount]");
    $adminsearch=apply_filters('row_seats_custom_field_shortcode_key',$adminsearch);
    $showid = $data[0]['show_id'];

    $availableseats = getavailableseatsbyshow($showid);
	$bookedseats = getbookedseatsbyshow($showid);
	$totalseats = gettotalseatsbyshow($showid);

    $username = $data[0]['name'];

    $useremail = $data[0]['email'];

    $showdate = $data[0]['show_date'];
	$show_start_time = $data[0]['show_start_time'];
    $showdate = date('F j, Y', strtotime($showdate));
    $showtime = date('jS \of F Y h:i:s A', strtotime($show_start_time));

    $showname = $data[0]['show_name'];

    $seatdetails = '';

    for ($i = 0; $i < count($data); $i++) {

        $seatdetails .= $data[$i]['ticket_seat_no'] . ' - ' . $symbol . $data[$i]['seat_cost'] . '<br/>';

    }

    $replace = array($username, $showname, $showdate, $seatdetails, $dlink,$showtime,$username, $showname, $showdate, $seatdetails, $dlink,$showtime);

    $blogname = get_option('blogname');

    $adminreplace = array($blogname, $username, $showname, $showdate, $seatdetails, $availableseats,$showtime,$totalseats,$bookedseats,$blogname, $username, $showname, $showdate, $seatdetails, $availableseats,$showtime,$totalseats,$bookedseats);
    $adminreplace=apply_filters('row_seats_custom_field_shortcode_value',$adminreplace,$data[0]['booking_id']); 
    $mailBodyText = str_replace($search, $replace, $useremailtemp);

    $mailBodyTextadmin = str_replace($adminsearch, $adminreplace, $adminemailtemp);

    $rst_options = get_option(RSTPLN_OPTIONS);

    $fromAddr = $rst_options['rst_email'];
    $esub = $rst_options['rst_esub'];
    $offlineesub = $rst_options['rst_off_esub'];

    if ($fromAddr == '') {

        $fromAddr = get_option('admin_email');

    }

    // the address to show in From field.

    $recipientAddr = $useremail;
	
						foreach($data as $key=>$value)
				{
				$mailstring.="<br>".$key."=".$value;
				}	
				$mailstring.="<br>GET varaibles<br>";
				foreach($_GET as $key=>$value)
				{
				$mailstring.="<br>".$key."=".$value;
				}	
				$mailstring.="<br>To email address=".$recipientAddr;
	
	
    $headers  = "From: $from\r\n";
    $headers .= "Content-type: text/html\r\n";

    //mail($to, $subject, $mailstring, $headers);			

//print $mailstring."<br><br><br>";
    if ($txn_id == 'Offline Reservation' && $offlineesub != '')
        $subjectStr = $offlineesub;
    else if ($esub != '')
        $subjectStr = $esub;
    else
        $subjectStr = 'Bookings';

    $recipientAddradmin = $fromAddr;

    $subjectStradmin = get_option('blogname') . ' Bookings';

    $filePath = $attachments[$i];

    $fileName = basename($filePath);

    $fileType = 'pdf/pdf';
    $headers = "From: $recipientAddradmin\r\n";
    $headers .= "Content-type: text/html\r\n";

    //mail($recipientAddr, $subjectStr, $mailBodyText, $headers); // commented to enable default wp_mail
	add_filter( 'wp_mail_content_type', 'set_html_content_type' );
    wp_mail($recipientAddr, $subjectStr, $mailBodyText, $headers); 

    if ($stopadminemails != 'off') {
        //mail($recipientAddradmin, $subjectStradmin, $mailBodyTextadmin, $headers);// commented to enable default wp_mail
		wp_mail($recipientAddradmin, $subjectStradmin, $mailBodyTextadmin, $headers); 
		$email_copy = $rst_options['rst_email_copy'];
		
		if($email_copy)
		{
			$email_copy_array=explode(",",$email_copy);
			if(count($email_copy_array)>0)
			{
			   foreach($email_copy_array as $ademail)
			   {
					if (is_email($ademail, true) )
					{
					wp_mail($ademail, $subjectStradmin, $mailBodyTextadmin, $headers); 
					
					}
			   }
			}
		}

    }
	remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
}
function set_html_content_type() {

	return 'text/html';
}







function displaybookingdetails($booking_id)
{
global $wpdb;
//print_r($_SESSION);
//print "<br>";
//print_r($_COOKIE);
//$_SESSION['mybookingsess']=212;
//if($_COOKIE['mybookingcook'])
//{
//$booking_id=$_COOKIE['mybookingcook'];
if($_REQUEST['item_number'])
{
$_SESSION['mybookingsess']=$_REQUEST['item_number'];

}


if($_SESSION['mybookingsess'])
{
$booking_id=$_SESSION['mybookingsess'];


        $sql = "select * from $wpdb->rst_bookings rstbk,$wpdb->rst_booking_seats_relation bsr,$wpdb->rst_shows rsts

        where (rstbk.payment_status ='ipn_verified' OR rstbk.payment_status ='offline_registration')

        and bsr.booking_id = rstbk.booking_id

        and rsts.id = bsr.show_id

        and bsr.booking_id =" . $booking_id;

        if ($results = $wpdb->get_results($sql, ARRAY_A)) {

            $booking_details = $wpdb->get_results($sql, ARRAY_A);			

            $data = $booking_details;


        //}
		
		$txn_id="TXYN".$booking_id;
		
		$tsql = "SELECT * FROM rst_payment_transactions WHERE tx_str='".$booking_id."'";
		$trows = $wpdb->get_results($tsql, ARRAY_A);
		$trows=$trows[0];
		//print_r($trows);
		$pstatus=$trows['payment_status'];
		$gtotal=$trows['gross'];
		
		
		
		

		
//print_r($data);
    $rst_options = get_option(RSTPLN_OPTIONS);
    $stopadminemails = $rst_options['rst_disable_admin_email'];
    $rst_options = get_option(RSTPLN_OPTIONS);
    $rst_paypal_options = get_option(RSTPLN_PPOPTIONS);

    $symbol = $rst_paypal_options['currencysymbol'];
	
$symbol = get_option('rst_currencysymbol');
    $symbols = array(
        "0" => "$",
        "1" => "&pound;",
        "2" => "&euro;",
        "3" => "&#3647;",
        "4" => "&#8362;",
        "5" => "&yen;",
        "6" => "&#8377;",
        "7" => "R$",
        "8" => "kr",
        "9" => "zł",
        "10" => "Ft",
        "11" => "Kč",
        "12" => "&#1088;&#1091&#1073;",
        "13" => "&#164;",
        "14" => "&#x20B1;",
        "15" => "Fr",
        "16" => "RM");
    $symbol = $symbols[$symbol];
    $useremailtemp = $rst_options['rst_etemp'];
	$useremailtemp = html_entity_decode(stripslashes(apply_filters( 'the_content', $useremailtemp )));
	
//$wplanguagesoptions = get_option(RSTLANGUAGES_OPTIONS);
//if($wplanguagesoptions['rst_enable_languages']=="on" && $wplanguagesoptions['languages_event_user_email_template'])
//{
//$useremailtemp=$wplanguagesoptions['languages_event_user_email_template'];
//}	

    $adminemailtemp = $rst_options['rst_adminetemp'];
	$adminemailtemp = html_entity_decode(stripslashes(apply_filters( 'the_content', $adminemailtemp )));

    $search = array("<username>", "<showname>", "<showdate>", "<bookedseats>", "<downloadlink>", "<showtime>","[username]", "[showname]", "[showdate]", "[bookedseats]", "[downloadlink]", "[showtime]");
	
   // $downloadlink = RSTTICKETDOWNURL . '?id=' . $txn_id;
   $txn_id_enc=base64_encode($txn_id);
	$downloadlink = get_bloginfo("wpurl"). '/?mybookingticket=' . $txn_id_enc;
    $dlink = 'Please click <a href="' . $downloadlink . '">here</a> to download your tickets';
    $adminsearch = array("<blogname>", "<username>", "<showname>", "<showdate>", "<bookedseats>", "<availableseats>", "<showtime>","[blogname]", "[username]", "[showname]", "[showdate]", "[bookedseats]", "[availableseats]", "[showtime]");

    $showid = $data[0]['show_id'];

    $availableseats = getavailableseatsbyshow($showid);

    $username = $data[0]['name'];

    $useremail = $data[0]['email'];

    $showdate = $data[0]['show_date'];
	$show_start_time = $data[0]['show_start_time'];
    $showdate = date('F j, Y', strtotime($showdate));
    $showtime = date('jS \of F Y h:i:s A', strtotime($show_start_time));

    $showname = $data[0]['show_name'];

    $seatdetails = '';

    for ($i = 0; $i < count($data); $i++) {

        $seatdetails .= $data[$i]['ticket_seat_no'] . ' - ' . $symbol . $data[$i]['seat_cost'] . '<br/>';

    }
if($pstatus=='Pending')		
{	
	
						$tags1 = array('{payer_name}', '{payer_email}', '{payment_status}', '{show_name}', '{show_date}', '{seats}','{amount}');
						$vals1 = array($username, $username, $pstatus,$showname ,$showdate,$seatdetails,$gtotal );
						$pemailbody = html_entity_decode(stripslashes(apply_filters( 'the_content', get_option('rst_pending_email_body') )));
						$body = str_replace($tags1, $vals1, $pemailbody);
						//$mail_headers = "Content-Type: text/plain; charset=utf-8\r\n";
						//$mail_headers .= "From: ".get_option('rst_from_name')." <".get_option('rst_from_email').">\r\n";
						//$mail_headers .= "X-Mailer: PHP/".phpversion()."\r\n";
						//wp_mail(/*$email*/$payer_offlinepayment, get_option('rst_pending_email_subject'), $body, $mail_headers);
print $body;						
}else
{
    $replace = array($username, $showname, $showdate, $seatdetails, $dlink,$showtime,$username, $showname, $showdate, $seatdetails, $dlink,$showtime);

    $blogname = get_option('blogname');

    $adminreplace = array($blogname, $username, $showname, $showdate, $seatdetails, $availableseats,$showtime,$blogname, $username, $showname, $showdate, $seatdetails, $availableseats,$showtime);

    $mailBodyText = str_replace($search, $replace, $useremailtemp);

    $mailBodyTextadmin = str_replace($adminsearch, $adminreplace, $adminemailtemp);

    $rst_options = get_option(RSTPLN_OPTIONS);

	print $mailBodyText;
	
	}
	unset($_SESSION['mybookingsess']);	
}
}
}




















/*
 * Returns list of seats which can be booked
 */
function getavailableseatsbyshow($showid)
{
    global $wpdb;

    $sql = "select count(*) as total from $wpdb->rst_shows gs,$wpdb->rst_seats gse
            WHERE gs.id = gse.show_id
            AND gse.seattype != ''
            AND gse.seattype != 'B'
            AND gs.id =" . $showid;

    $availseats = 0;

    $bookingdata = $wpdb->get_results($sql, ARRAY_A);

    for ($row = 0; $row < count($bookingdata); $row++) {

        $availseats = $bookingdata[$row]['total'];

    }

    return $availseats;

}

function gettotalseatsbyshow($showid)
{
    global $wpdb;

    $sql = "select count(*) as total from $wpdb->rst_shows gs,$wpdb->rst_seats gse
            WHERE gs.id = gse.show_id
            AND gse.seattype != ''
            AND gs.id =" . $showid;

    $availseats = 0;

    $bookingdata = $wpdb->get_results($sql, ARRAY_A);

    for ($row = 0; $row < count($bookingdata); $row++) {

        $availseats = $bookingdata[$row]['total'];

    }

    return $availseats;

}

function getbookedseatsbyshow($showid)
{
    global $wpdb;

    $sql = "select count(*) as total from $wpdb->rst_shows gs,$wpdb->rst_seats gse
            WHERE gs.id = gse.show_id
            AND gse.seattype != '' 
			AND gse.seattype = 'B' 
            AND gs.id =" . $showid;

    $availseats = 0;

    $bookingdata = $wpdb->get_results($sql, ARRAY_A);

    for ($row = 0; $row < count($bookingdata); $row++) {

        $availseats = $bookingdata[$row]['total'];

    }

    return $availseats;

}





function delete_seats($action, $finalseats, $showid)
{
    global $wpdb;
    return $wpdb->query("DELETE FROM $wpdb->rst_seats WHERE show_id='$showid'");
}


function offline_payment_form($_data = array()) {


	if (isset($_data['payment_method']) && $_data['payment_method'] == "offlinepayment_force") {
		$qty = sizeof($_data['owners'])*$_data['qty'];
		foreach($_data as $key=>$value)
	{
	//if(in_array($key,$hidden_fields))
	//$hiddenfields.='<input type="hidden" name="'.$key.'" value="'.$value.'"/>';
	
	}	


	$rst_options = get_option(RSTPLN_OPTIONS);

	$rst_paypal_options = get_option(RSTPLN_PPOPTIONS);
	
	$wplanguagesoptions = get_option(RSTLANGUAGES_OPTIONS);
	$offline_payment_mode="Offline payment mode";
	$offline_cc="Credit Card";
    $offline_cheque="Cheque";
    $offline_fill="Fill your name /email to receive a copy of ticket. This will serve as purchase receipt";
    $offline_place_name="Place your name (required)";
	$offline_place_email="Your email address (required)";
	$offline_billing_address="Billing Address";
	$offline_first_name="First name";
	$offline_last_name="Last name";
	$offline_street="Street";
	$offline_city="City";
	$offline_state="State";
	$offline_country="Country";
	$offline_zip="Zip";
	$offline_phone="Phone";

	

    
	if($wplanguagesoptions['rst_enable_languages']=="on")
	{
		if($wplanguagesoptions['languages_offline_payment_mode'])
		{
			$offline_payment_mode=$wplanguagesoptions['languages_offline_payment_mode'];
		}
		if($wplanguagesoptions['languages_offline_cc'])
		{
			$offline_cc=$wplanguagesoptions['languages_offline_cc'];
		}
		if($wplanguagesoptions['languages_offline_cheque'])
		{
			$offline_cheque=$wplanguagesoptions['languages_offline_cheque'];
		}
		if($wplanguagesoptions['languages_offline_fill'])
		{
			$offline_fill=$wplanguagesoptions['languages_offline_fill'];
		}
        if($wplanguagesoptions['languages_offline_place_name'])
		{
			$offline_place_name=$wplanguagesoptions['languages_offline_place_name'];
		}			
        if($wplanguagesoptions['languages_offline_place_email'])
		{
			$offline_place_email=$wplanguagesoptions['languages_offline_place_email'];
		}			
        if($wplanguagesoptions['languages_offline_billing_address'])
		{
			$offline_billing_address=$wplanguagesoptions['languages_offline_billing_address'];
		}		
        if($wplanguagesoptions['languages_offline_first_name'])
		{
			$offline_first_name=$wplanguagesoptions['languages_offline_first_name'];
		}	
        if($wplanguagesoptions['languages_offline_last_name'])
		{
			$offline_last_name=$wplanguagesoptions['languages_offline_last_name'];
		}			
        if($wplanguagesoptions['languages_offline_street'])
		{
			$offline_street=$wplanguagesoptions['languages_offline_street'];
		}
		if($wplanguagesoptions['languages_offline_city'])
		{
			$offline_city=$wplanguagesoptions['languages_offline_city'];
		}
		if($wplanguagesoptions['languages_offline_state'])
		{
			$offline_state=$wplanguagesoptions['languages_offline_state'];
		}
		if($wplanguagesoptions['languages_offline_country'])
		{
			$offline_country=$wplanguagesoptions['languages_offline_country'];
		}
		if($wplanguagesoptions['languages_offline_zip'])
		{
			$offline_zip=$wplanguagesoptions['languages_offline_zip'];
		}
		if($wplanguagesoptions['languages_offline_phone'])
		{
			$offline_phone=$wplanguagesoptions['languages_offline_phone'];
		}

		
 		
	}	
	
	

		$returnstring= '<div id="offlinepaymentform"><form action="" method="POST" id="row_seats_default_offlinepayment_payment_form">					
		<input type="hidden" name="campaign_title" value="'.$_data['campaign_title'].'"/>
		<input type="hidden" name="action" value="row_seats_default_offlinepayment"/>
		<input type="hidden" name="redirect" value="'.$_data['return_url'].'"/>
		<input type="hidden"  id="txn_key" name="txn_key" value="">
		<input type="hidden" id="x_custom" name="x_custom" value="">
		<input type="hidden" id="x_invoice_num" name="x_invoice_num" value="">	
		<input type="hidden" id="item_number" name="item_number" value="">	
		<input type="hidden" name="amount" value="'.$_data['amount'].'"/>
		<input type="hidden" name="offlinepayment_code" value="'.base64_encode($_data['tx_str']).'">'.$hiddenfields.'
		<div id="payment-errors" class="row_seats_error_message" style="display:none">Attention! Please correct the errors below and try again.<div id="offlinepayment_errors"></div></div><br>
		<table class=row_seats_confirmation_table>
				<!--<tr>
					<td class="row_seats_confirmation_title" width="278" >'.$offline_payment_mode.':</td>
					<td class="row_seats_confirmation_data"><input type="radio"  name="transaction_mode"  id="transaction_mode" Value="Credit Card" class="row_seats_input" checked/>&nbsp;'.$offline_cc.'&nbsp;&nbsp;<input type="radio"  name="transaction_mode"  id="transaction_mode" Value="Cheque" class="row_seats_input"/>&nbsp;'.$offline_cheque.' </td>
				</tr>
				<tr>
					<td class="row_seats_confirmation_title" colspan=2><span style="color:#888888;font-style:italic;font-weight:normal">'.$offline_fill.'</span><br>
					<div class="row_seats_confirmation_data"><input class="row_seats_input" title="'.$offline_place_name.'" onblur="if (this.value == \'\') {this.value = \''.$offline_place_name.'\';}" onfocus="if (this.value == \''.$offline_place_name.'\') {this.value = \'\';}" value="'.$offline_place_name.'" style="width:230px;padding-left:20px;" type="text" name="offlinepayment_payer_name" id="offlinepayment_payer_name" />&nbsp;&nbsp;&nbsp;
					
					<input class="row_seats_input" title="'.$offline_place_email.'" onblur="if (this.value == \'\') {this.value = \''.$offline_place_email.'\';}" onfocus="if (this.value == \''.$offline_place_email.'\') {this.value = \'\';}" value="'.$offline_place_email.'" style="width:230px;padding-left:20px;" type="text" id="offlinepayment_payer_email" name="offlinepayment_payer_email" /></div>
					</td>
				</tr>-->';
/*
				$returnstring.= '<input type="hidden"  name="address_verification" id="address_verification"  value="enabled"/><tr>
					<td class="row_seats_confirmation_title" colspan="2"><b>'.$offline_billing_address.'</b></td>
				</tr>
				<tr>
					<td class="row_seats_confirmation_title" width="278">'.$offline_first_name.':</td>
					<td class="row_seats_confirmation_data"><input type="text" size="20" autocomplete="off" name="first_name"   id="first_name" class="row_seats_input"/></td>
				</tr>	
											<tr>
					<td class="row_seats_confirmation_title" width="278">'.$offline_last_name.':</td>
					<td class="row_seats_confirmation_data"><input type="text" size="20" autocomplete="off" name="last_name"   id="last_name" class="row_seats_input"/></td>
				</tr>	
				<tr>
					<td class="row_seats_confirmation_title" width="278">'.$offline_street.':</td>
					<td class="gc_confirmation_data"><input type="text" size="20" autocomplete="off" name="street"   id="street" class="row_seats_input"/></td>
				</tr>							
				<tr>
					<td class="row_seats_confirmation_title" width="278">'.$offline_city.':</td>
					<td class="gc_confirmation_data"><input type="text" size="20" autocomplete="off" name="city"   id="city" class="row_seats_input"/></td>
				</tr>						  		<tr>
					<td class="row_seats_confirmation_title" width="278">'.$offline_state.':</td>
					<td class="row_seats_confirmation_data"><input type="text" size="20" autocomplete="off" name="state"   id="state" class="row_seats_input"/></td>
				</tr>						  		<tr>
					<td class="row_seats_confirmation_title" width="278">'.$offline_country.':</td>
					<td class="row_seats_confirmation_data">
					<select id="country" name="country" class="row_seats_input">
					<option value="US">United States</option>
					<option value="AF">Afghanistan</option>
					<option value="AX">Aland Islands</option>
					<option value="AL">Albania</option>
					<option value="DZ">Algeria</option>
					<option value="AS">American Samoa</option>
					<option value="AD">Andorra</option>
					<option value="AO">Angola</option>
					<option value="AI">Anguilla</option>
					<option value="AQ">Antarctica</option>
					<option value="AG">Antigua and Barbuda</option>
					<option value="AR">Argentina</option>
					<option value="AM">Armenia</option>
					<option value="AW">Aruba</option>
					<option value="AU">Australia</option>
					<option value="AT">Austria</option>
					<option value="AZ">Azerbaijan</option>
					<option value="BS">Bahamas</option>
					<option value="BH">Bahrain</option>
					<option value="BD">Bangladesh</option>
					<option value="BB">Barbados</option>
					<option value="BY">Belarus</option>
					<option value="BE">Belgium</option>
					<option value="BZ">Belize</option>
					<option value="BJ">Benin</option>
					<option value="BM">Bermuda</option>
					<option value="BT">Bhutan</option>
					<option value="BO">Bolivia</option>
					<option value="BA">Bosnia and Herzegovina</option>
					<option value="BW">Botswana</option>
					<option value="BV">Bouvet Island</option>
					<option value="BR">Brazil</option>
					<option value="IO">British Indian Ocean Territory</option>
					<option value="BN">Brunei Darussalam</option>
					<option value="BG">Bulgaria</option>
					<option value="BF">Burkina Faso</option>
					<option value="BI">Burundi</option>
					<option value="KH">Cambodia</option>
					<option value="CM">Cameroon</option>
					<option value="CA">Canada</option>
					<option value="CV">Cape Verde</option>
					<option value="KY">Cayman Islands</option>
					<option value="CF">Central African Republic</option>
					<option value="TD">Chad</option>
					<option value="CL">Chile</option>
					<option value="CN">China</option>
					<option value="CX">Christmas Island</option>
					<option value="CC">Cocos (Keeling) Islands</option>
					<option value="CO">Colombia</option>
					<option value="KM">Comoros</option>
					<option value="CG">Congo</option>
					<option value="CD">Congo, The Democratic Republic of The</option>
					<option value="CK">Cook Islands</option>
					<option value="CR">Costa Rica</option>
					<option value="CI">Cote D\'ivoire</option>
					<option value="HR">Croatia</option>
					<option value="CU">Cuba</option>
					<option value="CY">Cyprus</option>
					<option value="CZ">Czech Republic</option>
					<option value="DK">Denmark</option>
					<option value="DJ">Djibouti</option>
					<option value="DM">Dominica</option>
					<option value="DO">Dominican Republic</option>
					<option value="EC">Ecuador</option>
					<option value="EG">Egypt</option>
					<option value="SV">El Salvador</option>
					<option value="GQ">Equatorial Guinea</option>
					<option value="ER">Eritrea</option>
					<option value="EE">Estonia</option>
					<option value="ET">Ethiopia</option>
					<option value="FK">Falkland Islands (Malvinas)</option>
					<option value="FO">Faroe Islands</option>
					<option value="FJ">Fiji</option>
					<option value="FI">Finland</option>
					<option value="FR">France</option>
					<option value="GF">French Guiana</option>
					<option value="PF">French Polynesia</option>
					<option value="TF">French Southern Territories</option>
					<option value="GA">Gabon</option>
					<option value="GM">Gambia</option>
					<option value="GE">Georgia</option>
					<option value="DE">Germany</option>
					<option value="GH">Ghana</option>
					<option value="GI">Gibraltar</option>
					<option value="GR">Greece</option>
					<option value="GL">Greenland</option>
					<option value="GD">Grenada</option>
					<option value="GP">Guadeloupe</option>
					<option value="GU">Guam</option>
					<option value="GT">Guatemala</option>
					<option value="GG">Guernsey</option>
					<option value="GN">Guinea</option>
					<option value="GW">Guinea-bissau</option>
					<option value="GY">Guyana</option>
					<option value="HT">Haiti</option>
					<option value="HM">Heard Island and Mcdonald Islands</option>
					<option value="VA">Holy See (Vatican City State)</option>
					<option value="HN">Honduras</option>
					<option value="HK">Hong Kong</option>
					<option value="HU">Hungary</option>
					<option value="IS">Iceland</option>
					<option value="IN">India</option>
					<option value="ID">Indonesia</option>
					<option value="IR">Iran, Islamic Republic of</option>
					<option value="IQ">Iraq</option>
					<option value="IE">Ireland</option>
					<option value="IM">Isle of Man</option>
					<option value="IL">Israel</option>
					<option value="IT">Italy</option>
					<option value="JM">Jamaica</option>
					<option value="JP">Japan</option>
					<option value="JE">Jersey</option>
					<option value="JO">Jordan</option>
					<option value="KZ">Kazakhstan</option>
					<option value="KE">Kenya</option>
					<option value="KI">Kiribati</option>
					<option value="KP">Korea, Democratic People\'s Republic of</option>
					<option value="KR">Korea, Republic of</option>
					<option value="KW">Kuwait</option>
					<option value="KG">Kyrgyzstan</option>
					<option value="LA">Lao People\'s Democratic Republic</option>
					<option value="LV">Latvia</option>
					<option value="LB">Lebanon</option>
					<option value="LS">Lesotho</option>
					<option value="LR">Liberia</option>
					<option value="LY">Libyan Arab Jamahiriya</option>
					<option value="LI">Liechtenstein</option>
					<option value="LT">Lithuania</option>
					<option value="LU">Luxembourg</option>
					<option value="MO">Macao</option>
					<option value="MK">Macedonia, The Former Yugoslav Republic of</option>
					<option value="MG">Madagascar</option>
					<option value="MW">Malawi</option>
					<option value="MY">Malaysia</option>
					<option value="MV">Maldives</option>
					<option value="ML">Mali</option>
					<option value="MT">Malta</option>
					<option value="MH">Marshall Islands</option>
					<option value="MQ">Martinique</option>
					<option value="MR">Mauritania</option>
					<option value="MU">Mauritius</option>
					<option value="YT">Mayotte</option>
					<option value="MX">Mexico</option>
					<option value="FM">Micronesia, Federated States of</option>
					<option value="MD">Moldova, Republic of</option>
					<option value="MC">Monaco</option>
					<option value="MN">Mongolia</option>
					<option value="ME">Montenegro</option>
					<option value="MS">Montserrat</option>
					<option value="MA">Morocco</option>
					<option value="MZ">Mozambique</option>
					<option value="MM">Myanmar</option>
					<option value="NA">Namibia</option>
					<option value="NR">Nauru</option>
					<option value="NP">Nepal</option>
					<option value="NL">Netherlands</option>
					<option value="AN">Netherlands Antilles</option>
					<option value="NC">New Caledonia</option>
					<option value="NZ">New Zealand</option>
					<option value="NI">Nicaragua</option>
					<option value="NE">Niger</option>
					<option value="NG">Nigeria</option>
					<option value="NU">Niue</option>
					<option value="NF">Norfolk Island</option>
					<option value="MP">Northern Mariana Islands</option>
					<option value="NO">Norway</option>
					<option value="OM">Oman</option>
					<option value="PK">Pakistan</option>
					<option value="PW">Palau</option>
					<option value="PS">Palestinian Territory, Occupied</option>
					<option value="PA">Panama</option>
					<option value="PG">Papua New Guinea</option>
					<option value="PY">Paraguay</option>
					<option value="PE">Peru</option>
					<option value="PH">Philippines</option>
					<option value="PN">Pitcairn</option>
					<option value="PL">Poland</option>
					<option value="PT">Portugal</option>
					<option value="PR">Puerto Rico</option>
					<option value="QA">Qatar</option>
					<option value="RE">Reunion</option>
					<option value="RO">Romania</option>
					<option value="RU">Russian Federation</option>
					<option value="RW">Rwanda</option>
					<option value="SH">Saint Helena</option>
					<option value="KN">Saint Kitts and Nevis</option>
					<option value="LC">Saint Lucia</option>
					<option value="PM">Saint Pierre and Miquelon</option>
					<option value="VC">Saint Vincent and The Grenadines</option>
					<option value="WS">Samoa</option>
					<option value="SM">San Marino</option>
					<option value="ST">Sao Tome and Principe</option>
					<option value="SA">Saudi Arabia</option>
					<option value="SN">Senegal</option>
					<option value="RS">Serbia</option>
					<option value="SC">Seychelles</option>
					<option value="SL">Sierra Leone</option>
					<option value="SG">Singapore</option>
					<option value="SK">Slovakia</option>
					<option value="SI">Slovenia</option>
					<option value="SB">Solomon Islands</option>
					<option value="SO">Somalia</option>
					<option value="ZA">South Africa</option>
					<option value="GS">South Georgia and The South Sandwich Islands</option>
					<option value="ES">Spain</option>
					<option value="LK">Sri Lanka</option>
					<option value="SD">Sudan</option>
					<option value="SR">Suriname</option>
					<option value="SJ">Svalbard and Jan Mayen</option>
					<option value="SZ">Swaziland</option>
					<option value="SE">Sweden</option>
					<option value="CH">Switzerland</option>
					<option value="SY">Syrian Arab Republic</option>
					<option value="TW">Taiwan, Province of China</option>
					<option value="TJ">Tajikistan</option>
					<option value="TZ">Tanzania, United Republic of</option>
					<option value="TH">Thailand</option>
					<option value="TL">Timor-leste</option>
					<option value="TG">Togo</option>
					<option value="TK">Tokelau</option>
					<option value="TO">Tonga</option>
					<option value="TT">Trinidad and Tobago</option>
					<option value="TN">Tunisia</option>
					<option value="TR">Turkey</option>
					<option value="TM">Turkmenistan</option>
					<option value="TC">Turks and Caicos Islands</option>
					<option value="TV">Tuvalu</option>
					<option value="UG">Uganda</option>
					<option value="UA">Ukraine</option>
					<option value="AE">United Arab Emirates</option>
					<option value="GB">United Kingdom</option>
					<option value="UM">United States Minor Outlying Islands</option>
					<option value="UY">Uruguay</option>
					<option value="UZ">Uzbekistan</option>
					<option value="VU">Vanuatu</option>
					<option value="VE">Venezuela</option>
					<option value="VN">Viet Nam</option>
					<option value="VG">Virgin Islands, British</option>
					<option value="VI">Virgin Islands, U.S.</option>
					<option value="WF">Wallis and Futuna</option>
					<option value="EH">Western Sahara</option>
					<option value="YE">Yemen</option>
					<option value="ZM">Zambia</option>
					<option value="ZW">Zimbabwe</option>
					</select>	
					</td>
				</tr>						  		<tr>
					<td class="row_seats_confirmation_title" width="278">'.$offline_zip.':</td>
					<td class="row_seats_confirmation_data"><input type="text" size="15" autocomplete="off" name="zip"   id="zip" class="row_seats_input"/></td>
				</tr>
				<tr>
					<td class="row_seats_confirmation_title" width="278">'.$offline_phone.':</td>
					<td class="row_seats_confirmation_data"><input type="text" size="15" autocomplete="off" name="phone"   id="phone" class="row_seats_input"/></td>
				</tr>';
*/
				$returnstring.= '</table>
		 <button id="rsbuynow" style="display:none" type="submit" class="submit-button">Submit Payment</button>
	  </form>				  
	  ';
	  echo $returnstring;

	}
}


function offline_payment_form_process() {

	global $row_seats, $wpdb;
//print_r($_POST);

	if(isset($_POST['action']) && $_POST['action'] == 'row_seats_default_offlinepayment') {

	$symbol = get_option('rst_currencysymbol');
	$currency = get_option('rst_currency');
    $symbols = array(
        "0" => "$",
        "1" => "&pound;",
        "2" => "&euro;",
        "3" => "&#3647;",
        "4" => "&#8362;",
        "5" => "&yen;",
        "6" => "&#8377;",
        "7" => "R$",
        "8" => "kr",
        "9" => "zł",
        "10" => "Ft",
        "11" => "Kč",
        "12" => "&#1088;&#1091&#1073;",
        "13" => "&#164;",
        "14" => "&#x20B1;",
        "15" => "Fr",
        "16" => "RM");

    $symbol = $symbols[$symbol];

	
		$gross_total = $_REQUEST['amount'];
		$first_name= $_REQUEST['first_name'];	
		$last_name= $_REQUEST['last_name'];
		$card_number = $_REQUEST['card-number'];					
		$card_cvc = $_REQUEST['card-cvc'];
		$card_expiry = $_REQUEST['card-expiry-month'].$_REQUEST['card-expiry-year'];				
		$street = $_REQUEST['street'];
		$city = $_REQUEST['city'];
		$state = $_REQUEST['state'];
		$country = $_REQUEST['country'];
		$zip = $_REQUEST['zip'];
		$phone = $_REQUEST['phone'];
		$transaction_mode_offline = $_REQUEST['transaction_mode'];
		$api_version = '85.0';		
		$request_params = array
		(
			'PAYMENTACTION' => 'Sale',
			'TRANSACTIONMODE' => $transaction_mode_offline,
			'IPADDRESS' => $_SERVER['REMOTE_ADDR'],
			'FIRSTNAME' => $first_name,
			'LASTNAME' => $last_name,
			'STREET' => $street,
			'CITY' => $city,
			'STATE' => $state,             
			'COUNTRYCODE' => $country,
			'ZIP' => $zip,
			'PHONE' => $phone,
			'AMT' => $gross_total,
			'CURRENCYCODE' => $currency,
			'DESC' => $campaign_title
		);

		$temp_array['address'] = array
		(
			'first_name' => $first_name,
			'last_name' => $last_name,
			'address' => $street,
			'city' => $city,
			'state' => $state,             
			'country' => $country,
			'zip' => $zip,
			'phone' => $phone
		);	

		$paymentData="";
		foreach($request_params as $var=>$val)
		{
		   $paymentData.= '&'.$var.'='.urlencode($val);  
		}					

		$payer_name	=$first_name." ".$last_name;		   
		$payer_name	=$_POST['offlinepayment_payer_name'];
		$payer_offlinepayment	=$_POST['offlinepayment_payer_email'];
		if($_POST['x_invoice_num'])	
		{
			$inid=$_POST['x_invoice_num'];
			$mc_currency =  $currency;

			if(current_user_can('contributor') || current_user_can('administrator')) // If booking done by Admin/Contributer
			{
				$transaction_type = 'Admin';
				$payment_status = 'Completed';	
			}elseif($_POST['amount']==0){    // If booking amount is 0
				$transaction_type = 'Zero booking'; 	
				$payment_status = 'Completed';					
			}else   // When there is no active payment gateway for customer
			{
				$transaction_type = 'Not Paid'; 
				$payment_status = 'Pending';
			}
			$sql = "select * from $wpdb->rst_bookings rstbk,$wpdb->rst_shows rsts 
			where   rsts.id = rstbk.show_id 
			and rstbk.booking_id =" . $_POST['x_invoice_num'];  
			//Below condition executes for customer check when there is not active payment gateway - Start
			if ($results = $wpdb->get_results($sql, ARRAY_A)) {
				$booking_details = $wpdb->get_results($sql, ARRAY_A);
				$data = $booking_details[0];
				
 		   
		$payer_name	=$booking_details[0]['name'];
		$payer_offlinepayment	=$booking_details[0]['email'];

		
				$show_name = $booking_details[0]['show_name'];
				$show_date= $booking_details[0]['show_date'];
				$booking_details = $booking_details[0]['booking_details'];
				$ticketno = $rst_options['rst_ticket_prefix'] . $_POST['x_invoice_num'];
				$booking_details = unserialize($booking_details);
				$ticket_seat_no=array();
				for ($row = 0; $row < count($booking_details); $row++) {
					$seats = $booking_details[$row]['seatno'];
					$showid = $booking_details[$row]['show_id'];
					$rowname = $booking_details[$row]['row_name'];
					$price = $booking_details[$row]['price'];
					$ticket_seat_no[]=$rowname . $seats;	
				}
				$ticket_seat_no=implode(",",$ticket_seat_no);
				//Preparing to send alert mail to customer to notify that payment for offline method is pending.
				if($transaction_type=="Not Paid")
				{
					$tags = array('{payer_name}', '{payer_email}', '{payment_status}', '{show_name}', '{show_date}', '{seats}','{amount}');
					$vals = array($payer_name, $payer_offlinepayment, $payment_status,$show_name ,$show_date,$ticket_seat_no,$gross_total );
					$body = str_replace($tags, $vals, get_option('rst_pending_email_body'));
					$mail_headers = "Content-Type: text/plain; charset=utf-8\r\n";
					$mail_headers .= "From: ".get_option('rst_from_name')." <".get_option('rst_from_email').">\r\n";
					$mail_headers .= "X-Mailer: PHP/".phpversion()."\r\n";
					wp_mail(/*$email*/$payer_offlinepayment, get_option('rst_pending_email_subject'), $body, $mail_headers);	
				}				

			}	
			//Below condition executes for customer check when there is not active payment gateway - End	


			$sql = "INSERT INTO rst_payment_transactions (
				tx_str, payer_name, payer_email, gross, currency, payment_status, transaction_type, details, created, deleted,custom,first_name,last_name,address,city,state,zip,country,phone,show_name,show_date,coupon_code,coupon_discount,special_fee,ticket_no,seat_numbers,seat_cost) VALUES (
				'".mysql_real_escape_string($inid)."',
				'".mysql_real_escape_string($payer_name)."',
				'".mysql_real_escape_string($payer_offlinepayment)."',
				'".floatval($gross_total)."',
				'".$mc_currency."',
				'".$payment_status."',
				'Offline Payment:".$transaction_type."',
				'".addslashes($paymentData)."',
				'".time()."', '0','".$_POST['x_custom']."',
					'".$first_name."',
					'".$last_name."',
					'".$street."',
					'".$city."',
					'".$state."',
					'".$zip."',
					'".$country."',
					'".$phone."',
					'".$show_name."',
					'".$show_date."',
					'',
					'0',
					'0',
					'',
					'',
					''
			)";

			$wpdb->query($sql) or die(mysql_error());
			$transaction_id=mysql_insert_id();
			$data = array(
			'txn_id' => "TXYN".$inid,
			'mc_gross' => $_POST['amount'],
			'custom' => $_POST['x_custom']
			);

			if($transaction_type=="Not Paid") {
			$data['sendemail']="no"; // Don't send ticket mail if payment is pending
			}
			rst_bookseatsfinal($data); // booking seats
			
			if($transaction_type!="Not Paid") {			
				$tags = array('{payer_name}', '{payer_email}', '{payment_status}', '{show_name}', '{show_date}', '{seats}','{amount}');
				$vals = array($payer_name, $payer_offlinepayment, $payment_status,$show_name ,$show_date,$ticket_seat_no,$gross_total );
				$body = str_replace($tags, $vals, get_option('rst_success_email_body'));

				$mail_headers = "Content-Type: text/plain; charset=utf-8\r\n";
				$mail_headers .= "From: ".get_option('rst_from_name')." <".get_option('rst_from_email').">\r\n";
				$mail_headers .= "X-Mailer: PHP/".phpversion()."\r\n";
				wp_mail($payer_offlinepayment, get_option('rst_success_email_subject'), $body, $mail_headers);				

			}			

		}

		$logMessages .= "Done.";
		//$wpdb->query("INSERT INTO $wpdb->rst_paypal_ipn_log (booking_time, booking_id, messages) VALUES (now(), '$inid', '$logMessages')");	

		//if(current_user_can('contributor') || current_user_can('administrator'))
		//{
		?><script language='javascript'>
		alert('Successfully Booked! eTicket.');
		window.location.href=window.location.href;
		</script>

		<?php	
		exit;

		//}else{

		//$_POST['return']=get_bloginfo("wpurl")."/thank-you?id=".$inid;

		//}
		//wp_redirect($_POST['return']); 			

	}		
}


//Class to manage payment gateway modules


class row_seats_module {

	var $options = array();

	var $module = array("id" => "", "title" => "", "settings" => false);


	

	function __construct() {

		add_filter('row_seats_modules', array(&$this, 'row_seats_modules'));
		
	
	$this->get_options();

	}

	

	function get_options() {

		$exists = get_option('row_seatsmodule_'.$this->module['id'].'_'.'version');
        //$exists=1;    
		if ($exists) {

			foreach ($this->options as $key => $value) {

				$this->options[$key] = get_option('row_seatsmodule_'.$this->module['id'].'_'.$key);

			}

		}
		
	

	}



	function update_options() {

		if (current_user_can('manage_options')) {

			foreach ($this->options as $key => $value) {

				update_option('row_seatsmodule_'.$this->module['id'].'_'.$key, $value);

			}

			update_option('row_seatsmodule_'.$this->module['id'].'_'.'version', '2');

		}

	}



	function populate_options() {

		foreach ($this->options as $key => $value) {

			if (isset($_POST['row_seatsmodule_'.$this->module['id'].'_'.$key])) {

				$this->options[$key] = stripslashes($_POST['row_seatsmodule_'.$this->module['id'].'_'.$key]);

			}

		}

	}



	function check_options($_errors, $_moduleid) {

		return $_errors;

	}

	

	function field_name($option) {

		if (empty($option)) return false;

		return 'row_seatsmodule_'.$this->module['id'].'_'.$option;

	}

	

	function row_seats_modules($modules) {

		if (!empty($this->module['id'])) {

			$modules[$this->module['id']] = array("title" => $this->module['title'], "settings" => $this->module['settings']);

		}

		return $modules;

	}

	

	function page_switcher ($_urlbase, $_currentpage, $_totalpages) {

		$pageswitcher = "";

		if ($_totalpages > 1) {

			$pageswitcher = '<div class="tablenav bottom"><div class="tablenav-pages">'.__('Pages:', 'row_seats').' <span class="pagiation-links">';

			if (strpos($_urlbase,"?") !== false) $_urlbase .= "&amp;";

			else $_urlbase .= "?";

			if ($_currentpage == 1) $pageswitcher .= "<a class='page disabled'>1</a> ";

			else $pageswitcher .= " <a class='page' href='".$_urlbase."p=1'>1</a> ";



			$start = max($_currentpage-3, 2);

			$end = min(max($_currentpage+3,$start+6), $_totalpages-1);

			$start = max(min($start,$end-6), 2);

			if ($start > 2) $pageswitcher .= " <b>...</b> ";

			for ($i=$start; $i<=$end; $i++) {

				if ($_currentpage == $i) $pageswitcher .= " <a class='page disabled'>".$i."</a> ";

				else $pageswitcher .= " <a class='page' href='".$_urlbase."p=".$i."'>".$i."</a> ";

			}

			if ($end < $_totalpages-1) $pageswitcher .= " <b>...</b> ";



			if ($_currentpage == $_totalpages) $pageswitcher .= " <a class='page disabled'>".$_totalpages."</a> ";

			else $pageswitcher .= " <a class='page' href='".$_urlbase."p=".$_totalpages."'>".$_totalpages."</a> ";

			$pageswitcher .= "</span></div></div>";

		}

		return $pageswitcher;

	}

}



//----Row Seats All Events widget starts here----//
class allevents_widget extends WP_Widget {
	function allevents_widget() {
		//parent::WP_Widget(false, __('Row Seats Events', 'rst'));
		parent::__construct( false, 'Row Seats Events' );
	}

	function widget($args, $instance) {
		global $wpdb;
		extract( $args );
		$title = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
		$args='posts_per_page=100&post_type=page';
		query_posts( $args );

		// The Loop
		$i=1;
		$shortcodepages=array();
		$shortcodepagesimages=array();
		while ( have_posts() ) : the_post();

			$titlehere=get_the_title();
			$content = get_the_content();
			$pattern = get_shortcode_regex();
			//$pimage = get_the_post_thumbnail(get_the_ID(), array(50,50));
			$pimage = get_the_post_thumbnail();
				if (   preg_match_all( '/'. $pattern .'/s', $content, $matches )
					&& array_key_exists( 2, $matches )
					&& in_array( 'showseats', $matches[2] ) )
				{
				$getid=explode("=",$matches[3][0]);
				$posturl=wp_get_shortlink() ;

				$shortcodepages[$getid[1]]=$posturl;
				$shortcodepagesimages[$getid[1]]=$pimage;
				}  

				
			$i++;
		endwhile;

// Reset Query
		wp_reset_query();

		$sql = "SELECT * FROM $wpdb->rst_shows where show_date>=now() order by show_end_time";
		$found = 0;
		$data = Array();
		$widgetcontent="";
		if ($results = $wpdb->get_results($sql, ARRAY_A)) {

			foreach ($results as $value) {

			$pimage="";
			$edate = date("F j, Y",strtotime($value['show_date'])); 
			if($shortcodepages[$value['id']])
			{
			if($shortcodepagesimages[$value['id']])
			{
			$pimage= $shortcodepagesimages[$value['id']]."<br><br>";
			}
			$widgetcontent.="<div style='padding:6px;margin:6px;border: dotted 1px black;' ><a href='".$shortcodepages[$value['id']]."'>".$pimage.$value['show_name']."</a><br>Date : ".$edate."</div>";
			}
			}

		}

	
		if (!empty($title) || !empty($widgetcontent)) {
			echo $before_widget;
			if (!empty($title)) echo $before_title.$title.$after_title;
			echo $widgetcontent;
			echo $after_widget;
		}

	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	function form($instance) {
		$instance = wp_parse_args((array)$instance, array('title' => ''));
		$title = strip_tags($instance['title']);
		echo '
		<p>
			<label for="'.$this->get_field_id("title").'">'.__('Title', 'rst').':</label>
			<input class="widefat" type="text" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" value="'.esc_attr($title).'" />
		</p>';
	}
}



?>