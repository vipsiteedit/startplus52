<?php

function convert_curr($name) {
   return str_replace(array('KAT','BER','RUR'), array('KZT','BYR', 'RUB'), $name);
}

function shoppage($folder){
//check business//
    if (!file_exists('system/business')){
	    return "Not Business";
    }
//check pages//
    $pages = simplexml_load_file('projects/' . $folder . 'pages.xml');
    foreach($pages->page as $page){
	$pagecontent = simplexml_load_file('projects/' . $folder . 'pages/' . $page["name"] . ".xml");
	foreach($pagecontent->sections as $section){
	    if (strpos($section->type, "mshop_vitrine") !== false) {
		return array('page' => $page["name"],'id' => $section->id);
	    }
	}
    }
}

function getBool($int) {
   return ($int) ? 'true' : 'false';
}

function replace($text){
    $search = array("&", "\"", ">", "<", "'");
    $replace = array("&amp;", "&quot;", "&gt;", "&lt;", "&apos;");
    $text = str_replace($search, $replace, $text);
    return $text;
}
function se_yandexmarket(){
   if (!SE_DB_ENABLE) return;

    if (file_exists('sitelang.dat'))
	$thisprj = trim(join('',file('sitelang.dat')));
    if (!empty($thisprj)) $thisprj .= '/';

    //se_db_connect();
    $link = se_db_query("SELECT * FROM `main` WHERE `lang`='rus'");
    if (!empty($link))
	$line = se_db_fetch_assoc($link);
    $main_id = $line['id']; 
    $is_store = $line['is_store'];
    $is_pickup = $line['is_pickup'];
    $is_delivery = $line['is_delivery'];
    $local_delivery_cost  = $line['local_delivery_cost'];

    if (empty($line['domain'])){
        $shopurl = "http://".iconv('UTF-8','CP1251', $_SERVER["HTTP_HOST"]);
    } else {
        $shopurl = "http://".iconv('UTF-8','CP1251',$line['domain']);
        $hosts = file('hostname.dat');
        foreach($hosts as $host){
            list($dom, $prj) = explode("\t", $host);
            if (str_replace("www.","",$line['domain']) == str_replace("www.", "", $dom)){
                $thisprj = trim($prj).'/';
            }
        }
    }

    $page = shoppage($thisprj);
    if ($page['page'] == "Not Business") return;


    //$compname = simplexml_load_file('projects/' . $thisprj . 'project.xml');
    //$name = iconv('UTF-8', 'CP1251', $compname->vars->sitesmallcompany);
    
    $name = replace(iconv('UTF-8', 'CP1251',$line['shopname']));
    if (!$name) $name = $shopurl;
    $company = iconv('UTF-8', 'CP1251',  $line['company']);
    $catpage = $page['page'];

    //$link = se_db_query("SELECT * FROM `shop_price` WHERE id_main={$main_id}");
    //if (!empty($link))
//        while($line = se_db_fetch_assoc($link)){
//    	    echo print_r($line),"<br>";
//        }
    $text = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\n";
    $text .= "<!DOCTYPE yml_catalog SYSTEM \"shops.dtd\">\n";
    $text .= "<yml_catalog date=\"".date("Y-m-d H:i")."\">\n";
    $text .= "\t<shop>\n";
    $text .= "\t\t<name>".$name."</name>\n";
    $text .= "\t\t<company>".$company."</company>\n";
    $text .= "\t\t<url>".$shopurl."</url>\n";
    $text .= "\t\t<currencies>\n";
    $link = se_db_query("SELECT mt.`name`, m.`kurs`, `date_replace` FROM `money` `m`
        INNER JOIN money_title `mt` ON (mt.id=m.money_title_id)
        WHERE mt.lang='rus' ORDER BY `date_replace` DESC ");

    if (!empty($link))
        while($line = se_db_fetch_assoc($link)){
            if (empty($money['"'.$line["name"].'"']))
    	       $money['"'.$line["name"].'"'] = $line["kurs"];
        }
    foreach($money as $title => $id)
	$text .= "\t\t\t<currency id=".convert_curr($title)." rate=\"".$id."\"/>\n";
    $text .= "\t\t</currencies>\n\t\t<categories>\n";
    $link = se_db_query("SELECT `id`, `upid`, `name` FROM `shop_group` WHERE lang='rus'");
    if (!empty($link))
        while($line = se_db_fetch_assoc($link)){
    	    if (!empty($line["upid"])) 
    		$parentid = ' parentId="' . $line["upid"] . '"';
    	    else 
    		$parentid="";
	    $text .= "\t\t\t<category id=\"".$line["id"]."\"$parentid>".replace(iconv('UTF-8', 'CP1251', $line["name"]))."</category>\n";
        
        }
    $text .= "\t\t</categories>\n\t\t<offers>\n";
    $link = se_db_query("SELECT (SELECT newproc FROM `shop_special` WHERE expires_date>=CURDATE() AND (id_group=sp.id_group OR id_price=sp.id) AND sp.special_price='Y' AND sg.special_price='Y' LIMIT 1) AS spec,sp.id, sp.code, sp.price, sp.curr, sp.id_group, sp.name, sp.note, sp.img,.
        sp.manufacturer, sp.enabled, sp.presence_count, sg.lang
        FROM `shop_price` `sp` 
        INNER JOIN shop_group sg ON (sg.id=sp.id_group)
        WHERE sp.`enabled` = 'Y' AND sp.`price`>0 AND sp.`name`<>'' AND sg.lang='rus'"); //sp.is_market=1 AND 
//echo mysql_error().'111';
    if (!empty($link))
        while($line = se_db_fetch_assoc($link)){
            $line['sales_notes'] = 'Магазин работает по предоплате 100% ';
            if (empty($line['lang'])) $line['lang'] = 'rus';
            $proc = floatval($line['spec']);
            if ($line["enabled"] == "Y"){
                $available = ($line["presence_count"] > 0 || $line["presence_count"]=='') ? "true" : "false";
                if ($available == 'false') continue;
                $price = number_format($line['price'] * ((100 - $proc) / 100), 2, '.', '');
                $text .= "\t\t\t<offer id=\"".$line["id"]."\" available=\"".$available."\">\n";
                $text .= "\t\t\t\t<url>".$shopurl."/".$catpage."/show/".$line["code"]."/</url>\n";
                $text .= "\t\t\t\t<price>".$price."</price>\n";
                $text .= "\t\t\t\t<currencyId>".$line["curr"]."</currencyId>\n";
                $text .= "\t\t\t\t<categoryId>".$line["id_group"]."</categoryId>\n";
                if (!empty($line["img"]))
                    $text .= "\t\t\t\t<picture>".$shopurl."/images/".$line["lang"]."/shopprice/".$line["img"]."</picture>\n";

                // Доставка
                if (!$is_delivery && !$is_store && !$is_pickup) {
                    //$is_delivery = true;
                }

                $text .= "\t\t\t\t<store>".getBool($is_store)."</store>\n";
                $text .= "\t\t\t\t<pickup>".getBool($is_pickup)."</pickup>\n";
                $text .= "\t\t\t\t<delivery>".getBool($is_delivery)."</delivery>\n";
                if ($local_delivery_cost && $is_delivery)
                    $text .= "\t\t\t\t<local_delivery_cost>".round($local_delivery_cost)."</local_delivery_cost>\n";

                $text .= "\t\t\t\t<name>".replace(iconv('UTF-8', 'CP1251', $line["name"]))."</name>\n";
                if (!empty($line["manufacturer"]))
                    $text .= "\t\t\t\t<vendor>" . replace(iconv('UTF-8', 'CP1251', $line["manufacturer"])). "</vendor>\n";
                if (!empty($line["note"]))
                    $text .= "\t\t\t\t<description>".replace(iconv('UTF-8', 'CP1251', $line["note"]))."</description>\n";
                if (!empty($line["sales_notes"]))
                    $text .= "\t\t\t\t<sales_notes>".replace(iconv('UTF-8', 'CP1251', $line["sales_notes"]))."</sales_notes>\n";
                $text .= "\t\t\t</offer>\n";
            }
        }
    $text .= "\t\t</offers>\n";
    $text .= "\t</shop>\n";
    $text .= "</yml_catalog>";
    //echo str_replace("\n","<br>",htmlspecialchars($text));
    $out = fopen('market.yml','w');
    fwrite($out,$text);
    fclose($out);
    return "done";
}
?>