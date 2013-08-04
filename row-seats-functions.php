<?php

/*
 * Adds the required columns to the existing table
 */
 
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

        mysql_query($sql);

    }
    if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb->rst_booking_seats_relation'") == $wpdb->rst_booking_seats_relation) {
        $sql = "ALTER TABLE " . $wpdb->rst_booking_seats_relation . " ADD COLUMN date_of_cancel TIMESTAMP  NULL";

        mysql_query($sql);

    }
    if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb->rst_booking_seats_relation'") == $wpdb->rst_booking_seats_relation) {
        $sql = "ALTER TABLE " . $wpdb->rst_booking_seats_relation . " ADD COLUMN b_seatid int(11)  NULL";

        mysql_query($sql);

    }
    if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb->rst_booking_seats_relation'") == $wpdb->rst_booking_seats_relation) {
        $sql = "ALTER TABLE " . $wpdb->rst_booking_seats_relation . " ADD COLUMN comments TEXT  NULL";

        mysql_query($sql);

    }
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
    add_submenu_page('rst-intro', __('Manage Seats', 'menu-test'), __('Manage Seats', 'menu-test'), $capability, 'rst-manage-seats', 'rst_manage_seats');
    add_submenu_page('rst-intro', __('Month Calender', 'menu-test'), __('Month Calender', 'menu-test'), $capability, 'rst-manage-seats-moncal', 'rst_manage_seats_moncalender');
    add_submenu_page('rst-intro', __('Reports', 'menu-test'), __('Reports', 'menu-test'), $capability, 'rst-reports', 'rst_reports');
}


/*
 * Generates seat chart html-content that is used to replace the shortcode
 */
function gettheseatchart($showid, $type = '')
{

global $screenspacing;
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

    $currenturl = curPageURL();

    $rst_paypal_options = get_option(RSTPLN_PPOPTIONS);

    $return_page = $rst_paypal_options['custom_return'];
    if ($return_page != '') {
        $currenturl = $return_page;
    }

    $rst_options = get_option(RSTPLN_OPTIONS);

    $stylecss = $rst_options['rst_theme'];
    if ($stylecss == '') {

        $stylecss = 'lite.css';

    }

    $rst_h_msg = $rst_options['rst_h_msg'];

    $paymentsuccess = "";

    if (isset($_POST) && $_POST['custom'] != '') {
        $paymentsuccess = succesrstsmessage();
    }

    $screenspacing=1;
    if($rst_options['rst_zoom'])
    {
    $screenspacing=$rst_options['rst_zoom'];
    }
    $seats = rst_seats_operations('list', '', $showid['id']);
    $divwidth = (($seats[0]['total_seats_per_row']) + 2) * 24;
    
    $divwidth=$divwidth * $rst_options['rst_zoom'];
    $mindivwidth = 640;
    if ($divwidth < $mindivwidth) {
        $divwidth = $mindivwidth;
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
<!--qwe-->
    <div style="width: <?php echo $divwidth;?>px; <?php echo $style; ?>">
    <script type="text/javascript">
        var RSTPLN_CKURL = '<?php echo RSTPLN_CKURL?>';
        var RSTAJAXURL = '<?php echo RSTPLN_URL?>ajax.php';
    </script>
    <script type='text/javascript' src='<?php echo RSTPLN_JALURL ?>jquery.alerts.js'></script>
    <script type='text/javascript' src='<?php echo RSTPLN_URL ?>js/jquery.blockUI.js'></script>
    <link rel="stylesheet" type="text/css" media="all" href="<?php echo RSTPLN_JALURL ?>jquery.alerts.css"/>
    <?php
    if ($type == 'offline') {
        $stylecss = 'lite.css';

    }
    ?>
   
    
    <link rel="stylesheet" type="text/css" media="all" href="<?php echo RSTPLN_CSSURL . $stylecss ?>"/>
    
    <style>


ul.r li {



    font-size: <?php echo (int)(10 * $rst_options['rst_zoom']);?>px !important;

    height: <?php echo (int)(24 * $rst_options['rst_zoom']);?>px !important;

    line-height: <?php echo (int)(24 * $rst_options['rst_zoom']);?>px !important;


    width: <?php echo (int)(21 * $rst_options['rst_zoom']);?>px !important;



  

}



</style> 

    <script type='text/javascript' src='<?php echo RSTPLN_COKURL ?>jquery.cookie.js'></script>
    <script type="text/javascript" src="<?php echo RSTPLN_IDLKURL ?>jquery.countdown.js"></script>
    <script type="text/javascript" src="<?php echo RSTPLN_IDLKURL ?>idle-timer.js"></script>
    <link rel="stylesheet" type="text/css" media="all" href="<?php echo RSTPLN_IDLKURL ?>jquery.countdown.css"/>

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
    if ($stopsonlinebooking == 0) {
        $stopsonlinebooking = '';
    } else {
        $stopsonlinebooking = " (Online booking will close $stopsonlinebooking $prefixh prior to engagement)";
    }
    if ($type == 'offline') {
        $stopsonlinebooking = '';
    }
    $currenttime = current_time('mysql', 0);
    if ($currentdate >= $eventdate1 && $type != 'offline') {

        echo   '<div><br/><strong>*Online Booking is Closed, We Appreciate Your Patronage!</strong></div></div>';

    } else {

        $html = '';


        // showcart ----->
        $html .= "<a name='show_top'></a><div class='showchart'><div style='width:".(int)(640 * $rst_options['rst_zoom'])."px; margin: 0 auto;'><div class='showchart paymentsucess'>$paymentsuccess</div>

        <div style='float:left;color:#f21313;'>YOUR CART WILL EMPTY IF IDLE FOR 7MIN.&nbsp;&nbsp;</div>

        <div id='defaultCountdown' ></div>

        </div><div id='eventdetails' >

        Event Name:$eventname <br/>

        Event Date & Time: $eventdate  $stopsonlinebooking.<br/>

        Venue:$venue<br/>
        <a style='margin-left:0px;' href='#view_cart'>View Cart</a> <div style='float:left'>

        </div></div></div>";
        // <----- showcart

        $html .= "<div id='showprview' class='localcss' align='center' style='width:100%; margin-left: auto;margin-right: auto;' >";

        ?>

        <script>
        var regtype = '<?php echo $type?>';
        var offlineAdmin = '<?php echo $offlineAdmin?>';

        ///////////////////////// idle time code /////////////////

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

                                jConfirm('<?php echo $rst_h_msg;?>', 'HandiCap Confirmation', function (r) {
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

        IdleTimer.subscribe("idle", function () {

            var status = document.getElementById("status");

            jQuery('#defaultCountdown').countdown('destroy');

            var austDay = new Date();

            austDay.setMinutes(austDay.getMinutes() + 7);

            jQuery('#defaultCountdown').countdown({until: austDay, onExpiry: liftOff, format: 'MS', compact: true,

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

        });

        IdleTimer.start(1000);
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

                message: 'Processing.....',
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

                                jConfirm('<?php echo $rst_h_msg;?>', 'HandiCap Confirmation', function (r) {

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

                                jConfirm('<?php echo $rst_h_msg;?>', 'HandiCap Confirmation', function (r) {

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

        jQuery(document).ready(function () {
            getupdatedshow("<?php echo $showid?>");
            var interval = setInterval(increment, 5000);
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
                        exit();
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

<?php }?>	

	

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
			
	

	

            if (id == 'placeorder') {

                document.getElementById('couponprogress').innerHTML = '<img src="<?php echo RSTPLN_URL;?>images/couponwait.gif" width="20" style="border:none !important;"/>';

                jQuery(".QTPopup").css('display', 'none');

                jQuery('#showprview').block({

                    message: 'Processing.....',
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

                                jConfirm('<?php echo $rst_h_msg;?>', 'HandiCap Confirmation', function (r) {

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

                                jConfirm('<?php echo $rst_h_msg;?>', 'HandiCap Confirmation', function (r) {

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
                jQuery('#couponapplybtn').html('Apply Member ID');
                jQuery('#couponcode').val('Enter Member ID');
            } else {
                jQuery('#couponapplybtn').html('Apply Coupon');
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
                $sql = "SELECT * FROM $wpdb->rst_seats st,$wpdb->rst_shows sh
                    WHERE
                    sh.id=st.show_id AND
                    st.row_name = '$row' AND
                    seattype<>'' AND
                    seatno = $seat AND
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

                            $sql = "UPDATE  $wpdb->rst_seats SET seattype='$originaltype',discount_price=$dicount,status='not blocked' WHERE show_id=" . $showid . " AND row_name='$row' AND seattype<>'' AND seatno=" . $seat;

                            $wpdb->query($sql);

                            rst_session_operations('deletebookingseat', $currentcart[$i]);

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

            $sql = "INSERT INTO $wpdb->rst_bookings (show_id,rst_session_id,paypal_vars,booking_time,booking_details,payment_status,name,email,phone,paypal_mode,ticket_no)
            VALUES ($showid, '$rst_session_id', '$paypal_vars',now(),'$booking_detail','$status','$username','$useremail','$phone','$papalmode','')";
            // ,rst_session_id,paypal_vars,booking_time,booking_details,payment_status,name,email,phone
            $wpdb->query($sql);

            return mysql_insert_id();

            break;

        case 'deleteall':

            for ($row = 0; $row < count($data); $row++) {
                $seatno = $data[$row]['seatno'];
                $showid = $data[$row]['show_id'];
                $rowname = $data[$row]['row_name'];
                $sql = "SELECT * FROM $wpdb->rst_seats st,$wpdb->rst_shows sh
                    WHERE
                    sh.id=st.show_id AND
                    st.row_name = '$rowname' AND
                    st.seatno = $seatno AND
                    st.seattype <> '' AND
                    sh.id =" . $showid;

                $seatdata = $wpdb->get_results($sql, ARRAY_A);
                $seatid = $seatdata[0]['seatid'];
                $seattype = $seatdata[0]['originaltype'];
                if ($seatid != "") {
                    $wpdb->query("UPDATE  $wpdb->rst_seats SET seattype='$seattype',status='not blocked' WHERE seatid=" . $seatid);

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

            $sql = "SELECT * FROM $wpdb->rst_seats st,$wpdb->rst_shows sh
                    WHERE
                    sh.id=st.show_id AND
                    st.row_name = '$row' AND
                    seattype<>'' AND
                    seatno = $seat AND
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

                        $sql = "UPDATE  $wpdb->rst_seats SET seattype='$originaltype',discount_price=$dicount,status='not blocked' WHERE show_id=" . $showid . " AND row_name='$row' AND seattype<>'' AND seatno=" . $seat;

                        $wpdb->query($sql);

                    }

                    $finalbookings[] = $bookings;
                    return $finalbookings;
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
                    seattype<>'' AND
                    seatno = $seat AND
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

                    if ($results[0]['seat_status'] != 'not blocked') {
                        return 'blocked';
                    }

                    $data = $wpdb->get_results($sql, ARRAY_A);

                    $data = $data[0];
                    $dicount = 0;

                    if ($data['seattype'] == 'Y') {

                        $dicount = $data['seat_price'];
                        $bookings['price'] = $dicount;
                        $sql = "UPDATE  $wpdb->rst_seats SET seattype='T',status='blocked',blocked_time=now() WHERE show_id=" . $showid . " AND row_name='$row' AND seattype<>'' AND seatno=" . $seat;

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
                        $sql = "UPDATE  $wpdb->rst_seats SET seattype='T',discount_price=$dicount,status='blocked',blocked_time=now() WHERE show_id=" . $showid . " AND row_name='$row' AND seattype<>'' AND seatno=" . $seat;

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

global $screenspacing;
    $rst_paypal_options = get_option(RSTPLN_PPOPTIONS);
    $rst_options = get_option(RSTPLN_OPTIONS);


    $symbol = $rst_paypal_options['currencysymbol'];

    $symbols = array(
        "0" => "$",
        "1" => "&pound;",
        "2" => "&euro;",
        "3" => "&#3647;",
        "4" => "&#8362;",
        "5" => "&yen;");

    $symbol = $symbols[$symbol];

    //$showid = $showid['id'];

    $data = getshowbyid($showid);
    $showorder = $data[0]['orient'];

    if ($showorder == 0 || $showorder == '') {
        $seats = rst_seats_operations('list', '', $showid);
    } else {
        $seats = rst_seats_operations('reverse', '', $showid);
    }

    $divwidth = (($seats[0]['total_seats_per_row']) + 2) * 24;
     $divwidth=$divwidth * $rst_options['rst_zoom'];

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

        $seats_avail_per_row = unserialize($data['seats_avail_per_row']);

        $otherscart = false;

        if ($data['seattype'] == 'N') {

            $html .= '<li class="un showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '_' . $seatno . '" title="Seat ' . $rowname . ($seatno) . ' Unavailable" rel="' . $data['seattype'] . '"></li>';

        } else if ($data['seattype'] == 'Y') {

            $html .= '<li class="notbooked showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '_' . $seatno . '" title="Seat ' . $rowname . ($seatno) . ' Price ' . $symbol . $seatcost . ' Available" rel="' . $data['seattype'] . '">' . ($seatno) . '</li>';

        } else if ($data['seattype'] == 'H') {

            $html .= '<li class="handy showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '_' . $seatno . '" title="Seat ' . $rowname . ($seatno) . ' Handicap Accomodation ' . $symbol . $dicount . ' " rel="' . $data['seattype'] . '">' . $seatno . '</li>';

        } else if ($data['seattype'] == 'B') {

            $html .= '<li class="booked showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '_' . $seatno . '" title="Seat ' . $rowname . ($seatno) . ' Booked" rel="' . $data['seattype'] . '">' . $seatno . '</li>';

        } else if ($data['seattype'] == 'T') {

            for ($o = 0; $o < count($rst_bookings); $o++) {

                if ($rst_bookings[$o]['row_name'] == $rowname && $rst_bookings[$o]['seatno'] == $seatno) {

                    $otherscart = true;

                }

            }

            if ($otherscart) {

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

    $html .= '<li class="ltr">' . $nextrow . '</li></ul></div>';

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

                    $sql = "INSERT INTO $wpdb->rst_seats (show_id,row_name,total_seats_per_row,seatno,seattype,originaltype,seat_price,discount_price,status,created_date,mod_date,mod_by)
                    VALUES ($showid, '$name', '$total_seats_per_row',$seatno,'$seattype','$seattype',$seat_price,$discount_price,'not blocked','$curdate','$curdate','$modby')";

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

global $screenspacing;

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

    $symbols = array(
        "0" => "$",
        "1" => "&pound;",
        "2" => "&euro;",
        "3" => "&#3647;",
        "4" => "&#8362;",
        "5" => "&yen;");

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

    $symbols = array(
        "0" => "$",
        "1" => "&pound;",
        "2" => "&euro;",
        "3" => "&#3647;",
        "4" => "&#8362;",
        "5" => "&yen;");

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

    <div class="QTPopupCntnr" style="width: 750px;">

    <div class="gpBdrLeftTop"></div>

    <div class="gpBdrRightTop"></div>

    <div class="gpBdrTop"></div>

    <div class="gpBdrLeft">

    <div class="gpBdrRight">

    <div class="caption">

        Booking Details

    </div>

    <a href="#" class="closeBtn" title="Close"></a>

    <div class="checkoutcontent">

    <form method='POST' action='<?php echo $paypal_url; ?>' enctype='multipart/form' name="checkoutform">

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

            $description = $rst_bookings;

            $total = 0;

            $totalseats = 0;
	    $totalspecialpricing=$rst_options['rst_special_pricing_count']+1;
	    if(!$rst_options['rst_special_pricing_count'])
	    $totalspecialpricing=1;

            for ($i = 0; $i < count($rst_bookings); $i++) {

                $rst_booking = $rst_bookings[$i];
				//creating special price dropdown
				if($rst_options['rst_enable_special_pricing']=="on" and row_seats_special_pricing_verification())
				{			
					$special_pricing_array=array();
					for($j=1;$j<$totalspecialpricing;$j++){
						if($rst_options['rst_special_pricing_title'.$j] && $rst_options['rst_special_pricing_price'.$j])
						{
							$flat_rate=$rst_options['rst_special_pricing_flat_rate'.$j];
							$finalprice=$rst_options['rst_special_pricing_price'.$j];
							if($flat_rate=="on")
							{
								$finalprice=$finalprice;
							}else{
								$finalprice=$rst_booking['price']-($rst_booking['price']*($finalprice/100));
							}
							$finalprice = number_format($finalprice, 2, '.', '');
							$special_pricing_array[$rst_options['rst_special_pricing_title'.$j]]=$finalprice;
						}
					}
				}
								

								?>


                <tr>
                    <td width=50%">Seat:<?php echo $rst_booking['row_name'] . $rst_booking['seatno'];?>-Cost:</td>
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
                <td width="50%"><span style="color: maroon;font-size: larger;">Total:</span></td>
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

                <td width="50%"><span style="color: maroon;font-size: larger;"><strong>Grand:</strong></span></td>

                <td width="50%"><span style="color: maroon;font-size: larger;"><?php echo $symbol;?></span><span
                        style="color: maroon;font-size: larger;" id="Grandtotal"><?php echo $gtotal;?></span></td>

            </tr>

        </table>

    </td>
    <td class="tabright" width="60%" style="border-left:1px solid #e7e7e7 !important; ">

        <table>
            <tr>
                <td colspan='2'><label for='contact-name'><span class='reqa'>*</span> Name:</label>

                    <input type='text' id='contact_name' class='contact-input' name='contact_name'
                           value=''/></td>
            </tr>

            <tr>
                <td colspan='2'><label for='contact-email'><span class='reqa'>*</span> Email:</label>

                    <input type='text' id='contact_email' class='contact-input' name='contact_email'
                           value=''/></td>
            </tr>

            <tr>
                <td colspan='2'><label for='contact-email'><span class='reqa'>*</span> Phone:</label>

                    <input type='text' id='contact_phone' class='contact-input' name='contact_phone'
                           value=''/></td>
            </tr>

            <tr>
                <td colspan='2'>
                    <div><input type='checkbox' id='rstterms' class='contact-input' name='rstterms'/>

                        <label class='termsclass'><span class='reqa'>*</span> I Agree Terms &amp;
                            Conditions:</label><br/><label
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
                    <label class='termsclass'> I am a VIP Member:</label></td>
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
                <td colspan='2'> &nbsp;</td>
            </tr>
            <!--added class by mahesh 20120512-->
            <tr>
                <td colspan='2'><a href="javascript:void(0);" onclick="savecheckoutdata('placeorder')"
                                   class='srbutton srbutton-css'>Place Order</a></td>
            </tr>

            <input type="hidden" name="cmd" value="_xclick"/>

            <input type="hidden" name="notify_url" value="<?php echo $notifyURL; ?>"/>

            <input type="hidden" name="return" id="return" value="<?php echo $return_page ?>"/>

            <input type="hidden" name="business" value="<?php echo $paypal_id; ?>"/>

            <input type="hidden" name="amount" id="amount" value="<?php echo esc_attr($gtotal); ?>"/>


            <input type="hidden" id="item_name" name="item_name" value="Seats Booking"/>

            <input type="hidden" name="custom" id="custom" value=""/>

            <input type="hidden" name="no_shipping" value="0"/>
            <input type="hidden" name="currency_code" value="<?php echo $currency ?>"/>


        </table>

    </td>
    </tr>

    </table>


    </form>

    <input type="hidden" name="appliedcoupon" id="appliedcoupon" value=""/>

    <input type="hidden" name="totalbackup" id="totalbackup" value="<?php echo esc_attr($gtotal); ?>"/>

    <input type="hidden" name="statusofcouponapply" id="statusofcouponapply" value=""/>

    <input type="hidden" name="totalrecords" id="totalrecords" value="<?php echo count($bookings);?>"/>
    
    <input type="hidden" name="coupondiscount" id="coupondiscount" value=""/>
    <input type="hidden" name="rst_fees" id="rst_fees" value="<?php echo esc_attr($sercharge); ?>"/>
	<input type="hidden" name="fee_name" id="fee_name" value="<?php echo esc_attr($fee_name); ?>"/>

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

    $divwidth = (($seats[0]['total_seats_per_row']) + 2) * 24;
     $divwidth=$divwidth * $rst_options['rst_zoom'];
    $showname = $data[0]['show_name'];

    $html .= '';

    $html .= '<div id="currentcart"><div style="width: '.(int)(640 * $rst_options['rst_zoom']).'px;">

        <span class="notbooked showseats" ></span> <span class="show-text">Available </span>

        <span class="blocked showseats" ></span> <span class="show-text">In Your Cart  </span>

       <span class="un showseats" ></span> <span class="show-text">In Other&#39;s Cart</span>

       <span class="booked showseats" ></span> <span class="show-text">Booked </span>

        <span class="handy showseats" ></span> <span class="show-text">Handicap Accomodation</span><br/><br/>';

    $html .= '<div class="stage-hdng"></div></div></div>';

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

    $html .= '<div class="seatplan" id="showid_' . $showid . '" style="width:' . $divwidth . 'px;">';

    $nextrow = '';

    $dicount = 0;

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

        $seats_avail_per_row = unserialize($data['seats_avail_per_row']);

        $otherscart = false;

        if ($data['seattype'] == 'N') {

            $html .= '<li class="un showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '_' . $seatno . '" title="Seat ' . $rowname . ($seatno) . ' Unavailable" rel="' . $data['seattype'] . '"></li>';

        } else if ($data['seattype'] == 'Y') {

            $html .= '<li class="notbooked showseats" id="' . $showname . '_' . $showid . '_' . $rowname . '_' . $seatno . '_' . $seatno . '" title="Seat ' . $rowname . ($seatno) . ' Price ' . $symbol . $seatcost . ' Available" rel="' . $data['seattype'] . '">' . ($seatno) . '</li>';

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

    // cartitems ----->

    $html .= '<div id="gap" style="clear:both;float:left;">&nbsp;</div><a NAME="view_cart"></a><div class="cartitems" style="width:' . $divwidth . 'px; border:1px solid;border-radius:5px;box-shadow: 5px 5px 2px #888888;"><div class="cart-hdng"align="center" style="border:0px solid;border-radius:5px;"><strong>Items in Cart</strong> <span style="float:right; width: 48px;"><a href="#show_top"><strong style="vertical-align: middle; float:left; color:#000;">Up</strong><img style="margin: 3px 0 0; float:right;" src="' . RSTPLN_URL . 'images/up.png" alt="Up" title="Up" /></a></span></div><table style="color:#51020b;">';

    if ($rst_bookings != '' && count($rst_bookings) > 0) {

        $total = 0;

        for ($i = 0; $i < count($rst_bookings); $i++) {

            $rst_booking = $rst_bookings[$i];

            $rst_booking['price'] = number_format($rst_booking['price'], 2, '.', '');

            $html .= '<tr><td>' . $rst_booking['row_name'] . ($rst_booking['seatno']) . ' Added - </td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>Cost:' . $symbol . $rst_booking['price'] . '</td><td><img src="' . RSTPLN_URL . 'images/delete.png" class="deleteitem" id="' . $showname . '_' . $showid . '_' . $rst_booking['row_name'] . '_' . ($rst_booking['seatno']) . '" onclick="deleteitem(this);" style="cursor:pointer;border:none!important"/></td></tr>';

            $total = $total + $rst_booking['price'];

        }

        $html .= '<tr><td></td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td class="total_price">Total:' . $symbol . number_format($total, 2, '.', '') . '</td></tr><tr><td><a class="contact rsbutton" href="javascript:void(0);" >Checkout</a></td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td><a class="rsbutton" href="javascript:void(0);"  id="' . $sessiondata . '" onclick="deleteitemall(this);">Clear Cart</a></td></tr></table></div>';

    } else {

        $html .= '<tr><td><img src="' . RSTPLN_URL . 'images/emptycart.png" style="border:none !important;"/></td></tr></table></div>';

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

    if ($results = $wpdb->get_results($sql, ARRAY_A)) {

        $bookedtickets = $wpdb->get_results($sql, ARRAY_A);

    }

    return $bookedtickets;

}


/*
 * Generates seat chart html-content that is used on backend
 */
function gettheadminseatchat($showid)
{

global $screenspacing;
    $rst_paypal_options = get_option(RSTPLN_PPOPTIONS);
    $symbol = $rst_paypal_options['currencysymbol'];
    $symbols = array(
        "0" => "$",
        "1" => "&pound;",
        "2" => "&euro;",
        "3" => "&#3647;",
        "4" => "&#8362;",
        "5" => "&yen;");
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

    $divwidth = (($seats[0]['total_seats_per_row']) + 2) * 24;
     //$divwidth=$divwidth * $rst_options['rst_zoom'];
    $showname = $data[0]['show_name'];

    $html = '';

    $html .= "<h2>" . __('Preview of the Show:' . $showname, 'rst') . "</h2>";

    $html .= apply_filters('pricing_per_seat_message_filter', '');

    $html .= '<div id="currentcart" style="width:600px;"><span class="notbooked showseats" >&nbsp;&nbsp;&nbsp;&nbsp;</span> Available &nbsp;&nbsp;

        <span class="blocked showseats" >&nbsp;&nbsp;&nbsp;&nbsp;</span> In Your Cart &nbsp;&nbsp;

        <span class="booked showseats" >&nbsp;&nbsp;&nbsp;&nbsp;</span> Booked &nbsp;&nbsp;

        <span class="un showseats" >&nbsp;&nbsp;&nbsp;&nbsp;</span> In Other&#39;s Cart &nbsp;&nbsp;

        <span class="handy showseats" >&nbsp;&nbsp;&nbsp;&nbsp;</span> Handicap Accomodation&nbsp;&nbsp;<br/><br/>

        <div id="stageshow"></div></div><div class="clear"></div>';

    $html .= '<div class="seatplan" id="showid_' . $showid . '" style="width:' . $divwidth . 'px;">';

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

            $wpdb->query("INSERT INTO $wpdb->rst_customer_session (rst_session_id,show_id,rowname,seatno,price,session_time,status) VALUES ('$sesid', $showid, '$rowname',$seatno,$price,now(),'blocked')");

            return true;

            break;

        case 'deletebookingseat':

            $showid = $data['show_id'];

            $sessiontime = date('Y-m-d H:i:s');

            $rowname = $data['row_name'];

            $seatno = $data['seatno'];

            $sesid = session_id();

            $wpdb->query("DELETE FROM $wpdb->rst_customer_session WHRE rst_session_id='$sesid' AND show_id = $showid AND  rowname='$rowname' AND seatno=$seatno");

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

    $sql = "SELECT * FROM $wpdb->rst_bookings where booking_id=" . $booking_id;

    if ($results = $wpdb->get_results($sql, ARRAY_A)) {

        $booking_details = $wpdb->get_results($sql, ARRAY_A);

        $data = $booking_details[0];

        $booking_details = $booking_details[0]['booking_details'];

        $booking_details = unserialize($booking_details);

        for ($row = 0; $row < count($booking_details); $row++) {

            $seats = $booking_details[$row]['seatno'];

            $showid = $booking_details[$row]['show_id'];

            $rowname = $booking_details[$row]['row_name'];

            $price = $booking_details[$row]['price'];

            $sql = "SELECT * FROM $wpdb->rst_seats st,$wpdb->rst_shows sh

                    WHERE

                    sh.id=st.show_id AND

                    st.row_name = '$rowname' AND

                    st.seatno = $seats AND

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

            $sql = "INSERT INTO $wpdb->rst_booking_seats_relation (ticket_no,ticket_seat_no,booking_id,show_id,b_seatid,total_paid,txn_id,seat_cost)

            VALUES ('$ticketno', '$ticket_seat_no', $booking_id,$showid,$seatid,$totalpaid,'$txn_id',$price)";

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

    exit();

}


/*
 * Sends emails to the customer and to the admin
 */
function sendrstmail($data, $txn_id)
{
    $rst_options = get_option(RSTPLN_OPTIONS);
    $stopadminemails = $rst_options['rst_disable_admin_email'];
    $rst_options = get_option(RSTPLN_OPTIONS);
    $rst_paypal_options = get_option(RSTPLN_PPOPTIONS);

    $symbol = $rst_paypal_options['currencysymbol'];

    $symbols = array(
        "0" => "$",
        "1" => "&pound;",
        "2" => "&euro;",
        "3" => "&#3647;",
        "4" => "&#8362;",
        "5" => "&yen;");
    $symbol = $symbols[$symbol];
    $useremailtemp = $rst_options['rst_etemp'];

    $adminemailtemp = $rst_options['rst_adminetemp'];

    $search = array("<username>", "<showname>", "<showdate>", "<bookedseats>", "<downloadlink>");
    $downloadlink = RSTTICKETDOWNURL . '?id=' . $txn_id;
    $dlink = 'Please click <a href="' . $downloadlink . '">here</a> to download your tickets';
    $adminsearch = array("<blogname>", "<username>", "<showname>", "<showdate>", "<bookedseats>", "<availableseats>");

    $showid = $data[0]['show_id'];

    $availableseats = getavailableseatsbyshow($showid);

    $username = $data[0]['name'];

    $useremail = $data[0]['email'];

    $showdate = $data[0]['show_date'];

    $showdate = date('F j, Y', strtotime($showdate));


    $showname = $data[0]['show_name'];

    $seatdetails = '';

    for ($i = 0; $i < count($data); $i++) {

        $seatdetails .= $data[$i]['ticket_seat_no'] . ' - ' . $symbol . $data[$i]['seat_cost'] . '<br/>';

    }

    $replace = array($username, $showname, $showdate, $seatdetails, $dlink);

    $blogname = get_option('blogname');

    $adminreplace = array($blogname, $username, $showname, $showdate, $seatdetails, $availableseats);

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

    if (
        mail($recipientAddr, $subjectStr, $mailBodyText, $headers)
    ) {

        //echo "<p>Mail has been sent with attachment ($fileName) !</p>";

    } else {

        //echo '<p>Mail sending failed with attachment ($fileName) !</p>';

    }

    if ($stopadminemails != 'off') {
        mail($recipientAddradmin, $subjectStradmin, $mailBodyTextadmin, $headers);
        //echo "<p>Mail has been sent with attachment ($fileName) !</p>";

    } else {

        // echo '<p>Mail sending failed with attachment ($fileName) !</p>';

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


function delete_seats($action, $finalseats, $showid)
{
    global $wpdb;
    return $wpdb->query("DELETE FROM $wpdb->rst_seats WHERE show_id='$showid'");
}


?>