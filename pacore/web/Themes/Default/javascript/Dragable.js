function status(html) {
  getElement("status").innerHTML =
    html;
}
////////////////////////////////////////////////////////////////////////
// drag and drop lib for BBM PeaopleAggregator
// uses on code from Danny Goodman
// extracted from weboutliner.js
// and generalized by Martin Spernau 2006-03
// modified to use MochiKit DOM

var gUserAgent =
  getUserAgent(); // user agent

var gIsOpera =
  navigator.userAgent.search (/Opera/i) + 1; // true if Opera

var gIsMacMSIE =
  navigator.userAgent.search (/MSIE.*Mac/i) + 1; // true if Mac MSIE

gIsMacMSIE =
  gIsMacMSIE & ! gIsOpera;

var gIsSafari =
  navigator.userAgent.search (/safari/i) + 1; // true if Safari

var gIsWin =
  navigator.appVersion.indexOf ("Win") != -1; // true if Windows, false if MacOS

var gIsIE =
  navigator.appName.indexOf ("Microsoft") != -1; // true if MSIE, false if other browser

var gIsWinMSIE =
  gIsWin & gIsIE; // true is Windows MSIE


// Browser sniffing
function getUserAgent() {
  var ua =
    navigator.userAgent;

  if (ua.search(/KHTML.*Safari/i) != -1) {
    return 'Safari';
  }

  if (ua.search(/MSIE.*Mac/i) != -1) {
    return 'MacIE';
  }

  if (ua.search(/Mozilla.*Gecko/) != -1 &&
      ua.indexOf('KHTML') == -1) {
    return 'Gecko';
  }

  if (ua.search(/MSIE.*Win/i) != -1) {
    return 'WinIE';
  }

  return 'unidentified';
}

// Add an eventListener to browsers that can do it somehow.
function addEvent(obj, evType, fn) {
  if (obj.addEventListener) {
    obj.addEventListener(evType, fn, true);
    return true;
  } else if (obj.attachEvent) {
    var r =
      obj.attachEvent("on"+evType, fn);
    return r;
  } else {
    return false;
  }
}



////////////////////////////////////////////////////////////////////////
// Constructor
function Dragable()
{
}

////////////////////////////////////////////////////////////////////////
// Class Constants

// Holds reference to currently dragged element
Dragable.draggedElem = {
  elem: null,
  proxy: null,
  activeDropzone: null,
  isDragging: false,
  startTime: Number.MAX_VALUE,
  IEScrollTop: 0,
  IEScrollLeft: 0
};

Dragable.dropzones =
  [];

////////////////////////////////////////////////////////////////////////
// Drag and drop support

// return mouse.x and mouse.y
Dragable.prototype.getMousePosition =
  function(evt) {
    var mouse =
      {};
    if (evt.pageX) {
    // compute new pointer location according to cursor movement
      mouse.x =
        evt.pageX;
      mouse.y =
        evt.pageY;
    } else if (evt.clientX || evt.clientY) {
      mouse.x =
        evt.clientX;
      mouse.y =
        evt.clientY;
    }
    return mouse;
}

Dragable.prototype.distance =
  function (p1,p2) {
    // Find the vector between the two
    var dx =
      p1.x - p2.x;
    var dy =
      p1.y - p2.y;
    // Now find the length
    return Math.sqrt(dx*dx + dy*dy);
}

// Position an object at a specific pixel coordinate
Dragable.prototype.shiftTo =
  function(objOrId, x, y) {
  var obj =
    getElement(objOrId);
  if (obj) {
  obj.style.left =
    x + "px";
  obj.style.top =
    y + "px";
  }
}


// returns element's position
Dragable.prototype.getElementPosition =
  function(objOrId) {
  var offsetTrail =
    getElement(objOrId); // point to target element
  var offsetLeft =
    0;
  var offsetTop =
    0;

  while (offsetTrail) {
  // compute element's coordinates, relative to its parent's
    offsetLeft +=
      offsetTrail.offsetLeft; // until we're back at the top of the document
    offsetTop +=
      offsetTrail.offsetTop;
    offsetTrail =
      offsetTrail.offsetParent;
      // offsetParent is nil when we've reached the top of the DOM's tree
  }

  if (gUserAgent.indexOf('Mac') != -1 &&
      typeof document.body.leftMargin != 'undefined') {
    offsetLeft +=
      document.body.leftMargin;
      // use document.body on Mac browsers that support it
    offsetTop +=
      document.body.topMargin;
  }

  // return left and top coordinates in a coordinates object
  return {
    left: offsetLeft,
    top: offsetTop
  };
}

// get an absolute Rectangle for an element
Dragable.prototype.getAbsoluteRect =
  function(elem) {
  var pos =
    this.getElementPosition(elem);
  var right =
    pos.left + parseInt(elem.offsetWidth);
  var bottom =
    pos.top + parseInt(elem.offsetHeight);
  var centerLeft =
    pos.left + parseInt(elem.offsetWidth/2)
  var centerTop =
    pos.top + parseInt(elem.offsetHeight/2);
  return {
    top: pos.top,
    right: right,
    bottom: bottom,
    left: pos.left,
    centerTop: centerTop,
    centerLeft: centerLeft
  }
}

Dragable.prototype.direction =
  function(p1,p2) {
  // determine if p2 is [top|right|bottom|left] from p1
  var dx =
    p1.x - p2.x;
  var dy =
    p1.y - p2.y;
  var xdir =
    (dx>0) ? "left" : "right";
  var ydir =
    (dy<0) ? "bottom" : "top";

  return (Math.abs(dx) > Math.abs(dy)) ? xdir : ydir;
}

Dragable.prototype.directionAndDistance =
  function(p1,p2) {
  return  {
    dir:this.direction(p1,p2),
    dist:this.distance(p1,p2)
  };
}

Dragable.prototype.boxesHaveIntersection =
  function(b1,b2) {
  var haveIntersection =
    (
    b1.left < b2.right &&
    b1.right > b2.left &&
    b1.top < b2.bottom &&
    b1.bottom > b2.top
    );
  if(haveIntersection) {
    return this.directionAndDistance(
        {x:b1.centerLeft,y:b1.centerTop},
        {x:b2.centerLeft,y:b2.centerTop}
      );
  } else {
    return null;
  }
}

Dragable.prototype.pointInRect =
  function(p,r) {
      if ( // is the point inside the box?
        p.x  > r.left  &&
        p.x  < r.right &&
        p.y  > r.top   &&
        p.y  < r.bottom
      ) {
        return this.directionAndDistance(
          {x:r.centerLeft,y:r.centerTop},
          p
        );
      }
}

Dragable.prototype.checkDropzone =
  function(dz,ms,pr) {
    if(! dz.absRect) {
    // re-calc position-cache
      dz.absRect =
        this.getAbsoluteRect(dz.elem);
    }
    var dzar = // shortcut
      dz.absRect;
    var hasHit =
      false;
    var ret = 
      null;
    // check for mousepointer hit
    var mph =
      this.pointInRect(ms,dzar);
    if (mph) {
      // if the mouse is actually over the DZ,
      // we use ONLY that to determine hit quadrant
      ret = 
        mph;
    } else if(pr) {
      // check for intersection hits
      // only do this if we have been passed a proxy element rect
      // see if boxes intersect
      ret =
        ( this.boxesHaveIntersection(dzar,pr) );
    }
    // add distance mod for DZs that can be containers for other DZs
    
    return ret;
}


// Set global reference to element being engaged and dragged
Dragable.prototype.setDraggedElem =
  function(elem) {
  var draggedElem =
    Dragable.draggedElem;
  draggedElem.elem =
    elem; // store a reference to element being dragged
  draggedElem.startTime =
    new Date();
  var bod =
    document.body; // shortcut reference for next statements
  draggedElem.IEScrollTop =
    (gIsIE) ? bod.scrollTop + ((bod.parentNode.scrollTop) ? bod.parentNode.scrollTop : 0) : 0;
    // adjust for IE scrolling
  draggedElem.IEScrollLeft =
    (gIsIE) ? bod.scrollLeft + ((bod.parentNode.scrollLeft) ? bod.parentNode.scrollLeft : 0) : 0;
    // adjust for IE scrolling
  return;
}

// process mouseDown events
Dragable.prototype.startDrag =
  function(evt) {
  evt =
    evt ? evt : window.event; // equalize event models
  var target =
    evt.target ? evt.target : evt.srcElement; // get source element
  // get the ACTUAL element to be dragged,
  // elem may only be a drag-handle
  while (! hasElementClass(target, "dragable")) {
    // we are dragging something INSIDE the dragable
    target =
      target.parentNode;
  }
  this.setDraggedElem(target);
  // set dragged element's globals
  var draggedElem =
    Dragable.draggedElem;
  if (document.body &&
    document.body.setCapture) {
    // engage event capture in IE/Win
    document.body.setCapture();
  }

  // get current top left coordinates of draggedElem
  var elemPosition =
    this.getElementPosition(draggedElem.elem);

  // record position relative to mouse
  var mouse =
    this.getMousePosition(evt);
  this.offsetX =
    elemPosition.left - mouse.x;
  this.offsetY =
    elemPosition.top - mouse.y;

// getElement("status").innerHTML =
//   "<pre>mouse: x:"+mouse.x+" y:"+mouse.y+"\n"
//   +"elem: left:"+elemPosition.left
//   +" top:"+elemPosition.top+"\n"
//   +"offset: x:"+this.offsetX+" y:"+this.offsetY
//   +"</pre>"

  if (evt.preventDefault) {
    // block Mozilla/Safari selection during drag
    evt.preventDefault();
  }

  return false;
}

// perform a mouse drag on mousemove
Dragable.prototype.dragIt =
  function(evt) {
  // drag an element
  evt =
    evt ? evt : window.event; // equalize event models
  var draggedElem =
    Dragable.draggedElem;
  var delay =
    (new Date ()) - draggedElem.startTime;
    // time diff between mouse down and now

  if (draggedElem.elem && delay > 150) {
    // if drag and drop is engaged
    if(! draggedElem.isDragging) {
      // here we actually set up the dragging!
      // create a proxy element for dragging around
      draggedElem.proxy =
        draggedElem.elem.cloneNode(true);
      // set class name for visual control
      addElementClass(draggedElem.proxy, "drag-proxy");
      // ensure proper height and width
      var ps =
        draggedElem.proxy.style;
      ps.width =
        parseInt(draggedElem.elem.offsetWidth)+"px";
      ps.heigth =
        parseInt(draggedElem.elem.offsetHeight)+"px";
      // make position absolute
      ps.position =
        "absolute";

      appendChildNodes(document.body, draggedElem.proxy);

      // make note and visual
      draggedElem.isDragging =
        true;
      addElementClass(draggedElem.elem, "isdragging");
    }

    // we are dragging
    var mousePos =
      this.getMousePosition(evt);
    var left, top;
    left = mousePos.x + this.offsetX;
    top = mousePos.y + this.offsetY;

    // position at new location
    this.shiftTo(
      draggedElem.proxy,
      left + draggedElem.IEScrollLeft,
      top + draggedElem.IEScrollTop
    );
    evt.cancelBubble =
      true; // stop event propagation

    // let's see if we are over any dropzone
    var proxyRect =
      this.getAbsoluteRect(draggedElem.proxy);
    var dzs =
      Dragable.dropzones;
    var adzs =
      [];
    // clear the plate
    this.cancelAllDropzones();
    // iterate the DZs
    for(var i=0;i<dzs.length;i++) {
      // make sure we are not over our own origin
      var hitDz =
        this.checkDropzone(dzs[i], mousePos, proxyRect);
      if(hitDz) {
        if(hasElementClass(dzs[i].elem, "isdragging")) {
          continue;
        }
        if(dzs[i].kind == 'inside') {
          hitDz.dist =
            hitDz.dist + 3000;
        }
        adzs[adzs.length] =
          {
          dist:hitDz.dist,
          dir:hitDz.dir,
          dz:dzs[i]
          };
        dzs[i].dir =
          hitDz.dir;
      } else {
        dzs[i].dir =
          ""; // reset possible earlier setting
      }
    }
    if(! adzs.length) {
      draggedElem.activeDropzone =
        null;
    } else {
      // find the closest one and activate that
      adzs.sort(keyComparator("dist"));
      var theADz =
        adzs[0];
      // note active Dropzone
      Dragable.draggedElem.activeDropzone =
        theADz.dz;
      this.markActiveDropzone(
        theADz.dz,
        theADz.dir
      );
    }
    return false;
  }
}


// perform node drop on mouseup
Dragable.prototype.release =
  function(evt) {
  // turn off on mouseup
  evt =
    evt ? evt : window.event; // equalize event models
  var target =
    evt.target ? evt.target : evt.srcElement; // get source element
  var draggedElem =
    Dragable.draggedElem;
  if (draggedElem.elem) { // if drag and drop is engaged
    if (document.body &&
      document.body.releaseCapture) { // stop event capture in IE/Win
      document.body.releaseCapture();
    }

    if(draggedElem.activeDropzone) {
    // we are above a DZ
      var dz =
        draggedElem.activeDropzone;
      // need to remember the actual container element we insert into
      var dzContainer = 
        null;
      if(dz.kind=="inside") {
        dzContainer =
          dz.elem;
        if ( 
          (dzContainer.hasChildNodes() ) 
          &&
          (dz.dir == "top" ||
            dz.dir == "left")
          ) {
          dzContainer.insertBefore(
            draggedElem.elem, // what
            dzContainer.firstChild // before this
          );            
        } else {
          // this is easy, just appendChild
          appendChildNodes(
            dz.elem,
            draggedElem.elem
          );
        }
      } else if(dz.kind=="ontop") {
        // decide if we want to inser before or after
        dzContainer =
          dz.elem.parentNode;
        if (
          dz.dir == "top" ||
          dz.dir == "left") {
          dzContainer.insertBefore(
            draggedElem.elem, // what
            dz.elem // before this
          );
        } else {
          dzContainer.insertBefore(
            draggedElem.elem, // what
            dz.elem.nextSibling
            // nextSibling returns null if there is none
            // insertBefore then acts like appendChild
          );
        }
      }
    } else {
    }

    // report the drop
    // but allow onclick etc to run FIRST!!
    var updateFn =
      bind(
        function () { 
          this.sendJSON(this.serializeState(dzContainer));
        },
        this // the 'this' we bind
      );
    window.setTimeout(
      updateFn,
      200);

    // remove the proxy element
    if(draggedElem.proxy) {
      removeElement(draggedElem.proxy);
    }

    // clean up Dropzones
    var dzs =
      Dragable.dropzones;
    this.cancelAllDropzones();
    // make sure the positions for dropzones are re-calced on next check
    // layout of page might have changed
    this.clearDropzonePositions();

    // disengage drag&drop
    if(draggedElem.isDragging) {
      removeElementClass(draggedElem.elem, "isdragging");
      draggedElem.isDragging =
        false;
    }
    draggedElem.elem =
      null;
    draggedElem.proxy =
      null;
    draggedElem.activeDropzone =
      null;
    draggedElem.startTime =
      Number.MAX_VALUE;
      // reset timer value
  }
}

// highlightDropzone
// make a dropzone visible when we drag something over it
Dragable.prototype.markActiveDropzone =
  function(dz, where) {
  // clean up previous visuals
  this.cancelDropzone(dz);
  // make visual
  addElementClass(
    dz.elem,
    "active-dropzone"
  );
  addElementClass(
    dz.elem,
    where+"-dz"
  );
}
// lowlightDropzone
// mouseout
Dragable.prototype.cancelDropzone =
  function(dz) {
  if(dz.elem) {
    // remove visual
    var cs =
      ["active-dropzone","top-dz","right-dz","bottom-dz","left-dz"];
    for(var c=0;c<cs.length;c++) {
      removeElementClass(
        dz.elem, cs[c]
      );
    }
  }
}

Dragable.prototype.cancelAllDropzones =
  function() {
  for(var dz in Dragable.dropzones) {
    this.cancelDropzone(Dragable.dropzones[dz]);
  }
}

Dragable.prototype.registerDropzone =
  function (elem) {
  var absRect =
    this.getAbsoluteRect(elem);
  var kind =
    "inside"; // default
  if(hasElementClass(elem,"ontop")) {
  // this should be set for dragable elements,
  // as we want to insert before or behind them in DOM order
    kind =
      "ontop";
  }
  var dz =
    {
    elem: elem,
    kind: kind,
    dir: "",
    absRect: absRect
    // we could leave null here,as this will be calced on first check
    };
  Dragable.dropzones[Dragable.dropzones.length] = dz;
}

Dragable.prototype.clearDropzonePositions =
  function() {
  var dzs =
    Dragable.dropzones;
  for(var i=0; i<dzs.length; i++) {
    dzs[i].absRect =
      null;
  }
}

Dragable.prototype.sendJSON =
  function(jsonString) {
  if(!page_id) {return;}
  var url =
    "/ajax/modulesettings.php?page_id="+page_id+"&data="+jsonString;
  var req = getXMLHttpRequest();
  req.open("POST", url, true);
  var d =
    sendXMLHttpRequest(req);
  var msg =
    url+"\n"+jsonString;
  d.addCallback(
//    log("OK: sending of JSON data\n"+msg)
  );
  d.addErrback(
    function(err) {
      log(err+"\nERROR: sending of JSON data\n"+msg);
    }
   );
}


// for each dropzone build an array of dragable objects inside
// then serialize that into an ajax-able query-string
Dragable.prototype.serializeState =
  function () {
// log("enter serializeState");

  var dzs =
    Dragable.dropzones;
  var dzsStruct =
    {};
  dzsStruct['collapsed'] =
    [];
  for(var i=0; i<dzs.length; i++) {
    if (hasElementClass(dzs[i].elem, "dragable")) {
      continue;
    }
    // else
    var dzName =
      getNodeAttribute(
      // the IE-safe way to get this
        dzs[i].elem,
        "class"
        );
      // alternate not IE safe:
      // dzs[i].elem.className;
// log("dz-name:",dzName);
    // clean up name
    var rg =
      /(\w+)-parent/;
    rg.exec(dzName);
    dzName =
      RegExp.$1;
    dzsStruct[dzName] =
      [];
    var drs =
      getElementsByTagAndClassName(
        "DIV", 
        "dragable", // dragable elements only
        dzs[i].elem // search withing this dropzone
      );
    for (var j=0;j<drs.length;j++) {
      try { 
        var handleDiv =
          getElementsByTagAndClassName(
            null,
            "drag",
             drs[j]
          )[0];
        var modName =
          handleDiv.id.replace(/_block/,'');
        var modDispl =
          getElement(modName+"_block_content").style.display;
        modDispl =
          (modDispl != "") ? modDispl : "block";
      } catch (e) {
        log("duh!", e);      
      };
      if(modName) {
        dzsStruct[dzName][dzsStruct[dzName].length] =
          modName;
        if (modDispl == 'none') {
          dzsStruct['collapsed'][dzsStruct['collapsed'].length] = 
            modName+"_block_content";
        } else {
        // log(modName, modDispl);
        }
      }
    }
  }
  return serializeJSON(dzsStruct);
}

Dragable.prototype.activate =
  function() {
  // add mouse Events to document
  // use the global gDragable object as reference to methods
  addEvent(document, "mousemove",
      function (evt) {
        return gDragable.dragIt(evt);
      }
  );
  addEvent(document, "mouseup",
      function (evt) {
        return gDragable.release(evt);
      }
  );

  // mouseover
  // mouseout

  // activate all "dragable" elements
  var dragables =
    getElementsByTagAndClassName(null, "drag");
  map(
    function(elem) {
      addEvent(elem,
        "mousedown",
          function (evt) {
            return gDragable.startDrag(evt);
          }
      );
    },
    dragables
  );

  // activate Dropzone elements
  var dzs =
    getElementsByTagAndClassName(null, "dropzone");
  for (var i = 0; i < dzs.length; i++) {
      gDragable.registerDropzone(dzs[i]);
  }
  // make all dragable elements also be
  // dropzones with special "ontop"
  var dots =
    getElementsByTagAndClassName(null, "dragable");
  // activate Drop ontop elements
  for (var i = 0; i < dots.length; i++) {
      addElementClass(dots[i], "drop ontop");
      gDragable.registerDropzone(dots[i]);
  }
}

function collapseMods() {
  // this might be better placed in another file
  if (window.collapsed) {
    for(var i=0; i<collapsed.length; i++) {
      getElement(collapsed[i]).style.display = 
        "none";
    }
  }
}

// create global Dragable
gDragable =
  new Dragable();


addEvent(
  window,
  "load",
  gDragable.activate
);
addEvent(
  window,
  "load",
  collapseMods
);
addEvent(
  window,
  "resize",
  gDragable.clearDropzonePositions
);
