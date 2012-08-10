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
	// Add stylesheet for login.
	$pageHead = '<link rel="stylesheet" type="text/css" href="../master/css/masterIndex.css" />';
	$pageHead .= '<link rel="stylesheet" type="text/css" href="../master/css/userIndex.css" />'
			. "\n\t<script type=\"text/javascript\" src=\"../master/js/userIndex.js\"></script>";
	$basedir = '../';
	$messText = "";
	session_start();
	
	$f = __FILE__;
	if (is_link($f)) $f = readlink($f); // Unix compatibility
	$appPath = dirname($f) . "/";
	
	// check for authentication
	if(!isset($_SESSION['user'])) {
		if(!$masterBreak)
			header('Location: ./login.php');
	} else {
		// get some user maddness.
		require_once($appPath . '../lib/user.php');
		// Make sure the user is a true user
		$globalUser = user_getUserByUsername($_SESSION['user']);
	}

	$set = true;
	
	if(empty($_POST['answer'])) {
		$message = "Please enter your secret answer.";
		$set = false;
	}

	if(empty($_POST['question'])) {
		$message = "Please enter your secret question.";
		$set = false;
	}

	if(empty($_POST['newPassword'])) {
		$message = "Please enter your new password.";
		$set = false;
	} else {
		if(empty($_POST['confirm'])) {
			$message = "Please enter your confirmation password.";
			$set = false;
		} else {
			$newPass = trim($_POST['newPassword']);
			$confirm = trim($_POST['confirm']);
	
			if($newPass != $confirm) {
				$message = "The passwords do not match.";
				$set = false;
			}
		}
	}

	if($set) {
		$updatedUser = new User(
				$globalUser->userName,
				md5($newPass),
				$_POST['question'],
				md5(strtolower(trim($_POST['answer']))),
				$globalUser->authLevel);
		user_updateUser($updatedUser);
		$message = "The changes were successful.";
		$set = true;
	}	
?>
<div id="divHeader" class="header">
	<div id="divNav">
		<a href="../index.php">Home</a>
	</div>
<div id="divLatestMessage">
	<div id="divLatestMessageLabel">Latest Message:&nbsp;</div>
	<div id="divLatestMessageText"><?php 
		if(!empty($_POST['post'])){
			print $message; 
		}
	?></div>
	
	</div>
</div>
<div id="divCPCenter">
	<div id="divCPBox">
		<div id="divCPBoxPadding">
			<form method="post" action="index.php" name="SettingsForm" onsubmit="return validateForm();">
			<div id="divLoginLogo">
				<img src="../master/images/password.gif" alt="Change Password" />
			</div>
			<div id="divNewPassword">
				<div id="divNewPasswordLabel">New Password: </div>
				<div id="divNewPasswordText"><input type="password" onblur="validateInput('newPassword', 'divNewPasswordLabel');" name="newPassword" /></div>
			</div>
			<div id="divConfirm">
				<div id="divConfirmLabel">Confirm Password: </div>
				<div id="divConfirmText"><input type="password" onblur="validateInput('confirm', 'divConfirmLabel');" name="confirm" /></div>
			</div>
			<div id="divQuestion">
				<div id="divQuestionLabel">Secret Question: </div>
				<div id="divQuestionText"><input type="text" onblur="validateInput('question', 'divQuestionLabel');" name="question" value="<?php if($set==false && isset($_POST['question'])) print $_POST['question']; ?>" /></div>
			</div>
			<div id="divAnswer">
				<div id="divAnswerLabel">Secret Answer: </div>
				<div id="divAnswerText"><input type="text" onblur="validateInput('answer', 'divAnswerLabel');" name="answer" value="<?php if($set==false && isset($_POST['answer'])) print $_POST['answer']; ?>" /></div>
			</div>
			<div id="divLinks">
				<div id="divLinksSubmit"><input type="submit" value="Save" /></div>
				<input type="hidden" name="post" value="post"/>
			</div>
			<div id="divValidation" style="margin-left: 15px; margin-top: 5px;"></div>
			</form>
		</div>
	</div>
</div>
<?php   
	$pagemaincontent = ob_get_contents();	// Assign all Page Specific Variables  
	ob_end_clean();  $pageTitle = "TorrentVolve | My Settings";  	//Apply the template
	require_once("../master.php");
	require_once("../lib/user.php")
?>