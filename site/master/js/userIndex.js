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
	// Manage the old password box
	if(document.SettingsForm.oldPassword.value == "") {
		valid = false;
		document.getElementById("divOldPassword").style.color = highlight;
		message += "Please enter your old password.<br />";
	}
	// Manage the new password box
	if(document.SettingsForm.newPassword.value == "") {
		valid = false;
		document.getElementById("divNewPassword").style.color = highlight;
		message += "Please enter your new password.<br />";
	}
	// Manage the confirmation password box
	if(document.SettingsForm.confirm.value == "") {
		valid = false;
		document.getElementById("divConfirm").style.color = highlight;
		message += "Please enter your confirmation password.<br />";
	}
	// Manage the secret question box
	if(document.SettingsForm.question.value == "") {
		valid = false;
		document.getElementById("divQuestion").style.color = highlight;
		message += "Please enter your secret question.<br />";
	}
	// Manage the secret answer box
	if(document.SettingsForm.answer.value == "") {
		valid = false;
		document.getElementById("divAnswer").style.color = highlight;
		message += "Please enter your secret answer.<br />";
	}
	document.getElementById("divValidation").innerHTML = message;
	return valid;
}