(function () {
    $('#form_new_publication_send').click(() => {
        if ($('#form_new_publication_text').val() !== "") {
            savePublication();
        }
    });
    setInterval(() => updatePublications(false), 60000);
})();

function updatePublications(spin = true) {
    const target = '#list_publications';
    const action = 'publications.publications';
    const currentUrl = window.location.href;
    const eventId = currentUrl.substring(currentUrl.lastIndexOf('/') + 1)
    const request = `a-action=${action}`;

    if (spin) {
        $(target).html('<div class="preloader-wrapper big active" style="margin-top:25px;"><div class="spinner-layer spinner-blue-only"><div class="circle-clipper left"><div class="circle"></div> </div><div class="gap-patch"> <div class="circle"></div></div><div class="circle-clipper right"> <div class="circle"></div></div></div></div>' + $(target).html());
    }

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

function savePublication() {
    const action = 'publications.new.publication';
    const currentUrl = window.location.href;
    const eventId = currentUrl.substring(currentUrl.lastIndexOf('/') + 1).replace('#!', '');
    const request = `a-action=${action}&${$('#form_new_publication').serialize()}`;

    $.ajax({
        url: `/app/ajax/switch.php/api/events/${eventId}`,
        type: 'POST',
        data: request,
        dataType: 'html',
        success() {
            $('#form_new_publication')[0].reset();
            updatePublications();
        },

        error: function (result, status, error) {
        },

        complete: function (result, status) {
        }
    });

}

