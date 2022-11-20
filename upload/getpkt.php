<?php
import_request_variables("p", "ext_");

if(!(isset($ext_session, $ext_act))) {
  header('HTTP/1.0 404');
  exit();
}

require_once "function.php";

$session = htmlspecialchars(addslashes($ext_session));

$act = htmlspecialchars(addslashes($ext_act));

if (!checkSID($session)) exit("no");

$path=getcwd()."/data/";

switch ($act)
{
  case "pkt":
    $userfile=$_FILES['userfile']['tmp_name'];
    $userfile_size=$_FILES['userfile']['size'];
    $user=$_FILES['userfile']['name'];

    $id = htmlspecialchars(addslashes($ext_id));
    $block = htmlspecialchars(addslashes($ext_block));
    
    $block = explode(":", $block);
    
    $uploadfile=$path.$id."_".$block[0].".tmp";

    if (move_uploaded_file($userfile, $uploadfile)) echo "ok"; else exit("no");
    break;

  case "make":
    $id = htmlspecialchars(addslashes($ext_id));
    $name = htmlspecialchars(addslashes($ext_name));
    if ($name=='list') $name=$session.".list";
    if ($name=='dellist') $name=$session.".del";
    
    $data = "";
    $i=1;

    while (file_exists($path.$id."_".$i.".tmp")) {
      $data.=join('',file($path.$id."_".$i.".tmp"));
      $i++;
    }

    $fname=$path.$name;
    $f = fopen($fname, "w+b");
    fwrite($f, $data);
    fclose($f);
    chmod($fname, 0644);

    $i=1;
    while (file_exists($path.$id."_".$i.".tmp")) {
      unlink($path.$id."_".$i.".tmp");
      $i++;
    }
    echo "ok";
    break;
}

?>