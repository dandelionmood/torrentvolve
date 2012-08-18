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
require_once('lib/configuration.php');

$fileName = $_GET['file'];

$xmlConfig = config_getConfiguration();
$filePath = config_getConfiguration()->getDownloadLocation()
	.'/'.$_GET['user'].'/'.$fileName;
$fileNameSplit = split('/', $fileName);
$fileName = $fileNameSplit[count($fileNameSplit) - 1];

if(file_exists($filePath)) {

	//allow lots of time (an hour) to create the ZIP archive
	//or for the client to download the file
	set_time_limit(3600);
	
	// If the file is actually a directory, we're going to create a zip archive
	// to allow an easy download.
	if(is_dir($filePath)) {
		
		// We're going to need the zip function.
		require_once('lib/zip.php');
		
		// We list files and create an archive of the folder for the user
		// to download.
		$zipfile = "$filePath.zip";
		$objects = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($filePath),
			RecursiveIteratorIterator::SELF_FIRST
		);
		$files = array();
		foreach($objects as $name => $object) $files[] = $name;
		create_zip($files, $zipfile, false);
		
		// We're going to give the zip archive to our user.
		$filePath = $zipfile;
		$fileName = "$fileName.zip";
		
		$deleteAfterSend = true;
	}

	ob_end_clean();

	// We're going to stream the file to the user (this way, there is no way
	// for him to know where other files are stored)
	if( $xmlConfig->getAllowDirectDownload() == 'no' ) {
		
		$file = fopen($filePath, 'rb');
		
		header('Pragma: ');// leave blank to avoid IE errors
		header('Cache-Control: ');// leave blank to avoid IE errors
		header('Content-type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.$fileName.'"');
		header('Content-length:' . filesize($filePath));
		
		// Send to client while connection is alive and file is not empty
		while(!feof($file) && connection_status() == 0) {
			print fread($file, 8192);
			flush();
		}
		
		fclose($file);
		
		if($deleteAfterSend) unlink($filePath);
	
	}
	
	// We're simply going to let the user download his file.
	else {
		
		// We're using the configuration to define 
		$direct_download_url = $url_to_direct_download_dir
			.$_GET['user'].'/'.$fileName;
		
		header("Location: $direct_download_url");
		
	}
	
} else echo 'The specified file does not exist.';
?>