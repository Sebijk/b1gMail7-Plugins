<table width="100%" cellpadding="4" cellspacing="0">
	<tr>
		<td>{lng p="from"}:</td>
		<td><font size="1">{text value=$from}</font></td>
	</tr>
	{if $cc}
	<tr>
		<td>{lng p="cc"}:</td>
		<td><font size="1">{text value=$cc}</font></td>
	</tr>
	{/if}
	<tr>
		<td>{lng p="date"}:</td>
		<td><font size="1">{date timestamp=$date elapsed=true}</font></td>
	</tr>
	<tr>
		<td>{lng p="subject"}:</td>
		<td><font size="1">{text value=$subject}</font></td>
	</tr>
	<tr>
		<td colspan="2" bgcolor="#FFFFFF">
			<font face="arial" size="1" color="#000000">
			{$text}
			</font>
		</td>
	</tr>
</table><br />
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="left">
			{if $prevID}<a href="main.php?action=read&id={$prevID}&sid={$sid}">&laquo;</a>{/if}
		</td>
		<td align="center">
			<a href="main.php?folder={$folderID}&sid={$sid}"><img src="{$selfurl}{$_tpldir}images/m/folders.gif" border="0" alt="{lng p="folders"}" width="16" height="16" /></a>
			<a href="main.php?action=compose&to={$replyTo}&subject={$replySubject}&sid={$sid}"><img src="{$selfurl}{$_tpldir}images/m/reply.gif" border="0" alt="{lng p="reply"}" width="16" height="16" /></a>
			<a href="main.php?action=deleteMail&id={$mailID}&sid={$sid}"><img src="{$selfurl}{$_tpldir}images/m/delete.gif" border="0" alt="{lng p="delete"}" width="16" height="16" /></a>
		</td>
		<td align="right">
			{if $nextID}<a href="main.php?action=read&id={$nextID}&sid={$sid}">&raquo;</a>{/if}
		</td>
	</tr>
</table>