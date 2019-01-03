<?php

function conn_ldap($host,$port,$user,$pwd,$username, &$err) {
/*
$host	  = ldap host
$port	  = ldap port
$user	  = bind user
$pwd	  = bind user password
$username = user making operation, stored in log
*/
	$err = '';
        $ldapconn = ldap_connect($host, $port);
        ldap_set_option($ldapconn, LDAP_OPT_NETWORK_TIMEOUT, 5);
        if ($ldapconn) {
                // binding to ldap server
                syslog(LOG_INFO,  "$username: Info: LDAP: Trying to connect to $host:$port");
                $ldapbind = ldap_bind($ldapconn, $user, $pwd);
                // verify binding
                if ($ldapbind) {
                        syslog(LOG_INFO,  "$username: Info: LDAP: Successfully BIND as <".$user.'>.');
			return $ldapconn;
                }
                else {
                        $err = 'LDAP: Error trying to bind on <'.$host.'> as <'.$user.'>: '.ldap_error($ldapconn);
			ldap_get_option($ldapconn, LDAP_OPT_DIAGNOSTIC_MESSAGE, $detail);
			if (!is_null($detail)) $err.= ". Details: $detail.";
                        syslog(LOG_ERR, "$username: Error: $err.");
                        ldap_unbind($ldapconn);
			$err = str_replace(" on <$host> as <$user>", '', $err);
			return FALSE;
                }
        }
        else {
                $err = 'LDAP: Could not connect to LDAP server '.$host.':'.$port;
		ldap_get_option($ldapconn, LDAP_OPT_DIAGNOSTIC_MESSAGE, $detail);
		$err .= ". Details: $detail.";
                syslog(LOG_ERR, $username.": Error: $err. Details: $detail.");
		return FALSE;
        }
}



function printuid ($info) {
/* Web print LDAP result attributes names and values.
   Return number of results, or False for errors.     */

        if (!(isset($info["count"]))) return False;
        for ($i=0; $i<$info["count"]; $i++) {
                print "<p style=\"margin:0\">dn: ".$info[$i]["dn"]."</p>";
                print <<<END
                        <blockquote style="margin:0">
                        <table border="0" cellpadding="1" cellspacing="2" style="border: none; margin: 0">
                        <tr>
                                <th nowrap>Attributo</th>
                                <th nowrap>Valori</th>
                        </tr>
END;
                for ($ii=0; $ii<$info[$i]["count"]; $ii++) {    #cicla negli attributi di questo dn
                        $attrib= $info[$i][$ii];
                        $tag=NULL;
                        print "<tr><td>$attrib</td><td>"; #nome attributo
                        for ($iii=0;$iii<$info[$i]["$attrib"]["count"];$iii++) {
                                print '<div id="attr" class="off" onmouseover="this.className=\'on\'" onmouseout="this.className=\'off\'">';
                                print nl2br($info[$i]["$attrib"][$iii]); #valore attributo
                                print '</div>';
                        }
                        print "</td>";
                }
                print "</table></blockquote>";
        }
        return $info["count"];
}


function ldapsearch($conn,$dn,$print,$key_ldap,$justthese,$order,$username) {
/* Perform an LDAP search. Print result table, if you want. Return result array, or FALSE on errors. */

	$err = NULL;
        $filter="($key_ldap)";
        if ($justthese =="") unset ($justthese);
        # $justthese is like  array ( "ou", "o", "uid", "cn", "mail", "mailalternateaddress");
        if (isset($justthese)) {
		if (!($sr=ldap_search($conn, $dn, $filter,$justthese)))
			$err = "Looking for <$key_ldap> over <$dn> failed. Error: ".ldap_error($conn).".";
	}
        else
		if (!($sr=ldap_search($conn, $dn, $filter)))
			$err = "Looking for <$key_ldap> over <$dn> failed. Error: ".ldap_error($conn).".";
	
	if (!isnull($err)) {
		syslog(LOG_ERR, "$username: $err");
		return FALSE;
	}
        if (!empty($order)) ldap_sort($conn, $sr, $order);
        $info = ldap_get_entries($conn, $sr);

        if ($print) if (!printuid($info)) print "No result found.";
        return $info;
}


function fetchattrib ($ds,$basedn,$attrib,$username) {
/* Return all values of attribute returned by a single-level search */
        $justthese = array("$attrib");
        if (!($sr=ldap_list($ds, $basedn, "$attrib=*", $justthese))) {
		$err = "Looking for <$key_ldap> over <$dn> failed. Error: ".ldap_error($conn).".";
		syslog(LOG_ERR, "$username: $err");
		return FALSE;
	}
        ldap_sort($ds,$sr,"$attrib");
        $info = ldap_get_entries($ds, $sr);
        $return=array();
        for ($i=0; $i<$info["count"]; $i++)
                for ($j=0; $j<$info[$i]["$attrib"]["count"]; $j++)
                        $return[]=$info[$i]["$attrib"][$j];
        return $return;
}

?>
