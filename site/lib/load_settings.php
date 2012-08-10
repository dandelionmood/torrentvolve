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

	$f = __FILE__;
	if (is_link($f)) $f = readlink($f); // Unix compatibility
	$appPath = dirname($f) . "/";

	require_once($appPath . 'configuration.php');

	//read settings from XML
	$xmlConfig = config_getConfiguration();

	//write settings to Torrent Module
	require_once($appPath . 'torrent_module_loader.php');
	$torrentModule = new TorrentFunctions('localhost');
	$torrentModule->writeSetting(TorrentSetting::MaxDownloadSpeed, $xmlConfig->getMaxDownloadSpeed());
	$torrentModule->writeSetting(TorrentSetting::MaxUploadSpeed, $xmlConfig->getMaxUploadSpeed());
	$torrentModule->writeSetting(TorrentSetting::MaxDownloads, $xmlConfig->getMaxDownloads());
	$torrentModule->writeSetting(TorrentSetting::MaxUploads, $xmlConfig->getMaxUploads());
	$torrentModule->writeSetting(TorrentSetting::MaxActiveTorrents, $xmlConfig->getMaxActiveTorrents());
	$torrentModule->writeSetting(TorrentSetting::TcpPort, $xmlConfig->getTcpPort());
	$torrentModule->writeSetting(TorrentSetting::UdpPort, $xmlConfig->getUdpPort());
?>