<?php
/* The Client Robert Van Winkle was kind enough to allow me to
 * upload this project to GitHub under the condition that all
 * HTTP Requests and URLs be removed for security reasons.
 * This page only shows functions I created to help with creating
 * Json files.
 */
include("functions.php");
function main() {
	$response = get("UserProfiles");	
	$unactivated_ids = checkActivation(json_decode($response,true));
	$res_post;
	if($unactivated_ids != null) {
		for($i = 0; $i<count($unactivated_ids); $i++) {
			$fields = createFields(["Activated"],[true]);
			$res_post[$i] = put(json_encode($fields), "UserProfiles('" . $unactivated_ids[$i] . "')");
		}
	}
}
function checkActivation($jsonFile) {
	$temp;
	$count = 0;
	for($i = 0; $i<count($jsonFile["value"]); $i++){
		if($jsonFile["value"][$i]["Activated"] != 1) {
			$temp[$count]=$jsonFile["value"][$i]["Id"];	
			$count++;		
		}
	}
	return $temp;
}
?>