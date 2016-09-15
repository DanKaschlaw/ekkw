function calendar(){
    var $ = jQuery;
    var events = [
        { Title: "Five K for charity", Date: new Date("01/09/2016") },
        { Title: "Dinner", Date: new Date("02/09/2016") },
        { Title: "Meeting with manager", Date: new Date("03/09/2016") }
    ];
    $( function() {
        $( "#datepicker" ).datepicker();
    } );

    /*beforeShowDay: function(date) {
        var result = [true, '', null];
        var matching = $.grep(events, function (event) {
            return event.Date.valueOf() === date.valueOf();
        });

        if (matching.length) {
            result = [true, 'highlight', null];
        }
        return result;
    },
    onSelect: function(dateText) {
        var date,
            selectedDate = new Date(dateText),
            i = 0,
            event = null;

        /!* Determine if the user clicked an event: *!/
        while (i < events.length && !event) {
            date = events[i].Date;

            if (selectedDate.valueOf() === date.valueOf()) {
                event = events[i];
            }
            i++;
        }
        if (event) {
            /!* If the event is defined, perform some action here; show a tooltip, navigate to a URL, etc. *!/
            alert(event.Title);
        }
    }*/
}
