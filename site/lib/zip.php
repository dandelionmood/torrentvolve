<?php
/*
 * This function allows to create a ZIP archive from a directory
 * easily using standard PHP ZipArchive lib.
*/

// Function to recursively add a directory,
// sub-directories and files to a zip archive
function addFolderToZip($dir, $zipArchive, $zipdir = ''){
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			// Add the directory
			if(!empty($zipdir)) $zipArchive->addEmptyDir($zipdir);
			// Loop through all the files
			while (($file = readdir($dh)) !== false) {
				//If it's a folder, run the function again!
				if(!is_file($dir . $file)){
					// Skip parent and root directories
					if( ($file !== ".") && ($file !== "..")){
						addFolderToZip($dir . $file . "/", $zipArchive, $zipdir . $file . "/");
					}
				}else{
					// Add the files
					$zipArchive->addFile($dir . $file, $zipdir . $file);
				}
			}
		}
	}
} 