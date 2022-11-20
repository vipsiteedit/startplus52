<script type='text/javascript' charset="utf-8">
<!--
tinyMCE.init({
	language : "ru",
	mode : "exact",
	elements : "editclosingtext",
	content_css : "/system/main/editor/tiny.css",
	convert_urls : false,
	theme : "advanced",
	forced_root_block : false,
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left"
});
-->
</script>

<style>
#cont_txt_edit {position:absolute; WIDTH:600; TOP: 0px; LEFT: 0px; z-index:999;}
</style>
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
  <td class="ttltd"><div><b>Завершающий текст</b></div>
		<textarea id="editclosingtext" class="field_arr" name="closingtext" rows="10" cols="40"><?php echo htmlspecialchars($this->page->closingtext) ?></textarea>
  </td>
 </tr> 
 
 <tr valign="top" align="left">
  <td class="ttltd">
	<div><b>&nbsp;</b></div>
	<input type="submit" value="Сохранить" name="GoToEditContent" id="sendData">
    <input type="button" value="Отмена" name="editData" id="editData" onClick="seLoadBox('','#se_editor_box','','')">
	<div id="hlp">
		<div class="ihlp"></div><a href="http://help.siteedit.ru/" target="_blank">Помощь</a>
		<a href="http://www.siteedit.ru/" style="float: right; position: relative; left: 0px; top: -5px;"><img border=0 width="86" height="28" src="/system/main/editor/siteedit.gif"></a>
	</div>
	</td>
 </tr>

 
 </table></form> 
 
	</td></tr>
 </table> 
</div>
 </div>
</td>
</tr>
</table>
	<!--div id="enterbg"></div-->
</div>

<style>
* {
    margin: 0;
	padding: 0px;
  }
 
a { color:#0600fe;} 
  
#bbox {
	margin: 15px;
}

#hlp {
	display: inline-block;
    float: right;
    font: 13px Arial,Helvetica,sans-serif;
    height: 30px;
    left: 3px;
    position: relative;
    top: 0;
    width: 175px;
}

#hlp .ihlp {
    background: url("/system/main/editor/ihlp.png") no-repeat scroll 0 0 #BFBFBF;
	margin: 0 2px 0 0;
    display: inline-block;
    float: left;
    width: 22px;
    height: 19px;
    position: relative;
    left: 0px;
    top: -1px;
}

#close { 
    background: url("/system/main/editor/cls.png") no-repeat scroll 0 0 #BFBFBF;
    display: inline-block;
    float: right;
    height: 22px;
    position: relative;
    right: -6px;
    top: -6px;
    width: 22px;
	cursor:pointer;
}

.traz {
	color: #3a3939;
	font: 18px Arial,Helvetica,sans-serif;
} 
 
input.pinput {
    display: inline-block;
	font: 14px Arial,Helvetica,sans-serif;
	float: left;
	height: 20px;
	width: 100%;
	margin: 1px auto;
}

textarea.field_arr { width: 100%; height: 300px;}
 
.ttltab {
    margin: 10px auto;
} 
 
.ttltd {
	font: bold 14px Arial,Helvetica,sans-serif;
}
 
.ttltd b {
    margin: 2px 5px;
} 
 
.ttltd div {
    margin: 4px auto 4px;
} 
 
#rbox {  
    -webkit-border-radius:0px 15px 15px 15px; -moz-border-radius:0px 15px 15px 15px; border-radius:15px 15px 15px 15px;
    background: none repeat scroll 0 0 #bfbfbf;
    border: 0px solid #DFDEDE;
    font: 14px Arial,Helvetica,sans-serif;
    min-height: 265px;
    padding: 0px;
    position: relative;
    width: 100%;  
	display: inline-block;
  }
  
#editbox { margin: 2% auto 0; color:#000000; min-width:800px; max-width:80%; }

#tabsbox {
  text-align: left;
}

#tabsbox b {
    -webkit-border-radius:5px 5px 0px 0px; -moz-border-radius:5px 5px 0px 0px; border-radius:5px 5px 0px 0px;
	background: url('/system/main/editor/p_m.gif') repeat-x scroll 0 0 #BFBFBF;
    display: inline-block;
    font: 14px Arial,Helvetica,sans-serif;
    height: 29px;
    margin: 0;
}

#tabsbox a {
    display: inline-block;
    font: bold 14px Arial,Helvetica,sans-serif;
    height: 20px;
    margin: 7px 12px 6px;
}

#tabsbox b.actp {
	background: url('/system/main/editor/active_p.gif') repeat-x scroll 0 0 #BFBFBF;
}

#tabsbox b.actp a {
	color: #878585;
	text-decoration:none;
	cursor:text;
}
</style>

<script type="text/javascript">

  $(document).ready(function(){
  
 var wsize = windowWorkSize(),                                               // размеры "рабочей области"
     testElem = document.getElementById("cont_txt_edit"),                        // cont_txt_edit, ложим наш блок в переменную
     testElemWid =  testElem.offsetWidth,                                    // ширина блока
     testElemHei =  testElem.offsetHeight;                                   // высота блока
	testElem.style.left = wsize[0]/2 - testElemWid/2 + "px";        // центрируем блок по горизонтали
	testElem.style.top = wsize[1]/2 - testElemHei/2 + (document.body.scrollTop || document.documentElement.scrollTop) + "px";    // центрируем блок по вертикали + скролл

    window.document.onscroll = function(){                                           // цетрируем по событию onclick
	testElem.style.left = wsize[0]/2 - testElemWid/2 + "px";        // центрируем блок по горизонтали
	testElem.style.top = wsize[1]/2 - testElemHei/2 + (document.body.scrollTop || document.documentElement.scrollTop) + "px";    // центрируем блок по вертикали + скролл
    };

   // фунция определения "рабочего пространства"
   function windowWorkSize(){
       var wwSize = new Array();
       if (window.innerHeight !== undefined) wwSize= [window.innerWidth,window.innerHeight]    // для основных браузеров
       else    
       {   // для "особо одарённых" (ИЕ6-8)
           wwSizeIE = (document.body.clientWidth) ? document.body : document.documentElement; 
           wwSize= [wwSizeIE.clientWidth, wwSizeIE.clientHeight];
       };
       return wwSize;
   };

  }); 

</script>