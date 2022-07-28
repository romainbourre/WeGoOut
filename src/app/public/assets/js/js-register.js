import {initAutocompletePlace} from "./location.service.js";

var ViewCreateLambdaUser = {

    idPromoRegistration: '#registration-user-promotion',
    idRegistrationCard: '#registration-user-registration-card',

    idForm: '#registration-user-form',

    idLastName: '#registration-user-lastName-field',
    idLastNameFeedback: '#registration-user-lastName-feedback',

    idFirstName: '#registration-user-firstName-field',
    idFirstNameFeedback: '#registration-user-firstName-feedback',

    idBirthDate: '#registration-user-birthDate-field',
    idBirthDateFeedback: '#registration-user-birthDate-feedback',

    idSex: '#registration-user-sex-select',
    idSexFeedback: '#registration-user-sex-feedback',

    idEmail: '#registration-user-email-field',
    idEmailFeedback: '#registration-user-email-feedback',

    idPassword: '#registration-user-password-field',
    idPasswordFeedback: '#registration-user-password-feedback',

    idLocation: '#registration-user-location-field',
    idLocationFeedback: '#registration-user-location-feedback',
    idPostalCode: '#registration-user-postalCode-hidden',
    idCity: '#registration-user-city-hidden',
    idCountry: '#registration-user-country-hidden',
    idPlaceId: '#registration-user-placeId-hidden',
    idLatitude: '#registration-user-latitude-hidden',
    idLongitude: '#registration-user-longitude-hidden',

    idSubmit: '#registration-user-form-submit',

    construct: function () {

        // LAYOUT OF CSS ELEMENT
        function layoutCSS() {
            var mainHeight = $('main').css('height');
            var cardRegistrationHeight = $(ViewCreateLambdaUser.idRegistrationCard).css('height');
            var promoRegistrationHeight = $(ViewCreateLambdaUser.idPromoRegistration).css('height');
            if (window.innerWidth > 1200) {
                $(ViewCreateLambdaUser.idRegistrationCard).css('margin-top', 'calc((' + mainHeight + ' - ' + cardRegistrationHeight + ')/2)');
                $(ViewCreateLambdaUser.idPromoRegistration).css('margin-top', 'calc((' + mainHeight + ' - ' + promoRegistrationHeight + ')/2)');
            } else {
                $(ViewCreateLambdaUser.idRegistrationCard).css('margin-top', '75px');
                $(ViewCreateLambdaUser.idPromoRegistration).css('margin-top', '75px');
            }
            if (window.innerWidth < 600) {
                $(ViewCreateLambdaUser.idRegistrationCard).css('margin-bottom', '0px');
            } else {
                $(ViewCreateLambdaUser.idRegistrationCard).css('margin-bottom', 'calc((' + mainHeight + ' - ' + cardRegistrationHeight + ')/2)');
            }
        }

        layoutCSS();

        $(window).resize(function () {
            layoutCSS();
        });

        // INIT SEX SELECT
        $(document).ready(function () {
            $(ViewCreateLambdaUser.idSex).material_select();
        });
        $(ViewCreateLambdaUser.idSex).material_select('destroy');

        // INIT DATEPICKER
        $(ViewCreateLambdaUser.idBirthDate).pickadate({
            monthsFull: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            monthsShort: ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Dec'],
            weekdaysFull: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
            weekdaysShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
            today: 'aujourd\'hui',
            clear: 'effacer',
            close: 'fermer',
            formatSubmit: 'dd/mm/yyyy',
            selectMonths: true, // Creates a dropdown to control month
            selectYears: 50, // Creates a dropdown of 15 years to control year
            dateBefore: null,
            // Formats
            format: 'dd/mm/yyyy',
            // Close on a user action
            closeOnSelect: true,
            closeOnClear: true,
            // Events
            onStart: undefined,
            onRender: undefined,
            onOpen: function () {
                this.dateBefore = ViewCreateLambdaUser.get_birthDate();
            },
            onClose: undefined,
            onStop: undefined,
            onSet: function () {
                if (this.dateBefore !== ViewCreateLambdaUser.get_birthDate()) {
                    CtrlCreateLambdaUser.birthDate.setDate(this.component.item.highlight["date"]);
                    CtrlCreateLambdaUser.birthDate.setMonth(this.component.item.highlight["month"]);
                    CtrlCreateLambdaUser.birthDate.setYear(this.component.item.highlight["year"]);
                    this.close();
                    $(ViewCreateLambdaUser.idSex).parent().children('.select-dropdown').focus();
                }
                CtrlCreateLambdaUser.check_birthDate();
            },
            // An integer (positive/negative) sets it relative to today.
            max: CtrlCreateLambdaUser.maxBirthDate
            // `true` sets it to today. `false` removes any limits.
        });

    },

    get_lastName: function () {
        return $(ViewCreateLambdaUser.idLastName).val();
    },

    set_lastName_feedback: function (feedback) {
        $(ViewCreateLambdaUser.idLastNameFeedback).html(feedback);
    },

    get_firstName: function () {
        return $(ViewCreateLambdaUser.idFirstName).val();
    },

    set_firstName_feedback: function (feedback) {
        $(ViewCreateLambdaUser.idFirstNameFeedback).html(feedback);
    },

    /**
     * Get in form the birth date of the user
     * @returns {string}
     */
    get_birthDate: function () {
        return $(ViewCreateLambdaUser.idBirthDate).val().substring(3, 5) + '/' + $(ViewCreateLambdaUser.idBirthDate).val().substring(0, 2) + '/' + $(ViewCreateLambdaUser.idBirthDate).val().substring(6, 10) + ' 00:00';
    },

    set_birthDate_feedback: function (feedback) {
        $(ViewCreateLambdaUser.idBirthDateFeedback).html(feedback);
    },

    get_sex: function () {
        return $(ViewCreateLambdaUser.idSex).val();
    },

    set_sex_feedback: function (feedback) {
        $(ViewCreateLambdaUser.idSexFeedback).html(feedback);
    },

    get_email: function () {
        return $(ViewCreateLambdaUser.idEmail).val();
    },

    set_email_feedback: function (feedback) {
        $(ViewCreateLambdaUser.idEmailFeedback).html(feedback);
    },

    get_password_length: function () {
        return $(ViewCreateLambdaUser.idPassword).val().length;
    },

    set_password_feedback: function (feedback) {
        $(ViewCreateLambdaUser.idPasswordFeedback).html(feedback);
    },

    get_location: function () {
        return $(ViewCreateLambdaUser.idLocation).val();
    },

    set_postal_code: function (postalCode) {
        $(ViewCreateLambdaUser.idPostalCode).val(postalCode);
    },

    set_city: function (city) {
        $(ViewCreateLambdaUser.idCity).val(city);
    },

    set_country: function (country) {
        $(ViewCreateLambdaUser.idCountry).val(country);
    },

    get_place_id: function () {
        return $(ViewCreateLambdaUser.idPlaceId).val();
    },

    set_place_id: function (place) {
        $(ViewCreateLambdaUser.idPlaceId).val(place);
    },

    get_latitude: function () {
        return $(ViewCreateLambdaUser.idLatitude).val();
    },

    set_latitude: function (latitude) {
        $(ViewCreateLambdaUser.idLatitude).val(latitude);
    },

    get_longitude: function () {
        return $(ViewCreateLambdaUser.idLongitude).val();
    },

    set_longitude: function (longitude) {
        $(ViewCreateLambdaUser.idLongitude).val(longitude);
    },

    set_location_feedback: function (feedback) {
        $(ViewCreateLambdaUser.idLocationFeedback).html(feedback);
    }


};

var CtrlCreateLambdaUser = {

    birthDate: new Date(),
    maxBirthDate: new Date(),

    construct: function () {
        // INIT BIRTH DATE
        CtrlCreateLambdaUser.maxBirthDate.setFullYear(CtrlCreateLambdaUser.birthDate.getFullYear() - $(ViewCreateLambdaUser.idBirthDate).attr('data-minage'));
        ViewCreateLambdaUser.construct();
        CtrlCreateLambdaUser.construct_checking();
    },

    construct_checking: function () {

        $(ViewCreateLambdaUser.idLastName).on('keyup, blur', function () {
            const result = CtrlCreateLambdaUser.check_lastName();
        });

        $(ViewCreateLambdaUser.idFirstName).on('keyup, blur', function () {
            const result = CtrlCreateLambdaUser.check_firstName();
        });

        $(ViewCreateLambdaUser.idBirthDate).on('change, blur', function () {
            const result = CtrlCreateLambdaUser.check_birthDate();
        });

        $(ViewCreateLambdaUser.idSex).on('change', function () {
            const result = CtrlCreateLambdaUser.check_sex()
        });

        $(ViewCreateLambdaUser.idEmail).on('keyup, change, blur', function () {
            const result = CtrlCreateLambdaUser.check_email();
        });

        $(ViewCreateLambdaUser.idPassword).on('change, blur', function () {
            const result = CtrlCreateLambdaUser.check_password();
        });

        $(ViewCreateLambdaUser.idLocation).on('change, blur', function () {
            const result = CtrlCreateLambdaUser.check_location();
        });

        $('input, select, option, text, .collection-item').on('change, click, blur, keyup', function () {
            const result = CtrlCreateLambdaUser.global_checking(false);
        });

        $(ViewCreateLambdaUser.idSubmit).on('click', function () {
            CtrlCreateLambdaUser.send_form();
        });

    },

    send_form: function () {
        if (CtrlCreateLambdaUser.global_checking()) {
            $(ViewCreateLambdaUser.idForm).submit();
        }
    },

    check_lastName: function (display) {
        if (display === undefined) display = true;
        var text = ViewCreateLambdaUser.get_lastName();
        var textLength = text.length;
        var maxLength = $(ViewCreateLambdaUser.idLastName).attr('data-length');
        if (textLength < 1 || textLength > maxLength) {
            if (display) {
                ViewCreateLambdaUser.set_lastName_feedback('Le nom doit être compris entre 1 et 50 caractères');
                $(ViewCreateLambdaUser.idLastName).removeClass('valid');
                $(ViewCreateLambdaUser.idLastName).addClass('invalid');
            }
            return false;
        } else {
            if (display) {
                ViewCreateLambdaUser.set_lastName_feedback('');
                $(ViewCreateLambdaUser.idLastName).removeClass('invalid');
                $(ViewCreateLambdaUser.idLastName).addClass('valid');
            }
            return true;
        }
    },

    check_firstName: function (display) {
        if (display === undefined) display = true;
        var text = ViewCreateLambdaUser.get_firstName();
        var textLength = text.length;
        var maxLength = $(ViewCreateLambdaUser.idFirstName).attr('data-length');
        if (textLength < 1 || textLength > maxLength) {
            if (display) {
                ViewCreateLambdaUser.set_firstName_feedback('Le nom doit être compris entre 1 et 50 caractères');
                $(ViewCreateLambdaUser.idFirstName).removeClass('valid');
                $(ViewCreateLambdaUser.idFirstName).addClass('invalid');
            }
            return false;
        } else {
            if (display) {
                ViewCreateLambdaUser.set_firstName_feedback('');
                $(ViewCreateLambdaUser.idFirstName).removeClass('invalid');
                $(ViewCreateLambdaUser.idFirstName).addClass('valid');
            }
            return true;
        }

    },

    check_birthDate: function (display) {
        if (display === undefined) display = true;
        CtrlCreateLambdaUser.birthDate.setTime(Date.parse(ViewCreateLambdaUser.get_birthDate()));
        var timestampBirthDate = CtrlCreateLambdaUser.birthDate.getTime();
        var timestampMinBirthDate = CtrlCreateLambdaUser.maxBirthDate.getTime();
        if (isNaN(timestampBirthDate)) {
            if (display) {
                ViewCreateLambdaUser.set_birthDate_feedback('Vous devez saisir un date valide');
                $(ViewCreateLambdaUser.idBirthDate).removeClass('valid');
                $(ViewCreateLambdaUser.idBirthDate).addClass('invalid');
            }
            return false;
        } else if (!isNaN(timestampBirthDate) && (timestampBirthDate < Date.now() && timestampBirthDate >= timestampMinBirthDate)) {
            if (display) {
                ViewCreateLambdaUser.set_birthDate_feedback('Vous devez avoir ' + $(ViewCreateLambdaUser.idBirthDate).attr('data-minage') + ' ans');
                $(ViewCreateLambdaUser.idBirthDate).removeClass('valid');
                $(ViewCreateLambdaUser.idBirthDate).addClass('invalid');
            }
            return false;
        } else {
            
            if (display) {
                ViewCreateLambdaUser.set_birthDate_feedback('');
                $(ViewCreateLambdaUser.idBirthDate).removeClass('invalid');
                $(ViewCreateLambdaUser.idBirthDate).addClass('valid');
            }
            return true;
        }

    },

    check_sex: function (display) {
        if (display === undefined) display = true;
        var value = ViewCreateLambdaUser.get_sex();
        if (value !== "H" && value !== "F") {
            if (display) {
                ViewCreateLambdaUser.set_sex_feedback('Vous hésitez ??');
                $(ViewCreateLambdaUser.idSex).parent().children('.select-dropdown').removeClass('valid');
                $(ViewCreateLambdaUser.idSex).parent().children('.select-dropdown').addClass('invalid');

            }
            return false;
        } else {
            if (display) {
                ViewCreateLambdaUser.set_sex_feedback('');
                $(ViewCreateLambdaUser.idSex).parent().children('.select-dropdown').removeClass('invalid');
                $(ViewCreateLambdaUser.idSex).parent().children('.select-dropdown').addClass('valid');
            }
            return true;
        }
    },

    check_email: function (display) {
        if (display === undefined) display = true;
        var email = ViewCreateLambdaUser.get_email();
        var reg = new RegExp('^[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*@[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*[\.]{1}[a-z]{2,7}$', 'i');
        if (!reg.test(email)) {
            if (display) {
                ViewCreateLambdaUser.set_email_feedback('Adresse e-mail invalide');
                $(ViewCreateLambdaUser.idEmail).removeClass('valid');
                $(ViewCreateLambdaUser.idEmail).addClass('invalid');
            }
            return false;
        } else {
            if (display) {
                ViewCreateLambdaUser.set_email_feedback('');
                $(ViewCreateLambdaUser.idEmail).removeClass('invalid');
                $(ViewCreateLambdaUser.idEmail).addClass('valid');
            }
            return true;
        }

    },

    check_password: function (display) {
        if (display === undefined) display = true;
        var pwdLength = ViewCreateLambdaUser.get_password_length();
        var minLength = $(ViewCreateLambdaUser.idPassword).attr('data-minlength');
        if (pwdLength === 0) {
            if (display) {
                ViewCreateLambdaUser.set_password_feedback('Vous devez saisir un mot de passe');
                $(ViewCreateLambdaUser.idPassword).removeClass('valid');
                $(ViewCreateLambdaUser.idPassword).addClass('invalid');
            }
            return false;
        } else if (pwdLength < minLength) {
            if (display) {
                ViewCreateLambdaUser.set_password_feedback('Votre mot de passe doit faire plus de ' + minLength + ' caractères');
                $(ViewCreateLambdaUser.idPassword).removeClass('valid');
                $(ViewCreateLambdaUser.idPassword).addClass('invalid');
            }
            return false;
        } else {
            if (display) {
                ViewCreateLambdaUser.set_password_feedback('');
                $(ViewCreateLambdaUser.idPassword).removeClass('invalid');
                $(ViewCreateLambdaUser.idPassword).addClass('valid');
            }
            return true;
        }
    },

    check_location: function (display) {
        if (display === undefined) display = true;
        if (ViewCreateLambdaUser.get_location() === "" || ViewCreateLambdaUser.get_place_id() === "" || ViewCreateLambdaUser.get_latitude() === "" || ViewCreateLambdaUser.get_longitude() === "") {
            if (display) {
                $(ViewCreateLambdaUser.idLocation).removeClass('valid');
                $(ViewCreateLambdaUser.idLocation).addClass('invalid');
                ViewCreateLambdaUser.set_location_feedback('Vous devez saisir une localisation correct');
            }
            return false;
        } else {
            if (display) {
                $(ViewCreateLambdaUser.idLocation).removeClass('invalid');
                $(ViewCreateLambdaUser.idLocation).addClass('valid');
                ViewCreateLambdaUser.set_location_feedback('');
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
        var run = true;
        if ($(ViewCreateLambdaUser.idForm).attr('data-type') !== "0") run = false;
        if (!CtrlCreateLambdaUser.check_lastName(display)) run = false;
        if (!CtrlCreateLambdaUser.check_firstName(display)) run = false;
        if (!CtrlCreateLambdaUser.check_birthDate(display)) run = false;
        if (!CtrlCreateLambdaUser.check_sex(display)) run = false;
        if (!CtrlCreateLambdaUser.check_email(display)) run = false;
        if (!CtrlCreateLambdaUser.check_password(display)) run = false;
        if (!CtrlCreateLambdaUser.check_location(display)) run = false;
        if (run) {
            $(ViewCreateLambdaUser.idSubmit).removeClass('disabled');
            return true;
        } else {
            $(ViewCreateLambdaUser.idSubmit).addClass('disabled');
            return false;
        }

    }

};

CtrlCreateLambdaUser.construct();

initAutocompletePlace('#registration-user-location-field', '.autocomplete-result', (selected, input, _) => {
    const props = selected['properties'];
    const geo = selected['geometry'];

    const id = props['id'];
    const postalCode = props['postcode'];
    const city = props['city'];
    const country = 'France';
    const longitude = geo['coordinates'][0];
    const latitude = geo['coordinates'][1];

    input.val(props.label);
    $(ViewCreateLambdaUser.idPlaceId).val(id);
    $(ViewCreateLambdaUser.idPostalCode).val(postalCode);
    $(ViewCreateLambdaUser.idCity).val(city);
    $(ViewCreateLambdaUser.idCountry).val(country);
    $(ViewCreateLambdaUser.idLatitude).val(latitude);
    $(ViewCreateLambdaUser.idLongitude).val(longitude);

    CtrlCreateLambdaUser.global_checking();
});