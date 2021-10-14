<form action="index.php?action=login" method="post">
<input type="hidden" name="do" value="login" />
<center>
	<p>
		<img src="{$selfurl}{$_tpldir}images/m/logo.gif" border="0" alt="" width="48" height="48" />
		<br /><br />
	</p>
	
	<p>
		<b>{lng p="email"}:</b><br />
		<input type="text" size="16" name="email" /><br />
		<br /><b>{lng p="password"}:</b><br />
		<input type="password" size="16" name="password" />
		<br /><b>{lng p="savelogin"}:</b><br />
		<input type="checkbox" name="savelogin" />
	</p>
	
	<p>
		<br />
		<input type="submit" value=" {lng p="login"} " />
	</p>
</center>
</form>