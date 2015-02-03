<?php
function addRunningTestJobDb($deviceSerial, $filename, $arrJob, $user, $remoteConn) {
	$start_time = new DateTime($arrJob['start']);
	
	// set schedule name
	$schedule_name = preg_replace('/ /', '', "$user $deviceSerial " . $start_time->format('U'));
		
	$sql = "INSERT INTO testjob_queue(test_job, device_serial, sent_by, execution_time, schedule_name) 
			VALUES('$filename', '$deviceSerial', '$user', '" . $start_time->format('Y-m-d H:i') . "', '$schedule_name" . $start_time->format('U') . "')";
	
	execute_sql($sql);
	
	return 1; // successful
}

function add_testjob_db($device_serial, $filename, $arr_job, $user, $remote_conn) {
	$arr_start_time = array();
	$start_time = new DateTime($arr_job["start"]);
	
	// get the current system of remote test machine
	$stream = ssh2_exec($remote_conn, 'date');
	stream_set_blocking($stream, true);
	$now = new DateTime(fgets($stream));

	if(comp_datetime($start_time, $now) == 1) {
		// set schedule name
		$schedule_name = preg_replace('/ /', '', "$user $device_serial " . $now->format('U'));
		
		switch($arr_job["repeats"]) {
			case 'Daily':
				$unit_s = 'D';
				$unit_f = "DAY";
				array_push($arr_start_time, $start_time);
				break;
			case 'Monthly':
				$unit_s = 'M';
				$unit_f = "MONTH";
				array_push($arr_start_time, $start_time);
				break;
			case 'Weekly':
				$unit_s = 'W';
				$unit_f = "WEEK";
				
				// create an array of start dates for the selected weekdays
				$weekdays = explode(',', $arr_job["weeklyOn"]);
				
				foreach($weekdays as $day) {
					$conv_start_time = clone $start_time;
					
					if(trim($day) == "Monday") {
						$day_diff = ((1 - weekday($start_time)) + 7) % 7;
						$conv_start_time->add(new DateInterval('P' . $day_diff . 'D'));
					} elseif(trim($day) == "Tuesday") {
						$day_diff = ((2 - weekday($start_time)) + 7) % 7;
						$conv_start_time->add(new DateInterval('P' . $day_diff . 'D'));
					} elseif(trim($day) == "Wednesday") {
						$day_diff = ((3 - weekday($start_time)) + 7) % 7;
						$conv_start_time->add(new DateInterval('P' . $day_diff . 'D'));
					} elseif(trim($day) == "Thursday") {
						$day_diff = ((4 - weekday($start_time)) + 7) % 7;
						$conv_start_time->add(new DateInterval('P' . $day_diff . 'D'));
					} elseif(trim($day) == "Friday") {
						$day_diff = ((5 - weekday($start_time)) + 7) % 7;
						$conv_start_time->add(new DateInterval('P' . $day_diff . 'D'));
					} elseif(trim($day) == "Saturday") {
						$day_diff = ((6 - weekday($start_time)) + 7) % 7;
						$conv_start_time->add(new DateInterval('P' . $day_diff . 'D'));
					} elseif(trim($day) == "Sunday") {
						$day_diff = ((0 - weekday($start_time)) + 7) % 7;
						$conv_start_time->add(new DateInterval('P' . $day_diff . 'D'));
					}
	
					array_push($arr_start_time, $conv_start_time);
				}
				break;
		}
		
		foreach($arr_start_time as $date) {
			if($arr_job["end"] == "After") {
				for($i = 0; $i < intval($arr_job["after"]); $i++) {
					$sql = "INSERT INTO testjob_queue(test_job, device_serial, sent_by, execution_time, schedule_name) 
			   			    VALUES('$filename', '$device_serial', '$user', '" . $date->format('Y-m-d H:i') . "', '$schedule_name')";
					execute_sql($sql);
					$date->add(new DateInterval('P' . $arr_job["every"] . $unit_s));
				}
			} elseif($arr_job["end"] == "On") {
				$end_time = new DateTime($arr_job["on"]);
				while(comp_datetime($start_time, $end_time) == -1) {
					$sql = "INSERT INTO testjob_queue(test_job, device_serial, sent_by, execution_time, schedule_name) 
			   			    VALUES('$filename', '$device_serial', '$user', '" . $date->format('Y-m-d H:i') . "', '$schedule_name')";
					execute_sql($sql);
					$date->add(new DateInterval('P' . $arr_job["every"] . $unit_s));
				}
			} elseif($arr_job["end"] == "Never") {
				// if end is never, create event so that this event inserts 3 test jobs ahead of time
				// calculate the gap between the current time and first run time, this will be used as a buffer 
				// so that new run time will be set accordingly according to the current time
				$diff_sec = get_seconds($date->diff($now));
				$sql = "CREATE EVENT $schedule_name
						ON SCHEDULE EVERY " . 3 * $arr_job["every"] . " $unit_f STARTS CURRENT_TIMESTAMP
						DO
							call enqueue('$filename','$device_serial','$user','" . $date->format('Y-m-d H:i') . "',
										 '$schedule_name','" . $arr_job["every"] . "', '$unit_f', '3', '$diff_sec');";
				execute_sql($sql);
			}	
		}
		
		return 1; // successful
	} else {
		echo "Please set the start time behind the current time of test machine:\n" . 
			 "Test Machine: [" . date_format($now, 'Y-m-d H:i:s') . "]";
	}
}

function execute_sql($sql) {
	// Connecting the database
	$link = mysql_connect('localhost', 'root', 'root123') or die('Could not connect: ' . mysql_error());

	// Select database
	mysql_select_db('testdepot') or die('Could not select database: ' . mysql_error());
	
	// Execute SQL
	mysql_query($sql) or die('Query failed: ' . mysql_error());
	
	// Closing connection
	mysql_close($link);
}


function schedule_testjob($device_serial, $filename, $arr_job, $user) {

	// Connecting the database
	$link = mysql_connect('localhost', 'root', 'root123') or die('Could not connect: ' . mysql_error());

	// Select database
	mysql_select_db('testdepot') or die('Could not select database: ' . mysql_error());

	$arr = array();

	$date_start = new DateTime($arr_job["start"]);
	$every = $arr_job["every"];
	$weekdays = explode(',', $arr_job["weeklyOn"]);

	//echo $start . "\n" . $now->format('Y-m-d H:i') . "\n" . $int_sign . $interval;exit(0);
	//array_push($arr, array('start' => "CURRENT_TIMESTAMP + INTERVAL $interval SECOND"));

	switch($arr_job["repeats"]) {
		case 'Min':
			$repeat = "MINUTE";
			$period = 'M';
			array_push($arr, array("start" => $date_start));
			break;
		case 'Hour':
			$repeat = "HOUR";
			$period = 'H';
			array_push($arr, array("start" => $date_start));
			break;
		case 'Daily':
			$repeat = "DAY";
			$period = 'D';
			array_push($arr, array("start" => $date_start));
			break;
		case 'Monthly':
			$repeat = "MONTH";
			$period = 'M';
			array_push($arr, array("start" => $date_start));
			break;
		case 'Weekly':
			$repeat = "WEEK";
			$period = 'W';
				
			// calculate the start date according to selected week day
			// start date will be current timestamp + interval waiting days between the
			// current week day and selected week day.
			// also, must specify the $date_start so that end date can be set correctly.
			// $start = "CURRENT_DATE + INTERVAL 6 - WEEKDAY(CURRENT_TIMESTAMP) DAY";
			foreach($weekdays as $index => $value) {
				// add difference days to the current start date
				$new_date_start = clone $date_start;

				if(trim($value) == "Monday") {
					$day_diff = ((1 - weekday($date_start)) + 7) % 7;
					$new_date_start->add(new DateInterval('P' . $day_diff . 'D'));
				} elseif(trim($value) == "Tuesday") {
					$day_diff = ((2 - weekday($date_start)) + 7) % 7;
					$new_date_start->add(new DateInterval('P' . $day_diff . 'D'));
				} elseif(trim($value) == "Wednesday") {
					$day_diff = ((3 - weekday($date_start)) + 7) % 7;
					$new_date_start->add(new DateInterval('P' . $day_diff . 'D'));
				} elseif(trim($value) == "Thursday") {
					$day_diff = ((4 - weekday($date_start)) + 7) % 7;
					$new_date_start->add(new DateInterval('P' . $day_diff . 'D'));
				} elseif(trim($value) == "Friday") {
					$day_diff = ((5 - weekday($date_start)) + 7) % 7;
					$new_date_start->add(new DateInterval('P' . $day_diff . 'D'));
				} elseif(trim($value) == "Saturday") {
					$day_diff = ((6 - weekday($date_start)) + 7) % 7;
					$new_date_start->add(new DateInterval('P' . $day_diff . 'D'));
				} elseif(trim($value) == "Sunday") {
					$day_diff = ((0 - weekday($date_start)) + 7) % 7;
					$new_date_start->add(new DateInterval('P' . $day_diff . 'D'));
				}

				array_push($arr, array("start" => $new_date_start));
			}

			break;
	}

	// for weekly repeat, we need to create event for every week day
	// in this case, we will have multiple start date
	// For daily or monthly, there will be one start date and just one
	// event is necessary
	foreach($arr as $key => $value) {
		$now = new DateTime("now");
		$start_date = $value["start"];
		
		if(comp_datetime($start_date, $now) == 1) {
			// if start time is ahead of current time
			// use start time as it is
			$schedule = "STARTS '" . $start_date->format('Y-m-d H:i:s') . "'";	
		} else {
			// if start time is behind the current time
			// replace start time with current time
			$schedule = "STARTS CURRENT_TIMESTAMP";
			$start_date = $now;
		}
		
		switch($arr_job["end"]) {
			case 'After':
				$after = intval($arr_job["after"]);

				// get the end date based on frequency (every) and number of times (after)
				$end_date = clone $start_date;
				for($i = 0; $i < $after - 1; $i++) {
					if($repeat == 'HOUR' || $repeat == 'MINUTE') {
						$end_date->add(new DateInterval('PT' . $every . $period));
					} else {
						$end_date->add(new DateInterval('P' . $every . $period));
					}
				}
				
				$schedule = $schedule . " ENDS '" . $end_date->format('Y-m-d H:i:s') . "'";
				break;
			case 'On':
				$end_date = new DateTime($arr_job["on"]);
				$schedule = $schedule . " ENDS '" . $end_date->format('Y-m-d H:i:s') . "'";
				break;
		}
		
		// set event name
		$timestamp = $now->format('U');
		$event_name = "$user $device_serial $timestamp";
		$event_name = preg_replace('/ /', '', $event_name);

		$sql = "CREATE EVENT $event_name\n";
		$sql = $sql . "ON SCHEDULE EVERY $every $repeat $schedule\n";
		$sql = $sql . "DO\n";
		$sql = $sql . "INSERT INTO testjob_queue(test_job, device_serial, sent_by, event_name, execution_time) 
			   VALUES('$filename', '$device_serial', '$user', '$event_name', '" . $start_date->format('Y-m-d H:i:s') . "');";

		// Execute sql
		echo "Now: " . $now->format('Y-m-d H:i:s') . ", Starts time: " . $value["start"]->format('Y-m-d H:i:s') . "\n$sql\n";
		mysql_query($sql) or die('Query failed: ' . mysql_error());
	}

	// Closing connection
	mysql_close($link);

	return 1; // successful
}

/**
 * Compare two datetime object and return
 * 1 if $date1 > $date2
 * 0 if $date1 = $date2
 * -1 if $date1 < $date2
 * @param DateTime $date1
 * @param DateTime $date2
 */
function comp_datetime($date1, $date2) {
	// conver to unix epoch
	$val_date1 = $date1->format('U');
	$val_date2 = $date2->format('U');
	
	if(($val_date1 - $val_date2) > 0) {
		return 1;
	} elseif(($val_date1 - $val_date2) == 0) {
		return 0;
	} elseif(($val_date1 - $val_date2) < 0) {
		return -1;
	}
}

/**
 * calculate seconds
 * @param DateTime $datetime
 */
function get_seconds($datetime) {
	$int_days = $datetime->format('%a');
	$int_hours = $datetime->format('%h');
	$int_mins = $datetime->format('%i');
	$int_sec = $datetime->format('%s');
	
	return $int_days * 86400 + $int_hours * 3600 + $int_mins * 60 + $int_sec;
}

/**
 * weekday
 * @param DateTime $datetime
 */
function weekday($datetime) {
	return date('w', strtotime($datetime->format('Y-m-d H:i')));
}

function get_testjobs($device_serial) {
	$jobs = array();

	// Connecting the database
	$link = mysql_connect('localhost', 'root', 'root123') or die('Could not connect: ' . mysql_error());

	// Select database
	mysql_select_db('testdepot') or die('Could not select database: ' . mysql_error());

	$sql = "SELECT job_id, test_job, execution_time, created_on, sent_by FROM testjob_queue WHERE device_serial = '$device_serial'
		    ORDER BY execution_time";
	$result = mysql_query($sql) or die('Query failed: ' . mysql_error());
	
	while($rows = mysql_fetch_array($result, MYSQLI_ASSOC)) {
		$job = array();
	
		foreach($rows as $field => $col_value) {
			$job[$field] = $col_value;
		}

		// append status column
		$job['status'] = 'pending';
	
		array_push($jobs, $job);
	}

	// Free resultset
	mysql_free_result($result);

	// Closing connection
	mysql_close($link);

	return $jobs;
}

function delete_testjob($id) {
	// Connecting the database
	$link = mysql_connect('localhost', 'root', 'root123') or die('Could not connect: ' . mysql_error());
	
	// Select database
	mysql_select_db('testdepot') or die('Could not select database: ' . mysql_error());
	
	// get associated schedule name of the test job
	$sql = "select schedule_name from testjob_queue where job_id = '$id'";
	$result = mysql_query($sql) or die('Query failed: ' . mysql_error());
	$row = mysql_fetch_array($result, MYSQLI_ASSOC);
	$schedule_name = $row['schedule_name'];
	
	// get how many jobs are involved with the schedule name
	$sql = "select count(job_id) as total from testjob_queue where schedule_name = '$schedule_name'";
	$result = mysql_query($sql) or die('Query failed: ' . mysql_error());
	$row = mysql_fetch_array($result, MYSQLI_ASSOC);
	
	if ($row['total'] == 1) {
		// if count is 1, delete the job only.
		$sql = "delete from testjob_queue where job_id = '$id';";
		$result = mysql_query($sql) or die('Query failed: ' . mysql_error());	
	} else if ($row['total'] > 1) {
		// if there are more than 1 job related to schedule name, delete all job and drop the event
		$sql = "delete from testjob_queue where schedule_name = '$schedule_name'";
		$result = mysql_query($sql) or die('Query failed: ' . mysql_error());
		
		$sql = "DROP EVENT IF EXISTS $schedule_name";
		$result = mysql_query($sql) or die('Query failed: ' . mysql_error());
	}
	
	// Free resultset
	mysql_free_result($result);

	// Closing connection
	mysql_close($link);
	
	return 1;
}

function add_script_path($name, $path, $url) {
	// Connecting the database
	$link = mysql_connect('localhost', 'root', 'root123') or die('Could not connect: ' . mysql_error());

	// Select database
	mysql_select_db('testdepot') or die('Could not select database: ' . mysql_error());

	$sql = "SELECT * FROM testcase_script_map WHERE testcase_name = '$name'";
	$result = mysql_query($sql) or die('Query failed: ' . mysql_error());
	$rows = mysql_fetch_array($result, MYSQLI_ASSOC);

	// Free resultset
	mysql_free_result($result);

	// Performing insert or update script path
	if ($rows) {
		// update existing row
		$sql = "UPDATE testcase_script_map SET script_path='$path', git_url='$url' WHERE testcase_name='$name'";
		mysql_query($sql) or die('Query failed: ' . mysql_error());
	} else {
		if (strlen(trim($path)) > 0) {
			// insert new row if path is not empty.
			$sql = "INSERT INTO testcase_script_map(testcase_name, script_path, git_url) VALUES('$name', '$path', '$url')";
			mysql_query($sql) or die('Query failed: ' . mysql_error());
		}
	}

	// Closing connection
	mysql_close($link);

	return 1;
}

function get_script_path($name) {
	$scripts = array();

	// Connecting the database
	$link = mysql_connect('localhost', 'root', 'root123') or die('Could not connect: ' . mysql_error());

	// Select database
	mysql_select_db('testdepot') or die('Could not select database: ' . mysql_error());

	$sql = "SELECT script_path, git_url FROM testcase_script_map WHERE testcase_name = '$name'";
	$result = mysql_query($sql) or die('Query failed: ' . mysql_error());

	while($rows = mysql_fetch_array($result, MYSQLI_ASSOC)) {
		/* Jung Soo Kim
		foreach($rows as $field => $value) {
			if (strlen(trim($value)) > 0) {
				array_push($scripts, $value);
			}
		}*/
		array_push($scripts, $rows);
	}

	// Free resultset
	mysql_free_result($result);

	// Closing connection
	mysql_close($link);

	return $scripts;
}

function str_starts_with($haystack, $needle) {
	// case insensitive - $needle
	return substr_compare($haystack, $needle, 0, strlen($needle), true) === 0;
}
?>