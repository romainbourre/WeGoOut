import dateEvent from "./create-date.js";
import CtrlCreateEvent from "./create-ctrl.js"
import {initAutocompletePlace} from "./location.service.js";

const ViewCreateEvent = {

    idModal: '#create-event-modal-window',

    idForm: '#create-event-modal-form',
    idSubmit: '#create-event-modal-submit',

    idCircleGroup: '#create-event-circle-group',
    idPublic: '#create-event-public-switch',
    idPrivate: '#create-event-private-switch',

    idTabs: '#create-event-modal-tabs',

    idGeneralsGroup: '#create-event-generals-group',
    idTitle: '#create-event-title-field',
    idTitleFeedback: '#create-event-title-feedback',
    idCategory: '#create-event-category-select',
    idCategoryFeedback: '#create-event-category-feedback',
    idDescription: '#create-event-description-text',

    idParticipantsGroup: '#create-event-participants-group',
    idSwitchGuest: '#create-event-guest-check',
    idParticipants: '#create-event-participants-number',
    idParticipantsFeedback: '#create-event-participants-feedback',

    idDatetimeGroup: '#create-event-datetime-group',
    idDateBegin: '#create-event-dateBegin-field',
    idTimeBegin: '#create-event-timeBegin-field',
    idDatetimeBeginFeedback: '#create-event-datetimeBegin-feedback',
    idDatetimeEndBloc: '#create-event-datetimeEnd-bloc',
    idActiveEnd: '#create-event-datetimeEnd-active',
    idActiveEndButton: '#create-event-datetimeEnd-button',
    idDateEnd: '#create-event-dateEnd-field',
    idTimeEnd: '#create-event-timeEnd-field',
    idDatetimeEndFeedback: '#create-event-datetimeEnd-feedback',

    idLocationGroup: '#create-event-location-group',
    idLocation: '#create-event-location-field',
    idLatitude: '#create-event-latitude-hidden',
    idLongitude: '#create-event-longitude-hidden',
    idAddress: '#create-event-address-hidden',
    idPostalCode: '#create-event-postal-hidden',
    idCity: '#create-event-city-hidden',
    idCountry: '#create-event-country-hidden',
    idPlaceId: '#create-event-place-hidden',
    idLocationFeedback: '#create-event-location-feedback',
    idLocationDesc: '#create-event-compAddress-text',
    idLocationDescFeedback: '#create-event-compAddress-feedback',


    construct: function() {

        // MANAGE CIRCLE OF PUBLICATION
        $(this.idPublic + ',' + this.idPrivate).on('click', function() {
            if(ViewCreateEvent.is_public()) {
                ViewCreateEvent.disable_guest_only();
                ViewCreateEvent.active_participants();
            }
            else if(ViewCreateEvent.is_private()) {
                ViewCreateEvent.active_guest_only();
                if(ViewCreateEvent.get_guest_only()) {
                    ViewCreateEvent.disable_participants();
                }
                else {
                    ViewCreateEvent.active_participants();
                }
            }

            initAutocompletePlace('#create-event-location-field', '#create-location-result', (selected, input, _) => {
                const props = selected['properties'];
                const geo = selected['geometry'];

                const id = props['id'];
                const postalCode = props['postcode'];
                const city = props['city'];
                const country = 'France';
                const longitude = geo['coordinates'][0];
                const latitude = geo['coordinates'][1];

                input.val(props.label);
                $(ViewCreateEvent.idPlaceId).val(id);
                $(ViewCreateEvent.idPostalCode).val(postalCode);
                $(ViewCreateEvent.idCity).val(city);
                $(ViewCreateEvent.idCountry).val(country);
                $(ViewCreateEvent.idLatitude).val(latitude);
                $(ViewCreateEvent.idLongitude).val(longitude);

                CtrlCreateEvent.check_location();
            });
        });

        // MANAGE GUEST ONLY
        $(ViewCreateEvent.idSwitchGuest).on('click', function() {
            if(ViewCreateEvent.get_guest_only()) {
                ViewCreateEvent.disable_participants();
            }
            else {
                ViewCreateEvent.active_participants();
            }
        });

        // DISPLAY DROP DOWN TABS
        $(this.idPublic + ',' + this.idPrivate).on('click', function() {
            $(ViewCreateEvent.idCircleGroup).css('display', 'none');
            $(ViewCreateEvent.idTabs).css('display', 'block');
        });

        // MANAGE DROP DOWN TABS
        $(document).ready(function(){
            $(ViewCreateEvent.idTabs).collapsible();
            $(ViewCreateEvent.idTabs + ' .next-index').click(function() {
                $(ViewCreateEvent.idTabs).collapsible('open', $(this).attr('data-next'));
            });
        });

        // INIT TITLE CHARACTER COUNTER
        $(document).ready(function() {
            $(ViewCreateEvent.idTitle).characterCounter();
        });

        // INIT SELECT CATEGORY
        $(document).ready(function() {
            $(ViewCreateEvent.idCategory).material_select();
        });

        $(ViewCreateEvent.idCategory).material_select('destroy');

        dateEvent.construct();

        $(ViewCreateEvent.idDateBegin).pickadate({
            monthsFull: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            monthsShort: ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Dec'],
            weekdaysFull: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
            weekdaysShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
            today: 'aujourd\'hui',
            clear: 'effacer',
            close: 'fermer',
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
            onSet: function() {
                dateEvent.dateBegin.setDate(this.component.item.highlight["date"]);
                dateEvent.dateBegin.setMonth(this.component.item.highlight["month"]);
                dateEvent.dateBegin.setYear(this.component.item.highlight["year"]);
                dateEvent.dateEnd.setTime(dateEvent.dateBegin.getTime() + (3600 * 1000));
                $(ViewCreateEvent.idDateEnd).pickadate('picker').set('min', dateEvent.dateBegin);
                ViewCreateEvent.set_date_end($(ViewCreateEvent.idDateBegin).val());
                ViewCreateEvent.set_time_end(dateEvent.dateEnd.getHours() + ':' + dateEvent.dateEnd.getMinutes());
                dateEvent.check_date();
            },
            // An integer (positive/negative) sets it relative to today.
            min: true
            // `true` sets it to today. `false` removes any limits.
        });

        $(ViewCreateEvent.idActiveEndButton).on('click', function() {
            if(ViewCreateEvent.status_datetime_end()) {
                ViewCreateEvent.status_datetime_end(false);
                $(this).html('+ Ajouter une fin');
                $(ViewCreateEvent.idDatetimeEndBloc).css('display', 'none');
            }
            else {
                ViewCreateEvent.status_datetime_end(true);
                $(this).html('Supprimer');
                $(ViewCreateEvent.idDatetimeEndBloc).css('display', 'block');
            }
        });

        $(ViewCreateEvent.idDateEnd).pickadate({
            monthsFull: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            monthsShort: ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Dec'],
            weekdaysFull: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
            weekdaysShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
            today: 'aujourd\'hui',
            clear: 'effacer',
            close: 'fermer',
            formatSubmit: 'dd/mm/yyyy',
            selectMonths: true, // Creates a dropdown to control month
            selectYears: 15, // Creates a dropdown of 15 years to control year

            // Formats
            format: 'dd/mm/yyyy',

            // Close on a user action
            closeOnSelect: true,
            closeOnClear: true,

            // Events
            onStart: function() {},
            onRender: undefined,
            onOpen: undefined,
            onClose: undefined,
            onStop: undefined,
            onSet: function () {
                dateEvent.dateEnd.setDate(this.component.item.highlight["date"]);
                dateEvent.dateEnd.setMonth(this.component.item.highlight["month"]);
                dateEvent.dateEnd.setYear(this.component.item.highlight["year"]);
                dateEvent.check_date();
            },

            // An integer (positive/negative) sets it relative to today.
            min: dateEvent.dateBegin
            // `true` sets it to today. `false` removes any limits.
        });

        $(ViewCreateEvent.idTimeBegin).pickatime({
            default: 'now', // Set default time: 'now', '1:30AM', '16:30'
            fromnow: 0,       // set default time to * milliseconds from now (using with default = 'now')
            twelvehour: false, // Use AM/PM or 24-hour format
            donetext: 'OK', // text for done-button
            cleartext: 'effacer', // text for clear-button
            canceltext: 'Annuler', // Text for cancel-button
            autoclose: true, // automatic close timepicker
            ampmclickable: true, // make AM PM clickable
            onSet: function() {},
            aftershow: function(){} //Function for after opening timepicker
        });

        $(ViewCreateEvent.idTimeEnd).pickatime({
            default: 'now', // Set default time: 'now', '1:30AM', '16:30'
            fromnow: (3600 * 1000),       // set default time to * milliseconds from now (using with default = 'now')
            twelvehour: false, // Use AM/PM or 24-hour format
            donetext: 'OK', // text for done-button
            cleartext: 'effacer', // text for clear-button
            canceltext: 'Annuler', // Text for cancel-button
            autoclose: true, // automatic close timepicker
            ampmclickable: true, // make AM PM clickable
            onSet: function(){},
            aftershow: function(){} //Function for after opening timepicker
        });

        $(ViewCreateEvent.idTimeBegin + ',' + ViewCreateEvent.idTimeEnd).on('change', function() {
            dateEvent.check_date();
        });

        // INIT GOOGLE PLACE
        initAutocompletePlace();

        // INIT COMPLEMENTS ADDRESS CHARACTER COUNTER
        $(document).ready(function() {
            $(ViewCreateEvent.idLocationDesc).characterCounter();
        });


    },

    /**
     * Check in form if event is public
     * @returns {*|jQuery|boolean}
     */
    is_public: function() {
        return ($(ViewCreateEvent.idPublic).is(':checked') && $(ViewCreateEvent.idPublic).val() === "1" && !$(ViewCreateEvent.idPrivate).is(':checked'));
    },

    /**
     * Check in form if event is private
     * @returns {boolean|*|jQuery}
     */
    is_private: function() {
        return (!$(ViewCreateEvent.idPublic).is(':checked') && $(ViewCreateEvent.idPrivate).is(':checked') && $(ViewCreateEvent.idPrivate).val() === "2");
    },

    /**
     * Get in form a title of event
     * @returns {*|jQuery}
     */
    get_title: function() {
        return $(ViewCreateEvent.idTitle).val();
    },

    /**
     * Get in form the category of event
     * @returns {*|jQuery}
     */
    get_category: function() {
        return $(ViewCreateEvent.idCategory).val();
    },

    /**
     * Get in form the description of event
     * @returns {*|jQuery}
     */
    get_description: function() {
        return $(ViewCreateEvent.idDescription).val();
    },

    /**
     * Check if guest only checkbox is activate or not
     */
    status_guest_only: function() {
        return !$(ViewCreateEvent.idSwitchGuest).is(':disabled');
    },

    /**
     * Disable checkbox of guest only option
     */
    disable_guest_only: function() {
        $(ViewCreateEvent.idSwitchGuest).prop('disabled', true);
    },

    /**
     * Activate checkbox of guest only option
     */
    active_guest_only: function() {
        $(ViewCreateEvent.idSwitchGuest).prop('disabled', false);
    },

    /**
     * Get value of guest only checkbox
     * @returns {*|jQuery}
     */
    get_guest_only: function() {
        return ($(ViewCreateEvent.idSwitchGuest).is(':checked'));
    },

    /**
     * Disable input of number of participants
     */
    disable_participants: function() {
        $(ViewCreateEvent.idParticipants).addClass('disabled');
        $(ViewCreateEvent.idParticipants).prop('disabled', true);
        CtrlCreateEvent.check_participants();
    },

    /**
     * Activate input of number of participants
     */
    active_participants: function() {
        $(ViewCreateEvent.idParticipants).removeClass('disabled');
        $(ViewCreateEvent.idParticipants).prop('disabled', false);
        CtrlCreateEvent.check_participants();
    },

    /**
     * Get in form the number of participants of the event
     * @returns {*|jQuery}
     */
    get_participants: function() {
        return $(ViewCreateEvent.idParticipants).val();
    },

    /**
     * Get in form the begin date and time of the event
     * @returns {string}
     */
    get_datetime_begin: function() {
        return $(ViewCreateEvent.idDateBegin).val().substring(3, 5) + '/' + $(ViewCreateEvent.idDateBegin).val().substring(0, 2) + '/' + $(ViewCreateEvent.idDateBegin).val().substring(6, 10) + ' ' + $(ViewCreateEvent.idTimeBegin).val();
    },

    set_date_begin: function(date) {
        $(ViewCreateEvent.idDateBegin).val(date);
    },

    set_time_begin: function(time) {
        $(ViewCreateEvent.idTimeBegin).val(time);
    },

    /**
     * Get status of date and time of the end of event
     * or set status
     * @param status bool
     * @returns {boolean}
     */
    status_datetime_end: function(status) {
        if(status === undefined) {
            return $(ViewCreateEvent.idActiveEnd).val() === "true";
        }
        else {
            $(ViewCreateEvent.idActiveEnd).val(status)
        }
    },

    /**
     * Get in form the end date and time of the event
     * @returns {string}
     */
    get_datetime_end: function() {
        return $(ViewCreateEvent.idDateEnd).val().substring(3, 5) + '/' + $(ViewCreateEvent.idDateEnd).val().substring(0, 2) + '/' + $(ViewCreateEvent.idDateEnd).val().substring(6, 10) + ' ' + $(ViewCreateEvent.idTimeEnd).val();
    },

    set_date_end: function(date) {
        $(ViewCreateEvent.idDateEnd).val(date);
    },

    set_time_end: function(time) {
        $(ViewCreateEvent.idTimeEnd).val(time);
    },

    get_location: function() {
        return $(ViewCreateEvent.idLocation).val();
    },

    get_latitude: function() {
        return $(ViewCreateEvent.idLatitude).val();
    },

    set_latitude: function(latitude) {
        $(ViewCreateEvent.idLatitude).val(latitude);
    },

    get_longitude: function() {
        return $(ViewCreateEvent.idLongitude).val();
    },

    set_longitude: function(longitude) {
        $(ViewCreateEvent.idLongitude).val(longitude);
    },

    set_address: function(address) {
        $(ViewCreateEvent.idAddress).val(address);
    },

    set_postal_code: function(postalCode) {
        $(ViewCreateEvent.idPostalCode).val(postalCode);
    },

    set_city: function(city) {
        $(ViewCreateEvent.idCity).val(city);
    },

    set_country: function(country) {
        $(ViewCreateEvent.idCountry).val(country);
    },

    get_place_id: function() {
        return $(ViewCreateEvent.idPlaceId).val();
    },

    set_place_id: function(placeId) {
        $(ViewCreateEvent.idPlaceId).val(placeId);
    },

    get_complements_address: function() {
        return $(ViewCreateEvent.idLocationDesc).val();
    }

};

export default ViewCreateEvent;