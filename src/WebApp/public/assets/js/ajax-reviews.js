import {aj_request_update_cmd} from "../../app/ajax/a-listevent.js";

(function () {
    $('#form_new_review_send').click(function () {
        let run = true;
        const note = $('#form_new_review_note').val();
        if (note <= 0 && note > 5) { // CHECK DATA
            run = false;
            $('#feedback_review').html('Vous devez saisir une note valide');
        }
        if (run) {
            saveReview()
        }
    });
    setInterval(() => updateReviews(false), 60000);
})();

function saveReview() {
    const action = 'reviews.reviews.new';
    const currentUrl = window.location.href;
    const eventId = currentUrl.substring(currentUrl.lastIndexOf('/') + 1);
    const request = `a-action=${action}&${$('#form_new_review').serialize()}`;

    $.ajax({
        url: `/app/ajax/switch.php/api/events/${eventId}`,
        type: 'POST',
        data: request,
        dataType: 'html',
        success(result) {
            $(result).appendTo($('body'));
            $('#form_new_review')[0].reset();
            updateReviews();
            updateFormReviews();
            aj_request_update_cmd();
        },

        error(result, status, error) {
        },

        complete(result, status) {
        }
    });
}

function updateReviews(spin = true) {
    const target = '#list_reviews';
    const action = 'reviews.reviews.update';
    const currentUrl = window.location.href;
    const eventId = currentUrl.substring(currentUrl.lastIndexOf('/') + 1);
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
            updateFormReviews();
            $(target).html(result);
        },

        error(result, status, error) {
        },

        complete(result, status) {
        }
    });

}

function updateFormReviews() {
    const target = '#form_reviews';
    const action = 'reviews.reviews.form';
    const currentUrl = window.location.href;
    const eventId = currentUrl.substring(currentUrl.lastIndexOf('/') + 1)
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