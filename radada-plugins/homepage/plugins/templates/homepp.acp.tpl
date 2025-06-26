<fieldset>
	<legend>{lng p="prefs"}</legend>
	
	<form action="{$adminlink}" method="post" onsubmit="spin(this)" enctype="multipart/form-data">
	<input type="hidden" name="saveconfig" value="true" />
		<table width="100%">
			<tr>
				<td class="td1" width="150">{lng p="homepp_indexfiles"}:</td>
				<td class="td2"><input type="text" style="width:85%;" name="indexfiles" value="{text value=$config.indexfiles}" /></td>
			</tr>
			<tr>
				<td class="td1" width="150">{lng p="homepp_defaultmime"}:</td>
				<td class="td2"><input type="text" style="width:85%;" name="defaultmime" value="{text value=$config.defaultmime}" /></td>
			</tr>
			<tr>
				<td class="td1" width="150">{lng p="homepp_dirlisting"}?</td>
				<td class="td2"><input name="dirlisting" {if $config.dirlisting}checked="checked"{/if} type="checkbox"></td>
			</tr>
		</table>
	
		<p align="right">
			<input type="submit" value=" {lng p="save"} " />
		</p>
	</form>
</fieldset>

<fieldset>
	<legend>{lng p="homepp_mimetypes"}</legend>

	<form action="{$adminlink}" name="f1" method="post" onsubmit="spin(this)">
	<table class="list">
		<tr>
			<th width="25" style="text-align:center;"><a href="javascript:invertSelection(document.forms.f1,'mimetype_');"><img src="{$tpldir}images/dot.png" border="0" alt="" width="10" height="8" /></a></th>
			<th>{lng p="filetypes"}</th>
			<th width="200">{lng p="homepp_mimetype"}</th>
			<th width="200">{lng p="homepp_showinbrowser"}</th>
			<th width="60">&nbsp;</th>
		</tr>
		
		{foreach from=$mimetypes item=mimetype}
		{cycle name=class values="td1,td2" assign=class}
		<tr class="{$class}">
			<td align="center"><input type="checkbox" name="mimetype_{$mimetype.id}" /></td>
			<td>{text value=$mimetype.extensions}</td>
			<td>{text value=$mimetype.mimetype}</td>
			<td>{if $mimetype.showinbrowser}{lng p="yes"}{else}{lng p="no"}{/if}</td>
			<td>
				<a href="{$adminlink}&do=edit&id={$mimetype.id}"><img src="{$tpldir}images/edit.png" border="0" alt="{lng p="edit"}" width="16" height="16" /></a>
				<a href="{$adminlink}&delete={$mimetype.id}" onclick="return confirm('{lng p="realdel"}');"><img src="{$tpldir}images/delete.png" border="0" alt="{lng p="edit"}" width="16" height="16" /></a>
			</td>
		</tr>
		{/foreach}
		
		<tr>
			<td class="footer" colspan="8">
				<div style="float:left;">
					{lng p="action"}: <select name="massAction" class="smallInput">
						<option value="-">------------</option>
						
						<optgroup label="{lng p="actions"}">
							<option value="delete">{lng p="delete"}</option>
						</optgroup>
					</select>&nbsp;
				</div>
				<div style="float:left;">
					<input type="submit" name="executeMassAction" value=" {lng p="execute"} " class="smallInput" />
				</div>
			</td>
		</tr>
	</table>
	</form>
</fieldset>

<fieldset>
	<legend>{lng p="homepp_addmime"}</legend>
	
	<form action="{$adminlink}&add=true" method="post" onsubmit="spin(this)" enctype="multipart/form-data">
		<table width="100%">
			<tr>
				<td width="40" valign="top" rowspan="3"><img src="{$tpldir}images/extension_add.png" border="0" alt="" width="32" height="32" /></td>
				<td class="td1" width="150">{lng p="filetypes"}:</td>
				<td class="td2"><input type="text" style="width:85%;" name="extensions" value="" /></td>
			</tr>
			<tr>
				<td class="td1" width="150">{lng p="homepp_mimetype"}:</td>
				<td class="td2"><input type="text" style="width:85%;" name="mimetype" value="" /></td>
			</tr>
			<tr>
				<td class="td1" width="150">{lng p="homepp_showinbrowser"}?</td>
				<td class="td2"><input name="showinbrowser" type="checkbox"></td>
			</tr>
		</table>
	
		<p align="right">
			<input type="submit" value=" {lng p="add"} " />
		</p>
	</form>
</fieldset>