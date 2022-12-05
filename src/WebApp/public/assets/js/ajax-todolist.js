import {getEventId} from "./one-event.js";

const initListTask = () => {
    const taskItemSelector = '.task-item > *:not(".task-checkbox, .task-actor")';
    $(taskItemSelector).on('click', function () {
        loadSlideOfTask($(this).parent().attr('data-task'));
    });

    $('.task-item .task-actor .btn').on('click', function () {
        const taskId = $(this).parent().parent('.task-item').attr('data-task');
        saveUserDesignated(taskId);
    });

    $('#task-list-content > .task-item .task-checkbox').one('click', function () {
        checkTask(this);
    });
};
initListTask();

function iniTask(button, full) {

    if (full === undefined) full = true;

    if (full === true) {
        $(button + ' #task-slide-out #task-close').sideNav({
            menuWidth: 300, // Default is 300
            edge: 'right', // Choose the horizontal origin
            closeOnClick: false, // Closes side-nav on <a> clicks, useful for Angular/Meteor
            draggable: true, // Choose whether you can drag to open on touch screens,
            onClose: function () {
                $('#task-slide-out').remove();
            } // A function to be called when sideNav is closed
        });
    }

    $('#task-form-edit .datepicker').pickadate({
        monthsFull: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
        monthsShort: ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Dec'],
        weekdaysFull: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
        weekdaysShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
        today: 'aujourd\'hui',
        clear: 'effacer',
        close: '',
        formatSubmit: 'dd/mm/yyyy',
        selectMonths: true, // Creates a dropdown to control month
        selectYears: 15, // Creates a dropdown of 15 years to control year

        // Formats
        format: 'dd/mm/yyyy',

        // Close on a user action
        closeOnSelect: true,
        closeOnClear: true,

        // Events
        onStart: undefined,
        onRender: undefined,
        onOpen: undefined,
        onClose: undefined,
        onStop: undefined,

        // An integer (positive/negative) sets it relative to today.
        min: true
        // `true` sets it to today. `false` removes any limits.
    });

    $('#task-form-edit input, #task-form-edit select').on('change', function() {
        saveTask();
    });

    $('#task-form-edit textarea').on('input', function() {
        saveTask()
    });

    $('#task-form-edit #task-delete').on('click', function() {
       deleteTask();
    });

}


function loadSlideOfTask(taskId) {
    const target = '#tab-task-content';
    const eventId = getEventId();
    const action = 'todolist.task.load';
    const request = `a-action=${action}&task=${taskId}`;
    const slide = $('#task-slide-out');

    $.ajax({
        url: `/app/ajax/switch.php/api/events/${eventId}`,
        type: 'POST',
        data: request,
        dataType: 'html',
        success(result) {
            if (slide.html() === undefined) {
                $(result).appendTo($(target));
            } else {
                slide.html($(result).html());
            }
            iniTask(target, (slide.html() === undefined));
        },

        error() {
        },

        complete() {
            $(document).ready(function () {
                $('#task-form-edit select').material_select();
            });
            if (slide.html() === undefined) $(target + ' #task-slide-out #task-close').sideNav('show');
        }
    });

}

function addTask() {
    const form = '#task-form-add';
    const action = 'todolist.task.add';
    const eventId = document.getElementById('event_id').value;
    const request = `a-action=${action}&${$(form).serialize()}`;

    $.ajax({
        url: `/app/ajax/switch.php/api/events/${eventId}`,
        type: 'POST',
        data: request,
        dataType: 'html',
        success(result) {
            $(result).appendTo($('#tab-task-content'));
        },

        error() {
        },

        complete() {
            updateTaskList();
            $(form)[0].reset();
        }
    });
}



function saveTask() {
    const form = '#task-form-edit';
    const taskId = $(form).attr('data-task');
    const action = 'todolist.task.save';
    const eventId = document.getElementById('event_id').value;
    const request = `a-action=${action}&task=${taskId}&${$(form).serialize()}`;

    $.ajax({
        url: `/app/ajax/switch.php/api/events/${eventId}`,
        type: 'POST',
        data: request,
        dataType: 'html',
        success(result) {
            $(result).appendTo($('#tab-task-content'));
        },

        error() {
        },

        complete() {
            updateTaskList();
            loadSlideOfTask(taskId);
        }
    });
}

function saveUserDesignated(numTask) {
    const eventId = getEventId();
    const action = 'todolist.task.user.set';
    const request = `a-action=${action}&task=${numTask}`;

    $.ajax({
        url: `/app/ajax/switch.php/api/events/${eventId}`,
        type: 'POST',
        data: request,
        dataType: 'html',
        success(result) {
            $(result).appendTo($('#tab-task-content'));
        },

        error() {
        },

        complete() {
            updateTaskList();
        }
    });
}

function deleteTask() {
    const form = '#task-form-edit';
    const taskId = $(form).attr('data-task');
    const eventId = getEventId();
    const action = 'todolist.task.delete';
    const request = `a-action=${action}&task=${taskId}`;

    $.ajax({
        url: `/app/ajax/switch.php/api/events/${eventId}`,
        type: 'POST',
        data: request,
        dataType: 'html',
        success(result) {
            $(result).appendTo($('#tab-task-content'));
        },

        error() {
        },

        complete() {
            $('#tab-task-content #task-slide-out #task-close').sideNav('destroy');
            updateTaskList();
        }
    });
}

function checkTask(task) {
    const eventId = getEventId();
    const taskId = $(task).parent().attr('data-task');
    const action = 'todolist.task.check';
    const request = `a-action=${action}&task=${taskId}`;

    $.ajax({
        url: `/app/ajax/switch.php/api/events/${eventId}`,
        type: 'POST',
        data: request,
        dataType: 'html',
        success(result) {
            $(result).appendTo($('#tab-task-content'));
        },

        error() {
        },

        complete() {
            updateTaskList();
        }
    });
}

function updateTaskList() {
    const target = '#task-list-content';
    const action = 'todolist.task.list.update';
    const eventId = document.getElementById('event_id').value;
    const request = `a-action=${action}`;

    $.ajax({
        url: `/app/ajax/switch.php/api/events/${eventId}`,
        type: 'POST',
        data: request,
        dataType: 'html',
        success(result) {
            $(target).html(result);
        },

        error() {
        },

        complete() {
            initListTask();
        }
    });

}

$('#task-form-add #task-add-label').on('focus', function() {
    $('#task-add-label').on('keyup', function(e) {
        if(e.keyCode === 13) { // KeyCode de la touche entrée
            addTask();
            $('#task-form-add').off('keyup');
        }
    });
});

$('#task-form-add #task-add-label').on('blur', function () {
    $('#task-form-add').off('keyup');
});

export {addTask}
