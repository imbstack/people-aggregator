function init_layout_editor() {
	document.getElementsByTagName('body')[0].style.backgroundColor = document.getElementById('bodybgcol').value;
	var mods = getElementsByClassName(document, '*', 'module');
	for(var i = 0; i < mods.length; i++)
		mods[i].style.borderColor = document.getElementById('modulecol').value;
	for(var i = 0; i < mods.length; i++) {
		var h1s = mods[i].getElementsByTagName('h1');
		for(var j = 0; j < h1s.length; j++) {
			var stylevalue = "background-color: " + document.getElementById('moduleh1bgcol').value + ";";
			if(document.getElementById("moduleh1col").value.length > 0)
				stylevalue += " color: " + document.getElementById("moduleh1col").value + ";"
			h1s[j].setAttribute("style", stylevalue);
		}
	}
	var mod = document.getElementById('header');
	var h1s = mod.getElementsByTagName('h1');
	for(var j = 0; j < h1s.length; j++) {
		var stylevalue = "color: " + document.getElementById('headerh1col').value + ";"
		h1s[j].setAttribute("style", stylevalue);
	}
	var h2s = mod.getElementsByTagName('h2');
	for(var j = 0; j < h2s.length; j++) {
		var stylevalue = "color: " + document.getElementById('headerh2col').value + ";"
		h2s[j].setAttribute("style", stylevalue);
	}
	var mod = document.getElementById('container');
	var stylevalue = "background-color: " + document.getElementById('containercol').value + ";"
	mod.setAttribute("style", stylevalue);
	var mod = document.getElementById('col_d');
	var stylevalue = "background-color: " + document.getElementById('colbcol').value + ";"
	mod.setAttribute("style", stylevalue);
}

function pickColor(color) {
	var colorObjId = window.ColorPicker_targetInput.id;
	window.ColorPicker_targetInput.value = color;
	switch(colorObjId) {
		case 'bodybgcol':
			document.getElementsByTagName('body')[0].style.backgroundColor = color;
			break;
		case 'modulecol':
			var mods = getElementsByClassName(document, '*', 'module');
			for(var i = 0; i < mods.length; i++)
				mods[i].style.borderColor = color;
			break;
		case 'moduleh1bgcol':
			var mods = getElementsByClassName(document, '*', 'module');
			for(var i = 0; i < mods.length; i++) {
				var h1s = mods[i].getElementsByTagName('h1');
				for(var j = 0; j < h1s.length; j++) {
					var stylevalue = "background-color: " + color + ";";
					if(document.getElementById("moduleh1col").value.length > 0)
						stylevalue += " color: " + document.getElementById("moduleh1col").value + ";"
					if(document.getElementById("moduleh1font").selectedIndex > 0)
						stylevalue += " font-family: " + document.getElementById("moduleh1font").options[document.getElementById("moduleh1font").selectedIndex].value + ";"
					h1s[j].setAttribute("style", stylevalue);
				}
			}
			break;
		case 'moduleh1col':
			var mods = getElementsByClassName(document, '*', 'module');
			for(var i = 0; i < mods.length; i++) {
				var h1s = mods[i].getElementsByTagName('h1');
				for(var j = 0; j < h1s.length; j++) {
					var stylevalue = "color: " + color + ";"
					if(document.getElementById("moduleh1bgcol").value.length > 0)
						stylevalue += " background-color: " + document.getElementById("moduleh1bgcol").value + ";"
					if(document.getElementById("moduleh1font").selectedIndex > 0)
						stylevalue += " font-family: " + document.getElementById("moduleh1font").options[document.getElementById("moduleh1font").selectedIndex].value + ";"
					h1s[j].setAttribute("style", stylevalue);
				}
			}
			break;
		case 'headerh1col':
			var mod = document.getElementById('header');
			var h1s = mod.getElementsByTagName('h1');
			for(var j = 0; j < h1s.length; j++) {
				var stylevalue = "color: " + color + ";"
				if(document.getElementById("headerh1font").selectedIndex > 0)
					stylevalue += " font-family: " + document.getElementById("headerh1font").options[document.getElementById("headerh1font").selectedIndex].value + ";"
				if(document.getElementById("headerh1vis").selectedIndex > 0)
					stylevalue += " visibility: " + document.getElementById("headerh1vis").options[document.getElementById("headerh1vis").selectedIndex].value + ";"
				h1s[j].setAttribute("style", stylevalue);
			}
			break;
		case 'headerh2col':
			var mod = document.getElementById('header');
			var h2s = mod.getElementsByTagName('h2');
			for(var j = 0; j < h2s.length; j++) {
				var stylevalue = "color: " + color + ";"
				if(document.getElementById("headerh2font").selectedIndex > 0)
					stylevalue += " font-family: " + document.getElementById("headerh2font").options[document.getElementById("headerh2font").selectedIndex].value + ";"
				if(document.getElementById("headerh2vis").selectedIndex > 0)
					stylevalue += " visibility: " + document.getElementById("headerh2vis").options[document.getElementById("headerh2vis").selectedIndex].value + ";"
				h2s[j].setAttribute("style", stylevalue);
			}
			break;
		case 'containercol':
			var mod = document.getElementById('container');
			var stylevalue = "background-color: " + color + ";"
			mod.setAttribute("style", stylevalue);
			break;
		case 'colbcol':
			var mod = document.getElementById('col_b');
			var stylevalue = "background-color: " + color + ";"
			mod.setAttribute("style", stylevalue);
			break;
	}
}

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

function chBodyFont() {
	document.getElementsByTagName('body')[0].style.fontFamily = document.getElementById("bodyfont").options[document.getElementById("bodyfont").selectedIndex].value;
}

function chBodyBgRepeat() {
	document.getElementsByTagName('body')[0].style.backgroundRepeat = document.getElementById("bodybgepeat").options[document.getElementById("bodybgepeat").selectedIndex].value;
}

function chBodyBgRepeat() {
	document.getElementsByTagName('body')[0].style.backgroundImage = document.getElementById("bodybgimage").value;
}

function chModuleH1Font() {
	var mods = getElementsByClassName(document, '*', 'module');
	for(var i = 0; i < mods.length; i++) {
		var h1s = mods[i].getElementsByTagName('h1');
		for(var j = 0; j < h1s.length; j++) {
			var stylevalue = "font-family: " + document.getElementById("moduleh1font").options[document.getElementById("moduleh1font").selectedIndex].value + ";"
			if(document.getElementById("moduleh1bgcol").value.length > 0)
				stylevalue += " background-color: " + document.getElementById("moduleh1bgcol").value + ";"
			if(document.getElementById("moduleh1col").value.length > 0)
				stylevalue += " color: " + document.getElementById("moduleh1col").value + ";"
			h1s[j].setAttribute("style", stylevalue);
		}
	}
}

function chHeaderH1Font() {
	var mod = document.getElementById('header');
	var h1s = mod.getElementsByTagName('h1');
	for(var j = 0; j < h1s.length; j++) {
		var stylevalue = "font-family: " + document.getElementById("headerh1font").options[document.getElementById("headerh1font").selectedIndex].value + ";"
		if(document.getElementById("headerh1col").value.length > 0)
			stylevalue += " color: " + document.getElementById("headerh1col").value + ";"
		if(document.getElementById("headerh1vis").selectedIndex > 0)
			stylevalue += " visibility: " + document.getElementById("headerh1vis").options[document.getElementById("headerh1vis").selectedIndex].value + ";"
		h1s[j].setAttribute("style", stylevalue);
	}
}

function chHeaderH1Visibility() {
	var mod = document.getElementById('header');
	var h1s = mod.getElementsByTagName('h1');
	for(var j = 0; j < h1s.length; j++) {
		var stylevalue = "visibility: " + document.getElementById("headerh1vis").options[document.getElementById("headerh1vis").selectedIndex].value + ";"
		if(document.getElementById("headerh1col").value.length > 0)
			stylevalue += " color: " + document.getElementById("headerh1col").value + ";"
		if(document.getElementById("headerh1font").selectedIndex > 0)
			stylevalue += " font-family: " + document.getElementById("headerh1font").options[document.getElementById("headerh1font").selectedIndex].value + ";"
		h1s[j].setAttribute("style", stylevalue);
	}
}

function chHeaderH2Font() {
	var mod = document.getElementById('header');
	var h2s = mod.getElementsByTagName('h2');
	for(var j = 0; j < h2s.length; j++) {
		var stylevalue = "font-family: " + document.getElementById("headerh2font").options[document.getElementById("headerh2font").selectedIndex].value + ";"
		if(document.getElementById("headerh2col").value.length > 0)
			stylevalue += " color: " + document.getElementById("headerh2col").value + ";"
		if(document.getElementById("headerh2vis").selectedIndex > 0)
			stylevalue += " visibility: " + document.getElementById("headerh2vis").options[document.getElementById("headerh2vis").selectedIndex].value + ";"
		h2s[j].setAttribute("style", stylevalue);
	}
}

function chHeaderH2Visibility() {
	var mod = document.getElementById('header');
	var h2s = mod.getElementsByTagName('h2');
	for(var j = 0; j < h2s.length; j++) {
		var stylevalue = "visibility: " + document.getElementById("headerh2vis").options[document.getElementById("headerh2vis").selectedIndex].value + ";"
		if(document.getElementById("headerh2col").value.length > 0)
			stylevalue += " color: " + document.getElementById("headerh2col").value + ";"
		if(document.getElementById("headerh2font").selectedIndex > 0)
			stylevalue += " font-family: " + document.getElementById("headerh2font").options[document.getElementById("headerh2font").selectedIndex].value + ";"
		h2s[j].setAttribute("style", stylevalue);
	}
}

function chBodyShad() {
	document.getElementById('body_shadow').style.visibility = document.getElementById("bodyshad").options[document.getElementById("bodyshad").selectedIndex].value;
}

function getCSS() {
	var ncss = '#container {\n'
	+ '\tbackground-color: ' + document.getElementById("containercol").value + ';\n'
	+ '}\n'
	+ '#col_b {\n'
	+ '\tbackground-color: ' + document.getElementById("colbcol").value + ';\n'
	+ '}\n'
	+ 'body {\n'
	+ '\tbackground-color: ' + document.getElementById("bodybgcol").value + ';\n'
	+ '\tfont-family: ' + document.getElementById("bodyfont").options[document.getElementById("bodyfont").selectedIndex].value + ';\n'
	+ '\tbackground-image: none;\n'
	+ '\tbackground-repeat: ' + document.getElementById("bodybgrepeat").options[document.getElementById("bodybgrepeat").selectedIndex].value + ';\n'
	+ '}\n'
	+ '.module {\n'
	+ '\tborder-color: ' + document.getElementById("modulecol").value + ';\n'
	+ '}\n'
	+ '.module h1{\n'
	+ '\tbackground-color: ' + document.getElementById("moduleh1bgcol").value + ';\n'
	+ '\tcolor: ' + document.getElementById("moduleh1col").value + ';\n'
	+ '}\n'
	+ '#header h1 {\n'
	+ '\tfont-family: ' + document.getElementById("headerh1font").options[document.getElementById("headerh1font").selectedIndex].value + ';\n'
	+ '\tcolor: ' + document.getElementById("headerh1col").value + ';\n'
	+ '\tvisibility: ' + document.getElementById("headerh1vis").options[document.getElementById("headerh1vis").selectedIndex].value + ';\n'
	+ '}\n'
	+ '\n'
	+ '#header h2 {\n'
	+ '\tfont-family: ' + document.getElementById("headerh2font").options[document.getElementById("headerh2font").selectedIndex].value + ';\n'
	+ '\tcolor: ' + document.getElementById("headerh2col").value + ';\n'
	+ '\tvisibility: ' + document.getElementById("headerh2vis").options[document.getElementById("headerh2vis").selectedIndex].value + ';\n'
	+ '}\n'
	+ '#body_shadow {\n'
	+ '\tvisibility: ' + document.getElementById("bodyshad").options[document.getElementById("bodyshad").selectedIndex].value + ';\n'
	+ '}';
	document.getElementById("newcss").innerHTML = ncss;
//	alert(ncss);
}