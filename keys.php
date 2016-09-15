<html>
	<body>
	<form method='post'>
		<table align='center'> 
			<tr><td>Please Input a Password</td></tr>
			
			<tr><td><input type='password' name='pass' value='' size='20' /></td></tr>
			<tr><td><center><input type='submit' name='submit' value='submit' /></center></td></tr>
		</table>
		</form>
		<?php
		/* The Client Robert Van Winkle was kind enough to allow me to
 		* upload this project to GitHub under the condition that all
		* HTTP Requests and URLs be removed for security reasons.
		* This page only shows functions I created to help with creating
		* Json files.
		*/
			if(isset($_POST['submit'])) {
				if($_POST['pass'] == "Buzz1234"){
					echo "Success, please wait while the next page loads";
					//Bellow was gutted as per Client request. Was a simple HTTP Request to Google for a code.
					//Moved to keys2 after receiving a response.
					echo "<meta http-equiv=\"refresh\" content=\"0; URL=/>";
				} else {
					echo 'INCORRECT PASSPHRASE TRY AGAIN';
				}
			}
		?>
	</body>
</html>