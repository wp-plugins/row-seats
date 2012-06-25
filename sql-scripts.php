<?php 
function gsc_create_tables()
{ global $wpdb;
       if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb->booking_seats_relation'") != $wpdb->booking_seats_relation)
    {
        $sql = "CREATE TABLE " . $wpdb->booking_seats_relation . " (  
      id int(11) NOT NULL AUTO_INCREMENT,   
      ticket_no varchar(20) NOT NULL,  
      ticket_seat_no  varchar(20) NOT NULL,
      booking_id int(11) NOT NULL ,
      show_id int(11) NOT NULL ,
      total_paid decimal(10,2) NULL,
      txn_id varchar(50)  NULL,
      seat_cost decimal(10,2) NULL,
      PRIMARY KEY (id),  
      KEY id (id)  
    );";
        mysql_query($sql);
    }
    if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb->gsc_customer_session'") != $wpdb->gsc_customer_session)
    {
        //sessionid,gsc_session_id,show_id,rowname,seatno,price,session_time,status
        $sql = "CREATE TABLE " . $wpdb->gsc_customer_session . " (  
      sessionid int(11) NOT NULL AUTO_INCREMENT,   
      gsc_session_id varchar(50) NOT NULL,  
      show_id int(11) NOT NULL,  
      rowname varchar(2) NOT NULL,
      seatno int(2) NOT NULL,   
      price decimal(10,2) NOT NULL,
      session_time timestamp NOT NULL,
      status  varchar(10) NOT NULL,
      PRIMARY KEY (sessionid),  
      KEY id (sessionid)  
    );";
        mysql_query($sql);
    }
    if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb->gsc_bookings'") != $wpdb->gsc_bookings)
    {
         $sql = "CREATE TABLE " . $wpdb->gsc_bookings . " (  
      booking_id int(11) NOT NULL AUTO_INCREMENT,
       show_id int(11) NOT NULL ,    
      gsc_session_id varchar(50) NOT NULL,  
      paypal_vars text  NULL,   
      booking_time timestamp NOT NULL,
      booking_details text  NULL,
      payment_status varchar(25) NOT NULL,
	  name  varchar(255) NOT NULL,
      email  varchar(255) NOT NULL,
      phone  varchar(50) NOT NULL,
      paypal_mode varchar(10) NOT NULL,
      ticket_no varchar(20) NOT NULL,
      PRIMARY KEY (booking_id),  
      KEY id (booking_id)  
    );";
        mysql_query($sql);
    }
    if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb->gsc_shows'") != $wpdb->gsc_shows)
    {
        //id,show_name,show_start_time,show_end_time,show_date,venue,,status,created_date,mod_date,mod_by
        $sql = "CREATE TABLE " . $wpdb->gsc_shows . " (  
      id int(10) NOT NULL AUTO_INCREMENT,   
      show_name varchar(255) NOT NULL,  
      show_start_time varchar(255) NOT NULL,  
      show_end_time varchar(255)  NULL,
      show_date date  NOT NULL,
      venue text NOT NULL,  
      allday  tinyint(1) NOT NULL,
      status varchar(50)NOT NULL,
      created_date datetime NULL,
      mod_date datetime NULL,
	  mod_by  varchar(255) NOT NULL,
      PRIMARY KEY (id),  
      KEY id (id)  
    );";
        mysql_query($sql);
    }
    if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb->gsc_seats'") != $wpdb->gsc_seats)
    {
 //seatid,show_id,row_name,total_seats_per_row,seatno,seattype,originaltype,seat_price,discount_price,created_date,mod_date,mod_by
        $sql = "CREATE TABLE " . $wpdb->gsc_seats . " (  
      seatid int(10) NOT NULL AUTO_INCREMENT,   
      show_id int(10) NOT NULL,  
      row_name varchar(2) NOT NULL,  
      total_seats_per_row int(10)  NULL,
      seatno int(11) NOT NULL,
      seattype varchar(1) NULL,
      originaltype varchar(1) NULL,  
      seat_price decimal(10,2) NOT NULL, 
      discount_price decimal(10,2) NOT NULL, 
      status varchar(20) NULL,
      blocked_time timestamp  NULL,
      created_date datetime NULL,
      mod_date datetime NULL,
	  mod_by  varchar(255) NOT NULL,
      PRIMARY KEY (seatid),  
      KEY id (seatid)  
    );";
    
        mysql_query($sql);
    }
    
    
}