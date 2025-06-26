<fieldset>
	<legend>{lng p="edit"}</legend>
	
	<form action="{$adminlink}&do=edit&id={$simplewidget.id}" method="post" onsubmit="spin(this)" enctype="multipart/form-data">
	<input type="hidden" name="save" value="true" />
		<table width="100%">
			<tr>
				<td width="40" valign="top" rowspan="7"><img src="{$tpldir}images/extension.png" border="0" alt="" width="32" height="32" /></td>
				<td class="td1" width="150">{lng p="title"}:</td>
				<td class="td2"><input type="text" style="width:85%;" name="title" value="{text value=$simplewidget.title}" /></td>
			</tr>
			<tr>
				<td class="td1" width="150">{lng p="siwi_content"}:</td>
				<td class="td2"><textarea style="width:85%;" rows="5" name="content">{text value=$simplewidget.content}</textarea></td>
			</tr>
			<tr>
				<td class="td1" width="150">{lng p="siwi_nosmarty"}?</td>
				<td class="td2"><input name="no_smarty" {if $simplewidget.no_smarty}checked="checked"{/if} type="checkbox"></td>
			</tr>
			<tr>
				<td class="td1" width="150">{lng p="siwi_activeated"}?</td>
				<td class="td2"><input name="aktiv" {if $simplewidget.aktiv}checked="checked"{/if} type="checkbox"></td>
			</tr>
			<tr>
				<td class="td1" width="150">{lng p="siwi_startboard"}?</td>
				<td class="td2"><input name="suitable_start" {if $simplewidget.suitable_start}checked="checked"{/if} type="checkbox"></td>
			</tr>
			<tr>
				<td class="td1" width="150">{lng p="siwi_organizerboard"}?</td>
				<td class="td2"><input name="suitable_organizer" {if $simplewidget.suitable_organizer}checked="checked"{/if} type="checkbox"></td>
			</tr>
			<tr>
				<td class="td1" width="150" style="vertical-align:top;">{lng p="groups"}:</td>
				<td class="td2">
					<input type="checkbox" name="groups[]" value="0" id="group_0"{if $simplewidget.groups.0} checked="checked"{/if} />
						<label for="group_0"><b><u>{lng p="siwi_allgroups"}</u></b></label><br />
					{foreach from=$groups item=group key=groupID}
						<input type="checkbox" name="groups[]" value="{$groupID}" id="group_{$groupID}"{if $simplewidget.groups.$groupID} checked="checked"{/if} />
							<label for="group_{$groupID}"><b>{text value=$group.title}</b></label><br />
					{/foreach}
				</td>
			</tr>
		</table>
	
		<p align="right">
			<input type="submit" value=" {lng p="save"} " />
		</p>
	</form>
</fieldset>