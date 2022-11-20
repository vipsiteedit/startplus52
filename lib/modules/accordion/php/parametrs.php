<?php
 if (!isset($section->parametrs->param9)) $section->parametrs->param9 = "false";
 if (!isset($section->parametrs->param10)) $section->parametrs->param10 = "contents";
 if (!isset($section->parametrs->param11)) $section->parametrs->param11 = "200";
 if (!isset($section->parametrs->param14)) $section->parametrs->param14 = "Y";
 if (!isset($section->parametrs->param13)) $section->parametrs->param13 = "Y";
 if (!isset($section->parametrs->param4)) $section->parametrs->param4 = "N";
 if (!isset($section->parametrs->param1)) $section->parametrs->param1 = "подробнее";
 if (!isset($section->parametrs->param5)) $section->parametrs->param5 = "N";
 if (!isset($section->parametrs->param3)) $section->parametrs->param3 = "Y";
 if (!isset($section->parametrs->param7)) $section->parametrs->param7 = "Y";
 if (!isset($section->parametrs->param6)) $section->parametrs->param6 = "Печать";
 if (!isset($section->parametrs->param2)) $section->parametrs->param2 = "Вернуться назад";
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