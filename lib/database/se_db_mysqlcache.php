<?php

			    			      

class MySQLCache {
   //���� � ���������� ���-������
   private $CachePath='/cachedb';     //���������� ������ ������ ����

   //��� ����� � ����������� � ������� ��������
   private $PeakFilename='!peak.txt';

   //����, ��� ��������� �������� ������ �������� ��������� �� �����
   public $Debug=false;

   //����, �����������, ��� ������ �������� �� ����
   public $FromCache=false;

   //���� ������������ ������
   public $DataDate=0;

   //��������� ��� ������ ���������� ��������� �������� � MySQL
   public $errno=0;

   //������ ������ ��������� �������� � MySQL
   public $error='';

   //���������� � ������� ��������
   private $Peak=array(
      0,    //����� ����������
      '',   //���� ����������
      '',   //������
      '',   //��������� ������
   );

   //����� ��������� ���������� ������
   public $NextRowNo=0;


   //������ ����������� �������
   public $ResultData=array(
      'fields'=>array(),
      'data'=>array(),
      'foundrows'=>0
   );

/*
 *--------------------------------------------------------------------------
 * �����������
 * ��������� � �������� ��������� ������ SELECT
 * � ����� ���������� ����������� ������� � ��������, ���� ����� ����������.
 * ���������� ���������� �������� ���������� �������.
 * ���� ������ �� SELECT, �� ���������� ��������� ���������� ����� �������.
 * ��� ������ ��������; ������� �������� ������ ��� ���� �� ����������.
 *--------------------------------------------------------------------------
 */
   public function MySQLCache($query, $valid=10){ //, $link = 'db_link'
      global $MEM;
      if ($this->CachePath==''){
         $this->CachePath=dirname(__FILE__);
      }
      $query = trim($query);
      if (!preg_match("/^SELECT/i", $query)){
         return mysql_query($query); //, $$link
      }
      $hashkey = md5($_SERVER['HTTP_HOST'].$query);
      $filename = getcwd().$this->CachePath.'/'.$hashkey.'.txt';
      if (!is_dir(getcwd().$this->CachePath)) mkdir(getcwd().$this->CachePath);

      if (!empty($MEM)){
        $serial = @$MEM->get($hashkey);
        if (!empty($serial)){
            $this->ResultData = unserialize($serial);
            $this->DataDate = time();
            $this->FromCache = true;
	    $this->logWork(1);
            return true;
        }
        $this->setData($query);
        if ($valid>0) {
           $MEM->set($hashkey, serialize($this->ResultData), false, $valid);
	   $this->logWork(0);
        }
        //$memcache_obj->close();
      } else {
        /* ������� ������ ���-����� */
        if ((@$file = fopen($filename, 'r')) && filemtime($filename)>(time()-$valid)){
            flock($file, LOCK_SH);
            $serial = file_get_contents($filename);
            $this->ResultData = unserialize($serial);
            $this->DataDate = filemtime($filename);
            $this->FromCache = true;
            fclose($file);
            return true;
        }
        if ($file){
            fclose($file);
    	    $this->delete_badfile(getcwd().$this->CachePath, $valid);
        }
        
        /* ���������� ������� */
        $this->setData($query);

        /* ������ ���� */
        if ($valid>0) {
           $file = fopen($filename, 'w');
           flock($file, LOCK_EX);
           fwrite($file, serialize($this->ResultData));
           fclose($file);
        }
      }
      return true;
   }


   private function logWork($flag = 0)
   {
        return;
        //if (!$this->Debug) return;
        $peak_filename = getcwd().$this->CachePath.'/db_log.dat';
        if (file_exists($peak_filename)) {
    	    $loglist = file($peak_filename);
    	    $log = explode('|', join('',$loglist));
    	} else {
    	    $log = array(0,0);
    	}
           $log[$flag] = intval($log[$flag]) + 1;
           $file = fopen($peak_filename, 'w+');
	   flock($file, LOCK_EX);
           fwrite($file, join('|', $log));
           fclose($file);
   }

   private function setData($query)
   {
        $time_start = microtime(true);
        @$SQLResult = mysql_query($query);
        $time_end = microtime(true);
        $this->DataDate = time();
        $time_exec = $time_end - $time_start;
        /* ��������� ������ ������� */
        if (!$SQLResult){
         if ($this->Debug){
            die('Error from query "'.$query.'": '.mysql_error());
         } else {
            $this->errno=mysql_errno();
            $this->error=mysql_error();
            return false;
         }
        }
        /* �������� ������� �������� */
        $peak_filename = getcwd().$this->CachePath.'/'.$this->PeakFilename;
        if (@$file = fopen($peak_filename, 'r')){
			flock($file, LOCK_SH);
			$fdata=file($peak_filename);
			foreach ($fdata as $key=>$value){
				$this->Peak[$key]=trim($value);
			}
			$this->Peak[0] = floatval($this->Peak[0]);
			fclose($file);
        }
        if ($time_exec>$this->Peak[0]){
			$this->Peak=array(
				$time_exec,
				date('r'),
				$query,
				$_SERVER['SCRIPT_FILENAME'],
			);
		 
			$file=fopen($peak_filename, 'w');
			flock($file, LOCK_EX);
			fwrite($file, implode("\n", $this->Peak));
			fclose($file);
        }
        /* ��������� �������� ����� */

        $nf = mysql_num_fields($SQLResult);
        for ($i=0; $i<$nf; $i++){
			$this->ResultData['fields'][$i]=mysql_fetch_field($SQLResult, $i);
        }

        /* ��������� ������ */
        $nr = mysql_num_rows($SQLResult);
        for ($i=0; $i<$nr; $i++){
			$this->ResultData['data'][$i] = mysql_fetch_row($SQLResult);
        }

        $nf=mysql_query("SELECT FOUND_ROWS();");
        if (mysql_num_rows($nf)>0)
			$this->ResultData['foundrows']=mysql_result($nf,0,0);
        else $this->ResultData['foundrows']=$nr;
   } 


   /*** ���������� ����� � ������� ***/
   public function num_fields(){
      return sizeof($this->ResultData['fields']);
   }

   /*** �������� ��������� ������� ���������� ������� ***/
   public function field_name($num){
      if (isset($this->ResultData['fields'][$num])){
         return $this->ResultData['fields'][$num]->name;
      }else{
         return false;
      }
   }

   /*** ���������� � ������� �� ���������� ������� � ���� ������� ***/
   public function fetch_field($num){
      if (isset($this->ResultData['fields'][$num])){
         return $this->ResultData['fields'][$num];
      }else{
         return false;
      }
   }

   /*** ����� ���������� ���� ***/
   public function field_len($num){
      if (isset($this->ResultData['fields'][$num])){
         return $this->ResultData['fields'][$num]->max_length;
      }else{
         return false;
      }
   }

   /*** ��� ���������� ���� ���������� ������� ***/
   public function field_type($num){
      if (isset($this->ResultData['fields'][$num])){
         return $this->ResultData['fields'][$num]->type;
      }else{
         return false;
      }
   }

   /*** ����� ���������� ���� ���������� ������� ***/
   public function field_flags($num){
      if (!isset($this->ResultData['fields'][$num])){
         return false;
      }
      $result=array();
      if ($this->ResultData['fields'][$num]->not_null){
         $result[]='not_null';
      }
      if ($this->ResultData['fields'][$num]->primary_key){
         $result[]='primary_key';
      }
      if ($this->ResultData['fields'][$num]->unique_key){
         $result[]='unique_key';
      }
      if ($this->ResultData['fields'][$num]->multiple_key){
         $result[]='multiple_key';
      }
      if ($this->ResultData['fields'][$num]->blob){
         $result[]='blob';
      }
      if ($this->ResultData['fields'][$num]->unsigned){
         $result[]='unsigned';
      }
      if ($this->ResultData['fields'][$num]->zerofill){
         $result[]='zerofill';
      }
      if ($this->ResultData['fields'][$num]->binary){
         $result[]='binary';
      }
      if ($this->ResultData['fields'][$num]->enum){
         $result[]='enum';
      }
      if ($this->ResultData['fields'][$num]->auto_increment){
         $result[]='auto_increment';
      }
      if ($this->ResultData['fields'][$num]->timestamp){
         $result[]='timestamp';
      }
      return implode(' ', $result);
   }

   /* ���������� ����� ���������� ������� */
   public function num_rows(){
      return sizeof($this->ResultData['data']);
   }

   /* ������������ ��� ���������� ������� � ���������� ��������������� ������ */
   public function fetch_row(){
      if (($this->NextRowNo+1)>$this->num_rows()){
         return false;
      }
      $this->NextRowNo++;
      return $this->ResultData['data'][$this->NextRowNo-1];
   }

   /* ������������ ��� ���������� ������� � ���������� ������������� ������ */
   public function fetch_assoc(){
      if (($this->NextRowNo+1)>$this->num_rows()){
         return false;
      }
      for ($i=0; $i<$this->num_fields(); $i++){
         $result[$this->ResultData['fields'][$i]->name]=
            $this->ResultData['data'][$this->NextRowNo][$i];
      }
      $this->NextRowNo++;
      return $result;
   }

   public function fetch_array(){
	if (($this->NextRowNo+1)>$this->num_rows()){
         return false;
      } else
	if ($this->num_rows()>0) {
      	  for ($i=0; $i<$this->num_fields(); $i++){

       
	  $result[$this->ResultData['fields'][$i]->name]=
            $this->ResultData['data'][$this->NextRowNo][$i];
         $result[$i]=
            $this->ResultData['data'][$this->NextRowNo][$i];
       } 
      } else return false;
      $this->NextRowNo++;
      return $result;
   }

   public function found_rows(){
        return $this->ResultData['foundrows'];
   }

   public function delete_badfile($dir, $tim) {
	$d=opendir($dir);
	while(($f=readdir($d))!==false) {  
	    if ($f=='.'||$f=='..') continue;
	    if (is_link($dir.'/'.$f)) continue;
	    if (is_dir($dir.'/'.$f)) continue;
	    if ($f=='!peak.txt') continue;
	    if (is_file($dir.'/'.$f) && (filemtime($dir.'/'.$f)+$tim<time())) unlink($dir.'/'.$f);
        }
	closedir($d);
   }


}

// ##############################################################################################################

?>