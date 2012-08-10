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
	$f = __FILE__;
	if (is_link($f)) $f = readlink($f); // Unix compatibility
	$appPath = dirname($f) . "/";
	if(!defined("APPPATH")) define("APPPATH", "$appPath");
	
	/**
	 * Class housing user information
	 *
	 */
	class User
	{
		//Set the class variables
		var $userName;
		var $passWord;
		var $secretQuestion;
		var $secretAnswer;
		var $authLevel;
		
		//public constructor that takes in a username passowrd secret question secret question answer and auth level
		//this then assigns these values to the user object
		public function __construct($userName, $passWord, 
			$secretQuestion, $secretAnswer, $authLevel){
			$this->userName=$userName;
			$this->passWord=$passWord;
			$this->secretQuestion=$secretQuestion;
			$this->secretAnswer=$secretAnswer;
			$this->authLevel=$authLevel;
		}
		//Get the authentication level of a user
		public function getAuthLevel() { return $this->authLevel; }
		//sets the suthentication of a user
		public function setAuthLevel($authLevel) { $this->authLevel = $authLevel; }
		//sets the user name of a user
		public function setUserName($username){
		$this->userName=strtolower($username);
		}
		/**
		 * Get the current username of the user.
		 * @return string username of the user
		 */
		public function getUserName() { return $this->userName; }
		/**
		 * Set the password of the user.
		 * @param unhashed password of the user
		 */
		public function setPassWord($passWord) { $this->passWord = $passWord; }
		/*
		Gets the password of a user
		*/
		public function getPassWord(){return $this->passWord; }
		//Gets the secret question of a user
		public function getSecretQuestion() { return $this->secretQuestion; }
		//Sets the secret question of a user
		public function setSecretQuestion($secretQuestion) { 
			$this->secretQuestion = $secretQuestion;
		}
		//get the secret question o a user
		public function getSecretAnswer() { return $this->secretAnswer; }
		//Set the secret question of a user
		public function setSecretAnswer($secretAnswer) { 
			$this->secretQuestion = $secretAnswer;
		}
	}
	
	/**
	 * Gets all of the users that are in the users
	 * XML file.
	 *
	 * @return User array of all of the 
	 * users in the configuration file.
	 */
	function user_getUsers(){
		$filePath = basename(__FILE__);
		$file = file_get_contents(APPPATH . '../../config/users.xml');
		$xml = new SimpleXMLElement($file);
		foreach($xml->xpath("//user") as $user){
			$users[] = new User(
				$user->userName,
				$user->passWord,
				$user->secretQuestion,
				$user->secretAnswer,
				$user->authLevel);
		}
		return $users;
	}
	
	/**
	 * Gets a user object of the specified
	 * username.
	 *
	 * @param string $userName The name of
	 * the user to get the user object from.
	 * @return User object returned from the
	 * specified username.
	 */
	function user_getUserByUsername($userName){
		$userName = strtolower($userName);
		$file = file_get_contents(APPPATH . '../../config/users.xml');
		$xml = new SimpleXMLElement($file);
		$userInfo = $xml->xpath("/users/user[userName='$userName']");

		//does this user exist?
		if(count($userInfo)) {
		$user = new User($userInfo[0]->userName, 
			$userInfo[0]->passWord,
			$userInfo[0]->secretQuestion,
			$userInfo[0]->secretAnswer,
			$userInfo[0]->authLevel);
		return $user;
		} else { return false; }
	}
	
	/**
	 * Removes a user from the list of users
	 * available.
	 *
	 * @param string $userName The username
	 * of the user to remove.
	 */
	function user_removeUser($userName) {
		$file = file_get_contents(APPPATH . '../../config/users.xml');
		$xml = new SimpleXMLElement($file);
		// Make sure the user doesn't exist
		$userInfo = $xml->xpath("/users/user[not(userName='$userName')]");
		$final = '<?xml version="1.0" encoding="UTF-8"?>
<users>';
 		foreach($userInfo as $user) {
 			$final .= $user->asXML();
 		}
		$final .= '</users>';
		file_put_contents(APPPATH . "../../config/users.xml", $final);
	}
	
	/**
	 * Update a particular user's information
	 * in the XML file.
	 *
	 * @param User $user The user object that 
	 * should replace. The username is the name
	 * of the user that will be updated and the
	 * options in that user will be the options
	 * that are replacing.
	 */
	function user_updateUser($user) {
		$file = file_get_contents(APPPATH . '../../config/users.xml');
		$xml = new SimpleXMLElement($file);
		$userName = $user->getUserName();
		// Make sure the user doesn't exist
		$userInfo = $xml->xpath("/users/user[userName='$userName']");
		$userInfo[0]->passWord = (string)$user->getPassWord();
		$userInfo[0]->secretAnswer = (string)$user->getSecretAnswer();
		$userInfo[0]->secretQuestion = (string)$user->getSecretQuestion();
		$userInfo[0]->authLevel = (string)$user->getAuthLevel();
		file_put_contents(APPPATH . "../../config/users.xml", $xml->asXML());
	}
	
	/**
	 * Creates a new user in the xml file or a
	 * new users XML file with that user in it
	 * if one doesn't exist
	 *
	 * @param User $user The user object to add
	 * to the XML.
	 * @return boolean whether or not the user
	 * was created.
	 */
	function user_createUser($user) {
		// Make sure the user object is complete
		$userName = strtolower($user->getUserName());
		$passWord = md5($user->getPassWord());
		$secretQuestion = $user->getSecretQuestion();
		$secretAnswer = md5(strtolower($user->getSecretAnswer()));
		$authLevel = $user->getAuthLevel();
		if(empty($userName) || 
			empty($passWord) || 
			empty($secretQuestion) ||
			empty($secretAnswer) ||
			empty($authLevel)) {
				return false;
			}
		// Get the xml
		if(file_exists(APPPATH . '../../config/users.xml')){
			$file = file_get_contents(APPPATH . '../../config/users.xml');
			$xml = new SimpleXMLElement($file);
			// Make sure the user doesn't exist
			$userInfo = $xml->xpath("/users/user[userName='$userName']");
			if(!empty($userInfo)){
				return false;
			}
		} else {
			$xml = new SimpleXMLElement(user_genXML());
		}
		// write the user to the xml file
		$userXML = $xml->addChild('user');
		$userXML->addChild('userName', "$userName");
		$userXML->addChild('passWord', "$passWord");
		$userXML->addChild('secretQuestion', "$secretQuestion");
		$userXML->addChild('secretAnswer', "$secretAnswer");
		$userXML->addChild('authLevel',"$authLevel");
		if(@file_put_contents(APPPATH . "../../config/users.xml", $xml->asXML())) return true;
		else return 'file error';
	}
	
	/**
	 * Generates base XML to be used.
	 */
	function user_genXML(){
		$xmlString = 
'<?xml version="1.0" encoding="UTF-8"?>
<users>
</users>';
		return $xmlString;
	}
?>