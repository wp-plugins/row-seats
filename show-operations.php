<?php 
function gsc_shows_operations($action,$data,$currentcart)
{       
	  
   
    global $wpdb;
     $modby = $current_user->user_login;
  
	switch ($action) {
	   case 'releasecurrentcart':
       $totalseatstorelase = array();
       $showid = $data;
       
      for($i=0;$i<count($currentcart);$i++){
        $row = $currentcart[$i]['row_name'];
        $showid = $currentcart[$i]['show_id'];
        $seat = $currentcart[$i]['seatno'];
        $sql = "SELECT * FROM gsc_seats st,gsc_shows sh
                    WHERE
                    sh.id=st.show_id AND
                    st.row_name = '$row' AND
                    seattype<>'' AND
                    seatno = $seat AND
                    sh.id =".$showid;
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
            $data = $data[0];
            $dicount=0;
            if($data['seattype']=='T'){
                
            $dicount = $data['seat_price'];
            $originaltype = $data['originaltype'];
            
            $sql="UPDATE  $wpdb->gsc_seats SET seattype='$originaltype',discount_price=$dicount,status='not blocked' WHERE show_id=".$showid." AND row_name='$row' AND seattype<>'' AND seatno=".$seat;       
           
            $wpdb->query($sql);
           
            gsc_session_operations('deletebookingseat',$currentcart[$i]);
            
           
            }     
            }
            }
        
        }
      return array();
       break;
	   case 'releaseall':
       $totalseatstorelase = array();
       $showid = $data;
      $gsc_options = get_option(GSCPLN_OPTIONS);
      $gsc_release_min = $gsc_options['gsc_release_min'];
      if($gsc_release_min==''){
        $gsc_release_min = 15;
      }
            ////////////////////////////
            $sql ="SELECT * FROM  $wpdb->gsc_seats  WHERE show_id=$showid AND status='blocked' AND blocked_time < (now()- INTERVAL $gsc_release_min MINUTE)";
            if ($results = $wpdb->get_results($sql, ARRAY_A)) {
		    $allblockedseats = $wpdb->get_results($sql, ARRAY_A); 
                        for($i=0;$i<count($allblockedseats);$i++){
                            $allblockedseat = $allblockedseats[$i];
                             $originaltype = $allblockedseat['originaltype'];
                             $dicount = $allblockedseat['seat_price'];
                             $row = $allblockedseat['row_name'];
                             $seatno = $allblockedseat['seatno'];
                             $seatid = $allblockedseat['seatid'];
                            if($allblockedseat['seattype']=='T')
                             $sql="UPDATE  $wpdb->gsc_seats SET seattype='$originaltype',discount_price=$dicount,status='not blocked' WHERE show_id=".$showid." AND row_name='$row' AND seattype<>'' AND seatno=$seatno AND seatid=".$seatid;       
                             $wpdb->query($sql);
                            
                            
                            }
            }
      
      
       break;
       
	   	case 'savebooking':
	   $gsc_session_id = session_id();
    
    $cartitems = unserialize($currentcart);
    
    
    $booking_details = $cartitems;
    $paypal_vars = '';
    $booking_time = date('Y-m-d H:i:s');
    $showid = $cartitems[0]['show_id'];
    $booking_detail = $currentcart;
    $username = $data['name'];
    $useremail = $data['email'];
    $phone = $data['phone'];
    $status = $data['status'];
    $gsc_pp_options = get_option(GSCPLN_PPOPTIONS);
    $papalmode ='';
    if($gsc_pp_options['paypal_url']=='https://www.sandbox.paypal.com/cgi-bin/webscr'){
    $papalmode = 'Test';
    }else{
     $papalmode = 'Live';   
    }
    
    $sql="INSERT INTO $wpdb->gsc_bookings (show_id,gsc_session_id,paypal_vars,booking_time,booking_details,payment_status,name,email,phone,paypal_mode,ticket_no) 
   VALUES ($showid, '$gsc_session_id', '$paypal_vars',now(),'$booking_detail','$status','$username','$useremail','$phone','$papalmode','')";
   // ,gsc_session_id,paypal_vars,booking_time,booking_details,payment_status,name,email,phone
 $wpdb->query($sql);

 
return mysql_insert_id();
           
		break;
        case 'deleteall':
       
		  for($row=0;$row<count($data);$row++){
            $seatno = $data[$row]['seatno'];
            $showid = $data[$row]['show_id'];
            $rowname = $data[$row]['row_name'];
                $sql = "SELECT * FROM gsc_seats st,gsc_shows sh
                    WHERE
                    sh.id=st.show_id AND
                    st.row_name = '$rowname' AND
                    st.seatno = $seatno AND
                    st.seattype <> '' AND
                    sh.id =".$showid;
                   
            $seatdata = $wpdb->get_results($sql, ARRAY_A);
            $seatid  = $seatdata[0]['seatid'];
             $seattype = $seatdata[0]['originaltype'];
            if($seatid!=""){
            $wpdb->query("UPDATE  $wpdb->gsc_seats SET seattype='$seattype',status='not blocked' WHERE seatid=".$seatid);
                
            }
           
            
          }
          return $showid;
		break;
	   	case 'deletebooking':
			 	 $details = $data['details'];
             $data1 = explode('_',$details);
             if(count($data1)==1){
                return $data1[0];
             }
             $showid =  $data1[1];
             $row =  $data1[2];
             $seat =  $data1[3];
             $bookings['show_id'] = $showid;
             $bookings['row_name'] = $row;
             $bookings['seatno'] = $seat;             
            
            
            $sql = "SELECT * FROM gsc_seats st,gsc_shows sh
                    WHERE
                    sh.id=st.show_id AND
                    st.row_name = '$row' AND
                    seattype<>'' AND
                    seatno = $seat AND
                    sh.id =".$showid;
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
            $data = $data[0];
            $dicount=0;
            if($data['seattype']=='T'){
                
            $dicount = $data['seat_price'];
            $originaltype = $data['originaltype'];
            
            $sql="UPDATE  $wpdb->gsc_seats SET seattype='$originaltype',discount_price=$dicount,status='not blocked' WHERE show_id=".$showid." AND row_name='$row' AND seattype<>'' AND seatno=".$seat;       
           
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
             $data1 = explode('_',$details);
             if(count($data1)==1){
                return $data1[0];
             }
                                        
         
             $showid =  $data1[1];
             $row =  $data1[2];
             $seat =  $data1[3];
             $bookings['show_id'] = $showid;
             $bookings['row_name'] = $row;
             $bookings['seatno'] = $seat;             
            
            
            $sql = "SELECT * FROM gsc_seats st,gsc_shows sh
                    WHERE
                    sh.id=st.show_id AND
                    st.row_name = '$row' AND
                    seattype<>'' AND
                    seatno = $seat AND
                    sh.id =".$showid;
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
            
            $data = $data[0];
            $dicount=0;
                
            if($data['seattype']=='Y'){
              
            $dicount = $data['seat_price'];
            $bookings['price'] = $dicount;  
            $sql="UPDATE  $wpdb->gsc_seats SET seattype='T',status='blocked',blocked_time=now() WHERE show_id=".$showid." AND row_name='$row' AND seattype<>'' AND seatno=".$seat;       
           
            $wpdb->query($sql);
           
            $finalbookings[] = $bookings;  
            }else if($data['seattype']=='H'){
             
              $gsc_options = get_option(GSCPLN_OPTIONS);
                $dicount = $gsc_options['gsc_h_disc'] ;
       if($dicount!=''){
        $dicount = $data['seat_price'] - ($data['seat_price']*($dicount/100));
        $dicount = round($dicount, 2);
        
       }else{
        $dicount = $data['seat_price'];
       }
       $bookings['price'] = $dicount;  
       $sql="UPDATE  $wpdb->gsc_seats SET seattype='T',discount_price=$dicount,status='blocked',blocked_time=now() WHERE show_id=".$showid." AND row_name='$row' AND seattype<>'' AND seatno=".$seat;       
           
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
			$wpdb->query("DELETE FROM $wpdb->gsc_shows WHERE id='$id'");
            return true;
           
		break;
		case 'insert':
                $name = $data['title'];
                $venue = $data['body'];
                $showstart = $data['start'];
                $showend = $data['end'];
                $status = $data['status'];
                $curdate = date( 'Y-m-d H:i:s');
                $showdate  = date('Y-m-d H:i:s', strtotime($showstart));
                $allday  = $data['allday'];

              	
                if(($name==''))
                return 'Please fill all the fields';
                $wpdb->query("INSERT INTO $wpdb->gsc_shows (show_name,show_start_time,show_end_time,show_date,venue,allday,status,created_date,mod_date,mod_by) VALUES ('$name', '$showstart', '$showend','$showdate','$venue',$allday,'$status','$curdate','$curdate','$modby')");
		     return mysql_insert_id();
             
           
            
            break;
      
    	case 'update':
                 $name = $data['title'];
                $venue = $data['body'];
                $showstart = $data['start'];
                $showend = $data['end'];
                $status = $data['status'];
                $curdate = date( 'Y-m-d H:i:s');
                $showdate  = date('Y-m-d H:i:s', strtotime($showstart));
                 $allday  = $data['allday'];
                $id = $data['id'];
      	$wpdb->query("UPDATE  $wpdb->gsc_shows SET show_name='$name',show_start_time='$showstart',show_end_time='$showend',show_date='$showdate',venue='$venue',allday=$allday,status='',mod_date='$curdate',mod_by='$modby' WHERE id=".$id);
        return '<div class="updated"><p><strong>Testimonial Updated..</strong></p></div>';
   
    case 'updatestatus':
  
                $status = $data['status'];
                $curdate = date( 'Y-m-d H:i:s');
                $id = $data['id'];
      	$wpdb->query("UPDATE  $wpdb->gsc_shows SET status='$status',mod_date='$curdate',mod_by='$modby' WHERE id=".$id);
        
            break;
        case 'list':
             
	        $sql = "SELECT * FROM $wpdb->gsc_shows " ;
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
	        
    
		break;
         
        	case 'byid':
            
            $id = $data['vmid'];
            $sql = "SELECT * FROM $wpdb->gsc_shows where id=".$id;
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
            
            break;
             
        default:
        
        	break;
	}
    
}