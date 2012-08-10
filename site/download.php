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

//We still use this method with PclZip
//so that we can stop adding files if
//the user disconnects.
function fillZipArchive(&$zipArchive, $directory, $extension = '') {

	//only ZIP more if the client is still connected
	if(connection_status() == 0) {
		$directoryListing = scandir($directory);

		foreach($directoryListing as $file) {
		
			$itemPath = "$directory/$file";
			if($extension == '') $fileName = "$file";
			else $fileName = "$extension/$file";

			//only ZIP this file if the client is still connected
			//OLD ZipArchive CODE
			//if(is_file($itemPath) && connection_status() == 0) $zipArchive->addFile($itemPath, $fileName);
			if(is_file($itemPath) && connection_status() == 0) $zipArchive->add($itemPath,
				PCLZIP_OPT_NO_COMPRESSION,
				PCLZIP_OPT_REMOVE_ALL_PATH,
				PCLZIP_OPT_ADD_PATH, $extension);

			if(is_dir($itemPath)) {
				
				if($file != '.' && $file != '..') fillZipArchive(&$zipArchive, $itemPath, $fileName);
			}
		}
	}
}

require_once('lib/configuration.php');

$fileName = $_GET['file'];
$filePath = config_getConfiguration()->getDownloadLocation() . '/' . $_GET['user'] . '/' . $fileName ;
$fileNameSplit = split('/', $fileName);
$fileName = $fileNameSplit[count($fileNameSplit) - 1];

if(file_exists($filePath)) {

	//allow lots of time (an hour) to create the ZIP archive
	//or for the client to download the file
	set_time_limit(3600);

	if(is_dir($filePath)) {

		//old PHP 5.2 ZipArchive code
		//we now use PclZip for portability
		//and its support of store-only
		//zipping
		//$zipFile = new ZipArchive();
		//$zipFile->open($filePath . '.zip', ZIPARCHIVE::OVERWRITE);
		//fillZipArchive(&$zipFile, $filePath);
		//$zipFile->close();

		require_once('lib/pclzip.lib.php');

		//delete ZIP file if it already exists
		if(file_exists("$filePath.zip")) unlink("$filePath.zip");

		$zipFile= new PclZip("$filePath.zip");

		//populate ZIP archive
		fillZipArchive(&$zipFile, $filePath);
		
		$filePath = $filePath . '.zip';
		$fileName = $fileName . '.zip';

		$deleteAfterSend = true;
	}

	ob_end_clean();

	$file = fopen($filePath, 'rb');

	header('Pragma: ');// leave blank to avoid IE errors
	header('Cache-Control: ');// leave blank to avoid IE errors
	header('Content-type: application/octet-stream');
	header('Content-Disposition: attachment; filename="' . $fileName . '"');
	header('Content-length:' . filesize($filePath));

	//send to client while connection is alive and file is not empty
	while(!feof($file) && connection_status() == 0) {

		print fread($file, 8192);
		flush();
	}
    
	fclose($file);

	if($deleteAfterSend) unlink($filePath);
} else echo 'The specified file does not exist.';
?>