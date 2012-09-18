<link rel="stylesheet" type="text/css" media="all" href="<?php echo GSCPLN_CSSURL ?>style.css" />
<?php
$updated = '';
if(isset($_POST) && $_POST['Submit']=='Save Settings'){
update_option(GSCPLN_OPTIONS,$_POST); 
$updated = 'yes'; 
}
$gsc_options = get_option(GSCPLN_OPTIONS);
if($gsc_options['gsc_adminetemp']==''){
$gsc_options['gsc_adminetemp'] = "Dear <blogname>,
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
if($gsc_options['gsc_etemp']==''){
$gsc_options['gsc_etemp'] = "Dear <username>,

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
<div id="post-body-content"  style="width: 800px !important; float:left !important;">
<?php if($updated!='') echo '<div class="updated"><p><strong>Settings Saved</strong></p></div>';?>

<div  class="stuffbox">
<h3><label for="link_name"><?php _ex('Show Chart Settings:', 'gsc') ?></label></h3>
<div class="inside">
  <form method="post" action="" name="gscform" class="gscform">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row"><label for="name"><?php echo __('Email', 'gsc'); ?><span style="color: red;">*</span></label>
                </th>
                <td>
                    <input type="text" id="gsc_email" name="gsc_email" class="regular-text" value="<?php echo $gsc_options['gsc_email']; ?>"/>
                </td>
            </tr>
             <tr valign="top">
                <th scope="row"><label for="name"><?php echo __('Ticket Prefix', 'gsc'); ?><span style="color: red;">*</span></label>
                </th>
                <td>
                    <input type="text" id="gsc_ticket_prefix" name="gsc_ticket_prefix" class="regular-text" value="<?php echo $gsc_options['gsc_ticket_prefix']; ?>"/>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><label for="quote"><?php echo __('Terms & Conditions', 'gsc'); ?><span style="color: red;">*</span></label>
                </th>
                <td>
                    <textarea class="large-text code" id="gsc_tandc" cols="40" rows="5" name="gsc_tandc"><?php echo $gsc_options['gsc_tandc']; ?></textarea>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="name"><?php echo __('User Email Template', 'gsc'); ?></label>
                </th>
                <td>
                    <textarea class="large-text code" id="gsc_etemp" cols="40" rows="15" name="gsc_etemp"><?php echo $gsc_options['gsc_etemp']; ?></textarea>
               </td>
            </tr>
          <tr valign="top">
                <th scope="row"><label for="name"><?php echo __('Admin Email Template', 'gsc'); ?></label>
                </th>
                <td>
                    <textarea class="large-text code" id="gsc_adminetemp" cols="40" rows="15" name="gsc_adminetemp"><?php echo $gsc_options['gsc_adminetemp']; ?></textarea>
               </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="name"><?php echo __('Minutes to Wait for Release the blocked seats', 'gsc'); ?></label>
                </th>
                <td>
                <input type="text" id="gsc_release_min" name="gsc_release_min" class="regular-text" value="<?php echo $gsc_options['gsc_release_min']; ?>"/>
               </td>
            </tr>
            <tr valign="top" >
                <th scope="row"><label for="name"><?php echo __('Select theme', 'gsc'); ?><span style="color: red;">*</span></label>
                </th>
                <td>
                <select name="gsc_theme" id="gsc_theme">
                <option value="lite.css" <?php  if($gsc_options['gsc_theme']=='lite.css')echo 'selected';?>>Lite</option>
                <option value="dark.css" <?php  if($gsc_options['gsc_theme']=='dark.css')echo 'selected';?>>Dark</option>
               
                 </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="name"><?php echo __('Disable JQuery', 'gsc'); ?><span style="color: red;">*</span></label>
                </th>
                <td>
                <input name="gsc_enable_jquery" id="gsc_enable_jquery" type="checkbox" value="off" <?php  if($gsc_options['gsc_enable_jquery']=='off' ) echo 'checked'; ?>/>
                </td>
            </tr>
             <tr valign="top">
                <th scope="row">
                </th>
                <td>
                 <p style="float:right;">
        <input type="submit" value="Save Settings" class="button-primary" name="Submit"/>
          </p> </td>
            </tr>
            <tbody>

        
        </table>
        
       
    </form>
  
</div>
</div>


</div>
</div>


</div>
    
 <div id="poststuff" style="float: left;width: 240px;padding-left: 10px;padding-top:none !important;padding-top: 0px !important;">

<div id="post-body">
<div id="post-body-content" >
    <div  class="stuffbox">
    <h3><label for="link_name"><?php _ex('Row Seats Advance:', 'gsc') ?></label></h3>
<div class="inside" >

    <p><a href="http://www.wpthemesforevents.com/row-seats-plugin" target="_blank"><img style="cursor:pointer;" src="<?php echo GSCPLN_URL?>images/row-seats-ad-220x500.jpg"/></a>
    </a></p>
    <p><a href="http://www.wpthemesforevents.com/donations" target="_blank" style="cursor:pointer;">If you find this plugin useful,<br /> please consider a donation</a>
    </p>
  
  
</div>
</div></div>
</div></div>