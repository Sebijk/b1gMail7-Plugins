<?php
/*
 * Homepage Plugin for b1gMail
 * (c) 2025 radada et al
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 */

class HomepagePlugin extends BMPlugin 
{
   function HomepagePlugin()
   {
      $this->name             = 'Homepage Plugin';
      $this->author           = 'radada';
      $this->web              = 'http://';
      $this->mail             = '';
      $this->version          = '1.1.5';
      $this->designedfor      = '7.1.0';
      $this->type             = BMPLUGIN_DEFAULT;
      $this->update_url       = 'http://my.b1gmail.com/update_service/';
      
      $this->admin_pages      = true;
      $this->admin_page_title = 'Homepage Plugin';
   }
   
   function OnReadLang(&$lang_user, &$lang_client, &$lang_custom, &$lang_admin, $lang)
   {
      if($lang=='german' || $lang=='deutsch')
      {
        $lang_admin['homepp_indexfiles']='Index-Dateien';
        $lang_admin['homepp_defaultmime']='Standard MIME-Typ';
        $lang_admin['homepp_dirlisting']='Verzeichnis-Auflistung global aktivieren';
        $lang_admin['homepp_mimetype']='MIME-Typ';
        $lang_admin['homepp_mimetypes']='MIME-Typen';
        $lang_admin['homepp_showinbrowser']='Im Browser anzeigen';
        $lang_admin['homepp_addmime']='MIME-Typ hinzufügen';
        $lang_admin['homepp_gopt_homepage']='Homepage aktivieren?';
        $lang_admin['homepp_gopt_adcode']='HTML-Code für Werbung:';
        $lang_admin['homepp_gopt_adposition']='HTML-Code Position:';
        $lang_admin['homepp_gopt_adposition_top']='oben';
        $lang_admin['homepp_gopt_adposition_bottom']='unten';
        
        
        $lang_user['userprefs_homepp'] = 'Homepage';
        $lang_user['prefs_d_userprefs_homepp'] = 'Einstellungen für Ihre Webdisk-Homepage bearbeiten';
        $lang_user['homepp_userprefs_title'] = 'Homepage';
        $lang_user['homepp_userprefs_useshare'] = 'Webdisk-Freigabe als Homepage';
        $lang_user['homepp_userprefs_noneshare'] = 'keine / deaktiviert';
        $lang_user['homepp_userprefs_dirlisting'] = 'Verzeichnis-Auflistung';
        $lang_user['homepp_userprefs_mainmail'] = 'Hauptadresse';
        $lang_user['homepp_userprefs_likemainmail'] = 'Wie Hauptadresse';
      }
      else
      {
        $lang_admin['homepp_indexfiles']='Index-files';
        $lang_admin['homepp_defaultmime']='Default MIME-type';
        $lang_admin['homepp_dirlisting']='Enable Directory Listing globally';
        $lang_admin['homepp_mimetype']='MIME-type';
        $lang_admin['homepp_mimetypes']='MIME-types';
        $lang_admin['homepp_showinbrowser']='Show in browser';
        $lang_admin['homepp_addmime']='Add MIME-type';
        $lang_admin['homepp_gopt_homepage']='Enable Homepage?';
        $lang_admin['homepp_gopt_adcode']='Ad HTML code:';
        $lang_admin['homepp_gopt_adposition']='HTML code position:';
        $lang_admin['homepp_gopt_adposition_top']='top';
        $lang_admin['homepp_gopt_adposition_bottom']='bottom';
        
        $lang_user['userprefs_homepp'] = 'Homepage';
        $lang_user['prefs_d_userprefs_homepp'] = 'Edit the  settings of your Webdisc Homepage';
        $lang_user['homepp_userprefs_title'] = 'Homepage';
        $lang_user['homepp_userprefs_useshare'] = 'Webdisc-share as Homepage';
        $lang_user['homepp_userprefs_noneshare'] = 'none / disabled';
        $lang_user['homepp_userprefs_dirlisting'] = 'Directory Listing';
        $lang_user['homepp_userprefs_mainmail'] = 'Main address';
        $lang_user['homepp_userprefs_likemainmail'] = 'Like main address';
      }
   }
   
   function AfterInit()
   {
      $this->Init();
      
      $searchstr='/share/index.php';
      if(substr($_SERVER['SCRIPT_FILENAME'],-strlen($searchstr))==$searchstr)// es existiert kein FileHandler in share/index.php
      {
        $this->GetURIPrefix();
        
        if($this->HandleUser())
          $this->HandleHomepage();
        else
          $this->HandleNoHomepage();
      }
   }
   
   function Init()
   {
      global $db,$lang_admin;
      $res=$db->Query("SELECT * FROM {pre}homepp_config LIMIT 1");
      $this->config=$res->FetchArray(MYSQL_ASSOC);
      $this->config['indexfiles']=explode(',',$this->config['indexfiles']);
      $this->config['dirlisting']=($this->config['dirlisting']=='yes');
      
      $this->RegisterGroupOption('homepp_Homepage', FIELD_CHECKBOX , $lang_admin['homepp_gopt_homepage']);
      $this->RegisterGroupOption('homepp_adcode', FIELD_TEXTAREA , $lang_admin['homepp_gopt_adcode']);
      $this->RegisterGroupOption('homepp_adposition', FIELD_RADIO , $lang_admin['homepp_gopt_adposition'],array('top'=>$lang_admin['homepp_gopt_adposition_top'],'bottom'=>$lang_admin['homepp_gopt_adposition_bottom']),'top');
   }
   
   
   function HandleUser() // User überprüfung, feststellen ob eine Homepage-Freigabe existiert
   {
      global $userMail,$userID,$thisUser,$userRow,$thisGroup,$groupRow,$db;
      $mySubdomain = strtolower($_SERVER['HTTP_HOST']);
      $myDomains = MyDomains();
      
      foreach($myDomains as $domain)
        if(strlen($domain) > 1 && substr($mySubdomain, strlen($domain)*-1) == $domain
          && substr($mySubdomain, strlen($domain)*-1-1, 1) == '.')
        {
          $userMail = substr_replace($mySubdomain, '@', strlen($domain)*-1-1, 1);
          break;
        } 
        
      if(!isset($userMail) || ($userID = BMUser::GetID($userMail)) == 0)
      {
        if(substr($_SERVER['REQUEST_URI'],strlen($this->URIPrefix),1)!='~')
          return false;
        
        foreach($myDomains as $domain)
          if(strlen($domain) > 1 && ( $mySubdomain==$domain || (substr($mySubdomain, strlen($domain)*-1) == $domain
          && substr($mySubdomain, strlen($domain)*-1-1, 1) == '.')))
          {
            $username=substr($_SERVER['REQUEST_URI'],strlen($this->URIPrefix)+1);
            $username=substr($username,0,strpos($username.'/','/'));
            if(strlen($username)>0)
              $userMail = $username.'@'.$domain;
            break;
          } 
        if(!isset($userMail) || ($userID = BMUser::GetID($userMail)) == 0)
          return false;
        else
        {
          $this->URIPrefix.='~'.$username.'/';
          if(strlen($this->URIPrefix) > strlen($_SERVER['REQUEST_URI']))
            $this->ShowMovedPermanently($_SERVER['REQUEST_URI'].'/');
        }
      }
      $_REQUEST['user']=$userMail;
      $thisUser = _new('BMUser', array($userID));
      $userRow = $thisUser->Fetch();
      $thisGroup = $thisUser->GetGroup();
      $groupRow = $thisGroup->Fetch();
      
      $this->ValidUserConfig($thisUser);
      
      if($userRow['gesperrt']!='no' || !$this->GetGroupOptionValue('homepp_Homepage') || $groupRow['share']=='no' || $thisUser->GetPref('homepp_share')=='-1')
        return false;
      
      $res=$db->Query('SELECT homepp_share FROM {pre}aliase WHERE user=? AND email=? LIMIT 1',$userID,$userMail);
      if((list($alias_share)=$res->FetchArray(MYSQL_NUM))!==false)
      {// -1 -> share deaktiviert für alias ; 0 -> share wie hauptadresse ; >0 -> id des share-ordners
        if($alias_share<0)
          return false;
        if($alias_share>0)
        {
          $this->sharefolder=$alias_share;
          return true;
        }
      }
      
      $res = $db->Query('SELECT id FROM {pre}diskfolders WHERE share=\'yes\' AND id=? AND user=? LIMIT 1',$thisUser->GetPref('homepp_share'),$userID);
      if($res->RowCOunt()==0)
            return false;
            
      list($this->sharefolder) = $res->FetchArray(MYSQL_NUM);
      
      return true;
   }
   
   function HandleHomepage()// Homepage-Freigabe existiert
   {
      global $userMail,$userID,$thisUser,$userRow,$thisGroup,$groupRow,$db;
      if(!class_exists('BMWebdisk'))
        include_once('../serverlib/webdisk.class.php');
      $webdisk 		= _new('BMWebdisk', array($userID));
      
      if((list($elementID,$elementType)=$this->HandlePath($webdisk))===false)
      {
        $this->ShowNotFound($_SERVER['REQUEST_URI']);
      }
      else
      {
        if((list($pass,$title)=$this->IsProtected($webdisk,$elementID))!==false)
          $this->HTTPAuth($pass,$title);
        
        if($elementType==WEBDISK_ITEM_FILE)
        {
          $this->HandleShowFile($webdisk,$elementID);
        }
        else
        {
          $this->HandleDirectory($webdisk,$elementID);
        }
      }
      
      exit();
   }
   
   function GetURIPrefix() // URI bis zu share-Verzeichnis, wichtig falls Sub-/Domain nicht direkt darauf zeigt
   {
      $path=$_SERVER['SCRIPT_NAME'];
      $path=substr($path,0, strrpos($path,'/')+1);
      $this->URIPrefix=$path;
   }
   
   function HandlePath($webdisk)// Vergleich URI mit Webdisk, liefern des entsprechenden Objektes
   {      
      $rel_uri=$_SERVER['REQUEST_URI'];
      if(($pos=strpos($rel_uri,'?'))!==false) $rel_uri=substr($rel_uri,0,$pos);
      if(($pos=strpos($rel_uri,'#'))!==false) $rel_uri=substr($rel_uri,0,$pos);
      
      if(substr($rel_uri,0,strlen($this->URIPrefix))==$this->URIPrefix)
        $rel_uri=substr($rel_uri,strlen($this->URIPrefix)-1);
      $rel_uri=$this->URLDecodePath($rel_uri);
      
      $folderPath 	= $webdisk->GetFolderPath($this->sharefolder);
      $folderPath_str='';
      foreach($folderPath AS $folder)
        $folderPath_str.=$folder['title'].'/';
      
      if((list($elementID,$elementType)=$webdisk->ParsePath(str_replace('//','/',$folderPath_str.$rel_uri), true))===false)
        return false;
      
      return array($elementID,$elementType);
   }
   
   function HandleShowFile($webdisk,$fileID) // Datei an Browser senden
   {
      global $groupRow,$userRow,$db;
      
      $fileInfo=$webdisk->GetFileInfo($fileID);
      list($mimetype,$show_in_browser)=$this->GetMimeType(array_pop(explode('.',$fileInfo['dateiname'])));
      
      if($groupRow['traffic']==-1 || ($groupRow['traffic'] > 0 && ($userRow['traffic_down']+$userRow['traffic_up']+$fileInfo['size']) <= $groupRow['traffic']))
      {
        // ok
        $speedLimit = $groupRow['wd_member_kbs'] <= 0 ? -1 : $groupRow['wd_member_kbs'];
        $db->Query('UPDATE {pre}users SET traffic_down=traffic_down+? WHERE id=?',
          $fileInfo['size'],
          $userRow['id']);
        
        // bannercode
        $adcode_code=$this->GetGroupOptionValue('homepp_adcode');
        $adcode_position=$this->GetGroupOptionValue('homepp_adposition');
        $adcode_doshow=(strlen($adcode_code)>0 && $show_in_browser && $mimetype=='text/html');
        
        // send file
        header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
        header('Last-Modified: '.date('r',$fileInfo['modified']));
        header('Content-Type: ' . $mimetype);
        header('Content-Length: ' . ($fileInfo['size']+  (($adcode_doshow)?strlen($adcode_code):0)  ));
        header('Content-Disposition: ' . (($show_in_browser) ? 'inline' : 'attachment') . '; filename="' . addslashes($fileInfo['dateiname']) . '"');
        Add2Stat('wd_down', ceil($fileInfo['size']/1024));
        
        if($adcode_doshow && $adcode_position=='top') echo $adcode_code;
        SendFile(DataFilename($fileInfo['id'], 'dsk'), $speedLimit);
        if($adcode_doshow && $adcode_position=='bottom') echo $adcode_code;
        
        exit();
      }
      else
      {
        $this->ShowTrafficExceeded();
      }
   }
   
   function HandleDirectory($webdisk,$folderID) // falls URI auf ein Verzeichniss verweisst
   {
      global $tpl,$thisUser;
      if(substr($_SERVER['REQUEST_URI'],-1)!='/')
      {
        $this->ShowMovedPermanently($_SERVER['REQUEST_URI'].'/');
      }
      else
      {
        $elements=$webdisk->GetFolderContent($folderID);
        foreach($elements AS $element)
        {
          if($element['type']==WEBDISK_ITEM_FILE && in_array($element['title'],$this->config['indexfiles']))
          {
            $this->HandleShowFile($webdisk,$element['id']);
            exit();
          }
        }
        if($this->config['dirlisting'] && $thisUser->GetPref('homepp_dirlisting')=='yes')
        {
          $this->ShowDirectoryListing($webdisk,$folderID);
        }
        else
        {
          $this->ShowForbidden($_SERVER['REQUEST_URI']);
        }
      }
   }
   
   function ShowDirectoryListing($webdisk,$folderID) // DirectoryListing, sofern von User als auch von Admin aktiviert
   {
      global $tpl,$thisUser;
      $elements=array();
      if($folderID!=$this->sharefolder)
      {
        $new_element=array();
        $new_element['isparent']=true;
        $new_element['isfile']=false;
        $new_element['modified']=0;
        $new_element['size']=0;
        $uri='/'.trim($_SERVER['REQUEST_URI'],'/');
        $new_element['file']=substr($uri,0,strrpos($uri,'/')+1);
        $elements[]=$new_element;
      }
      foreach($webdisk->GetFolderContent($folderID) AS $element)
      {
        $new_element=array();
        $new_element['isparent']=false;
        $new_element['isfile']=($element['type']==WEBDISK_ITEM_FILE);
        $new_element['file']=urlencode($element['title']);
        $new_element['title']=$element['title'];
        if(!$new_element['isfile'])$new_element['file'].='/';
        $new_element['modified']=$element['modified'];
        $new_element['size']=$element['size'];
        $elements[]=$new_element;
      }
      header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
      $tpl->assign('elements',$elements);
      $tpl->assign('path',$_SERVER['REQUEST_URI']);
      $tpl->display($this->_templatePath('homepp.dirlisting.tpl'));
      exit();
   }
   
   function HTTPAuth($pass,$title) // Login, falls Homepage-Freigabe ein Passwort besitzt
   {
      if($_SERVER['PHP_AUTH_PW']!=$pass)
      {
        header('WWW-Authenticate: Basic realm="'.$title.'"');
        header($_SERVER['SERVER_PROTOCOL'].' 401 Unauthorized');
        exit();
      }
   }
   
   function ShowNotFound($path='') // 404-Fehlerseite ausgeben
   {
      global $tpl;
      header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
      $tpl->assign('path',$path);
      $tpl->display($this->_templatePath('homepp.notfound.tpl'));
      exit();
   }
   
   function ShowTrafficExceeded() // Fehlerseite für keinen/zu wenig verbleibenden Traffic ausgeben
   {
      global $tpl;
      header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
      $tpl->display($this->_templatePath('homepp.traffic.tpl'));
      exit();
   }
   
   function ShowForbidden($path='') // Fehlerseite für Verzeichnisse bei deaktiviertem DirectoryListing
   {
      global $tpl;
      header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
      $tpl->assign('path',$path);
      $tpl->display($this->_templatePath('homepp.forbidden.tpl'));
      exit();
   }
   
   function ShowMovedPermanently($path) // Umleitung für den Fall, dass URI auf ein Verzeichniss verweisst, aber ein Slash am Ende fehlt
   {
      global $tpl;
      header($_SERVER['SERVER_PROTOCOL'].' 301 Moved Permanently');
      header('Location: '.$path);
      $tpl->assign('path',$path);
      $tpl->display($this->_templatePath('homepp.movedpermanently.tpl'));
      exit();
   }
   
   function HandleNoHomepage() // Falls es kein Homepageaufruf ist. Prüfung ob index.php direkt oder als 404-Fehlerseite aufgerufen wurde
   {
      $uri=$_SERVER['REQUEST_URI'];
      if(($pos=strpos($uri,'?'))!==false) $uri=substr($uri,0,$pos);
      if(($pos=strpos($uri,'#'))!==false) $uri=substr($uri,0,$pos);
      $posible_uri=array(
                      $_SERVER['SCRIPT_NAME'],
                      substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'],'/')),
                      substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'],'/')+1),
                      $this->URIPrefix,
                      $this->URIPrefix.basename($_SERVER['SCRIPT_NAME'])
                    );
                    
      if(!in_array($uri,$posible_uri))
      {
        $this->ShowNotFound($_SERVER['REQUEST_URI']);
      }
      else
      {
        $this->ParseURIVariables();
        header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
        $this->HandleHomepageFolderProtection();
      }
   }
   
   function HandleHomepageFolderProtection()
   {
      global $db;
      
      if( isset($_REQUEST['user']) && isset($_REQUEST['id']) && $_REQUEST['id']!='0' && in_array($_REQUEST['action'],array('getFolder','getFile')) && ($userID = BMUser::GetID( $_REQUEST['user'] )) !=0)
      {
        $thisUser = _new('BMUser', array($userID));
        $block=false;
        
        $folderid=$_REQUEST['id'];
        if($_REQUEST['action']=='getFile')
        {
          $res=$db->Query("SELECT ordner FROM {pre}diskfiles WHERE id=? AND user=? LIMIT 1",$_REQUEST['id'],$userID);
          list($folderid)=$res->FetchArray(MYSQL_NUM);
        }
        
        $protectedFolders=array();
        if($thisUser->GetPref('homepp_share') > 0)
          $protectedFolders[]=$thisUser->GetPref('homepp_share');
          
        $res=$db->Query("SELECT homepp_share FROM {pre}aliase WHERE user=?",$userID);
        while(list($share)=$res->FetchArray(MYSQL_NUM))
          if($share>0) $protectedFolders[]=$share;
          
        while($folderid>0)
        {
          if(in_array($folderid,$protectedFolders) )
          {
            $block=true;
            break;
          }
          $res=$db->Query("SELECT parent FROM {pre}diskfolders WHERE id=? AND user=? LIMIT 1",$folderid,$userID);
          if((list($folderid)=$res->FetchArray(MYSQL_NUM))===false)
            break;
        }
        
        if($block)
        {
          if($_REQUEST['action']=='getFile')
            die('Permission denied');
          else
            $_REQUEST['id']='0';
        }
      }
   }
   
   function IsProtected($webdisk,$elementID)
   {
      if(($fileInfo=$webdisk->GetFileInfo($elementID))!==false)
        $elementID=$fileInfo['ordner'];
      while($webdisk->IsFolderChildOf($elementID, $this->sharefolder))
      {
        $folderInfo=$webdisk->GetFolderInfo($elementID);
        if($folderInfo['share']=='yes' && $folderInfo['share_pw']!='')
          return array($folderInfo['share_pw'],$folderInfo['titel']);
        $elementID=$folderInfo['parent'];
      }
      return false;
   }
   
   function ParseURIVariables() // Auch bei "~USERNAME" soll auch die normale Webdisk-Freigabe funktionieren ; Variablen stehen ansonsten nicht zur Verfügung
   {
      if(($pos=strpos($_SERVER['REQUEST_URI'],'?'))!==false)
      {
        $urivars=substr($_SERVER['REQUEST_URI'],$pos+1);
        if(strlen($urivars)>0)
        {
          $urivars=explode('&',$urivars);
          foreach($urivars AS $urivar)
          {
            list($k,$v)=explode('=',$urivar);
            $k=urldecode($k);
            $_REQUEST[$k]=$_GET[$k]=urldecode($v);
          }
        }
      }
   }
   
   function ValidUserConfig(&$thisUser) // Prüfen/setzen einer gültigen Konfiguration für einen User
   {
      global $db;
      if(($share=$thisUser->GetPref('homepp_share'))===false)
      {
        $thisUser->SetPref('homepp_share','-1');
      }
      elseif($share!='-1')
      {
        $res = $db->Query('SELECT count(*) FROM {pre}diskfolders WHERE share=\'yes\' AND id=? AND user=?',$share,$thisUser->_id);
        list($num)=$res->FetchArray(MYSQL_NUM);
        if($num==0) $thisUser->SetPref('homepp_share','-1');
      }
      if(!in_array($thisUser->GetPref('homepp_dirlisting'),array('yes','no')))
        $thisUser->SetPref('homepp_dirlisting','yes');
      $db->QUery("UPDATE `{pre}aliase` AS A LEFT JOIN `{pre}diskfolders` AS D ON ( A.user = D.user AND A.homepp_share = D.id ) SET A.homepp_share = '0' WHERE A.homepp_share NOT IN ('-1','0') AND A.user = ? AND (D.share IS NULL OR D.share != 'yes')",$thisUser->_id);
   }
   
   function URLDecodePath($path) // URLDecode für Pfade, z.B. URI, falls Leerzeichen oder ähnlich in Dateiname
   {
      $path=explode('/',$path);
      $path=array_map('urldecode',$path);
      return implode('/',$path);
   }
   function URLEncodePath($path)
   {
      $path=explode('/',$path);
      $path=array_map('urlencode',$path);
      return implode('/',$path);
   }
   
   function GetMimeType($fileext) // Heraussuchen des MIME-Typen und ob dieser im Browser gezeigt werden soll
   {
      global $db;
      $res = $db->Query("SELECT mimetype,show_in_browser FROM {pre}homepp_mimetypes WHERE extensions REGEXP concat('(^|,)',?,'(,|$)')",$fileext);
      if($res->RowCOunt()==0)
            return array($this->config['defaultmime'],false);
      list($mimetype,$show_in_browser)=$res->FetchArray(MYSQL_NUM);
      return array($mimetype,$show_in_browser=='yes');
   }
   
   function FileHandler($file, $action) // Für die User-EInstellungen
   {
      if($file=='prefs.php' && $this->GetGroupOptionValue('homepp_Homepage'))
      {
         $GLOBALS['prefsItems']['userprefs_homepp'] = true;
         $GLOBALS['prefsImages']['userprefs_homepp'] = 'plugins/templates/images/homepp_ico48.gif';
         $GLOBALS['prefsIcons']['userprefs_homepp'] = 'plugins/templates/images/homepp_ico16.gif';
      }
   }
   
   function UserPrefsPageHandler($action) // User-EInstellungen
   {
      global $tpl,$thisUser,$db,$userRow;
      if($action != 'userprefs_homepp' || !$this->GetGroupOptionValue('homepp_Homepage'))
         return(false);
      
      if($_REQUEST['do']=='save')
      {
        $thisUser->SetPref('homepp_share',$_REQUEST['homepp_share']);
        $thisUser->SetPref('homepp_dirlisting',(($_REQUEST['homepp_dirlisting']=='on')?'yes':'no'));
        if(is_array($_REQUEST['homepp_alias_share']))
          foreach($_REQUEST['homepp_alias_share'] AS $id=>$share)
            $db->Query('UPDATE {pre}aliase SET homepp_share=? WHERE id=? AND user=?',$share,$id,$userRow['id']);
      }
      
      $this->ValidUserConfig($thisUser);
      
      include_once('./serverlib/webdisk.class.php');
      $webdisk  = _new('BMWebdisk', array($userRow['id']));
      
      $res=$db->Query("SELECT id FROM {pre}diskfolders WHERE share='yes' AND user=?",$userRow['id']);
      $shares=array();
      while(list($share_id)=$res->FetchArray(MYSQL_NUM))
      {
        $share=array('id'=>$share_id,'path'=>'');
        $folderPath 	= $webdisk->GetFolderPath($share_id);
        foreach($folderPath AS $folder)
          $share['path'].=$folder['title'].'/';
        $share['path']=trim($share['path'],'/');
        $shares[]=$share;
      }
      
      $res=$db->Query("SELECT id,email,homepp_share FROM {pre}aliase WHERE user=?",$userRow['id']);
      $aliase=array();
      while(list($alias_id,$alias_email,$alias_share)=$res->FetchArray(MYSQL_NUM))
      {
        $alias=array('id'=>$alias_id,'email'=>$alias_email,'share'=>$alias_share);
        $aliase[]=$alias;
      }
      
      $tpl->assign('homepp_share',$thisUser->GetPref('homepp_share'));
      $tpl->assign('shares',$shares);
      $tpl->assign('aliase',((count($aliase)>0)?$aliase:false));
      $tpl->assign('mainmail',$userRow['email']);
      $tpl->assign('homepp_dirlisting',$thisUser->GetPref('homepp_dirlisting'));
      $tpl->assign('homepp_global_dirlisting',$this->config['dirlisting']);
      $tpl->assign('pageContent', $this->_templatePath('homepp.prefspage.tpl'));
      $tpl->display('li/index.tpl');
      return(true);
   }
   
   function AdminHandler()
   {
      global $tpl,$db;
      
      $tpl->assign('adminlink',$this->_adminLink(true));
      
      if($_REQUEST['do']=='edit')
      {
        if(isset($_REQUEST['save']))
        {
          $db->Query('UPDATE {pre}homepp_mimetypes SET extensions=?,mimetype=?,show_in_browser=? WHERE id=?',$_REQUEST['extensions'],$_REQUEST['mimetype'],(($_REQUEST['showinbrowser']=='on')?'yes':'no'),$_REQUEST['id']);
          unset($_REQUEST['do']);
        }
        else
        {
          $res=$db->Query('SELECT id,extensions,mimetype,show_in_browser FROM {pre}homepp_mimetypes WHERE id=? LIMIT 1',$_REQUEST['id']);
          if(list($id,$extensions,$mimetype,$showinbrowser)=$res->FetchArray(MYSQL_NUM))
          {
            $tpl->assign('mimetype', array('id'=>$id,'extensions'=>$extensions,'mimetype'=>$mimetype,'showinbrowser'=>($showinbrowser=='yes')));
            $tpl->assign('page', $this->_templatePath('homepp.acp.editmime.tpl'));
          }
          else
          {
            unset($_REQUEST['do']);
          }
        }
      }
      
      if(isset($_REQUEST['add']))
      {
        $db->Query('INSERT INTO {pre}homepp_mimetypes(extensions,mimetype,show_in_browser) VALUES(?,?,?)',$_REQUEST['extensions'],$_REQUEST['mimetype'],(($_REQUEST['showinbrowser']=='on')?'yes':'no'));
      }
      
      if(isset($_REQUEST['delete']))
      {
        $db->Query('DELETE FROM {pre}homepp_mimetypes WHERE id=?',$_REQUEST['delete']);
      }
      
      if(isset($_REQUEST['executeMassAction']))
      {
        $extIDs = array();
        foreach($_POST as $key=>$val)
          if(substr($key, 0, 9) == 'mimetype_')
            $extIDs[] = (int)substr($key, 9);
        
        if(count($extIDs) > 0)
        {
          if($_REQUEST['massAction'] == 'delete')
          {
            $db->Query('DELETE FROM {pre}homepp_mimetypes WHERE id IN(' . implode(',', $extIDs) . ')');
          }
        }
      }
      
      
      if(isset($_REQUEST['saveconfig']))
      {
        $db->Query('UPDATE {pre}homepp_config SET indexfiles=?,dirlisting=?,defaultmime=?',$_REQUEST['indexfiles'],(($_REQUEST['dirlisting']=='on')?'yes':'no'),$_REQUEST['defaultmime']);
        $this->Init();
      }
      
      
      if(!isset($_REQUEST['do']))
      {
        $mimetypes=array();
        $res=$db->Query('SELECT id,extensions,mimetype,show_in_browser FROM {pre}homepp_mimetypes ORDER BY mimetype');
        while(list($id,$extensions,$mimetype,$showinbrowser)=$res->FetchArray(MYSQL_NUM))
        {
          $mimetypes[]=array('id'=>$id,'extensions'=>$extensions,'mimetype'=>$mimetype,'showinbrowser'=>($showinbrowser=='yes'));
        }
        $tpl->assign('mimetypes', $mimetypes);
        $tpl->assign('config', array('indexfiles'=>implode(',',$this->config['indexfiles']),'dirlisting'=>$this->config['dirlisting'],'defaultmime'=>$this->config['defaultmime']));
        $tpl->assign('page', $this->_templatePath('homepp.acp.tpl'));
      }
   }
   
   function Install()
   {
      global $db;
      $db->Query("CREATE TABLE `{pre}homepp_mimetypes` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `extensions` varchar(255) NOT NULL,
                    `mimetype` varchar(255) NOT NULL,
                    `show_in_browser` enum('yes','no') NOT NULL,
                    PRIMARY KEY (`id`)
                  );");
      $db->Query("INSERT INTO `{pre}homepp_mimetypes` (`extensions`, `mimetype`, `show_in_browser`) VALUES( 'txt', 'text/plain', 'yes'),( 'htm,html,php', 'text/html', 'yes'),( 'css', 'text/css', 'yes'),( 'js', 'application/javascript', 'yes'),( 'json', 'application/json', 'yes'),( 'xml', 'application/xml', 'yes'),( 'swf', 'application/x-shockwave-flash', 'yes'),( 'flv', 'video/x-flv', 'no'),( 'png', 'image/png', 'yes'),( 'jpe,jpeg,jpg', 'image/jpeg', 'yes'),( 'gif', 'image/gif', 'yes'),( 'bmp', 'image/bmp', 'yes'),( 'ico', 'image/vnd.microsoft.icon', 'yes'),( 'tiff,tif', 'image/tiff', 'yes'),( 'svg,svgz', 'image/svg+xml', 'yes'),( 'zip', 'application/zip', 'no'),( 'rar', 'application/x-rar-compressed', 'no'),( 'exe,msi', 'application/x-msdownload', 'no'),( 'cab', 'application/vnd.ms-cab-compressed', 'no'),( 'mp3', 'audio/mpeg', 'no'),( 'qt,mov', 'video/quicktime', 'no'),( 'pdf', 'application/pdf', 'yes'),( 'psd', 'image/vnd.adobe.photoshop', 'no'),( 'ai,eps,ps', 'application/postscript', 'no'),( 'doc', 'application/msword', 'no'),( 'rtf', 'application/rtf', 'no'),( 'xls', 'application/vnd.ms-excel', 'no'),( 'ppt', 'application/vnd.ms-powerpoint', 'no'),( 'odt', 'application/vnd.oasis.opendocument.text', 'no'),( 'ods', 'application/vnd.oasis.opendocument.spreadsheet', 'no');");
      $db->Query("CREATE TABLE `{pre}homepp_config` (
                    `indexfiles` varchar(255) NOT NULL,
                    `dirlisting` enum('yes','no') NOT NULL,
                    `defaultmime` varchar(255) NOT NULL
                  )");
      $db->Query("INSERT INTO `{pre}homepp_config` (`indexfiles`, `dirlisting`, `defaultmime`) VALUES('index.html,index.htm', 'yes', 'application/octet-stream');");
      $db->Query("ALTER TABLE `{pre}aliase` ADD `homepp_share` INT( 11 ) DEFAULT '0' NOT NULL;");
      return true;
   }
   
   function Uninstall()
   {
      global $db;
      $db->Query("DELETE FROM `{pre}userprefs` WHERE `key` IN ('homepp_share','homepp_dirlisting')");
      $db->Query("DROP TABLE `{pre}homepp_config`,`{pre}homepp_mimetypes`");
      $db->Query("ALTER TABLE `{pre}aliase` DROP `homepp_share` ");
      return true;
   }
}

$plugins->registerPlugin('HomepagePlugin');

?>
