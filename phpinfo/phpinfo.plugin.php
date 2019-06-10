<?php

class modphpinfo extends BMPlugin
{
	// Informationen zum Modul
	function modphpinfo()
	{
		$this->name				= 'PHP-Info';
		$this->author			= 'Home of the Sebijk.com';
		$this->web				= 'http://www.sebijk.com';
		$this->mail				= 'sebijk@web.de';
		$this->version			= '1.0';
		$this->designedfor		= '7';
		$this->admin_pages		=  true;
		$this->admin_page_title	= 'PHP-Info zeigen';
	}
	
	function AdminHandler()
	{
		phpinfo();
	}
}
/**
 * register plugin
 */
$plugins->registerPlugin('modphpinfo');
?>
