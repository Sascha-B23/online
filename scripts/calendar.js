<!--
function twoframes (url1, target1, url2, target2) {
    parent.frames[target1].location.href=url1;
    parent.frames[target2].location.href=url2;
}

function groupadd(url) {
  var spers = '', a, i;
  url += '&view=' + document.viewer.view.value;
  if (document.viewer.view.value == 1) {
    url += '&gru=' + document.viewer.gru[document.viewer.gru.selectedIndex].value;
    for(a = 0, i = 0; a < document.viewer.pers_length.value; a++) {
      if (document.viewer.pers[a].selected) {
        spers += 'i:'+i+';s:'+document.viewer.pers[a].value.length+':"'+document.viewer.pers[a].value+'";';
        i++;
      }
    }
    if (i == 0) { spers = 'N' } else { spers = 'a:'+i+':{'+spers+'}'; }
    url += '&spers=' + spers;
    url += '&axis=' + document.viewer.axis[document.viewer.axis.selectedIndex].value;
    url += '&dist=' + document.viewer.dist[document.viewer.dist.selectedIndex].value;
  }
  url += '&no_r=' + document.viewer.no_r.value;
  return url;
}

function dayweek(mode, year, month, day, sessid) {
  var url = 'm1.php?mode='+mode+'&year='+year+'&month='+month+'&day='+day+'&PHPSESSID='+sessid;
  url = groupadd(url);
  if (mode == 1) {
    if (day >= 10)
      document.create.day.value = day;
    else
      document.create.day.value = '0'+day;
  }
  parent.open(url, 'm');
}

function pagemonth(mode, sessid) {
  var m, y, url = '?mode=4&PHPSESSID='+sessid;
  y = document.viewer.year[document.viewer.year.selectedIndex].value;
  m = document.viewer.month[document.viewer.month.selectedIndex].value;
  if (mode == -1) { if(m== 1) {m=12; y--;} else {m--;} }
  if (mode ==  1) { if(m==12) {m= 1; y++;} else {m++;} }
  url += '&year=' + y + '&month=' + m;
  url += '&day=' + document.viewer.day.value;
  url += '&no_r=' + document.viewer.no_r.value;
  url = groupadd(url);
  parent.open('m1.php'+url, 'm');
  parent.open('l.php'+url, 'l');
}

function callm2(){
  frm = document.forms.create;
  url="m2.php?";
  for (i=1; i<=9; i++){
    //alert(frm.elements[i].name);
    url += frm.elements[i].name + "=" + frm.elements[i].value.replace(/&/,"%26") + "&";
  }
  url += frm.elements[i].name + "=" + frm.elements[i].value;
  url = url.replace(/\s/g, "+");
  if(document.viewer.view.value == 1) {
     url = groupadd(url);
  }
  //url+=url+"&year="+year+"&"+SID;
  //alert(url);
  parent.open(url, 'm');
}

function time(wert) {
  var x;
  x = parent.l.document.create.var1.value;
  if(x == 1) {
    parent.l.document.create.anfang.value = wert;
    parent.l.document.create.var1.value = '2';
  }
  else {
    parent.l.document.create.ende.value = wert;
    parent.l.document.create.var1.value = '1';
  }
}

//-->