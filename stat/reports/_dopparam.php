<SCRIPT Language="JavaScript" type="text/javascript">
<!--
function eSelect(t,e) {
	var el=document.getElementById(e);
	if (el) {
		if (t==0) el.disabled=true; else el.disabled=false;
	}
}

function EnaDis(elid,is) {
	var s=document.getElementById(elid);
	if (s) s.disabled=is;
}

function ptable_ex() {
	var t=document.getElementById("ptable");
	var i=document.getElementById("pimg");
	if (t.className=="vis1") {
		t.className="vis2";
		document.cookie="cnstats_report_pathes=hidden";
		i.src="img/arr_bottom.gif";
	}else {
		t.className="vis1";
		document.cookie="cnstats_report_pathes=visible";
		i.src="img/arr_top.gif";
	}
}

//-->
</SCRIPT>
<?php
$TOOLS = "
<table width='100%' border='0' class='tbl_tools'><tr><td>
<table width='100%' border='0'>
<tr class='tbltitle'>
    <td align='center'><a href='JavaScript:ptable_ex();'><img id='pimg' src='img/arr_top.gif' width=17 height=17 border=0></a></td>
    <td width='95%'>Дополнительные параметры</td></tr>
</table>
<table width='100%' id='ptable' class='vis1' border='0'>
<form action='index.php' method='get'>
    <input type='hidden' name='st' value='".$st."'>
    <input type='hidden' name='sdt' value='".$sdt."'>
    <input type='hidden' name='fdt' value='".$fdt."'>
    <input type='hidden' name='filter' value='".$filter."'>
    <tr class='trodd'>
        <td width='30%'>Ссылающаяся страница</td>
        <td width='20%'>
            <SELECT width='100%' OnChange=\"javascript:eSelect(this.value,'inp_referer')\" name='sel_referer' id='sel_referer'>
                <OPTION value='0' >не важно
                <OPTION value='1' >содержит
                <OPTION value='2' >не содержит
            </SELECT>
        </td>
        <td width='50%'><input disabled type='text' style='width:100%' id='inp_referer' name='inp_referer' value=''></td>
    </tr>
    <tr class='treven'>
        <td nowrap>Входная страница</td>
        <td>
            <SELECT width='100%' OnChange=\"javascript:eSelect(this.value,'inp_firstpage')\" name='sel_firstpage' id='sel_firstpage'>
                <OPTION value='0' >не важно
                <OPTION value='1' >содержит
                <OPTION value='2' >не содержит
            </SELECT>
        </td>
        <td><input disabled type='text' style='width:100%' id='inp_firstpage' name='inp_firstpage' value=''></td>
    </tr>
    <tr class='trodd'>
        <td>Одна из страниц</td>
        <td>
            <SELECT width='100%' OnChange=\"javascript:eSelect(this.value,'inp_anypage')\" name='sel_anypage' id='sel_anypage'>
                <OPTION value='0' >не важно
                <OPTION value='1' >содержит
                <OPTION value='2' >не содержит
            </SELECT>
        </td>
        <td><input disabled type='text' style='width:100%' id='inp_anypage' name='inp_anypage' value=''></td>
    </tr>
    <tr class='treven'>
        <td colspan=3 align='left'>
            <input type='checkbox' name='usereferer' id='usereferer'>&nbsp;
            <label for='usereferer'>Отображать ссылающиеся страницы</label>
        </td>
    </tr>
    <tr class='trodd'>
        <td colspan=3 align='center'>
            <input type='submit' value='Показать'>
        </td>
    </tr>
</form>
</table>
</td></tr></table>";

print $TOOLS;
?>