<?php

function convert_date_format($date_string) {


	list($date, $time) = split(" ", $date_string);
	list($year, $month, $day) = split("-", $date);

	if ($month == "01")
		$month = "Jan";
	if ($month == "02")
		$month = "Feb";
	if ($month == "03")
		$month = "Mar";
	if ($month == "04")
		$month = "Apr";
	if ($month == "05")
		$month = "May";
	if ($month == "06")
		$month = "Jun";
	if ($month == "07")
		$month = "Jul";
	if ($month == "08")
		$month = "Aug";
	if ($month == "09")
		$month = "Sep";
	if ($month == "10")
		$month = "Oct";
	if ($month == "11")
		$month = "Nov";
	if ($month == "12")
		$month = "Dec";

	$date_for_tc = $day . "-" . $month . "-" . $year . " " . $time;

	return $date_for_tc;
}




?>
