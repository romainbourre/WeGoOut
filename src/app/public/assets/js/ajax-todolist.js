import { getEventId } from "./one-event.js";

function initListTask() {
    const taskItemSelector = '.task-item > *:not("a, b")';
    $(taskItemSelector).on('click', function() {
        loadSlideOfTask($(this).parent().attr('data-task'));
    });

    $('.task-item .task-actor .btn').on('click', function() {
        const taskId = $(this).parent().parent('.task-item').attr('data-task');
        saveUserDesignated(taskId);
    });

    $('#task-list-content > .task-item .task-checkbox').one('click', function() {
        checkTask(this);
    });
}

function iniTask(button, full) {

    if(full === undefined) full = true;

    if(full === true) {
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
    const page = 'event';
    const action = 'task.load';

    const data = `a-request=${page}&a-action=${action}&id=${eventId}&task=${taskId}`;
    let slide;

    $.ajax({
        url: '/app/ajax/switch.php',
        type: 'POST',
        data: data,
        dataType: 'html',
        success: function (result) {
            slide = $('#task-slide-out');
            if(slide.html() === undefined) {
                $(result).appendTo($(target));
            }
            else {
                slide.html($(result).html());
            }
            iniTask(target, (slide.html() === undefined));

        },

        error: function () {
        },

        complete: function () {
            $(document).ready(function() {
                $('#task-form-edit select').material_select();
            });
            if(slide.html() === undefined) $(target + ' #task-slide-out #task-close').sideNav('show');


        }
    });

}

function addTask() {

    const form = '#task-form-add';

    const page = 'event';
    const action = 'task.add';

    const eventId = document.getElementById('event_id').value;
    const head = "a-request=" + page + "&a-action=" + action + "&id=" + eventId;

    const data = head + "&" + $(form).serialize();

    $.ajax({
        url: '/app/ajax/switch.php',
        type: 'POST',
        data: data,
        dataType: 'html',
        success: function (result) {
            $(result).appendTo($('#tab-task-content'));
        },

        error: function () {
        },

        complete: function () {
            updateTaskList();
            $(form)[0].reset();
        }

    });

}



function saveTask() {

    const form = '#task-form-edit';
    const taskId = $(form).attr('data-task');

    const page = 'event';
    const action = 'task.save';

    const eventId = document.getElementById('event_id').value;
    const request = `a-request=${page}&a-action=${action}&id=${eventId}&task=${taskId}`;
    const data = request + "&" + $(form).serialize();

    $.ajax({
        url: '/app/ajax/switch.php',
        type: 'POST',
        data: data,
        dataType: 'html',
        success: function (result) {
            $(result).appendTo($('#tab-task-content'));
        },

        error: function () {
        },

        complete: function () {
            updateTaskList();
            loadSlideOfTask(taskId);
        }

    });

}

function saveUserDesignated(numTask) {

    const form = '#task-form-edit';
    const eventId = getEventId();
    const page = 'event';
    const action = 'task.user.set';

    const request = `a-request=${page}&a-action=${action}&id=${eventId}`;
    const data = request + "&task=" + numTask;

    $.ajax({
        url: '/app/ajax/switch.php',
        type: 'POST',
        data: data,
        dataType: 'html',
        success: function (result) {
            $(result).appendTo($('#tab-task-content'));
        },

        error: function () {
        },

        complete: function () {
            updateTaskList();
        }

    });

}

function deleteTask() {

    const form = '#task-form-edit';
    const taskId = $(form).attr('data-task');

    const page = 'event';
    const action = 'task.delete';

    const request = `"a-request=${page}&a-action=${action}&id=${taskId}`;
    const data = request + "&task=" + taskId;

    $.ajax({
        url: '/app/ajax/switch.php',
        type: 'POST',
        data: data,
        dataType: 'html',
        success: function (result) {
            $(result).appendTo($('#tab-task-content'));
        },

        error: function () {
        },

        complete: function () {
            $('#tab-task-content #task-slide-out #task-close').sideNav('destroy');
            updateTaskList();
        }

    });

}

function checkTask(task) {
    const eventId = getEventId();
    const taskId = $(task).parent().attr('data-task');

    const page = 'event';
    const action = 'task.check';

    const request = `a-request=${page}&a-action=${action}&id=${eventId}`;
    const data = request + "&task=" + taskId;

    $.ajax({
        url: '/app/ajax/switch.php',
        type: 'POST',
        data: data,
        dataType: 'html',
        success: function () {
            //$(result).appendTo($('#tab-task-content'));
        },

        error: function () {
        },

        complete: function () {
            updateTaskList();
        }

    });

}

function updateTaskList() {

    const target = '#task-list-content';

    const page = 'event';
    const action = 'task.list.update';

    const eventId = document.getElementById('event_id').value;
    const data = "a-request=" + page + "&a-action=" + action + "&id=" + eventId;

    $.ajax({
        url: '/app/ajax/switch.php',
        type: 'POST',
        data: data,
        dataType: 'html',
        success: function (result) {
            $(target).html(result);
        },

        error: function () {
        },

        complete: function () {
            initListTask();
        }
    });

}

initListTask();

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
