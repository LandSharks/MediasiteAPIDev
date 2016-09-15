<?php
/* FUNCTIONS Page
 * Purpose: To allow for easy function use within all PHP Pages.
 *
 * Majority of functions written by Stephen Gimpel
 * with other functions written by Nick Hedge.
 * 
 * This was for the IRT Learning Space Services project for
 * Mediasite API Integration for Sacramento State University.
 *
 * NOTE: All HTTP Requests will be under the Test_Admin account.
 * A lot of "echo" commands were used specifically for debugging
 * and in no way are intentended in the final product of this
 * project.
 *
 * The Client Robert Van Winkle was kind enough to allow me to
 * upload this project to GitHub under the condition that all
 * HTTP Requests and URLs be removed for security reasons.
 * This page only shows functions I created to help with creating
 * Json files.
 */ 

/*==============================================================//
//---------------Search Criteria function-----------------------//
//==============================================================//
Required: An array of criteria and an associative array.

Function will look through the given $data array and check
for any value specified within $criteria and return a
appropriate associative array.

//==============================================================*/
function jsonParse($criteria, $data) {
  $arr;
  for($i = 0; $i < count($criteria); $i++) { //iterates through the criteria
    if(array_key_exists($criteria[$i], $data)) {  //checks if criteria requested exists in data
      $arr[$criteria[$i]] = $data[$criteria[$i]]; //associates
    
    //echo  "found: " . $arr[$criteria[$i]] . "<br>"; //for testing purposes

    } 
  }
  return $arr;  //returns new associative array.
}
/*==============================================================//
//---------------Create Fields Function-------------------------//
//==============================================================//
Required: an array of fields and an array of values. Both must
be equal in length.

Creates a associative array for Json creation. Will return a -1 
if fields and values are not equal in length.

returns an associative array $arr with the following association:

  $arr {
    fields => values
  }

//==============================================================*/
function createFields($fields, $values) {
  $jsonString = "";
  $arr;
  //checks if the arrays have equal cells to avoid out of bounds
  if(count($fields) == count($values)) {
    for($i = 0; $i < count($fields); $i++) { 
      $arr[$fields[$i]] = $values[$i]; //creates an associative array
    }
  } else {
    echo "error, fields != values";
    return -1; //error value
  }
  /*foreach($arr as $key => $value) {
    echo "$key => $value<br>";
  }*/
  return $arr; //returns associative array
}
/*==============================================================//
//---------------Add Association Function-----------------------//
//==============================================================//
Required: Specification on where the HTTP request should be
sent to, a entity ID you want to associate with said location.

Function does a http post request for creating associations
between the given location (this assumes location has an
ID within it) and a given entities ID.

Currently has only been used for modules, not sure if it can 
Add Associations for any other type of entity.

Will return the response from the server.

//==============================================================*/
function addAssociation($location, $id) {
  //This function has been gutted at the request of client. Was a basic CURL Request
}
/*==============================================================//
//---------------Post Function----------------------------------//
//==============================================================//
Required: A encoded JsonFile and the location it is needed to be
POSTed. 

Function will make a simple HTTP POST request with the given
encoded JsonFile.

Will return the servers response.

//==============================================================*/
function post($jsonFile, $location) {
//This function has been gutted at the request of client. Was a basic CURL Request
}
/*==============================================================//
//----------------Put Function----------------------------------//
//==============================================================//
Required: Given encoded Json File and the HTTP Location that it
will be PUT.

Function will make a simple HTTP PUT request at specified locaation
with the encoded json file.

Will return server Response.

//==============================================================*/
function put($jsonFile, $location) {
  //This function has been gutted at the request of client. Was a basic CURL Request
}
/*==============================================================//
//---------------PostParse Function-----------------------------//
//==============================================================//
Required: A location for the HTTP Request, an array of criteria
that you want returned, and a associative array ($jsonFile)

Calls all the neccesary functions to POST an associative array
to the given location and parse the response with the given
criteria.

ex:
$response = post(json_encode($jsonFile),'Presenters');
$parsed = jsonParse(['FirstName', 'LastName', 'Email', 'MediasiteId'
, 'Id', 'Email'], json_decode($response,true));

Note jsonFile is the same non-encoded associative array, location is
"Presenters" and Criteria is the array: ['FirstName'. 'LastName'....

Will return an associative array of Criteria information.

For further information see post function and jsonParse function.
//==============================================================*/
function postParse($location, $criteria, $jsonFile) {
  //echo "<br><b>" . $criteria[0] . "</b><br>";
  $response = post(json_encode($jsonFile), $location);
  //echo "<br><b>" . $response . "</b><br>";
  $parsed = jsonParse($criteria, json_decode($response,true));
  //echo "<br>Parsed Module Mediasite ID: " . $parsed['Id'];
  return $parsed; 
}
/*==============================================================//
//---------------getValue Function------------------------------//
//==============================================================//
Required: an associative array and a value wanted from the array.

The function will check if the value exists in the associative 
array and if a value is found will return what that value 
is associated to. 

Will return the value found or -1.
//==============================================================*/
function getValue($arr, $value) {
  if(array_key_exists($value, $arr)) {
    return $arr[$value];
  } 
  return -1;
}
/*==============================================================//
//---------------get Function-----------------------------------//
//==============================================================//
Required: a location that you want to GET from.

A simple HTTP GET Request at a given $location.

Will return server response.
//==============================================================*/
function get($location) {
  //This function has been gutted at the request of client. Was a basic CURL Request
}
/*==============================================================//
//---------------doesPresExist Function-------------------------//
//==============================================================//
Required: HTTP Location you wish to check, a given associative
array, and a array of criteria that you want to find in
the associative array you passed in and the returned infromation
from the server.

Uses a get command to obtain all information specified at 
$location, it will then search through both the returned
server array and the array passed in to look for any values
that are the same.

Will return the ID of the entity if a value is found otherwise
it will return -1.

NOTE: Will only work if the returned HTTP Request array has an
associtiave array of "values." This was neccesary for very 
specific mediasite requests.

//==============================================================*/
function doesItExist($location, $assoc, $criteria) {
  $response = get($location);
  $decoded = json_decode($response,true);
  for($i = 0; $i<count($decoded['value']);$i++) {
    if($assoc[$criteria] == $decoded['value'][$i][$criteria]) {
      //echo "FOUND!";
      return $decoded['value'][$i]['Id'];
    }
  }
  return -1;
}
/*==============================================================//
//---------------createFolder Function--------------------------//
//==============================================================//
Required: A given name. This can be anything, but while working on
the project it was requested the folder name be the same as the
module name. It also requries a Boolean to create the file as 
shared or not.

Checks if a folder has been already created for scheduled classroom
recording. Creates the folders should they not exist. Requires a 
Module Id in order to properly establish year folders and semester
folders. It will create all neccesary folders in order to ensure
proper folder structure.

Ex. year folder (2142) > date/semester folder (Spring) > 
  name (class).

Will return the Folder ID created.

NOTE: Will return an error if it can find the folder in the get
request even if the file is deleted on mediasite.
(Ex. found the file in the recycle section).
//==============================================================*/
function createFolder($module, $shared){
  $jsonFile = get('Folders?$top=1000000');  //grabs top one million folders
  $decoded = json_decode($jsonFile, true);  //decodes all that information
  $year = substr($module['Name'], 0, 3); //Grabs the current year using the date function in php
  $year = substr_replace($year, '0', 1, 0);
  //checks if the year folder is already created in the scheduled classrooms recordings folder
  $year_id_fold = checkFile($decoded["value"], $year , "Parent Folder ID");
  $year_id;
  //If our checkFile function cannot find the name with the associated parent id, create it.
  if($year_id_fold == -1) {
    //echo "NO YEAR FOLDER FOUND<br>";
    $file = createFields(["Name","Description", "ParentFolderId"], [$year, "Year " . $year . " Folder.
        All recordings of this year will be in this folder.", "Parent Folder Id"]);
    $year_id_fold = postParse('Folders', ['Id', 'Name', 'Description'], $file);
    //echo "<br> YEAR FOLDER GEN:" . $year_id_fold['Id'] . "<br>";
    $year_id = $year_id_fold['Id'];
  } else { //else it grabs its id
    //echo "YEAR FOLDER FOUND!<br>";
    $year_id = $year_id_fold;
  }
  $date_id_fold;
  $date_id;
  $season;
 //finds the date within the name, assumed to be in the format 2163-Name.... where 216 is 2016 and 3 is the semester
  $date = $module['Name'][3]; 
//used to determine the date we want for Semester.
  if($date == 1) { //will check if any file of the given semester type is already created.
    $date_id_fold = checkFile($decoded["value"], "Winter", $year_id);
    $season = "Winter";
  } else if($date == 3) {
    $date_id_fold = checkFile($decoded["value"], "Spring", $year_id);
    $season = "Spring";
  } else if($date == 6) {
    $date_id_fold = checkFile($decoded["value"], "Summer", $year_id);
    $season = "Summer";
  } else if($date == 8) {
    $date_id_fold = checkFile($decoded["value"], "Fall", $year_id);
    $season = "Fall";
  } else {
   $date_id_fold = checkFile($decoded['value'], 'Other', $year_id);
   $season = "Other";
  }
  if($date_id_fold == -1) { //If it doesn't find a folder for semester date within the year folder it will create it.
    //echo "NO DATE FOLDER FOUND!<br>";
    $date_id_fold = postParse("Folders", ['Id'], createFields(["Name","Description", "ParentFolderId"], 
      [$season, "Semester " . $seasons . "Folder.
        All recordings of " . $season . " will be in this folder.", $year_id]));
    $date_id = $date_id_fold['Id'];
  } else { //otherwiese state that the date folder has been found.
    //echo "DATE FOLDER FOUND!<br>";
    $date_id = $date_id_fold;
  }
  //finally creating the module folder
  //echo "<br><br> DATE ID: " . $date_id . "<br><br>";
  $response = postParse("Folders", ["ParentFolderId","Id", "Name"], createFields(["Name","Description", "ParentFolderId", "IsShared"], 
    [$module['Name'], $module['Description'], $date_id, $shared]));
  return $response['Id'];
}
/*==============================================================//
//---------------checkFile Function-----------------------------//
//==============================================================//
Requred: an array, the name of the folder, and the parent
id of the folder.

Looks for a a folder that is already created.

Will return the parent ID if a folder with the same name is
already there. If not it will return -1.

NOTE: Only works if the folder has a parent ID.

//==============================================================*/

function checkFile($arr, $name, $parent_id) {
  for($i = 0; $i < count($arr); $i++) {
    $temp_arr = jsonParse(['Name', 'Id', 'ParentFolderId'], $arr[$i]);
    //echo $i . "<br>";
    if ($temp_arr['Name'] == $name) {
    //echo "FOUND NAME!<br>" . $temp_arr["ParentFolderId"] . "<br>";
      if($temp_arr["ParentFolderId"] == $parent_id) {
      //echo "<br>FOUND NAME AND PARENT!<br>";
        return $temp_arr['Id'];
      }
    }
  }
  return -1;
}

/*==============================================================//
//---------------makeInputSecure--------------------------------//
//==============================================================//
Requred: any given string.

Will strip the data and ensure no code injection can occur.

Will return the manipulated string.

//==============================================================*/
function makeInputSecure($data) {
      $data = trim($data);
      $data = stripslashes($data);
      $data = htmlspecialchars($data);
      return $data;
  }
?>