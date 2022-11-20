<?php

/** -------------------------------------------------------------- //
* Отправка почты с аттачем
* @param string $subject	-	тема_письма
* @param string $to_email	-	мыло@получателя
* @param string $from_email	-	мыло@отправителя
* @param string $msg		-	тело_письма(если нужно)
* @param string $contenttype	Тип письма ('text/plain' - текст, 'text/html' - html)
* @param string $filename	-	имя_файла
* @param string $filepath	-	путь_к_файлу
* @param string $mimetype	-	тип_аттача(например image/jpeg или application/octet-stream)
* @param string $mime_filename - определять тип по имени
* Пример: 
* $mailfile = new plugin_mail("тема_письма","мыло@получателя","мыло@отправителя",
* 			"тело_письма(если нужно)",'', "имя_файла","путь_к_файлу","тип_аттача(например image/jpeg)","");
* $mailfile->sendfile();
**/ 
class plugin_mail
{
  private $subject;
  private $addr_to;
  private $text_body;
  private $text_encoded;
  private $mime_headers;
  private $mime_boundary = "--==================_846811060==_";
  private $smtp_headers;
  private $content_type = 'text/plain';

  public function __construct($subject, $to_email, $from_email, $msg,  $contenttype= '',$filename = '', $mimetype = "application/octet-stream", $mime_filename = false)
  {
    if (!empty($contenttype)){
    	$this->content_type = $contenttype;
   	}
	$this->subject = "=?utf-8?B?" . base64_encode($subject) . "?=";
    $this->addr_to = $to_email;
    $this->smtp_headers = $this->write_smtpheaders($from_email);
    $this->text_body = $this->write_body($msg);
    if (!empty($filename)){
	$filelist = explode(';', $filename);
	foreach($filelist as $file){
		$this->text_encoded .= $this->attach_file($file, $mimetype, $mime_filename);
	}
    }
    	$this->mime_headers = $this->write_mimeheaders($filename, $mime_filename);
  }

// Отправка письма
  public function sendfile()
  {
    $headers = $this->smtp_headers . $this->mime_headers;
    $message = $this->text_body . $this->text_encoded;
    return mail($this->addr_to, $this->subject, $message, $headers);
  }

  private function attach_file($filename, $mimetype, $mime_filename)
  {
    $encoded = $this->encode_file($filename);
    //print_r($encoded);
    
    if ($mime_filename) $filename = $mime_filename;
    $out = "--" . $this->mime_boundary . "\n";
    $out = $out . "Content-type: " . $mimetype . "; name=\"$filename\";\n";
    $out = $out . "Content-Transfer-Encoding: base64\n";
    $out = $out . "Content-disposition: attachment; filename=\"".basename($filename)."\"\n\n";
    $out = $out . $encoded . "\n";
    $out = $out . "--" . $this->mime_boundary . "--" . "\n";
    return $out;
    // added -- to notify email client attachment is done
  }

  private function encode_file($sourcefile)
  {
    if (is_readable($sourcefile))
    {
      $fd = fopen($sourcefile, "r");
      $contents = fread($fd, filesize($sourcefile));
      $encoded = $this->my_chunk_split(base64_encode($contents));
      fclose($fd);
    }
    return $encoded;
  }


  private function write_body($msgtext)
  {
    $out = "--" . $this->mime_boundary . "\n";
    $out = $out . "Content-Type: {$this->content_type}; charset=\"utf-8\"\n\n";
    $out = $out . $msgtext . "\n";
    return $out;
  }

  private function write_mimeheaders($filename, $mime_filename)
  {
    if ($mime_filename) $filename = $mime_filename;
    $out = "MIME-version: 1.0\n";
    $out = $out . "Content-type: multipart/mixed; ";
    $out = $out . "boundary=\"$this->mime_boundary\"\n";
    $out = $out . "Content-transfer-encoding: 7BIT\n";
    $out = $out . "X-attachments: $filename;\n\n";
    return $out;
  }

  private function write_smtpheaders($addr_from)
  {
    $out = "From: $addr_from\n";
    $out = $out . "Reply-To: $addr_from\n";
    $out = $out . "X-Mailer: PHP3\n";
    $out = $out . "X-Sender: $addr_from\n";
    return $out;
  }
  
  private function my_chunk_split($str)
  {
    $stmp = $str;
    $len = strlen($stmp);
    $out = "";
    while ($len > 0) {
      if ($len >= 76) {
        $out = $out . substr($stmp, 0, 76) ."\r\n";
        $stmp = substr($stmp, 76);
        $len = $len - 76;
      } else {
        $out = $out . $stmp . "\r\n";
        $stmp = ""; $len = 0;
      }
    }
    return $out;
  }
}

?>