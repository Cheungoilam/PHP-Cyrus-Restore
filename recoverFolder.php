<?php
require_once( 'config.php' );
require_once('function.php');

$l = count($_POST['folder']);
$server=$_POST['mailhost'];
if ($l==0) exit('<p>No folder selected.</p>');
$client_ip = $_SERVER['REMOTE_ADDR'];
openlog($process, LOG_PID, $fac);
$username=username();

if (is_null($username))
	exit('<pre id="content">You must setup your webserver to expose authentication.</pre>');

$imapRef = '{'.$server.':143}';
$mbox = imap_open($imapRef,$imapadmin, $imappwd, OP_READONLY)
        or exit('<p>'.htmlspecialchars("ERROR connecting to <$server>: " . imap_last_error()).'</p>');


$oldfolder = NULL;
for ($i=0;$i<$l;$i++) {
	$content = unserialize(base64_decode($_POST['folder'][$i]));
	$folder = $content['name'];
	$tm = $content['deldate'];
	$deldate = date('c',hexdec($tm));
	
        # The complete candidate folder name where to recover, ie 'user/name/Drafts@example.com'
        $recoverFolder= str_replace("/$tm", NULL, $content['path']);
        $recoverFolder= str_replace('DELETED/', NULL, $recoverFolder);
	if (!is_null($trashFolder))
		$recoverFolder= str_replace("/$trashFolder/", '/', $recoverFolder);

        # Determine if this folder was deleted many times. If yes, add the del date to the folder name.
        if ($folder == $oldfolder)
                $recoverFolder= str_replace('@', '-'.mb_convert_encoding( $deldate.'@', "UTF7-IMAP", "UTF-8"), $recoverFolder);
	$oldfolder = $folder;

        # If folder already exist add the del date to the folder name.
        # If the new folder already exists, add another del date to folder name and so on.
        $recoverFolder = folderPathRecover ($mbox, $imapRef, $recoverFolder, $tm);
	print "<p>Recovering ${content['path']} => $recoverFolder ... ";

	if ( $result = imap_renamemailbox ( $mbox , $imapRef.$content['path'] , $imapRef.$recoverFolder ) ) {
		print 'OK</p>';
		$status = 'success';
		$detail = 'Mailbox successfully recovered';
	}
	else {
		print 'ERROR</p>';
		$status = 'fail';
		$detail = imap_last_error();
	}

	$folderhum = mb_convert_encoding( $folder, "UTF-8", "UTF7-IMAP");
	$oldpathhum = mb_convert_encoding( $content['path'], "UTF-8", "UTF7-IMAP");
	$recoverFolderhum = mb_convert_encoding( $recoverFolder, "UTF-8", "UTF7-IMAP");
	syslog(LOG_INFO,"action=cyr_rename status=$status client_ip=$client_ip user=$username mailbox=${content['mailbox']} recoverfolder=\"$folderhum\" oldpath=\"$oldpathhum\" newpath=\"$recoverFolderhum\" deltime=\"$deldate\" detail=\"$detail\"");
}

?>
