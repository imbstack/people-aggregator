function modal_getPageScrollTop(){
	var yScrolltop;
	var xScrollleft;
	if (self.pageYOffset || self.pageXOffset) {
		yScrolltop = self.pageYOffset;
		xScrollleft = self.pageXOffset;
	} else if (document.documentElement && document.documentElement.scrollTop || document.documentElement.scrollLeft ){	 // Explorer 6 Strict
		yScrolltop = document.documentElement.scrollTop;
		xScrollleft = document.documentElement.scrollLeft;
	} else if (document.body) {// all other Explorers
		yScrolltop = document.body.scrollTop;
		xScrollleft = document.body.scrollLeft;
	}
	arrayPageScroll = new Array(xScrollleft,yScrolltop) 
	return arrayPageScroll;
}

function modal_getPageSize(){
	var de = document.documentElement;
	var w = window.innerWidth || self.innerWidth || (de&&de.clientWidth) || document.body.clientWidth;
	var h = window.innerHeight || self.innerHeight || (de&&de.clientHeight) || document.body.clientHeight
	arrayPageSize = new Array(w,h) 
	return arrayPageSize;
}

function modal_overlaySize() {
	if (window.innerHeight && window.scrollMaxY || window.innerWidth && window.scrollMaxX) {	
		yScroll = window.innerHeight + window.scrollMaxY;
		xScroll = window.innerWidth + window.scrollMaxX;
		var deff = document.documentElement;
		var wff = (deff&&deff.clientWidth) || document.body.clientWidth || window.innerWidth || self.innerWidth;
		var hff = (deff&&deff.clientHeight) || document.body.clientHeight || window.innerHeight || self.innerHeight;
		xScroll -= (window.innerWidth - wff);
		yScroll -= (window.innerHeight - hff);
	} else if (document.body.scrollHeight > document.body.offsetHeight || document.body.scrollWidth > document.body.offsetWidth){ // all but Explorer Mac
		yScroll = document.body.scrollHeight;
		xScroll = document.body.scrollWidth;
	} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
		yScroll = document.body.offsetHeight;
		xScroll = document.body.offsetWidth;
  	}
	$("#modal_overlay").css({"height":yScroll +"px", "width":xScroll +"px"});
}

var modal_DONE = false;
var modal_HEIGHT = 300;
var modal_WIDTH = 600;

function modal_show(caption, html, height, width, nonmodal) {
  modal_HEIGHT = height || 300;
  modal_WIDTH = width || 600;
  if(!modal_DONE) {
    $(document.body)
      .append("<div id='modal_overlay'></div><div id='modal_window'></div>");
    nonmodal = true; // debug
    if(nonmodal) {
      // we don't use the click method of the overlay directly
      // as this has strange behaviour in IE6
      // instead we trigger the onClick of the hide link
      $("#modal_overlay").click( 
        function(e) { 
          $('a.hide').focus().click(); 
        } 
      );
    }
    $(window).resize(modal_overlaySize);
    $(window).resize(modal_position);
    $(window).scroll(modal_overlaySize);
    // $(window).scroll(modal_position);
    modal_DONE = true;
  }

  $("#modal_window").html(
    '<a class="hide" href="javascript://" onclick="modal_hide();"></a>'
    + '<div id="modal_caption"><h3>' + caption + '</h3></div>'
    );

  $("#modal_window").append(html);

  modal_overlaySize();
  $("#modal_overlay").show();
  modal_position();
  $("#modal_window").show(); // 'slow'
}

function modal_hide() {
  $("#modal_window,#modal_overlay").hide();
}

function modal_position() {
	var pagesize = modal_getPageSize();	
	var arrayPageScroll = modal_getPageScrollTop();	
	$("#modal_window").css({width:modal_WIDTH+"px",left: (arrayPageScroll[0] + (pagesize[0] - modal_WIDTH)/2)+"px", top: (arrayPageScroll[1] + (pagesize[1]-modal_HEIGHT)/2)+"px" });
}



