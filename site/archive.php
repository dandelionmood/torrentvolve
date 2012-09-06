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

if(isset($_GET['user'])) {
	$userName = $_GET['user'];
} else
$userName = $_SESSION['user'];

function listDirectoryContents($rootDir, $userName, $subDir = '', $level = 0, $fileNameClass = 'fileNameUnsure') {

	// We'll check if direct downloads are enabled.
	$activated_direct_download = config_getConfiguration()->getAllowDirectDownload();
	// We need to get that info from the configuration file.
	global $url_to_direct_download_dir;
	
	$scanDir = "$rootDir/$userName/$subDir";

	//grab contents of directory
	$userDirContents = scandir($scanDir);

	$noFiles = true;

	echo '<div id="dir_' . str_replace('/', '_', $subDir) . '"';
	if($level != 0) echo ' style="display:none;"';
	echo '>';

	foreach($userDirContents as $userDirItem) {

		if($userDirItem != '.' && $userDirItem != '..' && substr($userDirItem, strlen($userDirItem) - strlen('.torrent')) != '.torrent') {			

			$noFiles = false;

			//check for a corresponding .torrent file
			if($level == 0) {
				if(in_array($userDirItem . '.torrent', $userDirContents)) $fileNameClass = 'fileName';
				else $fileNameClass = 'fileNameUnsure';
			}
			
			// If direct downloads are activated, we display the direct URL to the file.
			if( $activated_direct_download ) {
				$downloadLink = $url_to_direct_download_dir.$userName.'/'.$subDir.$userDirItem;
			} else {
				$downloadLink = 'download.php?user=' . $userName . '&amp;file=' . urlencode($subDir . $userDirItem);
			}
			
			if(isset($_GET['user'])) $deleteLink = 'archive.php?user=' . $userName . '&amp;deleteFile=' . urlencode($subDir . $userDirItem);
			else $deleteLink = 'archive.php?deleteFile=' . urlencode($subDir . $userDirItem);

			$torrentDirPath = "$scanDir/$userDirItem";
			$torrentDir = "$subDir$userDirItem/";

			if(is_dir($torrentDirPath)) {
				$torrentDirId = str_replace('/', '_', $torrentDir);

				$topIdString = 'id="top_' . $torrentDirId . '" ';
				$collapseLink = '<a onclick="collapseDirectory(\'' . $torrentDirId . '\');"><img id="img_' . $torrentDirId . '" src="master/images/plus.gif" alt="Directory Expander"/></a>&nbsp;';
				$downloadLinkTitle = 'Download ZIP archive of this directory';
				$deleteLinkTitle = 'Delete this directory';
			} else {
				$topIdString = '';
				$collapseLink = '';
				$downloadLinkTitle = 'Download this file';
				$deleteLinkTitle = 'Delete this file';
			}
?>
	<div class="file">
		<div <?php echo $topIdString;?>class="<?php echo $fileNameClass;?>"><?php
	for($i = 0; $i < $level; $i++) echo '&nbsp;&nbsp;&nbsp;&nbsp;';
	echo $collapseLink;?><a href="<?php echo $downloadLink;?>" title="<?php echo $downloadLinkTitle;?>"><?php echo $userDirItem;?></a></div>
		<div class="fileActions"><a href="<?php echo $deleteLink;?>" title="<?php echo $deleteLinkTitle;?>">Delete</a></div>
	</div>
		<div><img src="master/images/single.gif"
			width="100%" height="1px" alt="Separator" /></div>
<?php
			if(is_dir($torrentDirPath)) {
				listDirectoryContents($rootDir, $userName, $torrentDir, $level + 1, $fileNameClass);
			}
		}
	}

	echo '</div>';

	if($noFiles and $level == 0) echo '<div class="divStatus">There are no downloaded files in this download directory.</div>';
}

function recursiveRemoveDirectory($path) {   
	$dir = scandir($path);

	foreach($dir as $item) {

		if($item != '..' && $item != '.') {

			if(is_file("$path/$item")) unlink("$path/$item");

			if(is_dir("$path/$item")) recursiveRemoveDirectory("$path/$item");
		}
	}

	rmdir($path);
}

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Archive for <?php echo $userName;?></title>
<link rel="stylesheet" type="text/css" href="master/css/master.css" />
<link rel="stylesheet" type="text/css" href="master/css/archive.css" />
<script type="text/javascript">
<!--
function collapseDirectory(obj) {
	var divDir = document.getElementById('dir_' + obj);
	var divTop = document.getElementById('top_' + obj);
	var imgDir = document.getElementById('img_' + obj);
	if ( divDir.style.display != 'none' ) {
		divDir.style.display = 'none';
		imgDir.src = 'master/images/plus.gif';
		
	}
	else {
		divDir.style.display = '';
		scroll(0, findYPos(divTop) - 9);
		imgDir.src = 'master/images/minus.gif';
	}
}

function findYPos(obj) {
	var curtop = 0;
	if (obj.offsetParent) {
		curtop = obj.offsetTop
		while (obj = obj.offsetParent) {
			curtop += obj.offsetTop
		}
	}
	return curtop;
}
//-->
</script>
</head>
<body>
<div id="divArchiveCenter">
<div id="divArchiveBox">
<div class="divArchivePadder">
<div><img src="master/images/archiveTorrents.gif"
	alt="Torrents" /></div>
<div>
<?php
if(empty($_SESSION['user']))
echo '<div class="divStatus">You must be logged in to view an archive.<br /><br /><input type="button" value="Close" onclick="javascript:window.close()"/></div>';
else {
	require_once('lib/user.php');
	$userAuthLevel = user_getUserByUsername($_SESSION['user'])->getAuthLevel();

	if($userAuthLevel == 'Admin' || $userAuthLevel == 'Power User' || $userName == $_SESSION['user']) {
	//get download directory from config 		
	require_once('lib/configuration.php');
	$downloadDir = config_getConfiguration()->getDownloadLocation();

	//change to config-specified directory
	$userDir = $downloadDir . '/' . $userName;

	//process Torrent actions
	//process Torrent load
	if(isset($_GET['loadTorrent'])) {

		//add Torrent to Torrent Module
		require_once('lib/torrent_module_loader.php');
		$torrentModule = new TorrentFunctions('localhost');
		$torrentModule->addTorrentByFile($userDir . '/' . $_GET['loadTorrent'], $userDir);

		//add Torrent to XML database
		require_once('lib/torrent.php');
		$torrent = new Torrent($userName, torrent_getTorrentNameFromFileName($userDir . '/' . $_GET['loadTorrent'], $_GET['loadTorrent']), 'not defined');
		torrent_addTorrent($torrent);

		echo '	<div class="divStatus">' . $_GET['loadTorrent'] . ' loaded.</div>' . "\n";
	}

	//process Torrent delete
	if(isset($_GET['deleteTorrent'])) {

		//delete Torrent from file system
		unlink($userDir . '/' . $_GET['deleteTorrent']);
		echo '	<div class="divStatus">' . $_GET['deleteTorrent'] . ' deleted.</div>' . "\n";
	}
?>
	<div class="rowHeadings">
			<div class="headingFilename">Filename</div>
			<div class="headingActions">Actions</div>
	</div>
	<div><img src="master/images/single.gif" width="100%" height="1px" alt="Separator" /></div>
<?php
	//check to see if download directory exists for this user
	if(!file_exists($userDir))
		mkdir($userDir);

	//grab contents of directory
	$userDirContents = scandir($userDir);

	$noTorrents = true;

	foreach($userDirContents as $userDirItem) {

		if(is_file($userDir . '/' . $userDirItem) && substr($userDirItem, strlen($userDirItem) - strlen('.torrent')) == '.torrent') {			

			$noTorrents = false;
			$downloadLink = 'download.php?user=' . $userName . '&amp;file=' . urlencode($userDirItem);

			if(isset($_GET['user'])) {
				$loadLink = 'archive.php?user=' . $userName . '&amp;loadTorrent=' . urlencode($userDirItem);
				$deleteLink = 'archive.php?user=' . $userName . '&amp;deleteTorrent=' . urlencode($userDirItem);
			} else {
				$loadLink = 'archive.php?loadTorrent=' . urlencode($userDirItem);
				$deleteLink = 'archive.php?deleteTorrent=' . urlencode($userDirItem);
			}
?>
	<div class="file">
		<div class="fileName"><a href="<?php echo $downloadLink;?>" title="Download this .torrent file"><?php echo $userDirItem;?></a></div>
		<div class="fileActions"><a href="<?php echo $loadLink;?>" title="Load this .torrent file">Load</a> | <a href="<?php echo $deleteLink;?>" title="Delete this .torrent file">Delete</a></div>
	</div>
		<div><img src="master/images/single.gif"
			width="100%" height="1px" alt="Separator" /></div>
<?php
		}
	}

	if($noTorrents) echo '<div class="divStatus">There are no .torrent files in this download directory.</div>';
?>
</div>
</div>
<div class="divArchivePadder">
<div><img src="master/images/archiveDownloadedFiles.gif"
	alt="Downloaded Files" /></div>
<div>
<?php

	//process file actions
	//process file/dir delete
	if(isset($_GET['deleteFile'])) {

		$fileName = "$userDir/" . $_GET['deleteFile'];

		//delete Torrent from file system
		if(is_file($fileName)) unlink($fileName);
		if(is_dir($fileName)) recursiveRemoveDirectory($fileName);

		echo '	<div class="divStatus">' . $_GET['deleteFile'] . ' deleted.</div>' . "\n";
	}
?>
	<div class="rowHeadings">
			<div class="headingFilename">Filename</div>
			<div class="headingActions">Actions</div>
	</div>
	<div><img src="master/images/single.gif" width="100%" height="1px" alt="Separator" /></div>
<?php

	listDirectoryContents($downloadDir, $userName);

	} else echo '<div class="divStatus">You do not have permission to view this user\'s archive.<br /><br /><input type="button" value="Close" onclick="javascript:window.close()"/></div>';
}
?>
</div>
</div>
</div>
</div>
</body>
</html>