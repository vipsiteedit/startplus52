`<?php 
    set_time_limit(0);
    import_request_variables("gp", "ext_");
    if(!(isset($ext_s, $ext_d, $ext_p))) {
	header('HTTP/1.0 404');
	exit();
    }
    require_once "function.php";
    $session = htmlspecialchars(addslashes($ext_s));
    $domain = addslashes($ext_d);
    //$domain = $domain[0];
    $act = htmlspecialchars(addslashes($ext_p));
    if (!checkSID($domain, $session)) {
	exit("no SID");
    }
    if (!empty($ext_l)) 
	$lang = "/$ext_l"; 
    else 
	$lang = "";
    $path = ".." . $lang;
    if (!empty($ext_sub)) 
	$ext_sub = "/".$ext_sub;
    else 
	$ext_sub = '';
    if (!empty($subdir)) 
	$subdir = "/".$subdir;
    if ($act<10) {
	if ($act == 2) 
	    $dir=$path.'/images'.$subdir;
	else 
	    if ($act == 3) 
		$dir = $path;
	else 
	    if ($act == 4) 
		$dir = $path.'/files'.$subdir;
	else 
	    if ($act == 5) 
		$dir = $path.'/skin'.$subdir;
	else 
	    $dir = $path.'/arhiv';
    $fl_dir = false;
    if (is_dir($dir) && empty($ext_f)) {
	chdir($dir);
    if ($handle = opendir('.')) {
        while (false !== ($file = readdir($handle))) { 
    	     if ($file == '.' || $file == '..' || strpos($file, ".log") > 1) 
    	        continue;
             $fl_dir = true;
             if ($act == 3) {
                if (is_dir($file)) {
		    if (!(strlen($file)>3 || $file == 'system' || $file == 'catalog' || $file == 'stat' || $file == 'lib' || $file == 'admin' || $file == 'modules' || $file == 'rss'
		    ||$file == 'order' || $file == 'xml' || $file == 'files' || $file == 'skin' || $file == 'images' || $file == 'arhiv'
		    ||$file == 'installation' || $file == 'upload' || $file == 'searchdata' || $file == 'data'))
			    echo $ext_p . "|$file||0|0\r\n";
		 } else 
                    if ($file == 'favicon.ico' || $file == 'robots.txt' || $file == 'sitelang.dat' || $file == 'hostname.dat') {
                	if (file_exists($file.".log")) {
            		    $tmplogo = explode(":",join(file($file.".log"))); 
                    	    if (filesize($file)>0) echo $ext_p."|$file|".@$tmplogo[1]."|".@$tmplogo[2]."|".@$tmplogo[3]."\r\n";
                	} else 
                	    if (filesize($file)>0) echo $ext_p."|$file||".filesize($file)."|0\r\n";
                    }
                continue;
             }
    	    if (is_dir($file)) {
    		if ($act == 4) {
    		    echo $ext_p."|$subdir$file||0|0\r\n";
    		} else continue;
    	    }
    	    if (file_exists($file.".log")) {
    		$tmplogo = explode(":",join(file($file.".log"))); 
            	if (filesize($file)>0) echo $ext_p."|$file|".@$tmplogo[1]."|".@$tmplogo[2]."|".@$tmplogo[3]."\r\n";
            } else if (filesize($file)>0) echo $ext_p."|$file||".filesize($file)."|0\r\n";
	}
	closedir($handle);
    }
    if ($act == 4&& !$fl_dir) {
        rmdir(getcwd());
    }
    } else 
    if (!empty($ext_f)) {
        $file=$dir."/".$ext_f;
	$tmplogo = explode(":",join(file($file.".log"))); 
        if (filesize($file)>0) echo $ext_p."|$file|".@$tmplogo[1]."|".@$tmplogo[2]."|".@$tmplogo[3]."\r\n";
    }
    echo "\r\n";
}

if ($act == 10) {
    if (isset($ext_f)) {
	$filename = "$path/skin/".str_replace(chr(13).chr(10),"",$ext_f);
	if (file_exists($filename))
	echo join("",file($filename));
    }
}

if ($act>10) {
    Header("Content-type: image/jpeg");
    switch ($act) {
	case "11":$dir = "$path/arhiv"; break;
	case "12":$dir = "$path/images".$ext_sub; break;
	case "13":$dir = "$path".$ext_sub; break;
	case "14":$dir = "$path/files".$ext_sub; break;
	case "15":$dir = "$path/skin".$ext_sub; break;
	default : $dir = "$path/arhiv";
    }
    if (isset($ext_f)) {
	$filename=$dir."/".$ext_f;
	if (is_dir($dir) && file_exists($filename)) 
	    echo join("",file($filename));
	else exit();
    }
}
?>