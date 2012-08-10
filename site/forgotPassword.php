<?php
/*
	TorrentVolve - A lightweight, fully functional torrent client.
	Copyright (C) 2006  TorrentVolve

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License along
	with this program; if not, write to the Free Software Foundation, Inc.,
	51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

	ob_start(); // buffer
	session_start();
	// Add stylesheet for login.
	$pageHead = '<link rel="stylesheet" type="text/css" href="./master/css/forgotPassword.css" />';

	$message = '';
	$showSecretQuestion = false;
	$hide = false;

	//checks to see if the username entered is valid
	if(isset($_POST['username'])) {
		require_once('lib/user.php');
		$user = user_getUserByUsername(strtolower($_POST['username']));
		
		//if the username is vaild show the secret question
		if($user) {
			$un = $user->getUserName();

			$hide = true;
			$showSecretQuestion = true;
			$question = $user->getSecretQuestion();
		} else {
			$message = "Invalid user.";
		}
	
	} 
	/*
	 * This executes only if the user has answered their secret question
	 */
	else if(isset($_POST['answer'])) {
		require_once('lib/user.php');
		$hide = true;
		$userName = strtolower($_POST['validatedUsername']);
		$secretAnswer = md5(strtolower(trim($_POST['answer'])));
		$user = user_getUserByUsername($userName);
		//if the username is set this gets the secret question of the user
		if($user) {
			$un = $user->getUserName();

			$question = $user->getSecretQuestion();
			$answer = $user->getSecretAnswer();
			//If their secret answer they provided on this form mathes what is in the database
			//automatically logs the user into the site. 
			if($secretAnswer == $answer) {
				$_SESSION['user'] = $userName;
				header('Location: ./index.php');
			} 
			//if the secrect answer is wrong reasks their secret question to let them answer it correctly
			else {
				$message="Invalid answer.";
				$showSecretQuestion = true;
			}
		} 
		//If the username they entered doesn't exist then let them know 
		else {
			$message = "The user doesn't exist.";
			$hide = false;
		}
	}
	//When the secret question is asked this is form that does it.
	if($showSecretQuestion) {
?>
<div id="divCenter">
	<div id="divBoxOutline">
		<div id="divPadding">
			<div id="divForgotPasswordImage">
				<img src="./master/images/retrievePassword.gif" alt="Retrieve Password" />
			</div>
			<div id="divStatus"><?php print $message;?></div>
			<br />
			<form method="post" action="forgotPassword.php">
			<div>
				<div class="fieldLabel">Secret Question:</div>
				<div class="fieldData"><?php print $question; ?></div>
			</div>
			<br/>
			<div>
				<div class="fieldLabel">Answer:</div>
				<div class="fieldData"><input type="text" name="answer" /></div>
			</div>
			<br />
			<div id="divSubmit">
				<input type="hidden" name="validatedUsername" value="<?php print $un; ?>" />
				<input type="Submit" value="Validate" />
			</div>
			</form>
		</div>
	</div>
</div>
<?php
	}
	//When we need to get the username to see if it is vaild this is the form
	if(!$hide) {
?>
<div id="divCenter">
	<div id="divBoxOutline">
		<div id="divPadding">
			<div id="divForgotPasswordImage">
				<img src="./master/images/retrievePassword.gif" alt="Retrieve Password" />
			</div>
			<div id="divStatus"><?php print $message;?></div>
			<br />
			<form method="post" action="forgotPassword.php">
			<div>
				<div class="fieldLabel">Username:</div>
				<div class="fieldData"><input type="text" name="username" />
			</div>
			<br />
			<div id="divSubmit">
				<input type="Submit" value="Next" />
			</div>
			</form>
		</div>
	</div>
</div>
<?php 
	} 
	$masterBreak = true;
	$pagemaincontent = ob_get_contents();	// Assign all Page Specific Variables  
	ob_end_clean();  $pageTitle = "TorrentVolve | Password Retrieval";  	//Apply the template
	include("master.php");
?>