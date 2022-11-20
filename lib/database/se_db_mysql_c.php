<?php

/*
lib_database.php,v 8.12 2006/02/04

EDGESTILE SiteEdit,
http://www.edgestile.com

Copyright (c) 2006 EDGESTILE
*/
require_once dirname(__file__) . '/parser/yaml_mysql.php';
require_once dirname(__file__) . '/se_db_mysqlcache.php';

function se_db_connect($link = 'db_link')
{
  global $$link, $CONFIG;
  if ($link == "db_link")
  {
    if (isset($CONFIG)){
            $server = $CONFIG['HostName'];
            $username = $CONFIG['DBUserName'];
            $password = $CONFIG['DBPassword'];
            $database = $CONFIG['DBName'];
    } else {
            $server = HostName;
            $username = DBUserName;
            $password = DBPassword;
            $database = DBName;
    }
  }
  else
    if ($link == "info")
    {
      $server = "localhost";
      $username = "info";
      $password = "info";
      $database = "info";
    }

  $$link = mysql_connect($server, $username, $password);
  mysql_query("set character_set_client='UTF8'", $$link);
  mysql_query("set character_set_results='UTF8'", $$link);
  mysql_query("set collation_connection='UTF8_general_ci'", $$link);
  if ($$link)
    mysql_select_db($database);
  return $$link;
}

function se_db_close($link = 'db_link')
{
  global $$link;

  return mysql_close($$link);
}

function se_db_is_correct($table){
    $correcttable = file(dirname(__file__)."/correcttable.dat");
    foreach($correcttable as $line){
	if (empty($line)) continue;
	list($fundtable) = explode("|", $line, 1);
	if ($fundtable == $table) return true;
    }
    return false;
}


function se_db_input($string, $link = 'db_link')
{
  global $$link;
  if (get_magic_quotes_gpc()){
        $string = stripslashes($string);
  }
  	if (function_exists('mysql_real_escape_string'))
  	{
    	return mysql_real_escape_string($string, $$link);
  	} else
	if (function_exists('mysql_escape_string'))
  	{
    	return mysql_escape_string($string);
  	} else return htmlspecialchars($string);
}


function se_db_output($string)
{
  return htmlspecialchars(stripslashes($string));
}
// Проверка на существование поля в таблице
function se_db_is_field($table, $field, $link = 'db_link')
{
  global $$link;

  $aresult = mysql_query("SHOW FIELDS FROM `$table` WHERE Field='$field'", $$link);
  return @mysql_num_rows($aresult);
}

// Создание нового поля в таблице
function se_db_add_field($table, $field, $type = 'varchar(20)', $link = 'db_link')
{
  global $$link;
  
  	$type = str_replace(array('integer', 'string', 'integer(2)', 'integer(4)'), 
					array('int', 'varchar', 'int', 'bigint'), $type);
	if (preg_match("/float(\([\d\,]+\))?/u", $type, $m)){
		$m[1] = preg_replace("/[\(\)]/", '', $m[1]);
		if (!empty($m[1])){
			list($dec,) = explode(',', $m[1]);
			if (floatval($dec) < 8) $newType = 'float('.$m[1].')';
			else $newType = 'double('.$m[1].')';
		} else $newType = 'double(10,2)';
		$type = str_replace($m[0], $newType, $type);
	}

  	mysql_query("ALTER TABLE `{$table}` ADD `{$field}` {$type};", $$link);
}

function se_db_delete_item($table, $id, $link = 'db_link'){
    global $$link;
	$correcttable = file(dirname(__file__)."/correcttable.dat");
	mysql_query("DELETE FROM `$table` WHERE id=$id", $$link);
	foreach($correcttable as $line){
	    if (empty($line)) continue;
		$arr = explode("|", $line);
		if ($arr[0] == $table){
			$field = trim($arr[2]);
			if (trim($arr[3]) == '*')
				mysql_query("UPDATE `$arr[1]` SET `$field` = NULL WHERE `$field` = $id", $$link);
			else {
				$query = mysql_query("SELECT `id` FROM `$arr[1]` WHERE $field = $id", $$link);
				while ($l = mysql_fetch_row($query))
					se_db_delete_item($arr[1], $l[0]);
			}
		}
	}
}

function se_db_delete($table, $where = '', $log_upgate=true, $link = 'db_link'){
    global $$link;
    if (se_db_is_correct($table)){
	$res = mysql_query("SELECT id FROM $table WHERE $where;", $$link);
	if (!empty($res)){
	    while (@$id_rec = mysql_fetch_row($res)){
		se_db_delete_item($table, $id_rec[0]);
		if ($log_upgate) log_update($table, $id_rec[0], 'd');
	    }	
	    return true;
	}
    } else {
	$res = mysql_query("SELECT id FROM $table WHERE $where;", $$link);
	if (!empty($res)){
	    while (@$id_rec = mysql_fetch_row($res)){
		if ($log_upgate) log_update($table, $id_rec[0], 'd');
	    }
	    return mysql_query("DELETE FROM $table WHERE $where", $$link);
	}
    }
    return false;
}

function se_db_perform_restrict($table, $data){
	$correcttable = file(dirname(__file__)."/correcttable.dat");
	foreach($correcttable as $line){
	    if (trim($line)=='') continue;
	    $line = explode("|", $line);
	    if (trim($line[1]) == $table){
	        $id = $data[trim($line[2])];
		if ($id == '' || $id == 'null'){
		    return true;
		}
		if ($id){
		    if (se_db_is_item(trim($line[0]), "id={$id}")){
			return true;
		    }
		}
		return false;
	    }
	}
	return true;
}
function se_db_perform($table, $data, $action = 'insert', $where = '', $log_update = true,  $link = 'db_link')
{
    global $$link;
    reset($data);
    if ($action == 'insert'){
	if (!se_db_perform_restrict($table, $data)) return false;
    //if (!se_db_is_field($table, 'updated_at'))
    //{
    //  se_db_add_field($table, 'updated_at', 'TIMESTAMP');
    //}

    //if (!se_db_is_field($table, 'created_at'))
    //{
    //  se_db_add_field($table, 'created_at', 'TIMESTAMP');
    //}
	
	
    $query = 'insert into ' . $table . ' (';
    while (list($columns, ) = each($data))
    {
      $columns = str_replace('`', '', $columns);
      $query .= '`' . str_replace('`', '', $columns) . '`, ';
    }
    $query = substr($query, 0, -2) . ') values (';
    reset($data);
    while (list($field, $value) = each($data))
    {
      $value = str_replace('\r\n', "\r\n", $value);
      if (empty($value))
        $value = '';
      switch ((string )$value)
      {
        case 'now()':
          $query .= 'now(), ';
          break;
        case 'null':
          $query .= 'null, ';
          break;
        default:
          $query .= '\'' . se_db_input($value) . '\', ';
          break;
      }
    }
    $query = substr($query, 0, -2) . ')';

    $result = mysql_query($query, $$link);


    if ($result)
    {
      if (isset($data['id'])){
         $res = $data['id'];
      } else {
         list($res) = mysql_fetch_row(mysql_query("SELECT LAST_INSERT_ID()", $$link));
      }
      if ($log_update)
      {
        log_update($table, $res, 'i');
      }
      return $res;
    }
    else
    {
      //echo mysql_error() . '<br>' . $query;
    }

    //  return $result;
  } elseif ($action == 'update')
  {
    $query = 'update ' . $table . ' set ';
    while (list($columns, $value) = each($data))
    {
      $columns = str_replace('`', '', $columns);
      $value = str_replace('\r\n', "\r\n", $value);

      if (empty($value))
        $value = '';

      switch ((string )$value)
      {
        case 'now()':
          $query .= '`' . $columns . '` = now(), ';
          break;
        case 'null':
          $query .= '`' . $columns . '` = null, ';
          break;
        default:
          $query .= '`' . $columns . '` = \'' . se_db_input($value) . '\', ';
          break;
      }
      // log_update($table,$id_rec[0],'d');
      //updates_wlog("u",$table,$where);
    }
    $query = substr($query, 0, -2) . ' where ' . $where;
    $result = mysql_query($query, $$link);

      if ($log_update)
      {
         $res = mysql_query("select id from $table where $where;", $$link);
        if (!empty($res))
        while (@$id_rec = mysql_fetch_array($res))
          log_update($table, $id_rec[0], 'u');
      }

    //return $result;
  }

  return $result; //mysql_query($query,$$link);
}

function se_db_is_item($table, $where, $link = 'db_link')
{
  global $$link;
  return (@mysql_num_rows(mysql_query("SELECT * FROM $table WHERE $where", $$link)) > 0);
}

function se_db_insert_id($table, $link = 'db_link')
{
  global $$link;
  //$id=mysql_fetch_array(mysql_query("SELECT LAST_INSERT_ID();",$$link));
  $id = mysql_fetch_array(mysql_query("SELECT max(id) from $table", $$link));
  return $id[0];
}

function se_db_limit($offset = 0, $limit)
{
  if ($offset > 0)
    $limit = ' LIMIT ' . $offset . ',' . $limit;
  else
    $limit = ' LIMIT ' . $limit;

  return $limit;
}

function se_db_query($sql, $cashetime = 10,$link = 'db_link')
{
  global $$link;

  if (preg_match("/DELETE FROM(.+?)WHERE([\w\W\S\s\d]{1,})/im", $sql, $res_math))
  {
    $table = trim(str_replace('`', '', @$res_math[1]));
    if ($table != 'session')
    {
      $where = @$res_math[2];
      $res = mysql_query("SELECT id FROM $table WHERE $where", $$link);
      if (!empty($res))
        while (@$id_rec = mysql_fetch_array($res))
          log_update($table, $id_rec[0], 'd');
    }
  }
  if (preg_match("/^SELECT/", $sql) && ($cashetime>0)){
      return new MySQLCache($sql, $cashetime); //, $link
  } else {
      $result = @mysql_query($sql, $$link);
  }

  if (preg_match("/INSERT INTO(.+?)[\W]{1,}\(/im", $sql, $res_math))
  {
    $table = trim(str_replace('`', '', @$res_math[1]));
    if ($table != 'session')
    {
      $res = mysql_fetch_array(mysql_query("SELECT LAST_INSERT_ID()", $$link));
      log_update($table, $res[0], 'i');
    }
  }
  if (preg_match("/UPDATE(.+?)SET[\w\W]{1,}where([\w\W\S\s\d]{1,})/im", $sql, $res_math))
  {
    $table = trim(str_replace('`', '', @$res_math[1]));
    $table = trim(str_replace(' ', '', $table));
    if ($table != 'session')
    {
      $where = @$res_math[2];
      $res = mysql_query("SELECT id FROM $table WHERE $where", $$link);
      if (!empty($res))
        while (@$id_rec = mysql_fetch_array($res))
          log_update($table, $id_rec[0], 'u');
    }
  }
  return $result;
}

function se_db_columns_field($table, $link = 'db_link'){
  global $$link;
  	$result = array();
	$q = mysql_query("SHOW COLUMNS FROM `{$table}`", $$link);
    while ($value = mysql_fetch_assoc($q)){
    	$result[] = $value['Field'];
    }
    return $result;
}

function se_db_fetch_array(&$query){
  if (!empty($query->ResultData))
  return $query->fetch_array();
  else return @mysql_fetch_array($query);
}

function se_db_num_rows(&$query){
  if (!empty($query->ResultData))
      return $query->num_rows();
  else return (@mysql_num_rows($query));
}

function se_db_fetch_row(&$query){
  if (!empty($query->ResultData))
  return $query->fetch_row();
  else return mysql_fetch_row($query);
}

function se_db_fetch_assoc(&$query){
  if (!empty($query->ResultData))
  return $query->fetch_assoc();
else return mysql_fetch_assoc($query);
}

function se_db_fields($razdel, $table, $where, $select, $fieldreplace = '', $link = 'db_link')
{
  global $obj, $object_extern, $table_field_error, $tablevalues, $$link;
  $tablevalues = "";
  $fieldrepl = explode(";", $fieldreplace);
  if ($result = mysql_query("SELECT $select FROM $table WHERE $where", $$link))
  {
    $count = mysql_num_rows($result);
    for ($nn = 0; $nn < $count; $nn++)
    {
      $tabl = mysql_fetch_array($result);
      $j = 0;
      while (@$linerepl = $fieldrepl[$j])
      {
        $linerepl = explode(",", $linerepl);
        if (@$tabl[$linerepl[0]] == @$linerepl[1])
          @$tabl[$linerepl[0]] = $linerepl[2];
        $j++;
      }

      $tablevalues = $tabl;
      $obj[$razdel][$nn] = replace_obj(0, $tabl, $razdel, $nn, "", "");
    }
  }
  else
    $table_field_error = "Ошибка чтения таблицы";

  $object_extern[$razdel] = true;
}


function se_db_fields_list($table, $where, $select, $maska, $itemtext = "", $itemfield = -1, $fieldreplace = "", $styles = "", $link = 'db_link')
{
  global $$link, $table_field_error, $itemselect;
  $tabvalue = "";
  $fieldrepl = explode(";", $fieldreplace);
  $itemselect = -1;
  $mstyles = array();
  if (!empty($styles))
    $mstyles = explode(",", $styles);
  $cnstyle = count($mstyles);

  if ($result = mysql_query("SELECT $select FROM $table WHERE $where", $$link))
  {
    $nn = 0;
    $cn = 0;
    while ($tabl = mysql_fetch_array($result))
    {
      $j = 0;
      if ($fieldreplace != "")
        while (@$linerepl = $fieldrepl[$j])
        {
          $linerepl = explode(",", $linerepl);
          @$tabl[$linerepl[0]] = str_replace($linerepl[1], $linerepl[2], $tabl[$linerepl[0]]);
          $j++;
        }


      $fmas = $maska;
      if ($cn == $cnstyle)
        $cn = 0;
      @$getstyle = $mstyles[$cn];
      $cn++;

      if ((@$itemfield >= 0) && ($itemtext == @$tabl[$itemfield]))
      {
        $select = "SELECTED";
        $itemselect = @$tabl[$itemfield];
      }
      else
        $select = "";
      //$i=0;
      foreach ($tabl as $k => $dline)
      {
        $fmas = str_replace("[@col" . $k . "]", $dline, $fmas);
      }
      ;
      $fmas = str_replace("[@row]", $nn + 1, $fmas);
      $fmas = str_replace("[@select]", $select, $fmas);
      $fmas = str_replace("[@style]", $getstyle, $fmas);

      $tabvalue .= preg_replace("/(\[@col)\d{1,2}\]/m", "", $fmas) . "\r";
      unset($fmas);
      $nn++;
    }

  }
  else
    $table_field_error = "Ошибка чтения таблицы";

  return $tabvalue;
}


function se_db_fields_item($table, $where, $select, $link = 'db_link')
{
  global $$link, $table_field_error;
  $itemval = "";
  //$varsel=explode(",",$select);
  $table = htmlspecialchars($table);
  $select = htmlspecialchars($select);
  if ($result = mysql_query("select $select FROM $table WHERE $where", $$link))
  {
    @$itemval = mysql_fetch_array($result);
    if (count($itemval) < 3)
    {
      $itemval = $itemval[0];
    }
  }
  else
    $table_field_error = "Ошибка чтения таблицы";
  return ($itemval);
}

function log_update($table, $id_rec = 0, $typech = 'i', $id_clman = 0, $id_log = 0, $fields = '')
{
  if ($typech != 'd' || strpos($table, 'stat_')!==false) return;
  if (!file_exists(SE_ROOT .'system/logs/log_synchro.upd'))
  {
    mysql_query("CREATE TABLE IF NOT EXISTS `log_synchro` (
		`id` bigint NOT NULL auto_increment,
		`timesynchro` int(15),
		`tabl` varchar(40) NULL default '',
		`id_rec` bigint(20) default '0',
		`typech` char(1) default 'i',
		`id_clman` int(10) default '0',
		`id_log` bigint(20) default '0',
		`fields` varchar(255),
		 PRIMARY KEY (`id`),
		 KEY `timesynchro` (`timesynchro`),
		 KEY `tabl` (`tabl`),
		 KEY `id_rec` (`id_rec`),
		 KEY `typech` (`typech`)
		) TYPE=MyISAM;");
	if (mysql_error() == ''){
		if (!is_dir(SE_ROOT .'system/logs')) mkdir(SE_ROOT .'system/logs');
		$fp = fopen(SE_ROOT .'system/logs/log_synchro.upd', "w+");
		fclose($fp);
	}
  }
  
  $deltime = time() - 86400 * 10;
  if ($typech == 'd') {
  	mysql_query("DELETE FROM `log_synchro` WHERE `timesynchro`<'$deltime'");
  }
                              
  $timestamp = time();
  mysql_query("INSERT INTO `log_synchro` (`tabl`,`id_rec`,`typech`,`timesynchro`,`id_clman`,`id_log`,`fields`)
    VALUES ('$table','$id_rec','$typech','" . $timestamp . "','$id_clman','$id_log', '$fields')");
}