<?php
 if (!isset($section->parametrs->param1) || $section->parametrs->param1=='') $section->parametrs->param1 = "0";
 if (!isset($section->parametrs->param2) || $section->parametrs->param2=='') $section->parametrs->param2 = "[namepage]";
 if (!isset($section->parametrs->param3) || $section->parametrs->param3=='') $section->parametrs->param3 = "Всего проголосовало";
 if (!isset($section->parametrs->param4) || $section->parametrs->param4=='') $section->parametrs->param4 = "#000000";
 if (!isset($section->parametrs->param5) || $section->parametrs->param5=='') $section->parametrs->param5 = "Голосовать";
 if (!isset($section->parametrs->param6) || $section->parametrs->param6=='') $section->parametrs->param6 = "Результат";
   foreach($section->parametrs as $__paramitem){
    foreach($__paramitem as $__name=>$__value){
      while (preg_match("/\[%([\w\d\-]+)%\]/u", $__value, $m)!=false){
        $__result = $__data->prj->vars->$m[1];
        $__value = str_replace($m[0], $__result, $__value);
      }
      $section->parametrs->$__name = $__value;
     }
   }
?>