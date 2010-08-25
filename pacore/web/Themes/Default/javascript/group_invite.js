var invite = {
  add_recipient:  function () {
    var friend = document.getElementById('sel_friend');
    var b = document.getElementById('email_user_name');
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