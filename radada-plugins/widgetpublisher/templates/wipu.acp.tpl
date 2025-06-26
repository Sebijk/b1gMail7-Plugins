<fieldset>
  <legend><b>{lng p="wipu_publishwidget"}</b></legend>
  
{if $showinfo}
<fieldset>
  <legend>{lng p="status"}</legend>
  <b><font color="green">{lng p="wipu_finished"}</font></b>
</fieldset>
{/if}
  
<form action="{$adminlink}" method="post" onsubmit="if(!confirm('{lng p="wipu_submitconfirm"}'))return false;spin(this);">
<input type="hidden" name="publish" value="true"/>
<fieldset>
	<legend>{lng p="wipu_widgetselect"}</legend>
	<table class="list">
		<tr>
			<th>{lng p="title"}</th>
			<th width="140" style="text-align:center;">{lng p="wipu_startboard"}</th>
			<th width="140" style="text-align:center;">{lng p="wipu_organizerboard"}</th>
		</tr>
		
		{foreach from=$widgetlist key=widgetkey item=widget}
		{cycle name=class values="td1,td2" assign=class}
		<tr class="{$class}">
			<td>{text value=$widget.title}<br /><small>{text value=$widgetkey}</small></td>
			<td style="text-align:center;">{if $widget.start}<input type="checkbox" name="startwidgets[]" value="{$widgetkey}" />{else}-{/if}</td>
			<td style="text-align:center;">{if $widget.organizer}<input type="checkbox" name="organizerwidgets[]" value="{$widgetkey}" />{else}-{/if}</td>
		</tr>
		{/foreach}
		
	</table>
</fieldset>


<fieldset>
  <legend>{lng p="groups"}</legend>
		<table width="100%">
			<tr>
				<td class="td1" width="170"><b>{lng p="wipu_allgroups"}</b> <input name="groups" value="all" checked="checked" type="radio"></td>
				<td class="td2"><input name="addtotemplates" id="addtotemplates" checked="checked" type="checkbox"><label for="addtotemplates">{lng p="wipu_addtodefault"}</label></td>
			</tr>
			<tr>
				<td class="td1" width="170" style="vertical-align:top;"><b>{lng p="wipu_selgroups"}</b> <input name="groups" value="sel" type="radio"></td>
				<td class="td2">
					{foreach from=$groups item=group key=groupID}
						<input type="checkbox" name="selgroups[]" value="{$groupID}" id="group_{$groupID}"{if $simplewidget.groups.$groupID} checked="checked"{/if} />
							<label for="group_{$groupID}">{text value=$group.title}</label><br />
					{/foreach}
				</td>
			</tr>
		</table>
</fieldset>

<fieldset>
  <legend>{lng p="wipu_widgetpos"}</legend>
		<table width="100%">
			<tr>
				<td class="td1" width="170"><b>{lng p="wipu_horizontal"}:</b></td>
				<td class="td2"><select name="hpos"><option value="0" selected="selected">{lng p="wipu_leftcol"}</option><option value="1">{lng p="wipu_middlecol"}</option><option value="2">{lng p="wipu_rightcol"}</option></select></td>
			</tr>
			<tr>
				<td class="td1" width="170"><b>{lng p="wipu_vertical"}:</b></td>
				<td class="td2"><select name="vpos"><option value="top">{lng p="wipu_top"}</option><option value="bottom" selected="selected">{lng p="wipu_bottom"}</option></select></td>
			</tr>
			<tr>
				<td class="td1" width="170"><b>{lng p="wipu_forcepos"}?</b></td>
				<td class="td2"><input name="forcepos" type="checkbox"></td>
			</tr>
		</table>
</fieldset>

<fieldset>
  <legend>{lng p="wipu_startpublish"}</legend>
		<center><input type="submit" value="{lng p="wipu_startpublish"}"></center>
</fieldset>

</form>
</fieldset>