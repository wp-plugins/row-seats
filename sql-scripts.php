<?php

function rst_create_tables()
{
    global $wpdb;

    /*if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb->rst_coupons'") != $wpdb->rst_coupons) {
        $sql = "CREATE TABLE " . $wpdb->rst_coupons . " (
      id int(11) NOT NULL AUTO_INCREMENT,
      c_code varchar(20) NOT NULL,
      c_name varchar(20) NOT NULL,
      valide_from  timestamp NOT NULL,
      valide_to  timestamp NOT NULL,
      type  varchar(12) NOT NULL,
      discount_amount decimal(10,2) NULL,
      total_exceeds decimal(10,2) NULL,
      show_id int(11) NOT NULL ,
      timestoapply  int(11)  NULL ,
      create_date timestamp NOT NULL,
      moddate timestamp NOT NULL,
      modby varchar(50) NOT NULL,
      PRIMARY KEY (id),
      KEY id (id)
    );";
        mysql_query($sql);
    }*/

    if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb->rst_booking_seats_relation'") != $wpdb->rst_booking_seats_relation) {
        $sql = "CREATE TABLE " . $wpdb->rst_booking_seats_relation . " (
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
       // mysql_query($sql);
		$wpdb->query($sql);
		$sqla = "ALTER TABLE " . $wpdb->rst_booking_seats_relation . " CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci";
		$wpdb->query($sqla);
    }

    if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb->rst_customer_session'") != $wpdb->rst_customer_session) {
        //sessionid,rst_session_id,show_id,rowname,seatno,price,session_time,status
        $sql = "CREATE TABLE " . $wpdb->rst_customer_session . " (
      sessionid int(11) NOT NULL AUTO_INCREMENT,
      rst_session_id varchar(50) NOT NULL,
      show_id int(11) NOT NULL,
      rowname varchar(20) NOT NULL,
      seatno varchar(50) NOT NULL,
      price decimal(10,2) NOT NULL,
      session_time timestamp NOT NULL,
      status  varchar(10) NOT NULL,
      PRIMARY KEY (sessionid),
      KEY id (sessionid)
    );";
        //mysql_query($sql);
		$wpdb->query($sql);
		$sqla = "ALTER TABLE " . $wpdb->rst_customer_session . " CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci";
		$wpdb->query($sqla);		
		
    }

    if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb->rst_bookings'") != $wpdb->rst_bookings) {
        $sql = "CREATE TABLE " . $wpdb->rst_bookings . " (
      booking_id int(11) NOT NULL AUTO_INCREMENT,
       show_id int(11) NOT NULL ,
      rst_session_id varchar(50) NOT NULL,
      paypal_vars text  NULL,
      booking_time timestamp NOT NULL,
      booking_details text  NULL,
      payment_status varchar(25) NOT NULL,
	  name  varchar(255) NOT NULL,
      email  varchar(255) NOT NULL,
      phone  varchar(50) NOT NULL,
      paypal_mode varchar(10) NOT NULL,
      ticket_no varchar(20) NOT NULL,
      c_code varchar(20) NULL,
      c_discount decimal(10,2) NULL,
      fees decimal(10,2) NULL,
      PRIMARY KEY (booking_id),
      KEY id (booking_id)
    );";
        //mysql_query($sql);
		$wpdb->query($sql);
			$sqla = "ALTER TABLE " . $wpdb->rst_bookings . " CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci";
		$wpdb->query($sqla);		
		
    }

    if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb->rst_shows'") != $wpdb->rst_shows) {
        //id,show_name,show_start_time,show_end_time,show_date,venue,,status,created_date,mod_date,mod_by
        $sql = "CREATE TABLE " . $wpdb->rst_shows . " (
      id int(10) NOT NULL AUTO_INCREMENT,
      show_name varchar(255) NOT NULL,
      show_start_time varchar(255) NOT NULL,
      show_end_time varchar(255)  NULL,
      show_date date  NOT NULL,
      venue text NOT NULL,
      allday  tinyint(1) NOT NULL,
      status varchar(50)NOT NULL,
      orient varchar(1)NOT NULL,
      created_date datetime NULL,
      mod_date datetime NULL,
	  mod_by  varchar(255) NOT NULL,
      PRIMARY KEY (id),
      KEY id (id)
    );";
        //mysql_query($sql);
		$wpdb->query($sql);
			$sqla = "ALTER TABLE " . $wpdb->rst_shows . " CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci";
		$wpdb->query($sqla);		
		
    }

    if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb->rst_seats'") != $wpdb->rst_seats) {
        //seatid,show_id,row_name,total_seats_per_row,seatno,seattype,originaltype,seat_price,discount_price,created_date,mod_date,mod_by
        $sql = "CREATE TABLE " . $wpdb->rst_seats . " (
      seatid int(10) NOT NULL AUTO_INCREMENT,
      show_id int(10) NOT NULL,
      row_name varchar(20) NOT NULL,
      total_seats_per_row int(10)  NULL,
      seatno varchar(50) NOT NULL,
      seattype varchar(1) NULL,
      originaltype varchar(1) NULL,
      seat_price decimal(10,2) NOT NULL,
      discount_price decimal(10,2) NOT NULL,
      status varchar(20) NULL,
      blocked_time timestamp  NULL,
      created_date datetime NULL,
      mod_date datetime NULL,
	  mod_by  varchar(255) NOT NULL,
	  seatcolor int(10) NOT NULL DEFAULT '0',
      PRIMARY KEY (seatid),
      KEY id (seatid)
    );";

        //mysql_query($sql);
		$wpdb->query($sql);
			$sqla = "ALTER TABLE " . $wpdb->rst_seats . " CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci";
		$wpdb->query($sqla);		
		
    }
	
    if ($wpdb->get_var("SHOW TABLES LIKE 'rst_payment_transactions'") != 'rst_payment_transactions') {
        //seatid,show_id,row_name,total_seats_per_row,seatno,seattype,originaltype,seat_price,discount_price,created_date,mod_date,mod_by
        $sql = "CREATE TABLE  rst_payment_transactions (
  id int(11) NOT NULL AUTO_INCREMENT,
  tx_str varchar(31) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  payer_name varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  payer_email varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  gross float DEFAULT NULL,
  currency varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  payment_status varchar(31) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  transaction_type varchar(31) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  details text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  created int(11) DEFAULT NULL,
  deleted int(11) DEFAULT '0',
  show_name varchar(200) NOT NULL,
  show_date date NOT NULL,
  coupon_code varchar(200) NOT NULL,
  coupon_discount float NOT NULL,
  special_fee float NOT NULL,
  ticket_no varchar(200) NOT NULL,
  seat_numbers varchar(200) NOT NULL,
  seat_cost varchar(200) NOT NULL,
  custom varchar(200) NOT NULL,
  first_name varchar(200) NOT NULL,
  last_name varchar(200) NOT NULL,
  address varchar(200) NOT NULL,
  city varchar(200) NOT NULL,
  state varchar(200) NOT NULL,
  zip varchar(200) NOT NULL,
  country varchar(200) NOT NULL,
  phone varchar(200) NOT NULL,
  UNIQUE KEY id (id)
);";

        //mysql_query($sql);
		$wpdb->query($sql);
		
			$sqla = "ALTER TABLE rst_payment_transactions CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci";
		$wpdb->query($sqla);	

		
    }	
	
	
    if ($wpdb->get_var("SHOW TABLES LIKE 'rst_seat_colors'") != 'rst_seat_colors') {
        //seatid,show_id,row_name,total_seats_per_row,seatno,seattype,originaltype,seat_price,discount_price,created_date,mod_date,mod_by
        $sql = "CREATE TABLE rst_seat_colors (
  color_id int(10) NOT NULL AUTO_INCREMENT,
  color_name varchar(100) NOT NULL,
  color_code varchar(100) NOT NULL,
  color_show_id int(10) NOT NULL,
  PRIMARY KEY (color_id)
);";

       // mysql_query($sql);
		$wpdb->query($sql);
    }		
	
			$sqlexist="SHOW columns from rst_bookings where field='user_id'";
			$sqlexist_details = $wpdb->get_results($sqlexist, ARRAY_A);
			if(count($sqlexist_details)==0)
			{
            $sql = "ALTER TABLE `rst_bookings` ADD `user_id` INT( 10 ) NOT NULL DEFAULT '0'";
            $wpdb->query($sql);
			}	
			
$sqlexist="SELECT CHARACTER_MAXIMUM_LENGTH
FROM INFORMATION_SCHEMA.COLUMNS
WHERE COLUMN_NAME = 'row_name'
AND table_name = 'rst_seats'
LIMIT 1 ";

    if ($wpdb->get_var($sqlexist) != 20) {
	
            $sql = "ALTER TABLE `rst_seats` CHANGE `row_name` `row_name` VARCHAR(20)";
            $wpdb->query($sql);	
}	



$sqlexist="SELECT CHARACTER_MAXIMUM_LENGTH
FROM INFORMATION_SCHEMA.COLUMNS
WHERE COLUMN_NAME = 'rowname'
AND table_name = 'rst_customer_session'
LIMIT 1 ";

    if ($wpdb->get_var($sqlexist) != 20) {
	
            $sql = "ALTER TABLE `rst_customer_session` CHANGE `rowname` `rowname` VARCHAR(20)";
            $wpdb->query($sql);	
}
			
			

    /*if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb->rst_memebers'") != $wpdb->rst_memebers) {
        $sql = "CREATE TABLE " . $wpdb->rst_memebers . " (
      id int(10) NOT NULL AUTO_INCREMENT,
      member_name varchar(250)  NULL,
      member_id varchar(250) NOT NULL,
      nof_free_seates int(10) NOT NULL,
      show_id int(10) NOT NULL,
      date_joined date NULL,
      seats_avail int(10)  NULL,
      created_date datetime NULL,
      mod_date datetime NULL,
	  mod_by  varchar(255) NOT NULL,
      PRIMARY KEY (id),
      KEY id (id)
    );";
        mysql_query($sql);
    }*/

    /*if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb->rst_paypal_ipn_log'") != $wpdb->rst_paypal_ipn_log) {
        $sql = "CREATE TABLE " . $wpdb->rst_paypal_ipn_log . " (
      id int(10) NOT NULL AUTO_INCREMENT,
      booking_time timestamp NOT NULL,
      booking_id int(10) NOT NULL,
      messages text  NULL,
      PRIMARY KEY (id),
      KEY id (id)
    );";
        mysql_query($sql);
    }*/
}