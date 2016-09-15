<html>
<body bgcolor="#FFFFFF">

<p align="center" style="font-size:24px; color:#990000; border:groove;">Calendar Events</p>
<table width="600" border="0" cellpadding="3" cellspacing="1" bgcolor="#CCCCCC" align="center">
<div>
            <form method='post'>
                      <table align='center'>
                        <tr>
                          <td><p>Schedule Id:</p>
                          <td><input type='TEXT' name='sch' value='' size='60' /></td>
                        </tr>
                        <br />
                          <!-- You can use PHP functions to automatically get the value of date -->
                          <td><input type='submit' name='media_submit' value='submit' />
                          </font></td>
                        </tr>
                                              </table>
            </form>
<?php
/* The Client Robert Van Winkle was kind enough to allow me to
 * upload this project to GitHub under the condition that all
 * HTTP Requests and URLs be removed for security reasons.
 * This page only shows functions I created to help with creating
 * Json files.
 */
			include("functions.php");
			if(isset($_POST['media_submit'])) {
				$sch = json_decode(get("Schedules('".$_POST['sch']."')"), true);
				$rec = get("Schedules('".$_POST['sch']."')/Recurrences");
				//echo $rec;
				$rec = json_decode($rec, true);
				$json = createEvent($rec['value'][0], $sch);
				$code = authToken(json_encode($json));
			}
			function createEvent($rec, $sch){
              $recordstart = timeAdjust($rec['StartRecordDateTime'], 7);
              $recordend = timeAdjust($rec['EndRecordDateTime'], 7);
             // echo $recrodstart . "<br>";
              //echo $recordend . "<br>";
              $endDate = explode("T", $recordstart);
              $endTime = explode("T", $recordend);
              $edate = $endDate[0] . "T". $endTime[1] . "-07:00";
             // echo $edate . "<br>";
             // echo $endDate . "<br>";
              $daysW = explode("|", $rec["DaysOfTheWeek"]);
              $days = "";
              for($j = 1; $j < count($daysW)-1; $j++) {
                $days = $days . substr($daysW[$j], 0, 2) . ","; 
              }
              $days = $days . substr($daysW[count($daysW)-1], 0, 2);
              $start = createFields(["dateTime", "timeZone"],[ $recordstart."-07:00", "America/Los_Angeles"]);
              $end = createFields(["dateTime", "timeZone"],[ $edate, "America/Los_Angeles"]);
              $until = str_replace("-","",$rec["EndRecordDateTime"]);
              $event = createFields(["kind","summary", "location", "colorId", "recurrence", "start", "end"],["calendar#event", $sch['Name'], $sch['RecorderName'],
               "11", ["RRULE:FREQ=". strtoupper($rec['RecurrencePattern']).";UNTIL=".str_replace(":","",$until)."Z;BYDAY=". $days], $start, $end]);
              return $event;
           }
          function timeAdjust($date, $inc) {
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
            function authToken($json) {
              //This function has been gutted at the request of client. Was a basic CURL Request
            }
          }
?>
</html>