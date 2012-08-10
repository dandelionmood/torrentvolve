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

class UpdateManager {

	private $latestRelease;
	private $currentVersion;
	private $latestVersion;
	private $rssError = FALSE;

	public function __construct() {

		$this->currentVersion = file_get_contents('../../VERSION');

		try {
			//timeout after a short period
			$timeout = 2;
			$oldTimeout = ini_set('default_socket_timeout', $timeout);

			//get RSS feed for TorrentVolve updates
			$rssFeed = @new SimpleXMLElement('http://sourceforge.net/export/rss2_projfiles.php?group_id=179905', NULL, TRUE);

			//back to the old timeout
			ini_set('default_socket_timeout', $oldTimeout);

			$this->latestRelease = $rssFeed->channel->item[0];
			preg_match('/torrentvolve ([\w\. ]+) released/', $this->latestRelease->title, $versionMatches);
			$this->latestVersion = $versionMatches[1];

		} catch(Exception $exc) {
			$this->rssError = TRUE;
			$this->latestVersion = 'SourceForge RSS feed unavailable';
		}
	}

	public function getCurrentVersion() {
		return $this->currentVersion;
	}

	public function getLatestVersion() {
		return $this->latestVersion;
	}

	public function getLatestDownloadUrl() {
		preg_match('/Includes files: ([\w\.\-]+) /', $this->latestRelease->description, $linkMatches);
		return 'http://prdownloads.sourceforge.net/torrentvolve/'
			. urlencode($linkMatches[1]) . '?download';
	}

	public function newerVersionExists() {
		return $this->latestVersion > $this->currentVersion && !$this->rssError;
	}
}

?>