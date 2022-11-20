<?php
 if (empty($section->parametrs->param0)) $section->parametrs->param0 = "10";
 if (empty($section->parametrs->param1)) $section->parametrs->param1 = "{$__data->prj->vars->adminmail}";
 if (empty($section->parametrs->param2)) $section->parametrs->param2 = "10";
 if (empty($section->parametrs->param8)) $section->parametrs->param8 = "\"Уважаемый администратор сайта [SITE]!\\r\\n\\r\\nНа Вашем сайте с помощью системы \"Поиск по сайту\" искали следующие слова и выражения:\\r\\n\\r\\n    Дата       Время       IP адрес       Строка запроса\\r\\n";
 if (empty($section->parametrs->param9)) $section->parametrs->param9 = "monlinenews,";
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