
<?php 
require('../../../wp-config.php');



         if(isset($_SESSION['totnum']))
		 {
			 unset($_SESSION['totnum']);
		 }
		 $url=$_REQUEST['url'];
		$avilable_seats=($_REQUEST['noseats'])-($_REQUEST['numseat']);
		
		
		$showid=$_REQUEST['showid'];
		$sqly=mysql_query("SELECT  * FROM rst_seats  WHERE show_id=$showid and seattype='y'");
		
		$_SESSION['totnum']=$_REQUEST['numseat'];
	
		header('location:'.$url.'');
		?>