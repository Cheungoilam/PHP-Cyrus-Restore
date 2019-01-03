<?php

$mboxname = '{'.$mailhost.':143}';
$mbox = imap_open($mboxname,$imapadmin, $imappwd, OP_READONLY)
        or exit('<p>'.htmlspecialchars("ERROR connecting to <$mailhost>: " . imap_last_error()).'</p>');
$deleted = list_fold($mbox,$mboxname,$uid,$dom,TRUE);
if ( $deleted === FALSE ) exit ('<p>'.htmlspecialchars('No restorable folders found.').'</p>');
if (!is_null($ignoreFolders))
	$deleted = array_values(preg_grep("/($ignoreFolders)/i",$deleted,PREG_GREP_INVERT));

$l = count($deleted);
if ($l == 0) exit ('<p>'.htmlspecialchars('No restorable folders found.').'</p>');
print <<<END
<form method="POST" accept-charset="UTF-8" name="ListFolder" action="recoverFolder.php" onSubmit="xmlhttpPost('recoverFolder.php', 'ListFolder', 'Recover', '<img src=\'/include/pleasewait.gif\'>'); return false;">
<input type="hidden" name="mailhost" value="$mailhost" />
<table id="tblData">
<tr>	<th>
      		<input type="checkbox" id="chkParent" />
    	</th>
	<th>Folder</th><th>Del Date</th></tr>
END;

sort($deleted);

for ($i=0;$i<$l;$i++) {
	$folder=NULL;
	$part = explode ('/',$deleted[$i]);
	$count = count($part);
	# Just the only folder name to print for view, ie "Drafts"
	for ($j=3;$j<($count-1);$j++) $folder .= $part[$j].'/';
	$folder=rtrim($folder,'/');

	# The hash part created by delay delete
	$tm=array_shift(explode ('@',$part[$count-1]));

	$post = array(	'path' => $deleted[$i],
			'deldate' => $tm,
			'name' => $folder,
			'mailbox' => "$uid@$dom"
		);
	
	print '<tr><td><input type="checkbox" name="folder[]"'.
		'value="'.base64_encode(serialize($post)).'" /></td><td>'.htmlspecialchars(mb_convert_encoding($folder, "UTF-8", "UTF7-IMAP")).'</td><td>'.date('r',hexdec($tm)).'</td>';
	print '</tr>';
}

imap_close($mbox);
print '<tr><td colspan="3"><input type="submit" value="Engage" name="Engage" class="btn"></td></tr>';
print '</table></form>';
?>
