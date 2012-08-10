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

require_once('lib/torrent_module_loader.php');
require_once('lib/torrent.php');

session_start();

//immediately redirect if nobody is logged in
if(empty($_SESSION['user'])) include("master.php");

//set autoRefresh session variable if it is unset
if(empty($_SESSION['autoRefresh']))
		$_SESSION['autoRefresh'] = "on";

require_once('lib/user.php');

//user info
$userName = $_SESSION['user'];
$userAuthLevel = user_getUserByUsername($userName)->getAuthLevel();

$torrentManagerModule = new TorrentFunctions('localhost');

//process get operations
if(count($_GET) > 0) {
	
	//assume the torrent name resides in the last GET variable
	foreach($_GET as $getVar) $torrentName = $getVar;

	// Handle auto refreshing
	if($_GET['autoRefresh'] == "off")
		$_SESSION['autoRefresh'] = "off";
	if($_GET['autoRefresh'] == "on")
		$_SESSION['autoRefresh'] = "on";

	// Handle torrent information
	if($userAuthLevel == 'Admin' || $userAuthLevel == 'Power User' || $userName == torrent_getTorrentByName($torrentName)->getUserName()) {
		if(isset($_GET['start'])) {
			$torrentManagerModule->startTorrent($torrentName);
		}

		if(isset($_GET['stop'])) {
			$torrentManagerModule->stopTorrent($torrentName);
		}

		if(isset($_GET['remove'])) {
			$torrentManagerModule->removeTorrent($torrentName);
			torrent_removeTorrent($torrentName);
		}
	}
	
	header('Location: index.php');
}
// Add stylesheet for login.
$pageHead = "<link rel=\"stylesheet\" type=\"text/css\" href=\"master/css/index.css\" />";

// Add auto page refreshing turn off
if($_SESSION['autoRefresh'] != "off")
	$pageHead .= "\n\t<meta http-equiv=\"refresh\" content=\"10\" />";
	
ob_start(); // buffer
?>

<!-- Index specific -->
<div id="divMessageHeader" class="header">
<div id="divUploadTop"><a href="load.php?sid=<?php echo session_id();?>" onclick="window.open(this.href, '', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=320,height=260');return false;" target="_blank" title="Load a new Torrent">Load
Torrent</a> | <a href="archive.php?sid=<?php echo session_id();?>" onclick="window.open(this.href, '', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=0,width=700,height=550');return false;" target="_blank" title="View your archive">
My Archive</a> | <a href="user/index.php" title="View and modify your user settings">My Settings</a><?php
		//is this user an admin?
if($userAuthLevel == 'Admin')
echo ' | <a href="admin/index.php" title="View and modify global settings">Admin</a>';
?> | <a
	href="logout.php" title="Logout from TorrentVolve">Logout</a></div>
</div>
<!-- The torrents download information -->
<div id="divTorrentHeader" class="header">
<div id="divTorrentLogo" class="header"><img
	src="master/images/bannerTorrents.gif" alt="Torrents" /></div>
</div>
<!-- Current downloads -->
<div id="divCurrentTorrents">
<?php
//get list of torrents
$torrentArray = $torrentManagerModule->getTorrentList();

//no torrents in list
if(count($torrentArray['torrents']['torrentNumber']) == 0) {
	?>
	<div id="divStatus">There are no torrents loaded.</div>
<?php
} else {
	$torrentUsers = torrent_getTorrentsHash();

	//occurs if the torrents XML file has been cleared manually
	if(empty($torrentUsers)) {
		$torrentManagerModule->removeAllTorrents();

		header('Location: index.php');
	}
	?>
<table id="tblTorrents">
	<tr id="rowHeadings">
		<td class="columnName">Name</td>
		<td class="columnStatus">Status</td>
		<td class="columnUser">User</td>
		<td class="columnAction">Actions</td>
	</tr>
	<tr>
		<td colspan="4"><img src="master/images/single.gif"
			width="100%" height="1px" alt="Separator" /></td>
	</tr>
	<?php
	//mapping for status-based images
	$startImage = array('start', 'startTorrent.gif', 'Start Torrent');
	$stopImage = array('stop', 'stopTorrent.gif', 'Pause/Stop Torrent');
	$statusImages = array('Downloading' => $stopImage, 'Seeding' => $stopImage,
			'Paused' => $startImage, 'Queued' => $startImage,
			'Error' => $startImage, 'Checking' => $stopImage);

	for($i=0; $i < count($torrentArray['torrents']['torrentNumber']); $i++) {

		$torrentName = $torrentArray['torrents']['torrentName'][$i];
		if(strlen($torrentName) > 32) $shortTorrentName = substr($torrentName, 0, 29) . '...';
		else $shortTorrentName = $torrentName;

		if(isset($torrentUsers[$torrentName])) $torrentUser = $torrentUsers[$torrentName]->getUserName();
		else $torrentUser = 'Unknown';
		
		// If the user is a basic one, we check that the other users aren't
		// automatically hidden via the configuration.
		if( $userAuthLevel == 'User' ) {
			require_once('lib/configuration.php');
			$hideOtherUsers = ( config_getConfiguration()->getHideOtherUsers() == 'yes' );
			if( $userName != $torrentUser && $hideOtherUsers ) continue;
		}
		
		$seedText = "Seeds: (" . $torrentArray['torrents']['connectedSeeds'][$i] .
					" / " . $torrentArray['torrents']['availableSeeds'][$i] . ")";
		$peerText = "Peers: (" . $torrentArray['torrents']['connectedPeers'][$i] .
					" / " . $torrentArray['torrents']['availablePeers'][$i] . ")";
	?>
	<tr class="rowTorrent">
		<td class="columnName" title="<?php echo $torrentName;?>"><?php
		if($torrentArray['torrents']['percent'][$i] == 100)
		echo('<a href="download.php?user=' . $torrentUser . '&amp;file=' . urlencode($torrentName) . '" title="Download this finished Torrent">' . $shortTorrentName . '</a>');
		else echo($shortTorrentName);
		?></td>
		<td class="columnStatus">
		<table class="tblTorrentStatus">
			<tr class="rowTorrentStatusTop">
				<td class="rowTorrentStatusPercent" title="ETA"><?php echo($torrentArray['torrents']['timeLeft'][$i]);?></td>
				<td class="rowTorrentStatusBar" colspan="4">
				<div style="float: left;"><?php echo($torrentArray['torrents']['percent'][$i]); ?>:&nbsp;</div><div class="divPercent" style="height:12px; float: left;"><img width="<?php echo($torrentArray['torrents']['percent'][$i]); ?>" height="12px"
				src="master/images/torrentPercent.gif" alt="Percent" /></div>
				</td>
			</tr>
			<tr class="rowTorrentStatusBottom">
				<td class="rowTorrentStatusPercent" title="Status"><?php echo($torrentArray['torrents']['status'][$i]);?></td>
				<td title="<?php echo $seedText; ?>">Dn: <?php echo($torrentArray['torrents']['amountDownloaded'][$i]);?></td>
				<td title="<?php echo $torrentArray['torrents']['maxDownSpeed'][$i]; ?>">(<?php echo($torrentArray['torrents']['downSpeed'][$i]);?>)</td>
				<td title="<?php echo $peerText; ?>">Up: <?php echo($torrentArray['torrents']['amountUploaded'][$i]);?></td>
				<td title="<?php echo $torrentArray['torrents']['maxUpSpeed'][$i]; ?>">(<?php echo($torrentArray['torrents']['upSpeed'][$i]);?>)</td>
			</tr>
		</table>
		</td>
		<td class="columnUser"><?php
		if($userAuthLevel == 'Admin' || $userAuthLevel == 'Power User') {
			?><a href="archive.php?sid=<?php echo session_id();?>&amp;user=<?php echo($torrentUser);?>" onclick="window.open(this.href, '', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=0,width=700,height=550');return false;" target="_blank" title="View this user's archive">
				<?php echo($torrentUser);?>
			</a>
		<?php } else {?><?php echo($torrentUser);?><?php }?></td>
		<td class="columnAction"><?php
		if($userAuthLevel == 'Admin' || $userAuthLevel == 'Power User' || $userName == $torrentUser) {
			if(isset($statusImages[$torrentArray['torrents']['status'][$i]])) {?><a href="index.php?<?php echo($statusImages[$torrentArray['torrents']['status'][$i]][0] . '=' . urlencode($torrentName));?>" title="<?php echo($statusImages[$torrentArray['torrents']['status'][$i]][2]);?>">
			<img src="master/images/<?php echo($statusImages[$torrentArray['torrents']['status'][$i]][1]);?>" alt="<?php echo($statusImages[$torrentArray['torrents']['status'][$i]][2]);?>" /></a>
		<?php }?>
		<a href="torrentSettings.php?sid=<?php echo session_id();?>&amp;name=<?php echo urlencode($torrentName);?>" onclick="window.open(this.href, '', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=420,height=285');return false;" target="_blank" title="Configure Torrent">
			<img src="master/images/configureTorrent.gif" alt="Configure Torrent" /></a>
		<a href="index.php?remove=<?php echo(urlencode($torrentName));?>" title="Remove Torrent">
			<img src="master/images/removeTorrent.gif" alt="Remove Torrent" /></a><?php
		}
?></td>
	</tr>
	<tr>
		<td colspan="4"><img src="master/images/single.gif"
			width="100%" height="1px" alt="Separator" /></td>
	</tr>
	<?php
	}
}
	if(count($torrentArray['torrents']['torrentNumber']) > 0) {
?>	<tr>
		<td colspan="4">
		<table style="width: 100%">
		<tr class="rowTorrentStatusBottom">
			<td>Total Download Speed: <?php echo $torrentArray['globalStats']['totalDownSpeed']; ?></td>
			<td colspan="4">Total Upload Speed: <?php echo $torrentArray['globalStats']['totalUpSpeed']; ?></td>
			<td colspan="4">Total Connected Seeds: <?php echo $torrentArray['globalStats']['totalConnectedSeeds']; ?></td>
			<td colspan="4">Total Connected Peers: <?php echo $torrentArray['globalStats']['totalConnectedPeers']; ?></td>
		</tr>
		</table>
		</td>
	</tr>
</table>
<?php 
	}
?>
</div>
<!-- The auto downloading information 
<div id="divAutoDownloader" class="header">
	<div id="divAutoDownloadLogo" class="header">
		<img src="master/images/bannerAutoDownload.gif" alt="Torrents" />
	</div>
	<div id="divAutoLeftSide" class="header">&nbsp;</div>
</div> -->
<!-- Drive Information -->
<?php
require_once('lib/configuration.php');
require_once('lib/driveSpace.php');
$path = config_getConfiguration()->getDownloadLocation();
$freeSpace = GetFreeSpace($path);
$totalSpace = GetTotalSpace($path);
$percent = GetUsedSpace($freeSpace, $totalSpace);
?>
	<div id="divDriveInformation">
		<div id="divDriveInformationLogo"><img
			src="master/images/driveInformation.gif" alt="DriveInformation" /></div>
		<div class="center">
		<div id="divDriveInformationStatus"><?php print "<img src=\"master/images/driveStatus.gif\" width=\"$percent%\" height=\"16px\" alt=\"Drive Free Space\" />"; ?></div>
		<div id="divDriveInformationText"><?php echo round($freeSpace/1024/1024/1024, 2)." GB / " . round($totalSpace/1024/1024/1024, 2) . " GB free (".$percent."% used)" ?></div>
	</div>
	<div id="divAutoRefresh" class="center"><br />
	<?php
		if($_SESSION['autoRefresh'] == "off")
			print "Click <a href=\"index.php?autoRefresh=on\">here</a> to enable automatic refreshing.";
		else
			print "Click <a href=\"index.php?autoRefresh=off\">here</a> to disable automatic refreshing.";
	?></div>
</div>

<?php
$pagemaincontent = ob_get_contents();	// Assign all Page Specific Variables
ob_end_clean();  $pageTitle = "TorrentVolve | View Torrents";  	//Apply the template
include("master.php");
?>