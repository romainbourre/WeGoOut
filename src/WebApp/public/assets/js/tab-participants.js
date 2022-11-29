import {ViewSearch} from './js-search.js';

const ctrlTabParticipant = {

    construct: function() {

        viewTabParticipant.construct();

    },


    check_guest: function(display) {

        if(display === undefined) display = true;

        const autocomplete = $(viewTabParticipant.idFormGuest + " " + ViewSearch.classInputSearch);
        const target = $('#' + $(viewTabParticipant.idFormGuest + " " + ViewSearch.classInputSearch).attr('for'));

        let correct = true;
        if(display) viewTabParticipant.set_feedback("");

        if(autocomplete.val().length > 0) {

            if (target.val() === "") {
                const reg = new RegExp('^[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*@[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*[\.]{1}[a-z]{2,7}$', 'i');
                if (!reg.test(autocomplete.val())) {
                    correct = false;
                    if(display) viewTabParticipant.set_feedback("Vous devez choisir un utilisateur ou saisir une adresse e-mail");
                }
            }

        }

        return correct;

    }


};
const viewTabParticipant = {

    idFormGuest: "#send-invitation",
    idInvitationFeedback: "#event-participant-sendGuest-feedback",
    idButtonInvitation: "#event-participant-sendGuest-submit",

    construct: function () {

        $('textarea').elastic();

        $(viewTabParticipant.idFormGuest + " " + ViewSearch.classInputSearch).on('click focus blur', function () {
            viewTabParticipant.set_feedback("");
        });

        $(viewTabParticipant.idButtonInvitation).on('click', function () {
            ctrlTabParticipant.check_guest();
        });

    },

    set_feedback: function (text) {
        $(viewTabParticipant.idInvitationFeedback).html(text);
    }


};


ctrlTabParticipant.construct();