import dateEvent from "./create-date.js";
import ViewCreateEvent from "./create-view.js";
import { getFormData } from './form.js';
import {filterList} from "../../app/ajax/a-listevent.js";

const CtrlCreateEvent = {

    dateEvent: dateEvent,

    construct: function () {
        this.modal_create();
    },

    construct_checking: function () {
        // INIT CHECKING TITLE EVENT
        $(ViewCreateEvent.idTitle).on({
            keyup: function () {
                CtrlCreateEvent.check_title();
            },
            blur: function () {
                CtrlCreateEvent.check_title();
            },
            focus: function () {
                $(ViewCreateEvent.idTitleFeedback).html('');
            }
        });

        $(ViewCreateEvent.idCategory).on('change', function () {
            CtrlCreateEvent.check_category();
            CtrlCreateEvent.global_checking(false);
        });

        $(ViewCreateEvent.idPublic + ',' + ViewCreateEvent.idPrivate + ',' + ViewCreateEvent.idSwitchGuest).on('click', function () {
            CtrlCreateEvent.check_participants();
        });

        $(ViewCreateEvent.idParticipants).on('change', function () {
            CtrlCreateEvent.check_participants();
        });

        $(ViewCreateEvent.idLocation).on('change', function () {
            CtrlCreateEvent.check_location();
        });

        // INIT CHECKING COMPLEMENTS ADDRESS EVENT
        $(ViewCreateEvent.idLocationDesc).on({
            keyup: function () {
                CtrlCreateEvent.check_address_complements();
            },
            blur: function () {
                CtrlCreateEvent.check_address_complements();
            },
            focus: function () {
                $(ViewCreateEvent.idLocationDescFeedback).html('');
            }
        });

        $('input, textarea, select').on('change, click', function () {
            CtrlCreateEvent.global_checking(false);
        });

    },

    modal_create: function () {

        const targetID = 'body';

        $.ajax({
            url: '/events/create-form',
            type: 'GET',
            dataType: 'html',
            success: function (result) {
                $(result).appendTo(targetID)
            },

            error: function (result, status, error) {
            },

            complete: function () {
                // CONSTRUCT MODAL WINDOW
                $(document).ready(function () {
                    $(ViewCreateEvent.idModal).modal({
                        dismissible: false, // Modal can be dismissed by clicking outside of the modal
                        opacity: 0.5, // Opacity of modal background
                        inDuration: 300, // Transition in duration
                        outDuration: 200, // Transition out duration
                        startingTop: '0%', // Starting top style attribute
                        endingTop: '0%', // Ending top style attribute
                        ready: function (modal, trigger) { // Callback for Modal open. Modal and trigger parameters available.
                        },
                        complete: function () {
                            $(ViewCreateEvent.idModal).parent().remove();
                            CtrlCreateEvent.modal_create();
                        } // Callback for Modal close
                    });
                });
                CtrlCreateEvent.send_form();
                CtrlCreateEvent.construct_checking();
                ViewCreateEvent.construct();
            }
        });

    },

    send_form: function () {

        $(ViewCreateEvent.idSubmit).on('click', function (e) {

            if (CtrlCreateEvent.global_checking()) {

                e.preventDefault();

                const body = getFormData($(ViewCreateEvent.idForm));

                $.ajax({
                    contentType: 'application/json',
                    url: '/events',
                    type: 'POST',
                    data: JSON.stringify(body),
                    dataType: 'html',
                    success: function () {
                        $(ViewCreateEvent.idModal).modal('close');
                        filterList();
                    },

                    error: function (result, status, error) {
                    },

                    complete: function (result, status) {
                    }
                });

            }

        });

    },

    /**
     * Check seizure of event title by user
     * @param display true(default)|false displaying error messages
     * @returns {boolean}
     */
    check_title: function (display) {
        const title = ViewCreateEvent.get_title();
        if (display === undefined) display = true;

        const lengthTitle = title.length;
        const max = $(ViewCreateEvent.idTitle).attr('data-length');
        if (lengthTitle > max) {
            if (display) {
                $(ViewCreateEvent.idTitle).removeClass('valid');
                $(ViewCreateEvent.idTitle).addClass('invalid');
                $(ViewCreateEvent.idGeneralsGroup + ' .character-counter').css('color', 'red');
                $(ViewCreateEvent.idTitleFeedback).html('Le titre doit faire maximum 65 caractères');
            }
            return false;
        } else if (lengthTitle <= max && lengthTitle > 0) {
            if (display) {
                $(ViewCreateEvent.idTitleFeedback).html('');
                $(ViewCreateEvent.idTitle).removeClass('invalid');
                $(ViewCreateEvent.idTitle).addClass('valid');
                $(ViewCreateEvent.idGeneralsGroup + ' .character-counter').css('color', '');
            }
            return true;
        } else if (lengthTitle === 0) {
            if (display) {
                $(ViewCreateEvent.idTitle).removeClass('valid');
                $(ViewCreateEvent.idTitle).addClass('invalid');
                $(ViewCreateEvent.idGeneralsGroup + ' .character-counter').css('color', '');
                $(ViewCreateEvent.idTitleFeedback).html('Vous devez donner un titre à votre évenement');
            }
            return false;
        }
    },

    /**
     * Check if user choose category
     * @param display true(default)|false displaying error messages
     * @returns {boolean}
     */
    check_category: function (display) {
        if (display === undefined) display = true;
        const value = ViewCreateEvent.get_category();
        if (value <= 0 || value === null) {
            if (display) {
                $(ViewCreateEvent.idCategoryFeedback).html('Vous devez sélectionner une catégorie');
            }
            return false;
        } else {
            if (display) {
                $(ViewCreateEvent.idCategoryFeedback).html('');
            }
            return true;
        }
    },

    /**
     * Check seizure of participants by user
     * @param display true(default)|false displaying error messages
     * @returns {boolean}
     */
    check_participants: function (display) {
        const id = ViewCreateEvent.idParticipants;
        const feedback = ViewCreateEvent.idParticipantsFeedback;
        if (display === undefined) display = true;
        if (!ViewCreateEvent.status_guest_only() || (ViewCreateEvent.status_guest_only() && !ViewCreateEvent.get_guest_only())) {
            const value = parseInt($(id).val());
            const minValue = parseInt($(id).attr('min'));
            const maxValue = parseInt($(id).attr('max'));
            if (value <= 0 || value < minValue || value > maxValue || $(id).val() === "") {
                if (display) {
                    $(id).removeClass('valid');
                    $(id).addClass('invalid');
                    $(feedback).html('Le nombre de participants doivent être compris entre ' + minValue + ' et ' + maxValue);
                }
                return false;
            } else {
                if (display) {
                    $(id).removeClass('invalid');
                    $(id).addClass('valid');
                    $(feedback).html('');
                }
                return true;
            }
        } else {
            if (display) {
                $(id).removeClass('invalid');
                $(id).removeClass('valid');
                $(feedback).html('');
            }
            return true;
        }
    },

    check_location: function (display) {
        if (display === undefined) display = true;
        if (ViewCreateEvent.get_location() === "" || ViewCreateEvent.get_place_id() === "" || ViewCreateEvent.get_latitude() === "" || ViewCreateEvent.get_longitude() === "") {
            if (display) {
                $(ViewCreateEvent.idLocation).removeClass('valid');
                $(ViewCreateEvent.idLocation).addClass('invalid');
                $(ViewCreateEvent.idLocationFeedback).html('Vous devez saisir une localisation correct');
            }
            return false;
        } else {
            if (display) {
                $(ViewCreateEvent.idLocation).removeClass('invalid');
                $(ViewCreateEvent.idLocation).addClass('valid');
                $(ViewCreateEvent.idLocationFeedback).html('');
            }
            return true;
        }
    },

    check_address_complements: function (display) {
        const complements = ViewCreateEvent.get_complements_address();
        if (display === undefined) display = true;
        const lengthComplements = complements.length;
        const max = parseInt($(ViewCreateEvent.idLocationDesc).attr('data-length'));
        if (lengthComplements > max) {
            if (display) {
                $(ViewCreateEvent.idLocationDesc).removeClass('valid');
                $(ViewCreateEvent.idLocationDesc).addClass('invalid');
                $(ViewCreateEvent.idLocationGroup + ' .character-counter').css('color', 'red');
                $(ViewCreateEvent.idLocationDescFeedback).html('Les précisions doivent faire maximum ' + max + ' caractères');
            }
            return false;
        } else if (lengthComplements <= max && lengthComplements > 0) {
            if (display) {
                $(ViewCreateEvent.idLocationDescFeedback).html('');
                $(ViewCreateEvent.idLocationDesc).removeClass('invalid');
                $(ViewCreateEvent.idLocationDesc).addClass('valid');
                $(ViewCreateEvent.idLocationGroup + ' .character-counter').css('color', '');
            }
            return true;
        } else {
            if (display) {
                $(ViewCreateEvent.idLocationDescFeedback).html('');
                $(ViewCreateEvent.idLocationDesc).removeClass('invalid');
                $(ViewCreateEvent.idLocationDesc).removeClass('valid');
                $(ViewCreateEvent.idLocationGroup + ' .character-counter').css('color', '');
            }
            return true;
        }
    },

    /**
     * Run all of checking functions
     * @param display true(default)|false displaying error messages
     */
    global_checking: function (display) {
        if (display === undefined) display = true;
        let run = true;
        if (!CtrlCreateEvent.check_title(display)) run = false;
        if (!CtrlCreateEvent.check_category(display)) run = false;
        if (!CtrlCreateEvent.check_participants(display)) run = false;
        if (!CtrlCreateEvent.check_location(display)) run = false;
        if (!CtrlCreateEvent.check_address_complements(display)) run = false;
        if (run) {
            $(ViewCreateEvent.idSubmit).removeClass('disabled');
            return true;
        } else {
            $(ViewCreateEvent.idSubmit).addClass('disabled');
            return false;
        }

    }

};

CtrlCreateEvent.construct();

export default CtrlCreateEvent;