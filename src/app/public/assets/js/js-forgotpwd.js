var ViewForgot = {

    idForm: '#forgot-password-form',
    idEmail: '#forgot-password-email-field',
    idEmailFeedback: '#forgot-password-email-feedback',
    idSubmit: '#forgot-password-form-submit',

    construct: function () {

        ViewForgot.layoutCSS();

        $(window).resize(function () {
            ViewForgot.layoutCSS();
        });

        $(ViewForgot.idSubmit).on('click', function() {
            CtrlForgot.send_form();
        });

    },

    layoutCSS: function() {

        var mainHeight = $('main').css('height');
        var cardValidationHeight = $(ViewForgot.idForm).css('height');
        if (window.innerWidth > 1200) {
            $(ViewForgot.idForm).css('margin-top', 'calc((' + mainHeight + ' - ' + cardValidationHeight + ')/2)');
        }
        else {
            $(ViewForgot.idForm).css('margin-top', '75px');
            $(ViewForgot.idForm).css('margin-bottom', '75px');

        }
        if(window.innerWidth < 600) {
            $(ViewForgot.idForm).css('margin-top', '0px');
            $(ViewForgot.idForm).css('margin-bottom', '0px');
        }
        else {
            $(ViewForgot.idForm).css('margin-bottom', 'calc((' + mainHeight + ' - ' + cardValidationHeight + ')/2)');
        }

    },

    get_email: function() {
        return $(ViewForgot.idEmail).val();
    },

    set_email: function(email) {
        $(ViewForgot.idEmail).val(email);
    },

    set_email_feedback: function(feedback) {
        $(ViewForgot.idEmailFeedback).html(feedback);
    },

};

var CtrlForgot = {

    construct: function () {
        ViewForgot.construct();
        CtrlForgot.construct_checking();
    },

    construct_checking: function () {

        $(ViewForgot.idEmail).on('change, blur', function() {
            CtrlForgot.check_email();
        });

        $('input').on('change, keyup, blur', function() {
            CtrlForgot.global_checking(false);
        });

    },

    send_form: function() {
        if(CtrlForgot.global_checking()) {
            $(ViewForgot.idForm).submit();
        }
    },

    check_email: function (display) {
        if(display === undefined) display = true;
        var email = ViewForgot.get_email();
        var reg = new RegExp('^[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*@[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*[\.]{1}[a-z]{2,7}$', 'i');
        if(!reg.test(email)) {
            if(display) {
                ViewForgot.set_email_feedback('Adresse e-mail invalide');
                $(ViewForgot.idEmail).removeClass('valid');
                $(ViewForgot.idEmail).addClass('invalid');
            }
            return false;
        }
        else {
            if(display) {
                ViewForgot.set_email_feedback('');
                $(ViewForgot.idEmail).removeClass('invalid');
                $(ViewForgot.idEmail).addClass('valid');
            }
            return true;
        }
    },

    /**
     * Run all of checking functions
     * @param display true(default)|false displaying error messages
     */
    global_checking: function (display) {
        if(display === undefined) display = true;
        var run = true;
        if(!CtrlForgot.check_email(display)) run = false;
        if(run) {
            $(ViewForgot.idSubmit).removeClass('disabled');
            return true;
        }
        else {
            $(ViewForgot.idSubmit).addClass('disabled');
            return false;
        }
    }

};

CtrlForgot.construct();