// functionality for the login.php page

$(document).ready(
  function() {
    // enable switching of login method
    $('a.loginmethod_inactive').click(
      function() {
        alert("Not yet available.");
      }
    );
    // hide all non-active login method boxen
    $('.loginform').not('.active').hide();
    // enable click to show
    $('a.loginmethod').click(
      function() {
        var id = $(this).attr('id').replace(/^show_/, '#');
        $('a.loginmethod').removeClass('active');
        $(this).addClass('active');
        // alert(id);
        $('.loginform').hide().removeClass('active'); // hide all
        $(id).show('slow').addClass('active'); // show the one we want
      }
    );
  }
);