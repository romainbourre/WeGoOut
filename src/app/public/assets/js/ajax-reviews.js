function Listener() {

    $('#form_new_review_send').click(function() {
        let run = true;
        const note = $('#form_new_review_note').val();
        if(note <= 0 && note > 5) { // CHECK DATA
            run = false;
            $('#feedback_review').html('Vous devez saisir une note valide');
        }
        if(run) saveReview()
    });

    setInterval("updateReviews(false)", 60000);

}
Listener();

function saveReview() {

    const page = 'event';
    const action = 'reviews.new';

    const data = "a-request=" + page + "&a-action=" + action + "&id=" + $_GET('id') + "&" + $('#form_new_review').serialize();

    $.ajax({
        url: '/app/ajax/switch.php',
        type: 'POST',
        data: data,
        dataType: 'html',
        success: function (result) {
            $(result).appendTo($('body'));
            $('#form_new_review')[0].reset();
            updateReviews();
            updateFormReviews();
            aj_request_update_cmd();
        },

        error: function (result, status, error) {
        },

        complete: function (result, status) {

        }
    });

}

function updateReviews(spin) {

    if(spin === undefined) spin = true;

    const target = '#list_reviews';

    const page = 'event';
    const action = 'reviews.update';

    const data = "a-request=" + page + "&a-action=" + action + "&id=" + $_GET('id');

    if(spin) $(target).html('<div class="preloader-wrapper big active" style="margin-top:25px;"><div class="spinner-layer spinner-blue-only"><div class="circle-clipper left"><div class="circle"></div> </div><div class="gap-patch"> <div class="circle"></div></div><div class="circle-clipper right"> <div class="circle"></div></div></div></div>' + $(target).html());

    $.ajax({
        url: '/app/ajax/switch.php',
        type: 'POST',
        data: data,
        dataType: 'html',
        success: function (result) {
            updateFormReviews();
            $(target).html(result);
        },

        error: function (result, status, error) {
        },

        complete: function (result, status) {

        }
    });

}

function updateFormReviews() {

    const target = '#form_reviews';

    const page = 'event';
    const action = 'reviews.form';

    const data = "a-request=" + page + "&a-action=" + action + "&id=" + $_GET('id');

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