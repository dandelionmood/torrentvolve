<table width="100%">
	<tr style="border: 0;">
		<td style="border: 0; padding: 0;">
			<h1>TorrentVolve</h1>
			<h2>What is this ?</h2>
		</td>
		<td width="128px" style="border: 0; padding: 0;">
			<img src="https://github.com/dandelionmood/torrentvolve/raw/master/site/master/images/favicon.png"
				alt="The project logo" />
		</td>
	</tr>
</table>

This is a repository to store my little tweaks on the TorrentVolve project.

Quoting from the website :

> TorrentVolve is a cross-platform PHP-driven web-based BitTorrent client.
> It focuses on speed and reliability, while also providing a full feature set.
> It includes a user management system, a Torrent file manager, and a
> configurable Torrent downloader.

See here for more informations : http://sourceforge.net/projects/torrentvolve/ 

## Improvements ##

I'm using the version «1.4 Beta» which is the latest that was released
as a starting point to my developments.

I basically want to add a few features that I think this project is missing
and maybe try to improve its look and feel, if I find the time.

Here are the features I've added so far :

* an admin option to prevent "basic" users from seeing other users torrents.
* you can upload several .torrent files at once through the form.
* cUrl extension will be used as a fallback to get .torrent files from URL if fopen() is disabled (eg. in safe mode).
* an admin option to allow direct file download (instead of streaming them).
  This is a bit less secure (as the user can potentially download files from
	other users if they know the URL) but it's also more convenient to use.