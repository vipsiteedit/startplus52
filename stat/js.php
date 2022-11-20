/* Календарь */
function CalendarSDT(y,m,d,input,div) {
	var ed = document.getElementById(input);
	var e = document.getElementById(div);

	if (!ed || !e) return;

	if (m<10) m = '0'+(m+'-'); else m+='-';
	if (d<10) d = '0'+(d+' '); else d+=' ';

	if (input == 'date2') hours = '23:59:59';
    else hours = '00:00:00';
	ed.value = y+'-'+m+d+hours;
	e.style.visibility = 'hidden';
}

function ShowCalendarE(div, input, nohide) {
    var el = document.getElementById(input);
    y = parseInt(el.value.substring(0,4));
    m = el.value.substring(5,7);
    if (m=="01") m=1;
    if (m=="02") m=2;
    if (m=="03") m=3;
    if (m=="04") m=4;
    if (m=="05") m=5;
    if (m=="06") m=6;
    if (m=="07") m=7;
    if (m=="08") m=8;
    if (m=="09") m=9;
    ShowCalendar(y, m, div, input, nohide);
}

function IsLeapYear(year) {
	if ((year % 400 == 0)||(year % 100 != 0)&&(year % 4 == 0)) return true;
    else return false;
}

function ShowCalendar(y, m, div, input, nohide) {
    var mdays_noleap = Array(31,28,31,30,31,30,31,31,30,31,30,31);
    var mdays_leap = Array(31,29,31,30,31,30,31,31,30,31,30,31);
    var month = Array('янв','фев','мар','апр','май','июн','июл','авг','сен','окт','ноя','дек');
    var smonth = Array('Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь');

	var e = document.getElementById(div);
	if (!e) return;

	var el = document.getElementById(input);
	ad = parseInt(el.value.substring(8,10));
    am = parseInt(el.value.substring(5,7));
    ay = parseInt(el.value.substring(0,4));

	if (nohide==0)
        if (e.style.visibility == 'visible') { e.style.visibility = 'hidden'; return; }

	if (e.style.width != '150px') {
		e.style.left = e.offsetLeft-35;
		e.style.width = '150px';
	}

    e.style.visibility = 'visible';

	if (IsLeapYear(y)) mdays = mdays_leap;
    else mdays = mdays_noleap;

	var d = '';
	var fday = new Date(y, m-1, 1);
	var dow = fday.getDay();
	var theDate = new Date();

	if (dow == 0) dow = 7;

	m = parseInt(m);
	if (m == 12) { mn = 1; yn = y+1; }
    else { mn = m+1; yn = y; }
	if (m == 1) { mp = 12; yp = y-1; }
    else { mp = m-1; yp = y; }

    d += '<table class="caltblmain" width="150px" border="0" cellpadding="1" cellspacing="0"><tr><td>';
	d += '<table class="caltbl" width=100% cellpadding=1 cellspacing=1 border=0><tr class="caltdtitle">';
    d += '<td width=25% class="calamonth"><a href="javascript:ShowCalendar('+yp+','+mp+',\''+div+'\',\''+input+'\',1);">«&nbsp;'+month[mp-1]+'</a></td>';
	d += '<td width=50% valign=top align=center class="caltekmonth">'+y+'<br>'+smonth[m-1]+'</td>';
    var DateM = new Date(y, mn-1, 1)
    if (DateM.getTime() < theDate.getTime())
        d += '<td width=25% class="calamonth"><a href="javascript:ShowCalendar('+yn+','+mn+',\''+div+'\',\''+input+'\',1);">'+month[mn-1]+'&nbsp;»</a></td>';
    else
        d += '<td width=25% class="calmonth">'+month[mn-1]+'</td>';
    d += '</tr></table>';

	d += '<table class="caltbl" width="100%" cellpadding="1" cellspacing="1" border="0"><tr>';
      d += '<td align="center" class="caltdweekday">пн</td>';
      d += '<td align="center" class="caltdweekday">вт</td>';
      d += '<td align="center" class="caltdweekday">ср</td>';
      d += '<td align="center" class="caltdweekday">чт</td>';
      d += '<td align="center" class="caltdweekday">пт</td>';
      d += '<td align="center" class="caltdweekday">сб</td>';
      d += '<td align="center" class="caltdweekday">вс</td>';
    d += '</tr><tr class="">';

	if (dow != 1) d += '<td colspan="'+(dow-1)+'" class="caltdday">&nbsp;</td>'
	var i=1;
	do {
		if ((i == ad)&&(m == am)&&(y == ay)) {
            styletd='class="caltddayactive" '; styletxt = 'class="caldayactive"';
        }else {
            styletd = 'class="caltdday" '; styletxt = ' ';
        }
    	var iDate = new Date(y, m-1, i);
        if (iDate.getTime() <= theDate.getTime())
            d += '<td align="right" '+styletd+' style="cursor:hand;" onClick="CalendarSDT('+y+','+m+','+i+',\''+input+'\',\''+div+'\');"><a href="javascript:CalendarSDT('+y+','+m+','+i+',\''+input+'\',\''+div+'\');"><font '+styletxt+'>'+i+'</font></a></td>';
        else
            d += '<td align="right" '+styletd+'><font class="caldaypas">'+i+'</font></td>';

        i++;
		dow++;
		if (dow > 7) { d += '</tr>\n<tr class="">'; dow=1; }
		} while (i <= mdays[m-1]);

	if (dow > 1) d += '<td colspan="'+(8-dow)+'" class="caltdday">&nbsp;</td>';

	d += '</tr></table>';
	d += '</td></tr></table>';

	if (e) e.innerHTML = d;
}
/* END Календарь */

/* Расширенные данные элементов */
var processed = false;
    openurl_busy = false;

function getXMLObject() {
    var A = null;
    try {
        A = new ActiveXObject('Msxml2.XMLHTTP')
    }
    catch(e) {
        try {
            A = new ActiveXObject('Microsoft.XMLHTTP')
        }
        catch(oc) {
            A = null
        }
    }
    if (!A && typeof XMLHttpRequest != 'undefined') {
        A = new XMLHttpRequest()
    }
    return A
}

function openurl(url,o) {
    openurl_busy = true;
    global_response = '';
    k = getXMLObject();
    if(k) {
        var rndnum = Math.round(Math.random() * 999111);
        k.open('GET', url+'&rnd='+rndnum, true);
        k.onreadystatechange = function() {
            if(k.readyState == 4&&k.responseText) {
                if (o) {
			        o.innerHTML = k.responseText;
			        openurl_busy = false;
			    }
		        else global_response = k.responseText
                processed = false;
            }
        }
        k.send(null);
    }
}

function expand(url,num) {
    if (processed) return;

    k = getXMLObject();
    if (!k) return;

    var o = document.getElementById('e'+num);
    o.innerHTML = '';

    if (o.className == 'block_u') {
        processed = true;
        o.className = 'block_v';
        o.innerHTML = '<center style="color:gray;">Идет загрузка. Пожалуйста, подождите.</center><br>';
        openurl(url,o);
    }
    else o.className = 'block_u';
}
/* END Расширенные данные элементов */


/* Отображение "Дополнительных параметров" */
function eSelect(t,e) {
	var el = document.getElementById(e);
	if (el) {
		if (t == 0) el.disabled = true;
        else el.disabled = false;
	}
}

function EnaDis(elid,is) {
	var s = document.getElementById(elid);
	if (s) s.disabled = is;
}

function ptable_ex() {
	var t = document.getElementById("ptable");
	var i = document.getElementById("pimg");
	if (t.className == "vis1") {
		t.className = "vis2";
		document.cookie = "cnstats_report_pathes=hidden";
		i.src = "img/arr_bottom.gif";
	}else {
		t.className = "vis1";
		document.cookie = "cnstats_report_pathes=visible";
		i.src = "img/arr_top.gif";
	}
}
/* END Отображение "Дополнительных параметров" */
