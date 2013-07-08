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
$rst_paypal_options = get_option(RSTPLN_PPOPTIONS);
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


$filter = '';
$from = '';
$to = '';
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
<form action="<?php echo get_option('home') ?>/wp-admin/admin.php?page=rst-reports" method="GET"
      name="filterclearaction"><input type="hidden" value="rst-reports" name="page"/><input type="submit" value="Clear"
                                                                                            class="button-primary"
                                                                                            name="action"/></form>


<!-- printable reports ----- -->
<?php echo apply_filters('rst_apply_printable_reports_filter','', $filter, $from, $to); ?>
<!-- ----- printable reports -->



<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

//mp_id,name,template,start_date,end_date,page_type,spin_type,status,

class Projects_Reposts_Table extends WP_List_Table
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
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
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

        if ($wpfeeoptions['rst_enable_fee'] == 'on') {
            $columns = array(
                'show_name' => 'Event Name',
                'show_date' => 'Event Date',
                'name' => 'Booked By',
                'email' => 'Email',
                'phone' => 'Phone',
                'ticket_no' => 'Booking ID',
                'ticket_seat_no' => 'Ticket No',
                'txn_id' => 'Paypal TXN ID',
                'seat_cost' => 'Seat Cost',
                'total_paid' => 'Total Paid',
                'c_code' => 'Coupon',
                'c_discount' => 'Coupon Discount',
                'fees' => $wpfeeoptions['fee_name'],
                'booking_time' => 'Booked On',
                'booking_status' => 'Booking Status',


            );
        } else {
            $columns = array(
                'show_name' => 'Event Name',
                'show_date' => 'Event Date',
                'name' => 'Booked By',
                'email' => 'Email',
                'phone' => 'Phone',
                'ticket_no' => 'Booking ID',
                'ticket_seat_no' => 'Ticket No',
                'txn_id' => 'Paypal TXN ID',
                'seat_cost' => 'Seat Cost',
                'total_paid' => 'Total Paid',
                'c_code' => 'Coupon',
                'c_discount' => 'Coupon Discount',
                'booking_time' => 'Booked On',
                'booking_status' => 'Booking Status',


            );
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

        $symbol = $rst_paypal_options['currencysymbol'];

        $symbols = array(
            "0" => "$",
            "1" => "&pound;",
            "2" => "&euro;",
            "3" => "&#3647;",
            "4" => "&#8362;",
            "5" => "&yen;");


        $symbol = $symbols[$symbol];


        $alldata = bookedtickets($_SESSION['rstfilter'], $_SESSION['rst_rpfrom'], $_SESSION['rst_rpto']);


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
<div class="wrap">


    <form id="movies-filter" method="get">
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <!-- Now we can render the completed list table -->
        <?php $testListTable->display() ?>
    </form>

</div>