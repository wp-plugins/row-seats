<?php

if ( !session_id() )

	session_start();

/*

Plugin Name: Row Seats

Plugin URI: http://www.wpthemesforevents.com/row-seats-plugin

Description: Booking seats is easier with Row Seats plugin.This is a new solution to the increasing request to sell seats.It features shopping cart features, calendar backend function, csv file upload of your seat details. It also handles special seating such as handicap accessability (availalabe in lite version). Just place the shortcode in a page or post  (automatically created page as "shows" when you activate) and sell your show.  It has paypal integration to accept payments [available in lite and premium version).

Version: 0.9

Author: GC Development Team 

Author URI: http://www.wpthemesforevents.com/row-seats-plugin

*/

define( 'GSCPLN_URL', plugins_url('/', __FILE__) );

define( 'GSCPLN_COKURL', plugins_url('/jquery-cookie/', __FILE__) );

define( 'GSCPLN_IDLKURL', plugins_url('/idle-counter/', __FILE__) );



define( 'GSCPLN_CALURL', plugins_url('/weekcal/', __FILE__) );

define( 'GSCPLN_FULCKURL', plugins_url('/fullcalendar/', __FILE__) );

define( 'GSCPLN_CKURL', plugins_url('/checkout/', __FILE__) );

define( 'GSCPLN_JALURL', plugins_url('/jalerts/', __FILE__) );

define( 'GSCPLN_DIR', dirname(__FILE__) );

define( 'GSCPLN_VERSION', '1.0' );

define( 'GSCPLN_OPTIONS', 'gsc_options' );

define( 'GSCPLN_PPOPTIONS', 'gsc_paypal_options' );

define( 'GSCPLN_Name', 'GerSon Seat Chart' );

define( 'GSCPLN_PRIFIX', 'gsc_' );

define( 'GSCPLN_CSSURL', plugins_url('/css/', __FILE__) );

define( 'GSCPLN_JSURL', plugins_url('/js/', __FILE__) );
define('GSCAJAXURL', home_url( "/" ).'wp-admin/admin-ajax.php');

$wpdb->booking_seats_relation = 'booking_seats_relation';

$wpdb->gsc_shows = 'gsc_shows';

$wpdb->gsc_seats = 'gsc_seats';

$wpdb->gsc_customer_session = 'gsc_customer_session';

$wpdb->gsc_customers = 'gsc_customers';

$wpdb->gsc_bookings = 'gsc_bookings';

register_activation_hook(__FILE__,'gsc_plugin_activation');
function gsc_plugin_activation(){

global $user_ID;

$post = array();
$post['post_type']    = 'page'; //could be 'page' for example
$post['post_content'] = esc_attr('[showseats id=1]');
$post['post_author']  = null;
$post['post_status']  = 'publish'; //draft
$post['post_title']   = 'shows';
$post['post_name']   = 'shows';
$postid = wp_insert_post ($post);

}
	add_action( 'admin_init', 'registerOptions' );
function gsc_scripts_method() {
 wp_enqueue_script( 'jquery' ); 
}    
 
  
 
add_action('wp_enqueue_scripts', 'gsc_scripts_method'); // For use on the Front end (ie. Theme)

add_action( 'admin_menu', 'adminMenu' );



	 function registerOptions() {

		

        gsc_create_tables();

}



 function adminMenu() {

	add_menu_page(__('Row Seats','menu-test'), __('Row Seats','menu-test'), 'manage_options', 'gsc-intro', 'gsc_intro_page' ,GSCPLN_URL.'images/row-seat-ico.png');

    add_submenu_page('gsc-intro', __('Row Seats Settings','menu-test'), __('Row Seats Settings','menu-test'), 'manage_options', 'gsc-settings', 'gsc_settings');

    add_submenu_page('gsc-intro', __('Manage Seats','menu-test'), __('Manage Seats','menu-test'), 'manage_options', 'gsc-manage-seats', 'gsc_manage_seats');

    add_submenu_page('gsc-intro', __('Month Calender','menu-test'), __('Month Calender','menu-test'), 'manage_options', 'gsc-manage-seats-moncal', 'gsc_manage_seats_moncalender');

   add_submenu_page('gsc-intro', __('Reports','menu-test'), __('Reports','menu-test'), 'manage_options', 'gsc-reports', 'gsc_reports');

    }





 require_once('sql-scripts.php');

function gsc_intro_page(){

        require_once('inc.info.php');

}

function gsc_settings(){

    require_once('inc.gsc-settings.php');

    

}

function gsc_reports(){

    require_once('inc.reports.php');

}

function gsc_manage_seats(){

    require_once('inc.manage-seats.php');

   // require_once('calender.php');

}

function gsc_manage_seats_calender(){

    //require_once('inc.manage-seats.php');

    require_once('calender.php');

}

function gsc_manage_seats_moncalender(){

    require_once('fullcalender.php');

}

function gsc_shows_set_session($data,$action,$currentcart){

    

   

   if($action=='add'){

    $bookingadded = '';

    $currentbooking =  array();

    if(isset($currentcart) && $currentcart!=null){

   

    $currentcart = base64_decode($currentcart);

     

    $currentbooking = unserialize($currentcart);

    }

    if(count($data)>0){

    $currentbooking[] = $data[0];

    

    gsc_session_operations('blocktheseat',$data[0]);

    }

    return $currentbooking;

    }else{

    $showid = $data[0]['show_id'];

    $row = $data[0]['row_name'];

    $seat = $data[0]['seatno'];

    gsc_session_operations('deletebookingseat',$data[0]);

    

     $currentcart = base64_decode($currentcart);

      $currentcart = unserialize($currentcart);

       $finalbookings = array();

       for($i=0;$i<count($currentcart);$i++){

        if($currentcart[$i]['show_id'] == $showid && $currentcart[$i]['row_name'] == $row && $currentcart[$i]['seatno'] == $seat){

            

        }else{

            $finalbookings[] = $currentcart[$i];

        }

        

       }

    return $finalbookings;

    }

    

}

function  gsc_session_operations($action,$data)

{ 

    

  global $wpdb;

      	switch ($action) {

	   	case 'blocktheseat':

                $showid = $data['show_id'];

                $rowname = $data['row_name'];

                $seatno = $data['seatno'];

                $price = $data['price'];

                

                $sesid = session_id();

                $wpdb->query("INSERT INTO $wpdb->gsc_customer_session (gsc_session_id,show_id,rowname,seatno,price,session_time,status) VALUES ('$sesid', $showid, '$rowname',$seatno,$price,now(),'blocked')");

		   

		      

               return true;

        break;

        	case 'deletebookingseat':

                $showid = $data['show_id'];

                $sessiontime = date('Y-m-d H:i:s');

               

                $rowname = $data['row_name'];

                $seatno = $data['seatno'];

                $sesid = session_id();

              

		        $wpdb->query("DELETE FROM $wpdb->gsc_customer_session WHRE gsc_session_id='$sesid' AND show_id = $showid AND  rowname='$rowname' AND seatno=$seatno");

		   

		      

               return true;

        break;

          default:

        

        	break;

        }

}

function gsc_ipncall($data){

     global $wpdb;

    

    $booking_id = $data['custom'];

    $gsc_options = get_option(GSCPLN_OPTIONS);

    $gsc_options['gsc_ticket_prefix'];

    $ticketno = $gsc_options['gsc_ticket_prefix'].$booking_id;

    

    $paypalvars = array();

    $txn_id = '';

    $totalpaid = 0;

    foreach($data as $key=>$value){

        if($key=='txn_id'){

        $txn_id = $value;    

        }

        if($key=='payment_gross'){

        $totalpaid = $value;    

        }

      

    }

    $paypal_vars = print_r($data,true);

    

    $wpdb->query("UPDATE  $wpdb->gsc_bookings SET paypal_vars='$paypal_vars',payment_status='ipn_verified',ticket_no='$ticketno' WHERE booking_id=".$booking_id);

    

    

    

    $sql = "SELECT * FROM $wpdb->gsc_bookings where booking_id=".$booking_id;

           

            if ($results = $wpdb->get_results($sql, ARRAY_A)) {

                

                $booking_details = $wpdb->get_results($sql, ARRAY_A);

                $data = $booking_details[0];

                $booking_details = $booking_details[0]['booking_details'];

                $booking_details =  unserialize($booking_details);

            

            for($row=0;$row<count($booking_details);$row++){

            $seats = $booking_details[$row]['seatno'];

            $showid = $booking_details[$row]['show_id'];

            $rowname = $booking_details[$row]['row_name'];

            $price = $booking_details[$row]['price'];

            $sql = "SELECT * FROM gsc_seats st,gsc_shows sh

                    WHERE

                    sh.id=st.show_id AND

                    st.row_name = '$rowname' AND

                    st.seatno = $seats AND

                    st.seattype <>'' AND

                    sh.id =".$showid;

            $seatdatatoupdate = $wpdb->get_results($sql, ARRAY_A); 

            $seatdata = $seatdatatoupdate[0];

           

            $seatid = $seatdata['seatid'];

            if($seatdata['seattype']=='T'){

                

               $wpdb->query("UPDATE  $wpdb->gsc_seats SET seattype='B',status='paid' WHERE show_id=".$showid." AND row_name='$rowname' AND seatid=".$seatid);

          

            }

            

             $ticket_seat_no = $ticketno.'-'.$rowname.$seats;

                $sql="INSERT INTO $wpdb->booking_seats_relation (ticket_no,ticket_seat_no,booking_id,show_id,total_paid,txn_id,seat_cost) 

   VALUES ('$ticketno', '$ticket_seat_no', $booking_id,$showid,$totalpaid,'$txn_id',$price)";

 

 $wpdb->query($sql);

        

                     

                }

           

          

          

          

             $sql="select * from gsc_bookings gscbk,booking_seats_relation bsr,gsc_shows gscs

        where gscbk.payment_status ='ipn_verified'

        and bsr.booking_id = gscbk.booking_id

and gscs.id = bsr.show_id

and bsr.booking_id =".$booking_id;



     if ($results = $wpdb->get_results($sql, ARRAY_A)) {

        $booking_details = $wpdb->get_results($sql, ARRAY_A);

         $data = $booking_details;

       sendgscmail($data);

        }

                

		   }

   

  

    //$wpdb->query($sql);

	

    exit();	      

}



function sendgscmail($data){

        $gsc_options = get_option(GSCPLN_OPTIONS);

        $useremailtemp = $gsc_options['gsc_etemp'];

        $adminemailtemp = $gsc_options['gsc_adminetemp'];

        $search = array("<username>","<showname>","<showdate>","<bookedseats>");

        $adminsearch = array("<blogname>","<username>","<showname>","<showdate>","<bookedseats>","<availableseats>");

        $showid = $data[0]['show_id'];

        $availableseats = getavailableseatsbyshow($showid);

        $username = $data[0]['name'];

        $useremail = $data[0]['email'];

        $showdate = $data[0]['show_date'];

        $showdate = date('F j, Y',strtotime($showdate)); 

        

        $showname = $data[0]['show_name'];

        $seatdetails = '';

        for($i=0;$i<count($data);$i++){

            $seatdetails.= $data[$i]['ticket_seat_no'].' - $'.$data[$i]['seat_cost'].'<br/>';

        }

      $replace = array($username,$showname,$showdate,$seatdetails);

      $blogname = get_option('blogname');

      $adminreplace = array($blogname,$username,$showname,$showdate,$seatdetails,$availableseats);

      $mailBodyText = str_replace($search, $replace, $useremailtemp);

      $mailBodyTextadmin = str_replace($adminsearch, $adminreplace, $adminemailtemp);

$gsc_options = get_option(GSCPLN_OPTIONS);

$fromAddr = $gsc_options['gsc_email']  ;



    if($fromAddr==''){

  $fromAddr = get_option('admin_email');      

    }

 // the address to show in From field.

$recipientAddr = $useremail;

$subjectStr = 'Your booked seat details';

$recipientAddradmin = $fromAddr;

$subjectStradmin = get_option('blogname').' Bookings';









$filePath = $attachments[$i];

$fileName = basename($filePath);

$fileType = 'pdf/pdf';
  $headers  = "From: $recipientAddradmin\r\n";
    $headers .= "Content-type: text/html\r\n"; 



















// file attachment part




if (

mail( $recipientAddr , $subjectStr , $mailBodyText, $headers )

) {

  //echo "<p>Mail has been sent with attachment ($fileName) !</p>";

} else {

  //echo '<p>Mail sending failed with attachment ($fileName) !</p>';

}

 if (

mail( $recipientAddradmin , $subjectStradmin , $mailBodyTextadmin, $headers )

) {

  //echo "<p>Mail has been sent with attachment ($fileName) !</p>";

} else {

 // echo '<p>Mail sending failed with attachment ($fileName) !</p>';

} 



}

require_once('show-operations.php');

function getshowbyid($showid){

     global $wpdb;

    $sql = "SELECT * FROM $wpdb->gsc_shows where id=".$showid;

           	$found = 0;

            $data = Array();

            if ($results = $wpdb->get_results($sql, ARRAY_A)) {

		      

            

			foreach ($results as $value) {

			 

			$found++;

            }

            if($found==0){

                return $data; 

            }else{

                $data = $wpdb->get_results($sql, ARRAY_A); 

               return $data; 

            }

           

		  }

}

function delete_seats($action,$finalseats,$showid){

     global $wpdb;

    return $wpdb->query("DELETE FROM $wpdb->gsc_seats WHERE show_id='$showid'");

           

}

require_once('row-seats-actions.php');

function gettheseatchatAjax($showid,$currenturl,$bookings){

  

   

 ?>









	<!-- OUR PopupBox DIV-->

<?php 

$gsc_options = get_option(GSCPLN_OPTIONS);

$gsc_paypal_options = get_option(GSCPLN_PPOPTIONS);



$gsc_tandc = $gsc_options['gsc_tandc'];

$gsc_email = $gsc_options['gsc_email'];

	$gsc_etem = $gsc_options['gsc_etem'];

	$paypal_id = $gsc_paypal_options['paypal_id'];

	$paypal_url = $gsc_paypal_options['paypal_url'];

	$return_page = $gsc_paypal_options['custom_return'];

    $symbol = $gsc_paypal_options['currencysymbol'];

    $symbols = array(

				"0" => "$",

				"1" => "&pound;",

				"2" => "&euro;",

                "3" => "&yen;");

                

    $symbol =  $symbols[$symbol]; 

$notifyURL = GSCPLN_URL."ipn.php";

                if($return_page == "") {

					$returnURL = get_option('siteurl')."/?paypal_return='true'";

				} else {

					$returnURL = $return_page;

				}

// Process



	// Send back the contact form HTML

  //  require_once('inc.checkout.form.php');







?>















<script  type="text/javascript" language="javascript">

jQuery(document).ready(function(){

	jQuery(".QTPopup").css('display','none')

	jQuery(".contact").click(function(){

	   document.getElementById('startedcheckout').value="yes";

		jQuery(".QTPopup").animate({width: 'show'}, 'slow');})

		jQuery(".closeBtn").click(function(){

		  document.getElementById('startedcheckout').value="";

	

		 	jQuery(".QTPopup").css('display', 'none');

		})

       

})

</script>







<?php 

$currenturl = $_REQUEST['redirecturl'];


$gsc_options = get_option(GSCPLN_OPTIONS);

$gsc_paypal_options = get_option(GSCPLN_PPOPTIONS);



$gsc_tandc = $gsc_options['gsc_tandc'];

$gsc_email = $gsc_options['gsc_email'];

	$gsc_etem = $gsc_options['gsc_etem'];

	$paypal_id = $gsc_paypal_options['paypal_id'];

	$paypal_url = $gsc_paypal_options['paypal_url'];

	$return_page = $gsc_paypal_options['custom_return'];

    $currency = $gsc_paypal_options['currency'];

$symbol = $gsc_paypal_options['currencysymbol'];

$symbols = array(

				"0" => "$",

				"1" => "&pound;",

				"2" => "&euro;",

                "3" => "&yen;");

                

    $symbol =  $symbols[$symbol];       

    $sesid = session_id();

$notifyURL = GSCPLN_URL."ipn.php";

                if($return_page == "") {

					$returnURL = get_option('siteurl')."/?paypal_return='true'";

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

				

				<div class="content">

                	

			<form method='POST' action=''  name="checkoutform" id="checkoutform">

      

            <table width="100%" cellpadding="0" cellspacing="0">



            <tr><td class="tableft" width="40%">



            	<table width="100%" cellpadding="0" cellspacing="0" >

						

                        <?php 

        

      $gsc_bookings = $bookings;

      $description = $gsc_bookings;

    



      

      $total = 0;

      $totalseats = 0;

        for($i=0;$i<count($gsc_bookings);$i++){

           

            $gsc_booking = $gsc_bookings[$i];

           ?>

             

         <tr><td>Seat:<?php echo $gsc_booking['row_name'].$gsc_booking['seatno'];?>-Cost:</td><td><?php echo $symbol.$gsc_booking['price'];?></td></tr>

         <?php $total = $total+$gsc_booking['price'];

                

              $total=  number_format($total, 2, '.', '');

           

            

        }



       

     

       ?>

      <tr class="carttotclass"><td><span style="color: maroon;font-size: larger;">Total Amount:</span></td><td><span style="color: maroon;font-size: larger;"><?php echo $symbol.$total;?></span></td></tr></table>

            </td><td  class="tabright" width="60%" style="border-left:1px solid #e7e7e7 !important; ">

            <table>	<tr><td colspan='2'><label for='contact-name'><span class='reqa'>*</span> Name:</label>

			<input type='text' id='contact_name' class='contact-input' name='contact_name'  value=''/></td></tr>

			<tr><td colspan='2'><label for='contact-email'><span class='reqa'>*</span> Email:</label>

			<input type='text' id='contact_email' class='contact-input' name='contact_email'  value=''/></td></tr>

            <tr><td colspan='2'><label for='contact-email'><span class='reqa'>*</span> Phone:</label>

			<input type='text' id='contact_phone' class='contact-input' name='contact_phone'  value=''/></td></tr>

             <tr><td colspan='2'><input type='checkbox' id='gscterms' class='contact-input'  name='gscterms'/>



            <label class='termsclass'><span class='reqa'>*</span> I Agree Terms &amp; Conditions:</label></td></tr>

 <tr><td colspan='2'><?php echo $gsc_tandc;?></td></tr>
<!--
Class name added by mahesh to below place order btn
-->
            <tr><td colspan='2'><a href="javascript:void(0);" onclick="savecheckoutdata()" class='srbutton srbutton_css'>Place Order</a></td></tr>

          
			<input type="hidden" name="return" id="return" value="http://evolve.vinmatrix.com/blog/success/" />

			<input type="hidden" name="business" value="<?php echo $paypal_id;?>" />

			<input type="hidden" name="amount" value="<?php echo esc_attr($total);?>" />

          		

			<input type="hidden" name="item_name" value="Seats Booking" />

            <input type="hidden" name="custom" id="custom" value="" />

           	<input type="hidden" name="no_shipping" value="0" />
            <input type="hidden" name="action" id="action" value="finalcall" />
            

            	</table>

            </td></tr>

            </table>

				

            

	</form>

						

				

					

				</div>

			</div>

		</div>

		<div class="gpBdrLeftBottom"></div>

		<div class="gpBdrRightBottom"></div>

		<div class="gpBdrBottom"></div>

</div>

</div>





    <?php

    	$html= '';

    $showid = $showid['id'];

 

    $seats =  gsc_seats_operations('list','',$showid);

           

  //print_r($seats);

   $data = getshowbyid($showid);

   

    $divwidth = (($seats[0]['total_seats_per_row'])+2)*22;

   $showname = $data[0]['show_name'];

   

  $html.= '';



        $html.=  '<div id="currentcart" style="float:left;">



        <span class="notbooked showseats" ></span> <span class="show-text">Available </span>







        <span class="blocked showseats" ></span> <span class="show-text">In the Cart  </span>



       <span class="un showseats" ></span> <span class="show-text">In Other&#39;s Cart</span>



       <span class="booked showseats" ></span> <span class="show-text">Booked </span><br/><br/>'  ;



      $html.= '<div class="stage-hdng"></div></div>';



       $gsc_bookings = $bookings;

                  

      $sessiondata = base64_encode(serialize($gsc_bookings));

     

    

  if($sessiondata!=""){

    ?>

    <script>

    jQuery.cookie("gsc_cart",'<?php echo $sessiondata;?>');

    </script>

    

    <?php 

  }

       



  



    $foundcartitems =0;

   $html.=  '<div class="seatplan" id="showid_'.$showid.'" style="width:'.$divwidth.'px;">';

   $nextrow='';

   $dicount = 0;

   for($i=0;$i<count($seats);$i++){

    $data = $seats[$i];

   $rowname = $data['row_name'];

    $seatno =  $data['seatno'];

    $seatcost =  $data['seat_price'];

    $seatdiscost =  $data['discount_price'];

    if($nextrow!='' && $nextrow !=$rowname)

     $html.= '<li class="ltr">'.$nextrow.'</li></ul></div>';

    if($nextrow=='' || $nextrow !=$rowname)

        $html.= '<div style="float:left;"><ul class="r"><li class="ltr">'.$rowname.'</li>';

       $gsc_options = get_option(GSCPLN_OPTIONS);

       $dicount = $gsc_options['gsc_h_disc'] ;

       if($dicount!=''){

        $dicount = $seatcost - ($seatcost*($dicount/100));

        $dicount = round($dicount, 2);

        

       }else{

        $dicount = $seatcost;

       }

      $dicount = number_format($dicount, 2, '.', '');

         $seats_avail_per_row = unserialize($data['seats_avail_per_row']);

        

       

            

            $otherscart = false;

             if($data['seattype']=='N'){

             

              $html.= '<li class="un showseats" id="'.$showname.'_'.$showid.'_'.$rowname.'_'.$seatno.'_'.$seatno.'" title="Seat '.$rowname.($seatno).' Unavailable" rel="'.$data['seattype'].'">'.($seatno).'</li>';

             

             }

            else if($data['seattype']=='Y'){

               

             $html.= '<li class="notbooked showseats" id="'.$showname.'_'.$showid.'_'.$rowname.'_'.$seatno.'_'.$seatno.'" title="Seat '.$rowname.($seatno).'for price '.$symbol.$seatcost.' Available" rel="'.$data['seattype'].'">'.($seatno).'</li>';

               

            }

          

            else if($data['seattype']=='B'){

                

             $html.= '<li class="booked showseats" id="'.$showname.'_'.$showid.'_'.$rowname.'_'.$seatno.'_'.$seatno.'" title="Seat '.$rowname.($seatno).' Booked" rel="'.$data['seattype'].'">'.$seatno.'</li>';

              

            }

             else if($data['seattype']=='T'){

                for($o=0;$o<count($gsc_bookings);$o++){

                    if($gsc_bookings[$o]['row_name']==$rowname  && $gsc_bookings[$o]['seatno']==$seatno){

                         

                            $otherscart = true;

                           

                    }

                }

                if($otherscart){

                $foundcartitems++;

               $html.= '<li class="blocked showseats" id="'.$showname.'_'.$showid.'_'.$rowname.'_'.$seatno.'_'.$seatno.'" title="Seat '.$rowname.($seatno).'" rel="'.$data['seattype'].'">'.$seatno.'</li>';

                 

                }else{

                    

              $html.= '<li class="un showseats" id="'.$showname.'_'.$showid.'_'.$rowname.'_'.$seatno.'_'.$seatno.'" title="Seat '.$rowname.($seatno).'  Blocked" rel="'.$data['seattype'].'">'.$seatno.'</li>';

                  

                }

            }

            else {

               

            $html.= '<li class="b showseats" id="'.$showname.''.$showid.$rowname.$seatno.'_'.$seatno.'" title="" rel=""></li>';

          }

           

           

          

       

        $nextrow=$rowname;

        

   }

   if($foundcartitems==0){

    ?>

    <script>

    jQuery.cookie("gsc_cart",null);

    </script>

    <?php

   }

    $html.= '<li class="ltr">'.$rowname.'</li></ul></div>';



      $html.= '</div><div id="gap" style="clear:both;float:left;">&nbsp;</div><div class="cartitems" style="width:'.$divwidth.'px;"><div class="cart-hdng"aligh="center"><strong>Items in Cart</strong></div><table style="color:#51020b;">';



        if($gsc_bookings!=''  && count($gsc_bookings)>0){

      

      $total = 0;

        for($i=0;$i<count($gsc_bookings);$i++){

          

            $gsc_booking = $gsc_bookings[$i];

            



             $gsc_booking['price'] = number_format($gsc_booking['price'], 2, '.', '');



              $html.= '<tr><td>'.$gsc_booking['row_name'].($gsc_booking['seatno']).' Added - </td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>Cost:'.$symbol.$gsc_booking['price'].'</td><td><img src="'.GSCPLN_URL.'images/delete.png" class="deleteitem" id="'.$showname.'_'.$showid.'_'.$gsc_booking['row_name'].'_'.($gsc_booking['seatno']).'" onclick="deleteitem(this);" style="cursor:pointer;border:none!important"/></td></tr>';



              $total = $total+$gsc_booking['price'];

                

            

        }

   



      $html.= '<tr><td></td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td class="total_price">Total:'.$symbol.number_format($total, 2, '.', '').'</td></tr><tr><td><a class="contact rsbutton" href="javascript:void(0);" >Checkout</a></td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td><a class="rsbutton" href="javascript:void(0);"  id="'.$sessiondata.'" onclick="deleteitemall(this);">Clear Cart</a></td></tr></table></div>';







      }else{



        $html.= '<tr><td><img src="'.GSCPLN_URL.'images/emptycart.png" style="border:none !important;"/></td></tr></table></div>';



   

      }

return  $html;



}

function gettheseatchatAutoRefresh($showid,$data,$currentcart){

$gsc_paypal_options = get_option(GSCPLN_PPOPTIONS);

$symbol = $gsc_paypal_options['currencysymbol'];

$symbols = array(

				"0" => "$",

				"1" => "&pound;",

				"2" => "&euro;",

                "3" => "&yen;");

                

    $symbol =  $symbols[$symbol]; 

    $showid = $showid['id'];

   

    $seats =  gsc_seats_operations('list','',$showid);

  

   $data = getshowbyid($showid);

   

    $divwidth = (($seats[0]['total_seats_per_row'])+2)*22;

   $showname = $data[0]['show_name'];

   $gsc_bookings = $currentcart;

      $sessiondata = $gsc_bookings;

      $sessiondata = base64_encode($sessiondata);

   $gsc_bookings = unserialize($gsc_bookings);

  $html=  '<div class="seatplan" id="showid_'.$showid.'" style="width:'.$divwidth.'px;">';

   $nextrow='';

   $dicount = 0;

   for($i=0;$i<count($seats);$i++){

    $data = $seats[$i];

   $rowname = $data['row_name'];

    $seatno =  $data['seatno'];

    $seatcost =  $data['seat_price'];

    $seatdiscost =  $data['discount_price'];

    if($nextrow!='' && $nextrow !=$rowname)

     $html.= '<li class="ltr">'.$nextrow.'</li></ul></div>';

    if($nextrow=='' || $nextrow !=$rowname)

        $html.= '<div style="float:left;"><ul class="r"><li class="ltr">'.$rowname.'</li>';

       $gsc_options = get_option(GSCPLN_OPTIONS);

       $dicount = '' ;

       if($dicount!=''){

        $dicount = $seatcost - ($seatcost*($dicount/100));

        $dicount = round($dicount, 2);

        

       }else{

        $dicount = $seatcost;

       }

     $dicount = number_format($dicount, 2, '.', '');

         $seats_avail_per_row = unserialize($data['seats_avail_per_row']);

        

       

            

            $otherscart = false;

             if($data['seattype']=='N'){

             

              $html.= '<li class="un showseats" id="'.$showname.'_'.$showid.'_'.$rowname.'_'.$seatno.'_'.$seatno.'" title="Seat '.$rowname.($seatno).' Unavailable" rel="'.$data['seattype'].'">'.($seatno).'</li>';

             

             }

            else if($data['seattype']=='Y'){

               

             $html.= '<li class="notbooked showseats" id="'.$showname.'_'.$showid.'_'.$rowname.'_'.$seatno.'_'.$seatno.'" title="Seat '.$rowname.($seatno).'for price '.$symbol.$seatcost.' Available" rel="'.$data['seattype'].'">'.($seatno).'</li>';

               

            }

          

            else if($data['seattype']=='B'){

                

             $html.= '<li class="booked showseats" id="'.$showname.'_'.$showid.'_'.$rowname.'_'.$seatno.'_'.$seatno.'" title="Seat '.$rowname.($seatno).' Booked" rel="'.$data['seattype'].'">'.$seatno.'</li>';

              

            }

             else if($data['seattype']=='T'){

                for($o=0;$o<count($gsc_bookings);$o++){

                    if($gsc_bookings[$o]['row_name']==$rowname  && $gsc_bookings[$o]['seatno']==$seatno){

                         

                            $otherscart = true;

                           

                    }

                }

                if($otherscart){

                     

               $html.= '<li class="blocked showseats" id="'.$showname.'_'.$showid.'_'.$rowname.'_'.$seatno.'_'.$seatno.'" title="Seat '.$rowname.($seatno).'" rel="'.$data['seattype'].'">'.$seatno.'</li>';

                 

                }else{

                    

              $html.= '<li class="un showseats" id="'.$showname.'_'.$showid.'_'.$rowname.'_'.$seatno.'_'.$seatno.'" title="Seat '.$rowname.($seatno).'  Blocked" rel="'.$data['seattype'].'">'.$seatno.'</li>';

                  

                }

            }

            else {

               

            $html.= '<li class="b showseats" id="'.$showname.''.$showid.$rowname.$seatno.'_'.$seatno.'" title="" rel=""></li>';

          }

           

           

          

       

        $nextrow=$rowname;

        

   }

    $html.= '<li class="ltr">'.$rowname.'</li></ul></div>';

   

   

return $html;

}

function curPageURL() {

 $pageURL = 'http';

 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}

 $pageURL .= "://";

 if ($_SERVER["SERVER_PORT"] != "80") {

  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];

 } else {

  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

 }

 return $pageURL;

}

function gettheseatchat($showid){
    $slug = basename(get_permalink());

 if($slug =='shows'){
    


   $currenturl = curPageURL();

    $gsc_options = get_option(GSCPLN_OPTIONS);



    $stylecss =  $gsc_options['gsc_theme'];



    if($stylecss==''){



      $stylecss = 'lite.css';



    }



     $gsc_h_msg = $gsc_options['gsc_h_msg'];

     $paymentsuccess = "";

     if(isset($_POST) && $_POST['custom']!=''){

       $paymentsuccess = succesgscsmessage(); 

     }

     

     

?>

<script type="text/javascript">

var GSCPLN_CKURL = '<?php echo GSCPLN_CKURL?>';

var GSCAJAXURL = '<?php echo GSCPLN_URL?>ajax.php';

</script>
<?php 

    if($gsc_options['gsc_enable_jquery']!='off'){
  ?>    
 
<?php 
      
    }
    
    ?>



<script type='text/javascript' src='<?php echo GSCPLN_URL ?>js/jquery.blockUI.js'></script>






<link rel="stylesheet" type="text/css" media="all" href="<?php echo GSCPLN_CSSURL.$stylecss ?>" />



 <script type='text/javascript' src='<?php echo GSCPLN_COKURL ?>jquery.cookie.js'></script>



<script type="text/javascript" src="<?php echo GSCPLN_IDLKURL ?>jquery.countdown.js"></script>

 <script type="text/javascript" src="<?php echo GSCPLN_IDLKURL ?>idle-timer.js"></script>

 <link rel="stylesheet" type="text/css" media="all" href="<?php echo GSCPLN_IDLKURL ?>jquery.countdown.css" />

 <style type="text/css">



</style>

<input type="hidden" name="startedcheckout" id="startedcheckout" value="" />

<input type="hidden" name="redirecturl" id="redirecturl" value="<?php echo $currenturl;?>" />

        

<?php  $showid = $showid['id'];

 $showdata['vmid'] = $showid;

$showdata = gsc_shows_operations('byid',$showdata,'');



$showdata = $showdata[0];

$eventname = $showdata['show_name'];

$venue = $showdata['venue'];

$eventdate = $showdata['show_start_time'];

$eventdate = date('Y-m-d H:i:s',strtotime($eventdate));

$dateexpire =  date('Y-m-d',strtotime($eventdate));

if($dateexpire<date('Y-m-d')){

echo   '<div style="color:#f21313;"><strong>Stay tuned for upcoming shows, we appreciate your patronage</strong></div>';

}else{



$html ="<div class='paymentsucess'>$paymentsuccess</div><div style='float:left;color:#f21313;'>YOUR CART WILL EMPTY IF IDLE FOR 7MIN.&nbsp;&nbsp;</div><div id='defaultCountdown'></div><div style='float:left'></div>

<div style='float:left;' id='eventdetails'>

Event Name:$eventname<br/>

Event Date & Time: $eventdate<br/>

Venue:$venue<br/>

</div>";

$html.= "<div id='showprview' align='center' style='max-width: 689px; -moz-user-select: none;margin-left: auto;margin-right: auto;' >";



       



   ?>

             <script>

             ///////////////////////// idle time code /////////////////

    function liftOff(){

  

        

          jQuery.post("<?php echo GSCAJAXURL?>",

        { 
             action:'releasecurrentcart',
            details:<?php echo $showid?>,

            redirecturl:document.getElementById('redirecturl').value,

             cartiterms:jQuery.cookie("gsc_cart")

        },

        function(msg){

               jQuery("#showprview").html((msg));

           document.getElementById('return').value = document.getElementById('redirecturl').value;

                          	           jQuery(".seatplan .showseats").each(function(i){

    



    if(jQuery(this).attr( "rel"  )=="Y"){

        jQuery(this).click(function()  {

            getupdatedshow(jQuery(this).attr( "id"  ));



      

         

        }); 

        }

        if(jQuery(this).attr( "rel"  )=="H"){

        jQuery(this).click(function()  {

            id = jQuery(this).attr( "id"  );

            jConfirm('<?php echo $gsc_h_msg;?>', 'HandiCap Confirmation', function(r) {

   if (r== true)

 {

  getupdatedshow(id);

 }

 else

 {

  

  } 

});



   

        }); 

        }

    



    

         

});

          

   

         

});

}



    

        IdleTimer.subscribe("idle", function(){

            var status = document.getElementById("status");



        jQuery('#defaultCountdown').countdown('destroy');

	    var austDay = new Date();



        austDay.setMinutes(austDay.getMinutes() + 7);





	jQuery('#defaultCountdown').countdown({until: austDay,onExpiry: liftOff,format: 'MS',compact: true, 

    description: ''});







        });

        

        IdleTimer.subscribe("active", function(){

            var status = document.getElementById("status");



 



	       var austDay = new Date();

 jQuery.cookie("gsc_cart_time",austDay.getTime());

            jQuery('#defaultCountdown').countdown('destroy');

            austDay.setMinutes(austDay.getMinutes() + 7);

            jQuery('#defaultCountdown').countdown({until: austDay,onExpiry: liftOff,format: 'MS',compact: true, 

    description: ''});

            jQuery('#defaultCountdown').countdown('pause'); 

        });

        

        IdleTimer.start(1000);



  ///////////////////////// idle time code ended /////////////////







          function getupdatedshow(id){

  if(jQuery.cookie("gsc_cart_time")==null){

             var dat =  new Date();

             jQuery.cookie("gsc_cart_time",dat.getTime());

             }else{

                var dat =  new Date();

                diff = dat.getTime() - jQuery.cookie("gsc_cart_time");

                

                if(diff>600000){

                    jQuery.cookie("gsc_cart",null);

                }

               

             }

            

  jQuery('#showprview').block({ 

                message: 'Processing.....', 

                css: { border: '3px solid #a00' } 

            }); 

    jQuery.post("<?php echo GSCAJAXURL?>",

        { 
            action:'booking',
            details:id,

            redirecturl:document.getElementById('redirecturl').value,

            cartiterms:jQuery.cookie("gsc_cart")

        },

        function(msg){

            jQuery("#showprview").html((msg));

           jQuery('#showprview').unblock();

           document.getElementById('return').value = document.getElementById('redirecturl').value;

                          	           jQuery(".seatplan .showseats").each(function(i){

    



    if(jQuery(this).attr( "rel"  )=="Y"){

        jQuery(this).click(function()  {

            getupdatedshow(jQuery(this).attr( "id"  ));



      

         

        }); 

        }

        if(jQuery(this).attr( "rel"  )=="H"){

        jQuery(this).click(function()  {

            id = jQuery(this).attr( "id"  );

            jConfirm('<?php echo $gsc_h_msg;?>', 'HandiCap Confirmation', function(r) {

   if (r== true)

 {

  getupdatedshow(id);

 }

 else

 {

  

  } 

});



   

        }); 

        }

    

});

    

         

});

}

function refreshshow(id){

    

    jQuery.post('<?php echo GSCAJAXURL?>',
        
        { 
            action: 'refresh',
            details:id,

            redirecturl:document.getElementById('redirecturl').value,

            cartiterms:jQuery.cookie("gsc_cart")

        },

        function(msg){

           document.getElementById('return').value = document.getElementById('redirecturl').value;

            resp = jQuery(msg);

            chart = resp.filter('.seatplan');

            jQuery(".seatplan").html((chart));

                          	           jQuery(".seatplan .showseats").each(function(i){

    



    if(jQuery(this).attr( "rel"  )=="Y"){

        jQuery(this).click(function()  {

            getupdatedshow(jQuery(this).attr( "id"  ));



      

         

        }); 

        }

        if(jQuery(this).attr( "rel"  )=="H"){

        jQuery(this).click(function()  {

            id = jQuery(this).attr( "id"  );

            jConfirm('<?php echo $gsc_h_msg;?>', 'HandiCap Confirmation', function(r) {

   if (r== true)

 {

  getupdatedshow(id);

 }

 else

 {

  

  } 

});



   

        }); 

        }

    

});

    

         

});

}

jQuery(document).ready(function () {  

    getupdatedshow("<?php echo $showid?>");

  

   var interval = setInterval(increment,5000);     

});

function releaseseats(){

   if (document.getElementById('startedcheckout').value== ''){

   releasenow("<?php echo $showid?>");

   }

}

 releasenow("<?php echo $showid?>");

function releasenow(id){

          action ='releasenow';  

        

          jQuery.post('<?php echo GSCAJAXURL?>',

        { 
            action : 'releasenow',
            details:id,

            redirecturl:document.getElementById('redirecturl').value,

            cartiterms:jQuery.cookie("gsc_cart")

        },

        function(msg){

          

   

         

});

 var interval = setInterval(releaseseats,300000);

          }

function increment(){

   if (document.getElementById('startedcheckout').value== ''){

   refreshshow("<?php echo $showid?>");

   }

}

          function savecheckoutdata(){

            if(document.getElementById('contact_name').value==""){

                alert('please enter your name');

                return false;

            }

          

            filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

                if (filter.test(document.getElementById('contact_email').value)) {



                }

                else

                {

                    alert('please enter proper email address');

                    return false;

                }

             if ((document.getElementById('contact_phone').value)=="") {



                    alert('please enter your emergency phone number to contact');

                    return false;

                }

         if ((document.getElementById('gscterms').checked)==false) {



                    alert('please agree the terms and conditions');

                    return false;

                }

                jQuery(".QTPopup").css('display', 'none');

        jQuery('#showprview').block({ 

                message: 'Processing.....', 

                css: { border: '3px solid #a00' } 

            });        
       
         jQuery.post('<?php echo GSCAJAXURL?>',

        { 

            
            action:'savebooking',
            name:document.getElementById('contact_name').value,

            email:document.getElementById('contact_email').value,

            phone:document.getElementById('contact_phone').value,

            status:'pending_paypal',

            redirecturl:document.getElementById('redirecturl').value,

             cartiterms:jQuery.cookie("gsc_cart")

        },

        function(msg){

            document.getElementById('return').value = document.getElementById('redirecturl').value;

          if(msg!='' && msg!=0){

              document.getElementById('custom').value = msg; 
               var data = jQuery("#checkoutform").serialize();
jQuery.cookie("gsc_cart",null);
	jQuery.post('<?php echo GSCAJAXURL?>', data, function(response) {
		 alert('Booking details has been sent to your email id...');
        window.location.reload();
	});
         
     


        // document.checkoutform.submit();

          }else{

            alert("Something Went wrong errorcode:".msg);

          }

        

	  

        });

           

          }

          

function deleteitem(obj){

        

        action ='deletebooking';

        if(obj.id == 'deleteall'){

          action :'deleteall';  

        }

          jQuery.post('<?php echo GSCAJAXURL?>',

        {  action:action,

            details:obj.id,

            redirecturl:document.getElementById('redirecturl').value,

            cartiterms:jQuery.cookie("gsc_cart")

        },

        function(msg){

            jQuery("#showprview").html((msg));

            document.getElementById('return').value = document.getElementById('redirecturl').value;

      jQuery(".seatplan .showseats").each(function(i){

    



    if(jQuery(this).attr( "rel"  )=="Y"){

        jQuery(this).click(function()  {

            

      getupdatedshow(jQuery(this).attr( "id"  ));

         

        }); 

        }

           if(jQuery(this).attr( "rel"  )=="H"){

        jQuery(this).click(function()  {

            id = jQuery(this).attr( "id"  );

            jConfirm('<?php echo $gsc_h_msg;?>', 'HandiCap Confirmation', function(r) {

   if (r== true)

 {

  getupdatedshow(id);

 }

 else

 {

  

  } 

});



   

        }); 

        }

    

});

    

         

});

          }

          function deleteitemall(obj){

          

        

          jQuery.post('<?php echo GSCAJAXURL?>',

        { 
            action:'deleteall' ,
            details:obj.id,

            redirecturl:document.getElementById('redirecturl').value,

            cartiterms:jQuery.cookie("gsc_cart")

        },

        function(msg){

            jQuery.cookie("gsc_cart",null);

            jQuery("#showprview").html((msg));

            document.getElementById('return').value = document.getElementById('redirecturl').value;

      jQuery(".seatplan .showseats").each(function(i){

    



    if(jQuery(this).attr( "rel"  )=="Y"){

        jQuery(this).click(function()  {

            

      getupdatedshow(jQuery(this).attr( "id"  ));

         

        }); 

        }

           if(jQuery(this).attr( "rel"  )=="H"){

        jQuery(this).click(function()  {

            id = jQuery(this).attr( "id"  );

            jConfirm('<?php echo $gsc_h_msg;?>', 'HandiCap Confirmation', function(r) {

   if (r== true)

 {

  getupdatedshow(id);

 }

 else

 {

  

  } 

});



   

        }); 

        }

    

});

    

         

});

          }

          </script>

   <?php 

   return $html.'</div>';

}
}else{
    echo 'This will work with only shows page or post!..';
}
}

function gettheadminseatchat($showid){

  

    ?>





    <link rel="stylesheet" type="text/css" media="all" href="<?php echo GSCPLN_CSSURL ?>seats.css" />



    <?php

    $showid = $showid['id'];

   

    $seats =  gsc_seats_operations('list','',$showid);



   $data = getshowbyid($showid);

   

    $divwidth = (($seats[0]['total_seats_per_row'])+2)*22;

   $showname = $data[0]['show_name'];

   

  $html = '';

   $html.= "<h2>". __('Preview of the Show:'.$showname, 'gsc')."</h2>";

       $html.=  '<div id="currentcart" style="width:600px;"><span class="notbooked showseats" >&nbsp;&nbsp;&nbsp;&nbsp;</span> Available &nbsp;&nbsp; 

        

        <span class="blocked showseats" >&nbsp;&nbsp;&nbsp;&nbsp;</span> In the Cart &nbsp;&nbsp; 

       

        <span class="booked showseats" >&nbsp;&nbsp;&nbsp;&nbsp;</span> Booked &nbsp;&nbsp; 

        <span class="un showseats" >&nbsp;&nbsp;&nbsp;&nbsp;</span> In Other&#39;s Cart &nbsp;&nbsp; 

        <br/><br/>

        <div id="stageshow"></div></div><div class="clear"></div>'  ; 

    

  

   $html.=  '<div class="seatplan" id="showid_'.$showid.'" style="width:'.$divwidth.'px;">';

   

   $nextrow = '';

   for($i=0;$i<count($seats);$i++){

    $data = $seats[$i];

    $rowname = $data['row_name'];

    $seatno =  $data['seatno'];

    $seatcost =  $data['seat_price'];

    $seatdiscost =  $data['discount_price'];

    if($nextrow!='' && $nextrow !=$rowname)

     $html.= '<li class="ltr">'.$nextrow.'</li></ul></div>';

    if($nextrow=='' || $nextrow !=$rowname)

        $html.= '<div style="float:left;"><ul class="r"><li class="ltr">'.$rowname.'</li>';

      

          

        

             if($data['seattype']=='N')

            $html.= '<li class="un showseats" id="'.$showname.'_'.$showid.'_'.$rowname.'_'.$seatno.'" title="Seat '.$rowname.($seatno).' Unavailable" rel="'.$seats_avail_per_row[$k].'">'.($seatno).'</li>';

            else if($data['seattype']=='Y')

            $html.= '<li class="notbooked showseats" id="'.$showname.'_'.$showid.'_'.$rowname.'_'.$seatno.'" title="Seat '.$rowname.($seatno).' Available" rel="'.$seats_avail_per_row[$k].'">'.($seatno).'</li>';

           
            else if($data['seattype']=='B')

            $html.= '<li class="booked showseats" id="'.$showname.'_'.$showid.'_'.$rowname.'_'.$seatno.'" title="Seat '.$rowname.($seatno).'  Booked" rel="'.$seats_avail_per_row[$k].'">'.($seatno).'</li>';

            else if($data['seattype']=='T')

            $html.= '<li class="blocked showseats" id="'.$showname.'_'.$showid.'_'.$rowname.'_'.$seatno.'" title="Seat '.$rowname.($seatno).'  Booked" rel="'.$seats_avail_per_row[$k].'">'.($seatno).'</li>';

            

            else 

            $html.= '<li class="b showseats" id="'.$showname.''.$showid.$rowname.$seatno.'" title="" rel=""></li>';

          

           

          

         

      $nextrow=$rowname;

   }

   $html.= '<li class="ltr">'.$rowname.'</li></ul></div>';

   return $html;

}

// admin reports



function getbookingdetailbyshow($showid){

  $seats = gsc_seats_operations('list','',$showid);

$totalbooking = 0;

 for($seat=0;$seat<count($seats);$seat++){

                if($seats[$seat]['seattype'] == 'B'){

                    $totalbooking++;

                

                }

            }

return  $totalbooking;

    

}

function bookedtickets(){

global $wpdb;



        $bookedtickets = array();

        $sql = "select * from gsc_bookings gscbk,booking_seats_relation bsr,gsc_shows gscs

        where gscbk.payment_status ='ipn_verified'

        and bsr.booking_id = gscbk.booking_id

and gscs.id = bsr.show_id";



             if ($results = $wpdb->get_results($sql, ARRAY_A)) {

		     

             $bookedtickets = $wpdb->get_results($sql, ARRAY_A);

           

		    }









return  $bookedtickets;

    

}

function succesgscsmessage(){



    $gscpapalopt = get_option(GSCPLN_PPOPTIONS);

   

    return  $gscpapalopt['return_page'];

    

}







function getavailableseatsbyshow($showid){

    global $wpdb;

        $sql = "SELECT * FROM gsc_seats st,gsc_shows sh

                    WHERE

                    sh.id=st.show_id AND

                    sh.id =".$showid;

                    

                    $totalseatsavailable = 0;

            $bookingdata = $wpdb->get_results($sql, ARRAY_A); 

             for($row=0;$row<count($bookingdata);$row++){

               $availseats = unserialize($bookingdata[$row]['seats_avail_per_row']);

                

             for($seat=0;$seat<count($availseats);$seat++){

               

                if( $availseats[$seat]!='B' && $availseats[$seat]!='U'){

      

                $totalseatsavailable++;

                     

                }

           

            }  

            }

            return $totalseatsavailable;

           

}



add_shortcode( 'showseats', 'gettheseatchat' );

function my_action_javascript() {
?>
<script type="text/javascript" >

function getthevalue(value){
	var data = {
		action: 'my_action1',
		whatever: value
	};

	// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
	jQuery.post('<?php echo  esc_url( home_url( '/' ) );?>/wp-admin/admin-ajax.php', data, function(response) {
		alert('Got this from the server: ' + response);
	});
}
function setthevalue(value){
	var data = {
		action: 'my_action2',
		whatever: value
	};

	// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
	jQuery.post('<?php echo  esc_url( home_url( '/' ) );?>/wp-admin/admin-ajax.php', data, function(response) {
		alert('Got this from the server: ' + response);
	});
}
</script>
<?php
}
add_action('wp_ajax_refresh', 'gsc_ajax_callback');
add_action('wp_ajax_nopriv_refresh', 'gsc_ajax_callback');
add_action('wp_ajax_releasenow', 'gsc_ajax_callback');
add_action('wp_ajax_nopriv_releasenow', 'gsc_ajax_callback');
add_action('wp_ajax_booking', 'gsc_ajax_callback');
add_action('wp_ajax_nopriv_booking', 'gsc_ajax_callback');
add_action('wp_ajax_deletebooking', 'gsc_ajax_callback');
add_action('wp_ajax_nopriv_deletebooking', 'gsc_ajax_callback');
add_action('wp_ajax_deleteall', 'gsc_ajax_callback');
add_action('wp_ajax_nopriv_deleteall', 'gsc_ajax_callback');
add_action('wp_ajax_finalcall', 'gsc_ajax_callback');
add_action('wp_ajax_nopriv_finalcall', 'gsc_ajax_callback');
add_action('wp_ajax_savebooking', 'gsc_ajax_callback');
add_action('wp_ajax_nopriv_savebooking', 'gsc_ajax_callback');
add_action('wp_ajax_deleteall', 'gsc_ajax_callback');
add_action('wp_ajax_nopriv_deleteall', 'gsc_ajax_callback');
add_action('wp_ajax_releasecurrentcart', 'gsc_ajax_callback');
add_action('wp_ajax_nopriv_releasecurrentcart', 'gsc_ajax_callback');

add_action('wp_ajax_save', 'gsc_ajax_callback');
add_action('wp_ajax_update', 'gsc_ajax_callback');
add_action('wp_ajax_delete', 'gsc_ajax_callback');
add_action('wp_ajax_get_events', 'gsc_ajax_callback');
add_action('wp_ajax_nopriv_get_events', 'gsc_ajax_callback');
function gsc_ajax_callback() {
	global $wpdb; // this is how you get access to the database

	if(isset($_POST['action'])){ 
    
  switch($_POST['action']){ 
    
    case 'savebooking': // { 
      $details = base64_decode($_POST['cartiterms']);
      $bookingid = gsc_shows_operations('savebooking',$_POST,$details);
      echo $bookingid;
     
      break;
      case 'finalcall': // { 
      gsc_ipncall($_POST);
    
      break;
      case 'deleteall': // { 
          $details = $_POST['cartiterms'];
          $details = base64_decode($_POST['cartiterms']);
          $currentcart = unserialize($details);
          $showid = gsc_shows_operations('deleteall',$currentcart,'');
    
    
       echo gettheseatchatAjax($showid,$_POST['redirecturl'],array());
   exit();	
      break;
      
      case 'releasenow': // { 
      $showid = $_POST['details'];
      gsc_shows_operations('releaseall',$showid,'');
   
      exit();
      break;
      case 'releasecurrentcart':
      $showid = $_POST['details'];
      $currentcart = $_POST['cartiterms'];
      $currentcart = base64_decode($currentcart);
       $currentcart = unserialize($currentcart);
      $currentcart = gsc_shows_operations('releasecurrentcart',$showid,$currentcart);
      echo gettheseatchatAjax($showid,$_POST['redirecturl'],$currentcart);
                
      exit();
      break;
       case 'deletebooking': // { 
        
        $details = $_POST['details'];
           
        $data1 = explode('_',$details);
             if(count($data1)==1 && $_POST['cartiterms']==null){
                 echo gettheseatchatAjax($data1[0]);
              
                exit(); 
             }else if($_POST['cartiterms']!='' && count($data1)==1 ) {
                 $currentcart = $_POST['cartiterms'];
                 $currentcart =  base64_decode($currentcart);
                $currentcart =  unserialize($currentcart);
                $showid['id'] = $_POST['details'];
                
                echo gettheseatchatAjax($showid,$_POST['redirecturl'],$currentcart);
                exit(); 
             }
             $currentcart = $_POST['cartiterms'];
     
      $bookingtodelete = gsc_shows_operations('deletebooking',$_POST,$currentcart);
      $cartplusbooking =  gsc_shows_set_session($bookingtodelete,'delete',$currentcart);
      $showid['id'] = $data1[1];
      echo gettheseatchatAjax($showid,$_POST['redirecturl'],$cartplusbooking);
      exit();
      break;
      
       case 'refresh': // { 
        
        if($_POST['cartiterms']!=null){
       $currentcart = $_POST['cartiterms'];
       $currentcart =  base64_decode($currentcart);
      
       }else{
        $currentcart = array();
       }
       $details = $_POST['details'];
       $data1 = explode('_',$details);
       echo gettheseatchatAutoRefresh($data1[0],$_POST['redirecturl'],$currentcart);
      exit();
      break;
       case 'booking': // { 
         $details = $_POST['details'];
           
        $data1 = explode('_',$details);
        
             if(count($data1)==1 && $_POST['cartiterms']==null){
                 echo gettheseatchatAjax($data1[0]);
               exit();
              
                
             }else if($_POST['cartiterms']!='' && count($data1)==1 ) {
                 $currentcart = $_POST['cartiterms'];
                 $currentcart =  base64_decode($currentcart);
                $currentcart =  unserialize($currentcart);
                $showid['id'] = $_POST['details'];
                
                echo gettheseatchatAjax($showid,$_POST['redirecturl'],$currentcart);
                exit(); 
             }
     
      $currentcart = $_POST['cartiterms'];
       $bookings = gsc_shows_operations('booking',$_POST,$currentcart);
      $cartplusbooking =  gsc_shows_set_session($bookings,'add',$currentcart);
     
        $showid['id'] = $data1[1];
      echo gettheseatchatAjax($showid,$_POST['redirecturl'],$cartplusbooking);
      exit();
      break;
    case 'save': // { 
        echo gsc_shows_operations('insert',$_POST,'');
        exit();
      
      break;
      case 'update': // { 
        return gsc_shows_operations('update',$_POST,'');
      
      break;
      case 'delete': // { 
        return gsc_shows_operations('delete',$_POST,'');
      
      break;
      case 'get_events':
      
    
      $arr = array();
      $data = gsc_shows_operations('list','','');
      for($i=0;$i<count($data);$i++){
          $arr[]=array( 
          'id'   =>$data[$i]['id'], 
          'title'=>$data[$i]['show_name'], 
          'start'=>date('Y-m-d H:i:s',strtotime($data[$i]['show_start_time'])),
          'end'  =>date('Y-m-d H:i:s',strtotime($data[$i]['show_end_time'])),
          'allday'  =>true, 
          'body'  =>$data[$i]['venue']
        ); 
      }
       echo json_encode($arr); 
    exit; 
      break;
     default:
     break;
 // } 
  
  
  } 
} 

	die(); // this is required to return a proper result
}
function my_action_callback() {
	global $wpdb; // this is how you get access to the database

	$whatever = ( $_POST['whatever'] );
	
        echo $whatever;

	die(); // this is required to return a proper result
}

 