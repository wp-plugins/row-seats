<?php 
function gsc_seats_operations($action,$finalseats,$showid)
{   global $wpdb;
     $modby = $current_user->user_login;
  
	switch ($action) {
		case 'delete':
		 	return $wpdb->query("DELETE FROM $wpdb->gsc_seats WHERE id='$showid'");
            return true;
           
		break;
		case 'insert':
                $showid = $showid;
                $sql = "SELECT * FROM $wpdb->gsc_seats where show_id=".$showid;
           	$found = 0;
           
            if ($results = $wpdb->get_results($sql, ARRAY_A)) {
		      
            
			foreach ($results as $value) {
			 
			$found++;
            }
            if($found!=0){
                delete_seats('delete','',$showid);
                
                }
		      }
            
              
              $curdate = date( 'Y-m-d H:i:s');
                 for($i=0;$i<count($finalseats);$i++){
                $data = $finalseats[$i];
                $name = $data['row'];
                $seat_price = $data['price'];
                if($seat_price==''){
                    $seat_price = 0.00;
                }
                $discount_price = $data['price'];
                  if($discount_price==''){
                    $discount_price = 0.00;
                }
                $seats = ($data['seats']);
                $total_seats_per_row = count($seats);
                $seatno = 0;
               
                      for($j=0;$j<count($seats);$j++){
                        
                        
                        $seattype = $seats[$j];
                        if($seattype=='S'){
                           
                        }
                       
                        if($seattype!=''){
                           $seatno++;  
                        }
          
          $sql="INSERT INTO $wpdb->gsc_seats (show_id,row_name,total_seats_per_row,seatno,seattype,originaltype,seat_price,discount_price,status,created_date,mod_date,mod_by) 
        VALUES ($showid, '$name', '$total_seats_per_row',$seatno,'$seattype','$seattype',$seat_price,$discount_price,'not blocked','$curdate','$curdate','$modby')";     
                    $wpdb->query($sql);  
                        }
                        }
                
                gsc_shows_operations('updatestatus',array('status'=>'Seats Added','id'=>$showid),'');
                
               
                return '<div class="updated"><p><strong>Seats Added..</strong></p></div>';
          
             
           
            
            break;
      
    	case 'update':
                $name = $data['title'];
                $venue = $data['body'];
                $showstart = $data['start'];
                $showend = $data['end'];
                $status = $data['status'];
                $curdate = date( 'Y-m-d H:i:s');
                $showdate  = date('Y-m-d H:i:s', strtotime($showstart));
                $id = $data['id'];
      	$wpdb->query("UPDATE  $wpdb->gsc_shows SET show_name='$name',show_start_time='$showstart',show_end_time='$showend',show_date='$showdate',venue='$venue',status='',mod_date='$curdate',mod_dy='$modby' WHERE id=".$id);
        return '<div class="updated"><p><strong>Testimonial Updated..</strong></p></div>';
   
    
            break;
        case 'list':
             
	        $sql = "SELECT * FROM $wpdb->gsc_seats where show_id=". $showid." order by seatid ";
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
             case 'reverse':
             
	        $sql = "SELECT * FROM $wpdb->gsc_seats where show_id=". $showid." order by seatid ";
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
                $finaldata = array();
                $reversed = array();
                $initialrow = $data[0]['row_name'];
                for($i=0;$i<count($data);$i++){
                  
                  if($initialrow==$data[$i]['row_name'] ){
                    
                    $finaldata[] = $data[$i];
                    if($i==count($data)-1){
                    $finaldata =   array_reverse($finaldata);
                    for($j=0;$j<count($finaldata);$j++){
                    $reversed[] = $finaldata[$j];
                    }}
                  
                  }else{
                  $finaldata =   array_reverse($finaldata);
                   for($j=0;$j<count($finaldata);$j++){
                    $reversed[] = $finaldata[$j];
                  }
                  
                  $finaldata = array();
                  $initialrow = $data[$i]['row_name'];
                  $finaldata[] = $data[$i];
                  }
                    
                    
                }
                
            
               return( $reversed); 
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