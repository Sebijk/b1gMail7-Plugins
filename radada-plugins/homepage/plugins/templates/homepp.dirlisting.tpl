<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
{assign var=maxlen value=16}{foreach from=$elements item=element}{if !$element.isparent AND $element.title|count_characters:true > $maxlen}{assign var=maxlen value=$element.title|count_characters:true}{/if}{/foreach}
<html>
 <head>
  <title>Index of {$path}</title>
 </head>
 <body>
<h1>Index of {$path}</h1>
<pre>Name{section name=for loop=$maxlen start=4 step=1} {/section}  Last modified      Size</a>  <hr>{foreach from=$elements item=element}
{if $element.isparent}
<a href="{$element.file}">Parent Directory</a>{section name=for loop=$maxlen start=16 step=1} {/section}                       -  
{else}
<a href="{$element.file}">{$element.title}</a>{section name=for loop=$maxlen start=$element.title|count_characters:true step=1} {/section}  {$element.modified|date_format:'%d-%b-%Y %H:%M'}  {if $element.isfile}{$element.size}{else}-{/if}  
{/if}
{/foreach}
<hr></pre>

</body></html>