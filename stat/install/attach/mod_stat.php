<?php
$SE_VARS['page_timestart'] = time();
function mod_stat($existspage = true)
{
  global $SE_VARS, $stat_idlog; //, $page_timestart;
  $referal = 0;
  $__data = seData::getInstance();
  if (isset($_SESSION['REFER']))
  {
    $referal = $_SESSION['REFER'];
  }
  if (strpos(getRequest('page'), '.') || getRequest('page') == 'skin' || getRequest('page') == 'images' || getRequest('page') == 'files' || getRequest('page') == 'system' || getRequest('page') == 'lib')
    return;

  $SE_VARS['razdel'] = getRequest('razdel');
  $SE_VARS['page_name'] = getRequest('page');
  $SE_VARS['sub'] = getRequest('sub');
  $SE_VARS['object'] = getRequest('object');
  if (!empty($titlepage))
    $SE_VARS['page_title'] = seData::getInstance()->page->titlepage;
  else
    $SE_VARS['page_title'] = seData::getInstance()->page->title;
  $SE_VARS['lang'] = se_getlang();

  require_once ('stat/functions.php');

  // загрузка конфигурации системы
  $statconfig = new seTable('stat_config');
  $statconfig->where("`variable` = 'savelogday'");
  $statconfig->fetchOne();
  $STAT_CONF[$statconfig->variable] = $statconfig->value;
  unset($statconfig);

  if (!empty($STAT_CONF["savelogday"]) && (intval($STAT_CONF["savelogday"]) > 0))
    $savelogday = intval($STAT_CONF["savelogday"]);
  else
    $savelogday = 180;

  if (isset($_SERVER['HTTP_USER_AGENT']))
  {
    $useag = $_SERVER['HTTP_USER_AGENT'];

    $ip = trim(sprintf("%u\n", ip2long(se_stat_getrealip())));
    $proxy = trim(sprintf("%u\n", ip2long(se_stat_getproxy())));

    $_date = date("Ymd");
    $_time = date("His");

    if (isset($_SERVER['HTTP_REFERER']))
    {
      $referer = $_SERVER['HTTP_REFERER'];

      // --- Определяем ссылающиеся домен, страницу и запрос(все, что после '?') ---
      $refurl = parse_url($referer);
      if (!empty($refurl['host']))
        $refd = $refurl['host'];
      else
        $refd = "";

      if (!empty($refurl['path']))
        $refp = $refurl['path'];
      else
        $refp = "";

      if (!empty($refurl['query']))
        $refpq = $refurl['query'];
      else
        $refpq = "";

      // --- Определяем поисковый запрос ---
      if (isset($refurl['query']))
        $refsq = strtolower(se_stat_getsearchquery($referer));
      else
        $refsq = "";

      // --- Определяем каталог ---
      $refc = se_stat_getcatalog($refd . $refp);

      // --- Определяем поисковую систему ---
      $refs = se_stat_getsearchsys($refd . $refp);

      // --- Определяем почтовую систему ---
      $refm['id'] = 0;

      // --- Определяем систему рейтинга ---
      $refr = se_stat_getratingsys($referer);
    }
    else
    {
      $refd = "";
      $refp = "";
      $refpq = "";
      $refsq = "";
      $refc['id'] = 0;
      $refs['id'] = 0;
      $refm['id'] = 0;
      $refr['id'] = 0;
    }

    // --- Определяем домен, на который попал посетитель ---
    if (isset($_SERVER['HTTP_HOST']))
      $curd = $_SERVER['HTTP_HOST'];
    else
      $curd = "";

    // --- Определяем URI, который был задан для доступа к данной странице ---
    if (isset($_SERVER['REQUEST_URI']))
      $cururi = $_SERVER['REQUEST_URI'];
    else
      $cururi = "";

    // --- Определяем существование страницы ---
    if ($existspage)
      $existspage = "Y";
    else
      $existspage = "N";

    // --- Определяем Accept-Languages ---
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
    {
      $acceptlang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    }
    else
      $acceptlang = "";

    // --- Определяем браузер ---
    $tbr = se_stat_getbrowser($useag);

    // --- Определяем ОС ---
    $tsys = se_stat_getsystem($useag);

    // --- Определяем страну ---
    $rescountry = mysql_query("SELECT country FROM stat_ip2country
                               WHERE (ip_start <= '" . $ip . "')AND(ip_end >= '" . $ip . "')");
    while ($rowcountry = mysql_fetch_array($rescountry, MYSQL_ASSOC))
      $idcountry = $rowcountry['country'];
    if (!isset($idcountry))
      $idcountry = 0;

    // --- Определяем город ---
    $rescity = mysql_query("SELECT city FROM stat_ip2city
                            WHERE (ip_start <= '" . $ip . "')AND(ip_end >= '" . $ip . "')");
    while ($rowcity = mysql_fetch_array($rescity, MYSQL_ASSOC))
      $idcity = $rowcity['city'];
    if (!isset($idcity))
      $idcity = 0;

    // --- Определяем зарегистрированных пользователей ---
    if (!empty($SESSION_VARS['IDUSER']))
      $iduserreg = intval($SESSION_VARS['IDUSER']);
    elseif (!empty($ID_AUTHOR))
      $iduserreg = intval($ID_AUTHOR);
    else
      $iduserreg = 0;

    // --- Определяем наличие cookies ---
    if (!empty($_COOKIE))
      $checkcook = "Y";
    else
      $checkcook = "N";


    ////////////////////////////////////////////
    // Если посетитель - робот поисковых систем

    // --- Определяем робота ---
    $trobot = se_stat_getrobot($useag);

    if ($trobot['id'] > 0)
    {
      // *** ЭТО РОБОТ *** //
      mysql_query("
        INSERT INTO stat_logrobots
               (`id_robot`, `ip`, `agent`,
                `date`, `time`,
                `domain`, `request_uri`, `page`, `titlepage`,
                `existspage`)
        VALUES ('" . $trobot['id'] . "', '" . $ip . "', '" . $useag . "',
                '" . $_date . "', '" . $_time . "',
                '" . $curd . "', '" . $cururi . "', '" . $SE_VARS['page_name'] . "', '" . $SE_VARS['page_title'] . "',
                '" . $existspage . "')");
      $idlog = mysql_insert_id();

    }
    else
    {
      // *** ЭТО ПОСЕТИТЕЛЬ *** //

      ////////////////////////////////////////////
      // Проверяем наличие cookie statidus

      if ($checkcook == 'N')
      {
        // Если куки отключены, то проверяем посетителя по ip (за последний час CONCAT(date_last,LEFT(time_last,2))='".date("YmdH")."')
        $statuser = new seTable('stat_users');
        $statuser->select('id');
        $statuser->where("ip_last='?'", $ip);
        $statuser->andWhere("date_last='?'", date("Ymd"));
        $statuser->fetchOne();
        $iduser = $statuser->id;


        if (!empty($iduser))
        {
          // --- Обновляем запись в таблице пользователей ---
          if ($iduserreg > 0)
          {
            $statuser->id_user_reg = $iduserreg;
          }
          $statuser->cookies = $checkcook;
          $statuser->ip_last = $ip;
          $statuser->date_last = $_date;
          $statuser->time_last = $_time;
          $statuser->request_uri_last = $cururi;
          $statuser->page_last = $SE_VARS['page_name'];
          $statuser->save(false);
        }
        else
        {
          // --- Добавляем запись в таблицу пользователей ---
          $statuser->insert();
          $statuser->id_user_reg = $iduserreg;
          $statuser->id_refer = $referal;
          $statuser->ip_first = $ip;
          $statuser->date_first = $_date;
          $statuser->time_first = $_time;
          $statuser->domain_first = $curd;
          $statuser->request_uri_first = $cururi;
          $statuser->page_first = $SE_VARS['page_name'];
          $statuser->ref_domain_first = $refd;
          $statuser->ref_page_first = $refp;
          $statuser->ref_pagequery_first = $refpq;
          $statuser->ref_catalog_first = $refc['id'];
          $statuser->ref_search_sys_first = $refs['id'];
          $statuser->ref_search_query_first = $refsq;
          $statuser->ref_mail_sys_first = $refm['id'];
          $statuser->ref_rating_first = $refr['id'];
          $statuser->cookies = $checkcook;
          $statuser->ip_last = $ip;
          $statuser->date_last = $_date;
          $statuser->time_last = $_time;
          $statuser->request_uri_last = $cururi;
          $statuser->page_last = $SE_VARS['page_name'];
          $iduser = $statuser->save(false);
          setcookie("statidus", $iduser, time() + 3600 * 24 * 365 * 10, "/");
        }

      }
      else
      {
        // Если куки включены, то определяем пользователя по куки
        $statuser = new seTable('stat_users');
        if (!empty($_COOKIE['statidus']))
        {
          // --- Обновляем запись в таблице пользователей ---
          $statidus = intval($_COOKIE['statidus']);
          $statuser->find($statidus);
        }
        if ($statuser->id)
        {

          if ($iduserreg > 0)
          {
            $statuser->id_user_reg = $iduserreg;
          }
          $statuser->cookies = $checkcook;
          $statuser->ip_last = $ip;
          $statuser->date_last = $_date;
          $statuser->time_last = $_time;
          $statuser->request_uri_last = $cururi;
          $statuser->page_last = $SE_VARS['page_name'];
          $statuser->save(false);
          $iduser = $statidus;
        }
        else
        {
          // --- Добавляем запись в таблицу пользователей ---
          $statuser->insert();
          $statuser->id_user_reg = $iduserreg;
          $statuser->id_refer = $referal;
          $statuser->ip_first = $ip;
          $statuser->date_first = $_date;
          $statuser->time_first = $_time;
          $statuser->domain_first = $curd;
          $statuser->request_uri_first = $cururi;
          $statuser->page_first = $SE_VARS['page_name'];
          $statuser->ref_domain_first = $refd;
          $statuser->ref_page_first = $refp;
          $statuser->ref_pagequery_first = $refpq;
          $statuser->ref_catalog_first = $refc['id'];
          $statuser->ref_search_sys_first = $refs['id'];
          $statuser->ref_search_query_first = $refsq;
          $statuser->ref_mail_sys_first = $refm['id'];
          $statuser->ref_rating_first = $refr['id'];
          $statuser->cookies = $checkcook;
          $statuser->ip_last = $ip;
          $statuser->date_last = $_date;
          $statuser->time_last = $_time;
          $statuser->request_uri_last = $cururi;
          $statuser->page_last = $SE_VARS['page_name'];
          $iduser = $statuser->save(false);
          setcookie("statidus", $iduser, time() + 3600 * 24 * 365 * 10, "/");
        }
      }


      //////////////////////////////////////////////
      // Обновляем сессии посещения. Если нужно создаем новую сессию.
      //Удаляем сессии, время которых больше 15 минут и свою
      $timestamp = time();
      $sesid = session_id();
      mysql_query("DELETE FROM `stat_sessions`
                 WHERE (`timestamp` < '" . ($timestamp - 900) . "')OR(`id`='" . $sesid . "')OR(`id_user`='" . $iduser . "');");
      //Добавляем новую сессию
      mysql_query("INSERT INTO `stat_sessions`(id, id_user, timestamp)
                 VALUES ('" . $sesid . "', '" . $iduser . "', '" . $timestamp . "')");


      ////////////////////////////////////////////////
      // Удаляем записи из таблиц по условию ограничения времени хранения данных. (!!!НЕОБХОДИМО ПЕРЕДЕЛАТЬ В ПЛАНИРОВЩИК ЗАДАЧ!!!)
      // Удаляется один раз в сутки
      $resdataclear = mysql_query("SELECT `value` FROM `stat_config`
                                 WHERE CONVERT( `variable` USING utf8 ) = 'timelastclear' LIMIT 1;");
      while ($rowdataclear = mysql_fetch_array($resdataclear, MYSQL_ASSOC))
        $timelastclear = $rowdataclear['value'];
      if (empty($timelastclear))
      {
        mysql_query("REPLACE `stat_config` (`variable`, `value`)
        VALUES ('timelastclear', '" . mktime(0, 0, 0, date('n'), date('d'), date('Y')) . "');");
      }
      elseif ($timelastclear < (time() - 24 * 3600))
      {
          $begdate = date("Ymd", (time() - (intval($savelogday) * 24 * 3600)));
          mysql_query("DELETE FROM `stat_log` WHERE (`date` < '" . $begdate . "');");
          mysql_query("DELETE FROM `stat_logrobots` WHERE (`date` < '" . $begdate . "');");
          mysql_query("DELETE FROM `stat_users` WHERE (`date_last` < '" . $begdate . "');");
          mysql_query("DELETE FROM `stat_total` WHERE (`date` < '" . $begdate . "');");

          mysql_query("UPDATE `stat_config` SET `value` = '" . mktime(0, 0, 0, date('n'), date('d'), date('Y')) . "'
          WHERE CONVERT( `variable` USING utf8 ) = 'timelastclear' LIMIT 1;");
      }
      

      ////////////////////////////////////////////////
      // Добавляем запись в таблицу логов.

      mysql_query("
    INSERT INTO stat_log
           (`id_session`, `id_user`, `ip`, `proxy`, `ref_domain`, `ref_page`, `ref_pagequery`, `ref_catalog`,
            `ref_search_sys`, `ref_search_query`, `ref_mail_sys`, `ref_rating`, `agent`, `browser`,  `os`,
            `acceptlang`, `date`, `time`, `domain`, `request_uri`, `page`, `titlepage`,
            `language`, `existspage`, `city`, `country`)
    VALUES ('" . $sesid . "', '" . $iduser . "', '" . $ip . "', '" . $proxy . "', '" . $refd . "', '" . $refp . "', '" . $refpq . "', '" . $refc['id'] . "',
            '" . $refs['id'] . "', '" . $refsq . "', '" . $refm['id'] . "', '" . $refr['id'] . "', '" . $useag . "', '" . $tbr['id'] . "', '" . $tsys['id'] . "',
            '" . $acceptlang . "', '" . $_date . "', '" . $_time . "', '" . $curd . "', '" . $cururi . "', '" . $SE_VARS['page_name'] . "', '" . $SE_VARS['page_title'] . "',
            '" . $SE_VARS['lang'] . "', '" . $existspage . "', '" . $idcity . "', '" . $idcountry . "')");
      $idlog = mysql_insert_id();


      ////////////////////////////////////////////////
      // Производим итоговый подсчет за день (просмотров, хитов, хостов, пользователей)
      $stat = new seTable('stat_log');
      $stat->select("date, COUNT(DISTINCT id) AS views, COUNT(DISTINCT id_session, page) AS hits,
                             COUNT(DISTINCT ip) AS hosts, COUNT(DISTINCT id_user) AS users");
      $stat->where("date='?'", date("Ymd"));
      $stat->groupBy('date');
      $stat->fetchOne();

      $date = $stat->date;
      $a_views = $stat->views;
      $a_hits = $stat->hits;
      $a_hosts = $stat->hosts;
      $a_users = $stat->users;
      unset($stat);

	

      mysql_query("REPLACE INTO `stat_total` (`date`, `views`, `hits`, `hosts`, `users`)
                 VALUES ('" . $date . "', '" . $a_views . "', '" . $a_hits . "', '" . $a_hosts . "', '" . $a_users . "');");

    }

    $stat_idlog = $idlog;
    $__data->prj->vars->statistic = str_replace(array("[stat_idlog]","[page_timestart]"), array($idlog, $SE_VARS['page_timestart']), $__data->prj->vars->statistic);
	$__data->prj->vars->copyright = str_replace(array("[stat_idlog]","[page_timestart]"), array($idlog, $SE_VARS['page_timestart']), $__data->prj->vars->copyright);

    return $idlog;
  }
  else
  {
    $idlog = '';
    $stat_idlog = $idlog;
    $__data->prj->vars->statistic = str_replace(array("[stat_idlog]","[page_timestart]"), 
		array($stat_idlog, $SE_VARS['page_timestart']), $__data->prj->vars->statistic);
    $__data->prj->vars->copyright = str_replace(array("[stat_idlog]","[page_timestart]"), 
		array($stat_idlog, $SE_VARS['page_timestart']), $__data->prj->vars->copyright);
    return $idlog;
  }

}

if (!empty($SE_VARS['page_timestart']))
  $page_timestart = str_replace(",", ".", $SE_VARS['page_timestart']);
else
  $page_timestart = '';

?>