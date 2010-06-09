var thumbs_rating = {
  click : function (star_id, id, rating_type, max_rating, login_uid) {
    get_data = 'rating='+star_id+'&type_id='+id+'&rating_type='+rating_type+'&max_rating='+max_rating;    
    $.ajax({
      type: "POST",
      url: base_url+"/ajax/rating_thumb.php",
      data: get_data,
      success: function(msg) {
        arr = msg.split('@');
        $('#overall_rating_'+id).html(arr[0]);
        $('#your_rating_'+id).html(arr[1]);
      }
    });
    this.user_rating = star_id;
  },
  user_rating : 0
};