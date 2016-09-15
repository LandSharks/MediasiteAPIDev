<html>
Page loaded!
<?php
/* The Client Robert Van Winkle was kind enough to allow me to
 * upload this project to GitHub under the condition that all
 * HTTP Requests and URLs be removed for security reasons.
 * This page only shows functions I created to help with creating
 * Json files.
 */
	include('functions.php');
	echo "PHP LOADED!";
	$schedules = get('Schedules?$top=1000');
	//echo $schedules;
	$schedules = json_decode($schedules, true);
	$schedules = getScheInfo(['Id', 'Name', 'RecorderName'], $schedules);
	/*foreach($arr[0] as $key=>$value){
		echo $value;
	}*/
	//echo "ENCODED: " . json_encode($schedules) . "<br>";
	$rec = getRecInfo(["StartRecordDateTime", "EndRecordDateTime","RecurrencePattern", "DaysOfTheWeek"], $schedules);
	echo "DATE AND TIME" . $rec[0]['DaysOfTheWeek'];
	$response = createEvent($rec, $schedules);

	function getScheInfo($criteria, $schedule){
		$arr;
		echo count($schedule['value']);
		for($i = 0; $i < count($schedule['value']); $i++) {
			for($j = 0; $j < count($criteria); $j++){
				$arr[$i][$criteria[$j]] = $schedule['value'][$i][$criteria[$j]];
				//echo "Loop<br>";
				//echo "Value! " . $arr[$i][$criteria[$j]] . "<br>";
			} 
		}
		return $arr;
	}
	function getRecInfo($criteria, $schedules){
		$arr;
		for($i = 0; $i < count($schedules); $i++){
			//echo $schedules[$i]['Id'];
			$request = get("Schedules('".$schedules[$i]['Id'] . "')/Recurrences");
			//echo "DJ REQUEST". $request . "<br>";
			$request = json_decode($request, true);
			for($j = 0; $j < count($criteria); $j++) {
				//echo "HIT" . $request['value'][$criteria[$j]] . "<br>";
				$arr[$i][$criteria[$j]] = $request['value'][0][$criteria[$j]];
				//echo "GOT IT!" . $arr[$i][$criteria[$j]] . "<br>";
			}
		}
		return $arr;
	}
	function createEvent($rec, $sch){
		for($i = 0; $i < count($sch); $i++) {
			$recordstart = timeAdjustment($rec[$i]['StartRecordDateTime'], 7);
			$recordend = timeAdjustment($rec[$i]['EndRecordDateTime'], 7);
			$endDate = explode("T", $recordstart);
			$endTime = explode("T", $recordend);
			$edate = $endDate[0] . "T". $endTime[1] . "-07:00";
			$daysW = explode("|", $rec[$i]["DaysOfTheWeek"]);
			$days = "";
			for($j = 1; $j < count($daysW)-1; $j++) {
				$days = $days . substr($daysW[$j], 0, 2) . ",";	
			}
			$days = $days . substr($daysW[count($daysW)-1], 0, 2);
			$start = createFields(["dateTime", "timeZone"],[ $recordstart."-07:00", "America/Los_Angeles"]);
			$end = createFields(["dateTime", "timeZone"],[ $edate, "America/Los_Angeles"]);
			$until = str_replace("-","",$rec[$i]["EndRecordDateTime"]);
			$event = createFields(["kind","summary", "description", "colorId", "recurrence", "start", "end"],["calendar#event", $sch[$i]['Name'], $sch[$i]['RecorderName'],
			 "11", ["RRULE:FREQ=". strtoupper($rec[$i]['RecurrencePattern']).";UNTIL=".str_replace(":","",$until)."Z;BYDAY=". $days], $start, $end]);
			echo "Event: " . $i . "created!";
			postGoogle(json_encode($event));
		}
		echo "DONE! CHECK YOUR CALENDARS!";

	}
	function postGoogle($json) {
		//This function has been gutted at the request of client. Was a basic CURL Request
	}
	function timeAdjustment($date, $inc) {
            $num = substr($date, 11,2);
            if($num - $inc < 0) {       //if adding 7 hours to 22 result will be 29, executing this line
              $temp = 24+$num;      //will then take the difference between 24 and the current time: 22 (2)
              $num = $temp-$inc;     //sub the result of the above with 7, creaing 5. 
              $date = substr_replace($date, $num, 11, 2);
            } else {  //else just increments and replaces
              $num -= $inc;
              if($num < 10) {
              	$date = substr_replace($date, "0".$num, 11, 2);
              } else {
              	$date = substr_replace($date, $num, 11, 2);
              }
            }
            return $date;
          }
?>
</html>