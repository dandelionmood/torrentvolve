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
function validateInput(name, highLight) {
	if(document.getElementsByName(name)[0].value == "") {
		document.getElementById(highLight).style.color = highlight;
	} else {
		document.getElementById(highLight).style.color = "";
	}
}
function validateForm() {
	var valid = true;
	var message = "";
	// Manage the username box
	if(document.LoginForm.UserName.value == "") {
		valid = false;
		document.getElementById("divUsernameLabel").style.color = highlight;
		message += "Please enter a username.<br />";
	}
	// Manage the password box
	if(document.LoginForm.PassWord.value == "") {
		valid = false;
		document.getElementById("divPasswordLabel").style.color = highlight;
		message += "Please enter a password.<br />";
	}
	// Set up the message
	document.getElementById("divStatus").innerHTML = message;
	return valid;
}