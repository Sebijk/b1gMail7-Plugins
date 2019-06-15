<fieldset>
	<legend>{lng p="prefs"}</legend>
	
	<form action="{$pageURL}&sid={$sid}&do=save" method="post" onsubmit="spin(this)">
	<table>
		<tr>
			<td align="left" rowspan="3" valign="top" width="40"><img src="../plugins/templates/images/logout_edit.png" border="0" alt="" width="32" height="32" /></td>
			<td>
				{lng p="logouthinweis_desc"}<br /><br />
				<textarea name="hinweistext" cols="120" rows="15" />{$hinweistext}</textarea>
			</td>
		</tr>
	</table>
	<p>
		<div style="float:right;">
			<input type="submit" value=" {lng p="save"} " />
		</div>
	</p>
	</form>
</fieldset>