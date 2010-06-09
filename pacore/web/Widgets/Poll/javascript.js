var http_request = false;
   function makePollPOSTRequest(url, parameters) {
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
      http_request.onreadystatechange = alertPollContents;
      http_request.open('POST', url, true);
      http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      http_request.setRequestHeader("Content-length", parameters.length);
      http_request.setRequestHeader("Connection", "close");
      http_request.send(parameters);
   }

   function alertPollContents() {
      if (http_request.readyState == 4) {
         if (http_request.status == 200) {
            result = http_request.responseText;
            document.getElementById('poll_module').innerHTML = result;
         } else {
            alert('There was a problem with the request.');
         }
      }
   }
   function getCheckedValue(radioObj) {
        if(!radioObj)
                return "";
        var radioLength = radioObj.length;
        if(radioLength == undefined)
                if(radioObj.checked)
                        return radioObj.value;
                else
                        return "";
        for(var i = 0; i < radioLength; i++) {
                if(radioObj[i].checked) {
                        return radioObj[i].value;
                }
        }
        return "";
 }
   function get_poll(obj) {
     var vote = getCheckedValue(obj.vote);
     if (vote == '') {
       alert('Please select one option to vote');
     } else {
     
      var poststr = "vote=" + encodeURI(vote)+
                    "&poll_id=" + encodeURI(obj.poll_id.value)+
                    "&uid=" + encodeURI(obj.uid.value);
      makePollPOSTRequest('Widgets/Poll/ajax_save_vote.php', poststr);
    }
   }