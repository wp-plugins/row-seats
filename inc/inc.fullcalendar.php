<h1><strong><span style="color: #ff0000;">Show Calendar</span></strong></h1>

The calendar works this way:
<br>
a) Month view - When you create and save a show in this view, it is considered for the whole day event.
<br>
b) Week view - You can set the time start/end of your event for any particular day.
<br>
c) Day view - same as week view above.
<br>
<br>

<script type="text/javascript">

    var RSTAJAXURL = '<?php echo RSTAJAXURL?>';

</script>

<link rel='stylesheet' type='text/css'
      href='<?php echo RSTPLN_FULCKURL ?>libs/css/smoothness/jquery-ui-1.9.2.custom.css'/>

<script type='text/javascript' src='<?php echo RSTPLN_FULCKURL ?>libs/jquery-ui-1.9.2.custom.min.js'></script>


<link rel='stylesheet' type='text/css' href='<?php echo RSTPLN_FULCKURL ?>/fullcalendar/fullcalendar.css'/>

<link rel='stylesheet' type='text/css' href='<?php echo RSTPLN_FULCKURL ?>/fullcalendar/fullcalendar.print.css'
      media='print'/>


<script type='text/javascript' src='<?php echo RSTPLN_FULCKURL ?>/fullcalendar/fullcalendar.min.js'></script>

<script type='text/javascript'>


jQuery(document).ready(function () {


    var calendar = jQuery('#calendar').fullCalendar({

        header: {

            left: 'prev,next today',

            center: 'title',

            right: 'month,agendaWeek,agendaDay'

        },

        selectable: true,

        selectHelper: true,

        lazyFetching: false,

        events: {
            url: RSTAJAXURL,
            type: 'POST',
            data: {
                action: 'get_events'

            }

        },


        eventClick: function (event, element) {

            ///////////////////// edit event //////////////////


            view = calendar.fullCalendar('getView');

            allday = false;

            if (view.name == 'month') {

                allday = true;

            }

            var $dialogContent = jQuery("#event_edit_container");

            resetForm($dialogContent);


            var startField = $dialogContent.find("input[name='start']").val(event.start);

            if (view.name == 'month') {

                //var endField = $dialogContent.find("input[name='end']").val(event.start);
				var endField = $dialogContent.find("input[name='end']").val(event.end);

            } else {

                var endField = $dialogContent.find("input[name='end']").val(event.end);


            }


            var titleField = $dialogContent.find("input[name='title']").val(event.title);

            var bodyField = $dialogContent.find("textarea[name='body']").val(event.body);
            var orient = $dialogContent.find("select[name='orient']").val(event.orient);
            ;


            $dialogContent.dialog({

                modal: true,

                title: "New Show",

                width: 500,

                close: function () {

                    $dialogContent.dialog("destroy");

                    $dialogContent.hide();

                    calendar.fullCalendar("removeUnsavedEvents");

                },

                buttons: {

                    save: function () {


                        event.start = startField.val();

                        event.end = endField.val();

                        event.title = titleField.val();

                        event.body = bodyField.val();
                        event.orient = orient.val();

                        event.allday = allday;

                        calendar.fullCalendar("updateEvent", event);
                        var startdate = jQuery.fullCalendar.formatDate(event.start, "yyyy-MM-dd H:mm:ss");
                        var enddate = jQuery.fullCalendar.formatDate(event.end, "yyyy-MM-dd H:mm:ss");

                        jQuery.post(RSTAJAXURL,

                            {
                                action: 'update',
                                'id': event.id,

                                'start': startdate,

                                'end': enddate,

                                'body': bodyField.val(),

                                'title': titleField.val(),

                                'allday': allday,
                                'orient': orient.val(),
                                'status': 'empty'

                            }, function (data) {

                                calendar.fullCalendar('rerenderEvents');


                            });


                        $dialogContent.dialog("close");


                    },

                    "delete": function () {

                        jQuery.post(RSTAJAXURL,

                            {

                                action: 'delete',

                                'id': event.id

                            });

                        calendar.fullCalendar("removeEvent", event.id);

                        calendar.fullCalendar('refetchEvents');

                        $dialogContent.dialog("close");


                    },


                    cancel: function () {

                        $dialogContent.dialog("close");

                    }

                }

            }).show();

            calendar.fullCalendar('rerenderEvents');

        },

        ///////////////////// edit event end  //////////////////

        eventDrop: function (event, delta) {

            alert(event.title + ' was moved ' + delta + ' days\n' +

                '(should probably update your database)');

        },

        select: function (start, end, allDay) {



            ///////////////////// New event //////////////////

            view = calendar.fullCalendar('getView');

            allday = false;

            if (view.name == 'month') {

                allday = true;

            }

            var $dialogContent = jQuery("#event_edit_container");

            resetForm($dialogContent);

            var startField = $dialogContent.find("input[name='start']").val(start);

            var endField = $dialogContent.find("input[name='end']").val(end);

            var titleField = $dialogContent.find("input[name='title']");

            var bodyField = $dialogContent.find("textarea[name='body']");
            var orient = $dialogContent.find("select[name='orient']");


            $dialogContent.dialog({

                modal: true,

                title: "New Show",

                width: 500,

                close: function () {

                    $dialogContent.dialog("destroy");

                    $dialogContent.hide();

                    calendar.fullCalendar("removeUnsavedEvents");

                },

                buttons: {

                    save: function () {


                        start = new Date(startField.val());

                        end = new Date(endField.val());

                        title = titleField.val();

                        body = bodyField.val();
                        orient = orient.val();
                        if (title) {

                            eventid = '';
                            var startdate = jQuery.fullCalendar.formatDate(start, "yyyy-MM-dd H:mm:ss");
                            var enddate = jQuery.fullCalendar.formatDate(end, "yyyy-MM-dd H:mm:ss");

                            jQuery.post(RSTAJAXURL,

                                {
                                    action: 'save',
                                    start: startdate,

                                    end: enddate,

                                    body: bodyField.val(),

                                    title: titleField.val(),

                                    allday: allday,
                                    orient: orient,
                                    status: 'empty'



                                },

                                function (data) {

                                    eventid = data;

         
                                    calendar.fullCalendar('renderEvent',

                                        {

                                            id: eventid,

                                            title: title,

                                            start: start,

                                            end: end,

                                            body: body,
                                            orient: orient,
                                            allDay: allDay



                                        }


                                    );

                                    calendar.fullCalendar('rerenderEvents');

                                }

                            )


                        }

                        calendar.fullCalendar('unselect');


                        $dialogContent.dialog("close");


                    },

                    cancel: function () {

                        $dialogContent.dialog("close");

                    }

                }

            }).show();


        },


        loading: function (bool) {

            if (bool) jQuery('#loading').show();

            else jQuery('#loading').hide();

        }



    });


});

function resetForm($dialogContent) {

    $dialogContent.find("input").val("");

    $dialogContent.find("textarea").val("");

}

</script>

<body>

<div id='loading' style='display:none'>loading...</div>

<div id='calendar'
     style="width: 900px !important;background-color: #EDEDED !important;border:1px solid black !important; padding: 3px !important;"></div>

<div id="event_edit_container" style="display: none;">

    <form>

        <input type="hidden"/>

        <ul>


            <li>

                <label for="start">Start Time: </label><input type="text" name="start" disabled="disabled" size="60"/>

            </li>

            <li>

                <label for="end">End Time: </label><input type="text" name="end" disabled="disabled" size="60"/>

            </li>

            <li>

                <label for="title">Show Name: </label><input type="text" name="title" size="20"/>

            </li>
            <li>

                <label for="title">Orientation: </label><select name="orient" id="orient">
                    <option value="0">Left-to-Right</option>
                    <option value="1">Right-to-LEft</option>
                </select>

            </li>
            <li>

                <label for="body">Venue: </label><textarea name="body" cols="40" rows="10"></textarea>

            </li>

        </ul>

    </form>

</div>