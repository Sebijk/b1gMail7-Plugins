<?php
class LogoutHinweis extends BMPlugin 
{
	function LogoutHinweis()
	{
		global $lang_admin;
		
		// plugin info
		$this->type					= BMPLUGIN_DEFAULT;
		$this->name					= 'Logout Hinweis';
		$this->author				= 'M.Cholys, Sebijk';
		$this->version				= '1.1.0';
		$this->designedfor         	= '7.4.0';
		$this->update_url 			= 'http://my.b1gmail.com/update_service/';
		
		// admin pages
		$this->admin_pages			= true;
		$this->admin_page_title		= 'Logout-Hinweis';
	}
	
	// Installation
    function Install()
    {
		global $db;
		
		$db->Query("ALTER TABLE {pre}users ADD loggedout tinyint(4) NOT NULL DEFAULT 1;");
		PutLog('Zeile <loggedout> in Tabelle bm60_users erstellt !',
					PRIO_PLUGIN,
					__FILE__,
					__LINE__);

		$db->Query("ALTER TABLE {pre}users ADD loggedout2 tinyint(4) NOT NULL DEFAULT 1;");
		PutLog('Zeile <loggedout2> in Tabelle bm60_users erstellt !',
					PRIO_PLUGIN,
					__FILE__,
					__LINE__);
					
		$db->Query("ALTER TABLE {pre}users ADD hinweistext TEXT NOT NULL");
		PutLog('Zeile <hinweistext> in Tabelle bm60_users erstellt !',
					PRIO_PLUGIN,
					__FILE__,
					__LINE__);
					
		$db->Query('UPDATE {pre}users SET hinweistext=?',
				nl2br("Hallo,\nBei Ihrem letzten Besuch haben Sie vergessen, Ihr Postfach per <b>Logout</b> zu schlie&szlig;en.
              Zur Ihrer eigenen Sicherheit sollten SIe jedoch immer daran denken, Ihr Postfach per <b>Logout</b> zu verlassen, damit kein Zugriff auf Ihre pers&ouml;nlichen Daten m&ouml;glich ist.")
				);
		return(true);
    }
	
	// Deinstallation
    function Uninstall()
    {
		global $db;
		
		$db->Query("ALTER TABLE {pre}users DROP loggedout;");
		PutLog('Zeile <loggedout> in Tabelle bm60_users geloescht !',
					PRIO_PLUGIN,
					__FILE__,
					__LINE__);
					
		$db->Query("ALTER TABLE {pre}users DROP loggedout2;");
		PutLog('Zeile <loggedout2> in Tabelle bm60_users geloescht !',
					PRIO_PLUGIN,
					__FILE__,
					__LINE__);
					
		$db->Query("ALTER TABLE {pre}users DROP hinweistext;");
		PutLog('Zeile <hinweistext> in Tabelle bm60_users geloescht !',
					PRIO_PLUGIN,
					__FILE__,
					__LINE__);
		return(true);
    }
    
  /*
	 *  Sprachvariablen
   */
    function OnReadLang(&$lang_user, &$lang_client, &$lang_custom, &$lang_admin, $lang)
    {
            if(strpos($lang, 'deutsch') !== false) {
                    $lang_user['logouthinweis_title'] = "Logout Hinweis";
                    $lang_admin['logouthinweis_desc'] = "Hier k&ouml;nnen Sie den Text eingeben der als Hinweis angezeigt wird, wenn ein User sich nicht ausgeloggt hat.";
            }
            else {
                    $lang_user['logouthinweis_title'] = "Logout Notice";
                    $lang_admin['logouthinweis_desc'] = "Here you can enter the text that will be displayed as a hint if a user has not logged out.";
            }
    }
	
	// Login
    function OnLogin($userID, $interface = 'web')
    {
        global $db, $tpl, $lang_user;
		
        $db->Query("UPDATE {pre}users SET loggedout2=loggedout WHERE id=?",$userID);
        $db->Query("UPDATE {pre}users SET loggedout=0 WHERE id=?",$userID);
		
		if(INTERFACE_MODE!=true) {
      $sql = $db->Query("SELECT loggedout2, hinweistext FROM {pre}users WHERE id=?",$userID);
      $row = $sql->FetchArray();
      $loggedout3 = $row['loggedout2'];
      $sql->Free();
      
        if($loggedout3 == 0)
        {
          $sql = $db->Query("UPDATE {pre}users SET loggedout2=1 WHERE id=?",$userID);
          $tpl->assign('title', $lang_user['logouthinweis_title']);
          $tpl->assign('msg', $row['hinweistext']);
          $tpl->assign('backLink', 'start.php?sid=' . session_id());
          $tpl->assign('page', $this->_templatePath('logouthinweis.plugin.tpl'));
          $tpl->display('nli/index.tpl');
          exit();
        }
      }
    }
	
	// Logout
    function OnLogout($userID)
    {    
		global $db;
		
    	//änderung für logout
		$db->Query("UPDATE {pre}users SET loggedout='1' WHERE id=?",$userID);
    }
	
	function AdminHandler()
	{
		global $tpl, $plugins, $lang_admin;
		
		if(!isset($_REQUEST['action']))
			$_REQUEST['action'] = 'prefs';
		
		$tabs = array(
			0 => array(
				'title'		=> $lang_admin['prefs'],
				'link'		=> $this->_adminLink() . '&',
				'active'	=> $_REQUEST['action'] == 'prefs'
			)
		);

		$tpl->assign('tabs', $tabs);
		
		if($_REQUEST['action'] == 'prefs')
			$this->_prefsPage();
	}
	
	function _prefsPage()
	{
		global $tpl, $db, $bm_prefs;
		
		// speichern
		if(isset($_REQUEST['do']) && $_REQUEST['do'] == 'save')
		{
			$db->Query('UPDATE {pre}users SET hinweistext=?',
				$_REQUEST['hinweistext']
				);
		}

		$res = $db->Query('SELECT hinweistext FROM {pre}users');
		$row = $res->FetchArray();
		$res->Free();
			
		// an template übergeben
		$tpl->assign('hinweistext', $row['hinweistext']);
		$tpl->assign('pageURL', $this->_adminLink());
		$tpl->assign('page', $this->_templatePath('logouthinweis.prefs.tpl'));
		return(true);
	}
}
$plugins->registerPlugin('LogoutHinweis');
?>