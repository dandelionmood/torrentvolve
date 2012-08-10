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

//simulated enum for Torrent module settings
final class TorrentSetting {
	
	const TcpPort = 0x01;
	const UdpPort = 0x02;
	const MaxActiveTorrents = 0x03;
	const MaxDownloads = 0x04;
	const MaxUploads = 0x05;
	const MaxDownloadSpeed = 0x06;
	const MaxUploadSpeed = 0x07;

	//do not allow instantiation
	private function __construct() { }
}

//simulated enum for Torrent priorities
final class TorrentPriority {
	
	const Normal = 0x01;
	const High = 0x02;
	const DoNotDownload = 0x03;

	//do not allow instantiation
	private function __construct() { }
}

//simulated enum for Torrent speed limits
final class TorrentSpeed {
	
	const Download = 0x01;
	const Upload = 0x02;

	//do not allow instantiation
	private function __construct() { }
}

//defines interface for accessing a Torrent module
interface ITorrentFunctions {
	function addTorrentByFile($fileName, $downloadTo);

	function removeTorrent($torrentName);
	
	function removeAllTorrents();

	function startTorrent($torrentName);

	function stopTorrent($torrentName);

	function getTorrentPriority($torrentName);

	function setTorrentPriority($torrentName, $torrentPriorityEnum);

	function getTorrentSpeedLimit($torrentName, $torrentSpeedEnum);

	function setTorrentSpeedLimit($torrentName, $torrentSpeedEnum, $rate);

	function getTorrentTracker($torrentName);

	function getTorrentList();

	function readSetting($torrentSettingEnum);

	function writeSetting($torrentSettingEnum, $value);

	function initialize();
}
?>