<?php
/*
 * Copyright (c) 2009, Home of the Sebijk.com
 * http://www.sebijk.com
 */

class modxmpphp extends BMPlugin
{	

	// Informationen zum Modul
	function modxmpphp()
	{
		
		$this->name				= 'XMPPHP Jabber-Integration';
		$this->author			= 'Home of the Sebijk.com';
		$this->web				= 'http://www.sebijk.com';
		$this->mail				= 'sebijk@web.de';
		$this->version			= '0.5.0 Alpha';
	}
	
	function OnReceiveMail($mail)
	{
	global $this;
	if(!class_exists('XMPPHP_XMPP')) include(B1GMAIL_DIR . 'serverlib/3rdparty/XMPPHP/XMPP.php');
	// Configuration variable
	$jabber_user = "youruser"; // without @server
	$jabber_passwort = "yourpassword";
	$jabber_server = "yourserver.tld";
	
	
  $jabber_class = new XMPPHP_XMPP($jabber_server, 5222, $jabber_user, $jabber_password, "b1gMail", $jabber_server, $printlog=false, $loglevel=XMPPHP_Log::LEVEL_INFO);
  $jabber_class->connect();
  $jabber_class->processUntil('session_start');
  $emailmessage = "Sie haben eine neue E-Mail von ".$mail->GetHeaderValue('from')." mit dem Betreff ".$mail->GetHeaderValue('subject')." erhalten.";
  $jabber_class->message($this->_userMail, $emailmessage, "chat", "Neue E-Mail erhalten");
	$jabber_class->disconnect();
	//PutLog("");
	}

}
/**
 * register plugin
 */
$plugins->registerPlugin('modxmpphp');
?>