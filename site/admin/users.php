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
session_start();
ob_start(); // buffer
	// Add stylesheet for login.
$pageHead = '<link rel="stylesheet" type="text/css" href="../master/css/masterIndex.css" />';
$pageHead .= '<link rel="stylesheet" type="text/css" href="../master/css/adminUsers.css" />';
$basedir = '../';

require_once('../lib/user.php');

$message = '';
?>
<!-- Page Navigation -->
<div id="divHeader" class="header">
	<div id="divNav">
		<a href="../index.php">Home</a> | 
		<a href="index.php">Admin</a> | 
		<a href="addUser.php">Add User</a> |
		<a href="../logout.php">Logout</a>
	</div>
</div>
<div id="divCenter">
	<div id="divBoxOutline">
		<div id="divPadding">
			<div><img src="../master/images/userAdministration.gif" alt="User Administration" /></div>
<?php
//allow only admins
if(isset($_SESSION['user']) && user_getUserByUsername($_SESSION['user'])->getAuthLevel() == 'Admin') {

	if(isset($_POST['delete'])) {
		// check if the user exists
		$userNameToDelete = $_POST['delete'];
		if(user_getUserByUsername($userNameToDelete)) {
			// delete the user
			user_removeUser($userNameToDelete);
			$message = "$userNameToDelete has been deleted.";
		} else $message = "That user doesn't exist.";
	}

	if(isset($_POST['update'])) {
		if(isset($_POST['authLevel'])) {
			if($_POST['authLevel'] == "Admin" ||
				$_POST['authLevel'] == "Power User" ||
				$_POST['authLevel'] == "User") {

				// check if the user exists
				$userNameToUpdate = $_POST['update'];
				if($userToUpdate = user_getUserByUsername($userNameToUpdate)) {
					// update the user
					$userToUpdate->setAuthLevel($_POST['authLevel']);
					user_updateUser($userToUpdate);

					$message = "$userNameToUpdate's authentication level has been updated.";
				} else $message = "That user doesn't exist.";
			} else {
				$message = "Invalid auth level.";
			}
		} else {
			$message = "Auth level not set.";
		}
	}
?>
			<div class="status"><?php print $message; ?></div><br />
			<table id="tableUsers" cellspacing="0" border="0">
				<tr id="rowHeader">
					<td class="cellHeader" width="125px">Username</td>
					<td class="cellHeader">Authentication Level</td>
					<td class="cellHeader">Delete</td>
				</tr>
				<tr>
					<td colspan="3"><img src="../master/images/single.gif" width="100%" height="1px" alt="Separator" /></td>
				</tr>
<?php
	$allUsers = user_getUsers();
	if(count($allUsers) > 1) {
		foreach($allUsers as $single) {
			// get the user information
			$userName = $single->getUserName();
			$authLevel = $single->getAuthLevel();
			if($userName != $_SESSION['user']) {

				// put together the drop down list
				switch ($authLevel) {
					case "User":
						$selection = '<option selected="selected">User</option><option>Power User</option><option>Admin</option>';
						break;
					case "Power User":
						$selection = '<option>User</option><option selected="selected">Power User</option><option>Admin</option>';
						break;
					case "Admin":
						$selection = '<option>User</option><option>Power User</option><option selected="selected">Admin</option>';
						break;
				}
?>
				<tr class="userRow">
					<td><a href="../archive.php?sid=<?php print session_id(); ?>&amp;user=<?php print $userName; ?>" onclick="javascript:window.open(this.href, 0, 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=0,width=700,height=550');return false;" target="_blank"  title="View this user's archive"><?php print $userName; ?></a></td>
					<td>
						<form method="post" action="users.php">
							<input type="hidden" name="update" value="<?php print $userName; ?>" />
							<select name="authLevel">
								<?php print $selection; ?>

							</select>
							<input type="submit" value="Update" />
						</form>
					</td>
					<td>
						<form method="post" action="users.php">
							<input type="hidden" name="delete" value="<?php print $userName; ?>" />
							<input type="submit" value="Delete" />
						</form>
					</td>
				</tr>
				<tr>
					<td colspan="3"><img src="../master/images/single.gif" width="100%" height="1px" alt="Separator" /></td>
				</tr>
<?php
			}
		}
	} else print '<tr class="userRow"><td class="status" colspan="3">You are the only active user.</td></tr>';
?>
			</table>
<?php
} else print '			<div class="status">Access denied.<br /><a href="../index.php">back</a></div>';
?>
		</div>
	</div>
</div>
<?php
	$pagemaincontent = ob_get_contents();	// Assign all Page Specific Variables
	ob_end_clean();  $pageTitle = "TorrentVolve | User Administration";  	//Apply the template
	require_once("../master.php");
?>