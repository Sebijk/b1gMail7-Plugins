<?xml version="1.0" encoding="{$charset}"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title>{$service_title}</title>
	
	<!-- meta -->
	<meta http-equiv="content-type" content="text/html; charset={$charset}" />

	<!-- links -->
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
</head>

<!-- body -->
<body link="#666666" alink="#666666" bgcolor="#FFFFFF">

	<table border="0" width="100%" cellspacing="0" cellpadding="4">
		<tr>
			<td bgcolor="#1978FF">
				<img align="absmiddle" src="{$selfurl}{$_tpldir}images/{if $titleIcon}{$titleIcon}{else}m/checkmail.gif{/if}" border="0" alt="" width="16" height="16" />
				{if $titleLink}<a href="{$titleLink}">{/if}<font color="#FFFFFF" face="arial" size="2"><b>{$pageTitle}</b></font>{if $titleLink}</a>{/if}
			</td>
		</tr>
		<tr>
			<td bgcolor="#E6ECF5" align="left">
				<font color="#000000" face="arial" size="1">
					{include file="$page"}
				</font>
			</td>
		</tr>
		<tr>
			<td align="center" bgcolor="#BDD6E6">
				<font color="#000000" face="arial" size="1">
					{literal}&nbsp;{/literal}
				</font>
			</td>
		</tr>
	</table>
	
	<img src="{$selfurl}cron.php?out=img" border="0" alt="" width="1" height="1" />
	
</body>

</html>
