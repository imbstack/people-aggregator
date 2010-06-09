var cnt;
var r_url;
var t;

$(document).ready(
  function() {
    if(typeof(redirect_url) != "undefined"){
      r_url = redirect_url;
    }
    if(typeof(redirect_delay) != "undefined"){
      cnt = redirect_delay;
      timedCount();
    }
  }
);

function timedCount() {
  document.getElementById('txt_cnt').value = cnt;
  cnt = cnt-1;
  t = setTimeout("timedCount()",1000);
  if(cnt <= 0) {
    clearTimeout(t);
    redirect_to_url();
  }
}

function redirect_to_url() {
  window.location = r_url;
}
