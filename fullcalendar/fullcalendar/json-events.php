<?php

	$year = date('Y');
	$month = date('m');

	echo json_encode(array(
	
		array(
			'id' => 111,
			'title' => "Event1",
			'start' => "$year-$month-10",
			'url' => "http://yahoo.com/"
		),
		
		array(
			'id' => 222,
			'title' => "Event2",
			'start' => "2013-06-12 08:00",
			'end' => "2013-06-12 10:00",
			'url' => "http://yahoo.com/",
			'allDay' =>'false'
		)
	
	));

?>
