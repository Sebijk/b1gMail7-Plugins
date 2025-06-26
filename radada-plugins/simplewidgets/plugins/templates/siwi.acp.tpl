<fieldset>
	<legend>{lng p="widgets"}</legend>

	<form action="{$adminlink}" name="f1" method="post" onsubmit="spin(this)">
	<table class="list">
		<tr>
			<th width="25" style="text-align:center;"><a href="javascript:invertSelection(document.forms.f1,'simplewidget_');"><img src="{$tpldir}images/dot.png" border="0" alt="" width="10" height="8" /></a></th>
			<th width="40">ID</th>
			<th width="60">{lng p="siwi_activeated"}?</th>
			<th>{lng p="title"}</th>
			<th width="150">{lng p="siwi_startboard"}?</th>
			<th width="150">{lng p="siwi_organizerboard"}?</th>
			<th width="60">&nbsp;</th>
		</tr>
		
		{foreach from=$simplewidgets item=simplewidget}
		{cycle name=class values="td1,td2" assign=class}
		<tr class="{$class}">
			<td align="center"><input type="checkbox" name="simplewidget_{$simplewidget.id}" /></td>
			<td>{text value=$simplewidget.id}</td>
			<td>{if $simplewidget.aktiv}{lng p="yes"}{else}{lng p="no"}{/if}</td>
			<td>{text value=$simplewidget.title}</td>
			<td>{if $simplewidget.suitable_start}{lng p="yes"}{else}{lng p="no"}{/if}</td>
			<td>{if $simplewidget.suitable_organizer}{lng p="yes"}{else}{lng p="no"}{/if}</td>
			<td>
        <a href="{$adminlink}&{if $simplewidget.aktiv}de{/if}activate={$simplewidget.id}"><img src="{$tpldir}images/plugin_switch.png" border="0" alt="{lng p="acdeactivate"}" title="{lng p="acdeactivate"}" border="0" width="16" height="16" /></a>
				<a href="{$adminlink}&do=edit&id={$simplewidget.id}"><img src="{$tpldir}images/edit.png" border="0" alt="{lng p="edit"}" title="{lng p="edit"}" width="16" height="16" /></a>
				<a href="{$adminlink}&delete={$simplewidget.id}" onclick="return confirm('{lng p="realdel"}');"><img src="{$tpldir}images/delete.png" border="0" alt="{lng p="delete"}" title="{lng p="delete"}" width="16" height="16" /></a>
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
							<option value="activate">{lng p="siwi_activeate"}</option>
							<option value="deactivate">{lng p="siwi_deactiveate"}</option>
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
	<legend>{lng p="siwi_createwidget"}</legend>
	
	<form action="{$adminlink}&add=true" method="post" onsubmit="spin(this)" enctype="multipart/form-data">
		<table width="100%">
			<tr>
				<td width="40" valign="top" rowspan="7"><img src="{$tpldir}images/extension_add.png" border="0" alt="" width="32" height="32" /></td>
				<td class="td1" width="150">{lng p="title"}:</td>
				<td class="td2"><input type="text" style="width:85%;" name="title" value="" /></td>
			</tr>
			<tr>
				<td class="td1" width="150">{lng p="siwi_content"}:</td>
				<td class="td2"><textarea style="width:85%;" rows="5" name="content"></textarea></td>
			</tr>
			<tr>
				<td class="td1" width="150">{lng p="siwi_nosmarty"}?</td>
				<td class="td2"><input name="no_smarty" type="checkbox"></td>
			</tr>
			<tr>
				<td class="td1" width="150">{lng p="siwi_activeated"}?</td>
				<td class="td2"><input name="aktiv" type="checkbox"></td>
			</tr>
			<tr>
				<td class="td1" width="150">{lng p="siwi_startboard"}?</td>
				<td class="td2"><input name="suitable_start" type="checkbox"></td>
			</tr>
			<tr>
				<td class="td1" width="150">{lng p="siwi_organizerboard"}?</td>
				<td class="td2"><input name="suitable_organizer" type="checkbox"></td>
			</tr>
			<tr>
				<td class="td1" width="150" style="vertical-align:top;">{lng p="groups"}:</td>
				<td class="td2">
					<input type="checkbox" name="groups[]" value="0" id="group_0" checked="checked"/>
						<label for="group_0"><b><u>{lng p="siwi_allgroups"}</u></b></label><br />
					{foreach from=$groups item=group key=groupID}
						<input type="checkbox" name="groups[]" value="{$groupID}" id="group_{$groupID}" />
							<label for="group_{$groupID}"><b>{text value=$group.title}</b></label><br />
					{/foreach}
				</td>
			</tr>
		</table>
	
		<p align="right">
			<input type="submit" value=" {lng p="create"} " />
		</p>
	</form>
</fieldset>