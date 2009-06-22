function getElementsByClassName(oElm, strTagName, strClassName) {
    var arrElements = (strTagName == "*" && document.all)? document.all : oElm.getElementsByTagName(strTagName);
    var arrReturnElements = new Array();
    strClassName = strClassName.replace(/\-/g, "\\-");
    var oRegExp = new RegExp("(^|\\s)" + strClassName + "(\\s|$)");
    var oElement;
    for(var i=0; i<arrElements.length; i++){
        oElement = arrElements[i];      
        if(oRegExp.test(oElement.className)){
            arrReturnElements.push(oElement);
        }   
    }
    return (arrReturnElements);
}

function startPrivacy() {
  var pdivs = getElementsByClassName(document, '*', 'privacy_selected');
  for(var i = 0; i < pdivs.length; i++) {
    var inputid = pdivs[i].id.substr(4, pdivs[i].id.length - 4);
    pdivs[i].innerHTML = '<input id="' + inputid + '" type="hidden" name="' + inputid + '" value="0" />'
      + '<a href="#" onclick="switchPrivacy(\'' + pdivs[i].id + '\', 0); return false;"><img src="img/red_on.gif" alt="" height="14" width="14" border="0"></a>'
      + '<a href="#" onclick="switchPrivacy(\'' + pdivs[i].id + '\', 1); return false;"><img src="img/yellow_off.gif" alt="" height="14" width="14" border="0"></a>'
      + '<a href="#" onclick="switchPrivacy(\'' + pdivs[i].id + '\', 2); return false;"><img src="img/green_off.gif" alt="" height="14" width="14" border="0"></a>';
  }
}

function switchPrivacy(divid, arg) {
  var pdiv = document.getElementById(divid);
  var inputid = divid.substr(4, divid.length - 4);
  switch(arg) {
    case 0:
      pdiv.innerHTML = '<input id="' + inputid + '" type="hidden" name="' + inputid + '" value="0" />'
        + '<a href="#" onclick="switchPrivacy(\'' + divid + '\', 0); return false;"><img src="img/red_on.gif" alt="" height="14" width="14" border="0"></a>'
        + '<a href="#" onclick="switchPrivacy(\'' + divid + '\', 1); return false;"><img src="img/yellow_off.gif" alt="" height="14" width="14" border="0"></a>'
        + '<a href="#" onclick="switchPrivacy(\'' + divid + '\', 2); return false;"><img src="img/green_off.gif" alt="" height="14" width="14" border="0"></a>';
      break;
    case 1:
      pdiv.innerHTML = '<input id="' + inputid + '" type="hidden" name="' + inputid + '" value="1" />'
        + '<a href="#" onclick="switchPrivacy(\'' + divid + '\', 0); return false;"><img src="img/red_off.gif" alt="" height="14" width="14" border="0"></a>'
        + '<a href="#" onclick="switchPrivacy(\'' + divid + '\', 1); return false;"><img src="img/yellow_on.gif" alt="" height="14" width="14" border="0"></a>'
        + '<a href="#" onclick="switchPrivacy(\'' + divid + '\', 2); return false;"><img src="img/green_off.gif" alt="" height="14" width="14" border="0"></a>';
      break;
    case 2:
      pdiv.innerHTML = '<input id="' + inputid + '" type="hidden" name="' + inputid + '" value="2" />'
        + '<a href="#" onclick="switchPrivacy(\'' + divid + '\', 0); return false;"><img src="img/red_off.gif" alt="" height="14" width="14" border="0"></a>'
        + '<a href="#" onclick="switchPrivacy(\'' + divid + '\', 1); return false;"><img src="img/yellow_off.gif" alt="" height="14" width="14" border="0"></a>'
        + '<a href="#" onclick="switchPrivacy(\'' + divid + '\', 2); return false;"><img src="img/green_on.gif" alt="" height="14" width="14" border="0"></a>';
      break;
  }
}

function spModule(arg) {
  for(var i = 0; ;i++) {
    if(document.getElementById("epm" + i) != null) {
      document.getElementById("epm" + i).className = (i == arg) ? 'active' : '';
      document.getElementById("div_epm" + i).style.display = (i == arg) ? 'block' : 'none';
    }
    else
      break;
  }
}