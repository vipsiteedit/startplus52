<?php
set_time_limit(0);
header('Content-Type: application/octet-stream');

require_once ("../system/conf_mysql.php");
require_once ("../lib/lib_database.php");
se_db_dsn('mysql');
// ��������� ���� ������
se_db_connect();
require_once ("../lib/lib.php");

error_reporting(0);//125

$half = join("", file("../system/.rkey"));
$half1 = @$_POST['half'];
$half2 = substr($half, 35, 10);
$sk = substr($half, 45, 32);


if (md5($half1 . $half2) != $sk){
  exit('no');
}

$data = 'OK:';

function dump($val)
{

  global $data;

  $len = strlen($val);

  if ($len < 255)
    $data .= chr($len);

  else
    $data .= "\xFF" . pack('V', $len);

  $data .= $val;

  if (strlen($data) > 100000)
  {

    echo $data;
    $data = '';

  }
}

function fieldname_validate($table, &$dataarr){
			$arr = se_db_columns_field($table);
			if (!empty($fields))
			foreach($fields as $field=>$value){
				if (!in_array($field, $arr)) {
					unset($fields[$field]);
				}
			}
}

function fields_validate($table, &$fields){
			$arr = se_db_columns_field($table);
			foreach($fields as $id=>$field){
				if (!in_array($field, $arr)) {
					unset($fields[$id]);
				}
			}
}

function commits($commit, $id_man){
global $table_id;
	// ������� � ������������ �������
	@list($head, $datalist) = explode('^^', $commit);
	@list($flag, $table, $id, $time, $fields, $poslimit, $limit) = explode(':', $head);

	$datalist = explode('|', $datalist);
	//$time = intval($time);
	// ��������� ����������
	if ($flag == 's') {

		$q = se_db_query("SELECT `timesynchro`,`tabl`,`id_rec`,`typech` FROM log_synchro WHERE (id_clman<>'$id_man') AND (`timesynchro`>'$time') ORDER BY `id`");
		if (!empty($q)){
			$listarr = array();
			if (!empty($q))
			while ($row = se_db_fetch_assoc($q)){
				$listarr[] = $row;
			}
			if (!empty($listarr)){
				dump(count($listarr[0])); // ����� �����
				dump(count($listarr)); // ����� �����
				foreach($listarr[0] as $field=>$value){
					dump($field);
				}
				foreach($listarr as $row=>$list){
					foreach($list as $field=>$value){
          				if (is_null($value))
            				dump('');
						else
            				dump(' ' . $value);
					}
				}
			}
		}
	
	}
	

	if ($flag == 'm') {
	// ��������	������������ ����� �������������
		$synchro = se_db_fields_item('log_synchro','1', 'max(timesynchro)');
		dump(0); // ����� �����
		dump(1); // ����� �����
		dump(' '.$synchro);
	}	

	if ($flag == 'f') {
		se_db_query("DROP TABLE `log_synchro`;");
	}	
	
	$fieldlist = explode(',',$fields);

	// ����� ������
	if ($flag == 'i') {
		$dataarr = array();
		foreach($fieldlist as $id_field=>$field){
			$field = str_replace('`', '', $field);
			$dataarr[$field] = $datalist[$id_field];
		}
		if (!empty($table_id[$table])){
		     $dataarr['id'] = $table_id[$table];
		     unset($table_id[$table]);
		} elseif($table == 'user_urid' || $table == 'person') {
		     $dataarr['id'] = $id;
		}
		
		
		$id_rec = se_db_fields_item('log_synchro', "`tabl`='$table' AND `typech`='i' AND `id_log`='$id'", 'id_rec');
		if ($id_rec){
			dump(0);
			dump(1);
			dump(' '.$id_rec[1]);
		} else {
			fieldname_validate($table, &$dataarr);
			$id_rec = se_db_perform($table, $dataarr, 'insert', '', false);
			if ($id_rec > 0)
			  log_update($table, $id_rec, 'i', $id_man, $id);
	//print_r($dataarr);
			dump(0);
			dump(1);
			dump(' '.$id_rec);
		}
                if ($table == 'se_user') $table_id['person'] = $table_id['user_urid'] = $id_rec;
	}

	// �������� ������
	if ($flag == 'u') {
		$dataarr = array();
		foreach($fieldlist as $id_field=>$field){
			$field = str_replace('`', '', $field);
			$dataarr[$field] = $datalist[$id_field];
		}
		fieldname_validate($table, &$dataarr);
		    dump(0);
		    dump(1);

		if (se_db_is_item($table, "id=$id") && se_db_perform($table, $dataarr, 'update', "id=$id", false))
		{
		    log_update($table, $id, 'u', $id_man);
		    dump(' 1');
		} else
		    dump(' 0');
	}

	// ������� ������
	if ($flag == 'd') {
		se_db_delete($table, "id={$id}", false);
		log_update($table, $id, 'd', $id_man);
		dump(0);
		dump(1);
		dump(' 1');
	}

	// ����� ������� � �������
	if ($flag == 'c') {
		$rec_count = se_db_fields_item($table, '1', "count(*)");
		dump(0);
		dump(1);
		dump(' '.$rec_count);
	}

	// Max ID in table
	if ($flag == 'r') {
		$rec_max = se_db_fields_item($table, '1', "max(id)");
		dump(0); // ����� �����
		dump(1); // ����� �����
		dump(' '.$rec_max);
	}	

	// ��������� ������
	if ($flag == 'l') {
		if (empty($fields)) $fields = '*';
		else 
		{
			$fields = explode(',', $fields);
			fields_validate($table, &$fields);
			$fields = join(',', $fields);
		}
		if ($id > 0){
		//echo "SELECT $fields FROM `{$table}` WHERE `id`={$id}\n";
		      $q = se_db_query("SELECT $fields FROM `{$table}` WHERE `id`={$id}");
		      if (se_db_num_rows($q)){
				$row = se_db_fetch_assoc($q);
      				dump(count($row)); // ����� �����
      				dump(1); // ����� �����
				foreach($row as $field=>$value){
				    dump($field);
				}
				foreach($row as $field=>$value){
          			    if (is_null($value))
            			    	dump('');
				    else
            				dump(' ' . $value);
				}
			} else {
			   dump(0);
			   dump(1);
			   dump('');
			}
		} else {
			$sqllimit = '';
			if (intval($limit) > 0){
				$sqllimit = ' LIMIT ' . strval(intval($poslimit).','.intval($limit));
			}
			$q = se_db_query("SELECT $fields FROM `{$table}` {$sqllimit};");
			$listarr = array();
			if (!empty($q))
			while ($row = se_db_fetch_assoc($q)){
				$listarr[] = $row;
			}
			if (!empty($listarr)){
				dump(count($listarr[0])); // ����� �����
				dump(count($listarr)); // ����� �����
				foreach($listarr[0] as $field=>$value){
					dump($field);
				}
				foreach($listarr as $row=>$list){
					foreach($list as $field=>$value){
          				if (is_null($value))
            				dump('');
						else
            				dump(' ' . $value);
					}
				}
			}
		}
	}
}

if (get_magic_quotes_gpc())
  foreach ($_POST as $key => $value)
    $_POST[$key] = stripslashes($value);

if (isset($_POST['clientman']))
	$id_clman = intval($_POST['clientman']);
else $id_clman = 0;

//mysql_free_result(mysql_query('BEGIN'));
$data = 'OK:';
for ($rn = 1; $rn < 1000; ++$rn)
{
  if (!array_key_exists('r' . $rn, $_POST))
    break;

  // ������������ ������� 
  commits($_POST['r' . $rn], $id_clman);
}
if ($data != '')
  echo $data;

?>