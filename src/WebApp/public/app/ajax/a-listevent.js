import {getFormData} from "../../assets/js/form.js";

export function aj_request_update_numb_part() {
    const target = '#sheet_event_part';
    const action = 'update.partitem';
    const url = window.location.href;
    const eventId = url.substring(url.lastIndexOf('/') + 1)
    const request = `a-action=${action}`;

    $.ajax({
        url: `/app/ajax/switch.php/api/events/${eventId}`,
        type: 'POST',
        data: request,
        dataType: 'html',
        success(result) {
            $(target).html(result);
        },

        error(result, status, error) {
        },

        complete(result, status) {
        }
    });
}

export function aj_request_update_cmd() {
    const target = '#sheet_event_cmd';
    const action = 'update.cmd';
    const url = window.location.href;
    const eventId = url.substring(url.lastIndexOf('/') + 1)
    const data = `a-action=${action}`;

    $.ajax({
        url: `/app/ajax/switch.php/api/events/${eventId}`,
        type: 'POST',
        data: data,
        dataType: 'html',
        success(result) {
            $(target).html(result);
            aj_request_update_window()
        },

        error(result, status, error) {
        },

        complete() {
            $('#sheet_event_cmd_wait').click(function () {
                aj_request_change_registration();
            });
            $('#sheet_event_cmd_registration').click(function () {
                aj_request_change_registration();
            });
        }
    });
}

export function aj_request_update_window() {
    const targetID = '#sheet_event_window';
    const action = 'update.window';
    const url = window.location.href;
    const eventId = url.substring(url.lastIndexOf('/') + 1)
    const request = `a-action=${action}`;

    $(targetID).html('<div class="preloader-wrapper big active"><div class="spinner-layer spinner-blue-only"><div class="circle-clipper left"><div class="circle"></div> </div><div class="gap-patch"> <div class="circle"></div></div><div class="circle-clipper right"> <div class="circle"></div></div></div></div>');

    $.ajax({
        url: `/app/ajax/switch.php/api/events/${eventId}`,
        type: 'POST',
        data: request,
        dataType: 'html',
        success(result) {
            $(targetID).html(result);
        },

        error(result, status, error) {
        },

        complete() {

            $(document).ready(function () {
                $('ul.tabs').tabs();
            });

            $('#part_filter_all').click(function () {
                updateParticipantsList('all');
            });

            $('#part_filter_valid').click(function () {
                updateParticipantsList('valid');
            });

            $('#part_filter_inv').click(function () {
                updateParticipantsList('invited');
            });

            $('#part_filter_wait').click(function () {
                updateParticipantsList('wait');
            });

            $('.part_set_valid').click(function () {
                acceptParticipant($(this).attr('data-id'));
            });

            $('.part-set-delete').click(function () {
                deleteParticipant($(this).attr('data-id'));
            });

            $('#form_new_publication_send').click(function () {
                savePublication();
            });
            $('#coll_part_filter .btn-flat').click(function () {
                $('#coll_part_filter .btn-flat').removeClass('on');
                $(this).addClass('on');
            });
        }
    });

}

export function aj_request_change_registration() {
    const url = window.location.href;
    const id = url.substring(url.lastIndexOf('/') + 1)

    $.ajax({
        url:  `/events/${id}/register`,
        type: 'PUT',
        success: function () {
            aj_request_update_cmd();
        },

        error: function (result, status, error) {
        },

        complete: function (result, status) {
        }
    });
}

export function aj_request_list_event(filterData, secret = false) {

    const targetID = '#list_events';

    if (!secret) {
        $(targetID).html('<div class="preloader-wrapper big active"><div class="spinner-layer spinner-blue-only"><div class="circle-clipper left"><div class="circle"></div> </div><div class="gap-patch"> <div class="circle"></div></div><div class="circle-clipper right"> <div class="circle"></div></div></div></div>');
    }

    $.ajax({
        url: '/events/view',
        type: 'POST',
        data: JSON.stringify(filterData),
        contentType: 'application/json',
        dataType: 'html',
        success: function (result) {
            $(targetID).html(result);
        },

        error: function (result, status, error) {
        },

        complete: function (result, status) {

        }
    });
}


export function filterList() {
    const filterData = getFormData($('#list_form_filter'));
    aj_request_list_event(filterData);
}