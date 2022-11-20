<?php
function se_stat_decoderregkey($regkey) {
    $_domain = ""; $_date = ""; $_license = ""; $_error = "";

    $fill_d = array(1 => 5,7,4,3,9,8,5,9,2,3,7,4,7,5,4,2,6,5,7,6,4,5,2,9,4,6,8,6,3,8,9,8,4,3,7,1,3,1,5,6,3,2,3,8,2,1,5,1,9,4,5,7,4,3,9,8,5,9,2,3,7,4,7,5,4,2,6,5,7,6,4,5,2,9,4,6,8,6,3,8,9,8,4,3,7,1,3,1,5,6,3,2,3,8,2,1,5,1,9,4);
    $fill_p = array(1 => 8,9,5,2,9,3,4,8);
    $fill_l = array(1 => 6,5,7,9,2,1,9,8,6,5);
    $replacestrtoint = array("X" => '0', "Z" => '1', "s" => '2', "E" => '3', "t" => '4', "I" => '5', "O" => '6', "A" => '7', "q" => '8', "w" => '9');
    $strznak = array("T"=>".","D"=>"-","I"=>"|");

    $pos_ks = strrpos($regkey, "?");
    $_KS = substr($regkey, $pos_ks+1);
    $KS = ""; for ($i=0; $i < strlen($_KS); $i++) $KS .= @$replacestrtoint[$_KS[$i]];

    $fk = preg_match("/[^a-z,^A-Z,^0-9,^?]+/i", $regkey);

if (($pos_ks === false) || ((strlen($regkey)-1-strlen($_KS)) != $KS) || $fk > 0){
    $_error = 1;
}else{

    // Декодируем домен
    preg_match("/[a-z,A-Z]+/i", substr($regkey, 13), $dmatches);
    $cnd = "";
    for ($i=0; $i < strlen($dmatches[0]); $i++) $cnd .= @$replacestrtoint[$dmatches[0][$i]];

    $l = 13 + strlen($dmatches[0]);
    $_domain = "";
    if (intval($cnd) <= 100)
        for ($i=1; $i <= intval($cnd); $i++) {
            $l += $fill_d[$i]+1;
            $_domain .= strtr(substr($regkey, $l, 1), $strznak);
        }

    // Декодируем дату
    $_date = "";
    $l += 9;
    for ($i=1; $i <= 8; $i++) {
        $l += $fill_p[$i]+1;
        $_date .= @$replacestrtoint[substr($regkey, $l, 1)];
    }

    // Декодируем лицензию
    $l += 7+1;
    preg_match("/[a-z,A-Z]+/i", substr($regkey, $l), $lmatches);

    $cnl = "";
    for ($i=0; $i < strlen($lmatches[0]); $i++) $cnl .= @$replacestrtoint[$lmatches[0][$i]];

    $l += strlen($lmatches[0]);
    $_license = "";
    if (intval($cnl) <= 10)
        for ($i=1; $i <= intval($cnl); $i++) {
            $l += $fill_l[$i]+1;
            $_license .= substr($regkey, $l, 1);
        }

    $f = @strtotime($_date);
    if (($f == -1) || (!in_array($_license, array("demo", "lease", "owned")))){
        $_domain = ""; $_date = ""; $_license = "";
        $_error = 1;
    }

}

return $_domain.";".$_date.";".$_license.";".$_error;
}

?>