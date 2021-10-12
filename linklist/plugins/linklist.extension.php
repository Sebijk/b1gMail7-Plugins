<?php

class modlinklist extends BMPlugin
{
    public function modlinklist()
    {
        global $db;

        $this->name = 'Linkliste';
        $this->type = BMPLUGIN_DEFAULT;
        $this->author = 'Dennis Schneider (McUles), Home of the Sebijk.com';
        $this->web = 'http://www.sebijk.com/';
        $this->mail = 'sebijk@web.de';
        $this->version = '1.2-dev';
        $this->designedfor = '7.4.0';
    }

    public function Install()
    {
        global $db;
        $sql = $db->Query("CREATE TABLE `{pre}mod_linklist` (
					`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
					`uid` VARCHAR( 255 ) NOT NULL ,
					`link` VARCHAR( 255 ) NOT NULL ,
					`betreff` VARCHAR( 255 ) NOT NULL ,
					`beschreibung` VARCHAR( 255 ) ,
					`privat` ENUM( '0', '1' ) NOT NULL ,
					PRIMARY KEY ( `id` )
				) TYPE = MYISAM ;");
    }

    public function Uninstall()
    {
        global $db;
        $sql = $db->Query('DROP TABLE {pre}mod_linklist;');
    }

    public function OnReadLang(&$lang_user, &$lang_client, &$lang_custom, &$lang_admin, $lang)
    {
        if ($lang == 'deutsch') {
            $lang_user['linklist'] = 'Linkliste';
            $lang_user['linklist_private_link'] = 'Privater Link';
            $lang_user['linklist_address'] = 'Link-Adresse';
            $lang_user['linklist_entry'] = 'Eintragen';
            $lang_user['linklist_displayname'] = 'Angezeigter Name';
            $lang_user['linklist_add'] = 'Neuen Link hinzufügen';
            $lang_user['linklist_new'] = 'Neuer Link';
            $lang_user['link_added'] = 'Ihr Link wurde in die Datenbank eingetragen.';
        } else {
            $lang_user['linklist'] = 'Linklist';
            $lang_user['linklist_private_link'] = 'Private Link';
            $lang_user['linklist_address'] = 'Link Address';
            $lang_user['linklist_entry'] = 'Entry';
            $lang_user['linklist_displayname'] = 'Display Name';
            $lang_user['linklist_add'] = 'Add a new link';
            $lang_user['linklist_new'] = 'New Link';
            $lang_user['link_added'] = 'Your link was successfully added to database';
        }
    }

    public function BeforePageTabsAssign(&$pageTabs)
    {
        global $lang_user;
        $pageTabs['linklist'] = [
                    'faIcon' => 'fa-external-link',
                    'link' => 'start.php?action=linklist&sid=',
                    'text' => $lang_user['linklist'],
                    'order' => 510,
                ];
    }

    /**
     * user interface handler.
     *
     * @param string $file
     * @param string $action
     */
    public function FileHandler($file, $action)
    {
        global $tpl,$db, $userRow, $lang_user;

        if ($file != 'start.php') {
            return;
        }

        if ($file == 'start.php' && $_REQUEST['action'] == 'linklist') {
            $links = [];
            $sql = $db->Query("SELECT * FROM {pre}mod_linklist WHERE uid=? OR privat ='0' ORDER BY 'privat' DESC, 'betreff' ASC", $userRow['id']);
            while ($row = $sql->FetchArray()) {
                $links[$row['link']] = ['link' => $row['link'], 'betreff' => $row['betreff'], 'beschreibung' => $row['beschreibung'], 'privat' => $row['privat']];
            }

            $text = '<table>';
            foreach ($links as $entrys) {
                if ($entrys['privat'] == 1) {
                    $isprivat = '(Privater Link)';
                } else {
                    $isprivat = '';
                }
                $text .= '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>->&nbsp;<a href="deref.php?'.$entrys['link'].'" target="_blank">'.$entrys['betreff'].'</a></td><td>'.$isprivat.'</td></tr>';
                if ($entrys['beschreibung'] != '') {
                    $text .= '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$entrys['beschreibung'].'</td><td></td></tr>';
                }
            }
            $text .= '</table>';
            $tpl->assign('title', $lang_user['linklist']);
            $tpl->assign('msg', $text);
            //$tpl->assign('page', 'box.tpl');
            $tpl->assign('pageMenuFile', $this->_templatePath('linklist.sidebar.tpl'));
            $tpl->assign('pageContent', 'li/msg.tpl');
            //$tpl->assign('pageContent', $text);
            $tpl->display('li/index.tpl');
        }
        if ($file == 'start.php' && $action == 'newlink') {
            $text = '<form action="start.php?action=newlinksend&amp;sid='.session_id().'" method="POST">
						<table>
							<tr>
								<td>'.$lang_user['linklist_address'].'</td>
								<td width="255"><input type="text" name="link" value="" size="75" maxlength="255"></td>
							</tr>
							<tr>
								<td>'.$lang_user['linklist_displayname'].'</td>
								<td><input type="text" name="betreff" value="" size="75" maxlength="255"></td>
							</tr>
							<tr>
								<td>'.$lang_user['description'].'</td>
								<td><input type="text" name="beschreibung" value="" size="75" maxlength="255"></td>
							</tr>
							<tr>
								<td>'.$lang_user['linklist_private_link'].'</td>
								<td><input type="checkbox" name="privat"></td>
							</tr>
							<tr>
								<td></td>
								<td><input type="submit" value="'.$lang_user['linklist_entry'].'"></td>
							</tr>
						</table>
					</form>';
            $tpl->assign('title', $lang_user['linklist_new']);
            $tpl->assign('msg', $text);
            //$tpl->assign('page', 'box.tpl');
            $tpl->assign('pageMenuFile', $this->_templatePath('linklist.sidebar.tpl'));
            $tpl->assign('pageContent', 'li/msg.tpl');
            $tpl->display('li/index.tpl');
        }
        if ($file == 'start.php' && $action == 'newlinksend') {
            $link = filter_var($_REQUEST['link'], FILTER_SANITIZE_URL);
            $betreff = filter_var($_REQUEST['betreff'], FILTER_SANITIZE_STRING);
            $beschreibung = filter_var($_REQUEST['beschreibung'], FILTER_SANITIZE_STRING);
            if ($_REQUEST['privat'] == 'on') {
                $privat = 1;
            } else {
                $privat = 0;
            }

            $sql = $db->Query('INSERT INTO `{pre}mod_linklist` (`id`, `uid`, `link`, `betreff`, `beschreibung`, `privat`) VALUES (?,?,?,?,?,?);', '', $userRow['id'], $link, $betreff, $beschreibung, $privat);
            $tpl->assign('title', $lang_user['linklist_new']);
            $tpl->assign('msg', $lang_user['link_added']);
            $tpl->assign('pageContent', 'li/msg.tpl');
            $tpl->display('li/index.tpl');
        }
    }
}
$plugins->registerPlugin('modlinklist');
