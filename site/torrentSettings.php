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

if(isset($_GET['sid'])) session_id(strip_tags($_GET['sid']));
session_start();

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Torrent Settings</title>
<link rel="stylesheet" type="text/css" href="./master/css/master.css" />
<link rel="stylesheet" type="text/css" href="./master/css/torrentSettings.css" />
</head>
<body>
<div id="divCenter">
<div id="divBox">
<div id="divPadder">
<div id="divLogo"><img src="./master/images/torrentSettings.gif"
	alt="Torrent Settings Logo" /></div>
<?php
if(empty($_SESSION['user'])) echo('<div id="divStatus">You must be logged in to configure a Torrent.</div><br/><input type="button" value="Close" onClick="javascript:window.close()"/>');
else {
	if(empty($_GET['name'])) echo('<div id="divStatus">No Torrent name specified.</div><br/><input type="button" value="Close" onClick="javascript:window.close()"/>');
	else {
		//get this user's access level
		require_once('lib/user.php');
		$userName = $_SESSION['user'];
		$userAuthLevel = user_getUserByUsername($userName)->getAuthLevel();

		$torrentName = $_GET['name'];

		//get owner of the Torrent
		require_once('lib/torrent.php');
		$torrentUser = torrent_getTorrentByName($torrentName)->getUserName();

		//check if the user the owner of the Torrent, an admin, or a power user
		if($userAuthLevel != 'Admin' && $userAuthLevel != 'Power User' && $userName != $torrentUser) echo('<div id="divStatus">You do not have permission to configure this Torrent.</div><br/><input type="button" value="Close" onClick="javascript:window.close()"/>');
		else {
			//setup Torrent Module
			require_once('lib/torrent_module_loader.php');
			$torrentModule = new TorrentFunctions('localhost');

			//user is saving settings
			if(count($_POST)) {
				//map form values to constant values
				$priorities = array('normal' => TorrentPriority::Normal,
					'high' => TorrentPriority::High,
					'doNotDownload' => TorrentPriority::DoNotDownload);

				//set Torrent's priority
				$torrentModule->setTorrentPriority($torrentName, $priorities[$_POST['priority']]);

				//set Torrent's maximum download speed
				$torrentModule->setTorrentSpeedLimit($torrentName, TorrentSpeed::Download, $_POST['maxDownload']);

				//set Torrent's maximum upload speed
				$torrentModule->setTorrentSpeedLimit($torrentName, TorrentSpeed::Upload, $_POST['maxUpload']);

				echo '<div id="divStatus">Settings applied.</div>';
			}

			//shorten Torrent name to fit
			if(strlen($torrentName) > 42) $shortTorrentName = substr($torrentName, 0, 39) . '...';
			else $shortTorrentName = $torrentName;

			$normalChecked = ''; $highChecked = ''; $doNotDownloadChecked = '';

			//check the corresponding radio button for the Torrent's priority
			switch($torrentModule->getTorrentPriority($torrentName)) {
				case TorrentPriority::Normal: $normalChecked = 'checked="checked" ';
					break;
				case TorrentPriority::High: $highChecked = 'checked="checked" ';
					break;
				case TorrentPriority::DoNotDownload: $doNotDownloadChecked = 'checked="checked" ';
					break;
			}
?>
<form action="torrentSettings.php?name=<?php echo urlencode($torrentName);?>" method="post" enctype="multipart/form-data">
<table width="100%">
<tr class="row">
	<td class="header">Torrent Name:</td>
	<td class="data" title="<?php echo $torrentName;?>"><?php echo $shortTorrentName;?></td>
</tr>
<tr class="row">
	<td class="header">Priority:</td>
	<td class="data">
		<input id="prio_n" type="radio" name="priority" value="normal" <?php echo $normalChecked;?>/><label for="prio_n">Normal</label><br />
		<input id="prio_h" type="radio" name="priority" value="high" <?php echo $highChecked;?>/><label for="prio_h">High</label><br />
		<input id="prio_d" type="radio" name="priority" value="doNotDownload" <?php echo $doNotDownloadChecked;?>/><label for="prio_d">Do Not Download</label><br />
	</td>
</tr>
<tr class="row">
	<td class="header">Max. Download Speed:</td>
	<td class="data"><input type="text" name="maxDownload" value="<?php echo $torrentModule->getTorrentSpeedLimit($torrentName, TorrentSpeed::Download);?>" />&nbsp;kB/s</td>
</tr>
<tr class="row">
	<td class="header">Max. Upload Speed:</td>
	<td class="data"><input type="text" name="maxUpload" value="<?php echo $torrentModule->getTorrentSpeedLimit($torrentName, TorrentSpeed::Upload);?>" />&nbsp;kB/s</td>
</tr>
<tr>
	<td class="applyRow" colspan="2"><input type="submit" value="Apply Settings" alt="Apply Settings" /></td>
</tr>
</table>
</form>
<?php
		}
	}
}	
?></div>
</div>
</div>
</body>
</html>