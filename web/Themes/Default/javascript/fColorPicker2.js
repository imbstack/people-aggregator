var perline = 9;
var curId;
var colorLevels = Array('0', '3', '6', '9', 'C', 'F');
var colorArray = Array();
var ie = false;
var nocolor = 'none';

if (document.all) { ie = true; nocolor = ''; }

function addColor(r, g, b) {
	var red = colorLevels[r];
	var green = colorLevels[g];
	var blue = colorLevels[b];
	addColorValue(red, green, blue);
}

function addColorValue(r, g, b) {
	colorArray[colorArray.length] = '#' + r + r + g + g + b + b;
}

function setFColor(color) {
	document.getElementById(curId).value = color;
	document.getElementById('colorpicker').style.display = 'none';
	document.getElementById('colorpickerbg').style.display = 'none';
	setColor(curId, color);
}
	
function initFC() {
	var elemDiv = document.getElementById('colorpicker');
	if(! elemDiv) {return false;}
	genColors();
	elemDiv.innerHTML = '<span class="pickacolor">Pick a color: ' 
		+ '<br />' 
		+ getColorTable() 
		+ '</span>';
	//document.getElementById('colorpickerbg').innerHTML = elemDiv.innerHTML;
}

function hidePicker() {
	if(document.getElementById('colorpicker').style.display == 'block') {
		document.getElementById('colorpicker').style.display = 'none';
		document.getElementById('colorpickerbg').style.display = 'none';
	}
}

function pickFColor(id) {
	if (id == curId && document.getElementById('colorpicker').style.display == 'block') {
		document.getElementById('colorpicker').style.display = 'none';
		document.getElementById('colorpickerbg').style.display = 'none';
		return;
	}
	curId = id;
	var thelink = document.getElementById(id);
	document.getElementById('colorpicker').style.top = (getAbsoluteOffsetTop(thelink) + 20) + 'px';
	document.getElementById('colorpicker').style.left = getAbsoluteOffsetLeft(thelink) + 'px';
	document.getElementById('colorpicker').style.display = 'block';
	document.getElementById('colorpickerbg').style.top = (getAbsoluteOffsetTop(thelink) + 20) + 'px';
	document.getElementById('colorpickerbg').style.left = getAbsoluteOffsetLeft(thelink) + 'px';
	document.getElementById('colorpickerbg').style.display = 'block';
}

function genColors() {
	addColorValue('0','0','0');
	addColorValue('3','3','3');
	addColorValue('6','6','6');
	addColorValue('8','8','8');
	addColorValue('9','9','9'); 
	addColorValue('A','A','A');
	addColorValue('C','C','C');
	addColorValue('E','E','E');
	addColorValue('F','F','F');  
			
	for (a = 1; a < colorLevels.length; a++)
			addColor(0,0,a);
	for (a = 1; a < colorLevels.length - 1; a++)
			addColor(a,a,5);

	for (a = 1; a < colorLevels.length; a++)
			addColor(0,a,0);
	for (a = 1; a < colorLevels.length - 1; a++)
			addColor(a,5,a);
			
	for (a = 1; a < colorLevels.length; a++)
			addColor(a,0,0);
	for (a = 1; a < colorLevels.length - 1; a++)
			addColor(5,a,a);
			
	for (a = 1; a < colorLevels.length; a++)
			addColor(a,a,0);
	for (a = 1; a < colorLevels.length - 1; a++)
			addColor(5,5,a);
			
	for (a = 1; a < colorLevels.length; a++)
			addColor(0,a,a);
	for (a = 1; a < colorLevels.length - 1; a++)
			addColor(a,5,5);

	for (a = 1; a < colorLevels.length; a++)
			addColor(a,0,a);			
	for (a = 1; a < colorLevels.length - 1; a++)
			addColor(5,a,5);
			
  	return colorArray;
}

function getColorTable() {
	var colors = colorArray;
 	var tableCode = '<ul class="ulstyle">';
		for (i = 0; i < colors.length; i++) {
			tableCode += '<li class="ilstyle"><div style="color: ' + colors[i] + '; background-color: ' + colors[i] + ';" class="boxstyle" onclick="setFColor(\'' + colors[i] + '\'); return false;">'
				+ '<a href="#" onclick="setFColor(\'' + colors[i] + '\'); return false;">&nbsp;&nbsp;&nbsp;</a></div></li>';
	}
	tableCode += '</ul>';
 	return tableCode;
}

function getAbsoluteOffsetTop(obj) {
	var top = obj.offsetTop;
	var parent = obj.offsetParent;
	while (parent != document.body) {
		top += parent.offsetTop;
		parent = parent.offsetParent;
	}
	return top;
}

function getAbsoluteOffsetLeft(obj) {
	var left = obj.offsetLeft;
	var parent = obj.offsetParent;
	while (parent != document.body) {
		left += parent.offsetLeft;
		parent = parent.offsetParent;
	}
	return left;
}


