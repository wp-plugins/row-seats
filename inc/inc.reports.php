<script type='text/javascript' src='<?php echo RSTPLN_URL ?>js/datepicker/jquery-ui-1.8.13.custom.min.js'></script>
<link rel="stylesheet" type="text/css" media="all"
      href="<?php echo RSTPLN_URL ?>js/datepicker/jquery-ui-1.8.13.custom.css"/>
<script>

    jQuery(document).ready(function () {
        jQuery("#rst_rpfrom").datepicker({changeMonth: true,
            changeYear: true, dateFormat: "yy-mm-dd"});

        jQuery("#rst_rpto").datepicker({changeMonth: true,
            changeYear: true, dateFormat: "yy-mm-dd"});
    });
</script><?php echo "  <h2>" . __('RST Reports:', 'rst') . "</h2>"; ?>
<?php
//3nowe
global $wpdb;
if(isset($_REQUEST['ctxnid']))
{
        $sql = "select * from $wpdb->rst_bookings rstbk,$wpdb->rst_booking_seats_relation bsr,$wpdb->rst_shows rsts

        where (rstbk.payment_status ='ipn_verified' OR rstbk.payment_status ='offline_registration')

        and bsr.booking_id = rstbk.booking_id

        and rsts.id = bsr.show_id

        and bsr.txn_id ='" . $_REQUEST['ctxnid']."'";
		//print $sql;

        if ($results = $wpdb->get_results($sql, ARRAY_A)) {

            $booking_details = $wpdb->get_results($sql, ARRAY_A);
            $data = $booking_details;
			$txn_id=$data[0]['txn_id'];
			//print "<br><br>--------------".$txn_id;
			
			if($txn_id)
			{
            sendrstmail($data, $txn_id);
			}

        }

}

if(isset($_REQUEST['Update']))
{
	$sql=mysql_query("update  rst_bookings set name='".$_REQUEST['b_by']."',email='".$_REQUEST['email']."',phone='".$_REQUEST['phone']."' where booking_id='".$_REQUEST['bid']."'");

	//$ssql=mysql_query("update  rst_booking_seats_relation set ticket_no='".$_REQUEST['t_no']."',ticket_seat_no='".$_REQUEST['ts_no']."',txn_id='".$_REQUEST['txn_id']."',seat_cost='".$_REQUEST['seat_cost']."' where id='".$_REQUEST['hid']."'");
	
	$etrafound=mysql_query("select * from rst_bookings where booking_id='".$_REQUEST['bid']."'");
$asql=mysql_fetch_array($etrafound);


$item['booking_details']=$asql['booking_details'];
$extra=unserialize ($item['booking_details']);
if($extra['customfield']!=''){
foreach ($extra['customfield'] as $key => $val){

	//echo $key.':  '.$_REQUEST[$key].'<br/>'.'<br/>';
	$key_name=str_replace(" ","_",$key);
	$extra['customfield'][$key]=$_REQUEST[$key_name];
	
	?>
    
	<?php }
	//print_r($extra['customfield']);
//print_r($extra);
$extra+=$extra['customfield'];

$booking=serialize($extra);
	//print_r($booking);
$update_customfield=mysql_query("update  rst_bookings set booking_details='".$booking."' where booking_id='".$_REQUEST['bid']."'");	
//print_r($_REQUEST['bid']);
//exit;
	}
}

if(isset($_REQUEST['booking_id']))
{
$edit=$_SERVER['PHP_SELF'].'?page='.$_REQUEST['page'].'&rstfilter='.$_GET['rstfilter'].'&rst_rpfrom='.$_GET['rst_rpfrom'].'&rst_rpto='.$_GET['rst_rpto'].'&action='.$_GET['action'];
	//$sql=mysql_query("select * from  rst_bookings as rb inner join rst_booking_seats_relation as rr inner join rst_shows as rs where rr.booking_id='".$_REQUEST['booking_id']."' and rr.booking_id=rb.booking_id and rb.show_id= rs.id ");
	//$ssql=mysql_query("select * from rst_booking_seats_relation where booking_id='".$_REQUEST['booking_id']."'");
	//$valuee=mysql_fetch_array($ssql);
	//$value=mysql_fetch_array($sql);
	
$sql="select * from  rst_bookings as rb inner join rst_booking_seats_relation as rr inner join rst_shows as rs where rr.booking_id='".$_REQUEST['booking_id']."' and rr.booking_id=rb.booking_id and rb.show_id= rs.id ";
$value = $wpdb->get_row($sql, ARRAY_A);
$sql="select * from rst_booking_seats_relation where booking_id='".$_REQUEST['booking_id']."'";
$valuee = $wpdb->get_row($sql, ARRAY_A);
	
//for etra edit
//$etrafound=mysql_query("select * from rst_bookings where booking_id='".$_REQUEST['booking_id']."'");
//$asql=mysql_fetch_array($etrafound);
$sql="select * from rst_bookings where booking_id='".$_REQUEST['booking_id']."'";
$asql = $wpdb->get_row($sql, ARRAY_A);


$item['booking_details']=$asql['booking_details'];
$extra=unserialize ($item['booking_details']);
?><form method="post" action="<?php echo $edit; ?>">
    
    <table>
      <tr><td>Booked By</td><td><input type="text" name="b_by" value="<?php echo $value['name'];  ?>"/></td></tr>
       <tr><td>Email</td><td><input type="text" name="email" value="<?php echo $value['email'];  ?>" /></td></tr>
       <tr><td>Phone</td><td><input type="text" name="phone"  value="<?php echo $value['phone'];  ?>"/></td></tr>
       <!--<tr><td>Booking ID</td><td><input type="text" name="t_no" value="<?php echo $value['ticket_no'];  ?>"/></td></tr>
       <tr><td>Ticket No</td><td><input type="text" name="ts_no" value="<?php echo $value['ticket_seat_no'];  ?>"/></td></tr>
        <tr><td>TXN ID</td><td><input type="text" name="txn_id" value="<?php echo $value['txn_id'];  ?>"/></td></tr>
        <tr><td>Seat Cost</td><td><input type="text" name="seat_cost" value="<?php echo $value['seat_cost'];  ?>"/></td></tr>-->
        <?php
		
		if($extra['customfield']!=''){
foreach ($extra['customfield'] as $key => $val){
$key_name=str_replace(" ","_",$key);

?>
    <tr><td><?php echo $key;?></td><td><input type="text" name="<?php echo $key_name;?>" value="<?php echo $val;?>" /></td></tr>
	<?php }
	
}
	?>
        
         <tr><td><input type="hidden" name="bid" value="<?php echo $value['booking_id'];  ?>" /></td><td><input type="submit" name="Update" value="Update" /></td><td><input type="submit" name="Cancel" value="Cancel" /></td><td><input type="hidden" name="hid" value="<?php echo $valuee['id'];  ?>" /></td></tr>
         </table>
         </form>
       
<?php }
$rst_paypal_options = get_option(RSTPLN_PPOPTIONS);
$rst_paypal_options = get_option(RSTPLN_PPOPTIONS);

//$symbol = $rst_paypal_options['rst_currencysymbol'];
$symbol = get_option('rst_currencysymbol');

$symbols = array(
    "0" => "$",
    "1" => "&pound;",
    "2" => "&euro;",
    "3" => "&#3647;",
    "4" => "&#8362;",
    "5" => "&yen;");




$symbol = $symbols[$symbol];


$filter = '';
$from = '';
$to = '';



if($_REQUEST['id'])
{
	function custom_admin_css() {
   echo '<style type="text/css">
   			#adminmenuback {visibility:hidden}
		   #adminmenuwrap {visibility:hidden}
		   #wpadminbar {visibility:hidden}
		   #message {visibility:hidden}
		   #wpbody-content h2 {visibility:hidden}
		
		   
		   
         </style>';
}


//add_action('admin_head', 'custom_admin_css');
echo custom_admin_css();

$sql="select * from rst_bookings where booking_id='".$_REQUEST['id']."'";
$asql = $wpdb->get_row($sql, ARRAY_A);


//$sql=mysql_query("select * from rst_bookings where booking_id='".$_REQUEST['id']."'");
//$asql=mysql_fetch_array($sql);
$item['booking_details']=$asql['booking_details'];
$extra=unserialize ($item['booking_details']);
			if($extra['customfield']!=''){
			foreach ($extra['customfield'] as $key => $value){
	echo $key.':  '.$value.'<br/>'.'<br/>';}

exit;}
else
{
	echo 'No Extra details found';
	exit;
}
}
if (isset($_REQUEST['paged']) && $_REQUEST['paged'] != '') {
    if (isset($_SESSION['rstfilter']) && $_SESSION['rstfilter'] != '') {
        $alldata = bookedtickets($_SESSION['rstfilter'], $_SESSION['rst_rpfrom'], $_SESSION['rst_rpto']);
        $filter = $_SESSION['rstfilter'];
        $from = $_SESSION['rst_rpfrom'];
        $to = $_SESSION['rst_rpto'];
    } else {
        $alldata = bookedtickets(0, '', '');
    }

} else {
    $_SESSION['rstfilter'] = '';
    $_SESSION['rst_rpfrom'] = '';
    $_SESSION['rst_rpto'] = '';
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] = 'Apply') {
    $alldata = bookedtickets($_REQUEST['rstfilter'], $_POST['rst_rpfrom'], $_REQUEST['rst_rpto']);
    $filter = $_REQUEST['rstfilter'];
    $from = $_REQUEST['rst_rpfrom'];
    $to = $_REQUEST['rst_rpto'];
    $_SESSION['rstfilter'] = $filter;
    $_SESSION['rst_rpfrom'] = $from;
    $_SESSION['rst_rpto'] = $to;
} else {
    $alldata = bookedtickets(0, '', '');

}
if($_REQUEST['bookingid'])
{
    $_SESSION['rstfilter'] = '';
    $_SESSION['rst_rpfrom'] = '';
    $_SESSION['rst_rpto'] = '';
$alldata = bookedticketsnew(0, '', '',$_REQUEST['bookingid']);

}

?>
<?php echo "  <h3>" . __('Row Seats Booking Details:', 'rst') . "</h3>"; ?>

<p><?php echo __('Below are list of booked tickets.', 'rst'); ?></p>
<form action="<?php echo get_option('home') ?>/wp-admin/admin.php?page=rst-reports" method="GET" name="filteraction">
    <select name="rstfilter" id="rstfilter">
        <option value="0">All For Today</option>

        <?php
        $wpfeeoptions = get_option(RSTFEE_OPTIONS);
        $sercharge = 0;
        // print_r($wpfeeoptions); fee_name




        $showdata = rst_shows_operations('list', '', '');
        for ($i = 0; $i < count($showdata); $i++) {
            ?>
            <option
                value="<?php echo $showdata[$i]['id']; ?>" <?php if ($alldata[$i]['id'] == $filter) echo 'selected'?> ><?php echo $showdata[$i]['show_name'];?></option>

        <?php
        }
        ?>
    </select>
    (Or) Date Range From:<input type="text" name="rst_rpfrom" id="rst_rpfrom" class="regular-text rpfrom "
                                value="<?php echo $from; ?>" size="12" style="width: 100px;"/> To: <input type="text"
                                                                                                          name="rst_rpto"
                                                                                                          id="rst_rpto"
                                                                                                          class="regular-text cpnfrom "
                                                                                                          value="<?php echo $to; ?>"
                                                                                                          size="12"
                                                                                                          style="width: 100px;"/>
    <input type="submit" value="Apply" class="button-primary" name="action"/>
    <input type="hidden" value="rst-reports" name="page"/>
</form> <br/>
<form action="<?php echo get_option('home') ?>/wp-admin/admin.php?page=rst-reports" method="GET" name="filteraction">
<input type="text"
                                                                                                          name="keywords"
                                                                                                          id="keywords"
                                                                                                          class="regular-text cpnfrom "
                                                                                                          value="<?php echo $_REQUEST['keywords'];?>"
                                                                                                          size="30"
                                                                                                          style="width: 200px;" />
    <input type="submit" value="Search" class="button-primary" name="action"/>
    <input type="hidden" value="rst-reports" name="page"/>
	<input type="button" value="Clear"  class="button-primary"  name="action" onclick="window.location.href='admin.php?page=rst-reports'"/>
</form><br/>




<!-- printable reports ----- -->
<?php echo apply_filters('rst_apply_printable_reports_filter','', $filter, $from, $to); ?>
<!-- ----- printable reports -->



<?php


//mp_id,name,template,start_date,end_date,page_type,spin_type,status,

class Projects_Reposts_Table extends WP_List_Table_Custom
{

    private $tabledata = array();

    function __construct()
    {
        global $status, $page;

        //Set parent defaults
        parent::__construct(array(
            'singular' => 'id', //singular name of the listed records
            'plural' => 'ids', //plural name of the listed records
            'ajax' => false //does this table support ajax?
        ));

    }


    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'show_name':
            case 'show_date':
            case 'name':
            case 'email':
            case 'phone':
		    case 'ticket_no':
            case 'ticket_seat_no':
            case 'txn_id':
            case 'seat_cost':
            case 'total_paid':
            case 'c_code':
            case 'c_discount':
            case 'fees':
            case 'booking_time':
            case 'booking_status':
           
		   return $item[$column_name];
		     
			 
			 case 'booking_details':
			 $installedplugins = get_option('active_plugins');
		
		 $result='row-seats-checkout-customfield/row-seats-checkout-custom-fields.php';
		 
		 if (in_array($result, $installedplugins)) {
                      $checkplugin='true';

			
?>

<a href="<?php echo $_SERVER['PHP_SELF'].'?page=rst-reports&id='.$item['booking_id']; ?>" onclick="load_window('<?php echo $_SERVER['PHP_SELF'].'?page=rst-reports&id='.$item['booking_id']; ?>');return false;">Details</a>


<script type="application/javascript">
	function load_window(url){
		var url = url;
		var centerX = (screen.width - 500) / 2;
		var centerY = (screen.height - 300) / 2;
		window.open(url,'1389699028581','toolbar=0,menubar=0,location=1,width=400,height=250,status=1,scrollbars=1,resizable=0,left='+centerX+',top='+centerY);
	
	}
</script>



<?php

		 }
		 else
		 {
		 }


			 //$item['booking_details']=print_r($extra['customfield']);
			 
			 break;
			 
            default:
               return print_r($item, true); //Show the whole array for troubleshooting purposes
			   //3nowe
			case 'action':
			if(isset($_GET['rstfilter'])){
				
				$edit=$_SERVER['PHP_SELF'].'?rstfilter='.$_GET['rstfilter'].'&rst_rpfrom='.$_GET['rst_rpfrom'].'&rst_rpto='.$_GET['rst_rpto'].'&action='.$_GET['action'].'&page='.$_REQUEST['page'].'&booking_id='.$item['booking_id'];
				$resend=$_SERVER['PHP_SELF'].'?rstfilter='.$_GET['rstfilter'].'&rst_rpfrom='.$_GET['rst_rpfrom'].'&rst_rpto='.$_GET['rst_rpto'].'&action='.$_GET['action'].'&page='.$_REQUEST['page'].'&ctxnid='.$item['txn_id'];
				}
				else{
			
			$edit=$_SERVER['PHP_SELF'].'?page='.$_REQUEST['page'].'&booking_id='.$item['booking_id'];
			$resend=$_SERVER['PHP_SELF'].'?page='.$_REQUEST['page'].'&ctxnid='.$item['txn_id'];
			}
			//print_r($item);
			echo '<a href="'.$edit.'">Edit</a>&nbsp;|&nbsp;<a href="'.$resend.'">Resend mail</a>';
      
			   
        }
    }


    function column_id($item)
    {

        //Build row actions


        // $actions = array(
//            'edit'      => sprintf('<a href="?page=%s&action=%s&id=%s">Edit</a>','nsmpc_create_projects','edit',$item['mp_id']),
//            'delete'    => sprintf('<a href="?page=%s&action=%s&id=%s">Delete</a>',$_REQUEST['page'],'delete',$item['mp_id']),
//            'launch'    => sprintf('<a href="?page=%s&action=%s&id=%s">Launch</a>',$_REQUEST['page'],'launch',$item['mp_id']),
//            'cancel'    => sprintf('<a href="?page=%s&action=%s&id=%s">Cancel</a>',$_REQUEST['page'],'cancel',$item['mp_id']),
//            'relaunch'    => sprintf('<a href="?page=%s&action=%s&id=%s">Relaunch</a>',$_REQUEST['page'],'relaunch',$item['mp_id']),
//            'pause'    => sprintf('<a href="?page=%s&action=%s&id=%s">Pause</a>',$_REQUEST['page'],'pause',$item['mp_id']),
//            'details'    => sprintf('<a href="?page=%s&action=%s&id=%s">Show details</a>',$_REQUEST['page'],'details',$item['mp_id']),
//        );
        //Return the title contents

    }


    function column_cb($item)
    {

    }



    function get_columns()
    {
 $installedplugins = get_option('active_plugins');
		
		 $result='row-seats-checkout-customfield/row-seats-checkout-custom-fields.php';
		 
		 if (in_array($result, $installedplugins)) {
                      $checkplugin='true';




        if ($wpfeeoptions['rst_enable_fee'] == 'on') {
            $columns = array(
                'show_name' => 'Event Name',
                'show_date' => 'Event Date',
                'name' => 'Booked By',
                'email' => 'Email',
                'phone' => 'Phone',
                'ticket_no' => 'Booking ID',
				'booking_details' => 'EF Info',
                'ticket_seat_no' => 'Ticket No',
                'txn_id' => 'TXN ID',
                'seat_cost' => 'Seat Cost',
                'total_paid' => 'Total Paid',
                'c_code' => 'Coupon',
                'c_discount' => 'Coupon Discount',
                'fees' => $wpfeeoptions['fee_name'],
                'booking_time' => 'Booked On',
                'booking_status' => 'Booking Status',
				//3nowe
                  'action' => 'Action',

            );
        } else if($wpfeeoptions['rst_enable_fee'] != 'on') {
            $columns = array(
                'show_name' => 'Event Name',
                'show_date' => 'Event Date',
                'name' => 'Booked By',
                'email' => 'Email',
                'phone' => 'Phone',
                'ticket_no' => 'Booking ID',
				'booking_details' => 'EF Info',
                'ticket_seat_no' => 'Ticket No',
                'txn_id' => 'TXN ID',
                'seat_cost' => 'Seat Cost',
                'total_paid' => 'Total Paid',
                'c_code' => 'Coupon',
                'c_discount' => 'Coupon Discount',
                'booking_time' => 'Booked On',
                'booking_status' => 'Booking Status',
				//3nowe
                    'action' => 'Action',

            );
        }
		
		 }
		 
		 else
		 
		 {
			if ($wpfeeoptions['rst_enable_fee'] == 'on') {
            $columns = array(
                'show_name' => 'Event Name',
                'show_date' => 'Event Date',
                'name' => 'Booked By',
                'email' => 'Email',
                'phone' => 'Phone',
                'ticket_no' => 'Booking ID',
				'ticket_seat_no' => 'Ticket No',
                'txn_id' => 'TXN ID',
                'seat_cost' => 'Seat Cost',
                'total_paid' => 'Total Paid',
                'c_code' => 'Coupon',
                'c_discount' => 'Coupon Discount',
                'fees' => $wpfeeoptions['fee_name'],
                'booking_time' => 'Booked On',
                'booking_status' => 'Booking Status',
				//3nowe
                  'action' => 'Action',

            );
        } else if($wpfeeoptions['rst_enable_fee'] != 'on') {
            $columns = array(
                'show_name' => 'Event Name',
                'show_date' => 'Event Date',
                'name' => 'Booked By',
                'email' => 'Email',
                'phone' => 'Phone',
                'ticket_no' => 'Booking ID',
			    'ticket_seat_no' => 'Ticket No',
                'txn_id' => 'TXN ID',
                'seat_cost' => 'Seat Cost',
                'total_paid' => 'Total Paid',
                'c_code' => 'Coupon',
                'c_discount' => 'Coupon Discount',
                'booking_time' => 'Booked On',
                'booking_status' => 'Booking Status',
				//3nowe
                    'action' => 'Action',

            );
        } 
			 
			 
		 }
		 
		 
		 
		 
        return $columns;
    }


    function get_sortable_columns()
    {
        $sortable_columns = array(//true means its already sorted


        );
        return $sortable_columns;
    }


    function process_bulk_action()
    {


    }

    function prepare_items()
    {

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 30;


        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();


        /**
         * REQUIRED. Finally, we build an array to be used by the class for column
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);


        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();


        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example
         * package slightly different than one you might build on your own. In
         * this example, we'll be using array manipulation to sort and paginate
         * our data. In a real-world implementation, you will probably want to
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
        $rst_paypal_options = get_option(RSTPLN_PPOPTIONS);
        $rst_paypal_options = get_option(RSTPLN_PPOPTIONS);

        //$symbol = $rst_paypal_options['rst_currencysymbol'];
        $symbol = get_option('rst_currencysymbol');

        $symbols = array(
            "0" => "$",
            "1" => "&pound;",
            "2" => "&euro;",
            "3" => "&#3647;",
            "4" => "&#8362;",
            "5" => "&yen;");


        $symbol = $symbols[$symbol];


        $alldata = bookedtickets($_SESSION['rstfilter'], $_SESSION['rst_rpfrom'], $_SESSION['rst_rpto']);
if($_REQUEST['bookingid'])
{
    $_SESSION['rstfilter'] = '';
    $_SESSION['rst_rpfrom'] = '';
    $_SESSION['rst_rpto'] = '';
//$alldata = bookedticketsnew(0, '', '',$_REQUEST['bookingid']);
$alldata = bookedticketsnew($_SESSION['rstfilter'], $_SESSION['rst_rpfrom'], $_SESSION['rst_rpto'],$_REQUEST['bookingid']);
}
if($_REQUEST['keywords'])
{
$alldata = bookedticketssearch($_REQUEST['keywords']);
}


        // $data = mass_project_operations('list','','','','');

        for ($i = 0; $i < count($alldata); $i++) {
            $data = $alldata[$i];

            if ($alldata[$i]['fees'] != '')
                $alldata[$i]['fees'] = $symbol . $alldata[$i]['fees'];

            $tranid = $data['txn_id'];
            $txn_id = base64_decode($alldata[$i]['txn_id']);
            //echo $alldata[$i]['txn_id'];
            $txn_id = split('-', $txn_id);
            if (count($txn_id) > 1) {
                $alldata[$i]['txn_id'] = $txn_id[0];
            } else {
                $alldata[$i]['txn_id'] = $alldata[$i]['txn_id'];
            }
            $alldata[$i]['booking_status'] = ($alldata[$i]['booking_status'] == '') ? 'Booked' : $alldata[$i]['booking_status'];


            $alldata[$i]['seat_cost'] = $symbol . $alldata[$i]['seat_cost'];
            $alldata[$i]['total_paid'] = $symbol . $alldata[$i]['total_paid'];
            $alldata[$i]['c_discount'] = $symbol . $alldata[$i]['c_discount'];

        }

        $data = $alldata;
        //    echo '<pre>';
        // print_r($data);
        // die;
        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         *
         * In a real-world situation involving a database, you would probably want
         * to handle sorting by passing the 'orderby' and 'order' values directly
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        function usort_reorder($a, $b)
        {
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'license_id'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
        }

        usort($data, 'usort_reorder');


        $current_page = $this->get_pagenum();


        $total_items = count($data);


        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);


        $this->items = $data;


        $this->set_pagination_args(array(
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page' => $per_page, //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items / $per_page) //WE have to calculate the total number of pages
        ));
    }

}


//Create an instance of our package class...
$testListTable = new Projects_Reposts_Table();
//Fetch, prepare, sort, and filter our data...
$testListTable->prepare_items();

?>
<div class="wrap admin_rst_wrap">


    <form id="movies-filter" method="get">
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <!-- Now we can render the completed list table -->
        <?php $testListTable->display() ?>
    </form>

</div>

<?php
/**
 * Base class for displaying a list of items in an ajaxified HTML table.
 *
 * @package WordPress
 * @subpackage List_Table
 * @since 3.1.0
 */

/**
 * Base class for displaying a list of items in an ajaxified HTML table.
 *
 * @package WordPress
 * @subpackage List_Table
 * @since 3.1.0
 * @access private
 */
class WP_List_Table_Custom {

	/**
	 * The current list of items
	 *
	 * @since 3.1.0
	 * @var array
	 * @access protected
	 */
	var $items;

	/**
	 * Various information about the current table
	 *
	 * @since 3.1.0
	 * @var array
	 * @access private
	 */
	var $_args;

	/**
	 * Various information needed for displaying the pagination
	 *
	 * @since 3.1.0
	 * @var array
	 * @access private
	 */
	var $_pagination_args = array();

	/**
	 * The current screen
	 *
	 * @since 3.1.0
	 * @var object
	 * @access protected
	 */
	var $screen;

	/**
	 * Cached bulk actions
	 *
	 * @since 3.1.0
	 * @var array
	 * @access private
	 */
	var $_actions;

	/**
	 * Cached pagination output
	 *
	 * @since 3.1.0
	 * @var string
	 * @access private
	 */
	var $_pagination;

	/**
	 * Constructor. The child class should call this constructor from its own constructor
	 *
	 * @param array $args An associative array with information about the current table
	 * @access protected
	 */
	function __construct( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'plural' => '',
			'singular' => '',
			'ajax' => false,
			'screen' => null,
		) );

		$this->screen = convert_to_screen( $args['screen'] );

		add_filter( "manage_{$this->screen->id}_columns", array( &$this, 'get_columns' ), 0 );

		if ( !$args['plural'] )
			$args['plural'] = $this->screen->base;

		$args['plural'] = sanitize_key( $args['plural'] );
		$args['singular'] = sanitize_key( $args['singular'] );

		$this->_args = $args;

		if ( $args['ajax'] ) {
			// wp_enqueue_script( 'list-table' );
			add_action( 'admin_footer', array( &$this, '_js_vars' ) );
		}
	}

	/**
	 * Checks the current user's permissions
	 * @uses wp_die()
	 *
	 * @since 3.1.0
	 * @access public
	 * @abstract
	 */
	function ajax_user_can() {
		die( 'function WP_List_Table_Custom::ajax_user_can() must be over-ridden in a sub-class.' );
	}

	/**
	 * Prepares the list of items for displaying.
	 * @uses WP_List_Table_Custom::set_pagination_args()
	 *
	 * @since 3.1.0
	 * @access public
	 * @abstract
	 */
	function prepare_items() {
		die( 'function WP_List_Table_Custom::prepare_items() must be over-ridden in a sub-class.' );
	}

	/**
	 * An internal method that sets all the necessary pagination arguments
	 *
	 * @param array $args An associative array with information about the pagination
	 * @access protected
	 */
	function set_pagination_args( $args ) {
		$args = wp_parse_args( $args, array(
			'total_items' => 0,
			'total_pages' => 0,
			'per_page' => 0,
		) );

		if ( !$args['total_pages'] && $args['per_page'] > 0 )
			$args['total_pages'] = ceil( $args['total_items'] / $args['per_page'] );

		// redirect if page number is invalid and headers are not already sent
		if ( ! headers_sent() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && $args['total_pages'] > 0 && $this->get_pagenum() > $args['total_pages'] ) {
			wp_redirect( add_query_arg( 'paged', $args['total_pages'] ) );
			exit;
		}

		$this->_pagination_args = $args;
	}

	/**
	 * Access the pagination args
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @param string $key
	 * @return array
	 */
	function get_pagination_arg( $key ) {
		if ( 'page' == $key )
			return $this->get_pagenum();

		if ( isset( $this->_pagination_args[$key] ) )
			return $this->_pagination_args[$key];
	}

	/**
	 * Whether the table has items to display or not
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @return bool
	 */
	function has_items() {
		return !empty( $this->items );
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 3.1.0
	 * @access public
	 */
	function no_items() {
		_e( 'No items found.' );
	}

	/**
	 * Display the search box.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @param string $text The search button text
	 * @param string $input_id The search input id
	 */
	function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
			return;

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		if ( ! empty( $_REQUEST['post_mime_type'] ) )
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
		if ( ! empty( $_REQUEST['detached'] ) )
			echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
?>
<p class="search-box">
	<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
	<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
	<?php submit_button( $text, 'button', false, false, array('id' => 'search-submit') ); ?>
</p>
<?php
	}

	/**
	 * Get an associative array ( id => link ) with the list
	 * of views available on this table.
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return array
	 */
	function get_views() {
		return array();
	}

	/**
	 * Display the list of views available on this table.
	 *
	 * @since 3.1.0
	 * @access public
	 */
	function views() {
		$views = $this->get_views();
		$views = apply_filters( 'views_' . $this->screen->id, $views );

		if ( empty( $views ) )
			return;

		echo "<ul class='subsubsub'>\n";
		foreach ( $views as $class => $view ) {
			$views[ $class ] = "\t<li class='$class'>$view";
		}
		echo implode( " |</li>\n", $views ) . "</li>\n";
		echo "</ul>";
	}

	/**
	 * Get an associative array ( option_name => option_title ) with the list
	 * of bulk actions available on this table.
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return array
	 */
	function get_bulk_actions() {
		return array();
	}

	/**
	 * Display the bulk actions dropdown.
	 *
	 * @since 3.1.0
	 * @access public
	 */
	function bulk_actions() {
		if ( is_null( $this->_actions ) ) {
			$no_new_actions = $this->_actions = $this->get_bulk_actions();
			// This filter can currently only be used to remove actions.
			$this->_actions = apply_filters( 'bulk_actions-' . $this->screen->id, $this->_actions );
			$this->_actions = array_intersect_assoc( $this->_actions, $no_new_actions );
			$two = '';
		} else {
			$two = '2';
		}

		if ( empty( $this->_actions ) )
			return;

		echo "<select name='action$two'>\n";
		echo "<option value='-1' selected='selected'>" . __( 'Bulk Actions' ) . "</option>\n";

		foreach ( $this->_actions as $name => $title ) {
			$class = 'edit' == $name ? ' class="hide-if-no-js"' : '';

			echo "\t<option value='$name'$class>$title</option>\n";
		}

		echo "</select>\n";

		submit_button( __( 'Apply' ), 'action', false, false, array( 'id' => "doaction$two" ) );
		echo "\n";
	}

	/**
	 * Get the current action selected from the bulk actions dropdown.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @return string|bool The action name or False if no action was selected
	 */
	function current_action() {
		if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
			return $_REQUEST['action'];

		if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
			return $_REQUEST['action2'];

		return false;
	}

	/**
	 * Generate row actions div
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param array $actions The list of actions
	 * @param bool $always_visible Whether the actions should be always visible
	 * @return string
	 */
	function row_actions( $actions, $always_visible = false ) {
		$action_count = count( $actions );
		$i = 0;

		if ( !$action_count )
			return '';

		$out = '<div class="' . ( $always_visible ? 'row-actions-visible' : 'row-actions' ) . '">';
		foreach ( $actions as $action => $link ) {
			++$i;
			( $i == $action_count ) ? $sep = '' : $sep = ' | ';
			$out .= "<span class='$action'>$link$sep</span>";
		}
		$out .= '</div>';

		return $out;
	}

	/**
	 * Display a monthly dropdown for filtering items
	 *
	 * @since 3.1.0
	 * @access protected
	 */
	function months_dropdown( $post_type ) {
		global $wpdb, $wp_locale;

		$months = $wpdb->get_results( $wpdb->prepare( "
			SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
			FROM $wpdb->posts
			WHERE post_type = %s
			ORDER BY post_date DESC
		", $post_type ) );

		$month_count = count( $months );

		if ( !$month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
			return;

		$m = isset( $_GET['m'] ) ? (int) $_GET['m'] : 0;
?>
		<select name='m'>
			<option<?php selected( $m, 0 ); ?> value='0'><?php _e( 'Show all dates' ); ?></option>
<?php
		foreach ( $months as $arc_row ) {
			if ( 0 == $arc_row->year )
				continue;

			$month = zeroise( $arc_row->month, 2 );
			$year = $arc_row->year;

			printf( "<option %s value='%s'>%s</option>\n",
				selected( $m, $year . $month, false ),
				esc_attr( $arc_row->year . $month ),
				/* translators: 1: month name, 2: 4-digit year */
				sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year )
			);
		}
?>
		</select>
<?php
	}

	/**
	 * Display a view switcher
	 *
	 * @since 3.1.0
	 * @access protected
	 */
	function view_switcher( $current_mode ) {
		$modes = array(
			'list'    => __( 'List View' ),
			'excerpt' => __( 'Excerpt View' )
		);

?>
		<input type="hidden" name="mode" value="<?php echo esc_attr( $current_mode ); ?>" />
		<div class="view-switch">
<?php
			foreach ( $modes as $mode => $title ) {
				$class = ( $current_mode == $mode ) ? 'class="current"' : '';
				echo "<a href='" . esc_url( add_query_arg( 'mode', $mode, $_SERVER['REQUEST_URI'] ) ) . "' $class><img id='view-switch-$mode' src='" . esc_url( includes_url( 'images/blank.gif' ) ) . "' width='20' height='20' title='$title' alt='$title' /></a>\n";
			}
		?>
		</div>
<?php
	}

	/**
	 * Display a comment count bubble
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param int $post_id
	 * @param int $pending_comments
	 */
	function comments_bubble( $post_id, $pending_comments ) {
		$pending_phrase = sprintf( __( '%s pending' ), number_format( $pending_comments ) );

		if ( $pending_comments )
			echo '<strong>';

		echo "<a href='" . esc_url( add_query_arg( 'p', $post_id, admin_url( 'edit-comments.php' ) ) ) . "' title='" . esc_attr( $pending_phrase ) . "' class='post-com-count'><span class='comment-count'>" . number_format_i18n( get_comments_number() ) . "</span></a>";

		if ( $pending_comments )
			echo '</strong>';
	}

	/**
	 * Get the current page number
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return int
	 */
	function get_pagenum() {
		$pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;

		if( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
			$pagenum = $this->_pagination_args['total_pages'];

		return max( 1, $pagenum );
	}

	/**
	 * Get number of items to display on a single page
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return int
	 */
	function get_items_per_page( $option, $default = 20 ) {
		$per_page = (int) get_user_option( $option );
		if ( empty( $per_page ) || $per_page < 1 )
			$per_page = $default;

		return (int) apply_filters( $option, $per_page );
	}

	/**
	 * Display the pagination.
	 *
	 * @since 3.1.0
	 * @access protected
	 */
	function pagination( $which ) {
		if ( empty( $this->_pagination_args ) )
			return;

		extract( $this->_pagination_args, EXTR_SKIP );

		$output = '<span class="displaying-num">' . sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

		$current = $this->get_pagenum();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

		$current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );

		$page_links = array();

		$disable_first = $disable_last = '';
		if ( $current == 1 )
			$disable_first = ' disabled';
		if ( $current == $total_pages )
			$disable_last = ' disabled';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'first-page' . $disable_first,
			esc_attr__( 'Go to the first page' ),
			esc_url( remove_query_arg( 'paged', $current_url ) ),
			'&laquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'prev-page' . $disable_first,
			esc_attr__( 'Go to the previous page' ),
			esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
			'&lsaquo;'
		);

		if ( 'bottom' == $which )
			$html_current_page = $current;
		else
			$html_current_page = sprintf( "<input class='current-page' title='%s' type='text' name='paged' value='%s' size='%d' />",
				esc_attr__( 'Current page' ),
				$current,
				strlen( $total_pages )
			);

		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[] = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . '</span>';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'next-page' . $disable_last,
			esc_attr__( 'Go to the next page' ),
			esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ),
			'&rsaquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'last-page' . $disable_last,
			esc_attr__( 'Go to the last page' ),
			esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
			'&raquo;'
		);

		$pagination_links_class = 'pagination-links';
		if ( ! empty( $infinite_scroll ) )
			$pagination_links_class = ' hide-if-js';
		$output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

		if ( $total_pages )
			$page_class = $total_pages < 2 ? ' one-page' : '';
		else
			$page_class = ' no-pages';

		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $this->_pagination;
	}

	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @since 3.1.0
	 * @access protected
	 * @abstract
	 *
	 * @return array
	 */
	function get_columns() {
		die( 'function WP_List_Table_Custom::get_columns() must be over-ridden in a sub-class.' );
	}

	/**
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return array
	 */
	function get_sortable_columns() {
		return array();
	}

	/**
	 * Get a list of all, hidden and sortable columns, with filter applied
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return array
	 */
	function get_column_info() {
		if ( isset( $this->_column_headers ) )
			return $this->_column_headers;

		$columns = get_column_headers( $this->screen );
		$hidden = get_hidden_columns( $this->screen );

		$_sortable = apply_filters( "manage_{$this->screen->id}_sortable_columns", $this->get_sortable_columns() );

		$sortable = array();
		foreach ( $_sortable as $id => $data ) {
			if ( empty( $data ) )
				continue;

			$data = (array) $data;
			if ( !isset( $data[1] ) )
				$data[1] = false;

			$sortable[$id] = $data;
		}

		$this->_column_headers = array( $columns, $hidden, $sortable );

		return $this->_column_headers;
	}

	/**
	 * Return number of visible columns
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @return int
	 */
	function get_column_count() {
		list ( $columns, $hidden ) = $this->get_column_info();
		$hidden = array_intersect( array_keys( $columns ), array_filter( $hidden ) );
		return count( $columns ) - count( $hidden );
	}

	/**
	 * Print column headers, accounting for hidden and sortable columns.
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param bool $with_id Whether to set the id attribute or not
	 */
	function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable ) = $this->get_column_info();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( 'paged', $current_url );

		if ( isset( $_GET['orderby'] ) )
			$current_orderby = $_GET['orderby'];
		else
			$current_orderby = '';

		if ( isset( $_GET['order'] ) && 'desc' == $_GET['order'] )
			$current_order = 'desc';
		else
			$current_order = 'asc';

		if ( ! empty( $columns['cb'] ) ) {
			static $cb_counter = 1;
			$columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All' ) . '</label>'
				. '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
			$cb_counter++;
		}

		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( 'manage-column', "column-$column_key" );

			$style = '';
			if ( in_array( $column_key, $hidden ) )
				$style = 'display:none;';

			$style = ' style="' . $style . '"';

			if ( 'cb' == $column_key )
				$class[] = 'check-column';
			elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) )
				$class[] = 'num';

			if ( isset( $sortable[$column_key] ) ) {
				list( $orderby, $desc_first ) = $sortable[$column_key];

				if ( $current_orderby == $orderby ) {
					$order = 'asc' == $current_order ? 'desc' : 'asc';
					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					$order = $desc_first ? 'desc' : 'asc';
					$class[] = 'sortable';
					$class[] = $desc_first ? 'asc' : 'desc';
				}

				$column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
			}

			$id = $with_id ? "id='$column_key'" : '';

			if ( !empty( $class ) )
				$class = "class='" . join( ' ', $class ) . "'";

			echo "<th scope='col' $id $class $style>$column_display_name</th>";
		}
	}

	/**
	 * Display the table
	 *
	 * @since 3.1.0
	 * @access public
	 */
	function display() {
		extract( $this->_args );

		$this->display_tablenav( 'top' );

?>
<style>
.admin_rst_wrap table.rst_records {


	width: 100%;


	border-collapse: collapse;


}


.admin_rst_wrap table.rst_records tr {





}


.admin_rst_wrap table.rst_records td,


.admin_rst_wrap table.rst_records th {


	padding: 5px 5px 5px 5px;


	vertical-align: top;


	border: 1px solid #C0C0C0;


}





.admin_rst_wrap table.rst_records th {


	background-color: #E0DBD0;


	vertical-align: middle;


}





.admin_rst_wrap table.rst_records a{


	color: #0080FF;


	text-decoration: none;


}


.admin_rst_wrap table.rst_records a:hover{


	text-decoration: underline;


}


.rst_buttons {text-align: right; margin-bottom: 10px; margin-top: 10px; float: right;}


.rst_pageswitcher {


	margin-right: 200px;


	float: left;


}


.rst_pageswitcher div {float: none !important;}

</style>


<table class="rst_records" cellspacing="0">
	<tbody>
	<tr>
		<?php $this->print_column_headers(); ?>
	</tr>

<?php $this->display_rows_or_placeholder(); ?>

	<tr>
		<?php $this->print_column_headers( false ); ?>
	</tr>


	</tbody>
</table>
<?php
		$this->display_tablenav( 'bottom' );
	}

	/**
	 * Get a list of CSS classes for the <table> tag
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return array
	 */
	function get_table_classes() {
		return array( 'widefat', 'fixed', $this->_args['plural'] );
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since 3.1.0
	 * @access protected
	 */
	function display_tablenav( $which ) {
		if ( 'top' == $which )
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
?>
	<div class="tablenav <?php echo esc_attr( $which ); ?>">

		<div class="alignleft actions">
			<?php $this->bulk_actions(); ?>
		</div>
<?php
		$this->extra_tablenav( $which );
		$this->pagination( $which );
?>

		<br class="clear" />
	</div>
<?php
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination
	 *
	 * @since 3.1.0
	 * @access protected
	 */
	function extra_tablenav( $which ) {}

	/**
	 * Generate the <tbody> part of the table
	 *
	 * @since 3.1.0
	 * @access protected
	 */
	function display_rows_or_placeholder() {
		if ( $this->has_items() ) {
			$this->display_rows();
		} else {
			list( $columns, $hidden ) = $this->get_column_info();
			echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
			$this->no_items();
			echo '</td></tr>';
		}
	}

	/**
	 * Generate the table rows
	 *
	 * @since 3.1.0
	 * @access protected
	 */
	function display_rows() {
		foreach ( $this->items as $item )
			$this->single_row( $item );
	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param object $item The current item
	 */
	function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class == '' ? ' class="alternate"' : '' );

		echo '<tr' . $row_class . '>';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Generates the columns for a single row of the table
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param object $item The current item
	 */
	 
	 
	function single_row_columns( $item ) {
		list( $columns, $hidden ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$class = "class='$column_name column-$column_name'";

			$style = '';
			if ( in_array( $column_name, $hidden ) )
				$style = ' style="display:none;"';

			$attributes = "$class$style";

			if ( 'cb' == $column_name ) {
				echo '<th scope="row" class="check-column">';
				echo $this->column_cb( $item );
				echo '</th>';
			}
			elseif ( method_exists( $this, 'column_' . $column_name ) ) {
				echo "<td $attributes>";
				echo call_user_func( array( &$this, 'column_' . $column_name ), $item );
				echo "</td>";
			}
			else {
				echo "<td $attributes>";
				echo $this->column_default( $item, $column_name );
				echo "</td>";
			}
		}
	}

	/**
	 * Handle an incoming ajax request (called from admin-ajax.php)
	 *
	 * @since 3.1.0
	 * @access public
	 */
	function ajax_response() {
		$this->prepare_items();

		extract( $this->_args );
		extract( $this->_pagination_args, EXTR_SKIP );

		ob_start();
		if ( ! empty( $_REQUEST['no_placeholder'] ) )
			$this->display_rows();
		else
			$this->display_rows_or_placeholder();

		$rows = ob_get_clean();

		$response = array( 'rows' => $rows );

		if ( isset( $total_items ) )
			$response['total_items_i18n'] = sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) );

		if ( isset( $total_pages ) ) {
			$response['total_pages'] = $total_pages;
			$response['total_pages_i18n'] = number_format_i18n( $total_pages );
		}

		die( json_encode( $response ) );
	}

	/**
	 * Send required variables to JavaScript land
	 *
	 * @access private
	 */
	function _js_vars() {
		$args = array(
			'class'  => get_class( $this ),
			'screen' => array(
				'id'   => $this->screen->id,
				'base' => $this->screen->base,
			)
		);

		printf( "<script type='text/javascript'>list_args = %s;</script>\n", json_encode( $args ) );
	}
}


?>