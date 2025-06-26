<?php
/*
 * Widget Publisher Plugin for b1gMail
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

class WidgetPublisher extends BMPlugin 
{
   function WidgetPublisher()
   {
      $this->name             = 'WidgetPublisher';
      $this->author           = 'radada';
      $this->web              = '';
      $this->mail             = '';
      $this->version          = '1.0.0';
      $this->designedfor      = '7.1.0';
      $this->type             = BMPLUGIN_DEFAULT;
      $this->update_url       = 'http://my.b1gmail.com/update_service/';
      $this->admin_pages      = true;
      $this->admin_page_title = 'WidgetPublisher';
   }
   
   function OnReadLang(&$lang_user, &$lang_client, &$lang_custom, &$lang_admin, $lang)
   {
      if($lang=='german' || $lang=='deutsch')
      {
        $lang_admin['wipu_publishwidget']='Widget verteilen';
        $lang_admin['wipu_finished']='Verteilung abgeschlossen!';
        $lang_admin['wipu_widgetselect']='Widget-Auswahl';
        $lang_admin['wipu_submitconfirm']='Abhängig von der Anzahl der Benutzer kann diese Aktion einige Zeit dauern. Trotzdem starten?';
        $lang_admin['wipu_allgroups']='Alle Gruppen';
        $lang_admin['wipu_selgroups']='Ausgewählte Gruppen';
        $lang_admin['wipu_leftcol']='Linke Spalte';
        $lang_admin['wipu_middlecol']='Mittlere Spalte';
        $lang_admin['wipu_rightcol']='Rechte Spalte';
        $lang_admin['wipu_top']='Oben';
        $lang_admin['wipu_bottom']='Unten';
        $lang_admin['wipu_horizontal']='Horizontal';
        $lang_admin['wipu_vertical']='Vertikal';
        $lang_admin['wipu_forcepos']='Position erzwingen';
        $lang_admin['wipu_addtodefault']='Zu Standard-Layout hinzufügen';
        $lang_admin['wipu_widgetpos']= 'Widget Position';
        $lang_admin['wipu_startpublish']= 'Verteilung starten';
        $lang_admin['wipu_startboard']='Start-Dashboard';
        $lang_admin['wipu_organizerboard']='Organizer-Dashboard';
      }
      else
      {
        $lang_admin['wipu_publishwidget']='Publish Widget';
        $lang_admin['wipu_finished']='Publishing finished!';
        $lang_admin['wipu_widgetselect']='Widget selection';
        $lang_admin['wipu_submitconfirm']='Depending on the number of users, this action can take some time. Start anyway?';
        $lang_admin['wipu_allgroups']='All groups';
        $lang_admin['wipu_selgroups']='Selected groups';
        $lang_admin['wipu_leftcol']='Left column';
        $lang_admin['wipu_middlecol']='Middle column';
        $lang_admin['wipu_rightcol']='right column';
        $lang_admin['wipu_top']='Top';
        $lang_admin['wipu_bottom']='Bottom';
        $lang_admin['wipu_horizontal']='Horizontal';
        $lang_admin['wipu_vertical']='Vertical';
        $lang_admin['wipu_forcepos']='Force position';
        $lang_admin['wipu_addtodefault']= 'Add to default layout';
        $lang_admin['wipu_widgetpos']= 'Widget position';
        $lang_admin['wipu_startpublish']= 'Start publishing';
        $lang_admin['wipu_startboard']='Start dashboard';
        $lang_admin['wipu_organizerboard']='Organizer dashboard';
      }
   }
   
   function WidgetOrderToColArray($widgetOrder)
   {
      $return_array=array(array(),array(),array());
      $rows=explode(';',$widgetOrder);
      foreach($rows AS $row)
      {
        $cols=explode(',',$row);
        for($i=0;$i<3;$i++)
          if(trim($cols[$i])!='') $return_array[$i][]=$cols[$i];
      }
      return $return_array;
   }
   
   function ColArrayToWidgetOrder($colArray)
   {
      $widgetOrder=array();
      for($i=0;$i<max(count($colArray[0]),count($colArray[1]),count($colArray[2]));$i++)
      {
        $widgetOrder[]=implode(',',array($colArray[0][$i],$colArray[1][$i],$colArray[2][$i]));
      }
      $widgetOrder=implode(';',$widgetOrder);
      return $widgetOrder;
   }
   
   function AddWidget($widgetOrder,$widget,$hpos=0,$vpos='bottom',$forcepos=false)
   {
      $colArray=$this->WidgetOrderToColArray($widgetOrder);
      for($c=0;$c<3;$c++)
        for($r=0;$r<count($colArray[$c]);$r++)
          if($colArray[$c][$r]==$widget)
          {
            if(!$forcepos)  return $widgetOrder;
            array_splice($colArray[$c],$r,1);
            $r--;
          }
      
      if(!array_key_exists($hpos,$colArray))
        $hpos=0;
      if($vpos=='bottom')
        array_push($colArray[$hpos],$widget);
      else
        array_unshift($colArray[$hpos],$widget);
        
      $widgetOrder=$this->ColArrayToWidgetOrder($colArray);
      return $widgetOrder;
   }
   
   function AddWidgets($widgetOrder,$widgets,$hpos=0,$vpos='bottom',$forcepos=false)
   {
      foreach($widgets AS $widget)
        $widgetOrder=$this->AddWidget($widgetOrder,$widget,$hpos,$vpos,$forcepos);
      return $widgetOrder;
   }
   
   function AdminHandler()
   {
      global $tpl,$db,$plugins,$bm_prefs;
      
      $start_widgets= $plugins->getWidgetsSuitableFor(BMWIDGET_START);
      $organizer_widgets= $plugins->getWidgetsSuitableFor(BMWIDGET_ORGANIZER);
      
      $showinfo=false;
      
      if(isset($_REQUEST['publish']))
      {
        $set_start_widgets=array();
        $set_organizer_widgets=array();
        if(is_array($_REQUEST['startwidgets']))
          foreach($_REQUEST['startwidgets'] AS $widget)
            if(in_array($widget,$start_widgets)) $set_start_widgets[]=$widget;
        if(is_array($_REQUEST['organizerwidgets']))
          foreach($_REQUEST['organizerwidgets'] AS $widget)
            if(in_array($widget,$organizer_widgets)) $set_organizer_widgets[]=$widget;
        
        if( (count($set_start_widgets)+count($set_organizer_widgets)) >0 )
        {
          @set_time_limit(0);
          if($_REQUEST['groups']=='all')
          {
            if(isset($_REQUEST['addtotemplates']))
            {
              $db->Query("UPDATE {pre}prefs SET widget_order_start=?,widget_order_organizer=?",
                $this->AddWidgets($bm_prefs['widget_order_start'],$set_start_widgets,$_REQUEST['hpos'],$_REQUEST['vpos'],$_REQUEST['forcepos']),
                $this->AddWidgets($bm_prefs['widget_order_organizer'],$set_organizer_widgets,$_REQUEST['hpos'],$_REQUEST['vpos'],$_REQUEST['forcepos'])
              );
            }
            $res=$db->Query("SELECT {pre}users.id FROM `{pre}users` LEFT OUTER JOIN {pre}userprefs ON ({pre}users.id={pre}userprefs.userid AND {pre}userprefs.key IN ('widgetOrderStart','widgetOrderOrganizer') ) GROUP BY {pre}users.id HAVING count({pre}userprefs.key)>0 ");
          }
          else
          {
            $res=$db->Query("SELECT {pre}users.id FROM `{pre}users` WHERE `gruppe` IN ?",$_REQUEST['selgroups']);
          }
          while(list($uid)=$res->FetchArray(MYSQL_NUM))
            foreach(array('widgetOrderStart','widgetOrderOrganizer') AS $widgetOrderType)
            {
              $res2=$db->Query("SELECT `value` FROM {pre}userprefs WHERE userid=? AND `key`=?",$uid,$widgetOrderType);
              list($widgetOrder)=$res2->FetchArray(MYSQL_NUM);
              if(trim($widgetOrder)=='') $widgetOrder=$bm_prefs[(($widgetOrderType=='widgetOrderStart')?'widget_order_start':'widget_order_organizer')];
              $res2->Free();
              $db->Query('REPLACE INTO {pre}userprefs(userID, `key`,`value`) VALUES(?, ?, ?)',
                $uid,
                $widgetOrderType,
                $this->AddWidgets($widgetOrder,(($widgetOrderType=='widgetOrderStart')?$set_start_widgets:$set_organizer_widgets),$_REQUEST['hpos'],$_REQUEST['vpos'],$_REQUEST['forcepos'])
              );
            }
          $res->Free();
          
          $showinfo=true;
        }
      }
      
      
      $widgets_start = $plugins->getWidgetsSuitableFor($type);
      
      $widgetlist=array();
      
      foreach($start_widgets as $widget)
      {
        if(!array_key_exists($widget,$widgetlist))
          $widgetlist[$widget]=array('title'=>'','start'=>false,'organizer'=>false);
        $widgetlist[$widget]['start']=true;
      }
      
      foreach($organizer_widgets as $widget)
      {
        if(!array_key_exists($widget,$widgetlist))
          $widgetlist[$widget]=array('title'=>'','start'=>false,'organizer'=>false);
        $widgetlist[$widget]['organizer']=true;
      }
      
      foreach($widgetlist as $widget=>$widgetarray)
        $widgetlist[$widget]['title']=$plugins->getParam('widgetTitle', $widget);
      
      $tpl->assign('showinfo',$showinfo);
      $tpl->assign('adminlink',$this->_adminLink(true));
      $tpl->assign('groups', BMGroup::GetSimpleGroupList());
      $tpl->assign('widgetlist',$widgetlist);
      $tpl->assign('page', $this->_templatePath('wipu.acp.tpl'));
   }
   
}

$plugins->registerPlugin('WidgetPublisher');

?>
