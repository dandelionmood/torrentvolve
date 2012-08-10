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
var highlight = "#CC6600";
function validateInput(inputObject, highLight) {
	if(inputObject.value == "") {
		document.getElementById(highLight).style.color = highlight;
	} else {
		document.getElementById(highLight).style.color = "";
	}
}
function validateForm() {
	var valid = true;
	var message = "";
	// Manage the username box
	if(document.AddUserForm.username.value == "") {
		valid = false;
		document.getElementById("divUsernameLabel").style.color = highlight;
		message += "Please enter a username.<br />";
	}
	// Manage the password box
	if(document.AddUserForm.password.value == "") {
		valid = false;
		document.getElementById("divPasswordLabel").style.color = highlight;
		message += "Please enter a password.<br />";
	}
	// Manage the secret question box
	if(document.AddUserForm.secretQuestion.value == "") {
		valid = false;
		document.getElementById("divSecQuesLabel").style.color = highlight;
		message += "Please enter a secret question.<br />";
	}
	// Manage the secret answer box
	if(document.AddUserForm.secretAnswer.value == "") {
		valid = false;
		document.getElementById("divSecAnsLabel").style.color = highlight;
		message += "Please enter a secret answer.<br />";
	}
	// Set up the message

	document.getElementById("divStatus").innerHTML = message;
	var message = "";
	return valid;
}