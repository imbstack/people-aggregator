function init() {
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
      setCSSAttribute(h1s[j], stylevalue);
    }
  }
  var mod = document.getElementById('header');
  var h1s = mod.getElementsByTagName('h1');
  for(var j = 0; j < h1s.length; j++) {
    var stylevalue = "color: " + document.getElementById('headerh1col').value + ";"
    setCSSAttribute(h1s[j], stylevalue);
  }
  var h2s = mod.getElementsByTagName('h2');
  for(var j = 0; j < h2s.length; j++) {
    var stylevalue = "color: " + document.getElementById('headerh2col').value + ";"
    setCSSAttribute(h2s[j], stylevalue);
  }
  var mod = document.getElementById('container');
  var stylevalue = "background-color: " + document.getElementById('containercol').value + ";"
  setCSSAttribute(mod, stylevalue);
  var mod = document.getElementById('col_b');
  var stylevalue = "background-color: " + document.getElementById('colbcol').value + ";"
  setCSSAttribute(mod, stylevalue);
}

function readColor(arg) {
  if(/#?[0-9A-Fa-f]{6}/.test(arg.value)) {
    arg.value = arg.value.toUpperCase();
    setColor(arg.id, arg.value);
  }
}

function setColor(colorObjId, color) {
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
          setCSSAttribute(h1s[j], stylevalue);
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
          setCSSAttribute(h1s[j], stylevalue);
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
        setCSSAttribute(h1s[j], stylevalue);
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
        setCSSAttribute(h2s[j], stylevalue);
      }
      break;
    case 'containercol':
      var mod = document.getElementById('container');
      var stylevalue = "background-color: " + color + ";"
      setCSSAttribute(mod, stylevalue);
      mod.style.cssText = stylevalue;
      break;
    case 'colbcol':
      var mod = document.getElementById('col_b');
      var stylevalue = "background-color: " + color + ";"
      setCSSAttribute(mod, stylevalue);
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
      setCSSAttribute(h1s[j], stylevalue);
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
    setCSSAttribute(h1s[j], stylevalue);
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
    setCSSAttribute(h1s[j], stylevalue);
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
    setCSSAttribute(h2s[j], stylevalue);
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
    setCSSAttribute(h2s[j], stylevalue);
  }
}

function chBodyShad() {
  document.getElementById('body_shadow').style.visibility = document.getElementById("bodyshad").options[document.getElementById("bodyshad").selectedIndex].value;
}

function getCSS() {
  var newcss = 'body {\n'
  + '\tbackground-color:'+document.getElementById("bodybgcol").value+';\n'
  + '\tfont-family: ' + document.getElementById("bodyfont").options[document.getElementById("bodyfont").selectedIndex].value + ';\n'
  + '}\n'
  + '\t#header h1 {\n'
  + '\tfont-family: ' + document.getElementById("headerh1font").options[document.getElementById("headerh1font").selectedIndex].value + ';\n'
  + '\tcolor: ' + document.getElementById("headerh1col").value + ';\n'
  + '\tvisibility: ' + document.getElementById("headerh1vis").options[document.getElementById("headerh1vis").selectedIndex].value + ';\n'
  + '}\n'
  + '\t#header h2 {\n'
  + '\tfont-family: ' + document.getElementById("headerh2font").options[document.getElementById("headerh1font").selectedIndex].value + ';\n'
  + '\tcolor: ' + document.getElementById("headerh2col").value + ';\n'
  + '\tvisibility: ' + document.getElementById("headerh2vis").options[document.getElementById("headerh2vis").selectedIndex].value + ';\n'
  + '}\n'
  + '.module h1{\n'
  + '\tbackground-color: ' + document.getElementById("moduleh1bgcol").value + ';\n'
  + '\tcolor: ' + document.getElementById("moduleh1col").value + ';\n'
  + '\tfont-family: ' + document.getElementById("moduleh1font").options[document.getElementById("moduleh1font").selectedIndex].value + ';\n'
  + '}\n'
  + '.module {\n'
  + '\tborder-color: ' + document.getElementById("modulecol").value + ';\n'
  + '}\n'
  + '#container {\n'
  + '\tbackground-color: ' + document.getElementById("containercol").value + ';\n'
  + '}\n'
  + '#col_b {\n'
  + '\tbackground-color: ' + document.getElementById("colbcol").value + ';\n'
  + '}\n'
  ;
  document.getElementById("newcss").innerHTML = newcss;
}

function setCSSAttribute(arg, vv) {
  if(!window.XMLHttpRequest)
    arg.style.cssText = vv;
  else
    arg.setAttribute("style", vv);
}




var moduleNames = {
  'mod_relations': 'RelationsModule',
  'mod_recent_media': 'ImagesModule',
  '"mod_my_groups': 'MyGroupsModule',
  // '': 'UserInformationModule',
  'community_blog': 'ShowContentModule',
  'mod_photo': 'UserPhotoModule',
  'mod_added_as_a_friend_by': 'InRelationModule',
  'mod_message': 'UserMessagesModule',
  'mod_my_recent_comments': 'RecentCommentsModule',
  'mod_flickr': 'FlickrModule',
  'mod_links_management': 'LinkModule',
  'mod_my_links': 'MyLinksModule',
  'mod_my_network': 'MyNetworksModule',
  'mod_about_user': 'AboutUserModule'
  };

var serialize = function() {
  var ser = {};
  ser['collapsed'] = [];
  $('div[@id^="col_"]').not('#col_b').each( // any div who's id starts with 'col_'
    function() {
      var col = $(this).attr('id'); 
      var id;
      switch (col) {
        case 'col_a':
          id = 'left';
          break;
        case 'col_b': // this should actually not happen
          id = 'middle';
          break;
        case 'col_c':
          id = 'right';
          break;
        default:
          id = col;
          break;
      }
      ser[id] = [];
      $('.module', this).each(
        function() {
          var hid;
          try {
            hid = $(this).attr('id');
          } catch(e) { 
            return;
          }
          var mid = (moduleNames[hid]) ? moduleNames[hid] : hid;
          if (mid) {
            ser[id].push(mid);
            if( $('div',this).is(':hidden') ) {
              ser['collapsed'].push(hid); 
              // we want the actual html id here!
              // as we will be using that on page reload to collapse the HTML
            }
          }
        }
      );
    }
  );
  // $('#serialize').html(toJSON(ser));
  // send AJAX request to server
//  alert(toJSON(ser));

  $.get(
    "ajax/modulesettings.php",
    {
      'page_id': page_id,
      'data': toJSON(ser)
    },
    function(xml) {
      // alert(xml);
    }
  );
}


// this code will be executed as soon as the the page's DOM is ready
$(document).ready(
  function() {
    
    // init colorpicker etc
    initFC();
    init(); 
    // init sortable modules
    $('div[@id^="col_"]').not('#col_b').Sortable(
        {
        accept : 'module',
        handle: 'h1',
        tolerance: 'intersect',
        snapDistance: 3,
        cursorAt: {top:6,left:20},
        helperclass: 'sorthelper',
        onStop: function() {
          serialize();
        }
      }
    );
    
    // collapse/expand click events
    $('.module > h1').click(
      function() {
        $('../div', this).toggle('slow', serialize);
      }
    );
    
    // see if we need to collapse any modules
    if (collapsed) {
      var l = collapsed.length;
      for (var i = 0; i<l; i++) {
        m = collapsed[i];
        $('#'+m+'/div').toggle();
      }
    }
  }
);