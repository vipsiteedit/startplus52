<?php

if (!se_db_is_field('shop_modifications','value_opt')){ 
    se_db_add_field('shop_modifications', 'value_opt', "double(10,2) NOT NULL AFTER `value`");
}
if (!se_db_is_field('shop_modifications','value_opt_corp')){ 
    se_db_add_field('shop_modifications', 'value_opt_corp', "double(10,2) NOT NULL AFTER `value_opt`");
}

if (!se_db_is_field('shop_modifications','`default`')){ 
    se_db_query("ALTER TABLE  `shop_modifications` ADD `default` BOOLEAN NOT NULL DEFAULT FALSE AFTER `sort`, ADD INDEX (  `default` )");
}
