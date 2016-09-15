<?
/* Permissions Page
 * Purpose: To allow for easy function use for permissions
 * pertaining to Mediasite API.
 *
 * All functions written by Stephen Gimpel
 * 
 * This was for the IRT Learning Space Services project for
 * Mediasite API Integration for Sacramento State University.
 *
 * NOTE: All HTTP Requests will be under the Test_Admin account.
 * A lot of "echo" commands were used specifically for debugging
 * and in no way are intentended in the final product of this
 * project.
 */ 
/* The Client Robert Van Winkle was kind enough to allow me to
 * upload this project to GitHub under the condition that all
 * HTTP Requests and URLs be removed for security reasons.
 * This page only shows functions I created to help with creating
 * Json files.
 */

include("functions.php");

/*==============================================================//
//------------------------Permissions---------------------------//
//==============================================================//
Required: Entity ID (Whatever you are trying to grant permissions
to), elist (a list of emails you want to have permissions on file),
and permissions list (list of permissions you want availalbe read
write, execute, etc)

function grabs the resource using get and at entity ID in order to
grab previous permissions from entity. It will then parse through
and add these permissions onto the jsonFile we want to send to 
Mediasite.

Function will then loop to examine the email list. It will check
to see if a UserProfile already exists with that same email,
if it does it will store the ID of that UserProfile for later use
otherwise it will create a new UserProfile. It will check if
the UserProfile is activated and will activate it if it is already
not activated. 
It will then set up a permissions array and will convert a 
UserProfileID to a role ID.
Finally it will put the permissions up and return the response.

Notes: Will always activate user using a PUT Command. Little
inefficent, searching for solutions.

Known Issues: Does not enjoy being given a list of  multiple emails.
  Seems fine if theres 2, but nothing above.
  May not also accept the same UserProfile
  Responds with a "Start of ArrayNode" or something.

//==============================================================*/
function permissions($id, $elist, $plist) {
  $jsonFile;
  $jsonP = get("ResourcePermissions('" . $id . "')");                             //grabbing previous permissions
  $jsonP = json_decode($jsonP, true);                                             //decoding response
  for($i = 0; $i < count($jsonP["AccessControlList"]); $i++) {                    //Iterating through the previous permissions
    $jsonFile["AccessControlList"][$i] = $jsonP["AccessControlList"][$i];         //Associating them to they Associative Array
  }
  /*
  Above should have resulted in something like this: AccessControlList:[{id:1234,read:....},{.....},...]
                                                                               0               1    etc.
  */
  /*for($i =0; $i<count($jsonFile["AccessControlList"]); $i++) {
    echo "Previous Perms: " . $jsonFile["AccessControlList"][$i]["RoleId"];
  }*/
  //Lines 60-84 verified and working.
  $count = 0;
  //echo "<br><br> EMAIL: " . $elist[$count] . "<br><br>";
  for($i = count($jsonP["AccessControlList"]); $i < count($elist) +               //start loop to itterate through list of emails
      count($jsonP["AccessControlList"]); $i++) {                                 //Taking into account previous number of perms
    //echo "<br><br> EMAIL: " . $elist[$count] . "<br><br>";
    $assoc['Email'] = $elist[$count];                                             //create an association for later processing
    $usr_id;
   // echo "<br> Assoc: " . $assoc['Email'] . "<br>";
    $usr_check = doesItExist("UserProfiles", $assoc, 'Email');         //Checking to see if the UserProfile already exists
   // echo "<br> usr_check: " . $usr_check . "<br>";                                //-1 means that it does not exist.
    if($usr_check == -1){                                                         //If the user profile does not exist, create a new one
    //  echo "<br> INSIDE IF<br>";
      $exemail = explode("@", $elist[$count]);                                    //Splits the string at the @ symbol
      $res = postParse("UserProfiles", ["Id"], createFields(['Email', 'UserName'], [$elist[$count],$exemail[0]]));
      $usr_id = $res["Id"];
    //  echo "<br> User_ID: " . $usr_id . "<br>";
    } else {                                                                      //else use the returned value from doesItExist.
    //  echo "found user";
      $usr_id = $usr_check;                                                       //This value will be the User ID
    }
    $fields = createFields(["Activated"],[true]);                               //will activate otherwise
    $res = put(json_encode($fields), "UserProfiles('" . $usr_id . "')");
    $arr = null;
    $arr[0] = roleGen($usr_id);                                                   //helps create role generation ID.
    for($j = 0; $j < 5; $j++) {                                                   //Doesn't check for out of bounds, could be a problem later
      $arr[$j+1] = $plist[$j];
    }
    $arr = createFields(['RoleId', 'Read', 'Write', 
          'Execute', 'Moderate', 'Approve'], $arr);                               //creates an associative array for Permissions
    $jsonFile["AccessControlList"][$i] = $arr;                                    //sets it only to cell of i in Access Control List
    $count++;                                                                     //Moves to the next email in $elist
  }
                             
  $response = put(json_encode($jsonFile), "ResourcePermissions('" . $id . "')");  //finally sends a put request of completed json data.
  return $response;
}
/*==============================================================//
//------------------------permOwner-----------------------------//
//==============================================================//
Required: Entity ID (Whatever you are trying to grant permissions
to), a single email, and permissions list (list of permissions 
you want availalbe read, write, execute, etc)

function grabs the resource using get and at entity ID in order to
grab previous permissions from entity. It will then parse through
and add these permissions onto the jsonFile we want to send to 
Mediasite.

Function will then check to see if a UserProfile already exists with 
the same email as the one passed in ($owner_email). If it does it will 
store the ID of that UserProfile for later use otherwise it will 
create a new UserProfile. It will check if the UserProfile is activated 
and will activate it if it is already not activated. 

It will then set up a permissions array and will convert a 
UserProfileID to a role ID.

Finally it will put the permissions up and return the response.

Notes: Will always activate user using a PUT Command. Little
inefficent, searching for solutions.

//==============================================================*/
function permOwner($id, $owner_email, $plist) {
  $jsonFile;
  $owner = explode("@", $owner_email);
  $jsonFile["Owner"] = $owner[0];                                                 //Sets the owner to the UserProfile
  $jsonP = get("ResourcePermissions('" . $id . "')");                             //grabbing previous permissions
  $jsonP = json_decode($jsonP, true);                                             //decoding response
  for($i = 0; $i < count($jsonP["AccessControlList"]); $i++) {                    //Iterating through the previous permissions
    $jsonFile["AccessControlList"][$i] = $jsonP["AccessControlList"][$i];         //Associating them to they Associative Array
  }
  /*
  Above should have resulted in something like this: AccessControlList:[{id:1234,read:....},{.....},...]
                                                                               0               1    etc.
  */
  $assoc['Email'] = $owner_email;
  $usr_id;

  $usr_check = doesItExist("UserProfiles", $assoc, 'Email');                     //Checking to see if the UserProfile already exists
                                                                                 //-1 means that it does not exist.
  if($usr_check == -1){                                                         //If the user profile does not exist, create a new one;
    $exemail = explode("@", $owner_email);                                      //Splits the string at the @ symbol
    $res = postParse("UserProfiles", ["Id"], createFields(['Email', 'UserName'], [$owner_email,$exemail[0]]));
    $usr_id = $res["Id"];
  } else {                                                                      //else use the returned value from doesItExist.
    $usr_id = $usr_check;                                                       //This value will be the User ID
  }
  $usr = json_decode(get("UserProfile('" . $usr_id . "')"), true);              
  $fields = createFields(["Activated"],[true]);                              
  $res = put(json_encode($fields), "UserProfiles('" . $usr_id . "')");

  
  $count = count($jsonFile["AccessControlList"]);                               //Counts current number of permissions on file
  $arr = null;
  $arr[0] = roleGen($usr_id);                                                   //helps create role generation ID.
  for($j = 0; $j < 5; $j++) {                                                   //Doesn't check for out of bounds, could be a problem later
    $arr[$j+1] = $plist[$j];
  }
  $arr = createFields(['RoleId', 'Read', 'Write', 
          'Execute', 'Moderate', 'Approve'], $arr);                             //Creates an associative array for Permissions
  $jsonFile["AccessControlList"][$count] = $arr;                                //Sets it to last cell in Access Control List
  $response = put(json_encode($jsonFile), "ResourcePermissions('" . $id . "')");//finally sends a put request of completed json data.
  return $response;
}
/*==============================================================//
//------------changeFolderPermissions---------------------------//
//==============================================================//
Required: Entity ID (Whatever you are trying to grant permissions
to) and a single email.

function grabs the resource using get and at entity ID in order to
grab previous permissions from entity. It will then parse through
and add these permissions onto the jsonFile we want to send to 
Mediasite.

Function will then check to see if a UserProfile already exists with 
the same email as the one passed in ($owner_email). If it does it will 
store the ID of that UserProfile for later use otherwise it will 
create a new UserProfile. It will check if the UserProfile is activated 
and will activate it if it is already not activated. 


Finally it will put the permissions up and return the response.
Thus changing ownership of the entity.

Notes: Will always activate user using a PUT Command. Little
inefficent, searching for solutions.

//==============================================================*/
function changePermissions($id, $owner_email, $lec_email) {
  $jsonFile;
  $owner = explode("@", $owner_email);
  $jsonFile["Owner"] = $owner[0];                                                 //Sets the owner to the UserProfile
  $jsonP = get("ResourcePermissions('" . $id . "')");                             //grabbing previous permissions
  $jsonP = json_decode($jsonP, true);                                             //decoding response
  for($i = 0; $i < count($jsonP["AccessControlList"]); $i++) {                    //Iterating through the previous permissions
    $jsonFile["AccessControlList"][$i] = $jsonP["AccessControlList"][$i];         //Associating them to they Associative Array
  }
  /*
  Above should have resulted in something like this: AccessControlList:[{id:1234,read:....},{.....},...]
                                                                               0               1    etc.
  */
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
  $fields = createFields(["Activated"],[true]);                               //will activate otherwise
  $res = put(json_encode($fields), "UserProfiles('" . $usr_id . "')");
  $count = count($jsonFile["AccessControlList"]);                               //Counts current number of permissions on file
  $arr = null;
  $arr[0] = roleGen($usr_id);                                                   //helps create role generation ID.
  for($j = 0; $j < 5; $j++) {                                                   //Doesn't check for out of bounds, could be a problem later
    $arr[$j+1] = true;
  }
  $arr = createFields(['RoleId', 'Read', 'Write', 
          'Execute', 'Moderate', 'Approve'], $arr);                             //Creates an associative array for Permissions
  $jsonFile["AccessControlList"][$count] = $arr;
  $response = put(json_encode($jsonFile), "ResourcePermissions('" . $id . "')");//finally sends a put request of completed json data.
  return $response;
}
/*==============================================================//
//-------------------folderPermissions--------------------------//
//==============================================================//
Required: the Id of the folder that needs to be changed and 
the name email of the owner.

Will change permissions on the given folderId. Grabs previous
permissions and attempts to create a storable array for them.
Alsow ill change the Owner (As provided via $owner_email) and set
the Propgates to true.

Returns response from server.

Known Issues: Mediasite is currently not changing PropagateOwner
or PropagatePermissions via POST Requests. Everything else will
properly work through this function though.

//==============================================================*/
function folderPermissions($id, $owner_email, $lec_email) {
  $jsonFile;
  $exemail = explode("@", $owner_email);
  $jsonFile["Owner"] = $exemail[0];
  $jsonFile["PropagateOwner"] = true;
  $jsonFile["PropagatePermissions"] = true;
  $jsonP = get("ResourcePermissions('" . $id . "')");                             //grabbing previous permissions
  $jsonP = json_decode($jsonP, true);                                             //decoding response
  for($i = 0; $i < count($jsonP["AccessControlList"]); $i++) {                    //Iterating through the previous permissions
    $jsonFile["Permissions"][$i] = $jsonP["AccessControlList"][$i];               //Associating them to the Associative Array
  }
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
  $fields = createFields(["Activated"],[true]);                               //will activate otherwise
  $res = put(json_encode($fields), "UserProfiles('" . $usr_id . "')");
  $count = count($jsonFile["AccessControlList"]);                               //Counts current number of permissions on file
  $arr = null;
  $arr[0] = roleGen($usr_id);                                                   //helps create role generation ID.
  for($j = 0; $j < 5; $j++) {                                                   //Doesn't check for out of bounds, could be a problem later
    $arr[$j+1] = true;
  }
  $arr = createFields(['RoleId', 'Read', 'Write', 
          'Execute', 'Moderate', 'Approve'], $arr);                             //Creates an associative array for Permissions
  $jsonFile["Permissions"][$count] = $arr;
  $response = post(json_encode($jsonFile), "Folders('" . $id . "')/UpdatePermissions");
  //echo json_encode($jsonFile);
  //echo $response;
  return $response;
}
/*==============================================================//
//-------------------folderOwnerChange--------------------------//
//==============================================================//
Required: the Id of the folder that needs to be changed and 
the name email of the owner.

Changes the owner of the provided folderId. 

Notes: Use only when switching owner. Mediasite reccomended it
on their API Page.

//==============================================================*/
function folderOwnerChange($id, $owner_email) {
  $exemail = explode("@", $owner_email);
  $jsonFile["Owner"] = $exemail[0];
  $response = post(json_encode($jsonFile), "Folders('" . $id . "')/UpdateOwner");
  //echo $response;
  return $response;
}
/*==============================================================//
//----------------------Role Generation-------------------------//
//==============================================================//
Pass in a UserProfile ID so it can be converted into a Role ID.

Mediasite has a static manipulation of ID's to create a Role ID.
It will move 8 characters and put a -, then 4 3 times and shave
off the last 2 characters of the string.

Ex.
UserProfileID: 0072dd1e929244789af4f54d9d523a8738
Will turn into: 0072dd1e-9292-4478-9af4-f54d9d523a87

//==============================================================*/
function roleGen($id) {                                                           //Splits the UserProfileId to produce a RoleId
  return substr($id, 0, 8) . '-' . substr($id, 8, 4) . '-' . substr($id, 12, 4) . '-' . substr($id, 16, 4) . '-' 
  . substr($id, 20, 12);
}
?>