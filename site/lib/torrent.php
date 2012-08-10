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
	if(!defined("APPPATH")) define("APPPATH", "$appPath");
	
	class Torrent{
		var $userName;
		var $torrentName;
		var $tracker;
		
		public function __construct($userName, $torrentName, $tracker) {
			$this->userName = $userName;
			$this->torrentName = $torrentName;
			$this->tracker = $tracker;
		}
		
		public function getUserName() { return $this->userName; }
		public function getTorrentName() { return $this->torrentName; }
		public function getTracker() { return $this->tracker; }
		public function setUserName($userName) {
			$this->userName = $userName;
		}
		public function setTorrentName($torrentName) {
			$this->torrentName = $torrentName;
		}
		public function setTracker($tracker) {
			$this->tracker = $tracker;
		}
	}
	
	/**
	 * Creates a new user in the xml file or a
	 * new users XML file with that user in it
	 * if one doesn't exist
	 *
	 * @param User $user The user object to add
	 * to the XML.
	 * @return boolean whether or not the user
	 * was created.
	 */
	function torrent_addTorrent($torrent) {
		// Make sure the torrent object is complete
		$userName = $torrent->getUserName();
		$torrentName = $torrent->getTorrentName();
		$tracker = $torrent->getTracker();
		if(empty($userName) ||
			empty($torrentName) ||
			empty($tracker)) {
			return false;	
		}
		// Get the xml
		if(file_exists(APPPATH . 
			'../../config/torrents.xml')){
			$file = file_get_contents(APPPATH . 
				'../../config/torrents.xml');
			$xml = new SimpleXMLElement($file);
			// Make sure the torrent doesn't exist
			$torrentInfo = $xml->xpath('/torrents/torrent[torrentName="' . $torrentName . '"]');
			if(!empty($torrentInfo)){
				return false;
			}
		} else {
			$xml = new SimpleXMLElement(torrent_genXML());
		}
		// write the torrent to the xml file
		$torrentXML = $xml->addChild('torrent');
		$torrentXML->addChild('userName', "$userName");
		$torrentXML->addChild('torrentName', "$torrentName");
		$torrentXML->addChild('tracker', "$tracker");
		try {
			file_put_contents(APPPATH . "../../config/torrents.xml", $xml->asXML());
		} catch (Exception $e){
			return false;
		}
		return true;
	}
	
	function torrent_getTorrents() {
		$file = file_get_contents(APPPATH . '../../config/torrents.xml');
		$xml = new SimpleXMLElement($file);
		$torrentInfo = $xml->xpath("/torrents/torrent");
		$torrents = array();
		foreach($torrentInfo as $torrent) {
			$torrents[] = new Torrent(
				$torrent->userName, $torrent->torrentName,
				$torrent->tracker);
		}
		return $torrents;
	}
	
	function torrent_getTorrentsHash() {
		$file = file_get_contents(APPPATH . '../../config/torrents.xml');
		$xml = new SimpleXMLElement($file);
		$torrentInfo = $xml->xpath("/torrents/torrent");
		$torrentHash = array();
		foreach($torrentInfo as $torrent) {
			$torrentHash["$torrent->torrentName"] = new Torrent(
				$torrent->userName, $torrent->torrentName,
				$torrent->tracker);
		}
		return $torrentHash;
	}
	
	function torrent_getTorrentByName($torrentName) {
		$file = file_get_contents(APPPATH . '../../config/torrents.xml');
		$xml = new SimpleXMLElement($file);
		$torrentInfo = $xml->xpath('/torrents/torrent[torrentName="' . $torrentName . '"]');
		$torrent = new Torrent($torrentInfo[0]->userName,
			$torrentInfo[0]->torrentName,
			$torrentInfo[0]->tracker);
		return $torrent;
	}
	
	function torrent_removeTorrent($torrentName) {
		$file = file_get_contents(APPPATH . '../../config/torrents.xml');
		$xml = new SimpleXMLElement($file);
		// Make sure the user doesn't exist
		$torrentInfo = $xml->xpath('/torrents/torrent[not(torrentName="' . $torrentName . '")]');
		$final = '<?xml version="1.0" encoding="UTF-8"?>
<torrents>';
 		foreach($torrentInfo as $torrent) {
 			$final .= $torrent->asXML();
 		}
		$final .= '</torrents>';
		try {
			file_put_contents(APPPATH . "../../config/torrents.xml", $final);
		} catch (Exception $e) {
			return false;
		}
		return true;
	}
	
	function torrent_createFile() {
		if(@file_put_contents(APPPATH . "../../config/torrents.xml", torrent_genXML())) return true;
		else return false;
	}
	
	/**
	 * Generates base XML to be used.
	 */
	function torrent_genXML(){
		$xmlString = 
'<?xml version="1.0" encoding="UTF-8"?>
<torrents>
</torrents>';
		return $xmlString;
	}

	function torrent_getTorrentNameFromFileName($torrentFile, $torrentFileName) {

		if(file_exists($torrentFile)) {
			$torrentLine = fgets(fopen($torrentFile, 'r'));

			//find the number of characters that make up the Torrent name
			preg_match('/name(?P<charCount>\d+):/', $torrentLine, $match);

			//find the Torrent name
			preg_match('/name\d+:(?P<torrentName>.{' . $match['charCount'] . '})/', $torrentLine, $match);


			if(!empty($match['torrentName'])) return $match['torrentName'];
			else return str_replace('.torrent', '', $torrentFileName);
		} else return 'File does not exist.';
	}

	function torrent_getTorrentNameFromFileData($torrentLine) {

		//find the number of characters that make up the Torrent name
		preg_match('/name(?P<charCount>\d+):/', $torrentLine, $match);

		//find the Torrent name
		preg_match('/name\d+:(?P<torrentName>.{' . $match['charCount'] . '})/', $torrentLine, $match);
			
		return $match['torrentName'];
	}
?>