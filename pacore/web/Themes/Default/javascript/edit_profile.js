// a more generalized version of the change_edit_profile_perm
// using the power of JQuery
// -Martin
function set_all_perms(selobj) {
  var select_id = selobj.value;
  // get the parent FORM element
  var myForm = $(selobj).parents('form');
  // get all *_perm selects in form via their class
  $('.select_access', myForm).not(this).each(
    function() {
      try { this.value = select_id; } catch(e) { }
    }
  );
}

function removerelation(link) {

  // this will be called from the ajax loaded friendsLists
  // get the configuration data from the HTML
  var fjq = $(link).parents('li.vcard');
  var network = fjq.parents('.personaproperties').attr('id');
  var network_uid = fjq.attr('id');
  $.post(
    '/ajax/extrelation.php',
    {
    'del': 1,
    'network': network,
    'network_uid': network_uid
    },
    function(data) {
      $(link).parents('div.friendconfig').html(data);
    }
     );
  return false;
}

function addrelation(link) {
  // this will be called from the ajax loaded friendsLists
  // get the configuration data from the HTML
  var fjq = $(link).parents('li.vcard');
  var network = fjq.parents('.personaproperties').attr('id');
  var network_uid = fjq.attr('id');
  var display_name = $('a.fn', fjq).text();
  var profile_url = $('a.fn', fjq).attr('href');
  var thumbnail_url = $('img.photo', fjq).attr('src');
  $.post(
    '/ajax/extrelation.php',
    {
    'network': network,
    'network_uid': network_uid,
    'display_name': display_name,
    'profile_url': profile_url,
    'thumbnail_url': thumbnail_url
    },
    function(data) {
      $(link).parents('div.friendconfig').html(data);
    }
     );
  return false;
}

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
    $(window).scroll(modal_position);
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



function blog_add() {
  // add a new blog

  // we know that the last blog seq number
  // is stored in last_blog variable
  // so we count up one
  // grab the HTML for last blog on form
  var blogs = $('div[id^="blog_"]');
  // update the last_blog var
  last_blog++;

  var newhtml = $(blogs[blogs.length-1]).html();
  newhtml = newhtml.replace(/Blog \d+/gi, 'Blog '+last_blog);
  newhtml = newhtml.replace(/\[\d+\]/g, '['+last_blog+']');
  newhtml = newhtml.replace(/\'\d+\'/g, "'"+last_blog+"'");
  // clone it
  $(blogs[blogs.length-1]).after(
    '<div id=\"blog_'+last_blog+'\">'
    +newhtml
    +'</div>'
    );

  // remove values
  blog_empty('#blog_'+last_blog);
  alert("Blog has been added");
}

function blog_empty(id) {
  // empty all values
  $('input', $(id)).each(
    function(){
      if ($(this).attr('type') == 'text') {
        $(this).attr('value', '');
      }
    }
  );
}

function blog_remove(btn, seq) {
  // ask for confimation
  var check = confirm("Do you want to remove this Blog from your profile?");
  if (check == false) {return false;}
  var feed    = document.getElementById("blog_feed["+ seq +"][value]").value;
  // check if this would be the last visible form

  var blogs = $('div[id^="blog_"]');
  if (blogs.length > 1) {
    // remove the div
    $('#blog_'+seq).remove();
  } else {
    blog_empty('#blog_'+seq);
  }

  if(feed.length > 0) {
    var ajaxurl = "/ajax/delete_user_feeds.php";
    var section = seq;
    $.ajax({
        type: "POST",
        url: ajaxurl,
        data:  {feed_url:feed, section_id: section},
        success: function(data) {
            alert(data);
//          $('#import_results').html(data);
        }
    });
  }
}

var edit_sections = [];

$(document).ready(
  function() {
    // add onChange to form elements to have dirtyBit set
    $('input, select, textarea').change(
      function() {
        // alert("don't forget to save!");
        window.dirtyBit = true;
      }
    );


//------- added by Zoran Hron: import Profile section ------------------

    function htmlEntities(in_txt){
      var i, chr, out_txt = '';
      for(i=0;i<in_txt.length;i++){
        chr = in_txt[i].charCodeAt(0);
        if( (chr > 47 && chr < 58) || (chr > 62 && chr < 127) ){
            out_txt += in_txt[i];
        }else{
            out_txt += "&#" + in_txt[i].charCodeAt(0) + ";";
        }
      }
      return out_txt;
    }

    $("#import_profile").submit( function()
    {
       if ($("#import_profile").validate().form()) {
         return true;
       } else {
         return false;
       }
    });

    $("a[id^='hcard_by_']").click(                    // call hCardXFN parser trough AJAX
      function() {
        var p_mode    = this.getAttribute('id').replace(/hcard_by_/,'');
        var caption   = $(this).text();
        var ajaxurl   = "/ajax/hCardParse.php";
        var p_source;
        if(p_mode == "url") {
           p_source  = $('#input_url').val();
        } else {
           p_source  = htmlEntities($('#input_text').val());
        }
        $('#import_results').html('Loading...');
        $.ajax({
                type: "POST",
                url: ajaxurl,
                data:  {mode: p_mode, source: p_source, current_theme_path: CURRENT_THEME_PATH},
                success: function(data)
                {
                   $('#import_results').html(data);
                }
        });
        return false;
      }
    );

//-----------------------------------------------------------------------------------


    // add "You are leaving this section" alerts
    $("a[id^='show-']").click(
      function() {
        if (window.dirtyBit) {
          // only do the are you sure if there have been changes to the form
          // onChange for form elements set the dirtyBit to ture
          var url = $(this).attr('href');
          var question = "Are you sure you want to leave this Profile section without saving?";
          var check = confirm(question);
          if (check == false) {
            return false;
          }
          document.location.href = url;
        }
      }
    );

    // configure overlays
    $("a[id^='showconfig-']").click(
      function() {
        // get the HTML to display
        var id = this.getAttribute('id').replace(/showconfig-/,'#');
        var caption = $(this).text();
        // clone over the FORM
        // so that submission will actually work
        // we only want the actual FORM, not children
        var newForm = $(id).parents('form').clone(false);
        //
        newForm.html(
          '<fieldset class="center_box ext_service">'
          + '<input type="hidden" name="profile_type" value="external" />'
          + $(id).html()
          + '</fieldset>'
          );
        modal_show(caption, newForm)
        // alert($('#modal_window').html());
        return false;
      }
    );
    $("a[id^='details_']").click(
      function() {
        // get the HTML to display
        var service = this.getAttribute('id').replace(/details_/,'');
        var caption = $(this).text();
        var ajaxurl = "/ajax/showpersonadata.php";
        modal_show(
          "Loading Data",
          "<b>from: " + service  );
        $.get(
          ajaxurl,
          {p: service},
          function(data) {
            modal_show(caption, data); // , 500, 700, true
          }
        );
        return false;
      }
    );

    // are you sure check
    $("a.disconnect").click(
      function(){
        var check = confirm("Do you want to disconnect this service from your profile?");
        if (check == false) {return false;}
        else {
          // POST to the URL
          var url = $(this).html('deleting...').attr('href');
          $.post(url,
            {},
            function() {
              document.location.href = '/myAccount/editProfile?type=external&exttype=connected';
            }
          );
        }
        // don't act if ever we get here
        return false;
      }
    );

    // are you sure check for ID Hub
    $("a.idhub").click(
      function(){
        // set up action type
        var url = $(this).attr('href');
        var action = $(this).html();
        if (! action.match(/manage/i)) {
          var question = "Do you want to " + action + " this login method?";
          if (action.match(/enable/i)) {
            question += "\nYou will then be redirected to the Login Page. Please use the ID system of your choice there to enable it for this account.";
          } else {
            question += "\nPlease make sure you have at least one ID system left to log in.";
          }
          var check = confirm(question);
          if (check == false) {
            return false;
          }
        }
        document.location.href = url;
      }
    );

    // enable AJAX status display
    window.freshen = [];
    $('a.showstatus').click(
      function() {
        var url = $(this).attr('href');
        var id = url.replace(/^.*p=(\d+)$/,'$1');
        $('#statusbox' + id).html('Loading...');
        var ajaxRefreshThis = function () {
          var t = new Date();
          var ts = t.getSeconds() + t.getMilliseconds();
          $.get(
            url + "&d=" + ts,
            {},
            function(data) {
              $('#statusbox' + id)
                .hide()
                // .html(ts + " : " + id + "<hr>" + data)
                .html(data)
                .show('slow');
            }
          );
        }
        window.freshen[id] = window.setInterval(ajaxRefreshThis, 5000);
        var clearThis = function() {
          // alert('done '+id);
          window.clearInterval(window.freshen[id]);
        }
        window.setTimeout(clearThis,20000);
        return false;
      }
    );
    $('a.showstatus').click(); // let's do it once

    // prepare the refresh links
    $('a.refresh').click(
      function() {
        var url = $(this).attr('href');
        var id = url.replace(/^.*p=(\d+)$/,'$1');
        $.post(
          url,
          {},
          function() {
            // click the appropriate Status link
            $('a[href*="p=' + id + '"]').not('.refresh')
            .click();
          }
        );

        return false;
      }
    );
  }
);