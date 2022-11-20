<?php

class ymarket {

    public function __construct() {
        $this->create_file();
    }

    private function create_file() {
        if (!SE_DB_ENABLE) return;

        $thisprj = '';
        if (file_exists('sitelang.dat'))
            $thisprj = trim(join('', file('sitelang.dat')));
        if (!empty($thisprj)) $thisprj .= '/';

        $link = se_db_query("SELECT * FROM `main` WHERE `lang`='rus'");
        if (!empty($link))
            $line = se_db_fetch_assoc($link);
        else return;
        $main_id   = $line['id'];
        $base_curr = $line['basecurr'];
        $is_manual = $line['is_manual_curr_rate'];
        $esupport  = $line['esupport'];

        $params = new seTable('shop_integration_parameter');
        $params->select('code, value');
        $params->where("`id_main`=?", $main_id);
        $params = $params->getList();

        $is_store = $is_pickup = $is_delivery = $sales_note = '';
        $local_delivery_cost = $local_delivery_days = 0;
        foreach ($params as $item) {
            switch ($item['code']) {
                case 'isYAStore':
                    $is_store = $item['value'];
                    break;
                case 'isPickur':
                    $is_pickup = $item['value'];
                    break;
                case 'isDelivery':
                    $is_delivery = $item['value'];
                    break;
                case 'localDeliveryCost':
                    $local_delivery_cost = $item['value'];
                    break;
                case 'localDeliveryDays':
                    $local_delivery_days = $item['value'];
                    break;
                case 'salesNotes':
                    $sales_note = $item['value'];
                    break;
            }
        }
        //  записывать ли доставку
        $show_delivery = (((int)$local_delivery_cost == 0) && ((int)$local_delivery_days == 0)) ? false : true;

        if (empty($line['domain'])){
            $shopurl = _HTTP_ . iconv('UTF-8','CP1251', $_SERVER["HTTP_HOST"]);
        } else {
            $line['domain'] = preg_replace("/.*:\\/\\//", '', $line['domain']);
            $shopurl = _HTTP_ . iconv('UTF-8','CP1251',$line['domain']);
            $hosts = (file_exists('hostname.dat')) ? file('hostname.dat') : array();
            foreach($hosts as $host){
                list($dom, $prj) = explode("\t", $host);
                if (str_replace("www.", "", $line['domain']) == str_replace("www.", "", $dom)){
                    $thisprj = trim($prj) . '/';
                }
            }
        }

        $page = $this->shoppage($thisprj);
        if (!$page) return;

        $name = $this->replace(iconv('UTF-8', 'CP1251', $line['shopname']));
        if (!$name) $name = $shopurl;
        if(strlen($name) > 0) $name = substr($name, 0, 20);
        $company = iconv('UTF-8', 'CP1251', $line['company']);
        $catpage = $page['page'];

        $text = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\n";
        $text .= "<!DOCTYPE yml_catalog SYSTEM \"shops.dtd\">\n";
        $text .= "<yml_catalog date=\"".date("Y-m-d H:i")."\">\n";
        $text .= "\t<shop>\n";
        $text .= "\t\t<name>"    . $name     . "</name>\n";
        $text .= "\t\t<company>" . $company  . "</company>\n";
        $text .= "\t\t<url>"     . $shopurl  . "</url>\n";
        $text .= "\t\t<platform>CMS SITEEDIT</platform>\n";
        $text .= "\t\t<version>5.2</version>\n";
        $text .= "\t\t<email>"   . $esupport . "</email>\n";

        //  валюта
        $text .= "\t\t<currencies>\n";

        $curr = new seTable('money_title', 'mt');
        $curr->select('mt.name, (SELECT m.kurs FROM money m WHERE m.money_title_id=mt.id ORDER BY created_at DESC LIMIT 1) as val');
        $curr->where("lang='rus'");
        $curr->andwhere("cbr_kod IS NOT NULL");
        $currlist = $curr->getList();
        $money = array();
        foreach($currlist as $it){
            $it['name'] = $this->convert_curr($it['name']);
            $money[$it['name']] = $it['val'];
            if(!$is_manual) {
                $money[$it['name']] = se_MoneyConvert(1, $it['name'], se_BaseCurrency());
            }
            $money[$it['name']] = str_replace(",", ".", $money[$it['name']]);
        }
        $money[se_BaseCurrency()] = 1;
        foreach($money as $title => $id)
            $text .= "\t\t\t<currency id=\"" . $this->convert_curr($title) . "\" rate=\"" . $id . "\"/>\n";

        $text .= "\t\t</currencies>\n";

        //  категории
        $text .= "\t\t<categories>\n";

        $groups = new seTable('shop_group', 'sg');
        $groups->select('`id`, `upid`, `name`');
//        $groups->select('`id`, `upid`, `name`, (SELECT COUNT(sp.id) FROM shop_price sp WHERE sp.id_group=sg.id) as counts');
        $groups->where("lang='rus'");
//        $groups->having('counts > 0');
        $groups = $groups->getList();
        foreach ($groups as $group) {
            if (!empty($group["upid"]))
                $parentid = ' parentId="' . $group["upid"] . '"';
            else
                $parentid="";
            $text .= "\t\t\t<category id=\"" . $group["id"] . "\"$parentid>" . $this->replace(iconv('UTF-8', 'CP1251', $group["name"])) . "</category>\n";
        }

        $text .= "\t\t</categories>\n";

        //  получаем доставку
        if($show_delivery) {
            $delivery = new seTable('shop_deliverygroup', 'sg');
            $delivery->select('sg.id_group, sd.price, sd.time');
            $delivery->innerjoin('shop_deliverytype sd', 'sd.id=sg.id_type');
            $delivery->where("sd.lang='rus' AND sd.status='Y'");
            $delivery->orderby('sg.id_group');
            $delivery->addorderby('sd.time');
            $delivery = $delivery->getList();

            $delivery_opt = array();
            foreach ($delivery as $row) {
                $delivery_opt[$row['id_group']][] = $row;
            }

            //        if(!empty($delivery)) {
            //            $text .= "\t\t\t\t<delivery-options>\n";
            //            foreach ($delivery as $item) {
            //                $text .= "\t\t\t\t\t<option cost=\"" . $item['price'] . "\" days=\"" . $item['time'] . "\" />\n";
            //            }
            //            $text .= "\t\t\t\t</delivery-options>\n";
            //        }

            $text .= "\t\t<delivery-options>\n";
            $text .= "\t\t\t<option cost=\"" . $local_delivery_cost . "\" days=\"" . $local_delivery_days . "\" />\n";
            $text .= "\t\t</delivery-options>\n";
        }

        //  товары
        $text .= "\t\t<offers>\n";
        //  получаем товары
        $price = new seTable('shop_price', 'sp');
        $price->select("sp.id, sp.code, sp.price, sp.curr, sp.id_group, sp.name, sp.note, sp.text, sp.img, sp.enabled, sp.presence_count, sg.lang, (SELECT GROUP_CONCAT(sha.id_acc) FROM shop_accomp sha WHERE sha.id_price=sp.id) as rec, (SELECT GROUP_CONCAT(shi.picture ORDER BY `default` DESC SEPARATOR '||') FROM shop_img shi WHERE shi.id_price=sp.id LIMIT 10) as imgs, (SELECT b.name FROM shop_brand b WHERE b.id=sp.id_brand) as brand");
        $price->innerjoin("shop_group sg", "sg.id=sp.id_group");
        $price->where("sp.`enabled` = 'Y' AND sp.`price`>0 AND sp.`name`<>'' AND sg.lang='rus' AND sp.is_market=1 AND (sp.presence_count!=0 OR sp.presence_count IS NULL)");


        $pricelist = $price->getList();
        foreach ($pricelist as $line) {
            if (empty($line['lang'])) $line['lang'] = 'rus';
            $available = 'true';
            $plugin_amount = new plugin_shopamount53($line['id'], '', 0, 1);
            $count    = (int)$plugin_amount->getPresenceCount();
            $price    =  $plugin_amount->getPrice(true);
            $discount = $plugin_amount->getDiscount();
            $oldprice = '';
            if ($discount > 0){
                $oldprice = $plugin_amount->getPrice(false);
                $percent  = 0 - $plugin_amount->getDiscountProc();
            }
            unset($plugin_amount);

            if (!empty($line['brand']))
                list($line['brand'], ) = explode('||', $line['brand']);
    	    if(empty($line['note'])) $line['note'] = $line['text'];
            $line['note'] = htmlspecialchars(strip_tags($line['note']), ENT_QUOTES);

            $text .= "\t\t\t<offer id=\"" . $line["id"] . "\" available=\"" . $available . "\">\n";
            $text .= "\t\t\t\t<url>"      . $shopurl  . "/" . $catpage . "/show/" . $line["code"] . "/</url>\n";
            $text .= "\t\t\t\t<price>"    . $price    . "</price>\n";
            if (!empty($oldprice))
                $text .= "\t\t\t\t<oldprice>" . $oldprice . "</oldprice>\n";
            $text .= "\t\t\t\t<currencyId>"   . $this->convert_curr($line["curr"]) . "</currencyId>\n";
            $text .= "\t\t\t\t<categoryId>"   . $line["id_group"] . "</categoryId>\n";
            $imgs = explode("||", $line['imgs']);
            foreach($imgs as $row) {
                $text .= "\t\t\t\t<picture>"  . $shopurl . "/images/" . $line["lang"] . "/shopprice/" . $row . "</picture>\n";
            }
            $text .= "\t\t\t\t<store>"    . $this->getBool($is_store)    . "</store>\n";
            $text .= "\t\t\t\t<pickup>"   . $this->getBool($is_pickup)   . "</pickup>\n";
            $text .= "\t\t\t\t<delivery>" . $this->getBool($is_delivery) . "</delivery>\n";
            if(isset($delivery_opt[$line["id_group"]]) && $show_delivery) {
                $text .= "\t\t\t\t<delivery-options>\n";
                foreach ($delivery_opt[$line["id_group"]] as $item) {
                    $text .= "\t\t\t\t\t<option cost=\"" . $item['price'] . "\" days=\"" . $item['time'] . "\" />\n";
                }
                $text .= "\t\t\t\t</delivery-options>\n";
            }

            $text .= "\t\t\t\t<name>"            . $this->replace(iconv('UTF-8', 'CP1251', $line["name"])) . "</name>\n";
            if (!empty($line["brand"]))
                $text .= "\t\t\t\t<vendor>"      . $this->replace(iconv('UTF-8', 'CP1251', $line["brand"])) . "</vendor>\n";
            if (!empty($line["note"]))
                $text .= "\t\t\t\t<description>" . $this->replace(iconv('UTF-8', 'CP1251', $line["note"])) . "</description>\n";
            if (!empty($sales_note))
                $text .= "\t\t\t\t<sales_notes>" . $this->replace(iconv('UTF-8', 'CP1251', $sales_note)) . "</sales_notes>\n";
            if (!empty($line['rec']))
                $text .= "\t\t\t\t<rec>"         . $line['rec'] . "</rec>\n";
            $text .= "\t\t\t</offer>\n";
        }
        $text .= "\t\t</offers>\n";
        $text .= "\t</shop>\n";
        $text .= "</yml_catalog>";
        $out = fopen('market.yml','w');
        fwrite($out, $text);
        fclose($out);

        return true;
    }

    private function shoppage($folder){
        //  check business
        if (!file_exists('system/business')){
            return false;
        }
        //  check pages
        $pages = simplexml_load_file('projects/' . $folder . 'pages.xml');
        foreach($pages->page as $page){
            $pagecontent = simplexml_load_file('projects/' . $folder . 'pages/' . $page["name"] . ".xml");
            foreach($pagecontent->sections as $section){
                if (strpos($section->type, "shop_vitrine") !== false) {
                    return array('page' => $page["name"],'id' => $section->id);
                }
            }
        }

        return false;
    }

    private function convert_curr($name){
        return str_replace(array('KAT','BER','RUR', 'UKH'), array('KZT','BYR', 'RUB', 'UAH'), $name);
    }

    private function getBool($int) {
        return ($int) ? 'true' : 'false';
    }

    private function replace($text){
        $search = array("&", "\"", ">", "<", "'");
        $replace = array("&amp;", "&quot;", "&gt;", "&lt;", "&apos;");
        $text = str_replace($search, $replace, $text);
        return $text;
    }
}
