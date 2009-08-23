var ajax_urls = new Array();
var ajax_titles = new Array();
var current_theme_path;
var login_click;

function private_module_expand_collapse (header_id, content_id, align, configure_id, is_configurable, block_type) {
  content_block = document.getElementById (content_id);
  main_block = document.getElementById (header_id);
  configure_block = document.getElementById (configure_id);

  if (is_configurable == 1) {//For configurable blocks.
    if (align == 1 || align == 0) {//For left and center alligned blocks.
      configure_image = document.getElementById (configure_id + '_image');
    }
    else {//For right alligned blocks.
      configure_image = document.getElementById (configure_id + '_image');
    }
  }

  if(content_block.style.display == "none") { //alert('hello');
    content_block.style.display = "block";
    if ((block_type == 'ContentBlock') || (block_type == 'UserProfileBlock')) {
        main_block.style.backgroundImage = "url(/Themes/Alpha/images/header-post-blog-open-long.gif)";
    }
    else {
      main_block.style.backgroundImage = "url(/Themes/Alpha/images/header-post-blog-open-long.gif)";
    }

    // Calling ajax function
    title_name = header_id.split('_');

  //  if (ajax_url) {
   //   ajax_call_method(title_name[0], uid, ajax_url);
  //  }

    if (is_configurable == 1) {//For configurable blocks.
      configure_image.style.display = "block";
      if (align == 1) {//For left and center alligned blocks.
        if ((block_type == 'ContentBlock') || (block_type == 'UserProfileBlock')) {
          configure_image.style.backgroundImage = "url(/Themes/Alpha/images/footer-post-expander.gif)";
        }
        else {
          configure_image.style.backgroundImage = "url(/Themes/Alpha/images/footer-expander.gif)";
        }
      }
      else {//For right alligned blocks.
        if ((block_type == 'ContentBlock') || (block_type == 'UserProfileBlock')) {
          configure_image.style.backgroundImage = "url(/Themes/Alpha/images/footer-post-expander.gif)";
        }
        else {
          configure_image.style.backgroundImage = "url(/Themes/Alpha/images/footer-expander.gif)";
        }
      }
    }
  }
  else {
    content_block.style.display = "none";
    if ((block_type == 'ContentBlock') || (block_type == 'UserProfileBlock')) {
        main_block.style.backgroundImage = "url(/Themes/Alpha/images/header-post-blog-closed-long.gif)";
    }
    else {
      main_block.style.backgroundImage = "url(/Themes/Alpha/images/header-post-blog-closed-long.gif)";
    }

    if (is_configurable == 1) {//For configurable blocks.
      configure_block.style.display = "none";
      configure_image.style.display = "none";
    }
  }
}

function public_module_expand_collapse (header_id, content_id) {

  content_block = document.getElementById (content_id);
  main_block = document.getElementById (header_id);
  if(content_block.style.display == "none") {
    content_block.style.display = "block";
     main_block.style.backgroundImage =
      "url(/Themes/Alpha/images/arrow_blu_dn.gif)";
  }
  else {
    content_block.style.display = "none";
     main_block.style.backgroundImage =
      "url(/Themes/Alpha/images/arrow_blu_rt.gif)";
  }
}


function show_hide_configure (configure_id, align, block_type) {
  configure_block = document.getElementById (configure_id);
  if (align == 1) {//For left and center alligned blocks.
    configure_image = document.getElementById (configure_id + '_image');
  }
  else {//For right alligned blocks.
    configure_image = document.getElementById (configure_id + '_image');
  }

  if(configure_block.style.display == "none" || configure_block.style.display == "") {
    configure_block.style.display = "block";
    if ((block_type == 'ContentBlock') || (block_type == 'UserProfileBlock')) {
      configure_image.style.backgroundImage = "url(/Themes/Alpha/images/footer-collapser.gif)";
    }
    else {
      configure_image.style.backgroundImage = "url(/Themes/Alpha/images/footer-collapser.gif)";
    }
  }
  else {
    configure_block.style.display = "none";
    if ((block_type == 'ContentBlock') || (block_type == 'UserProfileBlock')) {
      configure_image.style.backgroundImage = "url(/Themes/Alpha/images/footer-expander.gif)";
    }
    else {
      configure_image.style.backgroundImage = "url(/Themes/Alpha/images/footer-expander.gif)";
    }
  }
}

function close_block (id) {
  block_parent = document.getElementById (id+'_block_parent');
  block_parent.style.display = "none";
}

function change_menu(content_type) {
  id = 'content_menu';
  url = "change_contentmenu.php?type="+content_type;

  // ANOTHER TYPE OF AJAX REQUEST -->
  // var myAjax = new Ajax.Updater(id, url, {method: 'get'});
  $('#'+id).load(url);

  //alert(content_type);
}

function ajax_call_method (event_type_array, uid, ajax_url_array) {
   titles = event_type_array;
   urls = ajax_url_array;

   for (var i=0; i<titles.length; i++) {
    id = titles[i]+'_block_data';
    url = urls[i]+"&uid="+uid;

    // ANOTHER TYPE OF AJAX REQUEST -->
    // var myAjax = new Ajax.Updater(id, url, {method: 'get'});
    // Here $() is the short form of document.getElementById() -->
    // $(id).innerHTML = '<div class="orange-font">loading ...</div>';

    // AJAX jQuery way
    // this is ONE chained expression
// alert(id);
    $('#'+id)
      .html('<div class="orange-font">loading ...</div>')
      .load(url);
  }

}

function ajax_call_method_for_settings (event_type, uid, ajax_url) {

   form_name = 'form_'+event_type;
   var form_data = Form.serialize(form_name);
   url = ajax_url+'&uid='+uid+'&'+form_data;

   id = event_type+'_block_data';

   // ANOTHER TYPE OF AJAX REQUEST -->
   // var myAjax = new Ajax.Updater(id, url, {method: 'get'});
   // $(id).innerHTML = '<div class="orange-font">loading ...</div>';
   $('#'+id).html('<div class="orange-font">loading ...</div>').load(url);
}

function ajax_call_method_for_sorting (event_type, uid, ajax_url, select_id) {
   var sort_by;
   var selected_index = document.getElementById(select_id).selectedIndex;
   sort_by = document.getElementById(select_id).options[selected_index].value;
   id = 'list_'+event_type;
   url = ajax_url+"?sort_by="+sort_by;
   if (trim(uid) != '') {
     url = url+"&uid="+uid;
   }

      $('#'+id).html('<div class="color">loading ...</div>').load(url);
}

function ajax_call_method_for_sorting_group_members (event_type, gid, ajax_url, select_id) {
   var sort_by;
   var selected_index = document.getElementById(select_id).selectedIndex;
   sort_by = document.getElementById(select_id).options[selected_index].value;
   id = 'list_'+event_type;
   url = ajax_url+"?sort_by="+sort_by;
   if (trim(gid) != '') {
     url = url+"&gid="+gid;
   }

      $('#'+id).html('<div class="color">loading ...</div>').load(url);
}


// Home page
function homepage_module_expand_collapse (header_id, content_id, number) {

  content_block = document.getElementById (content_id);
  main_block = document.getElementById (header_id);
  var text_id = 'text-home'+number;
  var button = 'button'+number;
  var country = new Array();
  j = 0;


  for (var i=1; i<=5; i++) {

    var new_string = content_id;
    var header_block = header_id;
    if (number != i) {
      new_string = new_string.replace(number, i);
      header_block = header_block.replace(number, i);

      content_block_open = document.getElementById (new_string);
      var text_id_open = 'text-home'+i;
      var button_open = 'button'+i;
      main_block_open = document.getElementById (header_block);

      main_block_open.style.backgroundImage = "url(/Themes/Alpha/images/bome-page-btn-back.gif)";
      document.getElementById(text_id_open).style.color = 'white';
      document.getElementById(button_open).style.backgroundImage = "url(/Themes/Alpha/images/off_btn.gif)";
      content_block_open.style.display = "none";
    }
  }


  if(content_block.style.display == "none") {
    content_block.style.display = "block";
    main_block.style.backgroundImage = "url(/Themes/Alpha/images/bome-page-on-btn-back.gif)";
    document.getElementById(text_id).style.color = 'black';
    document.getElementById(button).style.backgroundImage = "url(/Themes/Alpha/images/on-light-img.gif)";

  //  if (ajax_url) {
   //   title_name = header_id.split('_');
   //   ajax_call_method(title_name[0], uid, ajax_url);
  //  }
  }
  else {
    content_block.style.display = "none";
    main_block.style.backgroundImage = "url(/Themes/Alpha/images/bome-page-btn-back.gif)";
    document.getElementById(text_id).style.color = 'white';
    document.getElementById(button).style.backgroundImage = "url(/Themes/Alpha/images/off_btn.gif)";
  }
}

function change_color(id, color) {
  document.getElementById(id).style.backgroundColor = color;
  return;
}

// Find Position
function findPosX(obj)
{
  var curleft = 0;
  if (obj.offsetParent)
  {
    while (obj.offsetParent)
    {
      curleft += obj.offsetLeft
      obj = obj.offsetParent;
    }
  }
  else if (obj.x)
    curleft += obj.x;
  return curleft;
}

function findPosY(obj)
{
  var curtop = 0;
  if (obj.offsetParent)
  {
    while (obj.offsetParent)
    {
      curtop += obj.offsetTop
      obj = obj.offsetParent;
    }
  }
  else if (obj.y)
    curtop += obj.y;
  return curtop;
}

// onclick change value and button image
function get_value_forhomepage(type) {
  if (type == 'all') {
    window.location="/home";
  }
  else {
    window.location="/home/post_type="+type;
  }
  //document.getElementById(id).style.backgroundColor = color;
  return;
}

// onclick change value and button image
function get_value_foruserpage(type, uid, base_url, mode) {
  var redirect_url;
  if (type == 'all') {
    if( mode == 'public' ) {
      redirect_url = base_url + '/user/' + uid;
    } else {
      redirect_url = base_url + '/myAccount';
    }
  }
  else {
    if( mode == 'public' ) {
      redirect_url = base_url + '/user/' + uid + '/post_type=' + type;
    } else {
      redirect_url = base_url + '/myAccount' + '/post_type=' + type;
    }
  }
  window.location = redirect_url;
  return;
}

// onclick change value and button image
function get_value_forgrouppage(type,gid) {

   window.location="/group/post_type="+type+"&gid="+gid;

  //document.getElementById(id).style.backgroundColor = color;
  return;
}
// javascript for hompage middle blocks expand collapse
function homepage_middle_module_expand_collapse (header_id, content_id, type) {
  var content_block = document.getElementById (content_id);
  var main_block = document.getElementById(header_id);
  if (content_block.style.display == "block") {
    content_block.style.display = "none";
    main_block.style.backgroundImage =
      "url(/Themes/Alpha/images/"+type+"-bottom-back.gif)";
  } else if (content_block.className.indexOf("display") == -1) {
    // the class 'display' has not been set, so this is visible
    content_block.className += " display";
    main_block.style.backgroundImage =
      "url(/Themes/Alpha/images/"+type+"-bottom-back.gif)";
  } else {
    content_block.style.display = "block";
    main_block.style.backgroundImage =
      "url(/Themes/Alpha/images/"+type+"-bottom.gif)";
  }

}

// javascript for hompage side blocks expand collapse
function homepage_side_module_expand_collapse (header_id, content_id) {
  content_block = document.getElementById (content_id);
  main_block = document.getElementById (header_id);
  if(content_block.style.display == "none") {
    content_block.style.display = "block";
    main_block.style.backgroundImage =
      "url(/Themes/Alpha/images/arrow_blu_dn.gif)";
    //main_block.className = "public-module-block-header drag1";
  }
  else {
    content_block.style.display = "none";
    main_block.style.backgroundImage =
      "url(/Themes/Alpha/images/arrow_blu_rt.gif)";
    //main_block.className = "public-module-block-header-closed drag1";
  }
}

function show_user_info (content_id) {
  content_block = document.getElementById (content_id);
  if (content_id == 'general-info') {
    content_block.style.display = "block";
    document.getElementById('personal-info').style.display = "none";
    document.getElementById('professional-info').style.display = "none";
    document.getElementById('general').style.color = 'black';
    document.getElementById('personal').style.color = '#7699c1';
    document.getElementById('professional').style.color = '#7699c1';
    document.getElementById('general').style.backgroundColor = '#999';
    document.getElementById('personal').style.backgroundColor = '#000000';
    document.getElementById('professional').style.backgroundColor = '#000000';
    document.getElementById('general').style.backgroundImage = "url(/Themes/Alpha/images/tab-on-dot.gif)";
    document.getElementById('personal').style.backgroundImage = "url(/Themes/Alpha/images/tab-off-dot.gif)";
    document.getElementById('professional').style.backgroundImage = "url(/Themes/Alpha/images/tab-off-dot.gif)";
  }
  else if (content_id == 'personal-info') {
    content_block.style.display = "block";
    document.getElementById('general-info').style.display = "none";
    document.getElementById('professional-info').style.display = "none";
    document.getElementById('general').style.color = '#7699c1';
    document.getElementById('personal').style.color = 'black';
    document.getElementById('professional').style.color = '#7699c1';
    document.getElementById('general').style.backgroundColor = '#000000';
    document.getElementById('personal').style.backgroundColor = '#999';
    document.getElementById('professional').style.backgroundColor = '#000000';
    document.getElementById('general').style.backgroundImage = "url(/Themes/Alpha/images/tab-off-dot.gif)";
    document.getElementById('personal').style.backgroundImage = "url(/Themes/Alpha/images/tab-on-dot.gif)";
    document.getElementById('professional').style.backgroundImage = "url(/Themes/Alpha/images/tab-off-dot.gif)";
  }
  else if (content_id == 'professional-info') {
    content_block.style.display = "block";
    document.getElementById('personal-info').style.display = "none";
    document.getElementById('general-info').style.display = "none";

    document.getElementById('general').style.color = '#7699c1';
    document.getElementById('personal').style.color = '#7699c1';
    document.getElementById('professional').style.color = 'black';
    document.getElementById('general').style.backgroundColor = '#000000';
    document.getElementById('personal').style.backgroundColor = '#000000';
    document.getElementById('professional').style.backgroundColor = '#999';
    document.getElementById('general').style.backgroundImage = "url(/Themes/Alpha/images/tab-off-dot.gif)";
    document.getElementById('personal').style.backgroundImage = "url(/Themes/Alpha/images/tab-off-dot.gif)";
    document.getElementById('professional').style.backgroundImage = "url(/Themes/Alpha/images/tab-on-dot.gif)";
  }
}

function go_to(page, uid) {
  if (page == 'create_post') {
    window.location="choose_content.php?uid="+uid;
  }
  else if (page == 'manage_content') {
    window.location="content_management.php?tier_one=management&uid="+uid;
  }
  else if (page == 'add_image') {
    window.location="gallery_image.php?tier_one=private&tier_two=private2&tier_three=pr2three1&uid="+uid;
  }
  else if (page == 'refresh') {
    window.location="";
  }
  else if (page == 'messages') {
    window.location="/myAccount/messages/folder_name=Inbox&page_no=1";
  }
  else if (page == 'invite') {
    window.location="/invitation";
  }
  else if (page == 'select-friend') {
    var friend_id = document.getElementById(page).options[document.getElementById(page).selectedIndex].value;
    window.location="/media/gallery/Images/uid=" + friend_id + "&view=friends";
  }
  else if (page == 'select-group') {
    var group_id = document.getElementById(page).options[document.getElementById(page).selectedIndex].value;
    window.location="/media/gallery/Images/gid=" + group_id + "&view=groups_media";
  }
}


function private_module_center_expand_collapse (header_id, content_id, align, configure_id, is_configurable, block_type) {
  content_block = document.getElementById (content_id);
  main_block = document.getElementById (header_id);
  configure_block = document.getElementById (configure_id);

  if (is_configurable == 1) {//For configurable blocks.
    if (align == 1 || align == 0) {//For left and center alligned blocks.
      configure_image = document.getElementById (configure_id + '_image');
    }
    else {//For right alligned blocks.
      configure_image = document.getElementById (configure_id + '_image');
    }
  }

  if(content_block.style.display == "none") {//alert('hello');
    content_block.style.display = "block";
    if (block_type == 'ContentBlock') {
      main_block.style.backgroundImage = "url(/Themes/Alpha/images/header-post-blog-open.gif)";
    }
    else {
      main_block.style.backgroundImage = "url(/Themes/Alpha/images/header-post-blog-closed.gif)";
    }

    // Calling ajax function
    title_name = header_id.split('_');

  //  if (ajax_url) {
   //   ajax_call_method(title_name[0], uid, ajax_url);
  //  }

    if (is_configurable == 1) {//For configurable blocks.
      configure_image.style.display = "block";
      if (align == 1) {//For left and center alligned blocks.
        if (block_type == 'ContentBlock') {
          configure_image.style.backgroundImage = "url(/Themes/Alpha/images/footer-post-expander.gif)";
        }
        else {alert('hello');
          configure_image.style.backgroundImage = "url(/Themes/Alpha/images/footer-expander.gif)";
        }
      }
      else {//For right alligned blocks.
        if (block_type == 'ContentBlock') {
          configure_image.style.backgroundImage = "url(/Themes/Alpha/images/footer-post-expander.gif)";
        }
        else {
          configure_image.style.backgroundImage = "url(/Themes/Alpha/images/footer-expander.gif)";
        }
      }
    }
  }
  else {
    content_block.style.display = "none";
    if (block_type == 'ContentBlock') {
      main_block.style.backgroundImage = "url(/Themes/Alpha/images/header-post-blog-closed.gif)";
    }
    else {
      main_block.style.backgroundImage = "url(/Themes/Alpha/images/block-header-closed.gif)";
    }

    if (is_configurable == 1) {//For configurable blocks.
      configure_block.style.display = "none";
      configure_image.style.display = "none";
    }
  }
}


function search_user() {
  var field = new Array('first_name', 'last_name', 'sex', 'city', 'state', 'company', 'industry');
  var blank = 0;

  for (var k=0; k<field.length; k++) {
    var n = document.getElementById(field[k]);
    if (field[k] == 'sex') {
      if (document.myform_search.sex[0].checked) {
        n1 = 'male';
      }
      else if (document.myform_search.sex[1].checked) {
        n1 = 'female';
      }
      else if (document.myform_search.sex[2].checked) {
        n1 = 'all';
      }

    }
    else {
      n1 = trim(n.value);
    }

    if (n1 == '') {
      blank = blank+1;
    }
  }

  if (blank == (field.length)) {
    alert("Please enter a search string.");
    return false;
  }
  else {
    return true;
  }

}

function sort_by( display ) {
  var block = new Array('latest_registered', 'alphabetical');
  for( cnt = 0; cnt < block.length; cnt++ ) {
    if( display ==  block[cnt]) {
      document.getElementById(block[cnt]).style.display = "block";
    }
    else {
      document.getElementById(block[cnt]).style.display = "none";
    }
  }
}


function validate_form() {
  var val1 = document.getElementById('groupname').value;
  val1 = trim(val1);
  var val2 = document.getElementById('group_category').options[document.getElementById('group_category').selectedIndex].value;

  if (val1=='') {
    alert('Please fill the name of the group');
    return false;
  }

  if (val2==0)
  {
    alert('Please select the category');
    return false;
  }



}

function isValidEmail(strEmail) {
  validRegExp = /^[^@]+@[^@]+.[a-z]{2,}$/i;
  if(strEmail.search(validRegExp) == -1) {
    return false;
  }
  return true;
}


function trim(field) {
  value = field;
  while (value.charAt(value.length-1) == " ") {
    value = value.substring(0,value.length-1);
  }
  while(value.substring(0,1) ==" ") {
    value = value.substring(1,value.length);
  }
  return value;


}

function check_comment() {
  var error = false;
  var n = document.getElementById("name");
  var c = document.getElementById("comment-text");

  n1 = trim(n.value);
  n2 = trim(c.value);



  if (n1 == ''){
    alert("Please fill your name.");
    return false;
  }
  else if (n2 == '') {
    alert("You can not post empty comment.");
    return false;
  }



}

function check_topic() {
  var error = false;
  var n = document.getElementById("title");
  var c = document.getElementById("body");

  n1 = trim(n.value);
  n2 = trim(c.value);



  if (n1 == ''){
    alert("Please fill title.");
    return false;
  }
  else if (n2 == '') {
    alert("Please fill contents.");
    return false;
  }



}

function show_media_gallery_info (content_id) {
  content_block = document.getElementById (content_id);
  if (content_id == 'gallery-images') {
    content_block.style.display = "block";
    document.getElementById('gallery-videos').style.display = "none";
    document.getElementById('gallery-audios').style.display = "none";
    document.getElementById('images').style.color = 'black';
    document.getElementById('videos').style.color = '#fff';
    document.getElementById('audios').style.color = '#fff';
    document.getElementById('images').style.backgroundColor = '#EEEEEE';
    document.getElementById('videos').style.backgroundColor = '#000';
    document.getElementById('audios').style.backgroundColor = '#000';
    document.getElementById('images').style.backgroundImage = "url(/Themes/Alpha/images/tab-on-dot.gif)";
    document.getElementById('videos').style.backgroundImage = "url(/Themes/Alpha/images/tab-off-dot.gif)";
    document.getElementById('audios').style.backgroundImage = "url(/Themes/Alpha/images/tab-off-dot.gif)";
  }
  else if (content_id == 'gallery-videos') {
    content_block.style.display = "block";
    document.getElementById('gallery-images').style.display = "none";
    document.getElementById('gallery-audios').style.display = "none";
    document.getElementById('images').style.color = '#fff';
    document.getElementById('videos').style.color = 'black';
    document.getElementById('audios').style.color = '#fff';
    document.getElementById('images').style.backgroundColor = '#000';
    document.getElementById('videos').style.backgroundColor = '#EEEEEE';
    document.getElementById('audios').style.backgroundColor = '#000';
    document.getElementById('images').style.backgroundImage = "url(/Themes/Alpha/images/tab-off-dot.gif)";
    document.getElementById('videos').style.backgroundImage = "url(/Themes/Alpha/images/tab-on-dot.gif)";
    document.getElementById('audios').style.backgroundImage = "url(/Themes/Alpha/images/tab-off-dot.gif)";
  }
  else if (content_id == 'gallery-audios') {
    content_block.style.display = "block";
    document.getElementById('gallery-videos').style.display = "none";
    document.getElementById('gallery-images').style.display = "none";

    document.getElementById('images').style.color = '#fff';
    document.getElementById('videos').style.color = '#fff';
    document.getElementById('audios').style.color = 'black';
    document.getElementById('images').style.backgroundColor = '#000';
    document.getElementById('videos').style.backgroundColor = '#000';
    document.getElementById('audios').style.backgroundColor = '#EEEEEE';
    document.getElementById('images').style.backgroundImage = "url(/Themes/Alpha/images/tab-off-dot.gif)";
    document.getElementById('videos').style.backgroundImage = "url(/Themes/Alpha/images/tab-off-dot.gif)";
    document.getElementById('audios').style.backgroundImage = "url(/Themes/Alpha/images/tab-on-dot.gif)";
  }
}


function show_content (content_id, page_links) {
  //array('all_content', 'blogs', 'events', 'reviews', 'person', 'group', 'image', 'audio', 'video');
  var con = new Array('all_content', 'blogs', 'events', 'reviews', 'person', 'group', 'image', 'audio', 'video');
  var content_new = new Array();
  var content_new1 = new Array();
  var j = 0;
  content_block = document.getElementById (content_id);
  for (var i=0; i<con.length; i++) {
    main_id = con[i]+'-content';
    if (main_id != content_id) {
      content_new[j] = main_id;
      content_new1[j] = con[i];
      j++;
    }
  }
  title_name = content_id.split('-');
  var hidden_value = title_name[0]+'-delete';

  content_block.style.display = "block";
  for (var k=0; k<content_new.length; k++) {
    document.getElementById(content_new[k]).style.display = "none";
  }

  //color: #7699C1;
  document.getElementById(title_name[0]).style.color = 'black';
  document.getElementById(title_name[0]).style.backgroundColor = '#fff';
  document.getElementById(title_name[0]).style.backgroundImage = "url(/Themes/Alpha/images/tab-on-dot.gif)";
  document.getElementById('delete_type').value = title_name[0];
  document.getElementById('paging').innerHTML = page_links;


  for (var l=0; l<content_new1.length; l++) {
    document.getElementById(content_new1[l]).style.color = '#7699c1';
    document.getElementById(content_new1[l]).style.backgroundColor = '#000';
    document.getElementById(content_new1[l]).style.backgroundImage = "url(/Themes/Alpha/images/tab-off-dot.gif)";
  }
}


// for deleting images

function delete_content1() {
  if (confirm('Are you sure you want to delete this content ?')) {
    return true;
  }
  else {
    return false;
  }
}
function confirm_delete(msg) {
  var msg = msg;
  if (confirm(msg)) {
    return true;
  }
  else {
    return false;
  }
}

function delete_content(con_type) {

  if (con_type == 'image') {
    var len = document.image_upload.elements.length;
  }
  else if (con_type == 'audio') {
    var len = document.image_upload1.elements.length;
  }
  else if (con_type == 'video') {
    var len = document.image_upload2.elements.length;
  }

  isChecked = 0;
  isValid = 0;
  j = 0;
  for(var i=0;i<len; i++) {
    if (con_type == 'image') {
      currentField = document.image_upload.elements[i];
    }
    else if (con_type == 'audio') {
      currentField = document.image_upload1.elements[i];
    }
    else if (con_type == 'video') {
      currentField = document.image_upload2.elements[i];
    }

    if(currentField.type == "checkbox") {
        if(currentField.checked == true) { isValid = 1; isChecked = 1;}
    }
  }
  if(isChecked == 0) {
    isValid = 0;

    if (con_type == 'image') {
      alert("No image has been selected.");
      return false;
    }
    else if (con_type == 'audio') {
      alert("No audio has been selected.");
      return false;
    }
    else if (con_type == 'video') {
      alert("No video has been selected.");
      return false;
    }
  }

  if (isValid != 0) {
    if (confirm("Are you sure you want to delete selected "+con_type+" ?")) {
      return true;
    }
    else {
      return false;
    }
  }
}
function delete_media_content(frm,link_delete) {
  var all;
  all = frm.elements.length;
  //var checked_boxes = 0;
  //for(counter=0; counter < all; counter++) {
   // if(frm.elements[counter].type == "checkbox" && frm.elements[counter].checked == true) {
     //   checked_boxes = checked_boxes + 1;
   // }
  //}

  //if(checked_boxes > 0) {
      if (confirm("Are you sure you want to delete selected  content")) {
      window.location=link_delete;
      return true;
      }
      else {
        return false;
      }

  //} else {
   //   alert('Please select the Content for Deletion');
    //  return false;
  //}

}



function open_album(show_id) {
  var on_show;
  on_show = show_id+'_show_hide';
  content_block = document.getElementById (show_id);
  if(content_block.style.display == "none") {
    content_block.style.display = "block";
    document.getElementById (on_show).style.backgroundImage = "url(/Themes/Alpha/images/down-arrow.gif)";
  }
  else {
    content_block.style.display = "none";
    document.getElementById (on_show).style.backgroundImage = "url(/Themes/Alpha/images/up-arrow-black.gif)";
  }
}



//message related functions
function message_markall(message_id_string) {
  var data = String(message_id_string).split(',');
  var cb1 = document.getElementById('Checkbox1');
  if(cb1.checked) {
    for(i=0;i<data.length-1;i++) {
    var el = document.getElementById(data[i]);
    el.checked=true;
    }
  }
  else {
    for(i=0;i<data.length-1;i++) {
    var el = document.getElementById(data[i]);
    el.checked=false
    }
  }
}

function message_get_folder_name(form_name) {
  var form_obj = document.forms[form_name];
   var answer = prompt ("Please enter a name for your folder.","")
    if(trim(answer) != "") {
      form_obj.new_folder.value = answer;
      form_obj.submit();
    }
}

function goto_folder(base_url) {

  var folder = document.getElementById('sel_folder');
  if (folder.value != 'select folder') {
    window.location=base_url+'/myAccount/messages/folder='+folder.value;
  }

}

function message_search() {

var s = document.getElementById("search_text");
  if (s.value) {
    window.location="/myAccount/messages/action=search&page_no=1&search_sort=sent_time&search_flag=0&name="+ s.value;
  }
  else {
    alert("Please enter the search string");
  }
}

function action_decide(message) {
  switch (message) {
    case 'delete':
      document.forms['message_form'].action = "deletemessage.php?action=delete";
      break;
    case 'move':
      document.forms['message_form'].action = "deletemessage.php?action=move";
      break;
    case 'reply':
      document.forms['message_form'].action = "/myAccount/newMessage";
      document.forms['message_form'].do_action.value = 'reply';
      break;
    case 'forward':
      document.forms['message_form'].action = "/myAccount/newMessage";
      document.forms['message_form'].do_action.value = 'forward';
      break;
  }
  document.forms['message_form'].submit();
}
//End message related functions.

// start -- for media gallery
function show_hide(display_on, display_off1, display_off2) {
  document.getElementById (display_off1).style.display = "none";
  document.getElementById (display_off2).style.display = "none";

  /*background-color:#EEEEEE;*/
  document.getElementById (display_on).style.display = "block";
  var num = display_on.length;

  var on = display_on.split('-');
  var off1 = display_off1.split('-');
  var off2 = display_off2.split('-');

  var on_show = on[0]+on[1];
  var off1_show = off1[0]+off1[1];
  var off2_show = off2[0]+off2[1];

  document.getElementById (on_show).style.backgroundImage = "url(/Themes/Alpha/images/tab-on-dot.gif)";
  document.getElementById (on_show).style.backgroundColor = "#fff";
  document.getElementById (on_show).style.color = "black";

  document.getElementById (off1_show).style.backgroundImage = "url(/Themes/Alpha/images/tab-off-dot.gif)";
  document.getElementById (off1_show).style.backgroundColor = "#000";
  document.getElementById (off1_show).style.color = "#7699c1";

  document.getElementById (off2_show).style.backgroundImage = "url(/Themes/Alpha/images/tab-off-dot.gif)";
  document.getElementById (off2_show).style.backgroundColor = "#000";
  document.getElementById (off2_show).style.color = "#7699c1";
  //background-color: #323532;
  //color: #7699C1;
}

// end -- for media gallery

// start -- for edit profile

function funcCheckAll(frm) {
    var n = frm.elements.length;
    if(frm.check_all.value == 0) {
      for(i=0; i< n; i++) {
          frm.elements[i].checked = true;
      }
      frm.check_all.value = 1;
    } else {
      for(i=0; i< n; i++) {
          frm.elements[i].checked = false;
      }
      frm.check_all.value = 0;
    }
}

function clear_textbox(div_id, log_click) {

  if (log_click == 'true') {
    login_click = true;
  }
  text = document.getElementById(div_id).value;
  if((div_id == 'edit-name' && text == 'username') ||
     (div_id == 'edit-pass' && text == 'password')) {
    document.getElementById(div_id).value = '';
  }
}

function fill_textbox(div_id) {
  text = document.getElementById(div_id).value;

  if (login_click == true) {
    //alert(text);
    if(text == '') {
      if(div_id == 'edit-name') {
        document.getElementById(div_id).value = '';
      }
      if(div_id == 'edit-pass') {
        document.getElementById(div_id).value = '';
      }
    }
  }
  else {
    if(text == '') {
      if(div_id == 'edit-name') {
        document.getElementById(div_id).value = 'username';
      }
      if(div_id == 'edit-pass') {
        document.getElementById(div_id).value = 'password';
      }
    }
  }
}

/* Script to validate the checkbox for terms and condition in user registration*/

function validateForm12(frm) {
    if(frm.chkbox_agree.checked == false) {
      alert('You must agree to Terms and Conditions before registering to PeopleAggregator');
      return false;
    }

}

/*  Funtion to validate to and from date on Search Content form */
function validateDate(frm) {
  if(frm.yTo.value > frm.yFrom.value) {
    return true;
  }
  else if(frm.yTo.value == frm.yFrom.value) {
      if(frm.mTo.value > frm.mFrom.value) {
          return true;
      }
      else if(frm.mTo.value == frm.mFrom.value) {
          if(frm.dTo.value >= frm.dFrom.value) {
              return true;
          }
          else {
              alert('Please enter valid day range');
              return false;
          }
      }
      else {
          alert('Please enter valid month range');
          return false;
      }
  }
  else {
    alert('Please enter valid year range');
    return false;
  }
}

function check_empty(id_title,id_body) {

  if(document.getElementById(id_title).value =='')
  {
    alert('Please fill title');
    return false;
  }
  if(document.getElementById(id_body).value =='')
  {
    alert('Please fill contents');
    return false;
  }
}


function show_sb_block (block_id) {
  if(block_id == 'SB_EVENT') {
    obj = document.getElementById(block_id);
    if(obj.style.display == "none") {
      obj.style.display = "block";
      document.getElementById('SB_REVIEW').style.display = "none";
    } else {
      obj.style.display = "none";
      document.getElementById('SB_REVIEW').style.display = "none";
    }
  }
  if(block_id == 'SB_REVIEW') {
    obj = document.getElementById(block_id);
    if(obj.style.display == "none") {
      obj.style.display = "block";
      document.getElementById('SB_EVENT').style.display = "none";
    } else {
      obj.style.display = "none";
      document.getElementById('SB_EVENT').style.display = "none";
    }
  }
  return;
}

function change_icon (image_name, image_src, color) {
    document[image_name].src = image_src;
    obj = document.getElementById('dynamic_bg');
    obj.style.background = color;
}

var total = 1;
function addElements(identity) {
  if (total < 5) {
    mydiv = document.createElement('div');
     data = '<input type="hidden" name="included[' + total + ']" value="on" />';
     data += '<hr /> <div class="field_medium">';
     data += '<h4>';
     data += '<label for="firstname"> First Name</label>';
     data += '</h4>';
     data +='<input type="text" class="text longer" name="firstname[' + total + ']" />';
     data +='</div>';

     data += '<div class="field_medium">';
     data += '<h4>';
     data += '<label for="lastname"> Last Name</label>';
     data += '</h4>';
     data +='<input type="text" class="text longer" name="lastname[' + total + ']" />';
     data +='</div>';

     data += '<div class="field_medium">';
     data += '<h4>';
     data += '<label for="email"><span class="required"> * </span>Email or Login name</label>';
     data += '</h4>';
     data +='<input type="text" class="text longer" name="email[' + total + ']" />';
     data +='</div>';

    mydiv.innerHTML = data;
    document.getElementById(identity).appendChild(mydiv);
    total++;
  }
  if (total == 5){
    document.getElementById("addmore_button").style.display = "none"
  }
}

// for media uploading

var total = 1;
function addmedia(identity,page_id) {

if (total < 5) {
  var setect_box = get_media_access('image_perm['+total+']');
  mydiv = document.createElement('div');
  var divIdName = 'my'+total+'Image';
  mydiv.setAttribute('id',divIdName);

    data ='<div class="field_medium start"><h5><label for="select file">Select a file to upload: or enter a URL below</label></h5>';
    data += '<input name="userfile_' + total + '" type="file" id="select_file" class="text long" value="" /></div>';

    if (page_id == 'media_gallery') {
      data += '<div class="field"><h5><label for="file url">Enter the URL of image</label></h5>';
      data += '<input name="userfile_url_'+ total +'" class="text long" id="file_url" type="text" value="" /></div>';
    }

    data += '<div class="field"><h5><label for="image title">Image title</label></h5>';
    data += '<input type="text" name="caption[' + total + ']" value="" class="text long" id="image_title"  /></div>';

    data += '<div class="field_big"><h5><label for="description">Description:</label></h5>';
    data += '<textarea id="description" name="body[' + total + ']" rows="3" cols="28"></textarea></div>';

    data += '<div class="field_medium"><h5><label for="tags">Tags (Separete tags with commas):</label></h4>';

    data += '<input type="text" name="tags[' + total + ']" class="text long" id="tag" value="" maxlength="255" /></div>';
   if (page_id == 'media_gallery') {

     data +=' <div class="field_medium end"><div class="center"><h5><label for="select image">Select who can see this image:</label></h5>';
     data += setect_box ;
   }
   data += '</div>';

   mydiv.innerHTML = data;
   document.getElementById(identity).appendChild(mydiv);
   total++;
 }
 if (total == 5){
   document.getElementById("addmore_button").className = "display_false"
 }
}

var total_audio= 1;

function addaudiomedia(identity,page_id) {

 if (total_audio < 5) {
  var setect_box = get_media_access('audio_perm['+total+']');
  mydiv = document.createElement('div');
  var divIdName = 'my'+total+'Audio';
  mydiv.setAttribute('id',divIdName);

    data ='<div class="field_medium start"><h5><label for="select file">Select a file to upload: or enter a URL below</label></h5>';
    data += '<input name="userfile_audio_' + total_audio + '" type="file" id="select_file" class="text long" value="" /></div>';

    if (page_id == 'media_audio') {
      data += '<div class="field"><h5><label for="file url">URL of audio</label></h5>';
      data += '<input name="userfile_audio_url_'+ total_audio +'" class="text long" id="file_url" type="text" value="" /></div>';
    }

    data += '<div class="field"><h5><label for="image title">Audio Title</label></h5>';
    data += '<input type="text" name="caption_audio[' + total_audio + ']" value="" class="text long" id="image_title"  /></div>';

    data += '<div class="field_big"><h5><label for="description">Description:</label></h5>';
    data += '<textarea id="description" name="body_audio[' + total_audio + ']" rows="3" cols="28"></textarea></div>';

    data += '<div class="field_medium"><h5><label for="tags">Tags (Separete tags with commas):</label></h5>';

    data += '<input type="text" name="tags_audio[' + total_audio + ']" class="text long" id="tag" value="" maxlength="255" /></div>';

    if (page_id == 'media_audio') {
      data +=' <div class="field_medium end"><div class="center"><h5><label for="select image">Select who can see this audio:</label></h5>';
      data += setect_box;
    }
    data += '</div>';
   mydiv.innerHTML = data;
   document.getElementById(identity).appendChild(mydiv);
   total_audio++;
 }

 if (total_audio == 5){
   document.getElementById("addmore_audiobutton").className = "display_false"
 }
}

var total_video = 1;

function addvideomedia(identity,page_id) {
 if (total_video < 5) {
   var setect_box = get_media_access('video_perm['+total+']');
   mydiv = document.createElement('div');
   var divIdName = 'my'+total+'Video';
   mydiv.setAttribute('id',divIdName);

    data =' <div class="field_medium start"><h5><label for="select file">Select a file to upload: or enter a URL below</label></h5>';
    data += '<input name="userfile_video_' + total_video + '" type="file" id="select_file" class="text long" value="" /></div>';

    if (page_id == 'media_video') {
      data += '<div class="field"><h5><label for="file url">URL of video</label></h5>';
      data += '<input name="userfile_video_url_'+ total_video +'" class="text long" id="file_url" type="text" value="" /></div>';
    }

    data += '<div class="field"><h5><label for="image title">Video Title</label></h5>';
    data += '<input type="text" name="caption_video[' + total_video + ']" value="" class="text long" id="image_title"  /></div>';

    data += '<div class="field_big"><h5><label for="description">Description:</label></h5>';
    data += '<textarea id="description" name="body_video[' + total_video + ']" rows="3" cols="28"></textarea></div>';

    data += '<div class="field_medium"><h5><label for="tags">Tags (Separete tags with commas):</label></h5>';

    data += '<input type="text" name="tags_video[' + total_video + ']" class="text long" id="tag" value="" maxlength="255" /></div>';
   if (page_id == 'media_video') {
     data +=' <div class="field_medium end"><div class="center"><h5><label for="select image">Select who can see this audio:</label></h5>';
     data += setect_box ;
   }
   data +='</div>';
   mydiv.innerHTML = data;
   document.getElementById(identity).appendChild(mydiv);
   total_video++;
 }

 if (total_video == 5){
   document.getElementById("addmore_videobutton").className = "display_false"
 }
}

function textCounter(field, countfield, maxlimit) {

  if (field.value.length > maxlimit) {
    // if too long...trim it!
    field.value = field.value.substring(0, maxlimit);
  }
  // otherwise, update 'characters left' counter
  else {
    countfield.value = maxlimit - field.value.length;
  }
}


function change_edit_profile_perm(select_box_id)
{
  if (select_box_id == 'select_gen') {
    var field = new Array('dob', 'sex', 'state', 'city', 'country', 'user_caption', 'sub_caption', 'user_tags', 'postal_code');
  }
  else if (select_box_id == 'select_per') {
    var field = new Array('ethnicity', 'religion', 'political_view', 'passion', 'activities', 'books', 'movies', 'music', 'tv_shows', 'cusines');
  }
  else if (select_box_id == 'select_pro') {
     var field = new Array('headline', 'industry', 'company', 'title', 'website', 'career_skill', 'prior_company', 'prior_company_city', 'prior_company_title', 'college_name', 'degree', 'summary', 'languages', 'awards', 'user_cv');
  }

  var select_id = document.getElementById(select_box_id).value;

    for (var k=0; k<field.length; k++) {
      var id = field[k]+'_perm';
      try {
        document.getElementById(id).value = select_id;
      } catch(e) {
        //
      }
    }
}
function other_state(select_box_id)
{
    var select_id = document.getElementById(select_box_id).value;
    if(select_id == 'Other') {
      document.getElementById('other_state_div').style.display="block";
      document.getElementById('other_state_text').value="";
    }
    else  {
       document.getElementById('other_state_div').style.display="none";
    }

}

function ajax_check_network_availability (element_id, network_url_id) {
    obj = document.getElementById(element_id);
    var check_name = document.getElementById(network_url_id).value;
    if(check_name == "") {obj.innerHTML = '<span class="required">Please enter the network address</span>'; return false;}
    var ajax_url = 'check_availability.php?check_address='+check_name;
    // var myAjax = new Ajax.Updater('availability', ajax_url, {method: 'get'});
    $('#availability').load(ajax_url);
}

function div_expand_collapse (div_id, edit) {
    element = document.getElementById(div_id);
    if(element.style.display == "none" || element.className == "display_false") {

      if(edit == 1) {
          if(document.getElementById('link_categories').value == 0) {
            alert('Please select a list');
            return false;
          } else {

              tmp_array = document.getElementById('link_categories').value.split(':');
              document.getElementById('category_name').value = tmp_array[1];
              document.getElementById('form_action').value = "update";
          }

      } else {
            document.getElementById('category_name').value = "";
            document.getElementById('form_action').value = "addnew";
      }
      element.style.display = "block";
      element.className = "display_true";

    } else {
        element.style.display = "none";
        element.className = "display_false";
    }
}

function ajax_category_links (element_id, category_field) {
    //tmp_array =  category_field.split(':');
    var ajax_url = 'ajax_links.php?category_id='+category_field;
    // var myAjax = new Ajax.Updater(element_id, ajax_url, {method: 'get'});
    // document.getElementById(element_id).innerHTML = '<div class="orange-font">loading ...</div>';
    $('#'+element_id).html('<div class="required">loading ...</div>').load(ajax_url);
    try {
      $('#edit_delete_list_btn').addClass("display_true");
      document.getElementById("edit_list").innerHTML = '';
      document.getElementById("edit_list_links").innerHTML = '';
      document.getElementById("error_message").innerHTML = '';
      document.getElementById("error_message_links").innerHTML = '';
    } catch(msg) {

    }
}

function ajax_category_default_links (element_id, category_field) {
    tmp_array =  category_field.split(':');
    var ajax_url = 'ajax_default_links.php?category_id='+tmp_array[0];
    // var myAjax = new Ajax.Updater(element_id, ajax_url, {method: 'get'});
    // document.getElementById(element_id).innerHTML = '<div class="orange-font">loading ...</div>';
    $('#'+element_id).html('<div class="required">loading ...</div>').load(ajax_url);
}

function add_link_expand_collapse (div_id) {
    element = document.getElementById(div_id);
    if(element.style.display == "none") {
        element.style.display = "block";
        document.getElementById('title').value = '';
        document.getElementById('url').value = 'http://';
        document.getElementById('form_action').value = "addnew";
        //document.getElementById('btn_save_link').value = "Apply Name";
    } else {
        element.style.display = "none";
    }
}

function delete_links (delete_type) {
    form_obj = document.formLinkManagement;
    if(delete_type == 1) {
        if(document.getElementById('link_categories').value == 0) {
            alert('Please select a list');
            return false;
          } else {
              if(confirm_delete('Are you sure you want to delete the category?')) {
                  document.getElementById('form_action').value = "delete_category";
                  form_obj.submit();
                  return true;
              } else {
                  return false;
              }
          }
    }
    else {

        var n = form_obj.elements.length;
        for(counter = 0; counter < n; counter++) {
            if(form_obj.elements[counter].type == "checkbox") {
                if(form_obj.elements[counter].checked == true) {
                    if(confirm_delete('Are you sure you want to delete the selected links?')) {
                        document.getElementById('form_action').value = "delete_links";
                        form_obj.submit();
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }
        alert('Please select the links to delete.');
        return false;
    }
}

function edit_links (div_id) {
    form_obj = document.formLinkManagement;
    element = document.getElementById(div_id);
    var n = form_obj.elements.length;
    //alert(n);
    var checked_boxes = 0;
    for(counter = 0; counter < n; counter++) {
        if(form_obj.elements[counter].type == "checkbox") {
            if(form_obj.elements[counter].checked == true) {
                checked_boxes =  checked_boxes + 1;
                if(checked_boxes > 1) {
                    alert('Please select only one link for editing.');
                    document.getElementById('form_action').value = "update";
                    return false;
                }
                checked_id = form_obj.elements[counter].id;
            }
        }
    }
//alert(checked_boxes)
    if(checked_boxes == 1) {
        /*if(element.style.display == "none") {
            element.style.display = "block";
        } else if(element.style.display == "block") {
            element.style.display = "block";
        } else {
            element.style.display = "none";
        }*/
        element.style.display = "block";
        url_field = checked_id+'_url';
        url_obj = document.getElementById(url_field);
        document.getElementById('url').value = url_obj.value;

        title_field = checked_id+'_title';
        title_obj = document.getElementById(title_field);
        document.getElementById('title').value = title_obj.value;
        document.getElementById('form_action').value = "update";
        //document.getElementById('btn_save_link').value = "Save";

    } else {
        document.getElementById('title').value = "";
        document.getElementById('url').value = "";
        alert('Please select a link for editing.');
        element.style.display = "none";
    }
}


function link_expand_collapse (caption, element_no, total) {

    id_selected = caption+'_'+element_no;

    for(counter = 0; counter < total; counter++) {
        id = caption+'_'+counter;
        element = document.getElementById(id);
        main_block_id = id+'_main';
        main_block = document.getElementById(main_block_id);

        image_name = 'links_bulb_'+counter;

        if(id_selected == id) {
            if(element.style.display == "block") {
                element.style.display = "none";
                document[image_name].src = "/Themes/Alpha/images/tab-off-dot.gif";
                main_block.style.backgroundImage = "url(/Themes/Alpha/images/tab-off.gif)";
            } else {
                element.style.display = "block";
                document[image_name].src = "/Themes/Alpha/images/tab-on-dot.gif";
                main_block.style.backgroundImage = "url(/Themes/Alpha/images/tab-on.gif)";

            }


        } else {
            element.style.display = "none";
            document[image_name].src = "/Themes/Alpha/images/tab-off-dot.gif";
            main_block.style.backgroundImage = "url(/Themes/Alpha/images/tab-off.gif)";
        }
    }
}


function link_expand_collapse_public (caption, element_no, total) {
    id_selected = caption+'_'+element_no;

    for(counter = 0; counter < total; counter++) {
        id = caption+'_'+counter;
        element = document.getElementById(id);
        main_block_id = id+'_main';
        main_block = document.getElementById(main_block_id);
        name_id = caption + '_name_' + counter;
        name_element = document.getElementById(name_id);

        image_name = 'links_bulb_'+counter;

        if(id_selected == id) {
            if(element.style.display == "block") {
                element.style.display = "none";
                document[image_name].src = "/Themes/Alpha/images/tab-off-dot.gif";
                main_block.style.backgroundColor = "#333333";
                name_element.style.color = "white";
            } else {
                element.style.display = "block";
                document[image_name].src = "/Themes/Alpha/images/tab-on-dot.gif";
                main_block.style.backgroundColor = "white";
                name_element.style.color = "black";

            }


        } else {
            element.style.display = "none";
            document[image_name].src = "/Themes/Alpha/images/tab-off-dot.gif";
            main_block.style.backgroundColor = "#333333";
            name_element.style.color = "white";
        }
    }
}

function myRemoveElement(divID) {

    var d = document.getElementById('uploadimageblock');
    var olddiv = document.getElementById(divID);
    olddiv.style.display = "none";
    d.removeChild(olddiv);
}

function edit_delete_for_media (select_id, media_type, uid, form_name) {

    check_val = document.getElementById(select_id).value;
    var checked_boxes = 0;
    var n = document.forms[form_name].elements.length;
    //alert(n+'\n'+check_val+'\n'+media_type);

    if (check_val == 'edit') {
      for(counter = 0; counter < n; counter++) {
          if(document.forms[form_name].elements[counter].type == "checkbox") {
              if(document.forms[form_name].elements[counter].checked == true) {
                  checked_boxes =  checked_boxes + 1;
                  if (checked_boxes == 1) {
                    check_box_name = document.forms[form_name].elements[counter].name;
                  }
                  checked_id = document.forms[form_name].elements[counter].id;
              }
          }
      }
      if (checked_boxes == 0) {
        alert('No '+media_type+ ' has been selected.');
        return false;
      }


      if (checked_boxes == 1) {
        window.location="/edit_media.php?cid="+check_box_name;
      }
      else if(checked_boxes > 1) {
        alert('Please select only one link for editing.');
        return false;
      }
    }
    else if (check_val == 'delete') {
       val = delete_content(media_type);
       if (val == true) {
         document.getElementById(form_name).submit();
       }
       else {
         return false;
       }
    }


}

// display on/off for show content module
function display_show_content(id1, id2, id3) {

  if( document.getElementById (id1).style.display == "none" || document.getElementById (id1).style.display == "") {
     document.getElementById (id1).style.display = "block";
     document['image_clickable'].src = "/Themes/Alpha/images/hide-filters.png";
     document.getElementById ('bottom-expand-blog').style.backgroundImage = "url(/Themes/Alpha/images/bottom-white-border-top.png)";

  }
  else {
     document.getElementById (id1).style.display = "none";
     document['image_clickable'].src = "/Themes/Alpha/images/show-filters.gif";
     document.getElementById ('bottom-expand-blog').style.backgroundImage = "url(/Themes/Alpha/images/bottom-white-border-top.png)";
  }

  if( document.getElementById (id2).style.display == "none" || document.getElementById (id2).style.display == "") {
     document.getElementById (id2).style.display = "block";
     document['image_clickable'].src = "/Themes/Alpha/images/hide-filters.gif";
     document.getElementById ('bottom-expand-blog').style.backgroundImage = "url(/Themes/Alpha/images/bottom-white-border-top.png)";
  }
  else {
     document.getElementById (id2).style.display = "none";
     document['image_clickable'].src = "/Themes/Alpha/images/show-filters.gif";
     document.getElementById ('bottom-expand-blog').style.backgroundImage = "url(/Themes/Alpha/images/bottom-white-border-top.png)";
  }

  if( document.getElementById (id3).style.display == "none" || document.getElementById (id3).style.display == "") {
     document.getElementById (id3).style.display = "block";
     document['image_clickable'].src = "/Themes/Alpha/images/hide-filters.gif";
     document.getElementById ('bottom-expand-blog').style.backgroundImage = "url(/Themes/Alpha/images/bottom-white-border-top.png)";
  }
  else {
     document.getElementById (id3).style.display = "none";
     document['image_clickable'].src = "/Themes/Alpha/images/show-filters.gif";
     document.getElementById ('bottom-expand-blog').style.backgroundImage = "url(/Themes/Alpha/images/bottom-white-border-top.png)";
  }


}

function links_validation (frm, button) {
    if(button == 1 ) {
      if(trim(frm.category_name.value) == "") {
          alert('Please enter the category name.');
          return false;
      }
    }
    else {
       if(trim(frm.title.value) == "") {
          alert('Please enter the link name.');
          return false;
       }
       if(trim(frm.url.value) == "") {
          alert('Please enter the URL for the link.');
          return false;
       }
    }
}

// functions for login-layer:
function showlogin() {
  try {
    document.getElementById('login-layer').style.display='block';
    document.getElementById('overlap').style.visibility="hidden";
  } catch(e) {
    // alert(e);
  }
}
function hidelogin() {
  try {
    document.getElementById('login-layer').style.display='none';
    document.getElementById('overlap').style.visibility="visible";
  } catch(e) {
    // alert(e);
  }
}

function forum_validation () {
  form_obj = document.topicform;
  var n = form_obj.elements.length;
  var msg = '';
  for(i = 0; i < n; i++) {
    if (form_obj.elements[i].type =='text' || form_obj.elements[i].type == 'textarea') {
      temp = trim(form_obj.elements[i].value);
      if(temp == "") {
        msg += '-> ' +form_obj.elements[i].id+' can not be left blank.\n';
      }
    }
  }
  if(msg.length > 0) {
    alert(msg);
    return false;
  } else {
    return true;
  }
}

var search_action = {
  onfocus : function(id) {
    document.getElementById(id).value = '';
    return;
  },
  onblur : function(id, val) {
    document.getElementById(id).value = val;
    return;
  }
}

function show_hide_network_categories(div_id, img_id) {
    image_name = img_id;
    if(document.getElementById(div_id).className == "display_false") {
        document.getElementById(div_id).className = 'display_block';
      } else {
       document.getElementById(div_id).className = 'display_false';
       document.getElementById(div_id+'_iframe').className = 'display_false';
      }
   str_url=window.location.href;

   if (str_url.indexOf('action=')>0) {

     url=make_url_aftermessagedisplay('action',str_url);
     window.location=url;

   }else if(str_url.indexOf('msg=') > 0 ){

     url=make_url_aftermessagedisplay('msg',str_url);
     window.location=url;
   }
   else if(str_url.indexOf('msg_id=') > 0){

     url=make_url_aftermessagedisplay('msg_id',str_url);
     window.location=url;
   }
   if ( document.getElementById('embed_video') ) {
     document.getElementById('embed_video').style.display='block';
   }

}
function change_background_color(div_1,a_id) {
  document.getElementById(div_1).style.background = 'white';
  document.getElementById(a_id).className = 'links-header-greyheader';
}
// for Confirmation on deleting

function delete_confirmation_msg(msg) {
  return_argument = confirm(msg);
  return return_argument;
}
//for deselection of all other selected value on the selection of one particular value
function deselect_others(select_box_id) {
  var obj = document.getElementById(select_box_id);
  var len = obj.options.length;
  if (obj.selectedIndex == 0 || obj.selectedIndex == 1) {
    if(obj.selectedIndex == 0)  {
      obj.options[1].selected = false;
    }
    if(obj.selectedIndex == 1)  {
      obj.options[0].selected = false;
    }
    for (var i = 2; i < len; i++) {
      obj.options[i].selected = false;
    }
  }
}

function user_info_hide_show(div_id_1,div_id_2,div_id_3,div_id_4,div_id_5,div_id_6) {
  if(document.getElementById(div_id_1).style.display == "block") {
    document.getElementById(div_id_1).style.display = 'none';
    document.getElementById(div_id_2).style.display = 'block';
    document.getElementById(div_id_3).style.display = 'none';
    document.getElementById(div_id_4).style.display = 'none';
    document.getElementById(div_id_5).style.display = 'block';
    document.getElementById(div_id_6).style.display = 'block';
  }


}

function showhide_block(id, display){
  block_id = document.getElementById (id);
  if (trim(block_id.style.display)=='' || block_id.style.display=='none') {
    block_id.style.display = 'block';
  } else {
    block_id.style.display = 'none';
  }
}
//this function 'function video_tour_display()' is added to redirect to video tour page when video tour div is being clicked

function video_tour_display() {
   window.location='http://blip.tv/file/66306';
}



//this function is for hide alternative option if one particular option is selected from select box
function hide_alternative_option(month_select_id) {
  var obj = document.getElementById(month_select_id);
  if(obj.selectedIndex == 0) {
    document.getElementById('alternative_month').style.display = "none";
  }else {
    document.getElementById('alternative_month').style.display = "block";
  }

}

/* function to (u)check all the checboxes in a given form
   @param:form name-> string, checkbox name which will be used for (un)check all
*/
function check_uncheck_all (manage_users, check_uncheck) {
  var form_obj = document.manage_users;
  var total_elements = form_obj.elements.length;
  var checkbox_status = false;

  if( form_obj.check_uncheck.checked ) {
    checkbox_status = true;
  }

  for( counter = 0; counter < total_elements; counter++ ) {
    if( form_obj[counter].type == "checkbox" ) {
      form_obj[counter].checked = checkbox_status;
    }
  }
}

/* Adding new field in Network Feed  */
var total = 1;

function addfeed(identity,maxlimit) {
 if (total < maxlimit) {
   mydiv = document.createElement('div');

    var divIdName = 'my'+total+'feed';
    mydiv.setAttribute('id',divIdName);

    data = '<dl>';
    data +='<dt>Feed Title:</dt>';
    data +='<dd><input name="title_' + total + '" type="text" /></dd>';
    data +='<dt>Blog or Feed URL :</dt>';
    data += '<dd><input type="text" name="feed_url_' + total + '" value=""><br /></dd></dl>';

    mydiv.innerHTML = data;
      document.getElementById(identity).appendChild(mydiv);
    total++;
 }
  if (total == maxlimit){
    document.getElementById("addmore_button").style.display = "none"
  }
}
function show_side_gallery_module (content_id) {
  content_block = document.getElementById (content_id);
  var con = new Array('gallery-images', 'gallery-videos', 'gallery-audios');
  var active = new Array('images', 'videos', 'audios');
  var temp = content_block.id;
  var active_li = temp.split('-');
  for (var i=0; i<con.length; i++) {
    conent_id = document.getElementById (con[i]);
    conent_id.className = 'image_list display_false';
    document.getElementById(active[i]).className='';
    if (conent_id == content_block)  {
      content_block.className = 'image_list display_true';
      document.getElementById(active_li[1]).className='active';
   }
  }

}

function announcement_submit(perform, content_id) {
  document.getElementById("form_action").value = perform;
  document.getElementById("aid").value = content_id;
  document.forms['announce_form'].submit();
}

function show_hide_inner_data (id) {
  var total = 0;
  var parent1 = document.getElementById(id);
  var thisChild = parent1.firstChild;
  while ( thisChild != parent1.lastChild )
  {
    if ( thisChild.nodeType == 1 && thisChild.nodeName == 'DIV')
    {   if(thisChild.id == "") {
          thisChild.id = id+'_temp_'+total;
        }
        if (document.getElementById(thisChild.id).style.display == 'none') {
          document.getElementById(thisChild.id).style.display='block'
        }
        else {
          document.getElementById(thisChild.id).style.display='none';
        }

    }
    thisChild = thisChild.nextSibling;
    total++;
  }
}

function edit_delete_media(select_id,check_val,form_name) {
  if (check_val == 'delete') {
      val = delete_content1();
       if (val == true) {
//         document.getElementById(form_name).action += "&id="+select_id ;
         document.getElementById('media_id').value = select_id;
         document.getElementById(form_name).submit();
       }
       else {
         return false;
       }
  }
}

function show_hide_media (id_1,id_2) {
  document.getElementById(id_1).style.display = 'block';
  document.getElementById(id_2).style.display = 'none';
}

function select_album() {
  var url = document.URL;
/*
  var m = url.indexOf('?');
  if ((url.indexOf('msg_id')) != -1) {
    url = url.replace('msg_id','');
  }
  if (m == -1) {
    window.location.href = url + '?album_id=' + document.getElementById('album_name').value;
  }
  else {
*/
      if(document.URL.indexOf('album_id') == -1 ) {
//         window.location.href = url + '&album_id=' + document.getElementById('album_name').value+'&msg_rep';
         window.location.href = url + '&album_id=' + document.getElementById('album_name').value;
      }
      else {
          var new_url = url.replace(/(album_id\=\d+)/g, 'album_id=' + document.getElementById('album_name').value );
          window.location.href = new_url;
/*
          var m = url.indexOf('album_id=');
          var mm = url.substring(0, m);
          mm += "album_id=" + document.getElementById('album_name').value;
          window.location.href = mm;
*/
/*
          // If variable exits
          var qs = location.search.substring(1);
          var abc = "a?" + location;
          var reg = /[a][l][b][u][m][_][i][d]/;
          var i = reg.test(qs);
          if(i) {
              var nv = qs.split('&');
              var url = new Object();
              var str = "";
              for(i = 0; i < nv.length; i++) {
                  var vl = nv[i].split('=');
                  if(i>0) {
                      str += "&";
                  }
                  if(vl[0]=="album_id") {
                     str += vl[0] + "=" + document.getElementById('album_name').value;
                  } else {
                       str += nv[i];
                    }
               }

          }
*/
      }
//  }
  if ((str.indexOf('msg_id')) != -1) {
    str = str.replace('msg_id','msg_rep');
  }
  location.replace(abc.split('?')[1] + "?" + str);
}
function select_frnd() {
  var url = document.URL;
/*
  var m = url.indexOf('?');
  if (m == -1) {
    window.location.href = url + '?uid=' + document.getElementById('frnd_list').value;
  }
  else {
*/
      if(document.URL.indexOf('uid') == -1 ) {
         window.location.href = url + '&uid=' + document.getElementById('frnd_list').value + "&view=friends";
      }
      else {
          var new_url = url.replace(/(uid\=\d+)/g, 'uid=' + document.getElementById('frnd_list').value );
          window.location.href = new_url;
/*
          // If variable exits
          var qs = location.search.substring(1);
          var abc = "a?" + location;
          var reg = /[u][i][d]/;
          var i = reg.test(qs);
          if(i) {
              var nv = qs.split('&');
              var url = new Object();
              var str = "";
              for(i = 0; i < nv.length; i++) {
                  var vl = nv[i].split('=');
                  if(i>0) {
                      str += "&";
                  }
                  if(vl[0]=="uid") {
                     str += vl[0] + "=" + document.getElementById('frnd_list').value;
                  } else {
                       str += nv[i];
                    }
               }
             location.replace(abc.split('?')[1] + "?" + str);
          }
*/
      }
//  }
}

function user_messages(form_action, skip) {
  var form_name = 'messageList';
  var form_obj = document.forms[form_name];
  var condition;
  if (skip) {
    condition = true;
  } else {
    condition = checked_boxes(form_name);
  }
  switch(form_action) {
    case 'delete':
      if (condition) {
//        form_obj.form_action.value = 'delete';
        form_obj.action.value = form_action;
        form_obj.submit();
      } else {
        alert('Please select message(s) to delete');
        return false;
      }
    break;
    case 'move':
      if (!condition) {
        alert('Please select message(s) to move');
        return false;
      } else if (form_obj.sel_folder.value == "") {
        alert('Please select a folder.');
        return false;
      } else if (form_obj.sel_folder.value == -1) {
        alert('Please create a folder to hold the message(s)');
        return false;
      } else {
//        form_obj.form_action.value = 'move';
        form_obj.action.value = 'move';
        form_obj.submit();
      }
    break;
  }
}

function checked_boxes(form_name) {
  var form_obj, n, i;
  form_obj = document.forms[form_name];
  n = form_obj.elements.length;//total number of elements in the form
  for (i=0; i < n; i++) {
    if ((form_obj.elements[i].type == "checkbox") && form_obj.elements[i].checked == true) {
      return true;
    }
  }
  return false;
}

function select_group() {
  var url = document.URL;
/*
  var m = url.indexOf('?');
  if (m == -1) {
    window.location.href = url + '?gid=' + document.getElementById('group_list').value;
  }
  else {
*/
      if(document.URL.indexOf('gid') == -1 ) {
         window.location.href = url + '&gid=' + document.getElementById('group_list').value;
      }
      else {
          var new_url = url.replace(/(gid\=\d+)/g, 'gid=' + document.getElementById('group_list').value );
          window.location.href = new_url;
/*
          var m = url.indexOf('gid=');
          var mm = url.substring(0, m);
          mm += "gid=" + document.getElementById('group_list').value;
          window.location.href = mm;
*/
/*
          var qs = location.search.substring(1);
          var abc = "a?" + location;
          var reg = /[g][i][d]/;
          var i = reg.test(qs);
          if(i) {
              var nv = qs.split('&');
              var url = new Object();
              var str = "";
              for(i = 0; i < nv.length; i++) {
                  var vl = nv[i].split('=');
                  if(i>0) {
                      str += "&";
                  }
                  if(vl[0]=="msg_id") {
                     str += 'id';
                  }
                  if(vl[0]=="gid") {
                     str += vl[0] + "=" + document.getElementById('group_list').value;
                  } else {
                       str += nv[i];
                    }
               }
            location.replace(abc.split('?')[1] + "?" + str);
          }
*/
      }
//  }
}


// Functions for the add_message module.
var add_message = {
  check: function() {
    var error_mesg = '';
    if (document.getElementById('to_box').value == "") {
       alert('Please select at least one recipient.');
       document.getElementById('to_box').focus();
       return false;
    }
    if (document.getElementById('subject').value == "" && document.getElementById('body').value == "") {
       error_mesg = 'Do you want to send this message without a subject and body?';
       if (!this.confirm(error_mesg)) {
         document.getElementById('subject').focus();
         return false;
       }
    } else {
      if (document.getElementById('subject').value == "") {
        error_mesg = 'Do you want to send this message without a subject?';
        if (!this.confirm(error_mesg)) {
          document.getElementById('subject').focus();
          return false;
        }
      }
      else if (document.getElementById('body').value == "") {
        error_mesg = 'Do you want to send this message without text in the body?';
        if (!this.confirm(error_mesg)) {
          document.getElementById('body').focus();
          return false;
        }
      }
    }
    document.forms['compose_form'].submit();

  },
  confirm: function(msg) {
    if (confirm(msg) == true) {
      return true;
    } else {
      return false;
    }
  },
  add_recipient:  function () {
    var friend = document.getElementById('sel_friend');
    var b = document.getElementById('to_box');
    var bd = document.getElementById('to_display_box');
    var temp = new Array();
    var len, flag = 0;
    temp = b.value.split(',');
    len = temp.length;
    for(i = 0;i < len;i++) {
        if(temp[i] == friend.value) {
          flag = 1;
        }
    }
    if (friend.value != 'select friend' && flag == 0) {
      if (b.value) {
        b.value += "," + friend.value;
        $(bd).html($(bd).html() + ", " + friend.options[friend.selectedIndex].text);
      }
      else {
        b.value += friend.value;
        $(bd).html(friend.options[friend.selectedIndex].text);
      }
    }
    if (friend.value == 'select friend') {
      $(bd).html('');
      b.value = '';
    }
  }
}




// for Emblem image  uploading

var total =1;
var bool=true;
function addfile(identity,page_id) {
  if (bool) {
    total=document.getElementById("total").value;
    bool=false;
    }
  mydiv = document.createElement('div');
  var divIdName = 'my'+total+'Image';
  mydiv.setAttribute('id',divIdName);
  if(total <5) {
    data ='<div class="field_big"><h5><label for="select file">Select a file to upload: or enter a URL below</label></h5>';
    data += '<input name="userfile_' + total + '" type="file" id="select_file" class="text longer" value="" /></div>';
    if (page_id == 'image_gallery') {
          data += '<div class="field_big"><h5><label for="file url">Enter the URL of image</label></h5>';
          data += '<input name="userfile_url_'+ total +'" class="text longer" id="file_url" type="text" value=""  maxlength="100"/></div>';
        }

     data += '<div class="field_big"><h5><label for="image title">Image title</label></h5>';
     data += '<input type="text" name="caption[' + total + ']" value="" class="textlonger" id="image_title"  maxlength="100" /></div>';
     mydiv.innerHTML = data;
     document.getElementById(identity).appendChild(mydiv);
      }
      else {

          alert("You can not added more images to Emblem");
          var olddiv_1= document.getElementById("addmore_button");
           olddiv_1.style.display = "none";
           return;
      }
      total++;


}
// for Emblem image  Removing

function removefile() {
  if (bool) {
    total=document.getElementById("total").value;
     bool=false;
     }
  if(total==1){
     alert("You cannot delete this image");
     return false;
      }
   var check = confirm("Do you want to remove this image from your emblem?");
   if (check == false) {return false;}
   var showbutton = document.getElementById("addmore_button");
   showbutton.style.display = "";
   var d = document.getElementById('block');
   total=total-1;
   var divIdName = 'my'+total+'Image';
   var olddiv = document.getElementById(divIdName);
   olddiv.style.display = "none";
   d.removeChild(olddiv);
}


function show_rows(baseurl)
{
  val=document.getElementById("rows").value;
  if (!val)
   {
     return;
   } else {
     str=window.location.href;
     str_len_main=baseurl.length;
     st=str.split("rows");
     if ( st.length >1) {
       window.location=st[0]+"rows="+val;
     } else if (st[0].length > str_len_main ){
       window.location=st[0]+"&rows="+val;
     } else {
       window.location=st[0]+"?rows="+val;
     }
   }
 }

 function get_media_access(name_of_var, default_selected) {
  if (default_selected == null) {
   default_selected = 1;
  }
  var output = '<select name= "'+name_of_var+'"  id="'+name_of_var+'"  class="select-txt text" style="width: 120px;">';

  if (default_selected == 0) {
    output += '<option value="0" selected="selected">Nobody</option>';
  }
  else {
    output += '<option value="0">Nobody</option>';
  }
  if (default_selected == 1) {
    output += '<option value="1" selected="selected">Everybody</option>';
  }
  else {
    output += '<option value="1">Everybody</option>';
  }
  if (default_selected == 2) {
    output += '<option value="2" selected="selected">Immediate Relations</option>';
  }
  else {
    output += '<option value="2">Immediate Relations</option>';
  }

  output += '</select>';
  return output;
}

function show_hide_network_default_categories(div_id,img_id,image_id) {
    image_name = img_id;
    if(document.getElementById(div_id).className == "display_false") {
        document.getElementById(div_id).className = 'display_block';
        document[image_id].src = '/Themes/Default/images/minus.gif';
    } else {
       document.getElementById(div_id).className = 'display_false';
       document[image_id].src = '/Themes/Default/images/plus.gif';
     }

}

/**
* add comments here with an example showing how to use this function
*/
function make_url_aftermessagedisplay(action,url) {
  var str_first_url = url.split(action);
  var str_second_url = str_first_url[1].split("&");
  if (str_second_url.length > 1) {
    finalurl = str_first_url[0] + str_second_url[1];
    len=str_second_url.length;
    start = 2;
    while (len > 2) {
      finalurl += '&' + str_second_url[start];
      start=start + 1;
      len=len-1;
    }

 } else {
   url_fetch=str_first_url[0].substr(0,str_first_url[0].length-1);
   finalurl= url_fetch;
 }

 return finalurl;
}

var show_hide_shortcuts = {
  obj : function (id) {
    return document.getElementById(id);
  },
  onmouseover : function (id) {
    if (this.obj(id).className == 'display_false'); {
      this.obj(id).className = 'display_true';
    }
  },
  onmouseout : function(id) {
    if (this.obj(id).className == 'display_true'); {
      this.obj(id).className = 'display_false';
    }
  }
}
// This function has been written to set focus on Ok button of message window
function hide_message_window(confirm_id) {
  try {
    document.getElementById(confirm_id).focus();
  }
  catch(e) {
  }
}

function track_this_ad(ad_id) {
    // credit: http://www.smart-it-consulting.com/article.htm?node=155&page=96
    if(document.images){
        (new Image()).src = base_url+"/track_ad.php?ad_id="+ad_id+"&loc="+document.location;
    }
    return true;
}

// This function will be used to monitor display of create and edit ad block.
function showhide_ad_block(id, display, page) {
  block_id = document.getElementById(id);
  if (display == 1) {
    window.location = page;
  } else {
    window.location = page+"open=1";
  }
}
function show_pending_invitation(base_url) {
  var gid = document.getElementById("groups").value;
  window.location = base_url+"/group/invitation/gid="+gid;
}
function form_submit()
{
document.forms['blog_save_settings'].submit();
}
function preview_url(url_str, element_id, base_url) {
  var url = trim(url_str);
  var url_length = url.length;
  var i;
  var preferred_url = '';
  var char;
  for (i = 0; i < url_length; i++) {
    //check for valid characters
    var field = new Array("'", "\"", ",", "$", "&", "<", ">", "?", ";", "#", ":", "=" ,"~", "+", "%", " ");
    for (var j = 0; j < field.length; j++) {
      var flag=false;
      if (url.charAt(i) == field[j]) {
         flag=true;
         break;
       }
    }
    if (flag==true) {
      preferred_url = preferred_url + '_';
    } else {
      preferred_url = preferred_url + url.charAt(i);
    }
  }
  document.forms['formStaticPagesManagement'].preferred_caption.value = preferred_url;
  document.getElementById(element_id).innerHTML = 'Url for your page would be like '+base_url+'/links/'+preferred_url;

}
function show_email_details(base_url) {
  var selected_index = document.getElementById("email_type").selectedIndex;
  var email_type = document.getElementById("email_type").options[selected_index].value;
  if (email_type != "0") {
    window.location = base_url+"/configure_email.php?email_type="+email_type;
  } else {
    window.location = base_url+"/configure_email.php";
  }
}
function delete_selected_groups() {
  condition = checked_boxes('manage_users');
  if (condition) {
    val = delete_confirmation_msg('Are you sure you want to delete these groups ?');
    if (val) {
      document.forms['manage_users'].submit();
    } else {
      return false;
    }
  } else {
    alert('Please select groups(s) to delete');
    return false;
  }
}
function delete_selected_celebrities() {
  condition = checked_boxes('manage_users');
  if (condition) {
    val = delete_confirmation_msg('Are you sure you want to delete these celebrities ?');
    if (val) {
      document.forms['manage_users'].submit();
    } else {
      return false;
    }
  } else {
    alert('Please select celebrity to delete');
    return false;
  }
}
/*adding for poll options.*/
function ajax_method_poll_options () {
  num = document.getElementById('num_pollid').value;
  url = '/ajax_poll_options.php'+"?num="+num;
  $('#show_options').html('<div style="height:100px;color:red;margin-top:50px" class="center">loading options...</div>').load(url);
}
//jquery function for validating the create poll form.
function create_poll_form_validation () {
   var msg = "";
   $("input[@type='text']").each(function() {
     Elemname = $(this).attr("name");
     if($(this).val() == "") {
       msg = msg+(Elemname + ' can not be empty\n');
     }
   });
   if (trim(msg) == "") {
     return true;
   }else {
     alert(msg);
     return false;
   }
}

function ajax_sort_activities(event_type, ajax_url, select_id) {
   var sort_by;
   var selected_index = document.getElementById(select_id).selectedIndex;
   sort_by = document.getElementById(select_id).options[selected_index].value;
   id = 'list_'+event_type;
   url = ajax_url+"&sort_by="+sort_by;

   $('#'+id).html('<div style="padding-left: 28px"><img src="/Themes/Default/images/ajaxload.gif" /><br />Loading...</div>').load(url);
}

function sanitize_input(frm) {
    var len,val;
    len=frm.elements.length;
    for(i=0;i<len;i++){
       if (frm.elements[i].type == 'text'){
         val=frm.elements[i].value;
         frm.elements[i].value = url_encode(val);
       }
       if (frm.elements[i].type == 'textarea'){
         val=frm.elements[i].value;
         frm.elements[i].value = url_encode(val);
       }
    }
    return true;
}

function url_encode(str) {
  return str.replace(/\(/g,'%28').replace(/\)/g,'%29').replace(/\:/g,'%3A').replace(/\;/g,'%3B').replace(/\#/g,'%23').replace(/\{/g,'%7B').replace(/\}/g,'%7D').replace(/\</g,'%3C').replace(/\>/g,'%3E').replace(/\[/g,'%5B').replace(/\]/g,'%5D').replace(/\+/g,'%2B').replace(/%20/g, '%20').replace(/\*/g, '%2A').replace(/\//g, '%2F').replace(/@/g, '%40');
}