
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js" type="text/javascript" ></script>
<script type="text/javascript" >
    $(function(){              
        $("#date").datepicker();                                                                                                                         
    });                                                                                                                             
</script>                                                                                                                             

<style type="text/css">                                                                                                                
/* Component containers
----------------------------------*/
.ui-widget { font-family: Lucida Grande, Lucida Sans, Arial, sans-serif; font-size: 1.1em; }
.ui-widget input, .ui-widget select, .ui-widget textarea, .ui-widget button { font-family: Lucida Grande, Lucida Sans, Arial, sans-serif; font-size: 1em; }
.ui-widget-content { border: 1px solid #dddddd; background: #f2f5f7 url(/skin/ui-bg_highlight-hard_100_f2f5f7_1x100.png) 50% top repeat-x; color: #362b36; }
.ui-widget-content a { color: #362b36; }
.ui-widget-header { border: 1px solid #aed0ea; background: #deedf7 url(/skin/ui-bg_highlight-soft_100_deedf7_1x100.png) 50% 50% repeat-x; color: #222222; font-weight: bold; }
.ui-widget-header a { color: #222222; }
/* Interaction states
----------------------------------*/
.ui-state-default, .ui-widget-content .ui-state-default { border: 1px solid #aed0ea; background: #d7ebf9 url(/skin/ui-bg_glass_80_d7ebf9_1x400.png) 50% 50% repeat-x; font-weight: bold; color: #2779aa; outline: none; }
.ui-state-default a, .ui-state-default a:link, .ui-state-default a:visited { color: #2779aa; text-decoration: none; outline: none; }
.ui-state-hover, .ui-widget-content .ui-state-hover, .ui-state-focus, .ui-widget-content .ui-state-focus { border: 1px solid #74b2e2; background: #e4f1fb url(/skin/ui-bg_glass_100_e4f1fb_1x400.png) 50% 50% repeat-x; font-weight: bold; color: #0070a3; outline: none; }
.ui-state-hover a, .ui-state-hover a:hover { color: #0070a3; text-decoration: none; outline: none; }
.ui-state-active, .ui-widget-content .ui-state-active { border: 1px solid #2694e8; background: #3baae3 url(/skin/ui-bg_glass_50_3baae3_1x400.png) 50% 50% repeat-x; font-weight: bold; color: #ffffff; outline: none; }
.ui-state-active a, .ui-state-active a:link, .ui-state-active a:visited { color: #ffffff; outline: none; text-decoration: none; }
/* Interaction Cues
----------------------------------*/
.ui-state-highlight, .ui-widget-content .ui-state-highlight {border: 1px solid #f9dd34; background: #ffef8f url(/skin/ui-bg_highlight-soft_25_ffef8f_1x100.png) 50% top repeat-x; color: #363636; }
.ui-state-highlight a, .ui-widget-content .ui-state-highlight a { color: #363636; }
.ui-state-error, .ui-widget-content .ui-state-error {border: 1px solid #cd0a0a; background: #cd0a0a url(/skin/ui-bg_flat_15_cd0a0a_40x100.png) 50% 50% repeat-x; color: #ffffff; }
.ui-state-error a, .ui-widget-content .ui-state-error a { color: #ffffff; }
.ui-state-error-text, .ui-widget-content .ui-state-error-text { color: #ffffff; }
.ui-state-disabled, .ui-widget-content .ui-state-disabled { opacity: .35; filter:Alpha(Opacity=35); background-image: none; }
.ui-priority-primary, .ui-widget-content .ui-priority-primary { font-weight: bold; }
.ui-priority-secondary, .ui-widget-content .ui-priority-secondary { opacity: .7; filter:Alpha(Opacity=70); font-weight: normal; }
.ui-datepicker { width: 17em; padding: .2em .2em 0;  }
.ui-datepicker .ui-datepicker-header { position:relative; padding:.2em 0; }
.ui-datepicker .ui-datepicker-prev, .ui-datepicker .ui-datepicker-next { position:absolute; top: 2px; width: 2.5em; height: 1.8em; }
.ui-datepicker .ui-datepicker-prev-hover, .ui-datepicker .ui-datepicker-next-hover { top: 1px; }
.ui-datepicker .ui-datepicker-prev { left:2px; }
.ui-datepicker .ui-datepicker-next { right:2px; }
.ui-datepicker .ui-datepicker-prev-hover { left:1px; }
.ui-datepicker .ui-datepicker-next-hover { right:1px; }
.ui-datepicker .ui-datepicker-prev span, .ui-datepicker .ui-datepicker-next span { display: block; position: absolute; left: 50%; margin-left: -12px; top: 50%; margin-top: -8px;  }
.ui-datepicker .ui-datepicker-title { margin: 0 2.3em; line-height: 1.8em; text-align: center; }
.ui-datepicker .ui-datepicker-title select { float:left; font-size:1em; margin:1px 0; }
.ui-datepicker select.ui-datepicker-month-year {width: 100%;}
.ui-datepicker select.ui-datepicker-month, 
.ui-datepicker select.ui-datepicker-year { width: 49%;}
.ui-datepicker .ui-datepicker-title select.ui-datepicker-year { float: right; }
.ui-datepicker table {width: 100%; font-size: .9em; border-collapse: collapse; margin:0 0 .4em; }
.ui-datepicker th { padding: .7em .3em; text-align: center; font-weight: bold; border: 0;  }
.ui-datepicker td { border: 0; padding: 1px; }
.ui-datepicker td span, .ui-datepicker td a { display: block; padding: .2em; text-align: right; text-decoration: none; }
.ui-datepicker .ui-datepicker-buttonpane { background-image: none; margin: .7em 0 0 0; padding:0 .2em; border-left: 0; border-right: 0; border-bottom: 0; }
.ui-datepicker .ui-datepicker-buttonpane button { float: right; margin: .5em .2em .4em; cursor: pointer; padding: .2em .6em .3em .6em; width:auto; overflow:visible; }
.ui-datepicker .ui-datepicker-buttonpane button.ui-datepicker-current { float:left; }
/* with multiple calendars */
.ui-datepicker.ui-datepicker-multi { width:auto; }
.ui-datepicker-multi .ui-datepicker-group { float:left; }
.ui-datepicker-multi .ui-datepicker-group table { width:95%; margin:0 auto .4em; }
.ui-datepicker-multi-2 .ui-datepicker-group { width:50%; }
.ui-datepicker-multi-3 .ui-datepicker-group { width:33.3%; }
.ui-datepicker-multi-4 .ui-datepicker-group { width:25%; }
.ui-datepicker-multi .ui-datepicker-group-last .ui-datepicker-header { border-left-width:0; }
.ui-datepicker-multi .ui-datepicker-group-middle .ui-datepicker-header { border-left-width:0; }
.ui-datepicker-multi .ui-datepicker-buttonpane { clear:left; }
.ui-datepicker-row-break { clear:both; width:100%; }
/* RTL support */
.ui-datepicker-rtl { direction: rtl; }
.ui-datepicker-rtl .ui-datepicker-prev { right: 2px; left: auto; }
.ui-datepicker-rtl .ui-datepicker-next { left: 2px; right: auto; }
.ui-datepicker-rtl .ui-datepicker-prev:hover { right: 1px; left: auto; }
.ui-datepicker-rtl .ui-datepicker-next:hover { left: 1px; right: auto; }
.ui-datepicker-rtl .ui-datepicker-buttonpane { clear:right; }
.ui-datepicker-rtl .ui-datepicker-buttonpane button { float: left; }
.ui-datepicker-rtl .ui-datepicker-buttonpane button.ui-datepicker-current { float:right; }
.ui-datepicker-rtl .ui-datepicker-group { float:right; }
.ui-datepicker-rtl .ui-datepicker-group-last .ui-datepicker-header { border-right-width:0; border-left-width:1px; }
.ui-datepicker-rtl .ui-datepicker-group-middle .ui-datepicker-header { border-right-width:0; border-left-width:1px; }
/* IE6 IFRAME FIX (taken from datepicker 1.5.3 */
.ui-datepicker-cover {
    display: none; /*sorry for IE5*/
    display/**/: block; /*sorry for IE5*/
    position: absolute; /*must have*/
    z-index: -1; /*must have*/
    filter: mask(); /*must have*/
    top: -4px; /*must have*/
    left: -4px; /*must have*/
    width: 200px; /*must have*/
    height: 200px; /*must have*/
}/* Dialog
</style>
<DIV class="content" id="cont_guest">
  <div id="subpage1">
    <form action="" enctype="multipart/form-data" method="post">
        
 <table class="tableTable" border="0">
    <tbody>
        <tr>
            <td class="tablerow"><label class="text datettl"><?php echo $section->parametrs->param23 ?></label></td>
            <td class="tablerow"> 
                <input class="inp" id="date" type="text" maxlength="10" name="date" value="<?php echo $postdate ?>">
                <div class="error"><?php echo $dateerror ?></div> 
            </td> 
        </tr> 
        <tr> 
            <td class="tablerow"><label class="text namettl"><?php echo $section->parametrs->param16 ?></label></td> 
            <td class="tablerow">
                <input class="inp" id="name" type="text" name="usrname" value="<?php echo $name ?>">
                <div class="error"><?php echo $nameerror ?></div>
            </td> 
        </tr> 
        <tr> 
            <td class="tablerow"><label class="text usrmailttl"><?php echo $section->parametrs->param17 ?></label></td> 
            <td class="tablerow">
                <input class="inp" id="usrmail" type="text" name="usrmail" value="<?php echo $mail ?>">
                <div class="error"><?php echo $mailerror ?></div>
            </td> 
        </tr> 
        <tr> 
            <td class="tablerow" colspan="2"> 
                <label class="text usernotettl"><?php echo $section->parametrs->param24 ?></label><br> 
                <div><textarea class="inp" id="note" name="note" rows="7" maxlength="<?php echo $section->parametrs->param35 ?>" cols="30"><?php echo $note ?></textarea></div>
                <div class="error"><?php echo $noteerror ?></div> 
            </td> 
        </tr> 
        <tr> 
            <td class="tablerow" colspan="2"> 
                <label class="text adminaswerttl"><?php echo $section->parametrs->param25 ?></label><br> 
                <div><textarea class="inp" id="admtxt" name="admtxt" rows="7" cols="30"><?php echo $adm_text ?></textarea><div>
            </td> 
        </tr> 
        <tr> 
            <td class="tablerow" colspan="2">
                <input class="cbox inp" id="cbox1" type="checkbox" name="del">
                <label class="text delmsgttl"><?php echo $section->parametrs->param26 ?></label>
            </td> 
        </tr> 
        <tr> 
            <td class="tablerow" colspan="2">
                <input class="cbox inp" id="cbox2" type="checkbox" name="block">
                <label class="text blockuserttl"><?php echo $section->parametrs->param27 ?></label>
            </td> 
        </tr> 
        <tr> 
            <td colspan="2" class="buttonBlock">
                <input class="buttonSend saveedit" type="submit" name="save" value="<?php echo $section->parametrs->param28 ?>"> 
                <input class="buttonSend blockedit" type="button" onclick="document.location = '<?php echo seMultiDir()."/".$_page."/".$razdel."/sub2/" ?>'" name="save" value="<?php echo $section->parametrs->param29 ?>"> 
            </td> 
        </tr> 
    </tbody> 
</table> 
    </form>
  </div>
</DIV>

