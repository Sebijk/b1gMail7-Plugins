<script language="javascript">
var mines_running={if $mines_running}true{else}false{/if};

{literal}

function minesweeper(action,x,y)
{
  MakeXMLRequest("start.php?sid={/literal}{$sid}{literal}&action=mines&do="+action+"&x="+x+"&y="+y, updatefield);
}


function updatefield(xmlHTTP)
{
  if (xmlHTTP.readyState!=4) return false;
  
  var picpath='plugins/templates/images/';
  var xml = xmlHTTP.responseXML;
  var celltag = xml.getElementsByTagName('cell');
  var infotag = xml.getElementsByTagName('info').item(0);

  for(var i=0;i<celltag.length;i++)
  {
    var thiscell=celltag.item(i);
    var img=document.getElementById('mines_'+thiscell.getAttribute("x")+'x'+thiscell.getAttribute("y"));
    var pic='';
    if(thiscell.getAttribute('opened') || !(infotag.getAttribute('running')))
    {
      if(thiscell.getAttribute('bomb'))
      {
        if(thiscell.getAttribute('marked'))
          pic='minesw_bm.png';
        else if(thiscell.getAttribute('opened'))
          pic='minesw_bc.png';
        else
          pic='minesw_b.png';
      }
      else
      {
        pic='minesw_'+thiscell.getAttribute('nearbombs')+'.png';
      }
    }
    else
    {
      if(thiscell.getAttribute('marked'))
        pic='minesw_m.png';
      else
        pic='minesw_u.png';
    }
    if(img.src!=picpath+pic)
      img.src=picpath+pic;
  }
  
  var statusf=document.getElementById('mines_status');
  if(infotag.getAttribute('running'))
  {
    if(!infotag.getAttribute('bombsplaced'))
    {
      statusf.value='{/literal}{lng p="mines_gamestarted"}{literal}';
      statusf.style.color='black';
    }
    else
    {
      statusf.value='{/literal}{lng p="mines_reaming"}{literal}: '+infotag.getAttribute('bombsreaming');
      statusf.style.color='black';
    }
  }
  else
  {
    if(infotag.getAttribute('won'))
    {
      statusf.value='{/literal}{lng p="mines_gamewon"}{literal}';
      statusf.style.color='green';
    }
    else
    {
      statusf.value='{/literal}{lng p="mines_gamelost"}{literal}';
      statusf.style.color='red';
    }
  }
  
  if(mines_running && !infotag.getAttribute('running'))
  {
    if(infotag.getAttribute('won'))
      alert('{/literal}{lng p="mines_gamewon"}{literal}');
    else
      alert('{/literal}{lng p="mines_gamelost"}{literal}');
  }
  mines_running=infotag.getAttribute('running');
}
minesweeper();
{/literal}
</script>
<div class="innerWidget" style="text-align:center;">
  <center>
  <div style="border-width:2px; border-color:#000000; border-style:solid; width: {$mines_sizex*16}px;background-color:#DDDDDD;text-align:center;">
<input type="text" id="mines_status" value="{lng p="mines_wait"}" style="width:{$mines_sizex*16-6}px;background-color:#CCCCCC;color:back;" readonly><br>
{section name=fory loop=$mines_sizey start=0 step=1}{section name=forx loop=$mines_sizex start=0 step=1}<img id="mines_{$smarty.section.forx.index}x{$smarty.section.fory.index}" src="plugins/templates/images/minesw_u.png" onclick="if(mines_running)minesweeper('click',{$smarty.section.forx.index},{$smarty.section.fory.index});" oncontextmenu="if(mines_running)minesweeper('rightclick',{$smarty.section.forx.index},{$smarty.section.fory.index});return false;">{/section}<br>{/section}
<a href="javascript:void(minesweeper('reset',0,0))" style="color:black;";><b>{lng p="mines_newgame"}</b></a>
  </div>
  </center>
</div>
