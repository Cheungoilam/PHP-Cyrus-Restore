<html>
<head>
<title>Restore Cyrus</title>
<meta http-equiv="Content-Type" content="text/html; charset="UTF-8">
<link rel="stylesheet" type="text/css" href="/include/style.css">
<link rel="SHORTCUT ICON" href="favicon.ico"> 
<script  src="/include/ajaxsbmt.js" type="text/javascript"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="/include/checkAll.js" type="text/javascript"></script>
</head>
<body>
<h1 style="margin:2">Cyrus Restore</h1>
<p style="text-align: right; margin:0">Logged in as <b><?php echo $_SERVER["REMOTE_USER"];?></b></p>

<?php
require_once('config.php');
require_once($welcomeFile);
print <<<END
 <form method="POST" accept-charset="UTF-8" name="Richiestadati" action="result.php" onSubmit="xmlhttpPost('result.php', 'Richiestadati', 'Risultato', '<img src=\'/include/pleasewait.gif\'>'); return false;">
<table align="center" cellspacing=1>
<caption>Cyrus Restore Tool</caption>
<thead>
<tr><td colspan="3"></td></tr>
</thead>
<tfoot>
<tr><td colspan="3" style= "text-align: right"><h6>Full HTML5 browser needed.</h6></td></tr>
</tfoot>
<tbody>
<tr>
<td class="form">Mailbox</td><td colspan="2"><input type="text" name="uid" size="25" class="input_text" id="1" placeholder="user" required> <b>@</b> <input type="text" name="dom" size="25" class="input_text" id="2" placeholder="domain" required></td></tr>

<tr>
<td class="form">Options</td>
    <td class="form"><input name="list" value="1" checked="" type="radio">List restorable folders</td>
    <td class="form"><input name="list" value="0" type="radio">Restore messages</td>
</tr>
<tr style= "margin-top: 3"><td colspan="3" style="text-align:center"><input type="submit" value="Engage"
   name="Engage" onClick="xmlhttpPost('list.htm', 'Richiestadati', 'Recover', ''); return true;"> <input type="reset" value="Reset" name="Reset" onclick="xmlhttpPost('list.htm', 'Richiestadati', 'Recover', ''); xmlhttpPost('list.htm', 'Richiestadati', 'Risultato', ''); return false;"></td></tr>
</tbody></table></form>

<div id="Risultato"></div>
<hr>
<div id="Recover"></div>
END;

?>
</body>
</html>
