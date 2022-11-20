<?php
 if (empty($section->parametrs->param6)) $section->parametrs->param6 = "30";
 if (empty($section->parametrs->param1)) $section->parametrs->param1 = "0";
 if (empty($section->parametrs->param2)) $section->parametrs->param2 = "[namepage]";
 if (empty($section->parametrs->param9)) $section->parametrs->param9 = "Y";
 if (empty($section->parametrs->param10)) $section->parametrs->param10 = "";
   foreach($section->parametrs as $__paramitem){
    foreach($__paramitem as $__name=>$__value){
      if (empty($__value)){
      }
      if (preg_match("/\[%([\w\d\-]+)%\]/u", $__value, $m)!=false){
        $section->parametrs->$__name = $__data->prj->vars->$m[1];
      }
     }
   }
?>