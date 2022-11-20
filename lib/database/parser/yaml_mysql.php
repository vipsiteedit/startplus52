<?php
/** ��������� Yaml � SQL ������� */

function se_yaml_to_sql($ymlfile, $outpath = '', $foreign_keys = true, $add_values = false)
{
    $typarr = array(
	'integer(2)'=>'int(10)  unsigned',
	'integer(4)'=>'bigint(20) unsigned',
	);
	
	$sql = '';
	$res_sql = '';
    $ymlres = seYAML::Load($ymlfile);
    $table = array();
    foreach($ymlres as $classname=>$value)
    {
    	//echo $classname;
		$sql = '';
    	$tablename = $value['tableName'];
    	$tabletype = $value['tableType'];
    	// ���������� ������
	$relations = $value['relations'];
	$actAs = $value['actAs'];
	$valueinsert = $value['values'];
	$indexes = $value['indexes'];
    	
    	$sequence = '';
    	$primary = '';
    	$unique = '';
    	$indexkey = array();
		if ($tablename != '')
		{
			if ($value['tableDrop'])
				$sql .= "DROP TABLE IF EXISTS `{$tablename}`;\n\n";

			$sql .= "CREATE TABLE IF NOT EXISTS `{$tablename}` ( ";
			foreach($value['columns'] as $field=>$valfield)
			{
			
			
				$unsigned = '';
				$default = $notnull = $sequence = '';
				if (is_array($valfield))
				{ 
					$type = $valfield['type'];
					if ($valfield['index'])
						$indexkey[] = array('field'=>$field, 'type'=>'index');

					if ($valfield['unique'])
						$indexkey[] = array('field'=>$field, 'type'=>'unique');

					
					if (!empty($valfield['primary'])) {
						$primary = $field; 	$notnull = ' NOT NULL';
						if (strpos($type, 'integer')!==false)
						{
							$unsigned = ' unsigned';
						}
					}
					if ($valfield['unsigned'])
					{
						$unsigned = ' unsigned';
					}
					
					if (!empty($valfield['notnull']) && $valfield['notnull']=='true') $notnull = ' NOT NULL';
					if (!empty($valfield['default'])) $default = " default '{$valfield['default']}'";
					if (!empty($valfield['sequence'])) $sequence = ' auto_increment';
				} else $type = $valfield;
				
				if (strpos($type, 'string(')!==false) 
				{
					$stype = explode('(', $type);
					list($countchar) = explode(')', $stype[1]);
					if ($countchar > 255) $type = 'text';
				}
				if (strpos($type, 'date(')!==false) 
				{
					$type = 'date';
				}

				
				$type = str_replace(array('integer', 'string', 'integer(2)', 'integer(4)'), 
					array('int', 'varchar', 'int', 'bigint'), $type);

				if (preg_match("/float(\([\d\,]+\))?/u", $type, $m))
				{
					$m[1] = preg_replace("/[\(\)]/", '', $m[1]);
					if (!empty($m[1])) 
					{
						list($dec,) = explode(',', $m[1]);
						if (floatval($dec) < 8) $newType = 'float('.$m[1].')';
						else $newType = 'double('.$m[1].')';
					} 
					else $newType = 'double(10,2)';
					$type = str_replace($m[0], $newType, $type);
				}
				
				//$type = str_replace(array('float(10)', 'integer(2)', 'integer(4)'), array('varchar', 'int', 'bigint'), $type);
				if ($type=='enum')
				{
						if (!empty($valfield['values']))
						{
							$type .= '(';
							//print_r($valfield['values']);
							
							foreach($valfield['values'] as $val) $type .= "'". $val . "',";
							$type = substr($type, 0, -1) . ')';
						}

				}

				$sql .= "\n `$field` $type" . $unsigned . $notnull . $default . $sequence . ",";
				$table[$field]['type'] = $type;

				if (!empty($unsigned))
				    $table[$field]['unsigned'] = $unsigned;
				if (!empty($default))
				    $table[$field]['default'] = $default;
				
			}
			if (!empty($actAs))
			{
				$sql .= "\n `updated_at` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,";
				$sql .= "\n `created_at` timestamp NOT NULL default '0000-00-00 00:00:00',";
				//$sql .= "\n `deleted` boolean NOT NULL default '0',";

				$table['updated_at']['type'] = 'timestamp';
				$table['updated_at']['unsigned'] = 'on update CURRENT_TIMESTAMP';
				$table['updated_at']['default'] = '0000-00-00 00:00:00';
				$table['created_at']['type'] = 'timestamp';
				$table['created_at']['default'] = '0000-00-00 00:00:00';
			}
			
			if (!empty($primary)) $sql .= "\n PRIMARY KEY  (`$primary`),";

			if (!empty($relations))
			foreach($relations as $references=>$valuefield)
			{
				$fl = false;
				foreach($indexkey as $index)
				{
					if ($index['field'] == $valuefield['local'] || $primary == $valuefield['local'])
					{
						$fl = true; break;
					}
				}
				if (!$fl)
					$indexkey[] = array('field'=>$valuefield['local'], 'type'=>'index');
			}

			if (!empty($indexkey))
			{
				foreach($indexkey as $index)
				{
				   // ���������� �������
				   if ($index['type'] == 'index')
				   {
				   	  $sql .="\n KEY `{$index['field']}` (`{$index['field']}`),";
				   }
				   // ���������� ���������� �������
				   if ($index['type'] == 'unique')
				   {
				   	  $sql .="\n UNIQUE KEY `{$index['field']}` (`{$index['field']}`),";
				   }
				}
			}
			if (!empty($indexes)){
				foreach($indexes as $name=>$index)
				{
				   // ���������� �������
				   if ($index['type'] == 'index')
				   {
				   	  $sql .="\n KEY `{$name}` (";
				   }
				   // ���������� ���������� �������
				   if ($index['type'] == 'unique')
				   {
				   	  $sql .="\n UNIQUE KEY `{$name}` (";
				   }
				   	  foreach($index['fields'] as $field){
				   	      $sql .= '`'. $field . '`,';
				   	  }
			                  $sql = substr($sql, 0, -1);
				   	  $sql .= '),';
				}
			}


			$sql = substr($sql, 0, -1);
			$sql .= "\n)";
			if (!empty($tabletype['engine']))
				$sql .= " ENGINE=". $tabletype['engine'];
			if (!empty($tabletype['charset']))
				$sql .= " DEFAULT CHARSET=".$tabletype['charset'];
			$sql .= ";\n###\n";

			$i = 1;
			if (!empty($relations) && $foreign_keys)
			foreach($relations as $references=>$valuefield)
			{
				$sql .= "ALTER TABLE `{$tablename}`";
				$sql .= " ADD CONSTRAINT `{$tablename}_ibfk_{$i}` FOREIGN KEY (`{$valuefield['local']}`)";
				$sql .= " REFERENCES `{$references}` (`{$valuefield['foreign']}`)";
				if (!empty($valuefield['onDelete']))
					$sql .= " ON DELETE {$valuefield['onDelete']}";
				if (!empty($valuefield['onUpdate']))
					$sql .= " ON UPDATE {$valuefield['onUpdate']}";
				$sql .= ";\n###\n";
				// �����
				$i ++; 	
			}
		
			// ���� ���� ������ �� ��������� - �������� ��
			if (!empty($valueinsert) && $add_values)
			{
				foreach($valueinsert as $valuenext)
				{
				        $sql .= "\n\nINSERT INTO `{$tablename}`(";
				        foreach($value['columns'] as $field=>$valfield)
				        {
					  if ($valuenext[$field] != '')
					    $sql .= "`$field`,"; 
				        }
				        $sql = substr($sql, 0, -1).") VALUES";
					$sql .= " ("; 
					foreach($value['columns'] as $field=>$valfield)
					{
					   if ($valuenext[$field] != ''){
                                        	$values = mysql_real_escape_string($valuenext[$field]);
						if ($values != '')
						$sql .= " '" . $values . "',";
                                     }
					}
					$sql = substr($sql, 0, -1);
					$sql .= ");\n###\n"; 
      			}
				//$sql = substr($sql, 0, -1) . ';';
				//echo $sql . "\n";
			}
			
		
		   if (!empty($outpath))
		   {
			if (!is_dir($outpath)) mkdir($outpath);
			
			$fp = fopen($outpath  .$tablename.'.sql', "w+");
			fwrite($fp, $sql);
			fclose($fp);
			
			$path_table = dirname(__file__) . '/../tables/';

			$fp = fopen($path_table . $tablename.'.ser', "w+");
			fwrite($fp, serialize($table));
			fclose($fp);
		  }
		  $res_sql .= $sql;
		}
    	
    }
    return $res_sql;
}

function se_db_to_yaml($table, $ymlfile, $link = 'db_link')
{
	global $$link;

	$result = mysql_query("SHOW COLUMNS FROM `{$table}`", $$link);

	$yaml = $table.":\n";
	$yaml .= "   tableName: {$table}\n";
	$yaml .= "   tableType:\n";
	$yaml .= "     engine: innoDB\n";
	$yaml .= "     charset: utf8\n";

	$tablist = array();
	$actAs = false;

	while ($value = mysql_fetch_assoc($result))
	{
	    if ($value['Field'] == 'updated_at' || $value['Field'] == 'created_at') {
		$actAs = true;
	    } else {
		$tablist[] = $value;
	    }
	}

	if ($actAs){
		$yaml .= "   actAs: [Timestampable]\n";
	}
	$yaml .= "   columns:\n";

	
	foreach($tablist as $value)
	{
	    if ($value['Field'] == 'updated_at' || $value['Field'] == 'created_at') {
	       continue;
	    }
		//print_r($value);
 		$values = '';
		 //([Field] => id, [Type] => int(10) unsigned,[Null] => NO, [Key] => PRI, [Default] =>,[Extra] => auto_increment )
		//( [Field] => id_up,[Type] => int(11),[Null] => NO,[Key] => MUL, [Default] => 0,[Extra] =>)
		$yaml .= "     {$value['Field']}:\n";
		list($type, $unsigned) = explode(' ', str_replace(array('varchar', 'double'), 
			array('string', 'float'), $value['Type']));

		if (preg_match("/(int|bigint|enum)(\(.*\))?/u", $type, $m))
		{
			$m[2] = preg_replace("/[\(\)]/", '', $m[2]);
			if ($m[1]!='enum' && !empty($m[2])) 
			{
				if ($m[1] <= 11) $type = 'integer';
				else $type = 'integer(4)';
			} 
			if ($m[1]=='enum')
			{
				$values = $m[2];
				$type = $m[1];
			}
		}

		$yaml .= "       type: {$type}\n";
		if (!empty($values)){
		    if ($values == 'Y' || $values == 'N') $values = "'".$values ."'";
		    $yaml .= "       values: [{$values}]\n";
		}
	
		if (!empty($unsigned))
			$yaml .= "       unsigned: true\n";

		if ($value['Null'] == 'NO')
			$yaml .= "       notnull: true\n";

		if ($value['Key'] == 'PRI')
			$yaml .= "       primary: true\n";
		if ($value['Key'] == 'MUL')
			$yaml .= "       index: true\n";
		if ($value['Key'] == 'UNI')
			$yaml .= "       unique: true\n";
		if (!empty($value['Default'])){
		    if ($value['Default'] == 'Y' || $value['Default'] == 'N') $value['Default'] = "'".$value['Default'] ."'";
			$yaml .= "       default: {$value['Default']}\n";
		}
 		if ($value['Extra'] == 'auto_increment')
			$yaml .= "       sequence: {$table}_{$value['Field']}\n";
    }
    // �������� ������
	$result = mysql_query("SELECT * FROM `{$table}`", $$link);
    if (!empty($result)){	
	$yaml .= "   values:\n";
	$n = 0;
       while ($values = mysql_fetch_assoc($result))
       {
	    $n++;
	    $yaml .= "     {$n}:\n";
	    foreach($values as $param=>$value){
	        if ($param == 'updated_at' || $param == 'created_at') continue;
		if ($value == 'Y' || $value == 'N') $value = "'".$value ."'";
		$yaml .= "       {$param}: {$value}\n";
	    }
    
    
       }
    }
			if (!is_dir(dirname($ymlfile))) mkdir(dirname($ymlfile));
			//echo join("<br>", explode("\n", $yaml));
			$fp = fopen($ymlfile, "w+");
			fwrite($fp, $yaml);
			fclose($fp);
    return $yaml;
	
}

function se_table_migration($migration_table, $link = 'db_link')
{
	global $$link;
    $table = '';
	if (empty($migration_table)) return;
	
	
      $mfile = substr(dirname(__file__), 0, -7) . '/migration/'.$migration_table.'.yml';

      if (file_exists($mfile))
		$migration = seYAML::Load($mfile);
	  else $migration = null;	

    	$tables = array();
		$sfile = substr(dirname(__file__), 0, -7) . '/schema/'.$migration_table.'.yml';
      	if (!file_exists($sfile)) return;
    	$schema = seYAML::Load($sfile);
    	$schemafields = array();
    	foreach($schema as $value)
    	{
			foreach($value['columns'] as $field=>$valfield)
			{
				$schemafields[] = $field;
				$fields[$field] = $field;
				if ($valfield['notnull'] == 'true')
				{
				  $notnull[$field] = true;
				}
			}
   	}

    	if (!empty($migration)){
			foreach($migration as $value)
    		{
    			$tablename1 = $value['tableName1'];
    			$charset1 = $value['charset1'];
    			$table = $value['tableName2'];
    			$charset2 = $value['charset2'];
				foreach($value['columns'] as $field=>$valfield)
				{
					$fields[$field] = $valfield;
				}
   			}
		}
        
	//mysql_query("SET FOREIGN_KEY_CHECKS=0;", $$link);
	//mysql_query("SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";");


	$qtable = mysql_query("SHOW TABLE STATUS WHERE `ENGINE`='innoDB' AND `NAME`='{$migration_table}'");
	if (empty($table) || $migration_table . '_tmp'==$table || $migration_table==$table)
	{
	
	    if (!empty($qtable)){
	       if (mysql_num_rows($qtable) == 0){
	    //echo 'table='.$migration_table."\n";
		$table = $migration_table.'_tmp';
		//echo ' remote ',$table."\n";
		mysql_query("RENAME TABLE `$migration_table` TO `$table`", $$link);

		$result = mysql_query("SHOW COLUMNS FROM `{$table}`", $$link);
    		if (!empty($result))
		while ($value = mysql_fetch_array($result)){
			$fields[$value['Field']] = $value['Field'];
		}
	       } else return;
	    }	     
	
	} else {
	    if (!empty($qtable) && mysql_num_rows($qtable) > 0) return;
        }

//return;
	/*
		$yaml = $table.":\n";
		$yaml .= "   tableName: {$migration_table}\n";
		$yaml .= "   columns:\n";
		foreach($fields as $field=>$tablfield)
		{
			$yaml .= "     $field:\n";
		}
	*/



    //echo "create table " . $migration_table . "\n";
    mysql_query("DROP TABLE IF EXISTS `$migration_table`", $$link);

	// ������ �� ��������� �������
	$sql = "SELECT * FROM `$table`";
	$result = mysql_query($sql, $$link);
	//echo mysql_error();

    // ���� ������ ���� � ������ ��������, �� ��������� � �����
    @$add_value =  (mysql_num_rows($result) == 0);
    
    // ���� ������ ���, �� ������������� ������ �� ���������
	$sql = se_yaml_to_sql($sfile,'sql/',true, $add_value);
    $sqlarr = explode("\n###\n", $sql);
    foreach($sqlarr as $sql)
    {
		//echo $sql ."\n";
		mysql_query($sql, $$link);
		//echo mysql_error()."\n\n";
    }
//$yaml .= "   values:\n";


    if (!empty($result))
	while ($value = mysql_fetch_array($result))
	{
		$sql = "INSERT INTO `{$migration_table}`(";
		foreach($fields as $field=>$tablfield)
		{
			if (empty($value[$tablfield]) || !in_array($field,$schemafields)) continue;
			$sql .= "`$field`,"; 
		}
		$sql = substr($sql, 0, -1).") VALUES(";
		foreach($fields as $field=>$tablfield)
		{
			if (empty($value[$tablfield]) || !in_array($field,$schemafields)) continue;
			//  $sql .= "NULL,"; 
			$sql .= "'" . se_db_input($value[$tablfield]) . "',"; 
			///$sql .= "'".iconv($charset2, $charset1, $value[$tablfield])."',"; 
		}
		$sql = substr($sql, 0, -1);
		$sql .= ");"; 
		//echo $sql."\n";
		@mysql_query($sql, $$link);
		//echo mysql_error()."\n";
	}
	//mysql_query("SET FOREIGN_KEY_CHECKS=1;", $$link);
    //mysql_query("DROP TABLE IF EXISTS `$table`", $$link);
	
}

?>