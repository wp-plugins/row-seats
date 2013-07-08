<div class="wrap">

<script type="text/javascript">
    var RSTAJAXURL = '<?php echo RSTPLN_URL?>ajax.php';
</script>




    <link rel="stylesheet" type="text/css" media="all" href="<?php echo RSTPLN_CSSURL ?>lite-seats.css"/>




    <link rel="stylesheet" type="text/css" media="all" href="<?php echo RSTPLN_CSSURL ?>style.css"/>
<link rel="stylesheet" type="text/css" media="all" href="<?php echo RSTPLN_CSSURL ?>seats.css"/>



    <!-- pricing per seat inc scripts ----- -->
    <?php echo apply_filters('pricing_per_seat_inc_scripts_filter',''); ?>
    <!-- ----- pricing per seat inc scripts -->

    <!-- pricing per seat popup ----- -->
    <?php echo apply_filters('pricing_per_seat_popup_filter',''); ?>
    <!-- ----- pricing per seat popup -->




    <?php

$showid = '';
$formaction = '';
if (isset($_POST) && $_POST['Submit'] == 'Upload') {

    if (FALSE == empty($_FILES['rst_seats']['tmp_name'])) {
        $row = 1;
        $handle = fopen($_FILES['rst_seats']['tmp_name'], "r");
        $csvdata = array();
        $csvheaders = array();
        $k = 1;

        while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {

            if ($k == 4) {
                array_shift($data);
                $csvheaders['price'] = $data;

            } else if ($k == 6) {
                array_shift($data);
                $csvheaders['headers'] = $data;

            } else if ($k > 6) {
                array_shift($data);
                $csvdata[] = $data;

            }
            $k++;
        }


        $seatsperrow = array();
        $seats = array();
        $finalseats = array();
        for ($k = 0; $k < count($csvheaders['headers']); $k++) {
            $seatsperrow = array();
            $seats = array();
            $seats['row'] = $csvheaders['headers'][$k];
            $seats['price'] = number_format($csvheaders['price'][$k], 2, '.', '');
            for ($l = 0; $l < count($csvdata); $l++) {
                $seatsperrow[] = $csvdata[$l][$k];

            }
            $seats['seats'] = $seatsperrow;
            $finalseats[] = $seats;
        }

        echo rst_seats_operations('insert', $finalseats, $_POST['rst_showid']);


        fclose($handle);

    }
}
$showname = '';
$showid = '';

if (isset($_POST) && $_POST['showid'] != '' && $_POST['rstaction'] == 'show') {
    $formaction = 'show';
    $showid['id'] = $_POST['showid'];

    echo "<div id='current-show-id' style='display: none'>".$showid['id']."</div>";
    echo "<div id='showprview' align='center' style='max-width: 689px; -moz-user-select: none;margin-left: auto;margin-right: auto;' >";
    echo gettheadminseatchat($showid);
    echo "</div>";
    ?>

    </div>
    <div class="clear"></div>
    <div style="color:#7A0230 !important;padding-top:20px; "><strong>Help: To add this Seating Chart in any POST/PAGE
            use Respected short Codes below</strong></div>
    <div class="clear"></div>
    <?php
    // print_r($seats);
}

if (isset($_POST) && $_POST['showid'] != '' && $_POST['rstaction'] == 'setprice') {
    $showid['id'] = $_POST['showid'];
    $showid = $showid['id'];

    ?>


    <?php
    // print_r($seats);
}
if (isset($_POST) && $_POST['showid'] != '' && $_POST['rstaction'] == 'delete') {
    $data['id'] = $_POST['showid'];
    rst_shows_operations('delete', $data, '');

}
if (isset($_POST) && $_POST['showid'] != '' && $_POST['rstaction'] == 'edit') {
    $data = getshowbyid($_POST['showid']);
    $showname = $data[0]['show_name'];
    $showid = $data[0]['id'];

}
//echo rst_shows_operations('insert',$_POST);


?>
<?php
if ($showname != '') {
    echo "<h2>" . __('Upload seats to a Show:', 'rst') . "</h2>";
    ?>
    <div id="rstdiv">
        <form method="post" action="" name="showsform" class="showsform" enctype="multipart/form-data">
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><label for="name"><?php echo __('Show Name', 'rst'); ?><span
                                style="color: red;">*</span></label>
                    </th>
                    <td>
                        <input type="text" id="rst_showname" name="rst_showname" class="regular-text"
                               disabled="disabled" value="<?php echo $showname ?>"/>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="name"><?php echo __('Upload CSV file', 'rst'); ?><span
                                style="color: red;">*</span></label>
                    </th>
                    <td>
                        <input type="file" id="rst_seats" name="rst_seats"/>
                    </td>
                </tr>

                <input type="hidden" id="rst_showid" name="rst_showid" value="<?php echo $showid ?>"/>
                </tbody>

                </p>
            </table>
            <p class="submit">

                <input type="submit" value="Upload" class="button-primary" name="Submit"/>
                <a href="<?php echo RSTPLN_URL . 'download.php?f=seatingtemplate.csv' ?>" target="_blank">Download
                    Template</a>

            <div>
                <table>
                    <tr>
                        <td>
                            <span class="notbooked showseats">&nbsp;&nbsp;&nbsp;&nbsp;</span> Available - Represent as
                            (Y) in template<br/>
                            <span class="b showseats">&nbsp;&nbsp;&nbsp;&nbsp;</span> Unavailable - Represent as (N) in
                            template<br/>
                            <span class="booked showseats">&nbsp;&nbsp;&nbsp;&nbsp;</span> Already Booked - Represent as
                            (B) in template<br/>

                            <span class="handy showseats">&nbsp;&nbsp;&nbsp;&nbsp;</span> Handicap Accessiable
                            -Represent as (H) in template <br/>

                        </td>
                    </tr>

                </table>
            </div>

        </form>
    </div>
<?php
}
?>

<?php echo "  <h3>" . __('List of Shows:', 'rst') . "</h3>"; ?>

<p><?php echo __('Below are list of shows where you can see the details.', 'rst'); ?></p>

<form method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <table cellspacing="0" class="widefat fixed">
        <thead>
        <tr class="thead">
            <th class="column-username" id="username" scope="col"
                style="width: 100px;"><?php echo __('Show ID', 'rst'); ?></th>
            <th class="column-username" id="username" scope="col"
                style="width: 150px;"><?php echo __('Show Name', 'rst'); ?></th>
            <th class="column-name" id="name"
                scope="col"><?php echo __('Status', 'rst'); ?></th>
            <th class="column-name" id="name"
                scope="col"><?php echo __('Booked', 'rst'); ?></th>
            <th class="column-name" id="name"
                scope="col"><?php echo __('Actions', 'rst'); ?></th>
            <th class="column-name" scope="col"><?php echo __('Short Code', 'rst'); ?></th>
        </tr>
        </thead>
        <tfoot>
        <tr class="thead">
            <th class="column-username" scope="col"
                style="width: 100px;"><?php echo __('Show ID', 'rst'); ?></th>
            <th class="column-username" scope="col"
                style="width: 150px;"><?php echo __('Show Name', 'rst'); ?></th>
            <th class="column-name" scope="col"><?php echo __('Status', 'rst'); ?></th>
            <th class="column-name" scope="col"><?php echo __('Booked', 'rst'); ?></th>
            <th class="column-name" scope="col"><?php echo __('Actions', 'rst'); ?></th>
            <th class="column-name" scope="col"><?php echo __('Short Code', 'rst'); ?></th>
        </tr>
        </tfoot>
        <?php
        $alldata = rst_shows_operations('list', '', '');

        for ($i = 0; $i < count($alldata); $i++) {

            $data = $alldata[$i];
            $eventdate = $data['show_start_time'];
            $eventdate = date('Y-m-d H:i:s', strtotime($eventdate));
            $dateexpire = date('Y-m-d', strtotime($eventdate));
            if ($dateexpire < date('Y-m-d')) {
                $data['status'] = 'Show Ended';
            }
            if ($data['status'] == 'Seats Added') {
                $data['status'] = 'Running Show';
            }
            $bookings = getbookingdetailbyshow($data['id']);
            ?>

            <tbody class="list:user user-list" id="users">
            <tr class="alternate" id="user-1">
                <td class="username column-username" style="width: 100px;">
                    <?php echo $data['id']; ?>
                </td>
                <td class="username column-username" style="width: 150px;">
                    <?php echo $data['show_name']; ?>
                </td>
                <td class="username column-username" style="width: 100px;">
                    <?php echo $data['status']; ?>
                </td>
                <td class="username column-username" style="width: 100px;">
                    <?php echo '(' . $bookings . ')'; ?>
                </td>
                <td class="name column-name">

                    <div class="">
                        <?php
                        if ($data['status'] == 'empty') {
                            ?>
                            <span class='edit'>
                    <a href="javascript:void(0);" title="Edit this show" onclick="editform('<?= $data[id] ?>');">Add
                        Seats</a>
                    | </span>
                        <?php } else if ($bookings == 0) { ?>
                            <span class='edit'>
                    <a href="javascript:void(0);" title="Edit this show" onclick="editform('<?= $data[id] ?>');">Update
                        Seats</a>
                    | </span>
                        <?php } ?>
                        <span class='delete'>
                    <a class='submitdelete' title='Delete this show'
                       href="javascript:void(0);"
                       onclick="deleteform('<?= $data[id] ?>');">Delete</a> |</span>
                    <span class='edit'>
                    <a href="javascript:void(0);" title="Edit this show" onclick="SeetheShow('<?= $data[id] ?>');">See
                        the Show</a>
                    | </span>

                    </div>
                </td>
                <td class="username column-username" style="width: 100px;">
                    [showseats id=<?php echo $data[id]; ?>]
                </td>
            </tr>
            </tbody>
        <?php

        }
        if (count($alldata) < 1) {
            ?>

            <tbody class="list:user user-list" id="users">
            <tr class="alternate" id="user-1">
                <th class="check-column" scope="row">
                </th>
                <td class="name column-name" colspan="2">
                    <?php echo __('There are no Shows yet', 'rst'); ?>
                </td>
            </tr>
            </tbody>
        <?php

        };
        ?>
    </table>
</form>


<form method="post" action="" name="rstactions">
    <input type="hidden" value="" name="rstaction" id="rstaction"/>
    <input type="hidden" value="" name="showid" id="showid"/>
</form>
<script>
    function editform(id) {
        document.getElementById('rstaction').value = 'edit';
        document.getElementById('showid').value = id;
        document.rstactions.submit();
    }
    function SeetheShow(id) {
        document.getElementById('rstaction').value = 'show';
        document.getElementById('showid').value = id;
        document.rstactions.submit();
    }
    function setprice(id) {
        document.getElementById('rstaction').value = 'setprice';
        document.getElementById('showid').value = id;
        document.rstactions.submit();

    }
    function deleteform(id) {
        var r = confirm("Are you sure you want to delete the show!");
        if (r == true) {
            document.getElementById('rstaction').value = 'delete';
            document.getElementById('showid').value = id;
            document.rstactions.submit();
        }
        else {

        }

    }
    function getupdatedshow(id) {

        jQuery.post(RSTAJAXURL + '?action=booking',
            {
                'details': id

            },
            function (msg) {
                jQuery('#showprview').html((msg));
                jQuery('.seatplan .showseats').each(function (i) {


                    if (jQuery(this).attr('rel') == 'Y') {
                        jQuery(this).click(function () {

                            getupdatedshow(jQuery(this).attr('id'));

                        });
                    }

                });
                jQuery('.contact').click(function () {
                    loadPopupBox();
                });
                jQuery('#popupBoxClose').click(function () {
                    unloadPopupBox();
                });


                function unloadPopupBox() {	// TO Unload the Popupbox
                    jQuery('#popup_box').fadeOut("slow");
                    jQuery("#container").css({ // this is just for style
                        "opacity": "1"
                    });
                }

                function loadPopupBox() {	// To Load the Popupbox
                    jQuery('#popup_box').fadeIn("slow");
                    jQuery("#container").css({ // this is just for style
                        "opacity": "0.3"
                    });
                }
            });
    }

    <?php

    if($formaction=='show1'){
       ?>
    getupdatedshow('<?php echo $showid?>');
    <?php

 }?>


</script>



<?php
// pricing per seat -----
echo apply_filters('pricing_per_seat_script_filter','');
// ----- pricing per seat
?>









</div>
