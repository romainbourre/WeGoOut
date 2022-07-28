function Listener() {

    $('#form_new_publication_send').click(function() {
        let run = true;
        if($('#form_new_publication_text').val() == "") {
            run = false;
        }
        if(run) savePublication();
    });

    setInterval("updatePublications(false)", 60000);

}
Listener();

function updatePublications(spin) {

    if(spin === undefined) spin = true;

    const target = '#list_publications';

    const page = 'event';
    const action = 'publications';

    const url = window.location.href;
    const id = url.substring(url.lastIndexOf('/') + 1)
    const data = "a-request=" + page + "&a-action=" + action + "&id=" + id;

    if(spin) $(target).html('<div class="preloader-wrapper big active" style="margin-top:25px;"><div class="spinner-layer spinner-blue-only"><div class="circle-clipper left"><div class="circle"></div> </div><div class="gap-patch"> <div class="circle"></div></div><div class="circle-clipper right"> <div class="circle"></div></div></div></div>' + $(target).html());

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

        complete: function (result, status) {

        }
    });

}

function savePublication() {

    const page = 'event';
    const action = 'new.publication';

    const url = window.location.href;
    const id = url.substring(url.lastIndexOf('/') + 1)
    const data = "a-request=" + page + "&a-action=" + action + "&id=" + id + "&" + $('#form_new_publication').serialize();

    $.ajax({
        url: '/app/ajax/switch.php',
        type: 'POST',
        data: data,
        dataType: 'html',
        success: function () {
            $('#form_new_publication')[0].reset();
            updatePublications();
        },

        error: function (result, status, error) {
        },

        complete: function (result, status) {

        }
    });

}

