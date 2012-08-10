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
	$pageHead = '<link rel="stylesheet" type="text/css" href="./master/css/install.css" />';
	require_once('lib/directory.php');
	require_once('lib/user.php');
	require_once('lib/torrent.php');
	
	$installed = false;
	$message = '';

	$userName = $passWord = $module = $location = $secretQuestion = $secretAnswer = '';

	if($_POST) {
		$userName = trim($_POST['UserName']);
		$passWord = trim($_POST['PassWord']);
		$module = trim($_POST['TorrentModule']);
		$location = trim($_POST['Location']);
		$secretQuestion = trim($_POST['SecretQuestion']);
		$secretAnswer = trim($_POST['SecretAnswer']);
	}

	// Make sure they are posting
	// back the information
	if(!empty($userName) &&
		!empty($passWord) &&
		!empty($module) &&
		!empty($location) &&
		!empty($secretQuestion) &&
		!empty($secretAnswer)){

			//create download directory if it doesn't exist
			if(!file_exists($location)) mkdir($location);
		
			$installed = true;
			require_once('lib/configuration.php');
			// set up the configuration.xml
			$configuration = new Configuration($location,
				$module,0,0,6,4,10,60000,60000);
			// write out the configuration.xml
			if(!config_setConfiguration($configuration)){
				$message .= "The configuration XML file could not be written.<br />";
				$installed = false;
			}
			
			//Check to see if the user information provided can be used to create a site admin
			if(user_createUser(new User($userName, $passWord, 
				$secretQuestion, $secretAnswer, "Admin")) === 'file error') {
				$message .= "The users XML file could not be written.<br />";
				$installed = false;
			}

			//check to see if the torrents.xml file is able to be written
			if(!torrent_createFile()) {
				$message .= "The torrents XML file could not be written.<br />";
				$installed = false;
			}
		} else if($_POST) {
			$message = "Please fill out the install form completely.<br />";
		}
	if($installed) {
?><!-- Executes when installed. -->
<div id="divInstallCenter">
	<div id="divInstallBox">
		<div id="divInstallBoxPad">
			<img src="./master/images/installLogo.gif" alt="Install Logo" />
		</div>
		<div id="divMessageHolder">TorrentVolve was installed successfully.<br /><br /><a href="login.php">Login</a></div>
	</div>
</div>
<?php
	// The module is installed already
	} else if(empty($message) && file_exists('../config/configuration.xml') && 
		file_exists('../config/users.xml')) {
?>
<div id="divInstallCenter">
	<div id="divInstallBox">
		<div id="divInstallBoxPad">
			<img src="./master/images/installLogo.gif" alt="Install Logo" />
		</div>
		<div id="divMessageHolder">TorrentVolve is already installed.</div>
	</div>
</div>
<?php
	} else {
?>
<!-- Executes when not installed. -->
<div id="divInstallCenter">
	<div id="divInstallBox">
		<div id="divInstallBoxPad">
			<form action="install.php" method="post">
			<img src="./master/images/installLogo.gif" alt="Install Logo" />
			<div id="divMessageHolder"><?php print $message; ?></div>
			<div id="divUsername">
				<div id="divUsernameLabel">Username:</div>
				<div id="divUsernameText"><input type="text" name="UserName" value="<?php print $userName;?>"></input></div>
			</div>
			<div id="divPassword">
				<div id="divPasswordLabel">Password:</div>
				<div id="divPasswordText"><input type="password" name="PassWord"></input></div>
			</div>
			<div id="divSecretQuestion">
				<div id="divSecretQuestionLabel">Secret Question:</div>
				<div id="divSecretQuestionText"><input type="text" name="SecretQuestion" value="<?php print $secretQuestion;?>"></input></div>
			</div>
			<div id="divSecretAnswer">
				<div id="divSecretAnswerLabel">Secret Answer:</div>
				<div id="divSecretAnswerText"><input type="text" name="SecretAnswer" value="<?php print $secretAnswer;?>"></input></div>
			</div>
			<div id="divDownloadLocation">
				<div id="divDownloadLocationLabel">DL Location:</div>
				<div id="divDownloadLocationText"><input type="text" name="Location" value="<?php print $location;?>"></input></div>
			</div>
			<div id="divTorType">
				<div id="divTorTypeLabel">Torrent Module:</div>
				<div id="divTorTypeText">
					<select name="TorrentModule">
					<?php
						$modulePath = realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . '/lib/torrentmodules';

						// Get all of the Torrent modules
						$dirs = scandir($modulePath);

						foreach($dirs as $dir) {
							if(is_dir("$modulePath/$dir") && !strstr($dir, '.')) {
								if($dir == $module) $selected = ' selected="selected"';
								else $selected = '';

								print "<option$selected>$dir</option>";
							}
						}
					?>
					</select>
				</div>
			</div>
			<br />
			<div id="divSubmit"><input type="submit" value="Install" /></div>
			</form>
		</div>
	</div>
</div>
<?php 
	}
	$masterBreak = true;
	$pagemaincontent = ob_get_contents();	// Assign all Page Specific Variables  
	ob_end_clean();  $pageTitle = "TorrentVolve | Installation";  	//Apply the template
	require("master.php");
?>