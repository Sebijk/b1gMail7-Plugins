<form action="main.php?action=sendMail&sid={$sid}" method="post">
<table cellspacing="0" cellpadding="2">
	<tr>
		<td>{lng p="to"}:</td>
		<td><input type="text" name="to" value="{text value=$mail.to allowEmpty=true}" size="16"></td>
	</tr>
	<tr>
		<td>Cc:</td>
		<td><input type="text" name="cc" value="{text value=$mail.cc allowEmpty=true}" size="16"></td>
	</tr>
	<tr>
		<td>{lng p="subject"}:</td>
		<td><input type="text" name="subject" value="{text value=$mail.subject allowEmpty=true}" size="16"></td>
	</tr>
	<tr>
		<td valign="top">{lng p="text"}:</td>
		<td><textarea name="text" cols="16" rows="8">{$mail.text}</textarea></td>
	</tr>
</table>

<center>
	<br />
	<input type="submit" value=" {lng p="submit"} " />
	<br />
	<br /><a href="main.php?sid={$sid}">{lng p="back"}</a>
</center>

</form>