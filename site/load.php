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

// It is compulsory to be logged in.
// ---------------------------------------------------------------
if(empty($_SESSION['user'])) {
	echo('<div id="divStatus">You must be logged in to load a Torrent.<br /><br />
		<input type="button" value="Close" onclick="javascript:window.close()"/></div>');
}
else
{
	// Are there torrent files or URL to process ?
	// ---------------------------------------------------------------
	if(isset($_FILES['torrentFile']['name']) || isset($_POST['url']))
	{
		// This variable will get true once we know we need to add torrents
		// to the download queue.
		$loadTorrent = false;
		
		// We initialise those variables as we're going to need them later.
		$torrentNames = array();
		$uploadFiles = array();
		
		// Get download directory from config 		
		require_once('lib/configuration.php');
		$uploadRoot = config_getConfiguration()->getDownloadLocation();

		// Change to config-specified directory
		$uploadDir = $uploadRoot.DIRECTORY_SEPARATOR.(string)$_SESSION['user'];

		// Create download directory if it doesn't exist
		if(!file_exists($uploadDir))
		mkdir($uploadDir);
		
		require_once('lib/torrent.php');
		
		// Torrent files were uploaded directly
		// ------------------------------------
		if(isset($_FILES['torrentFile']['name'])) {
			
			// We iterate on those to finish their uploading
			for( $i = 0; $i < count($_FILES['torrentFile']['name']); $i++ ) {
				
				// A quick check on the filename ...
				if (stristr($_FILES['torrentFile']['name'][$i], '.torrent')) {
					
					// get the actual name of the torrent
					$torrentNames[] = torrent_getTorrentNameFromFileName(
						$_FILES['torrentFile']['tmp_name'][$i],
						$_FILES['torrentFile']['name'][$i]
					);
					
					$uploadFiles[] = $uploadDir.DIRECTORY_SEPARATOR
						.$torrentNames[$i].'.torrent';
					
					// the uploader breaks permissions
					move_uploaded_file(
						$_FILES['torrentFile']['tmp_name'][$i],
						$uploadFiles[$i]
					);
					
					$loadTorrent = true;
					
				}
				else
				{
					echo('<div id="divStatus">The file you uploaded is not a Torrent.</div><br />');
					
					$loadContent = true;
				}
			
			}
		
		// ... A URL was provided
		// ----------------------
		} else {

			if(!empty($_POST['url'])) {
				
				// We need to download the file contents first, let's decide how :
				// ---------------------------------------------------------------
				
				// The fopen buffered way, if it's available
				if( ini_get('allow_url_fopen') ) {
					
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
					
				// We fall back to Curl if that isn't allowed (safe mode)
				} elseif( in_array('curl', get_loaded_extensions()) ) {
					
					$curl = curl_init();  
					curl_setopt($curl, CURLOPT_URL, $_POST['url']);  
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  
					$contents = curl_exec($curl);  
					curl_close($curl);   
					
				} else {
					
					echo('<div id="divStatus">You must either allow URL opening via
						fopen() or install PHP Curl extension to do that.</div><br />');
					$loadContent = true;
					
				}
				
				// The file content was successfully retrieved.
				// --------------------------------------------
				if( $content ) {
					
					// Get the actual name of the torrent
					$torrentNames[] = torrent_getTorrentNameFromFileData($contents);
					$uploadFiles[] = $uploadDir.DIRECTORY_SEPARATOR
						.$torrentNames[0].'.torrent';
					
					file_put_contents($uploadFiles[0], $contents);
					
					$loadTorrent = true;
				}
				
			} else {
				
				echo('<div id="divStatus">The URL was left empty.</div><br />');
				$loadContent = true;
				
			}
		}
		
		// There are torrents to upload ...
		// --------------------------------
		if($loadTorrent) {
			
			require_once('lib/torrent_module_loader.php');
			$torrentModule = new TorrentFunctions('localhost');
			
			$errors = array();
			
			// We iterate on the files we need to upload.
			foreach( $uploadFiles as $i => $uploadFile ) {
				$uploadFile = $uploadFiles[$i];
				$torrentName = $torrentNames[$i];
				
				$torrentModule->addTorrentByFile($uploadFile, $uploadDir);
				$torrent = new Torrent($_SESSION['user'], $torrentName, 'not defined');
				
				// We add the torrent to the queue and check for an error.
				$r = torrent_addTorrent($torrent);
				if( !$r ) $errors[] = $torrentName;
			}
			
			if( count($errors) > 0 ) {
				
				echo('<div id="divStatus">There was an issue with the following
					torrents, they may have already been downloaded by either you or
					another user :<br />'
					.implode(', ', $errors).'<br />'
					.'<input type="button" value="Close" onclick="javascript:window.close()"/></div>');
				
			} else {
				
				$howManyFiles = count($uploadFiles);
				if( $howManyFiles == 1 ) {
					echo('<div id="divStatus">The torrent was successfully loaded.<br /><br />
						<input type="button" value="Close" onclick="javascript:window.close()"/></div>');
				} else {
					echo('<div id="divStatus">The '.$howManyFiles.' torrents were successfully loaded.<br /><br />
						<input type="button" value="Close" onclick="javascript:window.close()"/></div>');
				}
				
			}
			
		}
		
	} else $loadContent = true;
	
	// Do we need to display a form ?
	if($loadContent == true) {
		?>
<form action="load.php" method="post" enctype="multipart/form-data">
	<div id="divFile">
	<div id="divFileLabel">File:</div>
	<div id="divFileText"><input type="file" name="torrentFile[]" multiple="multiple" /></div>
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
