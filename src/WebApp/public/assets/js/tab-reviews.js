import './ajax-reviews.js';

$('textarea').elastic();

$(document).ready(function () {
    $('#form_new_review_text').characterCounter();
});

$('#form_new_review_text').keypress(function () {
    var lenght = $('#form_new_review_text').val().length;
    var max = $('#form_new_review_text').attr('data-length');
    if(lenght >= max) {
        $('.character-counter').addClass('red-text');
    } else {
        $('.character-counter').removeClass('red-text');
    }
});

$('.star-note').hover(function() {
    var note = $(this).attr('data-note');
    var noteInput = $('#form_new_review_note').val();
    var tab = ['one', 'two', 'three', 'for', 'five'];
    for (let i = 0; i < tab.length; i++) {
        if ((i + 1) > note) {
            $('.star-note.' + tab[i]).html('star_border');
        } else {
            $('.star-note.' + tab[i]).html('star');
        }
    }

    $(this).click(function() {
        $('#form_new_review_note').val(note);
        noteInput = $('#form_new_review_note').val();
    });

    $(this).mouseout(function() {
        if(noteInput > 0 && noteInput <= 5) {
            for (let i = 0; i < tab.length; i++) {
                if ((i + 1) > noteInput) {
                    $('.star-note.' + tab[i]).html('star_border');
                } else {
                    $('.star-note.' + tab[i]).html('star');
                }
            }
        } else {
            $('.star-note').html('star_border');
        }

    })

});

