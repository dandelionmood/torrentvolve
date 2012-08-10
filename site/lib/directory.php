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

	//   Developer: Charles Pence
	//        Date: 10/23/2006
	// Description: This file is used to gather file and directory information.
	
	
	/*
	* Gets the current path
	* @return string current path
	*/
	function getPath(){
		$path = $_GET['path'];
		if(!isset($path)){
			$path = ".";
		}
		return $path;
	}
	
	/**
	 * Gets the current directory names
	 * within a givin path.
	 * @param string path to list the directories in
	 * @return string[] current directories within
	 * this path.
	 */
	function dir_getDirs($path){
		$path = $_SERVER['APPL_PHYSICAL_PATH'] . $path;
		$dirArray = array();	// creates the string[] for the directories
		// print $_SERVER['APPL_PHYSICAL_PATH'] . $path;
	if($handle = opendir($path)){
			while(false !== ($file = readdir($handle))){	
				if($file != "." && $file != ".."){
					$fName = $file;
					$file = $path.DIRECTORY_SEPARATOR.$file;
	
					if(is_dir($file)){

						//strSubString

						array_push($dirArray,$file);
						//array_push($dirArray,$file);
					}
				}
			}
		}
		return $dirArray;		// return the string[]
	}
	
	
	/*
	* Gets the current files in the specified directory
	* @param string path to the current directory
	* @return string[] current files in directory.
	*/
	function dir_getFiles($path){
		$path = $_SERVER['APPL_PHYSICAL_PATH'] . $path;
		print $path;
		$fileArray = array();	// creates the string[] of files;
		if($handle = opendir($path)){
			while(false !== ($file = readdir($handle))){		
				if($file != "." && $file != ".."){
					
					$fName = $file;
					$file = $path.DIRECTORY_SEPARATOR.$file;
	
					if(is_file($file)){
						array_push($fileArray,$file);
					}
				}
			}
		}
		return $fileArray;		// return the string[].
	}
?>