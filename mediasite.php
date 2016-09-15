<?php
session_start();
?>
<html>
<head><title>Mediasite Creation</title></head>

<body bgcolor="#FFFFFF">

<p align="center" style="font-size:24px; color:#990000; border:groove;">Create All</p>
<table width="600" border="0" cellpadding="3" cellspacing="1" bgcolor="#CCCCCC" align="center">
<div>
<center>All reccomendations for input were provided to the developer, these inputs can vary except for catalogs. Inputs may vary from class to class, discuss with your supervisor what is desired.</center>
            <form method='post'>
                      <table align='center'>
                        <tr>
                          <td><p>Presenter First Name:</p>
                          <td><input type='TEXT' name='firstname' value='' size='60' /></td>
                        </tr>
                        <br />
                        <tr>
                          <td>Presenter Last Name:</td>
                          <td><input type='text' name='lastname' value='' size='60' /></td>
                        <tr>
                          <td>Presenter Email:</td>
                          <td><input type='text' name='email' value='' size=60 /><br>
                          <font size = 2> 
                            The csus email the presenter/user is using. This is important for UserProfile creation and ownership. Ex. Jon@csus.edu
                          </font>
                          </td>
                        </tr>
                       <!-- Previous permission options.
                       <tr>
                          <td>Permissions:</td>
                          <td>
                            <input type="checkbox" name="permissions[0]" value="true"> Read 
                            <input type="checkbox" name="permissions[1]" value="true"> Write 
                            <input type="checkbox" name="permissions[2]" value="true"> Execute 
                            <input type="checkbox" name="permissions[3]" value="true"> Moderate 
                            <input type="checkbox" name="permissions[4]" value="true"> Approve  
                          </td>
                        </tr>-->
                        <tr>
                          <td><p>Module ID:</p>
                          <td><input type='TEXT' name='moduleid' value='' size='60' /><br>
                          <font size = 2> The identification of the module being created. yearsemester-class-section lastname Ex. 2166-CSC131-01 Salem</font>
                          </td>
                        </tr>
                        <tr>
                          <td>Module Name:</td>
                          <td><input type='text' name='name' value='' size='60' /><br>
                           <font size = 2> Name that will appear on mediasite. Same as id. Ex. 2166-CSC131-01 Salem</font>
                           </td>
                        <tr>
                          <td><p>Catalog Name:</p>
                          <td><input type='TEXT' name='cat_name' value='' size='60' />
                            <input type="checkbox" name="shared" value="true"> <font size = '2'>Shared Folder?
                          </font><br>
                          <font size = 2> Name that will appear on mediasite. Primary ID. Same as above. Ex. 2166-CSC131-01 Salem</font>
                          </td>
                        </tr>
                        <tr>
                          <td>Catalog Friendly Name:</td>
                          <td><input type='text' name='cat_friendname' value='' size='60'><br>
                          <font size = 2> URL Name that will appear on mediasite. Same as above. Ex. 2166-CSC131-01 Salem</font>
                          </td>
                        </tr>
                        <tr>
                          <td>Description:</td>
                          <td><textarea rows='4' cols='50' name='description' value='' size=60 /></textarea><br>
                          <font size = 2> Fields to input the professor, class, semester, etc. All info regarding class. This will appear for modules, catalogs, and folders.</font>
                          </td>
                        </tr>
                        <tr>
                          <tr>
                          <!-- You can use PHP functions to automatically get the value of date -->
                          <td><input type='submit' name='media_submit' value='submit' />
                           <input type="checkbox" name="schedules" value="true"> <font size = '2'>Create a Schedule?
                          </font></td>
                        </tr>
                                              </table>
            </div>
            <b><center><font size = '1'>*Folder generation MUST have a catalog name and MUST  be in the format yearsemester-class-section</font></center></b>
    <br>
    <br>
<?php    
/*
All message related code is commeneted out due to the functions missing within the functions.php page.
They were likely overwritten.
*/
/* The Client Robert Van Winkle was kind enough to allow me to
 * upload this project to GitHub under the condition that all
 * HTTP Requests and URLs be removed for security reasons.
 * This page only shows functions I created to help with creating
 * Json files.
 */
include('functions.php');
include('permissions.php');
$folder_id;
$flag;
if(isset($_POST['media_submit'])) {   //checks if submit button is hit or not.
  //$message = "";
  $flag = true;
  $presenter = presenter_create();
  $module = module_create();
  $catalog = catalog_create();
  $shared = $_POST['shared'];
 /* if($presenter['Email'] == $_POST['email']) {  //Checks to see if the found or created presenter matches the email.
    $presenter = postParse('Presenters',['FirstName', 'LastName', 'Email', 'MediasiteId', 'Id'],$presenter);  //posts if they do
    //$message = titledEmailBodyBuilder($message, $pres_res, "Presenter");
  }*/                                                                                 //if not the presenter already exists
  $mod_res = postParse('Modules',['Owner', 'CreationDate', 'ModuleId', 'Id'],$module);
  $mod_change = changePermissions($mod_res['Id'], "dummy@email.com", $presenter['Email']);             //changes owner
  //$message = titledEmailBodyBuilder($message, $mod_res, "Module");
  $cata_res = postParse('Catalogs',['Name', 'FriendlyName', 'Description', 'Owner', 'Id'],$catalog);
  $cata_set = catalog_set($cata_res['Id']);
  $cata_change = changePermissions($cata_res['Id'], "dummy@email.com",$presenter['Email']);           //changes owner
  //$message = titledEmailBodyBuilder($message, $cata_res, "Catalog");
  $asso_res = addAssociation($mod_res['Id'], $cata_res['Id']);
  if($cata_res['Name'] != "") {
    $folder_id = createFolder($cata_res, $shared);
    $folder_perm = folderPermissions($folder_id, "dummy@email.com",$presenter['Email']);        //changes owner and changes propagate booleans
    //$message = titledEmailBodyBuilder($message, array("Id" => $folder_id), "Folder");
  }
  //$_SESSION["message"] = $message;
  $_SESSION["lecturer"] = $presenter;
  $_SESSION["module_id"] = $mod_res['Id'];
  //sendEmail('hedge@csus.edu', 'New Mediasite Submission', $message);
}
if($folder_id != null && $_POST['schedules']) {
  echo "<br>Success! Next page will load momentairly.";
  //BELOW MUST BE INCLUDED TO MOVE TO SCHEDULES PAGE PROPERLY. COMMENTED OUT FOR TESTING PURPSOES
  echo '<META HTTP-EQUIV="Refresh" Content="2; URL=schedules.php?folder_id=' . $folder_id . '">';
} else if ($flag && $folder_id == null){
  echo "Folder not created, no catalog name provided. Unable to move to schedules without a folder.";
} else if(!$_POST['schedules'] && $flag) {
  echo "Everything created! Schedules was not selected!";
}
function catalog_create() {
  $cat_name = $_POST['cat_name'];
  $cat_friendname = $_POST['cat_friendname'];
  $cat_desc = $_POST['description'];
  $jsonFile = createFields(['Name', 'FriendlyName', 'Description', 'SearchTerm', 'IsSearchBased'],[$cat_name, $cat_friendname, $cat_desc, $cat_name, true]);
  return $jsonFile;
}
function catalog_set($id) {
  $jsonFile = createFields(['SearchFields'], ['All']);
  return put(json_encode($jsonFile), "Catalogs('" . $id . "')/Settings");
}
function presenter_create() {
  $fname = $_POST['firstname'];
  $lname = $_POST['lastname'];
  $email = $_POST['email'];
  $assoc['Email'] = $email;
  $check = doesItExist("Presenters", $assoc, 'Email');    //checks if the presenter already exists
  $jsonFile;
  if($check == -1){
    $jsonFile = createFields(['FirstName', 'LastName', 'Email'],[$fname, $lname, $email]);
  } else {
    $jsonFile = get("Presenters('". $check . "')");

  }
  return $jsonFile;
}
function module_create() {
  $moduleid = $_POST['moduleid'];
  $name = $_POST['name'];
  $description = $_POST['description'];
  $jsonFile = createFields(['ModuleId', 'Name', 'Description'],[$moduleid, $name, $description]);
  return $jsonFile;
}
?>
</form>
</body>
</html>