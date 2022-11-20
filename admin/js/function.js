function translite(s){
r = String(s);
r = r.toLowerCase();
r = r.split(' ').join('-');
r = r.split('_').join('-');
r = r.split('--').join('-');
r = r.split('а').join('a');
r = r.split('б').join('b');
r = r.split('в').join('v');
r = r.split('г').join('g');
r = r.split('д').join('d');
r = r.split('е').join('e');
r = r.split('ё').join('yo');
r = r.split('ж').join('zh');
r = r.split('з').join('z');
r = r.split('и').join('i');
r = r.split('й').join('j');
r = r.split('к').join('k');
r = r.split('л').join('l');
r = r.split('м').join('m');
r = r.split('н').join('n');
r = r.split('о').join('o');
r = r.split('п').join('p');
r = r.split('р').join('r');
r = r.split('с').join('s');
r = r.split('т').join('t');
r = r.split('у').join('u');
r = r.split('ф').join('f');
r = r.split('х').join('h');
r = r.split('ц').join('c');
r = r.split('ч').join('ch');
r = r.split('ш').join('sh');
r = r.split('щ').join('sch');
r = r.split('ъ').join('');
r = r.split('ы').join('y');
r = r.split('ь').join('');
r = r.split('э').join('e');
r = r.split('ю').join('yu');
r = r.split('я').join('ya');

var reg = new RegExp('[^a-z\-0-9]' ,'gim');
r = r.replace(reg,'');

return r;
}

function seLoadBox(section, id, name, value) {
  $.ajax({
	url: "/?on_ajax_execute"+section+"",
        context: document.body,
        type: 'POST',
        data: {name: ""+name+"",value: ""+value+""},
        success: function(data){
            //alert(data);
            $(id).html(data);
	}
  });
} 