{foreach from=$folders item=folder key=folderID}
	<img src="{$selfurl}{$_tpldir}images/li/menu_ico_{$folder.type}.png" border="0" alt="" width="16" height="16" align="absmiddle" />
	&nbsp;<a href="main.php?folder={$folderID}&sid={$sid}">{$folder.title}</a><br />
{/foreach}