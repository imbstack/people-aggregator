var json_data = null;
var json_defaults = null;
var count = 0;
var canClose = true;
var currSelId = null;
var currPropId = null;
var changed = false;
var uid = '1';
var gid = '5';
var settings_type = '';
var user_js_data = null;
var imagesdir_abspath = base_url;
var imagesdir = '/Themes/Default/images/';

var formAction = '';

// Now we save the user json in the user profile such that we can retrive the user's data

var json_file = '/configurator.json';

$(document).ready(function() {
	$.get(json_file, function(json) {
    if (trim(user_js_data) == '') {
      user_js_data = json;
    }
    json_data = user_js_data;
		json_defaults = user_js_data;
		readCSS();
    drawControls();
		//applyCSS();
    setJson();
		$('#colorpicker').farbtastic();
	});
  
	$('.wide_content').click(function() {
		if(canClose)
			hideColorPicker();
		else
			canClose = true;
	});
});


function applyCSS() {
	eval('var cf = ' + json_data);
	for(var k = 0; k < cf.groups.length; k++) {
		for(var i = 0; i < cf.groups[k].selectors.length; i++) {
			var myStyle = '';
			for(var j = 0; j < cf.groups[k].selectors[i].selector_properties.length; j++) {
				myStyle += trProperty(cf.groups[k].selectors[i].selector_properties[j].property_id) + ': ';
        prop_val = cf.groups[k].selectors[i].selector_properties[j].property_value;
        if(prop_val.indexOf('!important') == -1) {
           prop_val = prop_val + ' !important';
        }
				myStyle += '\"' + prop_val + '\", ';
			}
			if(myStyle.length > 0) myStyle = myStyle.substring(0, myStyle.length - 2);
			eval('$(cf.groups[k].selectors[i].selector_id).css({ ' + myStyle + ' })');
		}
	}
}


function trProperty(arg) {
	var temp = arg;
	var output = '';
	while(temp.indexOf('-') > -1) {
		output += temp.substring(0, temp.indexOf('-'))
		temp = temp.substring(temp.indexOf('-') + 1);
		temp = temp.substring(0, 1).toUpperCase() + temp.substring(1);
	}
	output += temp;
	return output;
}


function drawControls() {
	eval('var cf = ' + json_data);
	var chtml = '';
	chtml += '<form method="post" action="' + formAction + '" onsubmit="getCSS();">';
	for(var k = 0; k < cf.groups.length; k++) {
		chtml += '<div' + setConfClass(cd_group) + '>'
			+ '<div' + setConfClass(cd_group_info) + '>'
			+ '<div' + setConfClass(cd_group_title) + '><p' + setConfClass(cp_group_title) + '>'
			+ cf.groups[k].group_title
			+ '</p></div>'
			+ '<div' + setConfClass(cd_group_icon) + '>'
			+ '<img src="' + cf.groups[k].group_icon + '" alt="" />'
			+ '</div>'
			+ '</div>'
			+ '<div' + setConfClass(cd_group_ctrls) + '>';
		for(var i = 0; i < cf.groups[k].selectors.length; i++) {
			//chtml += '<div' + setConfClass(cd_sel_desc) + '><p' + setConfClass(cp_sel_desc) + '>'
			//	+ cf.groups[k].selectors[i].selector_description
			//	+ '</p></div>';
			for(var j = 0; j < cf.groups[k].selectors[i].selector_properties.length; j++) {
				count++;
				chtml += '<div' + setConfClass(cd_prop) + '>'
					+ '<div' + setConfClass(cd_prop_desc) + '><p' + setConfClass(cp_prop_desc) + '>'
					+ cf.groups[k].selectors[i].selector_properties[j].property_description
					+ '</p></div>'
					+ '<div' + setConfClass(cd_prop_ctrls) + '>'
					+ drawPropertyControl(cf.groups[k].selectors[i].selector_id, cf.groups[k].selectors[i].selector_properties[j])
					+ '</div>'
					+ '<div' + setConfClass(cd_clearall) + '></div>'
					+ '</div>';
			}
		}

		chtml += '</div>'
			+ '<div' + setConfClass(cd_clearall) + '></div>'
			+ '</div>';
	}
	chtml += '<div' + setConfClass(cd_ctrls) + '>' 
		+ '<textarea name="form_data[newcss]" id="newcss" style="display: none;"></textarea>'
		+ '</div>'
    + '<input name="profile_type" value="ui" type="hidden">'
		+ '<input name="type" value="style" type="hidden">'
    + '<textarea name="form_data[user_json]" id="user_json" style="display: none;"></textarea>'
    + '<input type="hidden" name="uid" value="'+ uid +'" />'
    + '<input type="hidden" name="gid" value="'+ gid +'" />'
    + '<input type="hidden" name="stype" value=' + settings_type +' />'
    + '<input type="hidden" value="" name="action" id="form_action" />'
    + '<input name="submit" type="submit" value="  Save styles  " onclick="javascript: document.getElementById(\'form_action\').value=\'applyStyle\';" />'
    + '<input name="restore_default" type="submit" value="  Restore default styles " onclick="javascript: document.getElementById(\'form_action\').value=\'restoreStyle\';" />'
    + '</form>';

  
	$("#conf_container").html(chtml);
	$("body").html($("body").html() + '<div id="colorpicker_cont" style="display: none"><div id="colorpicker"></div>'
		+ '<div id="colorpicker_close"><ul' + setConfClass(cu_choose_color) + '>' 
			+ '<li' + setConfClass(cl_choose_color) + '><a href="#" onclick="hideColorPicker(); return false;">Set color</a></li></ul></div>'
		+ '</div><iframe id="colorpicker_cont2" style="display: none"></iframe>');
	$('#colorpicker_cont').mouseover(function() { 
		canClose = false;
	});
	$('#colorpicker_cont').mouseout(function() { 
		canClose = true;
	});

}


function drawPropertyControl(sel_id, arg) {
	var cphtml = '';

	if(arg.property_id.indexOf("color") >= 0) {
    imp_off = arg.property_value.indexOf('!important');
    if(imp_off != -1) {
      arg.property_value = arg.property_value.substring(0,imp_off);
    }
		cphtml += '<p' + setConfClass(cd_choose_color) + '>' 
      + '<input' + setConfClass(cp_inputtext) + ' type="text" id="cpi_' + count + '" style="background-color: ' + arg.property_value + ';" value="' + decToHex(arg.property_value) + '" ' 
      + 'onchange="readColor(\'' + sel_id + '\', \'' + arg.property_id + '\', this);" readonly /></p>'
			+ '</div><div' + setConfClass(cd_choose_color) + '><ul' + setConfClass(cu_choose_color) + '><li' + setConfClass(cl_choose_color) + '>'
			+ '<a href="#" onclick="showColorPicker(this, ' + count + ', \'' + sel_id + '\', \'' + arg.property_id + '\', \'' + arg.property_value + '\'); return false;">Pick a color</a></li></ul>';
	}

	else if(arg.property_id.indexOf("font-family") >= 0) {
		cphtml += '<select' + setConfClass(cp_select) + ' id="cpi_' + count + '" onchange="changeCSS(\'' + sel_id + '\', \'' + arg.property_id + '\', this.options[this.selectedIndex].value);">';
		for(var i = 0; i < arg.property_defaults.length; i++) {
			if(arg.property_defaults[i] == arg.property_value)
				cphtml += '<option selected="selected" value="' + arg.property_defaults[i] + '">' + arg.property_defaults[i] + '</option>';
			else
				cphtml += '<option value="' + arg.property_defaults[i] + '">' + arg.property_defaults[i] + '</option>';
		}
		cphtml += '</select>';
	}

	else if(arg.property_id.indexOf("image") >= 0) {
    var img_width = 'auto'; 
    if(sel_id == 'div.module') {
      img_width = '50px';
    }
		cphtml += '<ul' + setConfClass(cu_image_list) + '>';
  	cphtml += '<li' + setConfClass(cl_image_list) + '><img border="1" src="/Themes/Default/images/white.jpg" alt="" /><br /><input' + setConfClass(cp_inputradio) + ' checked type="radio" name="cpi_' + count + '" value="" '
				+ 'onclick="changeCSS(\'' + sel_id + '\', \'' + arg.property_id + '\', \'none\');" /></li>';
    
		for(var i = 0; i < arg.property_defaults.length; i++) {
			if(/url\((.*)\)/.test(arg.property_defaults[i])) {
				if(arg.property_value == 'url(/' + RegExp.$1 + ')')
					cphtml += '<li' + setConfClass(cl_image_list) + '><img border="1" src="' + RegExp.$1 + '" alt="" width="'+img_width+'" height="'+img_width+'"/><br />'
						+ '<input' + setConfClass(cp_inputradio) + ' checked type="radio" name="cpi_' + count + '" value="' + arg.property_defaults[i] + '" ' 
						+ 'onclick="changeCSS(\'' + sel_id + '\', \'' + arg.property_id + '\', \'' + arg.property_defaults[i] + '\');" /></li>';
				else
					cphtml += '<li' + setConfClass(cl_image_list) + '><img border="1" src="' + RegExp.$1 + '" alt="" width="'+img_width+'" height="'+img_width+'"/><br />'
						+ '<input' + setConfClass(cp_inputradio) + ' type="radio" name="cpi_' + count + '" value="' + arg.property_defaults[i] + '" ' 
						+ 'onclick="changeCSS(\'' + sel_id + '\', \'' + arg.property_id + '\', \'' + arg.property_defaults[i] + '\');" /></li>';
			}
		}
    
		cphtml += "</ul>";
	}

	else if(arg.property_id.indexOf("visibility") >= 0) {
		var chkd = (arg.property_value == 'visible') ? ' checked="checked"' : '';
		cphtml += '<input' + setConfClass(cp_inputcheckbox) + ' id="cpi_' + count + '" type="checkbox"' + chkd 
			+ ' onclick="(this.checked) ? changeCSS(\'' + sel_id + '\', \'' + arg.property_id + '\', \'visible\') : changeCSS(\'' + sel_id + '\', \'' + arg.property_id + '\', \'hidden\');" />';
	}
	return cphtml;
}


function changeCSS(sel_id, p_id, p_val) {
	eval('var cf = ' + json_data);
	for(var k = 0; k < cf.groups.length; k++) {
		for(var i = 0; i < cf.groups[k].selectors.length; i++) {
			if(cf.groups[k].selectors[i].selector_id == sel_id) {
				for(var j = 0; j < cf.groups[k].selectors[i].selector_properties.length; j++) {
					if(cf.groups[k].selectors[i].selector_properties[j].property_id == p_id) {
            if(p_val.indexOf('!important') == -1) {
               p_val = p_val + ' !important';
            }
						cf.groups[k].selectors[i].selector_properties[j].property_value = p_val;
						break;
					}
				}
				break;
			}
		}
	}
	changed = true;
	json_data = JSONstring.make(cf).replace(/\n/g, '');
	applyCSS();
}


function showColorPicker(arg, numc, selid, propid, propvalue) {
	$.farbtastic('#colorpicker').linkTo('#cpi_' + numc);
	$.farbtastic('#colorpicker').setColor(propvalue);
	canClose = false;
	currSelId = selid;
	currPropId = propid;
	eval('$(\'#colorpicker_cont\').css({ position: \"absolute\", display: \"block\", top: \"' 
		+ (getAbsoluteOffsetTop(arg) + 20) + 'px\", left: \"' 
		+ (getAbsoluteOffsetLeft(arg) - 100) + 'px\" });');
	eval('$(\'#colorpicker_cont2\').css({ position: \"absolute\", display: \"block\", top: \"' 
		+ (getAbsoluteOffsetTop(arg) + 20) + 'px\", left: \"' 
		+ (getAbsoluteOffsetLeft(arg) - 100) + 'px\" });');
}


function hideColorPicker() {
	changeCSS(currSelId, currPropId, $.farbtastic('#colorpicker').color);
	$("#colorpicker_cont2").hide();
	$("#colorpicker_cont").hide();
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


function readColor(selid, propid, arg) {
	if(/#?[0-9A-Fa-f]{6}/.test(arg.value)) {
		$.farbtastic('#colorpicker').linkTo('#' + arg.id);
		$.farbtastic('#colorpicker').setColor(arg.value);
		currSelId = selid;
		currPropId = propid;
		hideColorPicker();
	}
}


function viewJSON() {
	var json_code = json_data;
	if(changed) {
		json_code = json_code.replace(/{/g, '{\n');
		json_code = json_code.replace(/}/g, '\n}\n');
		json_code = json_code.replace(/\[/g, '\[\n');
		json_code = json_code.replace(/]/g, '\n]\n');
		json_code = json_code.replace(/,/g, ',\n');
		json_code = json_code.replace(/}\n,/g, '},');
		json_code = json_code.replace(/\n\n/g, '\n');
	
		var json_code_p = json_code.split('\n');
		var indc = new Array(json_code_p.length);
		var symbsx = ['{', '['];
		var symbdx = ['}', ']'];
		for(var i = 0; i < json_code_p.length; i++)
			indc[i] = 0;
		for(var i = 0; i < json_code_p.length; i++) {
			for(var k = 0; k < symbsx.length; k++) {
				if(json_code_p[i].charAt(json_code_p[i].length - 1) == symbsx[k]) {
					for(var j = i + 1; j < json_code_p.length; j++)
						indc[j]++;
				}
				else if(json_code_p[i].charAt(0) == symbdx[k]) {
					for(var j = i; j < json_code_p.length; j++)
						indc[j]--;
				}
			}

		}
		for(var i = 0; i < json_code_p.length; i++) {
			for(var k = 0; k < indc[i]; k++) {
				json_code_p[i] = '\t' + json_code_p[i];
			}
		}
		json_code = json_code_p.join('\n');
	}
}


function viewCSS() {
	eval('var cf = ' + json_data);
	var css_code = "";
	for(var k = 0; k < cf.groups.length; k++) {
		for(var i = 0; i < cf.groups[k].selectors.length; i++) {
			css_code += cf.groups[k].selectors[i].selector_id + ' {\n';
			for(var j = 0; j < cf.groups[k].selectors[i].selector_properties.length; j++) {
				css_code += '\t' + cf.groups[k].selectors[i].selector_properties[j].property_id + ': ';
				css_code += cf.groups[k].selectors[i].selector_properties[j].property_value + ';\n';
			}
			css_code += '}\n\n';
		}
	};
}


function viewHTML() {
	//alert($('#conf_container').html());
}


function setConfClass(arg) {
	if(convert_css) {
		if(arg != null) {
			if(arg.length > 0)
				return ' class="' + arg + '"';
		}
	}
}


function getCSS() {
	eval('var cf = ' + json_data);
	var css_code = "";
	for(var k = 0; k < cf.groups.length; k++) {
		for(var i = 0; i < cf.groups[k].selectors.length; i++) {
			css_code += cf.groups[k].selectors[i].selector_id + ' {\n';
			for(var j = 0; j < cf.groups[k].selectors[i].selector_properties.length; j++) {
				css_code += '\t' + cf.groups[k].selectors[i].selector_properties[j].property_id + ': ';
				css_code += cf.groups[k].selectors[i].selector_properties[j].property_value + ';\n';
			}
			css_code += '}\n\n';
		}
	};
	$('#newcss').html(css_code);
  $('#user_json').html(json_data);
}


function restoreDefaults() {
	json_data = json_defaults;
	drawControls();
	applyCSS();
}

function readCSS() {
  eval('var cf = ' + json_data);
  for(var k = 0; k < cf.groups.length; k++) {
    for(var i = 0; i < cf.groups[k].selectors.length; i++) {
      var sel_id = cf.groups[k].selectors[i].selector_id;
      for(var j = 0; j < cf.groups[k].selectors[i].selector_properties.length; j++) {
        var prop_id = cf.groups[k].selectors[i].selector_properties[j].property_id;
        if(($(sel_id).css(trProperty(prop_id)) != null) && ($(sel_id).css(trProperty(prop_id)) != '') && ($(sel_id).css(trProperty(prop_id)) != 'undefined')) {
          if(/rgb\((.*)\)/.test($(sel_id).css(trProperty(prop_id))))
            cf.groups[k].selectors[i].selector_properties[j].property_value = decToHex($(sel_id).css(trProperty(prop_id)));
          else if(/url\((.*)\)/.test($(sel_id).css(trProperty(prop_id)))) {
              var temp_path = imagesdir_abspath.replace(/\//g, '\\/')
              cf.groups[k].selectors[i].selector_properties[j].property_value = eval('$(sel_id).css(trProperty(prop_id)).replace(/' + temp_path + '/g, \'\')');
          }
          else
            cf.groups[k].selectors[i].selector_properties[j].property_value = $(sel_id).css(trProperty(prop_id));
        }
      }
    }
  }
  json_data = JSONstring.make(cf).replace(/\n/g, '');
}

function decToHex(arg) {
  if(/rgb\((.*)\)/.test(arg)) {
    var decs = RegExp.$1.replace(/ /g, '').split(',');
    var output = '#';
    for(var i = 0; i < decs.length; i++) {
      output += from10toradix(decs[i], 16, 2);
    }
    return output;
  }
  else
    return arg;
}


function initArray() {
  this.length = initArray.arguments.length;
  for (var i = 0; i < this.length; i++)
    this[i] = initArray.arguments[i];
}
  

function from10toradix(value, radix, digits){
  var retval = '';
  var ConvArray = new initArray(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f');
  var intnum;
  var tmpnum;
  var i = 0;
  intnum = parseInt(value, 10);
  if (isNaN(intnum)) {
    retval = 'NaN';
  }
  else {
    while(intnum > 0.9) {
      i++;
      tmpnum = intnum;
      retval = ConvArray[tmpnum % radix] + retval;  
      intnum = Math.floor(tmpnum / radix);
      if (i > 100) {
        retval = 'NaN';
        break;
      }
    }
  }
  while(retval.length < digits)
    retval = '0' + retval;
  return retval;
}

