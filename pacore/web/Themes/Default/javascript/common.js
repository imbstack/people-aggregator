$(document).ready( 
  function() {
    // we register
    // collapse/expand click events
    $('.module > h1').click(
      function() {
      var m = 'image_'+$(this).parent().attr('id');  // getting the parent ID 
      if ($('../div',this).is(':hidden')) {
        document.getElementById(m).src = CURRENT_THEME_PATH + "/images/arrow_dn.gif";
        $('../div', this).show('slow');
      }
      else {
        document.getElementById(m).src = CURRENT_THEME_PATH + "/images/arrow_up.gif";
        $('../div', this).hide('slow');
      }
    }
    );
  } 
);