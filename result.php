<div id="content">
<?php

require_once('function.php');
require_once('config.php');

$uid = strtolower(utf8_encode($_POST['uid']));
$dom = utf8_encode($_POST['dom']);
$range = $_POST['days'];


/* Controlli preliminari */

$options = array(
    'options' => array('min_range' => 1, 'max_range' => 30)
);

if (filter_var("$uid@$dom", FILTER_VALIDATE_EMAIL) === FALSE)
 exit ('<p>'.htmlspecialchars("Insert a valid username, please").'.</p>');


/* Determinazione dominio, uid e mailhost */
if (IsValidFQDN($dom)) {
        if (!($conn = conn_ldap($ldapserver,$ldapport,$dnlog,$password,NULL,$err))) exit
        	(htmlentities("\n$err"));
	else $log = "Connection with LDAP Server established.";
	$domok = check_domain($conn,$dom,$base,$attr_ldap_localdom);
}
else exit ('<p>'.htmlspecialchars("Insert a FQDN domain, please.").'</p>');

if ($domok) print "<p>$dom: valid domain.</p>";
else exit ("<p>$dom: NOT valid.</p>");

$mailhost = searchAttr($conn,'mailhost','uid',"$uid@$dom","o=$dom,".$base);
ldap_close($conn);

if (!isset($mailhost)) exit ('<p>ERROR: '.htmlspecialchars("Can't determine a popserver for <$uid@$dom>.").'</p>');
print '</div>';


/* Delayed deleted folder */
if ($_POST['list']) {
	require_once('listDELfolder.php');
}
else {
	require_once('listfolder.php');
}

?>
