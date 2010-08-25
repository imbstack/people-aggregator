var switchStylestyle = function(styleName) {
  $('link[rel*=style][title]').each(function(i) {
	 this.disabled = true;
	 if (this.getAttribute('title') == styleName) this.disabled = false;
  });
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
  $('div[id^="col_"]').not('#col_b').each( // any div who's id starts with 'col_'
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
            if( $('/div',this).is(':hidden') ) {
              if(hid == 'ImagesModule' ) {
               if(document.getElementById('module_tabbed_image_list').style.display == "none") {
                  ser['collapsed'].push(hid);
                }
               }
                else {
                  ser['collapsed'].push(hid);
                  // we want the actual html id here!
                  // as we will be using that on page reload to collapse the HTML
              }

            }
          }
        }
      );
    }
  );

  $.get(
    "/ajax/modulesettings.php",
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
    $('.styleswitcher').click(function() {
      switchStylestyle(this.getAttribute("rel"));
      return false;
    });

    // init sortable modules
    $('div[id^="col_"]').not('#col_b').Sortable(
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
      var m = 'image_'+$(this).parent().attr('id');  // getting the parent ID
      if ($('../div',this).is(':hidden')) {
        document.getElementById(m).src = "/Themes/Default/images/arrow_dn.gif";
        $('../div', this).show('slow',serialize);
      }
      else {
        document.getElementById(m).src = "/Themes/Default/images/arrow_up.gif";
        $('../div', this).hide('slow',serialize);
      }
    }

    );
 // collapse/expand click events
    $('.wide_content > h2').click(
      function() {
        if ($('../div',this).is(':hidden')) {
          document.getElementById('BlockSettingModule').src = "/Themes/Default/images/arrow_dn.gif";
          $('../div', this).show('slow');
        }
        else {
          document.getElementById('BlockSettingModule').src = "/Themes/Default/images/arrow_up.gif";
          $('../div', this).hide('slow');
        }
      }
    );


    // see if we need to collapse any modules
    if (typeof(collapsed) != 'undefined') {
      var l = collapsed.length;
      for (var i = 0; i<l; i++) {
        m = collapsed[i];
        document.getElementById('image_'+m).src = "/Themes/Default/images/arrow_up.gif";
        $('#'+m+'/div').toggle();
      }
    }
  }
);
