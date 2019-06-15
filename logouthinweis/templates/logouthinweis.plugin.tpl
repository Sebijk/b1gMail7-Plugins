{literal}
<style type="text/css" media="screen">
<!--
.title   { 
	color: #ffffff; 
	font-size: 18px; 
	font-family: Arial; 
	font-style: italic; 
	text-align: left;
	}
	
.hinweis   { 
	color: #6e6e6e; 
	font-size: 15px; 
	font-family: Arial; 
	font-style: italic; 
	text-align: left;
	}
-->
</style>
{/literal}
<center>{banner}</center>
	<div class="container">
		<div class="page-header"><h1>{$title}</h1></div>

		<p>
	<table height="50%">
	<tr>
		<td valign="top" width="64" align="center"><br /><img src="{$tpldir}images/li/msg.png" width="48" height="48" border="0" alt="" /></td>
		<td valign="top" class="hinweis"><br />
		{$msg}
		<br /><br />
		</td>
	</tr>
</table>
		</p>
		
			<button type="button" class="btn btn-success" onclick="document.location.href='{$backLink}';">
				<span class="glyphicon"></span> Weiter zur Mailbox
			</button>
		</p>