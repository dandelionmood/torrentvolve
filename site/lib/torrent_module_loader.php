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

require_once('configuration.php');
// Temp directory fix
$f = __FILE__;
if (is_link($f)) $f = readlink($f); // Unix compatibility
$appPath = dirname($f) . "/";

if(!isset($basedir))
		$basedir = "./";
// check for config file
if(!file_exists($appPath . '../../config/configuration.xml') || 
	!file_exists($appPath . '../../config/users.xml')) {
	header('Location: ./install.php');
} else
require_once('torrentmodules/' . config_getConfiguration()->getTorrentModule() . '/torrent_functions.php');
?>