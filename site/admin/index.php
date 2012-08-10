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
ob_start(); // buffer

// Add stylesheet for login.
$pageHead = '<link rel="stylesheet" type="text/css" href="../master/css/masterIndex.css" />';
$pageHead .= '<link rel="stylesheet" type="text/css" href="../master/css/adminIndex.css" />';
$basedir = '../';
?>
<div id="divHeader" class="header">
	<div id="divNav">
		<a href="../index.php">Home</a> | 
		<a href="users.php">Manage Users</a> |
		<a href="../logout.php">Logout</a>
	</div>
</div>
<div id="divCenter">
	<div id="divBoxOutline">
		<div id="divPadder">
			<div id="divLogo"><img src="../master/images/admin.gif"
				alt="Admin Logo" /></div>
<?php
session_start();

require_once('../lib/user.php');

//allow only admins
if(isset($_SESSION['user']) && user_getUserByUsername($_SESSION['user'])->getAuthLevel() == 'Admin') {

	require_once('../lib/update_manager.php');
	$updateManager = new UpdateManager();
?>
<div class="divSetting">
				<div class="divSettingLabel">Current Version:</div>
				<div class="divSettingText"><?php echo $updateManager->getCurrentVersion();?></div>
</div>
<div class="divSetting">
				<div class="divSettingLabel">Latest Version:</div>
				<div class="divSettingText"><?php echo $updateManager->getLatestVersion();?></div>
</div>
<?php if($updateManager->newerVersionExists()) {?>
<div class="divSetting">
				<div class="divSettingLabel">&nbsp;</div>
				<div class="divSettingText"><a href="<?php
						echo $updateManager->getLatestDownloadUrl();
					?>" target="_blank">Download the latest version of TorrentVolve</a></div>
</div>
<?php
}
	require_once('../lib/configuration.php');

//read settings from XML
	$xmlConfig = config_getConfiguration();

//write settings
	if(count($_POST)) {
	//write settings to XML
		$xmlConfig->setDownloadLocation($_POST['downloadLocation']);
		$xmlConfig->setTorrentModule($_POST['torrentModule']);
		$xmlConfig->setMaxDownloadSpeed($_POST['maxDownloadSpeed']);
		$xmlConfig->setMaxUploadSpeed($_POST['maxUploadSpeed']);
		$xmlConfig->setMaxDownloads($_POST['maxDownloads']);
		$xmlConfig->setMaxUploads($_POST['maxUploads']);
		$xmlConfig->setMaxActiveTorrents($_POST['maxActiveTorrents']);
		$xmlConfig->setTcpPort($_POST['tcpPort']);
		$xmlConfig->setUdpPort($_POST['udpPort']);
		$xmlConfig->setHideOtherUsers($_POST['hideOtherUsers']);
		config_setConfiguration($xmlConfig);

	//write settings to Torrent Module
		require_once('../lib/torrent_module_loader.php');
		$torrentModule = new TorrentFunctions('localhost');
		$torrentModule->writeSetting(TorrentSetting::MaxDownloadSpeed, $_POST['maxDownloadSpeed']);
		$torrentModule->writeSetting(TorrentSetting::MaxUploadSpeed, $_POST['maxUploadSpeed']);
		$torrentModule->writeSetting(TorrentSetting::MaxDownloads, $_POST['maxDownloads']);
		$torrentModule->writeSetting(TorrentSetting::MaxUploads, $_POST['maxUploads']);
		$torrentModule->writeSetting(TorrentSetting::MaxActiveTorrents, $_POST['maxActiveTorrents']);
		$torrentModule->writeSetting(TorrentSetting::TcpPort, $_POST['tcpPort']);
		$torrentModule->writeSetting(TorrentSetting::UdpPort, $_POST['udpPort']);
		?>
<div id="divStatus">Settings saved.</div>
<br />
		<?php
	} else print '<br />';
	?>
			<form action="index.php" method="post">
			<div class="divSetting">
				<div class="divSettingLabel">Download Location:</div>
				<div class="divSettingTextBig"><input type="text" name="downloadLocation" value="<?php echo str_replace('\\\\', '\\', $xmlConfig->getDownloadLocation())?>"/></div>
			</div>
			<div class="divSetting">
				<div class="divSettingLabel">Torrent Module:</div>
				<div class="divSettingText">
					<select name="torrentModule">
					<?php
						$modulePath = realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . '/../lib/torrentmodules';

						// Get all of the Torrent modules
						$dirs = scandir($modulePath);

						foreach($dirs as $dir) {
							if(is_dir("$modulePath/$dir") && !strstr($dir, '.')) {

								// Get current configured Torrent module						
								if($dir == $xmlConfig->getTorrentModule()) $selected = ' selected="selected"';
								else $selected = '';

								print "<option$selected>$dir</option>";
							}
						}
					?>
					</select>
				</div>
			</div>
			<div class="divSetting">
				<div class="divSettingLabel">Max. Download Speed (kB/sec):</div>
				<div class="divSettingText"><input type="text" name="maxDownloadSpeed" value="<?php echo $xmlConfig->getMaxDownloadSpeed();?>" /></div>
			</div>
			<div class="divSetting">
				<div class="divSettingLabel">Max. Upload Speed (kB/sec):</div>
				<div class="divSettingText"><input type="text" name="maxUploadSpeed" value="<?php echo $xmlConfig->getMaxUploadSpeed();?>" /></div>
			</div>
			<div class="divSetting">
				<div class="divSettingLabel">Max. # of Downloads:</div>
				<div class="divSettingText"><input type="text" name="maxDownloads" value="<?php echo $xmlConfig->getMaxDownloads();?>" /></div>
			</div>
			<div class="divSetting">
				<div class="divSettingLabel">Max. # of Uploads:</div>
				<div class="divSettingText"><input type="text" name="maxUploads" value="<?php echo $xmlConfig->getMaxUploads();?>" /></div>
			</div>
			<div class="divSetting">
				<div class="divSettingLabel">Max. # of Active Torrents:</div>
				<div class="divSettingText"><input type="text" name="maxActiveTorrents" value="<?php echo $xmlConfig->getMaxActiveTorrents();?>" /></div>
			</div>
			<div class="divSetting">
				<div class="divSettingLabel">TCP Listen Port:</div>
				<div class="divSettingText"><input type="text" name="tcpPort" value="<?php echo $xmlConfig->getTcpPort();?>" /></div>
			</div>
			<div class="divSetting">
				<div class="divSettingLabel">UDP Listen Port:</div>
				<div class="divSettingText"><input type="text" name="udpPort" value="<?php echo $xmlConfig->getUdpPort();?>"/></div>
			</div>
			<div class="divSetting">
				<div class="divSettingLabel">Hide basic users torrents to <br />each other ?</div>
				<div class="divSettingText">
					<select name="hideOtherUsers">
						<option <?php if( $xmlConfig->getHideOtherUsers() == 'yes' ): ?>selected="selected"<?php endif; ?>>yes</option>
						<option <?php if( $xmlConfig->getHideOtherUsers() == 'no' ): ?>selected="selected"<?php endif; ?>>no</option>
					</select>
				</div>
			</div>
			<div id="divSubmit"><input type="submit" value="Modify Settings" /></div>
			</form>
			<?php } else{?>
			<div id="divStatus">Access denied.<br /><a href="../index.php">back</a></div>
			<?php
}?>
		</div>
	</div>
</div>
					<?php
					$pagemaincontent = ob_get_contents();	// Assign all Page Specific Variables
					ob_end_clean();  $pageTitle = "TorrentVolve | Administration";  	//Apply the template
					require_once("../master.php");
?>