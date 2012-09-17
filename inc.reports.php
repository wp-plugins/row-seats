<?php echo "  <h2>" . __('GSC Reports:', 'gsc') . "</h2>"; ?> 
<?php
$alldata = bookedtickets();

?>
  <?php echo "  <h3>" . __('Row Seats Booking Details:', 'gsc') . "</h3>"; ?>

    <p><?php echo __('Below are list of booked tickets.', 'gsc'); ?></p>

        <table cellspacing="0" class="widefat fixed">
            <thead>
            <tr class="thead">
                
                <th class="column-username" id="username" scope="col"
                    style="width: 100px;"><?php echo __('Event Name', 'gsc'); ?></th>
                    <th class="column-username" id="username" scope="col"
                    style="width: 100px;"><?php echo __('Event Date', 'gsc'); ?></th>
                <th class="column-name" id="name"
                    scope="col"><?php echo __('Booked By', 'gsc'); ?></th>
                    <th class="column-name" id="name"
                    scope="col"><?php echo __('Email', 'gsc'); ?></th>
                    <th class="column-name" id="name"
                    scope="col"><?php echo __('phone', 'gsc'); ?></th>
                    <th class="column-name" id="name"
                    scope="col"><?php echo __('Booking ID', 'gsc'); ?></th>
                    <th class="column-name" id="name"
                    scope="col"><?php echo __('Ticket No', 'gsc'); ?></th>
                         <th class="column-name" id="name"
                    scope="col"><?php echo __('Seat Cost', 'gsc'); ?></th>
                                         <th class="column-name" id="name"
                    scope="col"><?php echo __('Booked On', 'gsc'); ?></th>
            </tr>
            </thead>
            <tfoot>
            <tr class="thead">
          
                <th class="column-username" scope="col"
                    style="width: 100px;"><?php echo __('Event Name', 'gsc'); ?></th>
                    <th class="column-username" scope="col"
                    style="width: 100px;"><?php echo __('Event Date', 'gsc'); ?></th>
                <th class="column-name" scope="col"><?php echo __('Booked By', 'gsc'); ?></th>
                 <th class="column-name" scope="col"><?php echo __('Email', 'gsc'); ?></th>
                <th class="column-name" scope="col"><?php echo __('Phone', 'gsc'); ?></th>
                 <th class="column-name" scope="col"><?php echo __('Booking ID', 'gsc'); ?></th>
               
                <th class="column-name" scope="col"><?php echo __('Ticket No', 'gsc'); ?></th>
                <th class="column-name" scope="col"><?php echo __('Seat Cost', 'gsc'); ?></th>
                  <th class="column-name" scope="col"><?php echo __('Booked On', 'gsc'); ?></th>
                
            </tr>
            </tfoot>
        <?php
        
        for ($i=0;$i<count($alldata);$i++) {
           
            $data = $alldata[$i];
           
            
              
             ?>
            
                <tbody class="list:user user-list" id="users">
                <tr class="alternate" id="user-1">
                  
                    <td class="username column-username" style="width: 100px;">
                    <?php echo $data['show_name']; ?>
               </td>
                <td class="username column-username" style="width: 100px;">
                    <?php echo $data['show_date']; ?>
               </td>
               <td class="username column-username" style="width: 100px;">
                    <?php echo $data['name']; ?>
               </td>
                <td class="username column-username" style="width: 100px;">
                    <?php  echo $data['email']; ?>
               </td>
               <td class="username column-username" style="width: 100px;">
                    <?php  echo $data['phone']; ?>
               </td>
                <td class="username column-username" style="width: 50px;">
                    <?php  echo $data['ticket_no']; ?>
               </td>
               <td class="username column-username" style="width: 100px;">
                    <?php  echo $data['ticket_seat_no']; ?>
               </td>
                  
                  <td class="username column-username" style="width: 100px;">
                    <?php  echo $data['seat_cost']; ?>
               </td> 
                 <td class="username column-username" style="width: 100px;">
                    <?php  echo $data['booking_time']; ?>
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
                    <?php echo __('There are no booked tickets', 'gsc'); ?>
        </td>
                </tr>
                </tbody>
            <?php

        };
        ?>
          </table>
          
<hr />