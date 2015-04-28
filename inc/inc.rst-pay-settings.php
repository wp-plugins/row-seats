<script type='text/javascript' src='<?php echo RSTPLN_URL ?>js/jquery.blockUI.js'></script>
<style>
    .form-table th {
        width: 250px !important;
    }

    .validated {
        background-color: #B8FCB5;
        border-color: #087704;
        padding: 0 0.6em;
        margin: 5px 0 15px;
        border-radius: 3px 3px 3px 3px;
        border-style: solid;
        border-width: 1px;
    }
</style>
<?php
$symbol = array(
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
$currencies=array();
$currencies = apply_filters('row_seats_currencies', $currencies); //getting currency list from all payment gateways
//array of payment setting variables
$rst_payment_settings=array("rst_success_email_subject","rst_success_email_body","rst_failed_email_subject","rst_failed_email_body","rst_currencysymbol","rst_currency","rst_from_name","rst_from_email","rst_pending_email_body","rst_pending_email_subject","rst_waitingapproval_email_subject","rst_waitingapproval_email_body","rst_cancelled_email_subject","rst_cancelled_email_body","rst_refund_email_subject","rst_refund_email_body");

$updated = '';
$validmsg = '';
$validated = 'no';
if (isset($_POST) && $_POST['Submit'] == 'Save Settings') {
	foreach($rst_payment_settings as $paymentvars)
	{
		update_option($paymentvars, $_POST[$paymentvars]);	//Update custom vairables
	}	
	do_action('row_seats_save_options'); //Update payment gateway setting variables
    $updated = 'yes';
}
if (isset($_POST) && $_POST['l_action'] == 'pleasevalidate') {



    ?>
    <script>
        window.location = '<?php echo get_option('siteurl')?>/wp-admin/admin.php?page=rst-pay-settings';
    </script>
<?php

}

$rst_options = get_option(RSTPLN_OPTIONS);
//Start populating payment custom variables
$defaultpopulate="Yes";
	foreach($rst_payment_settings as $paymentvars)
	{
	$rst_options[$paymentvars] = get_option($paymentvars);
	if($rst_options[$paymentvars])
	{
	$defaultpopulate="No";
	}
	}
if($_REQUEST['resettodefault']=="yes")
{
$defaultpopulate="Yes";

}	
if($defaultpopulate=="Yes")
{
$mydata=array();

$mydata['rst_currencysymbol']= $symbol[0];
$mydata['rst_currency']= "USD";
$mydata['rst_from_name']= "Row seat";
$mydata['rst_from_email']= "admin@rowseatsplugin.com";
$mydata['rst_success_email_subject']= "Payment Success  - Your ticket is ready";
$mydata['rst_success_email_body']= "Dear {payer_name},

We received your booking seat(s) details, there were sent to {payer_email} for the following show:  

Show: {show_name}
Show Date: {show_date}
Seats Selected:{seats}
Total due: {amount}

The payment status of your booking is {payment_status}. 

Enjoy the show.

Administration";
$mydata['rst_failed_email_subject']= "Payment failed";
$mydata['rst_failed_email_body']= "Payment failed 

{payer_name
{payer_email}
{payment_status}";
$mydata['rst_pending_email_subject']= "offline payment - please make payment asap";
$mydata['rst_pending_email_body']= "Dear {payer_name},

We received your booking seat(s) details, there were sent to {payer_email} for the following show:  

Show: {show_name}
Show Date: {show_date}
Seats Selected:{seats}
Total due: {amount}

The payment status of your booking is {payment_status}.  Once payment is received, we will email you a confirmation of your booked seats.

Enjoy the show.

Administration";

	foreach($rst_payment_settings as $paymentvars)
	{
		update_option($paymentvars, $mydata[$paymentvars]);	//Update custom vairables
	}
	foreach($rst_payment_settings as $paymentvars)
	{
	$rst_options[$paymentvars] = get_option($paymentvars);

	}
?>
    <script>
        window.location = '<?php echo get_option('siteurl')?>/wp-admin/admin.php?page=rst-pay-settings';
    </script>
<?php	
	
}	

if ($rst_options['rst_adminetemp'] == '') {
    $rst_options['rst_adminetemp'] = "Dear <blogname>,
The following seats were purchased for <showname> for <showdate>:<br/>
<bookedseats>
There are <availableseats> seats left to book full house.  <br/>
<br>
You can check them in your admin panel and download the csv for the corresponding event.<br/>
<blogname>
If you have any questions, please let me know.<br>
Regards,<br/>
Administration";
}
if ($rst_options['rst_etemp'] == '') {
    $rst_options['rst_etemp'] = "Dear <username>,

You have successfully booked for <showname> for <showdate>.  The following seats were purchased:<br/><br/>
<bookedseats>
<br>
</br>

Please arrive 30min prior to the engagement for ticket validation.  We appreciate your business.<br/>

Enjoy the show.<br/><br/>

Regards,<br/>

Your name or Company name<br/>
P.O. Box 88899<br/>
Hollywood, CA 90009<br/>

phone: (818) 555-5555<br/>
";
}
?>

<div class="wrap">
<div id="poststuff">

<div id="post-body">
<div id="post-body-content" style="width: 800px !important; float:left !important;">
<?php if ($updated != '') echo '<div class="updated"><p><strong>Settings Saved</strong></p></div>';?>

<div class="stuffbox">
<h3><label for="link_name"><?php _ex('Payment Gateway Settings:', 'rst') ?></label></h3>

<div class="inside">

    <form method="post" action="" name="rstform" class="rstform">
        <table class="form-table">
            <tbody>
			
			
	                                <tr valign="top">
                                    <th scope="row"><?php _e('Currency Symbol', 'rst-plugin');    ?></th>
                                    <td>
                                        <select name="rst_currencysymbol">
                                            <?php
                                            foreach ($symbol as $key => $value) {
                                                ?>
                                                <option
                                                    value="<?php echo $key; ?>" <?php if ($rst_options['rst_currencysymbol'] == $key) echo "selected"; ?>><?php echo $value?></option>

                                            <?php
                                            }
                                            ?>
                                        </select></td>
                                </tr>		
<?php

			$row_seats_price_option = '<select name="rst_currency" id="rst_currency">';

			foreach ($currencies as $currency) {

				$row_seats_price_option .= '<option value="'.$currency.'"'.(!empty($rst_options['rst_currency']) ? ($currency == $rst_options['rst_currency'] ? ' selected="selected"' : '') : '').'>'.$currency.'</option>';

			}

			$row_seats_price_option .= '</select>';




echo '<tr>

										<th>'.__('Currency', 'row_seats').':</th>

										<td>'.$row_seats_price_option.'</td>

									</tr>
									<tr>

										<th>'.__('Sender name', 'row_seats').':</th>

										<td>

											<input type="text" id="rst_from_name" name="rst_from_name" value="'.stripslashes(htmlspecialchars($rst_options['rst_from_name'], ENT_QUOTES)).'" class="widefat">

											<br /><em>'.__('Please enter sender name. All messages are sent using this name as "FROM:" header value.', 'row_seats').'</em>

										</td>

									</tr>

									<tr>

										<th>'.__('Sender e-mail', 'row_seats').':</th>

										<td>

											<input type="text" id="rst_from_email" name="rst_from_email" value="'.stripslashes(htmlspecialchars($rst_options['rst_from_email'], ENT_QUOTES)).'" class="widefat">

											<br /><em>'.__('Please enter sender e-mail. All messages are sent using this e-mail as "FROM:" header value.', 'row_seats').'</em>

										</td>

									</tr>
									<tr>

										<th>'.__('Successful payment e-mail subject for Payer', 'row_seats').':</th>

										<td>

											<input type="text" id="rst_success_email_subject" name="rst_success_email_subject" value="'.stripslashes(htmlspecialchars($rst_options['rst_success_email_subject'], ENT_QUOTES)).'" class="widefat">

											<br /><em>'.__('Notify your customers of successful charge. This is subject field of the message.', 'row_seats').'</em>

										</td>

									</tr><tr>

										<th>'.__('Successful payment e-mail body for Payer', 'row_seats').':</th>

										<td>

											<textarea id="rst_success_email_body" name="rst_success_email_body" style="height: 120px;" class="widefat">'.stripslashes(htmlspecialchars($rst_options['rst_success_email_body'], ENT_QUOTES)).'</textarea>

											<br /><em>'.__('This e-mail message is sent to your customers in case of successful and cleared payment. You can use the following keywords: {payer_name}, {payer_email}.', 'wpgc').'</em>

										</td>

									</tr>

									<tr>

										<th>'.__('Failed purchasing e-mail subject for Payer', 'row_seats').':</th>

										<td>

											<input type="text" id="rst_failed_email_subject" name="rst_failed_email_subject" value="'.stripslashes(htmlspecialchars($rst_options['rst_failed_email_subject'], ENT_QUOTES)).'" class="widefat">

											<br /><em>'.__('In case of pending, non-cleared or fake payment, your customers receive e-mail message about that. This is subject field of the message.', 'row_seats').'</em>

										</td>

									</tr>

									<tr>

										<th>'.__('Failed purchasing e-mail body for Payer', 'row_seats').':</th>

										<td>

											<textarea id="rst_failed_email_body" name="rst_failed_email_body" style="height: 120px;" class="widefat">'.stripslashes(htmlspecialchars($rst_options['rst_failed_email_body'], ENT_QUOTES)).'</textarea>

											<br /><em>'.__('This e-mail message is sent to your customers in case of pending, non-cleared or fake payment. You can use the following keywords: {payer_name}, {payer_email}, {payment_status}.', 'row_seats').'</em>

										</td>

									</tr>
									<tr>

										<td colspan=2 align=center bgcolor="#e19230"><font color=black>

											Payment Gateway other status

											<br /><em>'.__('Only applies to some payment gateways that have the following status : pending,cancelled,refund', 'row_seats').'</em>

										</font></td>

									</tr>									
									<tr>

										<th>'.__('Waiting Approval/Pending e-mail subject for Payer', 'row_seats').':</th>

										<td>

											<input type="text" id="rst_waitingapproval_email_subject" name="rst_waitingapproval_email_subject" value="'.stripslashes(htmlspecialchars($rst_options['rst_waitingapproval_email_subject'], ENT_QUOTES)).'" class="widefat">

											<br /><em>'.__('In case of Waiting Approval/Pending payment, your customers receive e-mail message about that. This is subject field of the message.', 'row_seats').'</em>

										</td>

									</tr>

									<tr>

										<th>'.__('Waiting Approval/Pending  e-mail body for Payer', 'row_seats').':</th>

										<td>

											<textarea id="rst_waitingapproval_email_body" name="rst_waitingapproval_email_body" style="height: 120px;" class="widefat">'.stripslashes(htmlspecialchars($rst_options['rst_waitingapproval_email_body'], ENT_QUOTES)).'</textarea>

											<br /><em>'.__('This e-mail message is sent to your customers in case of Waiting Approval/Pending payment. You can use the following keywords: {payer_name}, {payer_email}, {payment_status}.', 'row_seats').'</em>

										</td>

									</tr>
									
									
									<tr>

										<th>'.__('Cancelled e-mail subject for Payer', 'row_seats').':</th>

										<td>

											<input type="text" id="rst_cancelled_email_subject" name="rst_cancelled_email_subject" value="'.stripslashes(htmlspecialchars($rst_options['rst_cancelled_email_subject'], ENT_QUOTES)).'" class="widefat">

											<br /><em>'.__('In case of Cancelled payment, your customers receive e-mail message about that. This is subject field of the message.', 'row_seats').'</em>

										</td>

									</tr>

									<tr>

										<th>'.__('Cancelled  e-mail body for Payer', 'row_seats').':</th>

										<td>

											<textarea id="rst_cancelled_email_body" name="rst_cancelled_email_body" style="height: 120px;" class="widefat">'.stripslashes(htmlspecialchars($rst_options['rst_cancelled_email_body'], ENT_QUOTES)).'</textarea>

											<br /><em>'.__('This e-mail message is sent to your customers in case of Cancelled payment. You can use the following keywords: {payer_name}, {payer_email}, {payment_status}.', 'row_seats').'</em>

										</td>

									</tr>		
									
									
									<tr>

										<th>'.__('Refund e-mail subject for Payer', 'row_seats').':</th>

										<td>

											<input type="text" id="rst_refund_email_subject" name="rst_refund_email_subject" value="'.stripslashes(htmlspecialchars($rst_options['rst_refund_email_subject'], ENT_QUOTES)).'" class="widefat">

											<br /><em>'.__('In case of refund payment, your customers receive e-mail message about that. This is subject field of the message.', 'row_seats').'</em>

										</td>

									</tr>

									<tr>

										<th>'.__('Refund  e-mail body for Payer', 'row_seats').':</th>

										<td>

											<textarea id="rst_refund_email_body" name="rst_refund_email_body" style="height: 120px;" class="widefat">'.stripslashes(htmlspecialchars($rst_options['rst_refund_email_body'], ENT_QUOTES)).'</textarea>

											<br /><em>'.__('This e-mail message is sent to your customers in case of Refund payment. You can use the following keywords: {payer_name}, {payer_email}, {payment_status}.', 'row_seats').'</em>

										</td>

									</tr>																		
									
									<tr>

										<th>'.__('Offline payment pending notification e-mail subject for Payer', 'row_seats').':</th>

										<td>

											<input type="text" id="rst_pending_email_subject" name="rst_pending_email_subject" value="'.stripslashes(htmlspecialchars($rst_options['rst_pending_email_subject'], ENT_QUOTES)).'" class="widefat">

											<br /><em>'.__('In case of Offline payment, your customers receive e-mail message about that. This is subject field of the message.', 'row_seats').'</em>

										</td>

									</tr><tr>

										<th>'.__('Offline payment pending notification e-mail body for Payer', 'row_seats').':</th>

										<td>

											<textarea id="rst_pending_email_body" name="rst_pending_email_body" style="height: 120px;" class="widefat">'.stripslashes(htmlspecialchars($rst_options['rst_pending_email_body'], ENT_QUOTES)).'</textarea>

											<br /><em>'.__('In case of Offline payment, your customers receive e-mail message about that. This is message field of the message. You can use the following keywords: {payer_name}, {payer_email}, {payment_status}, {show_name}, {show_date}, {seats}, {amount}.', 'row_seats').'</em>

										</td>

									</tr><tr><td></td><td align=right>
																<div class="alignright">
								<input type="button" onclick="if (confirm(\'Are you sure?\')) {  window.location.href=\''.get_option('siteurl').'/wp-admin/admin.php?page=rst-pay-settings&resettodefault=yes\'}" class="row_seats_button button-primary" name="reset" value="'.__('Reset to default', 'row_seats').'">&nbsp;<input type="submit" class="row_seats_button button-primary" name="Submit" value="'.__('Save Settings', 'row_seats').'">
							</div><br><br>
									
									</td></tr>';	
	?>		
			
	            </tbody>


        </table>		
			<?php
			do_action('row_seats_echo_options_box');
		
			?>

    </form>

</div>
</div>


</div>
</div>


</div>
<div id="poststuff"
     style="float: left;width: 240px;padding-left: 10px;padding-top:0 !important;padding-top: 0px !important;">

    <div id="post-body">
        <div id="post-body-content">

        </div>
    </div>
</div>