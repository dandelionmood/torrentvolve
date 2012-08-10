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
<title>Load Torrent</title>
<link rel="stylesheet" type="text/css" href="./master/css/master.css" />
<link rel="stylesheet" type="text/css" href="./master/css/load.css" />
</head>
<body>
<div id="divLoadCenter">
<div id="divLoadBox">
<div id="divPadder">
<div id="divLoadLogo"><img src="./master/images/loadTorrent.gif"
	alt="Load Torrent Logo" /></div>
<?php
$loadContent = false;

if(empty($_SESSION['user']))
echo('<div id="divStatus">You must be logged in to load a Torrent.<br /><br /><input type="button" value="Close" onclick="javascript:window.close()"/></div>');
else
{
	if(isset($_FILES['torrentFile']['name']) || isset($_POST['url']))
	{
		$loadTorrent = false;

		//get download directory from config 		
		require_once('lib/configuration.php');
		$uploadRoot = config_getConfiguration()->getDownloadLocation();

		//change to config-specified directory
		$uploadDir = $uploadRoot . '/' . (string)$_SESSION['user'];

		//create download directory if it doesn't exist
		if(!file_exists($uploadDir))
		mkdir($uploadDir);

		require_once('lib/torrent.php');

		if(isset($_FILES['torrentFile']['name'])) {
			if (stristr($_FILES['torrentFile']['name'], ".torrent")) {

				//get the actual name of the torrent
				$torrentName = torrent_getTorrentNameFromFileName($_FILES['torrentFile']['tmp_name'], $_FILES['torrentFile']['name']);
				$uploadFile = $uploadDir . '/' . $torrentName . '.torrent';

				//the uploader breaks permissions
				move_uploaded_file($_FILES['torrentFile']['tmp_name'], $uploadFile);

				$loadTorrent = true;
			}
			else
			{
				echo('<div id="divStatus">The file you uploaded is not a Torrent.</div><br />');
				$loadContent = true;
			}
		} else {

			if(isset($_POST['url']) && $_POST['url'] != '') {
				//open URL
				$webFile = @fopen($_POST['url'], 'rb');
				
				//download file
				$contents = '';
				do {
					$data = fread($webFile, 8192);
					if (strlen($data) == 0) {
						break;
					}
					$contents .= $data;
				} while (true);
				fclose($webFile);

				//get the actual name of the torrent
				$torrentName = torrent_getTorrentNameFromFileData($contents);
				$uploadFile = $uploadDir . '/' . $torrentName . '.torrent';
				$torrentFile = @fopen($uploadFile, 'wb');
				fwrite($torrentFile, $contents);
				fclose($torrentFile);

				$loadTorrent = true;
			} else {
				echo('<div id="divStatus">The URL was left empty.</div><br />');
				$loadContent = true;
			}
		}
		
		if($loadTorrent) {
			require_once('lib/torrent_module_loader.php');
			$torrentModule = new TorrentFunctions('localhost');
			$torrentModule->addTorrentByFile($uploadFile, $uploadDir);
			
			$torrent = new Torrent($_SESSION['user'], $torrentName, 'not defined');

			torrent_addTorrent($torrent);

			echo('<div id="divStatus">The Torrent was successfully loaded.<br /><br /><input type="button" value="Close" onclick="javascript:window.close()"/></div>');
		}
	} else $loadContent = true;

	if($loadContent == true) {
		?>
<form action="load.php" method="post" enctype="multipart/form-data">
<div id="divFile">
<div id="divFileLabel">File:</div>
<div id="divFileText"><input type="file" name="torrentFile" /></div>
</div>
<div class="divUpload"><input type="submit" value="Upload"
	alt="Upload" /></div>
</form>
<form action="load.php" method="post" enctype="multipart/form-data">
<div id="divUrl">
<div id="divUrlLabel">URL:</div>
<div id="divUrlText"><input type="text" name="url" /></div>
</div>
<div class="divUpload"><input type="submit" value="Upload"
	alt="Upload" /></div>
</form>
<?php
	}
}
?></div>
</div>
</div>
</body>
</html>
