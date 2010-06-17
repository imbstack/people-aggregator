$(document).ready(
  function() {
function getPageCoords (element) {
var coords = { x: 0, y: 0 };
while (element) {
coords.x += element.offsetLeft;
coords.y += element.offsetTop;
element = element.offsetParent;
}
return coords;
}

    /* $("div[@id^='imgcontainer_']").hover(
      function(){
        var obj_id     = "#"+this.getAttribute('id');
        var oid = this.getAttribute('id').replace(/imgcontainer_/,'');
        var tooltip_id = "#tooltip_" + this.getAttribute('id').replace(/imgcontainer_/,'');
        var elm = document.getElementById("tooltip_" + oid);
        var coords = getPageCoords(this);
        if(!elm.style.left) {
          elm.style.left = (coords.x + 30) + "px";
          elm.style.top = (coords.y - 40) + "px";
        }
        $(tooltip_id).show();
        $(tooltip_id).fadeTo(500, 1.00);
//        $(tooltip_id).fadeTo(500, 0.50);
        $(tooltip_id).hover (
          function() {
        $(this).show();
//            $(this).fadeTo(500, 1.00);
            return false;
          },
          function() {
            setTimeout("",1000);
            $(this).hide();
            return false;
        });
      },
      function(){
        var tooltip_id = "#tooltip_" + this.getAttribute('id').replace(/imgcontainer_/,'');
        $(tooltip_id).hide();
      }
    );*/

    $("#chbox_no_photo_ok").click(
      function() {
        var is_checked = $("#chbox_no_photo_ok").attr('checked');
        if(is_checked)  {
          $("input[@id^='no_photo_ok_']").val('1');
        } else {
          $("input[@id^='no_photo_ok_']").val('0');
        }
        document.forms['myform_show_mode'].submit();
      }
    );

/*
    $("div[@id^='tooltip_']").hover(
      function(){
        $(this).show();
        $(this).fadeTo(500, 1.00);
        return false;
      },
      function(){
        $(this).fadeTo(500, 0.00);
        $(this).hide();
        return false;
      }
    );
*/

    // Find the input tag of ID advance_search
    // on the click event
    $('#button_text').click(
      function() {
        // toggle of div id  advance search
        $('#advance_search_options').toggle("slow");
        var toggle_text = $("#toggle_text").html();
        document.getElementById('last_name').value = '';
        document.getElementById('allnames').value = '';
        if (toggle_text == 'Advanced Search') {
          $("#toggle_text").html("Simple Search");
        } else if (toggle_text == 'Simple Search'){
          $("#toggle_text").html("Advanced Search");
        }
      }
    );
    $('.module > h1').click(
      function() {
      $('../div', this).toggle('slow');
      }
    );
		$('#first_name').keypress(
			function (e) {
        if (e.keyCode == 13)//13 is for return key
				document.forms['myform_search'].submit();
			}
		);
	 $('#last_name').keypress(
			function (e) {
        if (e.keyCode == 13)//13 is for return key
				document.forms['myform_search'].submit();
			}
		);
  }
);

