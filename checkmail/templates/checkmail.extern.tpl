<html>
<head>
<meta http-equiv="refresh" content="{$in_refresh}" />
<title>{$titel} - CheckMail</title>
<link href="{$tpldir}style/loggedin.css" rel="stylesheet" type="text/css" />
<link href="{$tpldir}style/dtree.css" rel="stylesheet" type="text/css" />
</head>
<body>
<table width="100%" cellspacing="0" cellpadding="0">
  <tr><td class="box_head_left"><center>CheckMail</center></td></tr>
  <tr>
	<td class="box_left">

				<center>{$zeitangabe}
<p />
<a target="_blank" href="start.php?sid={$sid}"><img border="0" src="./plugins/templates/images/checkmail_logo.gif" alt="" /></a>
<p />
<font color="#666666" face="Tahoma" size="3">{$s_usermail}</font>
<p />
<font face="Tahoma" size="2">{$willkommenstext}</font>
<p />
<font face="Tahoma" size="2">
<a href="start.php?action=checkmail&amp;sid={$sid}" onclick="window.location.reload()" style="text-decoration: none">Aktualisieren</a>
</font></center>
<p />{if $cgicore}<img src="{$cgiurl}" border="0" width="1" height="1" alt="" />{/if}
  <img src="cron.php?out=img&sid={$sid}" width="1" height="1" border="0" alt="" />
    </td>
  </tr>
</table>
</body>
</html>