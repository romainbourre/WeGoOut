export function Listener() {

    $('#user_send_request').click(function() {
        aj_request_profile_exec('friend.send');
    });

    $('#user_cancel_send').click(function() {
        aj_request_profile_exec('friend.cancel.send')
    });

    $('.user_accept').click(function() {
        const id = $(this).attr('data-id');
        if(id === null || id === undefined) {
            aj_request_profile_exec('friend.accept');
        }
        else {
            const data = '&friendId=' + id;
            aj_request_profile_exec('friend.accept', data);
        }
    });

    $('.user_refuse').click(function() {
        const id = $(this).attr('data-id');
        if(id === null || id === undefined) {
            aj_request_profile_exec('friend.refuse');
        }
        else {
            const data = '&friendId=' + id;
            aj_request_profile_exec('friend.refuse', data);
        }
    });

    $('.user_delete').click(function() {
        const id = $(this).attr('data-id');
        if (id === null || undefined) {
            aj_request_profile_exec('friend.delete');
        }
        else {
            const data = '&friendId=' + id;
            aj_request_profile_exec('friend.delete', data);

        }
    });

}

function aj_request_profile_view(action, target) {

    const page = 'profile';

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
            Listener();
        }
    });

}

function aj_request_profile_exec(action, data) {

    if(data === undefined) data = "";

    const page = 'profile';

    const url = window.location.href;
    const id = url.substring(url.lastIndexOf('/') + 1)
    data = "a-request=" + page + "&a-action=" + action + "&id=" + id + data;

    $.ajax({
        url: '/app/ajax/switch.php',
        type: 'POST',
        data: data,
        dataType: 'html',
        success: function () {
            aj_request_profile_view('friend.update', '#head_user_friend');
            aj_request_profile_view('content.update', '#profile_content');
        },

        error: function (result, status, error) {
        },

        complete: function (result, status) {
        }
    });

}



