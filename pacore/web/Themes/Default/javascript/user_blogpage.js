// this code will be executed as soon as the the page's DOM is ready
$(document).ready(
  function() {
    if (collapsed) {
      var l = collapsed.length;
      for (var i = 0; i<l; i++) {
        m = collapsed[i];
        document.getElementById('image_'+m).src = CURRENT_THEME_PATH + "/images/arrow_up.gif";
        $('#'+m+'/div').toggle();
      }
    }
  }
);