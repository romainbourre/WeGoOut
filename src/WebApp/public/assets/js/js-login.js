var ViewLogin = {

    idForm: '#login-user-form',
    idEmail: '#login-user-email-field',
    idEmailFeedback: '#login-user-email-feedback',
    idPassword: '#login-user-password-field',
    idPasswordFeedback: '#login-user-password-feedback',
    idSubmit: '#login-user-form-submit',

    construct: function () {

        ViewLogin.layoutCSS();

        $(window).resize(function () {
            ViewLogin.layoutCSS();
        });

    },

    layoutCSS: function() {

        var mainHeight = $('main').css('height');
        var cardValidationHeight = $(ViewLogin.idForm).css('height');
        if (window.innerWidth > 1200) {
            $(ViewLogin.idForm).css('margin-top', 'calc((' + mainHeight + ' - ' + cardValidationHeight + ')/2)');
        }
        else {
            $(ViewLogin.idForm).css('margin-top', '75px');
            $(ViewLogin.idForm).css('margin-bottom', '75px');

        }
        if(window.innerWidth < 600) {
            $(ViewLogin.idForm).css('margin-top', '0px');
            $(ViewLogin.idForm).css('margin-bottom', '0px');
        }
        else {
            $(ViewLogin.idForm).css('margin-bottom', 'calc((' + mainHeight + ' - ' + cardValidationHeight + ')/2)');
        }

    },

    get_email: function() {
        return $(ViewLogin.idEmail).val();
    },

    set_email: function(email) {
        $(ViewLogin.idEmail).val(email);
    },

    set_email_feedback: function(feedback) {
        $(ViewLogin.idEmailFeedback).html(feedback);
    },

    get_password_length: function () {
        return $(ViewLogin.idPassword).val().length;
    },

    set_password_feedback: function(feedback) {
        $(ViewLogin.idPasswordFeedback).html(feedback);
    }

};

var CtrlLogin = {

    construct: function () {
        ViewLogin.construct();
        CtrlLogin.construct_checking();
        $(ViewLogin.idSubmit).on('click', function() {
            CtrlLogin.send_form();
        });
    },

    construct_checking: function () {

        $(ViewLogin.idEmail).on('change, blur', function() {
            CtrlLogin.check_email();
        });

        $(ViewLogin.idPassword).on('change, blur', function() {
            CtrlLogin.check_password();
        });

        $('input').on('change, keyup, blur', function() {
           CtrlLogin.global_checking(false);
        });

    },

    send_form: function() {
        if(CtrlLogin.global_checking()) {
            $(ViewLogin.idForm).submit();
        }
    },

    check_email: function (display) {
        if(display === undefined) display = true;
        var email = ViewLogin.get_email();
        var reg = new RegExp('^\\w+([\+\.-]?\\w+)*@\\w+([\.-]?\\w+)*(\\.\\w{2,3})+$', 'i');
        if(!reg.test(email)) {
            if(display) {
                ViewLogin.set_email_feedback('Adresse e-mail invalide');
                $(ViewLogin.idEmail).removeClass('valid');
                $(ViewLogin.idEmail).addClass('invalid');
            }
            return false;
        }
        else {
            if(display) {
                ViewLogin.set_email_feedback('');
                $(ViewLogin.idEmail).removeClass('invalid');
                $(ViewLogin.idEmail).addClass('valid');
            }
            return true;
        }
    },

    check_password: function(display) {
        if(display === undefined) display = true;
        var pwdLength = ViewLogin.get_password_length();
        if(pwdLength === 0) {
            if(display) {
                ViewLogin.set_password_feedback('Vous devez saisir un mot de passe');
                $(ViewLogin.idPassword).removeClass('valid');
                $(ViewLogin.idPassword).addClass('invalid');
            }
            return false;
        }
        else {
            if(display) {
                ViewLogin.set_password_feedback('');
                $(ViewLogin.idPassword).removeClass('invalid');
                $(ViewLogin.idPassword).addClass('valid');
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
        if(!CtrlLogin.check_email(display)) run = false;
        if(!CtrlLogin.check_password(display)) run = false;
        if(run) {
            $(ViewLogin.idSubmit).removeClass('disabled');
            return true;
        }
        else {
            $(ViewLogin.idSubmit).addClass('disabled');
            return false;
        }
    }

};

CtrlLogin.construct();