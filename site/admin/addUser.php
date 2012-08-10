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

$pageHead = '<link rel="stylesheet" type="text/css" href="../master/css/masterIndex.css" />';
$pageHead .= '<link rel="stylesheet" type="text/css" href="../master/css/adminAddUser.css" />'
		. "\r\t<script type=\"text/javascript\" src=\"../master/js/adminAddUser.js\"></script>";		
$basedir = '../';

require_once('../lib/user.php');

//Starts the session on the page
session_start();

$message = '';
?>
<!--Page Navigation -->	
<div id="divHeader" class="header">
	<div id="divNav">
		<a href="../index.php">Home</a> | 
		<a href="index.php">Admin</a> | 
		<a href="users.php">Manage Users</a> |
		<a href="../logout.php">Logout</a>
	</div>
</div>	
<!--This is the actual form to display to the user-->
<div id="divCenter">
	<!-- Base Box  -->  
	<div id="divBox">
		<div id="divPadder">
			<div id="divLogo">
				<img src="../master/images/adminAddUser.gif" alt="Add User Logo" /><br />
			</div>
<?php
//checks to see if the person is logged in as an admin.
if(isset($_SESSION['user']) && user_getUserByUsername($_SESSION['user'])->getAuthLevel() == 'Admin') {

	$createUser = false;
	$username = ''; $secretQuestion = ''; $secretAnswer = '';
	$usernameLabelStyle = $passwordLabelStyle = $secretQuestionLabelStyle = $secretAnswerLabelStyle = 'fieldLabel';

	//user is posting back
	if(count($_POST)) {

		//check username field
		if(!empty($_POST['username'])) {
			$username = strtolower(trim($_POST['username']));
			$createUser = true;
		} else {
			$message .= 'Please enter a username.<br />';
			$usernameLabelStyle .= 'Highlight';
		}

		//check password field
		if(!empty($_POST['password'])) {
			$password = trim($_POST['password']);
			$createUser = $createUser & true; //create if all fields so far are valid
		} else {
			$message .= 'Please enter a password.<br />';
			$passwordLabelStyle .= 'Highlight';
			$createUser = false;
		}

		//check secret question field
		if(!empty($_POST['secretQuestion'])) {
			$secretQuestion = trim($_POST['secretQuestion']);
			$createUser = $createUser & true; //create if all fields so far are valid
		} else {
			$message .= 'Please enter a secret question.<br />';
			$secretQuestionLabelStyle .= 'Highlight';
			$createUser = false;
		}

		//check secret answer field
		if(!empty($_POST['secretAnswer'])) {
			$secretAnswer = strtolower(trim($_POST['secretAnswer']));
			$createUser = $createUser & true; //create if all fields so far are valid
		} else {
			$message .= 'Please enter a secret answer.<br />';
			$secretAnswerLabelStyle .= 'Highlight';
			$createUser = false;
		}

		$authLevel = $_POST['authLevel']; //don't bother checking the select input

		if($createUser) {
			$user = new User($username, $password, $secretQuestion, $secretAnswer, $authLevel);

			if(user_createUser($user)) $message = 'The user was added successfully.';
			else {
				$message = 'This user already exists.';
				$createUser = false;
			}
		}
	}
?>
			<form action="addUser.php" method="post" name="AddUserForm" onsubmit="return validateForm();">
			<div id="divStatus"><?php print $message; ?></div>
			<br />
			<table>
			<tr class="field">
				<td id="divUsernameLabel" class="<?php print $usernameLabelStyle;?>">Username:</td>
				<td class="fieldData"><input type="text" name="username" onblur="validateInput(this, 'divUsernameLabel');" value="<?php if(!$createUser) print $username;?>" /></td>
			</tr>
			<tr class="field">
				<td id="divPasswordLabel" class="<?php print $passwordLabelStyle;?>">Password:</td>
				<td class="fieldData"><input type="password" name="password" onblur="validateInput(this, 'divPasswordLabel');" /></td>
			</tr>
			<tr class="field">
				<td id="divSecQuesLabel" class="<?php print $secretQuestionLabelStyle;?>">Secret Question:</td>
				<td class="fieldData"><input type="text" name="secretQuestion" onblur="validateInput(this, 'divSecQuesLabel');" value="<?php if(!$createUser) print $secretQuestion;?>" /></td>
			</tr>
			<tr class="field">
				<td id="divSecAnsLabel" class="<?php print $secretAnswerLabelStyle;?>">Secret Question Answer:</td>
				<td class="fieldData"><input type="text" name="secretAnswer" onblur="validateInput(this, 'divSecAnsLabel');" value="<?php if(!$createUser) print $secretAnswer;?>" /></td>
			</tr>
			<tr class="field">
				<td class="fieldLabel">Authentication Level:</td>
				<td class="fieldData"><select name="authLevel"><option selected="selected">User</option><option>Power User</option><option>Admin</option></select></td>
			</tr>
			<tr id="rowSubmit">
				<td colspan="2"><input type="submit" title="Submit" value="Add User" /></td>
			</tr>
			</table>
			</form>
<?php } else{?>
			<div id="divStatus">Access denied.<br /><a href="../index.php">back</a></div>
			<?php
}?>

		</div>
	</div>
</div>
<?php
$pagemaincontent = ob_get_contents();	// Assign all Page Specific Variables
ob_end_clean();  $pageTitle = "TorrentVolve | Add User";  	//Apply the template
require_once("../master.php");

?>