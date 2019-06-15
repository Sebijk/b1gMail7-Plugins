<?php
/*
 * Checkmail
 * Original Autor: haggi0505
 * (ph234@web.de)
 *
 * Optimized by Sebijk
 * http://www.sebijk.com
 */
class modcheckmail extends BMPlugin
{	

	function modcheckmail()
	{
		
		$this->name			= 'CheckMail';
		$this->author			= 'Sebijk, haggi0505';
		$this->web				= 'http://www.sebijk.com';
		$this->mail				= 'sebijk@web.de';
		$this->version			= '1.3';

	}
		
	function FileHandler(&$file, $action)
	{
		global $_REQUEST, $tpl, $lang_user, $db, $userRow;
		
		if($file=="start.php" && $_REQUEST['action']=="checkmail")
		{
			if(!class_exists('BMMailbox')) include(B1GMAIL_DIR . 'serverlib/mailbox.class.php');	
      // count mails
      $mailbox = _new('BMMailbox', array($thisUser->_id, $userRow['email'], $thisUser));
      $mailCount = $mailbox->GetMailCount(-1, true);
			// Templatevariabeln zuweisen
			$tpl->assign('s_usermail', $userRow['email']);
			$tpl->assign('zeitangabe', gmdate("d M Y, H:i:s", time()));
			$tpl->assign('in_refresh', "30");
			$tpl->assign('willkommenstext', sprintf($lang_user['newmailtext'],
			$mailCount));
			$tpl->assign('titel', sprintf(strip_tags($lang_user['newmailtext']),
			$mailCount));
			$tpl->display($this->_templatePath('checkmail.extern.tpl'));
		}
	}
	function BeforePageTabsAssign(&$pageTabs)
	{
		$pageTabs['checkmail'] = array(
					'faIcon'	=> 'fa-spinner',
					'link'		=> 'javascript://Checkmail" onclick="window.open(\'start.php?action=checkmail&sid='.session_id().'\',\'checkmail\',\'toolbar=no,width=190,height=280,resizable=yes,scrollbars=no\');" sid="',
					'text'		=> "CheckMail",
					'order'		=> 102
				);
	}
}
/**
 * register plugin
 */
$plugins->registerPlugin('modcheckmail');

?>