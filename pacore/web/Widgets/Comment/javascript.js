var http_request = false;
   function makePOSTRequest(url, parameters, call_type) {
      http_request = false;
      if (window.XMLHttpRequest) { // Mozilla, Safari,...
         http_request = new XMLHttpRequest();
         if (http_request.overrideMimeType) {
                // set type accordingly to anticipated content type
            //http_request.overrideMimeType('text/xml');
            http_request.overrideMimeType('text/html');
         }
      } else if (window.ActiveXObject) { // IE
         try {
            http_request = new ActiveXObject("Msxml2.XMLHTTP");
         } catch (e) {
            try {
               http_request = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {}
         }
      }
      if (!http_request) {
         alert('Cannot create XMLHTTP instance');
         return false;
      }
      if (call_type == 'comment') {
        http_request.onreadystatechange = comment.showContents;
      }else if (call_type == 'rating') {
        http_request.onreadystatechange = comment.showrating;
      }else if (call_type == 'pagination') {
        http_request.onreadystatechange = comment.showpaging;
      }
      http_request.open('POST', url, true);
      http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      http_request.setRequestHeader("Content-length", parameters.length);
      http_request.setRequestHeader("Connection", "close");
      http_request.send(parameters);
   }

var comment = {
   showContents : function () {
    if (http_request.readyState == 4) {
         if (http_request.status == 200) {
            result = http_request.responseText;
            document.forms['comment_form'].comment.value = '';
            if (result != 'Please login to comment') {
              alert('Comment posted sucessfully'); 
            }else {
              document.getElementById('comment_form_div').style.display = 'none';
            }
            document.getElementById('posted_comment').innerHTML = result;
         } else {
            alert('There was a problem with the request.');
         }
      }
  },
  showrating : function () {
    if (http_request.readyState == 4) {
         if (http_request.status == 200) {
            result = http_request.responseText;
            document.getElementById('rating_main_div').innerHTML = result;
         } else {
            alert('There was a problem with the request.');
         }
      }
  },
  showpaging : function () {
     if (http_request.readyState == 4) {
         if (http_request.status == 200) {
            result_paging = http_request.responseText;
            document.getElementById('comment_page_div').innerHTML = result_paging;
         } else {
            alert('There was a problem with the request.');
         }
      }
  }  
};
   function get(obj) {
     if (obj.comment.value == '') {
       alert('Comment can not be empty');
     } else {
     
      var poststr = "comment=" + encodeURI(obj.comment.value)+
                    "&slug=" + encodeURI(obj.slug.value)+
                    "&c_id=" + encodeURI(obj.c_id.value)+
                    "&uid=" + encodeURI(obj.uid.value);
      makePOSTRequest('Widgets/Comment/post_comment.php', poststr,'comment');
    }
   }
   function redirect_login(url) {
     window.location= url+window.location;
   }
var thumbs_rating = {
  click : function (star_id, id, rating_type, max_rating,login_uid) {
    get_data = 'rating='+star_id+'&type_id='+id+'&rating_type='+rating_type+'&max_rating='+max_rating+'&login_uid='+login_uid;    
    makePOSTRequest('Widgets/Comment/rating_thumb.php', get_data, 'rating');
  },
  user_rating : 0
};
function ajax_pagination(obj, cid) {
  query_string = obj.search;
  page_num = query_string.split('=');
  page_num = parseInt(page_num[1]);
  get_data = 'page='+page_num+'&cid='+cid;
  makePOSTRequest('Widgets/Comment/comment_pagination.php', get_data, 'pagination');
}