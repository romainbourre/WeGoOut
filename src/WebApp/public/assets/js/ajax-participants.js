import {aj_request_update_numb_part} from '../../app/ajax/a-listevent.js'

function sendInvitation() {

    const source = '#send-invitation';

    const page = 'event';
    const action = 'part.invite';

    const url = window.location.href;
    const id = url.substring(url.lastIndexOf('/') + 1)
    const head = "a-request=" + page + "&a-action=" + action + "&id=" + id;

    const data = head + "&" + $(source).serialize();

    $.ajax({
        url: '/app/ajax/switch.php',
        type: 'POST',
        data: data,
        dataType: 'html',
        success: function () {
            $(source)[0].reset();
        },

        error: function (result, status, error) {
        },

        complete: function () {
            aj_request_update_numb_part();
            updateParticipantsList($('#coll_part_list .collection').attr('data-level'));
            updateParticipantsFilter();
        }
    });

}

function acceptParticipant(userId) {

    const page = 'event';
    const action = 'part.accept';

    const url = window.location.href;
    const id = url.substring(url.lastIndexOf('/') + 1)
    const data = "a-request=" + page + "&a-action=" + action + "&id=" + id + "&userId=" + userId;

    $.ajax({
        url: '/app/ajax/switch.php',
        type: 'POST',
        data: data,
        dataType: 'html',
        success: function (result, status) {
        },

        error: function (result, status, error) {
        },

        complete: function (result, status) {
            aj_request_update_numb_part();
            updateParticipantsList($('#coll_part_list .collection').attr('data-level'));
            updateParticipantsFilter();
        }
    });

}

function deleteParticipant(userId) {

    const page = 'event';
    const action = 'part.delete';

    const url = window.location.href;
    const id = url.substring(url.lastIndexOf('/') + 1)
    const data = "a-request=" + page + "&a-action=" + action + "&id=" + id + "&userId=" + userId;

    $.ajax({
        url: '/app/ajax/switch.php',
        type: 'POST',
        data: data,
        dataType: 'html',
        success: function (result, status) {
        },

        error: function (result, status, error) {
        },

        complete: function () {
            aj_request_update_numb_part();
            updateParticipantsList($('#coll_part_list .collection').attr('data-level'));
            updateParticipantsFilter();
        }
    });

}

function updateParticipantsFilter() {

    const target = '#coll_part_filter';

    const page = 'event';
    const action = 'filter.update';

    const url = window.location.href;
    const id = url.substring(url.lastIndexOf('/') + 1)
    const data = "a-request=" + page + "&a-action=" + action + "&id=" + id;

    $.ajax({
        url: '/app/ajax/switch.php',
        type: 'POST',
        data: data,
        dataType: 'html',
        success: function (result) {
            $(target).html(result);
        },

        error: function (result, status, error) {
        },

        complete: function () {
            $('#part_filter_all').click(function() {
                updateParticipantsList('all');
            });
            $('#part_filter_valid').click(function() {
                updateParticipantsList('valid');
            });
            $('#part_filter_inv').click(function() {
                updateParticipantsList('invited');
            });
            $('#part_filter_wait').click(function() {
                updateParticipantsList('wait');
            });
            $('#coll_part_filter .btn-flat').click(function() {
                $('#coll_part_filter .btn-flat').removeClass('on');
                $(this).addClass('on');
            });
        }
    });

}

function updateParticipantsList(filter) {

    const target = '#coll_part_list';

    const page = 'event';
    const action = 'filter.' + filter;

    const url = window.location.href;
    const id = url.substring(url.lastIndexOf('/') + 1)
    const data = "a-request=" + page + "&a-action=" + action + "&id=" + id;

    $.ajax({
        url: '/app/ajax/switch.php',
        type: 'POST',
        data: data,
        dataType: 'html',
        success: function (result) {
            $(target).html(result);
        },

        error: function (result, status, error) {
        },

        complete: function () {
            $('.part_set_valid').click(function() {
                acceptParticipant($(this).attr('data-id'));
            });
            $('.part-set-delete').click(function() {
                deleteParticipant($(this).attr('data-id'));
            });
        }
    });

}

$('#part_filter_all').click(function() {
   updateParticipantsList('all');
});

$('#part_filter_valid').click(function() {
    updateParticipantsList('valid');
});

$('#part_filter_inv').click(function() {
    updateParticipantsList('invited');
});

$('#part_filter_wait').click(function() {
    updateParticipantsList('wait');
});

$('.part_set_valid').click(function() {
    acceptParticipant($(this).attr('data-id'));
});

$('.part-set-delete').click(function() {
   deleteParticipant($(this).attr('data-id'));
});

$('#coll_part_filter .btn-flat').click(function() {
   $('#coll_part_filter .btn-flat').removeClass('on');
   $(this).addClass('on');
});

$('#event-participant-sendGuest-submit').on('click', function() {
    sendInvitation();
});