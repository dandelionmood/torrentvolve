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

class TvTelnetClient {

	private $appPath;

	public function __construct() {
		$f = __FILE__;
		if (is_link($f)) $f = readlink($f); // Unix compatibility
		$this->appPath = dirname($f) . "/";
	}

	public function executeCommand($server, $command) {
		$fp = @fsockopen($server, 57006, $errno, $errstr, 5);
		if (!$fp) {

			set_time_limit(60);

			$this->startLocalServer();

			while(!$fp) {
				sleep(5);

				$fp = @fsockopen($server, 57006, $errno, $errstr, 5);
			}

			require_once($this->appPath . '../../../load_settings.php');
		}

		$count = 0;

		while($count < 2) {
			$data = fgets($fp);

			if(strstr($data, "> -----")) {
				$count++;
			}
		}

		fwrite($fp, $command . "\r\n");
		fwrite($fp, "logout\r\n");

		$output = "";

		while(!feof($fp)) {
			$data = fgets($fp);
			if($data != "")
			$output .= $data;
		}
			
		fclose($fp);

		return $output;
	}
	
	public function startLocalServer() {

		$path = $this->appPath . '../azureusServer';
		$command = 'java -Dazureus.config.path="' . $path . '" -jar "' . $path . '/Azureus2.jar" --ui=telnet';

		if (strtoupper(substr(PHP_OS, 0, 3) == 'WIN')) popen('start /D"' . $path . '" ' . $command, 'w');
		else exec("$command > /dev/null 2>&1 &");
	}
}
?>