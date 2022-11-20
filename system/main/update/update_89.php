<?php

if (!se_db_is_field('shop_img','picture_alt')){ 
    se_db_add_field('shop_img', 'picture_alt', "picture_alt varchar(255) DEFAULT NULL AFTER  `picture`");
}

if (!se_db_is_field('shop_img','sort')){ 
    se_db_add_field('shop_img', 'sort', "sort int(11) NOT NULL DEFAULT 0 AFTER  `title`");
}


if (!se_db_is_field('shop_modifications_img','sort')){ 
    se_db_add_field('shop_modifications_img', 'sort', "sort int(11) NOT NULL DEFAULT 0 AFTER  `id_img`");
}
