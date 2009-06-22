$(document).ready(
  function() {

    $('.month-calendar ul.calendar li.noevents').hover(
      function() {$(this).addClass('hovering');},
      function() {$(this).removeClass('hovering');}
    );
    $('.month-calendar ul.calendar li.hasevents').hover(
      function() {$(this).addClass('hovering');},
      function() {$(this).removeClass('hovering');}
    );

    $('.month-calendar ul.calendar li.hasevents').click(
      function() {
        $(this).addClass('activeday');
        $('.month-calendar ul.calendar').hide()
        .removeClass('calendar').addClass('list')
        .show('slow');
      }
    );

    
    // style switcher for calendar style
    $('.month-calendar .list-style').click(
      function() {
        $('.month-calendar ul.calendar').hide()
        .removeClass('calendar').addClass('list')
        .show('slow');
        // modify the display mode in month navigation
        $('.month-calendar .month-prev, .month-calendar .month-next').each(
        	function() {
        		if (this.href.indexOf('dmode=') != -1) {
        			this.href = this.href.replace(/dmode=calendar/,'dmode=list');
        		} else {
        			var parts = this.href.split('#');
        			this.href = parts[0] + '&dmode=list';
        			if (parts[1]) this.href = this.href + "#" + parts[1];
        		}
        	}
        );
      return false;
      }
    );
    $('.month-calendar .calendar-style').click(
      function() {
        $('.month-calendar ul.list .activeday').removeClass('activeday');
        $('.month-calendar ul.list').hide()
        .removeClass('list').addClass('calendar')
        .show('slow');
        // modify the display mode in month navigation
        $('.month-calendar .month-prev, .month-calendar .month-next').each(
        	function() {
        		if (this.href.indexOf('dmode=') != -1) {
        			this.href = this.href.replace(/dmode=list/,'dmode=calendar');
        		} else {
        			var parts = this.href.split('#');
        			this.href = parts[0] + '&dmode=calendar';
        			if (parts[1]) this.href = this.href + "#" + parts[1];
        		}
        	}
        );
      return false;
      }
    );
  }
);