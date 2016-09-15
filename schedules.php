<?php
session_start();
?>
<html>
<body bgcolor="#FFFFFF">
  <p align="center" style="font-size:24px; color:#990000; border:groove;">Create Schedule</p>
  <table width="600" border="0" cellpadding="3" cellspacing="1" bgcolor="#CCCCCC" align="center">
    <div>
      <form method='post'>
        <table align='center'>
          <tr>
            <td><p>Schedule Name:</p></td>
            <td><input type='TEXT' name='name_sch' value='' size='60' /><br>
              <font size = 2> Name of the given recording.</font></td>
            </tr>
            <tr>
              <td>Schedule Description:</td>
              <td><textarea rows='4' cols='50' name='description' value='' size=60 /></textarea><br>
                <font size = 2> Recording description. Whats the class? What times? Who is speaking?</font></td>
              </tr>
              <tr>
               <td>Recorder Choice</td>
               <td>
                <?php
              /*
              Dynamically grabs all recorders avaliable on csus Mediasite.
              */
              include('functions.php');
              $response = get('Recorders');
              $decoded = json_decode($response, true);
              $parsed;
              echo "<select name =\"recorderlist\">";
              for($i = 0; $i < count($decoded['value']); $i++) {  //Will loop and create a drop down list of all recorders
               $parsed[$i] = jsonParse(['Name', 'Id', 'Username', 'Password', 'WebServiceUrl'], $decoded['value'][$i]);
               echo "<option value = '". $parsed[$i]['Id'] ."' name = 'recorder'>" . $parsed[$i]['Name'] . "</option>";
             }
             echo "</select>";
             ?> <br>
             <font size = 2> Room the recording is taking place. NOTE: Sometimes recorders will not display names if they have been recently disconnected from the network.</font>
           </td>
         </tr>
         <tr>
           <td>Template Choice</td>
           <td>
            <?php
              /*
              Dynamically grabs all templates avaliable on csus Mediasite.
              */
              include('functions.php');
              $response = get('Templates');
              $decoded = json_decode($response, true);
              $parse;
              echo "<select name =\"templatelist\">";
              for($i = 0; $i < count($decoded['value']); $i++) {  //Will loop and create a drop down list of all Templates
               $parse[$i] = jsonParse(['Name', 'Id'], $decoded['value'][$i]);
               echo "<option value = '". $parse[$i]['Id'] ."' name = 'template'>" . $parse[$i]['Name'] . "</option>";
             }
             echo "</select>";
             ?><br>
             <font size = 2> What type of recording is desired?</font>
           </td>
         </tr>
         <tr>
          <td>Recording Duration:</td>
          <td><input type='text' name='rec_sec' value='' size='60' /><br>
            <font size = 2> How long will the recording go on for? Must be in minutes.</font></td>
          </tr>
          <tr>
            <td>Recording Start Date:</td>
            <td><input type="datetime-local" name="rec_start_date"><input type="checkbox" name="onetime" value=true> 
              <font size = 2> One time? </font><br>
              <font size = 2> What day and time does should the recordings start? Is it only one recording?</font>
            </td>
          </tr>
          <tr>
            <td><b>*</b> Recording End Date:</td>
            <td><input type="datetime-local" name="rec_end_date"><br>
              <font size = 2> What day and time does should the recordings end?</font>
            </td>
          </tr>
          <tr>
            <td><b>*</b> Recording Recurrence Pattern:</td>
            <td>
              <select name = "patternlist">
                <option value = "None" name = 'pattern'> None </option>
                <option value = "Daily" name = 'pattern'> Daily </option>
                <option value = "Weekly" name = 'pattern'> Weekly </option>
                <option value = "Monthly" name = 'pattern'> Monthly </option>
                <option value = "Yearly" name = 'pattern'> Yearly </option>
              </select>
              <font size = 2> How often will this need to be recorded?</font>
            </td>
          </tr>
          <tr>
            <td><b>*</b> Recording Day Recurrence:</td>
            <td>
              <input type="checkbox" name="days[0]" value="Sunday"> Sunday 
              <input type="checkbox" name="days[1]" value="Monday"> Monday 
              <input type="checkbox" name="days[2]" value="Tuesday"> Tuesday 
              <input type="checkbox" name="days[3]" value="Wednesday"> Wednesday
              <input type="checkbox" name="days[4]" value="Thursday"> Thursday
              <input type="checkbox" name="days[5]" value="Friday"> Friday
              <input type="checkbox" name="days[6]" value="Saturday">  Saturday <br>
              <font size = 2> What days of the week are needed to be recorded?</font>
            </td>
          </tr>
          <tr>
            <td><b>*</b> Week of the Month:</td>
            <td>
              <select name = "weeklist">
                <option value = "None" name = 'week'> None </option>
                <option value = "First" name = 'week'> First </option>
                <option value = "Second" name = 'week'> Second </option>
                <option value = "Third" name = 'week'> Third </option>
                <option value = "Forth" name = 'week'> Forth </option>
                <option value = "Last" name = 'week'> Last </option>
              </select>
              <font size = 2> Is it a certain week of each month that needs to be recorded? Leave none if not applicable.</font>
            </td>
          </tr>
          <tr>
            <!-- You can use PHP functions to automatically get the value of date -->
            <td><input type='submit' name='sch_submit' value='submit' /></td>
          </tr>
        </td>
        <center> <font size = 2> The <b>*</b> indicates that they are not required if "One time?" is selected.
        </font></center>

        <?php
        /* The Client Robert Van Winkle was kind enough to allow me to
         * upload this project to GitHub under the condition that all
         * HTTP Requests and URLs be removed for security reasons.
         * This page only shows functions I created to help with creating
         * Json files.
         */
          //STILL DOES START TIMES WIERD BECAUSE OF TIMEZONE BLACK MAGIC
          //date_default_timezone_set('America/Los_Angeles');
        include('functions.php');
        include('permissions.php');
          if(isset($_POST['sch_submit'])) { //checks if submit is hit\
            //THE CODE BELOW ONLY EXISTS TO COUNTERACT STRANGE TIMEZONE IRREGULAIRTY ON CURRENT SERVER. MAY BECOME OBSOLETE.
            $record_start = $_POST['rec_start_date']. ":00"; //grabs time inputed
            $record_start = timeAdjustment($record_start, 7);
            $record_end = $_POST['rec_end_date'] . ":00";
            $record_end = timeAdjustment($record_end, 7);
            //START OF NON OBSOLETE CODE.
            if($_POST['onetime']) { //will only create neccesary fields for one lecture recording.
              $sch_name = $_POST['name_sch'];
              $sch_desc = $_POST['desc_sch'];
              $recorder = $_POST['recorderlist'];
              $rec_duration = $_POST['rec_sec'] * 60 * 1000;        
              $fold_id = $_GET['folder_id'];              //Gets the folder ID
              $sch_template = $_POST['templatelist']; //Default template ID
              //Creates the schedule jsonfile
              $sch_jsonFile = createFields(["TimeZoneRegistryKey", "Name", "Description", "FolderId", "ScheduleTemplateId", "RecorderId","TitleType","CreatePresentation"
                ,"LoadPresentation","AutoStart","AutoStop","SendersEmail","NotifyPresenter", "PlayerId"], 
                ["Pacific Standard Time", $sch_name, $sch_desc, $fold_id, $sch_template, $recorder, "ScheduleNameAndAirDateTime", true, true, true, true, "", false, "Random ID"]); 
              $sch_response = postParse("Schedules", ["Id", "RecorderName", "Name"], $sch_jsonFile); //posts the file up
              if($sch_response['Id'] != null) {
                //Creates the Reccurence Jsonfile
                $rec_jsonFile = createFields(["MediasiteId", "RecordDuration","StartRecordDateTime",
                  "NextScheduleTime", "RecurrencePatternType", "RecurrenceFrequency", "WeekDayOnly",  "ExcludeHolidays"]
                  ,[$sch_response['Id'], $rec_duration, $record_start, $record_start,1, 1, false, true]);
                //Posts it up
                $rec_response = postParse("Schedules('" . $sch_response['Id'] . "')/Recurrences", ['Id',"StartRecordDateTime","EndRecordDateTime","RecurrencePattern", "DaysOfTheWeek"], $rec_jsonFile);
                $presenter = postParse("Schedules('" . $sch_response['Id'] . "')/Presenters",
                    ['FirstName', 'LastName', 'Email', 'MediasiteId', 'Id'],$_SESSION["lecturer"]);
                //echo "<br>PRESENTER: " . $presenter . "<br>";
               
                $asso_res = addAssociation($_SESSION['module_id'], $sch_response['Id']);
                if($rec_response['Id'] != null) {
                  $own_res = changePermissions($sch_response['Id'], "dummy@email.com",$_SESSION["lecturer"]["Email"]);
                  echo "Success! <br>";
                   //$event = createEvent($rec_response, $sch_response);
                  auth($sch_response['Id']);
                } else {
                  echo "An error occured while establishing times.<br>";
                }
              } else {
                echo "An error occured while creating the Schedule.<br>";
              }
            } else {
              $sch_name = $_POST['name_sch'];
              $sch_desc = $_POST['desc_sch'];
              $recorder = $_POST['recorderlist'];
              $days = $_POST['days'];             //grabs days            
              $cleaned = cleanDaysArr($days);     //cleans the days array up and should only contain days selected now
              $pattern = $_POST['patternlist'];
              $rec_duration = $_POST['rec_sec'] * 60 * 1000;        //Currently in seconds for record time
              $days_ready = arrayToString($cleaned);      //Takes the days array and adds | for the jsonfile to be ready
              $week_month = $_POST['weeklist'];
              $fold_id = $_GET['folder_id'];              //Gets the folder ID
              $sch_template = $_POST['templatelist'];          //Default template ID
              //Creates the schedule jsonfile
              $sch_jsonFile = createFields(["TimeZoneRegistryKey", "Name", "Description", "FolderId", "ScheduleTemplateId", "RecorderId","TitleType","CreatePresentation"
                ,"LoadPresentation","AutoStart","AutoStop","SendersEmail","NotifyPresenter", "PlayerId"], 
                ["Pacific Standard Time", $sch_name, $sch_desc, $fold_id, $sch_template, $recorder, "ScheduleNameAndAirDateTime", true, true, true, true, "", false,"Random ID"]); 
              $sch_response = postParse("Schedules", ["Id", "RecorderName", "Name"], $sch_jsonFile); //posts the file up
              if($sch_response['Id'] != null) {
              //Creates the Reccurence Jsonfile
                $schId = $sch_response['Id'];
                //echo $record_end;
                $rec_jsonFile = createFields(["MediasiteId", "RecordDuration","StartRecordDateTime","EndRecordDateTime","RecurrencePattern",
                  "NextScheduleTime", "RecurrencePatternType", "RecurrenceFrequency", "WeekDayOnly", "DaysOfTheWeek", "WeekOfTheMonth", 
                  "ExcludeHolidays"],[$sch_response['Id'], $rec_duration, $record_start, $record_end, $pattern, $record_start,
                  1, 1, false, $days_ready, $week_month, true]);
                //Posts it up
                $rec_response = postParse("Schedules('" . $sch_response['Id'] . "')/Recurrences", ['Id',"StartRecordDateTime", "EndRecordDateTime","RecurrencePattern", "DaysOfTheWeek"], $rec_jsonFile);
                $presenter = postParse("Schedules('" . $sch_response['Id'] . "')/Presenters",
                    ['FirstName', 'LastName', 'Email', 'MediasiteId', 'Id'],$_SESSION["lecturer"]);
                //echo "<br>PRESENTER: " . $presenter . "<br>";
                $asso_res = addAssociation($_SESSION['module_id'], $sch_response['Id']);
                if($rec_response['Id'] != null) {
                  $own_res = changePermissions($sch_response['Id'], "dummy@email.com",$_SESSION["lecturer"]["Email"]);
                  echo "Success! <br>";
                  $event = createEvent($rec_response, $sch_response);
                  auth($sch_response['Id']);
                
                } else {
                  echo "An error occured while establishing times.<br>";
                  echo $rec_response;
                }
              } else {
                echo "An error occured while creating the Schedule.<br>";
              }
            }
          }
          /*
          Had to hardcode this loop in order to properly account for days.
          */
          function cleanDaysArr($arr){
            $temp;
            $count = 0;
            for($i = 0; $i<7;$i++){ //loops and grabs days the checked marked boxes have
              if($arr[$i] != null) {
                $temp[$count] = $arr[$i];
                $count++;
              }
            }
            return $temp; 
          }
          function arrayToString($arr){ 
            if(count($arr) == 0) {
              return "None";
            } else if(count($arr) == 1) {
              return $arr[0];
            } else {
              $string = $arr[0];
              for($i = 1; $i < count($arr); $i++) {
                $string = $string . "|" . $arr[$i];
              }
              return $string;
            }
          }
          /* timeAdjustment(date-time-string, integer)
           * This function is used to adjust a date-time string by a given increment
           * Will return the new adjusted date.
           */
          function timeAdjustment($date, $inc) {
            $num = substr($date, 11,2);
            if($num + $inc > 23) {       //if adding 7 hours to 22 result will be 29, executing this line
              $temp = 24-$num;      //will then take the difference between 24 and the current time: 22 (2)
              $num = $inc - $temp;     //sub the result of the above with 7, creaing 5. 
              $date = substr_replace($date, "0".$num, 11, 2);
            } else {  //else just increments and replaces
              $num += $inc;
              $date = substr_replace($date, $num, 11, 2);
            }
            return $date;
          }

          function setPresenter($email) {
            $assoc['Email'] = $lec_email;
            $usr_id;
            $usr_check = doesItExist("UserProfiles", $assoc, 'Email');                    //Checking to see if the UserProfile already exists
                                                                                         //-1 means that it does not exist.
            if($usr_check == -1){                                                         //If the user profile does not exist, create a new one
              $exemail = explode("@", $lec_email);                                      //Splits the string at the @ symbol
              $res = postParse("UserProfiles", ["Id"], createFields(['Email', 'UserName'], [$lec_email,$exemail[0]]));
              $usr_id = $res["Id"];
            } else {                                                                      //else use the returned value from doesItExist.
              $usr_id = $usr_check;                                                       //This value will be the User ID
            }
            $usr = json_decode(get("UserProfile('" . $usr_id . "')"), true);              //checks to see if user is activated
            return $usr['Id'];
          }
            function auth($schId) {
              $myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
              fwrite($myfile, $schId);
              fclose($myfile);
              echo "Copy and paste the following code for the next page! (Its stored into a file on the php server if you miss it: " . $schId;
              //Bellow was gutted as per employers request. Moved to Event.php
              echo "<meta http-equiv=\"refresh\" content=\"15; URL='";
            }
            ?>
</tr>
</table>
</form>
</div>
</html>