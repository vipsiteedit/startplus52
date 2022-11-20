<?php

/** -------------------------------------------------------------- //
 * �������� ����� � �������
 * @param string $subject	-	����_������
 * @param string $to_email	-	����@����������
 * @param string $from_email	-	����@�����������
 * @param string $msg		-	����_������(���� �����)
 * @param string $contenttype	��� ������ ('text/plain' - �����, 'text/html' - html)
 * @param string $filename	-	���_�����
 * @param string $filepath	-	����_�_�����
 * @param string $mimetype	-	���_������(�������� image/jpeg ��� application/octet-stream)
 * @param string $mime_filename - ���������� ��� �� �����
 * ������: 
 * 
 * $mailfile = new plugin_mail("����_������",
 * 			"����@����������",
 * 			"����@�����������");
 * $content_id = $mailfile->attach(array( 
 * 						array(
 *							'filename' => "���_�����"
 *							'filepath' => "����_�_�����",
 *							'mime'	   => "���_������(�������� image/jpeg)"
 *						)
 *					);
 * $mailfile->addtext(����_������, mime-type);
 * $mailfile->send();
 **/
class plugin_jmail
{
    private $subject;
    private $addr_to = '';
    private $body = '';
    private $headers;

    private $messages = array();
    private $attaches = array();

    private $boundary;

    public function __construct($subject, $to_email, $from_email)
    {
        $this->boundary = '==================' . strtoupper(uniqid()) . '==';
        //smtp_headers
        $this->subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $this->addr_to = $to_email;

        //headers
        $this->headers = "From: " . $from_email . "\n";
        //$this->headers .= "To: " . $to_email . "\n";
        //$this->headers .= "Subject: " . $subject . "\n";
        $this->headers .= "Reply-To: " . $from_email . "\n";
        $this->headers .= "X-Mailer: PHP5\n";
        $this->headers .= "X-Sender: " . $from_email . "\n";
        $this->headers .= "Mime-Version: 1.0\n";
        $this->headers .= "Content-Type: multipart/mixed; ";
        $this->headers .= "boundary=\"" . $this->boundary . "\"\n\n";

    }
    public function attach($filename = '', $filepath = '', $mime = '', $cid = false)
    {
        if (empty($mime))
            $mime = 'application/octet-stream';
        $content_id = uniqid();
        $this->attaches[] = array('filename' => $filename, 'filepath' => $filepath, 'mime' => $mime, 'cid' => $content_id, 'cid' => $cid);
        return $content_id;
    }
    public function addtext($message, $mime)
    {
        if (empty($mime))
            $mime = 'text/plain';
        $this->messages[] = array('message' => $message, 'mime' => $mime);
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

    private function my_chunk_split($str)
    {
        $stmp = $str;
        $len = strlen($stmp);
        $out = "";
        while ($len > 0)
        {
            if ($len >= 76)
            {
                $out = $out . substr($stmp, 0, 76) . "\r\n";
                $stmp = substr($stmp, 76);
                $len = $len - 76;
            } else
            {
                $out = $out . $stmp . "\r\n";
                $stmp = "";
                $len = 0;
            }
        }
        return $out;
    }

    private function create()
    {
        foreach ($this->messages as $message)
        {
            $this->body .= '--' . $this->boundary . "\n";
            $this->body .= "Content-Type: " . $message['mime'] . "; charset=\"UTF-8\"\n";
            $this->body .= "Content-Transfer-Encoding: 8bit\n";
            $this->body .= "\n" . $message['message'] . "\n\n";
        }
        foreach ($this->attaches as $attach)
        {
    	    if (substr($attach['filename'], 0, 1) == '/'){
    		$ffr = true;
    	    } else {
    		$ffr = false;
    	    }
    	    
            $this->body .= '--' . $this->boundary . "\n";
            if (!$ffr){
        	$this->body .= "Content-Type: " . $attach['mime'] . "; name=\"" . $attach['filename'] . "\"\n";
            } else {
        	$this->body .= "Content-Type: " . $attach['mime'] . "; name=\"" . end(explode('/',$attach['filename'])) . "\"\n";
            }
            if (!$ffr){
        	$this->body .= "Content-disposition: attachment; name=\"" . $attach['filename'] . "\"\n";
    	    } else {
    		$this->body .= "Content-disposition: attachment; name=\"" . end(explode('/',$attach['filename'])) . "\"\n";
    	    }
            if ($attach['cid'])
                $this->body .= "Content-ID: <" . $attach['cid'] . ">\n";
            $this->body .= "Content-Transfer-Encoding: base64\n";
            if (!empty($attach['filepath']) && substr($attach['filepath'], 0, 1) !== '/')
                $attach['filepath'] = '/' . $attach['filepath'];
            if (!empty($attach['filepath']) && substr($attach['filepath'], -1, 1) == '/')
                $attach['filepath'] = substr($attach['filepath'], 0, -1);
            if (!$ffr){
        	$this->body .= "\n" . $this->encode_file($_SERVER['DOCUMENT_ROOT'] . '/files' . $attach['filepath'] . '/' . $attach['filename']) . "\n";
    	    } else {
    	    	$this->body .= "\n" . $this->encode_file($attach['filename']) . "\n";
    	    }
        }
        $this->body .= '--' . $this->boundary . "--";
    }

    public function show_headers()
    {
        echo '<pre>';
        echo $this->headers;
        echo $this->body;
        echo '</pre>';
    }

    // �������� ������
    public function send()
    {
        $this->create();
        //return
        return mail($this->addr_to, $this->subject, $this->body, $this->headers);
    }
}
?>