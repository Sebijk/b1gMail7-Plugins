<?php
/*
 * Copyright (c) 2015, Home of the Sebijk.com
 * http://www.sebijk.com
 */
class modprosody extends BMPlugin
{	
	/*
	* Eigenschaften des Plugins
	*/
	function modprosody()
	{
		$this->name					= 'Prosody-Integration';
		$this->version				= '0.9.5';
		$this->designedfor			= '7.3.0';
		$this->type					= BMPLUGIN_DEFAULT;

		$this->author				= 'Home of the Sebijk.com';
		$this->web					= 'http://www.sebijk.com';
		$this->mail					= 'sebijk@web.de';

		$this->update_url			= 'http://my.b1gmail.com/update_service/';
		$this->website				= 'http://my.b1gmail.com/details/187/';

		$this->admin_pages			=  false;
	}

	/*
	 * installation routine
	 */	
	function Install()
	{
		PutLog('Plugin "'. $this->name .' - '. $this->version .'" wurde erfolgreich installiert.', PRIO_PLUGIN, __FILE__, __LINE__);
		return(true);
	}

	/*
	 * uninstallation routine
	 */
	function Uninstall()
	{
		PutLog('Plugin "'. $this->name .' - '. $this->version .'" wurde erfolgreich deinstalliert.', PRIO_PLUGIN, __FILE__, __LINE__);
		return(true);
	}

	/*
	 * OnDeleteUser
	 */
	function OnDeleteUser($id)
	{
		global $db;

		$res = $db->Query("SELECT email FROM {pre}users WHERE id=?", $id);
		$jabber = $res->FetchArray();
		$res->Free();
	
		$jidsplit = explode("@", $jabber['email']);

		$db->Query('DELETE FROM prosody WHERE host = ? AND user= ?', $jidsplit[1], $jidsplit[0]);

		// If prosody mod_mam is installed and use SQL as storage
		$res = $db->Query("SHOW TABLES LIKE 'prosodyarchive'");
		if($res->RowCount() > 0) {
			$db->Query('DELETE FROM prosodyarchive WHERE host = ? AND user= ?', $jidsplit[1], $jidsplit[0]);
		}
	}

}
/**
 * register plugin
 */
$plugins->registerPlugin('modprosody');
?>