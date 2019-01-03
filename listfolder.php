<?php

$mailbox="$uid@$dom";
$mboxname = '{'.$mailhost.':143/imap/novalidate-cert/authuser='.$imapadmin.'}';
$mbox = imap_open($mboxname, $mailbox, $imappwd, OP_READONLY)
        or exit('<p>'.htmlspecialchars("ERROR connecting to <$mailhost> with user <$mailbox>: " . imap_last_error()).'</p>');
$folders = list_fold($mbox,$mboxname,$uid,$dom);
if ( $folders === FALSE ) exit ('<p>'.htmlspecialchars('Unexpected result. Something was wrong when listing folders.').'</p>');
if (!is_null($ignoreFolders))
        $folders = array_values(preg_grep("/($ignoreFolders)/i",$folders,PREG_GREP_INVERT));

$l = count($folders);
if ($l == 0) exit ('<p>'.htmlspecialchars('Unexpected result. No restorable folders found.').'</p>');
print <<<END
<form method="POST" accept-charset="UTF-8" name="ListFolder" action="recoverMail.php" onSubmit="xmlhttpPost('recoverMail.php', 'ListFolder', 'Recover', '<img src=\'/include/pleasewait.gif\'>'); return false;">
<input type="hidden" name="mailhost" value="$mailhost" />
<table id="tblData">
<tr>	<th>
      		<input type="checkbox" id="chkParent" />
    	</th>
	<th text="Range of days to recover from now">Range</th><th>Folder</th></tr>
	<tr><td><input type="radio" unchecked name="demo" id="demo" value="1" /></td><td class="shadow" colspan="2">Demo</td></tr>
END;

if (is_array($folders)) {
	
        foreach ( $folders as $key => $folder ) {
		# Construct the path for unexpunge command
		if ($folder == 'INBOX')
			$path = "user/$mailbox";
		else
			$path = "user/$uid/$folder@$dom";
		# Variables passed to unexpunge page
	        $post = array(  'path' => $path,
                        'name' => $folder,
                        'mailbox' => $mailbox,
			'key' => $key
                );

                printf('<tr><td><input type="checkbox" name="folder[]" value="%s" /><td><input type="number" disabled="disabled" value="%d" min="1" max="%d" name="range[]" /></td></td><td>%s</td></tr>',
                        base64_encode(serialize($post)),
			$maxday,
			$maxday,
                        htmlspecialchars(mb_convert_encoding($folder, "UTF-8", "UTF7-IMAP"))
		);
	}
}

imap_close($mbox);
print '<tr><td colspan="3"><input type="submit" value="Engage" name="Engage" class="btn"></td></tr>';
print '</table></form>';
?>
