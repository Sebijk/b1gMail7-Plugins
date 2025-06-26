<fieldset>
	<legend>{lng p="edit"}</legend>
	
	<form action="{$adminlink}&do=edit&id={$mimetype.id}" method="post" onsubmit="spin(this)" enctype="multipart/form-data">
	<input type="hidden" name="save" value="true" />
		<table width="100%">
			<tr>
				<td width="40" valign="top" rowspan="3"><img src="{$tpldir}images/extension.png" border="0" alt="" width="32" height="32" /></td>
				<td class="td1" width="150">{lng p="filetypes"}:</td>
				<td class="td2"><input type="text" style="width:85%;" name="extensions" value="{text value=$mimetype.extensions}" /></td>
			</tr>
			<tr>
				<td class="td1" width="150">{lng p="homepp_mimetype"}:</td>
				<td class="td2"><input type="text" style="width:85%;" name="mimetype" value="{text value=$mimetype.mimetype}" /></td>
			</tr>
			<tr>
				<td class="td1" width="150">{lng p="homepp_showinbrowser"}?</td>
				<td class="td2"><input name="showinbrowser" {if $mimetype.showinbrowser}checked="checked"{/if} type="checkbox"></td>
			</tr>
		</table>
	
		<p align="right">
			<input type="submit" value=" {lng p="save"} " />
		</p>
	</form>
</fieldset>