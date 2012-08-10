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

	/**
	 * This function will get the total disk space in bytes for drive c:\
	 * @param string $Path path to the drive.
	 * @return integer value for the total space of the drive in bytes.
	 */
	function GetTotalSpace($Path){
		$TotalSpace = disk_total_space($Path);
		return $TotalSpace;
	}
	
	
	
	/**
	 * This function will get the free disk space in bytes for drive c:\
	 * @param string $Path path to the drive.
	 * @return integer value for the free space of the drive in bytes.
	 */
	function GetFreeSpace($Path){
		$FreeSpace = disk_free_space($Path);
		return $FreeSpace;
	}
	
	
	
	/**
	 * This function will calculate the Disk Space used and send back the percentage
	 *
	 * @param integer of the free space.
	 * @param integer of the total space.
	 * @return integer for the percentage of used space on the drive.
	 */
	function GetUsedSpace($fs,$ts){
		$du = $ts - $fs;
		$percentage = round(($du / $ts)*100);
		return $percentage;
	}
?>