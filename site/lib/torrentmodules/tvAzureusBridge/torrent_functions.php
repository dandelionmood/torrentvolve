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

require_once($appPath . '../i_torrent_functions.php');
require_once($appPath . 'tvTelnetClient/client.php');

class TorrentFunctions implements ITorrentFunctions {
	private $telnetClient;
	private $server;
	private $statuses;
	private $settings;
	private $priorities;

	public function __construct($server) {
		$this->telnetClient = new TvTelnetClient();
		$this->server = $server;
		$this->statuses = array('>' => 'Downloading', '*' => 'Seeding',
			'!' => 'Paused', '.' => 'Waiting', ':' => 'Ready',
			'-' => 'Queued', 'A' => 'Allocating', 'C' => 'Checking',
			'E' => 'Error', 'I' => 'Initializing', '?' => 'Unknown');
		$this->settings = array(TorrentSetting::TcpPort => 'TCP.Listen.Port',
			TorrentSetting::UdpPort => 'UDP.Listen.Port',
			TorrentSetting::MaxActiveTorrents => 'max active torrents',
			TorrentSetting::MaxDownloads => 'max downloads',
			TorrentSetting::MaxUploads => 'Max Uploads',
			TorrentSetting::MaxDownloadSpeed => 'Max Download Speed KBs',
			TorrentSetting::MaxUploadSpeed => 'Max Upload Speed KBs');
		$this->priorities = array(TorrentPriority::Normal => 'normal',
			TorrentPriority::High => 'high',
			TorrentPriority::DoNotDownload => 'dnd');
		$this->speedLimits = array(TorrentSpeed::Download => 'downloadspeed',
			TorrentSpeed::Upload => 'uploadspeed');
	}

	public function addTorrentByFile($fileName, $downloadTo) {
		$fileName = str_replace('\\', '\\\\', $fileName);
		$downloadTo = str_replace('\\', '\\\\', $downloadTo);

		if(file_exists($fileName) && file_exists($downloadTo)) {
			$this->telnetClient->executeCommand($this->server, 'add -o "' . $downloadTo . '" "' . $fileName . '"');
			return 'Torrent added successfully.';
		}
		else
		return 'File does not exist.';
	}

	public function removeTorrent($torrentName) {
		$this->telnetClient->executeCommand($this->server, "show t \"$torrentName\"\r\nremove 1");
	}
	
	public function removeAllTorrents() {
		$this->telnetClient->executeCommand($this->server, "show t\r\nremove all");
	}

	public function startTorrent($torrentName) {
		$this->telnetClient->executeCommand($this->server, "show t \"$torrentName\"\r\nstart 1");
	}

	public function stopTorrent($torrentName) {
		$this->telnetClient->executeCommand($this->server, "show t \"$torrentName\"\r\nstop 1");
	}

	/*
		Gets the priority of the specified Torrent

		Returns TorrentPriority::Normal,
			TorrentPriority::High, or
			TorrentPriority::DoNotDownload, which
			can be found in the
			i_torrent_functions.php page

		$torrentName - the name of the Torrent
	*/
	public function getTorrentPriority($torrentName) {
		$azureusPriorities = array('>' => TorrentPriority::Normal,
			'+' => TorrentPriority::High,
			'!' => TorrentPriority::DoNotDownload);

		$commandResult = $this->telnetClient->executeCommand($this->server, "show t \"$torrentName\"\r\nshow 1");

		preg_match("/- Files Info -\r?\n\s+1\s\((?P<priority>[>+!])/", $commandResult, $matches);

		return $azureusPriorities[$matches['priority']];
	}

	/*
		Sets the priority of the specified Torrent

		$torrentName - the name of the Torrent

		$torrentPriorityEnum - The priority you want
			to specify (can be
			TorrentPriority::Normal,
			TorrentPriority::High, or
			TorrentPriority::DoNotDownload, which
			can be found in the
			i_torrent_functions.php page)
	*/
	public function setTorrentPriority($torrentName, $torrentPriorityEnum) {
		$this->telnetClient->executeCommand($this->server, "show t \"$torrentName\"\r\nprio 1 all " . $this->priorities[$torrentPriorityEnum]);
	}

	/*
		Gets the upload or download transfer rate
		limit	of the specified Torrent

		Returns the transfer rate limit in kilobytes
			per second

		$torrentName - the name of the Torrent

		$torrentSpeedEnum - TorrentSpeed::Download
			or TorrentSpeed::Upload (these
			constant variables can be found in the
			i_torrent_functions.php page)
	*/
	public function getTorrentSpeedLimit($torrentName, $torrentSpeedEnum) {
		$regex = array(TorrentSpeed::Download => "/\(max\s(?P<speed>\d+\.?\d*\s)(?P<unit>\w?B\/s)\)\s\//",
			TorrentSpeed::Upload => "/\(max\s(?P<speed>\d+\.?\d*\s)(?P<unit>\w?B\/s)\)\s+Amount/");

		$speeds = array('B/s' => 1/1024,
			'kB/s' => 1,
			'MB/s' => 1024);

		$commandResult = $this->telnetClient->executeCommand($this->server, "show t \"$torrentName\"");

		if(preg_match($regex[$torrentSpeedEnum], $commandResult, $matches))
			return round($matches['speed'] * $speeds[$matches['unit']]);
	}

	/*
		Sets the upload or download transfer rate
		limit	of the specified Torrent

		$torrentName - the name of the Torrent

		$torrentSpeedEnum - TorrentSpeed::Download
			or TorrentSpeed::Upload (these
			constant variables can be found in the
			i_torrent_functions.php page)

		$rate - transfer rate limit in kilobytes
			per second
	*/
	public function setTorrentSpeedLimit($torrentName, $torrentSpeedEnum, $rate) {
		//make the rate non-decimal
		$rate = round($rate);

		//use Azureus Console UI "hack" command
		$this->telnetClient->executeCommand($this->server, "show t \"$torrentName\"\r\nhack 1 " . $this->speedLimits[$torrentSpeedEnum] . " $rate");
	}
	
	public function getTorrentTracker($torrentName) {
		$commandResult = $this->telnetClient->executeCommand($this->server, "show t \"$torrentName\"\r\nshow 1");
				
		$trackerPattern = '/URL:\s(?P<trackerUrl>http:\/\/.*)\//';
		preg_match($trackerPattern, $commandResult, $outputArray);
		
		return $outputArray['trackerUrl'];
	}

	public function getTorrentList() {
		$commandResult = $this->telnetClient->executeCommand($this->server, 'show t');

		$torrentPattern = "/(?P<torrentNumber>\d)\s\[(?P<status>.*)\]\s(?P<percent>\d{3}\.\d)%\s+(?P<torrentName>[\w-_ \.\[\]\(\)'\+]+)\s\((?P<size>\d+\.?\d+\s\w?B)\)\sETA:\s(?P<timeLeft>(\s?\d*\w)*\??)\s+Speed:\s(?P<downSpeed>\d+\.?\d*\s\w?B\/s)(\((?P<maxDownSpeed>max\s\d+\.?\d*\s\w?B\/s)\))?\s\/\s(?P<upSpeed>\d+\.?\d*\s\w?B\/s)(\((?P<maxUpSpeed>max\s\d+\.?\d*\s\w?B\/s)\))?\sAmount:\s(?P<amountDownloaded>\d+\.?\d*\s\w?B)(?:\s\(\s(?P<amountDownloadedDiscarded>\d+\.?\d*\s\w?B)\sdiscarded\s\))?\s\/\s(?P<amountUploaded>\d+\.?\d*\s\w?B)\sConnections:\s((?P<connectedSeeds>\d+)\((?P<availableSeeds>[\d\?]+)\)\s\/\s(?P<connectedPeers>\d+)\((?P<availablePeers>[\d\?]+)\))/";
		preg_match_all($torrentPattern, $commandResult, $outputArray['torrents']);

		for($i = 0; $i < count($outputArray['torrents']['torrentNumber']); $i++) {
			$outputArray['torrents']['percent'][$i] = floor($outputArray['torrents']['percent'][$i]) . '%';
			$outputArray['torrents']['status'][$i] = $this->statuses[$outputArray['torrents']['status'][$i]];
		}

		$statsPattern = '/Total\sSpeed\s\(down\/up\):\s((?P<totalDownSpeed>\d+\.?\d*\s\w?B\/s)\s\/\s(?P<totalUpSpeed>\d+\.?\d*\s\w?B\/s))\s+Transferred\sVolume\s\(down\/up\/discarded\):\s((?P<transferredVolumeDown>\d+\.?\d*\s\w?B\s)\/\s(?P<transferredVolumeUp>\d+\.?\d*\s\w?B\s)\/\s\d+\.?\d*\s\w?B)\s+Total\sConnected\sPeers\s\(seeds\/peers\):\s((?P<totalConnectedSeeds>\d+)\s\/\s(?P<totalConnectedPeers>\d+))/';
		preg_match($statsPattern, $commandResult, $outputArray['globalStats']);

		return $outputArray;
	}
	
	public function readSetting($torrentSettingEnum) {
		$commandResult = $this->telnetClient->executeCommand($this->server, 'set "' . $this->settings[$torrentSettingEnum] . '"');

		$preferencePattern = '/' . $this->settings[$torrentSettingEnum] . ':\s(?P<value>.+)\s\[\w+\]|default:\s(?P<defaultValue>.+)\]/';
		preg_match($preferencePattern, $commandResult, $outputArray);

		if($outputArray['value'] == '')
		return $outputArray['defaultValue'];
		return $outputArray['value'];
	}
	
	public function writeSetting($torrentSettingEnum, $value) {
		//if the value is numeric, make it non-decimal
		if(is_numeric($value)) $value = round($value);

		$this->telnetClient->executeCommand($this->server, 'set "' . $this->settings[$torrentSettingEnum] . '" "' . $value . '"');
	}
	
	public function initialize() {
		$output = $this->telnetClient->startLocalServer();

		return $output;
	}
}
?>