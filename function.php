<?php

require_once('ldapfunctions.php');
ini_set('error_log', 'syslog');

function username() {
        if (isset ($_SERVER['REMOTE_USER'])) $user = $_SERVER['REMOTE_USER'];
                else if (isset ($_SERVER['USER'])) $user = $_SERVER['USER'];
                else if ( isset($_SERVER['PHP_AUTH_USER']) ) $user = $_SERVER['PHP_AUTH_USER'];
                else {
                        syslog(LOG_ALERT, "action=check status=fail detail=\"No user given by connection from ${_SERVER['REMOTE_ADDR']}.\"");
                        return NULL;;
                }
        return $user;
}


function check_domain($conn,$fqdn,$base,$attrname) {
	if (!($sr=ldap_list($conn, $base, "$attrname=*",array("$attrname")))) exit (htmlspecialchars("Ricerca di <$attrname> su <$base> fallita. Errore:".ldap_error($conn)."."));
	$attr = ldap_get_entries($conn, $sr);
	for ($i=0;$i<$attr['count'];$i++)
		for ($j=0;$j<$attr[$i]["$attrname"]['count'];$j++)
			if ($attr[$i]["$attrname"][$j] == $fqdn) return true;
	return false;
}

function searchAttr ($conn,$attr,$attrnamefilt,$attrvaluefilt,$base) {
	if (!($sr=ldap_list($conn, $base, "(&($attrnamefilt=$attrvaluefilt)(objectclass=mailrecipient))",array("$attr")))) exit (htmlspecialchars("Ricerca di <$attrnamefilt=$attrvaluefilt> su <$base> fallita. Errore:".ldap_error($conn)."."));
	$entry = ldap_get_entries($conn, $sr);
	if ($entry['count'] > 1) {
		print '<p>'.htmlspecialchars('ERROR: there are more than one account with $attrname.').'</p>';
		return NULL;
	}
	if ($entry['count'] == 0) {
		print '<p>'.htmlspecialchars("ERROR: value <$attrvaluefilt> of $attrnamefilt not found on LDAP.").'</p>';
		return NULL;
	}
	if ($entry['count'] == 1) {
		print '<p>'.htmlspecialchars("Account <$attrvaluefilt> found on <".$entry[0]["$attr"][0].'>').'.</p>';
		return $entry[0]["$attr"][0];
	}
}


function list_fold($imapRes,$imapRef,$uid,$dom, $delaydel=FALSE) {

	if ($delaydel)
		$search='DELETED/user/'.$uid.'/*@'.$dom;
	else
		$search='INBOX*';
	$return=array();
	$list = imap_list($imapRes, $imapRef , $search);
	if (is_array($list)) {
                foreach ($list as $mbox)
                        $return[] = str_replace('INBOX/', NULL, explode($imapRef,$mbox,2)[1]);
	} else {
		print '<p>'.htmlspecialchars('ERRORE: can\'t list folder: ' . imap_last_error() ). '</p>';
		return FALSE;
	}
	return $return;
}

function mailboxExists($imapRes, $imapRef, $mailbox) {
# Return TRUE if a unique mailbox $mailbox is found.

	$alreadyfolder = imap_list($imapRes, $imapRef, $mailbox);
	if (is_array($alreadyfolder)) {
		if ( count($alreadyfolder) == 1)
			return TRUE;
	}
	return FALSE;
}

function folderPathRecover ($imapRes, $imapRef, $recoverFolder, $timestampFolder) {
# if $recoverFolder already exists, add a $timestamp in its name

	if ( mailboxExists($imapRes,$imapRef,$recoverFolder) ) {
		$parts = explode('@',$recoverFolder);
                $recoverFolder = $parts[0] . '-' . mb_convert_encoding( date('c',hexdec($timestampFolder)). '@' .$parts[1], "UTF7-IMAP", "UTF-8");
		return folderPathRecover ($imapRes, $imapRef, $recoverFolder, $timestampFolder);
	}
	else {
		return $recoverFolder;
	}
}

function IsValidFQDN($FQDN) {
    return (!empty($FQDN) && preg_match('/(?=^.{1,254}$)(^(?:(?!\d|-)[a-z0-9\-]{1,63}(?<!-)\.)+(?:[a-z]{2,})$)/i', $FQDN) > 0);
}
?>
