<div id="cont_txt_edit">
<table border="0" cellpadding="0" cellspacing="0" width="100%" id="editbox">
<tr>
<td valign="top" align="center" id="contentbox">
<div id="rbox">
<div id="bbox">
<div style="width:100%; height:25px;">
<b class="actp">Редактор страницы</b>
<div onclick="seLoadBox('','#se_editor_box','','')" id="close" title="Закрыть"></div>
</div>

<form style="margin:0px;" method="post" action="<?php echo getRequest('page').'?'.time() ?>">
 <table border="0" cellpadding="0" cellspacing="0" width="100%" id="toptab">

 <tr valign="top" align="left">
  <td class="ttltd" colspan=2>
	<div><b>&nbsp;</b></div>
	<input type="submit" value="Сохранить" name="GoToEditContent" id="sendData" class="EdbuttonSend">
    <input type="button" value="Отмена" name="editData" id="editData" class="EdbuttonSend" onClick="seLoadBox('','#se_editor_box','','')">
	<div id="hlp">
		<div class="ihlp"></div><a href="http://help.siteedit.ru/" target="_blank">Помощь</a>
		<a href="http://www.siteedit.ru/" style="float: right; position: relative; left: 0px; top: -5px;"><img border=0 width="86" height="28" src="/system/main/editor/siteedit.gif"></a>
	</div>
	</td>
 </tr>

 
 </table>

 </form> 
 
	</td></tr>
 </table> 
</div>
 </div>
</td>
</tr>
</table>
	<!--div id="enterbg"></div-->
</div>