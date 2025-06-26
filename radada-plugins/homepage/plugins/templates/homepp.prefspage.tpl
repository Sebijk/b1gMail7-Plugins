<h1><img src="plugins/templates/images/userprefs_ico16.png" width="16" height="16" border="0" alt="" align="absmiddle" /> {lng p="userprefs_homepp"}</h1>

<form name="f1" method="post" action="prefs.php?action=userprefs_homepp&do=save&sid={$sid}">
	<table class="listTable">
		<tr>
			<th class="listTableHead" colspan="2">{lng p="homepp_userprefs_title"}</th>
		</tr>
		<tr>
			<td class="listTableLeftDesc">{lng p="homepp_userprefs_useshare"}:</td>
			<td class="listTableRightDesc">{lng p="homepp_userprefs_mainmail"}</td>
		</tr>
		<tr>
			<td class="listTableLeft"><label for="homepp_share">{text value=$mainmail}:</label></td>
			<td class="listTableRight">
				<select name="homepp_share" id="homepp_share">
					<option value="-1">{lng p="homepp_userprefs_noneshare"}</option>
				{foreach from=$shares item=share}
					<option value="{$share.id}"{if $share.id==$homepp_share} selected="selected"{/if}>{$share.path}</option>
				{/foreach}
				</select>
			</td>
		</tr>
		{if $aliase!==false}
		<tr>
			<td class="listTableLeftDesc">{lng p="homepp_userprefs_useshare"}:</td>
			<td class="listTableRightDesc">{lng p="aliases"}</td>
		</tr>
		{foreach from=$aliase item=alias}
		<tr>
			<td class="listTableLeft"><label for="homepp_alias_share[{$alias.id}]">{text value=$alias.email}:</label></td>
			<td class="listTableRight">
				<select name="homepp_alias_share[{$alias.id}]" id="homepp_share">
					<option value="-1">{lng p="homepp_userprefs_noneshare"}</option>
					<option value="0"{if $alias.share=="0"} selected="selected"{/if}>{lng p="homepp_userprefs_likemainmail"}</option>
				{foreach from=$shares item=share}
					<option value="{$share.id}"{if $share.id==$alias.share} selected="selected"{/if}>{$share.path}</option>
				{/foreach}
				</select>
			</td>
		</tr>
		{/foreach}
		{/if}
		{if $homepp_global_dirlisting}
		<tr>
			<td class="listTableLeftDesc"><img src="{$tpldir}images/li/ico_common.png" width="16" height="16" border="0" alt="" /></td>
			<td class="listTableRightDesc">{lng p="common"}</td>
		</tr>
		<tr>
			<td class="listTableLeft"><label for="homepp_dirlisting">{lng p="homepp_userprefs_dirlisting"}:</label></td>
			<td class="listTableRight">
				<input type="checkbox" name="homepp_dirlisting" id="homepp_dirlisting"{if $homepp_dirlisting=='yes'} checked="checked"{/if} />
					<label for="homepp_dirlisting">{lng p="enable"}</label>
			</td>
		</tr>
		{/if}
		<tr>
			<td class="listTableLeft">&nbsp;</td>
			<td class="listTableRight">
				<input type="submit" value="{lng p="ok"}" />
				<input type="reset" value="{lng p="reset"}" />
			</td>
		</tr>
	</table>
</form>
