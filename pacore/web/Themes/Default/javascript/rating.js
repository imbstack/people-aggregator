var toggle_stars = {
  mouseover : function (star_id, id) {
    for (cnt = 0; cnt < star_id; cnt++) {
      $('#star_'+id+'_'+(cnt+1)).attr("src", "/Themes/Default/images/star.gif");
    }
    $('#star_'+id+'_'+(star_id)).attr("src", "/Themes/Default/images/staractive.gif")
    .addClass('hover');
  },
  mouseout : function (star_id, id) {
    var starsrc = "/Themes/Default/images/star.gif";
    // if there is no rating at all
    if (! $('.user_star').is('.current_rating')) {
      starsrc = "/Themes/Default/images/starfaded.gif";
    }
    $('.user_star').each(
      function () {
        $(this)
          .attr("src", starsrc)
          .removeClass('hover');
        if($(this).is('.current_rating')) {
          // switch to faded from here onwards
          starsrc = "/Themes/Default/images/starfaded.gif";
        }
      }
    );
  },
  click : function (star_id, id, rating_type, max_rating) {
    get_data = 'rating='+star_id+'&type_id='+id+'&rating_type='+rating_type+'&max_rating='+max_rating;    
    $.ajax({
      type: "POST",
      url: "/ajax/rating.php",
      data: get_data,
      success: function(msg) {
        $('#overall_rating_'+id).html(msg);
      }
    });
    // remove the firmer 'current_rating' class
    $(".current_rating").removeClass('current_rating');
    // set to new
    $('#star_'+id+'_'+(star_id)).addClass('current_rating');
    // make sure it shows
    this.mouseout(star_id, id);
    
  }
  // , user_rating : 0
};