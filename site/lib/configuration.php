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
	
	class Configuration {
		var $downloadLocation;
		var $torrentModule;
		var $maxDownloadSpeed;
		var $maxUploadSpeed;
		var $maxDownloads;
		var $maxUploads;
		var $maxActiveTorrents;
		var $tcpPort;
		var $udpPort;
		var $hideOtherUsers;
		
		public function __construct($downloadLocation, $torrentModule,
			$maxDownloadSpeed, $maxUploadSpeed, $maxDownloads, $maxUploads,
			$maxActiveTorrents, $tcpPort, $udpPort, $hideOtherUsers){
			$this->downloadLocation = $downloadLocation;
			$this->torrentModule = $torrentModule;
			$this->maxDownloadSpeed = $maxDownloadSpeed;
			$this->maxUploadSpeed = $maxUploadSpeed;
			$this->maxDownloads = $maxDownloads;
			$this->maxUploads = $maxUploads;
			$this->maxActiveTorrents = $maxActiveTorrents;
			$this->tcpPort = $tcpPort;
			$this->udpPort = $udpPort;
			$this->hideOtherUsers = $hideOtherUsers;
		}
		
		public function getDownloadLocation(){ return $this->downloadLocation; }
		public function getTorrentModule(){ return $this->torrentModule; }
		public function getMaxDownloadSpeed() { return $this->maxDownloadSpeed; }
		public function getMaxDownloads() { return $this->maxDownloads; }
		public function getMaxUploadSpeed() { return $this->maxUploadSpeed; }
		public function getMaxUploads() { return $this->maxUploads; }
		public function getMaxActiveTorrents() { return $this->maxActiveTorrents; }
		public function getTcpPort() { return $this->tcpPort; }
		public function getUdpPort() { return $this->udpPort; }
		public function getHideOtherUsers() { return $this->hideOtherUsers; }
		
		public function setDownloadLocation($downloadLocation) { 
			$this->downloadLocation = $downloadLocation; 
		}
		public function setTorrentModule($torrentModule){
			$this->torrentModule = $torrentModule;
		}
		public function setMaxDownloadSpeed($maxDownloadSpeed){
			$this->maxDownloadSpeed = $maxDownloadSpeed;
		}
		public function setMaxUploadSpeed($maxUploadSpeed){
			$this->maxUploadSpeed = $maxUploadSpeed;
		}
		public function setMaxDownloads($maxDownloads){
			$this->maxDownloads = $maxDownloads;
		}
		public function setMaxUploads($maxUploads){
			$this->maxUploads = $maxUploads;
		}
		public function setMaxActiveTorrents($maxActiveTorrents) {
			$this->maxActiveTorrents = $maxActiveTorrents;
		}
		public function setTcpPort($tcpPort){
			$this->tcpPort = $tcpPort;
		}
		public function setUdpPort($udpPort) {
			$this->udpPort = $udpPort;
		}
		public function setHideOtherUsers($hideOtherUsers) {
			$this->hideOtherUsers = $hideOtherUsers;
		}
	}
	
	/**
	 * Gets the configuration from the XML
	 * text file.
	 *
	 * @return Configuration object containing
	 * the configuration obtained from the XML
	 * file.
	 */
	function config_getConfiguration(){
		$file = file_get_contents(APPPATH . "../../config/configuration.xml");
		$xml = new SimpleXMLElement($file);
		$config = new Configuration(
			$xml->downloadLocation,
			$xml->torrentModule,
			$xml->maxDownloadSpeed,
			$xml->maxUploadSpeed, 
			$xml->maxDownloads, 
			$xml->maxUploads,
			$xml->maxActiveTorrents, 
			$xml->tcpPort, 
			$xml->udpPort,
			$xml->hideOtherUsers);
		return $config;
	}
	
	/**
	 * Write a configuration object to
	 * the configuration XML file.
	 *
	 * @param Configuration $config object
	 * to write to the XML file.
	 * @return boolean Whether or not the
	 * configuration was installed and configured.
	 */
	function config_setConfiguration($config) {
		$xml = new SimpleXMLElement(genXML());
		$xml->downloadLocation = (string)$config->getDownloadLocation();
		$xml->torrentModule = (string)$config->getTorrentModule();
		$xml->maxDownloadSpeed = (string)$config->getMaxDownloadSpeed();
		$xml->maxUploadSpeed = (string)$config->getMaxUploadSpeed();
		$xml->maxDownloads = (string)$config->getMaxDownloads();
		$xml->maxUploads = (string)$config->getMaxUploads();
		$xml->maxActiveTorrents = (string)$config->getMaxActiveTorrents();
		$xml->tcpPort = (string)$config->getTcpPort();
		$xml->udpPort = (string)$config->getUdpPort();
		$xml->hideOtherUsers = (string)$config->getHideOtherUsers();
		if(@file_put_contents(APPPATH . "../../config/configuration.xml", $xml->asXML())) return true;
		else return false;
	}
	
	/**
	 * Generates a default XML configuration
	 * string to be used by the configuration
	 * writer.
	 *
	 * @return string XML of default configuration.
	 */
	function genXML(){
		$xmlString = 
'<?xml version="1.0" encoding="UTF-8"?>
<configuration>
	<downloadLocation>null</downloadLocation>
	<torrentModule>tvAzureusBridge</torrentModule>
	<maxDownloadSpeed>null</maxDownloadSpeed>
	<maxUploadSpeed>null</maxUploadSpeed>
	<maxDownloads>null</maxDownloads>
	<maxUploads>null</maxUploads>
	<maxActiveTorrents>null</maxActiveTorrents>
	<tcpPort>null</tcpPort>
	<udpPort>null</udpPort>
	<hideOtherUsers>no</hideOtherUsers>
</configuration>';
		return $xmlString;
	}
?>