<?php
require_once( 'config.php' );
require_once('function.php');

$l = count($_POST['folder']);
$server=$_POST['mailhost'];
if (isset($_POST['demo'])) {
	if ($_POST['demo'])
		$demo=TRUE;
}
else
	$demo=FALSE;
if ($l==0) exit('<p>No folder selected.</p>');
$client_ip = $_SERVER['REMOTE_ADDR'];
openlog($process, LOG_PID, $fac);
$username=username();
if (is_null($username))
	exit('<pre id="content">You must setup your webserver to expose authentication.</pre>');

print '<div id="containerblock">';
$count = 0;
foreach ($_POST['folder'] as $key => $item) {
        $content = unserialize(base64_decode($item));
        $folder = $content['name'];
	$mailbox = $content['mailbox'];
	$path = $content['path'];
	$id = $content['key'];
	$range = $_POST['range'][$id];

	if ($demo)
		$unexpunge = sprintf('/usr/bin/ssh root@%s "%s/unexpunge -l %s"',
			escapeshellcmd($server),
			$cyrpath,
			escapeshellarg($path)
		);
	else
		$unexpunge = sprintf('/usr/bin/ssh root@%s "%s/unexpunge -t %dd -d -v %s"',
			escapeshellcmd($server),
			$cyrpath,
			escapeshellcmd($range),
			escapeshellarg($path)
		);

	$result=array();
	$match=array();
	$match['tot'] = 0;
	$command = exec($unexpunge, $result, $ko);

	/* Example of non demo result
	restoring expunged messages in mailbox 'uc/csi/it!user/baraccone/Drafts'
	restored 0 expunged messages

	restoring expunged messages in mailbox 'uc/csi/it!user/baraccone/Drafts'
	Unexpunged uc/csi/it!user/baraccone/Drafts: 25 => 26
	restored 1 expunged messages */

	$folderhum = mb_convert_encoding( $folder, "UTF-8", "UTF7-IMAP");
	if ($demo) {
		$mode='demo';
		if (!$ko) {
			$status='success';
			if (empty($result))
				printf ('<table class="block"><tr><th>%s</th></tr><tr><td>No restorable mails in folder %s</td></tr></table>', htmlspecialchars($folderhum), htmlspecialchars($folderhum));
			foreach ($result as $row) {
				if (preg_match('/^UID:\s+(?P<uid>\d+)/', $row, $match))
					printf ("<table class=\"block\"><tr><th colspan=\"2\">%s UID: %d</th></tr>",
						htmlspecialchars($folderhum),
						$match['uid']
					);
				else {
					if (preg_match('/(?P<name>[^\:]+)\:\s(?P<value>[^$]+)/',$row, $match))
						print "<tr><td>${match['name']}</td><td>${match['value']}</td></tr>";
				}
				if (preg_match('/^Subj\:/',$row)) {
					print '</table>';
					# ensure exit to escape some bug on unexpunge
					continue;
				}
			}
		}
		else {
			print "<p>Something was wrong executing unexpunge command. </p><hr>";
			$status= 'fail';
		}
		$match['tot'] = 0;
	}
	else {
		$mode = 'real';
		if (!$ko) {
			$status = 'success';
                        if (count($result)==2) {
                                printf ('<p> No restorable mails in folder %s</p><hr>', htmlspecialchars($folderhum));
			        syslog(LOG_INFO, sprintf('action=cyr_unexpunge status=%s mode=%s client_ip=%s user=%s mailbox=%s folder="%s" delay=%d nmsg=%d',
                			$status,
                			$mode,
                			$client_ip,
                			$username,
                			$mailbox,
                			$folderhum,
                			$range,
                			$match['tot'] )
        			);
				continue;
			}
                        foreach ($result as $row) {
				if (preg_match('/^restoring/',$row))
					print "<table class=\"block\"><thead><tr><th colspan=\"2\">$folderhum ($range days)</th></tr><tr><th>Old uid</th><th>New uid</th></tr></thead><tbody>";
                                if (preg_match('/^Unexpunged[^\:]+\:\s(?P<olduid>\d+)\s\=\>\s(?P<newuid>\d+)/',$row, $match))
					print "<tr><td>${match['olduid']}</td><td>${match['newuid']}</td></tr>";
				if (preg_match('/^restored\s(?P<tot>\d+)/' ,$row, $match)) {
					print "</tbody><tfoot><tr><td colspan=\"2\">Restored ${match['tot']} msg</td></tr></tfoot></table>";
					$count += $match['tot'];
				}
			}
		}
                else {
                        print "<p>Something was wrong executing unexpunge command. </p><hr>";
                        $status= 'fail';
		}
	}

	syslog(LOG_INFO, sprintf('action=cyr_unexpunge status=%s mode=%s client_ip=%s user=%s mailbox=%s folder="%s" delay=%d nmsg=%d',
		$status,
		$mode,
		$client_ip,
		$username,
		$mailbox,
		$folderhum,
		$range,
		$match['tot'] )
	);
}

print '</div>';
if (!($ko OR $demo))
	print "<p>Total restored messages: $count</p>";
?>
