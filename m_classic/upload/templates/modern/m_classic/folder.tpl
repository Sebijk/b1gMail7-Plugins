<table width="100%" cellspacing="0" cellpadding="2">
	<tr>
		<td align="left">
			<a href="main.php?action=folders&sid={$sid}"><img src="{$selfurl}{$_tpldir}images/m/folders.gif" border="0" alt="{lng p="folders"}" width="16" height="16" /></a>
			<a href="main.php?action=compose&sid={$sid}"><img src="{$selfurl}{$_tpldir}images/m/compose.gif" border="0" alt="{lng p="sendmail"}" width="16" height="16" /></a>
			<a href="main.php?action=logout&sid={$sid}"><img src="{$selfurl}{$_tpldir}images/m/logout.gif" border="0" alt="{lng p="logout"}" width="16" height="16" /></a>
		</td>
		<td align="right">
			<font color="#000000" face="arial" size="1">
				{lng p="pages"}: {pageNav page=$pageNo pages=$pageCount on="<b>[.t]</b>&nbsp;" off="<a href=\"main.php?folder=$folderID&page=.s&sid=$sid\">.t</a>&nbsp;"}
			</font>
		</td>
	</tr>
</table>
{if $mails}
<table width="100%" cellpadding="4" cellspacing="0">
{foreach from=$mails item=mail key=mailID}
{cycle name=color values="#FFFFFF,#E6ECF5" assign=color}
	<tr>
		<td bgcolor="{$color}">
			<font face="arial" size="2">
				<a href="main.php?action=read&id={$mailID}&sid={$sid}">{if $mail.flags&1}<b>{/if}{text value=$mail.subject cut=35}{if $mail.flags&1}</b>{/if}</a>
			</font><br />
			<font color="#000000" face="arial" size="1">
				{if $mail.from_name}{text value=$mail.from_name cut=25}{else}{text value=$mail.from_mail cut=25}{/if}
				<a href="main.php?action=deleteMail&amp;id={$mailID}&amp;sid={$sid}"><img src="{$tpldir}images/m/delete.gif" border="0" alt="{lng p="delete"}" width="16" height="16" /></a>
			</font>		
		</td>
	</tr>
{/foreach}
</table>
{else}
<p>
	<i>{lng p="nomails"}</i>
</p>
{/if}
<table width="100%" cellspacing="0" cellpadding="2">
	<tr>
		<td align="left">
			<a href="main.php?action=folders&sid={$sid}"><img src="{$selfurl}{$_tpldir}images/m/folders.gif" border="0" alt="{lng p="folders"}" width="16" height="16" /></a>
			<a href="main.php?action=compose&sid={$sid}"><img src="{$selfurl}{$_tpldir}images/m/compose.gif" border="0" alt="{lng p="sendmail"}" width="16" height="16" /></a>
			<a href="main.php?action=logout&sid={$sid}"><img src="{$selfurl}{$_tpldir}images/m/logout.gif" border="0" alt="{lng p="logout"}" width="16" height="16" /></a>
		</td>
		<td align="right">
			<font color="#000000" face="arial" size="1">
				{lng p="pages"}: {pageNav page=$pageNo pages=$pageCount on="<b>[.t]</b>&nbsp;" off="<a href=\"main.php?folder=$folderID&page=.s&sid=$sid\">.t</a>&nbsp;"}
			</font>
		</td>
	</tr>
</table>