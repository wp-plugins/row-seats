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


$updated = '';
$validmsg = '';
$validated = 'no';
if (isset($_POST) && $_POST['Submit'] == 'Save Settings') {
//print "Inside";
    update_option(RSTPLN_OPTIONS, $_POST);
	//do_action('row_seats_save_options');
    $updated = 'yes';
}
if (isset($_POST) && $_POST['l_action'] == 'pleasevalidate') {



    ?>
    <script>
        window.location = '<?php echo get_option('siteurl')?>/wp-admin/admin.php?page=rst-settings';
    </script>
<?php

}

$rst_options = get_option(RSTPLN_OPTIONS);
if ($rst_options['rst_adminetemp'] == '') {
    $rst_options['rst_adminetemp'] = "Dear [blogname],
The following seats were purchased for [showname] for [showdate]:<br/>
[bookedseats]
There are [availableseats] seats left to book full house.  <br/>
<br>
You can check them in your admin panel and download the csv for the corresponding event.<br/>
[blogname]
If you have any questions, please let me know.<br>
Regards,<br/>
Administration";
}
if ($rst_options['rst_etemp'] == '') {
    $rst_options['rst_etemp'] = "Dear [username],

You have successfully booked for [showname] for <showdate>.  The following seats were purchased:<br/><br/>
[bookedseats]
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


if ($rst_options['rst_idle_message'] == '') {
    $rst_options['rst_idle_message'] = "{showname} : Sorry this page is idle for long. To continue <a href=\"{returnurl}\">click here</a>";
}





?>

<div class="wrap">
<div id="poststuff">

<div id="post-body">
<div id="post-body-content" style="width: 800px !important; float:left !important;">
<?php if ($updated != '') echo '<div class="updated"><p><strong>Settings Saved</strong></p></div>';?>

<div class="stuffbox">
<h3><label for="link_name"><?php _ex('Show Chart Settings:', 'rst') ?></label></h3>

<div class="inside">

    <form method="post" action="" name="rstform" class="rstform">
        <table class="form-table">
            <tbody>


<!--            admins ----- -->
<?php echo apply_filters('rst_user_roles_filter',''); ?>
<!-- ----- admins -->



            <tr valign="top">
                <th scope="row"><label for="name"><?php echo __('Email', 'rst'); ?><span
                            style="color: red;">*</span></label>
                </th>
                <td>
                    <input type="text" id="rst_email" name="rst_email" class="regular-text"
                           value="<?php echo stripslashes($rst_options['rst_email']); ?>"/>
                </td>
            </tr>
			
            <tr valign="top">
                <th scope="row"><label for="name"><?php echo __('Send Copy of order emails to (Emails seperated by comma)', 'rst'); ?><span
                            style="color: red;">*</span></label>
                </th>
                <td>
                    <input type="text" id="rst_email_copy" name="rst_email_copy" class="regular-text"
                           value="<?php echo stripslashes($rst_options['rst_email_copy']); ?>"/>
                </td>
            </tr>			
            <tr valign="top">
                <th scope="row"><label for="name"><?php echo __('Ticket Prefix', 'rst'); ?><span
                            style="color: red;">*</span></label>
                </th>
                <td>
                    <input type="text" id="rst_ticket_prefix" name="rst_ticket_prefix" class="regular-text"
                           value="<?php echo stripslashes($rst_options['rst_ticket_prefix']); ?>"/>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="name"><?php echo __('Handicap Discount in (%)', 'rst'); ?><span
                            style="color: red;">*</span></label>
                </th>
                <td>
                    <input type="text" id="rst_h_disc" name="rst_h_disc" class="regular-text"
                           value="<?php echo stripslashes($rst_options['rst_h_disc']); ?>"/>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="name"><?php echo __('HandiCap Alert Message', 'rst'); ?><span
                            style="color: red;">*</span></label>
                </th>
                <td>
                    <textarea class="large-text code" id="rst_h_msg" cols="40" rows="5"
                              name="rst_h_msg"><?php echo stripslashes($rst_options['rst_h_msg']); ?></textarea></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label
                        for="name"><?php echo __('Cart refresh time : (in seconds)<br/> (if left blank the default is 5 second.)', 'rst'); ?>
                        <span style="color: red;">*</span></label>
                </th>
                <td>
                    <input type="text" id="rst_close_bookings" name="rst_refresh_time"
                           value="<?php echo stripslashes($rst_options['rst_refresh_time']); ?>"/></td>
            </tr>			
            <tr valign="top">
                <th scope="row"><label
                        for="name"><?php echo __('Close bookings before show starts : (in mins)<br/> (if left blank the default is 60 min.)', 'rst'); ?>
                        <span style="color: red;">*</span></label>
                </th>
                <td>
                    <input type="text" id="rst_close_bookings" name="rst_close_bookings"
                           value="<?php echo stripslashes($rst_options['rst_close_bookings']); ?>"/></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="quote"><?php echo __('Terms & Conditions', 'rst'); ?><span
                            style="color: red;">*</span></label>
                </th>
                <td>
                    <textarea class="large-text code" id="rst_tandc" cols="40" rows="5"
                              name="rst_tandc"><?php echo stripslashes($rst_options['rst_tandc']); ?></textarea>
                </td>
            </tr>
			
			
			
            <tr valign="top">
                <th scope="row"><label
                        for="name"><?php echo __('Close page if idle for : (in mins)<br/> (if left blank the default is 10 min. <br/>The placed time cannot be the same as Front-Countdown timer)', 'rst'); ?>
                        <span style="color: red;">*</span></label>
                </th>
                <td>
                    <input type="text" id="rst_idle_time" name="rst_idle_time"
                           value="<?php echo stripslashes($rst_options['rst_idle_time']); ?>"/></td>
            </tr>
			
            <tr valign="top">
                <th scope="row"><label
                        for="name"><?php echo __('Front-Countdown timer: (in mins)<br/> (if left blank the default is 7 min.)', 'rst'); ?>
                        <span style="color: red;">*</span></label>
                </th>
                <td>
                    <input type="text" id="rst_idle_clear_cart" name="rst_idle_clear_cart"
                           value="<?php echo stripslashes($rst_options['rst_idle_clear_cart']); ?>"/></td>
            </tr>	

			
            <tr valign="top">
                <th scope="row"><label for="quote"><?php echo __('Message displayed if page is idle for more than the above set value. you can use short code {showname},{showdate}, {returnurl}', 'rst'); ?><span
                            style="color: red;">*</span></label>
                </th>
                <td>
                    <textarea class="large-text code" id="rst_idle_message" cols="40" rows="5"
                              name="rst_idle_message"><?php echo stripslashes($rst_options['rst_idle_message']); ?></textarea>
                </td>
            </tr>			
			
			
			
			
            <tr valign="top">
                <th scope="row"><label for="name"><?php echo __('User Email Template', 'rst'); ?></label>
                </th>
                <td>
                    <!--<textarea class="large-text code" id="rst_etemp" cols="40" rows="15"
                              name="rst_etemp"><?php echo stripslashes($rst_options['rst_etemp']); ?></textarea>-->
							  
<?php wp_editor( stripslashes($rst_options['rst_etemp']), 'rst_etemp', $settings = array('textarea_name' => rst_etemp,'tinymce'=>true) ); ?>

							  
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="name"><?php echo __('User Email Subject', 'rst'); ?><span
                            style="color: red;">*</span></label>
                </th>
                <td>
                    <input type="text" id="rst_esub" name="rst_esub" class="regular-text"
                           value="<?php echo stripslashes($rst_options['rst_esub']); ?>"/>
                </td>
            </tr>


<!-- Offline Reservation Email Subject ----- -->
<?php echo apply_filters('offline_reservation_email_filter','', $rst_options); ?>
<!-- ----- Offline Reservation Email Subject -->


            <tr valign="top">
                <th scope="row"><label for="name"><?php echo __('Admin Email Template', 'rst'); ?></label>
                </th>
                <td>
                    <!--<textarea class="large-text code" id="rst_adminetemp" cols="40" rows="15"
                              name="rst_adminetemp"><?php echo stripslashes($rst_options['rst_adminetemp']); ?></textarea>-->
							  
<?php wp_editor( stripslashes($rst_options['rst_adminetemp']), 'rst_adminetemp', $settings = array('textarea_name' => rst_adminetemp) ); ?>

							  
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="name"><?php echo __('Disable Admin Email Notification', 'rst'); ?></label>
                </th>
                <td>
                    <input name="rst_disable_admin_email" id="rst_disable_admin_email" type="checkbox"
                           value="off" <?php  if ($rst_options['rst_disable_admin_email'] == 'off') echo 'checked'; ?>/>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label
                        for="name"><?php echo __('Minutes to Wait for Release the blocked seats', 'rst'); ?></label>
                </th>
                <td>
                    <input type="text" id="rst_release_min" name="rst_release_min" class="regular-text"
                           value="<?php echo stripslashes($rst_options['rst_release_min']); ?>"/>
                </td>
            </tr>
<!--            <tr valign="top">-->
<!--                <th scope="row"><label for="name">--><?php //echo __('Enable Coupons', 'rst'); ?><!--</label>-->
<!--                </th>-->
<!--                <td>-->
<!---->
<!--                    <input type="checkbox" id="rst_enab_coupon" name="rst_enab_coupon"-->
<!--                           value="on" --><?php //echo ($rst_options['rst_enab_coupon'] == 'on') ? 'checked' : ''; ?><!--/>-->
<!--                </td>-->
<!--            </tr>-->
            <tr valign="top">
                <th scope="row"><label for="name"><?php echo __('Select theme', 'rst'); ?></label>
                </th>
                <td>
                    <select name="rst_theme" id="rst_theme">
                        <option value="lite.css" <?php  if ($rst_options['rst_theme'] == 'lite.css') echo 'selected';?>>
                            Lite
                        </option>
                        <option value="dark.css" <?php  if ($rst_options['rst_theme'] == 'dark.css') echo 'selected';?>>
                            Dark
                        </option>
                        <option
                            value="lite_yellow.css" <?php  if ($rst_options['rst_theme'] == 'lite_yellow.css') echo 'selected';?>>
                            Yellow
                        </option>
                        <option
                            value="lite_purple.css" <?php  if ($rst_options['rst_theme'] == 'lite_purple.css') echo 'selected';?>>
                            Purple
                        </option>
                        <option
                            value="lite_pink.css" <?php  if ($rst_options['rst_theme'] == 'lite_pink.css') echo 'selected';?>>
                            Pink
                        </option>
                        <option
                            value="lite_gray.css" <?php  if ($rst_options['rst_theme'] == 'lite_gray.css') echo 'selected';?>>
                            Gray
                        </option>

                    </select>
                </td>
            </tr>

	    
	    
	 <tr valign="top">
                <th scope="row"><label for="name"><?php echo __('Zoom seats settings', 'rst'); ?></label>
                </th>
                <td>
                    <select name="rst_zoom" id="rst_zoom">
                        <option value="1" <?php  if ($rst_options['rst_zoom'] == '1') echo 'selected';?>>
                            100%
                        </option>
                        <option value="0.9" <?php  if ($rst_options['rst_zoom'] == '0.9') echo 'selected';?>>
                            90%
                        </option>
                        <option value="0.8" <?php  if ($rst_options['rst_zoom'] == '0.8') echo 'selected';?>>
                            80%
                        </option>
                        <option value="0.7" <?php  if ($rst_options['rst_zoom'] == '0.7') echo 'selected';?>>
                            70%
                        </option>
                    </select>
                </td>
            </tr>    
			
	 <tr valign="top">
            <tr valign="top">
                <th scope="row"><label
                        for="name"><?php echo __('Fix the Stage/Shopping cart width in px(this will override the Zoom option above). This is a global setting it affects all seat charts. The option is only useful for small seat charts aesthetics.', 'rst'); ?></label>
                </th>
                <td>
                    <input type="text" id="rst_fixed_width" name="rst_fixed_width" 
                           value="<?php echo stripslashes($rst_options['rst_fixed_width']); ?>"/>px
                </td>
            </tr>

			
			
<tr valign="top">
    <th scope="row"><label for="rst_alignment"><?php echo __('Alignment', 'rst'); ?></label>
    </th>
    <td>
        <select name="rst_alignment" id="rst_alignment">
            <option value="1" <?php if ($rst_options['rst_alignment'] == '1') echo 'selected'; ?>>
                Center
            </option>
            <option value="2" <?php if ($rst_options['rst_alignment'] == '2') echo 'selected'; ?>>
                Left
            </option>
            <option
                value="3" <?php if ($rst_options['rst_alignment'] == '3') echo 'selected'; ?>>
                Right
            </option>

        </select>
    </td>
</tr>
<tr valign="top">
    <th scope="row"><label for="rst_alignment"><?php echo __('Stage Location', 'rst'); ?></label>
    </th>
    <td>
        <select name="rst_stage_alignment" id="rst_stage_alignment">
            <option value="top" <?php if ($rst_options['rst_stage_alignment'] == 'top') echo 'selected'; ?>>
                Top
            </option>
            <option value="bottom" <?php if ($rst_options['rst_stage_alignment'] == 'bottom') echo 'selected'; ?>>
                Bottom
            </option>
            <option
                value="disable" <?php if ($rst_options['rst_stage_alignment'] == 'disable') echo 'selected'; ?>>
                Disable
            </option>

        </select>
    </td>
</tr>

<tr valign="top">
    <th scope="row"><label for="rst_alignment"><?php echo __('Display Wheelchair Access', 'rst'); ?></label>
    </th>
    <td>
        <select name="rst_seat_help" id="rst_seat_help">
            <option value="enable" <?php if ($rst_options['rst_seat_help'] == 'enable') echo 'selected'; ?>>
                Enable
            </option>
            <option
                value="disable" <?php if ($rst_options['rst_seat_help'] == 'disable') echo 'selected'; ?>>
                Disable
            </option>

        </select>
    </td>
</tr>
<!-- Special Pricing Email Subject ----- --><?php //echo apply_filters('row_seats_generel_admission_settings','', $rst_options); ?><!-- ----- generel_admission -->
<!-- Special Pricing Email Subject ----- --><?php echo apply_filters('special_pricing_email_filter','', $rst_options); ?><!-- ----- Special Pricing Email Subject -->
<!-- Seat Restriction ----- --><?php echo apply_filters('row_seats_seat_restriction_admin_settings_filter','', $rst_options); ?><!-- ----- Seat Restriction -->

            <!--<tr valign="top">
                <th scope="row"><label for="name"><?php echo __('Disable JQuery', 'rst'); ?></label>
                </th>
                <td>
                    <input name="rst_enable_jquery" id="rst_enable_jquery" type="checkbox"
                           value="off" <?php  if ($rst_options['rst_enable_jquery'] == 'off') echo 'checked'; ?>/>
                </td>
            </tr>-->
            <tr valign="top">
                <th scope="row">
                </th>
                <td>
                    <p style="float:right;">
                        <input type="submit" value="Save Settings" class="button-primary" name="Submit"/>
                    </p></td>
            </tr>

			
            </tbody>


        </table>
<?php echo apply_filters('rowseats-userregistration-settings-hidden',''); ?>
<?php echo apply_filters('rowseats-generel_admission-settings-hidden',''); ?>

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
            <div class="stuffbox">
                <h3><label for="link_name"><?php _ex('ROW SEATS PRODUCTS', 'rst') ?></label></h3>

                <div class="inside">

                    <p><a href="http://www.rowseatsplugin.com/" target="_blank"><img
                                style="cursor:pointer;"
                                src="<?php echo RSTPLN_URL ?>images/multi-language.jpg"/></a>
                        </a></p>                    
                    <p><a href="http://www.rowseatsplugin.com/row-seats-add-on-qr-code-tickets" target="_blank"><img
                                style="cursor:pointer;"
                                src="<?php echo RSTPLN_URL ?>images/row-seats-qr-tix-ad.jpg"/></a>
                        </a></p>
                    <p><a href="http://www.rowseatsplugin.com/" target="_blank"><img
                                style="cursor:pointer;"
                                src="<?php echo RSTPLN_URL ?>images/row-seats-cortesia-theme.jpg"/></a>
                        </a></p>
                    </p>


                </div>
            </div>
        </div>
    </div>
</div>