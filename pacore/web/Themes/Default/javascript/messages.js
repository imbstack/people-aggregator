$(document).ready(
  function() {    
    $('#select_all').click(
      function() {
        if (this.checked == true) {
          $("input[type='checkbox']").each(function() { this.checked = true;})
        } else {
          $("input[type='checkbox']").each(function() {this.checked = false;})
        }
      }
    );
  } 
);