export const ViewNotifications = {

    idButton: '#notifications-button',
    idCounter: '.notifications-counter',
    idDropDown: '#notifications-down',

    construct() {
        $(ViewNotifications.idButton).on('click', () => {
            CtrlNotifications.ajax_exec('read');
            CtrlNotifications.ajax_exec('update.counter', ViewNotifications.idCounter);
        });
        setInterval(() => CtrlNotifications.update_notification(), 60000);
    }

};

export const CtrlNotifications = {

    construct() {
        ViewNotifications.construct();
        CtrlNotifications.update_notification();
    },

    update_notification() {
        if ($(ViewNotifications.idDropDown).css('display') === 'none') {
            CtrlNotifications.ajax_exec('update.notifications', ViewNotifications.idDropDown);
            CtrlNotifications.ajax_exec('update.counter', ViewNotifications.idCounter);
        }
    },

    ajax_exec: function (action, target, type) {

        if(type === undefined) type = 0;

        const page = 'notifications';

        const data = "a-request=" + page + "&a-action=" + action;

        $.ajax({
            url: '/app/ajax/switch.php',
            type: 'POST',
            data: data,
            dataType: 'html',
            success: function (result) {
                if(target !== undefined) {
                    if (type === 0) {
                        $(target).html(result);
                    }
                    else if (type === 1) {
                        $(result).appendTo(target)
                    }
                }
            },

            error: function (result, status, error) {
            },

            complete: function (result, status) {
            }

        });

    }

};

CtrlNotifications.construct();