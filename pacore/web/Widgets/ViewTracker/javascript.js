var http_request = false;
   function makeViewPOSTRequest(url, parameters) {
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
      http_request.onreadystatechange = alertViewContents;
      http_request.open('POST', url, true);
      http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      http_request.setRequestHeader("Content-length", parameters.length);
      http_request.setRequestHeader("Connection", "close");
      http_request.send(parameters);
   }

   function alertViewContents() {
      if (http_request.readyState == 4) {
         if (http_request.status == 200) {
            result = http_request.responseText;
            document.getElementById('view_response').innerHTML = result;
         } else {
            alert('There was a problem with the request.');
         }
      }
   }
   function get_view(page_type) {
     if (document.title == '') {
       alert("Page Title can't be empty");
       return;
     }
     if (page_type == '' ) {
       alert("Type can't be empty");
     } else {
      var poststr = "type=" + encodeURI(page_type)+
                    "&title=" + encodeURI(document.title)+
                    "&url=" + encodeURI(document.URL);
                    // we need to make a entry in the .htaccess in order to send cross domain ajax request.
      makeViewPOSTRequest('Widgets/ViewTracker/update_views.php', poststr);
    }
   }