<?php
/*
 * MinesweeperWidget Plugin for b1gMail
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


class MinesweeperWidget extends BMPlugin 
{
   function MinesweeperWidget()
   {
      $this->name             = 'MinesweeperWidget';
      $this->author           = 'radada';
      $this->web              = '';
      $this->mail             = '';
      $this->version          = '1.0.1';
      $this->designedfor         = '7.0.0 (PL2)';
      $this->type             = BMPLUGIN_WIDGET;
      
      $this->widgetTitle         = 'Minesweeper';
      $this->widgetTemplate      = 'widget.minesweeper.tpl';
      
      // SPielfeld
      $this->size_x=9; //Breite
      $this->size_y=9; //Höhe
      $this->bombs=10; //Anzahl Bomben
   }
   
   function OnReadLang(&$lang_user, &$lang_client, &$lang_custom, &$lang_admin, $lang)
   {
      if($lang=='german' || $lang=='deutsch')
      {
        $lang_user['mines_newgame'] = 'Neues Spiel';
        $lang_user['mines_gamewon'] = 'Spiel gewonnen!';
        $lang_user['mines_gamelost'] = 'Spiel verloren!';
        $lang_user['mines_reaming'] = 'Verbleibend';
        $lang_user['mines_gamestarted'] = 'Spiel gestartet...';
        $lang_user['mines_wait'] = 'Bitte warten...';
      }
      else
      {
        $lang_user['mines_newgame'] = 'New Game';
        $lang_user['mines_gamewon'] = 'Game won!';
        $lang_user['mines_gamelost'] = 'Game lost!';
        $lang_user['mines_reaming'] = 'Reaming';
        $lang_user['mines_gamestarted'] = 'Game started...';
        $lang_user['mines_wait'] = 'Please wait...';
      }
   }
   
   function isWidgetSuitable($for)
   {
      return($for == BMWIDGET_START);
   }
   
   function createplayfield()
   {
     $pf=array();
     $pf['bombesplaced']=false;
     $pf['running']=true;
     $pf['won']=false;
     $pf['x']=$this->size_x;
     $pf['y']=$this->size_y;
     $pf['field']=array();
     $pf['bombsreaming']=0;
     for($i=0;$i<$pf['y'];$i++)
       for($ii=0;$ii<$pf['x'];$ii++)
         $pf['field'][$i][$ii]=array('bomb'=>false,'marked'=>false,'nearbombs'=>0,'shown'=>false);
     return $pf;
   }
   
   function placebombs(&$pf)
   {
      if(!$pf['bombesplaced'])
      {
        $pf['bombsreaming']=$bombs_reaming=$this->bombs;
        while($bombs_reaming>0)
        {
          $x=rand(0,$pf['x']-1);
          $y=rand(0,$pf['y']-1);
          if(!$pf['field'][$y][$x]['bomb'] && !$pf['field'][$y][$x]['shown'])
          {
            $pf['field'][$y][$x]['bomb']=true;
            $bombs_reaming--;
          }
        }
        for($i=0;$i<$pf['y'];$i++)
          for($ii=0;$ii<$pf['x'];$ii++)
          {
            $nearbombs=0;
            for($iy=$i-1;$iy<=$i+1;$iy++)
              for($ix=$ii-1;$ix<=$ii+1;$ix++)
              {
                if( ($iy==$i && $ix==$ii) || $iy<0 || $ix<0 || $iy>=$pf['y'] || $ix>=$pf['x'])
                  continue;
                if($pf['field'][$iy][$ix]['bomb'])
                  $nearbombs++;
                
              }
            $pf['field'][$i][$ii]['nearbombs']=$nearbombs;
          }
      }
      $pf['bombesplaced']=true;
   }
   
   function click(&$pf,$x,$y)
   {
      if($x>=0 && $x<$pf['x'] && $y>=0 && $y<$pf['y'] && $pf['running'] && !$pf['field'][$y][$x]['marked'])
      {
        $pf['field'][$y][$x]['shown']=true;
        if(!$pf['bombesplaced'])
        {
          $this->placebombs($pf);
        }
        if($pf['field'][$y][$x]['bomb'])
        {
          $pf['running']=false;
          $pf['won']=false;
          $pf['field'][$y][$x]['shown']=true;
        }
        else
        {
          if($pf['field'][$y][$x]['nearbombs']==0)
           $this->openaround($pf,$x,$y); 
        }
      }
   }
   
   function openaround(&$pf,$x,$y)
   {
      if($x>=0 && $x<$pf['x'] && $y>=0 && $y<$pf['y'] && !$pf['field'][$y][$x]['bomb'])
      {
        $pf['field'][$y][$x]['shown']=true;
        if($pf['field'][$y][$x]['nearbombs']==0)
          for($iy=$y-1;$iy<=$y+1;$iy++)
            for($ix=$x-1;$ix<=$x+1;$ix++)
              if(!$pf['field'][$iy][$ix]['shown'])
                $this->openaround($pf,$ix,$iy);
      }
   }
   
   function rightclick(&$pf,$x,$y)
   {
      if($x>=0 && $x<$pf['x'] && $y>=0 && $y<$pf['y'] && $pf['running'] && !$pf['field'][$y][$x]['shown'])
      {
        $pf['field'][$y][$x]['marked']=($pf['field'][$y][$x]['marked'])?false:true;
        $pf['bombsreaming']+=($pf['field'][$y][$x]['marked'])?-1:+1;
      }
   }
   
   function checkfinished(&$pf)
   {
     $leftfields=0;
     $pf['won']=$pf['running']=true;
     for($i=0;$i<$pf['y'];$i++)
       for($ii=0;$ii<$pf['x'];$ii++)
       {
         if(!$pf['field'][$i][$ii]['shown'] && !$pf['field'][$i][$ii]['bomb'])
           $leftfields++;
         if($pf['field'][$i][$ii]['shown'] && $pf['field'][$i][$ii]['bomb'])
         {
           $leftfields=0;
           $pf['won']=false;
           break 2;
         }
       }
     if($leftfields==0)
     {
       $pf['running']=false;
     }
   }
   
   function genshowfield(&$pf)
   {
     $sfield=array();
     for($i=0;$i<$pf['y'];$i++)
       for($ii=0;$ii<$pf['x'];$ii++)
       {
          $sfield[$i][$ii]['opened']=$pf['field'][$i][$ii]['shown'];
          $sfield[$i][$ii]['bomb']=(!$pf['running'] && $pf['field'][$i][$ii]['bomb']);
          $sfield[$i][$ii]['marked']=$pf['field'][$i][$ii]['marked'];
          $sfield[$i][$ii]['nearbombs']=(!$pf['running'] || $pf['field'][$i][$ii]['shown'])?$pf['field'][$i][$ii]['nearbombs']:'';
       }
     return $sfield;
   }
   
   function FileHandler($file,$action)
   {
    global  $thisUser;
      if($file=='start.php' && $action=='mines')
      {
        $pf=$thisUser->GetPref('mineswidget_game');
        if(trim($pf)=='' || $_REQUEST['do']=='reset')
          $pf=$this->createplayfield();
        else
          $pf=unserialize($pf);
        
        if($_REQUEST['do']=='click')
          $this->click($pf,$_REQUEST['x'],$_REQUEST['y']);
        if($_REQUEST['do']=='rightclick')
          $this->rightclick($pf,$_REQUEST['x'],$_REQUEST['y']);
        
        $this->checkfinished($pf);
        
        $thisUser->SetPref('mineswidget_game',serialize($pf));
        
        $field=$this->genshowfield($pf);
        
        global $currentCharset;
        header('Content-Type: text/xml; charset=' . $currentCharset);
        header('Pragma: no-cache');
        header('Cache-Control: no-cache');
        echo '<?xml version="1.0" encoding="' . $currentCharset . '" ?>' . "\n";
        
        echo "<response>\n";
        
        foreach($field AS $y=>$row)
          foreach($row AS $x=>$cell)
            echo "\t<cell x=\"".$x."\" y=\"".$y."\" opened=\"".(($cell['opened'])?'1':'')."\" bomb=\"".(($cell['bomb'])?'1':'')."\" marked=\"".(($cell['marked'])?'1':'')."\" nearbombs=\"".$cell['nearbombs']."\" />\n";
        
        echo "\t<info sizex=\"".$pf['x']."\" sizey=\"".$pf['y']."\" running=\"".(($pf['running'])?'1':'')."\" won=\"".(($pf['won'])?'1':'')."\" bombsplaced=\"".(($pf['bombesplaced'])?'1':'')."\" bombsreaming=\"".$pf['bombsreaming']."\" />\n";
        echo "</response>";
        
        exit();
      }
   }
   
   function renderWidget()
   {
      global $tpl, $userRow, $thisUser;
      
      $pf=$thisUser->GetPref('mineswidget_game');
      if(trim($pf)=='')
        $pf=$this->createplayfield();
      else
        $pf=unserialize($pf);
      
      $this->checkfinished($pf);
      
      $thisUser->SetPref('mineswidget_game',serialize($pf));
      
      $tpl->assign('mines_sizex', $pf['x']);
      $tpl->assign('mines_sizey', $pf['y']);
      $tpl->assign('mines_running', $pf['running']);
   }
   
   function Uninstall()
   {
      global $db;
      $db->Query("DELETE FROM `{pre}userprefs` WHERE `key`='mineswidget_game'");
      return true;
   }
}

$plugins->registerPlugin('MinesweeperWidget');

?>
