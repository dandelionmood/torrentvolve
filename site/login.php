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
	$pageHead = '<link rel="stylesheet" type="text/css" href="master/css/login.css" />'.
				"\n\t<script type=\"text/javascript\" src=\"master/js/login.js\"></script>";

	//init message variable
	$message = '';

	// See if they are posting back
	if(!empty($_POST['UserName']) && 
		!empty($_POST['PassWord'])) {
		require_once('lib/user.php');
		// Adjust the variables
		$userName = strtolower(trim($_POST['UserName']));
		$passWord = md5(trim($_POST['PassWord']));
		$user = user_getUserByUsername($userName);
		// Check for username
		if($user) {
			if($user->getPassWord() == $passWord) {
				// write username to session
				$_SESSION['user'] = $userName;
				// if the user authentication is correct redirect to the site index
				header('Location: index.php');
			} else {
				$message = "Invalid credentials.";
			}
		} else {
			$message = "Invalid credentials.";
		}
	} else {
		$userName = '';
	}
?>
<div id="divLoginCenter">
	<!-- Base Box  -->  
	<div id="divLoginBox">
		<div id="divPadder">
			<div id="divLoginLogo"><img src="master/images/loginLogo.gif" alt="Login Logo" /></div>
			<div id="divStatus"><?php print $message; ?></div>
			<form action="login.php" name="LoginForm" method="post" onsubmit="return validateForm();">
			<table>
			<tr id="divUsername">
				<td id="divUsernameLabel">Username:</td>
				<td id="divUsernameText">
					<input type="text" name="UserName" onblur="validateInput('UserName', 'divUsernameLabel');" value="<?php echo $userName;?>" />
				</td>
			</tr>
			<tr id="divPassword">
				<td id="divPasswordLabel">Password:</td>
				<td id="divPasswordText">
					<input type="password" name="PassWord" onblur="validateInput('PassWord', 'divPasswordLabel');" />
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<table>
					<tr id="divAuth">
						<td id="divAuthForgot"><a href="forgotPassword.php">Forgot Password</a></td>
						<td id="divAuthLogin">
							<input type="submit" value="Login" />
						</td>
					</tr>
					</table>
				</td>
			</tr>
			</table>
			</form>
		</div>
	</div>
</div>
<?php 
	$masterBreak = true; 
	$pagemaincontent = ob_get_contents();	// Assign all Page Specific Variables  
	ob_end_clean();  $pageTitle = "TorrentVolve | Login";  	//Apply the template
	require_once("master.php");
?>