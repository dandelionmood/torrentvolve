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

// Find TorrentVolve application directory
$f = __FILE__;
if (is_link($f)) $f = readlink($f); // Unix compatibility
$appPath = dirname($f) . "/";

if(!isset($basedir))
		$basedir = "";
// check for config file
if(!file_exists($appPath . '../config/configuration.xml') || 
	!file_exists($appPath . '../config/users.xml')) {
	if(!$masterBreak)
		header('Location: install.php');
} else {
	// check for authentication
	if(!isset($_SESSION['user'])) {
		if(!$masterBreak)
			header('Location: login.php');
	} else {
		// get some user maddness.
		require_once($appPath . 'lib/user.php');
		// Make sure the user is a true user
		$globalUser = user_getUserByUsername($_SESSION['user']);
		if($globalUser->getUserName() == "") {
				if(!$masterBreak) {
					header('Location: login.php');
				}
		}
	}
}

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?php echo $pageTitle; ?></title>
	<link rel="stylesheet" type="text/css" href="<?php print $basedir . 'master/css/master.css'; ?>" />
	<?php echo $pageHead; ?>

</head>
<body>
<div id="divPrimaryHolder">
	<div id="divTopBanner" class="innerMargin">
		<img src="<?php print $basedir . 'master/images/logo.gif'; ?>" alt="TorrentVolveLogo" />
	</div>
	<div id="divContent" class="innerMargin">
<?php print $pagemaincontent; ?>
	</div>
</div>
</body>
</html>