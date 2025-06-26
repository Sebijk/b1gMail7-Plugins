<?php
/*
 * Simple Widgets Plugin for b1gMail
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

class SimpleWidgets extends BMPlugin 
{
   function SimpleWidgets($widget=false)
   {
      global $db;
      
      $this->widget=$widget;
      $this->widgetdata=array();
      
      $this->name             = 'SimpleWidgets';
      $this->author           = 'radada';
      $this->web              = '';
      $this->mail             = '';
      $this->version          = '1.0.1';
      $this->designedfor      = '7.1.0';
      
      
      if($this->widget===false)
      {
        $this->name             = 'SimpleWidgets';
        $this->type             = BMPLUGIN_DEFAULT;
        $this->update_url       = 'http://my.b1gmail.com/update_service/';
        $this->admin_pages      = true;
        $this->admin_page_title = 'SimpleWidgets';
      }
      else
      {
        $res=$db->Query('SELECT * FROM {pre}simplewidgets WHERE id=? LIMIT 1',$this->widget);
        $this->widgetdata=$res->FetchArray(MYSQL_ASSOC);
        $this->widgetdata['groups']=explode(',',$this->widgetdata['groups']);
        
        $this->name             = 'SimpleWidgets VirtualWidget ID'.$this->widget.' ('.$this->widgetdata['title'].')';
        $this->type             = BMPLUGIN_WIDGET;
        $this->widgetTitle      = $this->widgetdata['title'];
        $this->widgetTemplate   = 'siwi.simplewidget.tpl';
      }
   }
   
   function OnReadLang(&$lang_user, &$lang_client, &$lang_custom, &$lang_admin, $lang)
   {
      if($lang=='german' || $lang=='deutsch')
      {
        $lang_admin['siwi_activeated']='Aktiviert';
        $lang_admin['siwi_activeate']='Aktivieren';
        $lang_admin['siwi_deactiveate']='Deaktivieren';
        $lang_admin['siwi_nosmarty']='Nicht von Smarty parsen';
        $lang_admin['siwi_createwidget']='Widget anlegen';
        $lang_admin['siwi_content']='Inhalt (HTML/Smarty)';
        $lang_admin['siwi_allgroups']='Alle Gruppen';
        $lang_admin['siwi_startboard']='Start-Dashboard';
        $lang_admin['siwi_organizerboard']='Organizer-Dashboard';
      }
      else
      {
        $lang_admin['siwi_activeated']='Activated';
        $lang_admin['siwi_activeate']='Activate';
        $lang_admin['siwi_deactiveate']='Deactivate';
        $lang_admin['siwi_nosmarty']='Dont\'t parse by Smarty';
        $lang_admin['siwi_createwidget']='Create widget';
        $lang_admin['siwi_content']='Content (HTML/Smarty)';
        $lang_admin['siwi_allgroups']='All groups';
        $lang_admin['siwi_startboard']='Start dashboard';
        $lang_admin['siwi_organizerboard']='Organizer dashboard';
      }
   }
   
   function OnLoad()
   {
      global $db;
      if($this->widget===false)
      {
        $res=$db->Query("SELECT id FROM {pre}simplewidgets WHERE aktiv='yes' ORDER BY id");
        while(list($fid)=$res->FetchArray(MYSQL_NUM))
          $this->fork($fid);
      }
   }
   
   function isWidgetSuitable($for)
   {
      global $groupRow;
      
      if($this->widget===false) return false;
      
      if(!ADMIN_MODE && !in_array('0',$this->widgetdata['groups']) && !in_array($groupRow['id'],$this->widgetdata['groups'])) return false;
      
      if($for==BMWIDGET_START     && $this->widgetdata['suitable_start']=='yes')      return true;
      if($for==BMWIDGET_ORGANIZER && $this->widgetdata['suitable_organizer']=='yes')  return true;
      return false;
   }
   
   function renderWidget()
   {
      global $tpl;
      $content=$tpl->get_template_vars('siwi_content');
      if(!is_array($content)) $content=array();
      $content[$this->internal_name]=array('data'=>$this->widgetdata['content'],'nosmarty'=>($this->widgetdata['no_smarty']!='no'));
      $tpl->assign('siwi_content', $content);
   }
   
   function fork($id)
   {
    global $plugins;
		$pluginInstance = _new('SimpleWidgets',array($id));
		$pluginInstance->internal_name = 'SimpleWidgets_VirtualWidget_ID'.$id;
		$pluginInstance->installed = true;
			$pluginInstance->OnLoad();
		$pluginInfo = array(
			'type'			=> $pluginInstance->type,
			'name'			=> $pluginInstance->name,
			'version'		=> $pluginInstance->version,
			'author'		=> $pluginInstance->author,
			'id'			=> $pluginInstance->id,
			'instance'		=> $pluginInstance,
			'signature'		=> $signature,
			'packageName'	=> $packageName
		);
		
			$GLOBALS['plugins']->_plugins[$pluginInstance->internal_name] = $pluginInfo;
   }
   
   function Install()
   {
      global $db;
      $db->Query("CREATE TABLE `{pre}simplewidgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aktiv` enum('yes','no') NOT NULL,
  `suitable_start` enum('yes','no') NOT NULL,
  `suitable_organizer` enum('yes','no') NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `no_smarty` enum('yes','no') NOT NULL,
  `groups` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
  );");
      return true;
   }
   
   function Uninstall()
   {
      global $db;
      if($this->widget===false)
      {
        $db->Query("DROP TABLE `{pre}simplewidgets`");
      }
      else
      {
        $db->Query("UPDATE {pre}simplewidgets SET aktiv='no' WHERE id=?",$this->widget);
      }
      return true;
   }
   
   function AdminHandler()
   {
      global $tpl,$db;
      
      $tpl->assign('adminlink',$this->_adminLink(true));
      $tpl->assign('groups', BMGroup::GetSimpleGroupList());
      
      if($_REQUEST['do']=='edit')
      {
        if(isset($_REQUEST['save']))
        {
          if(!is_array($_REQUEST['groups'])) $_REQUEST['groups']=array();
          foreach($_REQUEST['groups'] AS $gk=>$gv)
            if(!(preg_match('#^[0-9]+$#',$gv)>0)) unset($_REQUEST['groups'][$gk]);
          
          $db->Query('UPDATE {pre}simplewidgets SET title=?,content=?,no_smarty=?,aktiv=?,suitable_start=?,suitable_organizer=?,groups=? WHERE id=?',$_REQUEST['title'],$_REQUEST['content'],(($_REQUEST['no_smarty']=='on')?'yes':'no'),(($_REQUEST['aktiv']=='on')?'yes':'no'),(($_REQUEST['suitable_start']=='on')?'yes':'no'),(($_REQUEST['suitable_organizer']=='on')?'yes':'no'),implode(',',$_REQUEST['groups']),$_REQUEST['id']);
          unset($_REQUEST['do']);
        }
        else
        {
          $res=$db->Query('SELECT id,title,content,no_smarty,aktiv,suitable_start,suitable_organizer,groups FROM {pre}simplewidgets WHERE id=? LIMIT 1',$_REQUEST['id']);
          if(list($id,$title,$content,$no_smarty,$aktiv,$suitable_start,$suitable_organizer,$groups)=$res->FetchArray(MYSQL_NUM))
          {
            $groups=array_flip(explode(',',$groups));
            foreach($groups AS $gk=>$gv)
              $groups[$gk]=true;
            
            $tpl->assign('simplewidget', array('id'=>$id,'title'=>$title,'content'=>$content,'no_smarty'=>($no_smarty=='yes'),'aktiv'=>($aktiv=='yes'),'suitable_start'=>($suitable_start=='yes'),'suitable_organizer'=>($suitable_organizer=='yes'),'groups'=>$groups));
            $tpl->assign('page', $this->_templatePath('siwi.acp.edit.tpl'));
          }
          else
          {
            unset($_REQUEST['do']);
          }
        }
      }
      
      if(isset($_REQUEST['add']))
      {
        if(!is_array($_REQUEST['groups'])) $_REQUEST['groups']=array();
        foreach($_REQUEST['groups'] AS $gk=>$gv)
          if(!(preg_match('#^[0-9]+$#',$gv)>0)) unset($_REQUEST['groups'][$gk]);
        
        $db->Query('INSERT INTO {pre}simplewidgets(title,content,no_smarty,aktiv,suitable_start,suitable_organizer,groups) VALUES(?,?,?,?,?,?,?)',$_REQUEST['title'],$_REQUEST['content'],(($_REQUEST['no_smarty']=='on')?'yes':'no'),(($_REQUEST['aktiv']=='on')?'yes':'no'),(($_REQUEST['suitable_start']=='on')?'yes':'no'),(($_REQUEST['suitable_organizer']=='on')?'yes':'no'),implode(',',$_REQUEST['groups']));
      }
      
      if(isset($_REQUEST['delete']))
      {
        $db->Query('DELETE FROM {pre}simplewidgets WHERE id=?',$_REQUEST['delete']);
      }
      
      if(isset($_REQUEST['activate']))
      {
        $db->Query('UPDATE {pre}simplewidgets SET aktiv=\'yes\' WHERE id=?',$_REQUEST['activate']);
      }
      
      if(isset($_REQUEST['deactivate']))
      {
        $db->Query('UPDATE {pre}simplewidgets SET aktiv=\'no\' WHERE id=?',$_REQUEST['deactivate']);
      }
      
      if(isset($_REQUEST['executeMassAction']))
      {
        $extIDs = array();
        foreach($_POST as $key=>$val)
          if(substr($key, 0, 13) == 'simplewidget_')
            $extIDs[] = (int)substr($key, 13);
        
        if(count($extIDs) > 0)
        {
          if($_REQUEST['massAction'] == 'delete')
          {
            $db->Query('DELETE FROM {pre}simplewidgets WHERE id IN(' . implode(',', $extIDs) . ')');
          }
          if($_REQUEST['massAction'] == 'activate')
          {
            $db->Query('UPDATE {pre}simplewidgets SET aktiv=\'yes\' WHERE id IN(' . implode(',', $extIDs) . ')');
          }
          if($_REQUEST['massAction'] == 'deactivate')
          {
            $db->Query('UPDATE {pre}simplewidgets SET aktiv=\'no\' WHERE id IN(' . implode(',', $extIDs) . ')');
          }
        }
      }
      
      
      if(!isset($_REQUEST['do']))
      {
        $simplewidgets=array();
        $res=$db->Query('SELECT id,title,aktiv,suitable_start,suitable_organizer FROM {pre}simplewidgets ORDER BY id');
        while(list($id,$title,$aktiv,$suitable_start,$suitable_organizer)=$res->FetchArray(MYSQL_NUM))
        {
          $simplewidgets[]=array('id'=>$id,'title'=>$title,'aktiv'=>($aktiv=='yes'),'suitable_start'=>($suitable_start=='yes'),'suitable_organizer'=>($suitable_organizer=='yes'));
        }
        $tpl->assign('simplewidgets', $simplewidgets);
        $tpl->assign('page', $this->_templatePath('siwi.acp.tpl'));
      }
   }
   
}

$plugins->registerPlugin('SimpleWidgets');

?>
