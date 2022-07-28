$(document).ready(function() {
    $('#edit_event_title').characterCounter();
});

$('#edit_event_title').keyup(function() {
    var length = $('#edit_event_title').val().length;
    var max = $('#edit_event_title').attr('data-length');
    if(length > max) {
        $('#feedback_title').html('Le titre doit faire maximum 100 caractères');
        $(this).addClass('red-text');
        $('.character-counter').addClass('red-text');
    }
    else if (length < max && length !== 0) {
        $('#feedback_title').html('');
        $(this).removeClass('red-text');
        $('.character-counter').removeClass('red-text');
    }
    else if(length === 0) {
        $('#feedback_title').html('Vous devez donner un titre à votre évenement');
    }
});

/* ---------------------------------------- MANAGE DATE ------------------------------------------------ */

var date1 = $('#edit_event_date_begin').val().substring(3, 5) + '/' + $('#edit_event_date_begin').val().substring(0, 2) + '/' + $('#edit_event_date_begin').val().substring(6, 10);
var date2 = $('#edit_event_date_end').val().substring(3, 5) + '/' + $('#edit_event_date_end').val().substring(0, 2) + '/' + $('#edit_event_date_end').val().substring(6, 10);


var dateBegin = new Date();
var dateEnd = new Date();
dateBegin.setTime(Date.parse(date1 + ' ' + $('#edit_event_time_begin').val()));
dateEnd.setTime(Date.parse(date2 + ' ' + $('#edit_event_time_end').val()));

$('#edit_event_date_end').change(function() {
    checkDate()
});

$('#edit_event_date_begin').change(function() {
    checkDate()
});

$('#edit_event_time_begin').change(function() {
    temp = new Date();
    temp.setTime(Date.parse('01/01/2000 ' + $('#edit_event_time_begin').val()));
    dateBegin.setHours(temp.getHours());
    dateBegin.setMinutes(temp.getMinutes());
    dateEnd.setHours(temp.getHours()+1);
    dateEnd.setMinutes(temp.getMinutes());
    $('#edit_event_time_end').val(dateEnd.getHours() + ':' + dateEnd.getMinutes());
    checkDate()
});

$('#edit_event_time_end').change(function() {
    temp = new Date();
    temp.setTime(Date.parse('01/01/2000 ' + $('#edit_event_time_begin').val()));
    dateEnd.setHours(temp.getHours());
    dateEnd.setMinutes(temp.getMinutes());
    checkDate();
});

function checkDate() {
    timestampBegin = dateBegin.getTime();
    timestampEnd = dateEnd.getTime();
    if(timestampBegin < timestampEnd && timestampBegin !== timestampEnd) {
        $('#feedback_datetime').html("");
        $('#edit_event_date_begin').removeClass('red-text');
        $('#edit_event_date_end').removeClass('red-text');
        $('#edit_event_time_begin').removeClass('red-text');
        $('#edit_event_time_end').removeClass('red-text');
        return true;
    }
    else {
        $('#edit_event_date_begin').addClass('red-text');
        $('#edit_event_date_end').addClass('red-text');
        $('#edit_event_time_begin').addClass('red-text');
        $('#edit_event_time_end').addClass('red-text');
        $('#feedback_datetime').html("Les heures et les dates doivent être cohérente");
        return false;
    }
}


$('#edit_event_date_begin').pickadate({
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
        dateBegin.setDate(this.component.item.highlight["date"]);
        dateBegin.setMonth(this.component.item.highlight["month"]);
        dateBegin.setYear(this.component.item.highlight["year"]);
        $('#edit_event_date_end').pickadate('picker').set('min',dateBegin);
        $('#edit_event_date_end').val($('#edit_event_date_begin').val());
        dateEnd.setHours(dateBegin.getHours()+1);
        dateEnd.setMinutes(dateBegin.getMinutes());
        $('#edit_event_time_end').val(dateEnd.getHours() + ':' + dateEnd.getMinutes());
        checkDate();
    },

    // An integer (positive/negative) sets it relative to today.
    min: true
    // `true` sets it to today. `false` removes any limits.

});

$('#edit_event_date_end').pickadate({
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
        dateEnd.setDate(this.component.item.highlight["date"]);
        dateEnd.setMonth(this.component.item.highlight["month"]);
        dateEnd.setYear(this.component.item.highlight["year"]);
        checkDate();
    },

    // An integer (positive/negative) sets it relative to today.
    min: dateBegin
    // `true` sets it to today. `false` removes any limits.

});




$('#edit_event_time_begin').pickatime({
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

$('#edit_event_time_end').pickatime({
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

/* ----------------------------------------------------------------------------------------------------- */


/* ------------------------------------------ GOOGLE API ----------------------------------------------- */



// SETTINGS OF GOOGLE API
var input = document.getElementById('edit_input_completeAddress');
var options = {
    types: ['geocode'],
    componentRestrictions: {country: 'fr'}
};

// INITIALIZATION
autocomplete = new google.maps.places.Autocomplete(input, options);
geocoder = new google.maps.Geocoder();
autocomplete.addListener('place_changed', fillInAddress);

// RESET INPUT LOCATION
input.addEventListener("input", function () {
    document.getElementById('edit_input_address').value = "";
    document.getElementById('edit_input_postalCode').value = "";
    document.getElementById('edit_input_city').value = "";
    document.getElementById('edit_input_placeId').value = "";
    document.getElementById('edit_input_lat').value = "";
    document.getElementById('edit_input_lng').value = "";
});


/**
 * Récupère l'id de la ville séléctionner et l'envoi à geocodeAddress
 */
function fillInAddress() {
    var placeId = autocomplete.getPlace().place_id;
    geocodeAddress(placeId);
}

/**
 * Récupère les détails concernant un lieu (à partir de son ID)
 * et place les infos dans <input> de formulaire
 * @param place_id ID Google du lieu
 */
function geocodeAddress(place_id) {
    geocoder.geocode({'placeId': place_id}, function (results, status) {

        if (status === 'OK') {

            // SEARCH AND SET LOCATION DATA
            var address = results[0]['address_components'];
            for (var i = 0; i < address.length; i++) {
                var numberStreet = "";
                if (address[i]['types'][0] == "street_number") numberStreet = address['long_name'] + ", ";
                if (address[i]['types'][0] == "route") document.getElementById('edit_input_address').value = numberStreet + address[i]['long_name'];
                if (address[i]['types'][0] == "locality") document.getElementById('edit_input_city').value = address[i]['long_name'];
                if (address[i]['types'][0] == "postal_code") document.getElementById('edit_input_postalCode').value = address[i]['long_name'];
            }

            // SET GOOGLE DATA
            document.getElementById('edit_input_placeId').value = results[0]['place_id'];
            document.getElementById('edit_input_lat').value = results[0]['geometry']['location'].lat();
            document.getElementById('edit_input_lng').value = results[0]['geometry']['location'].lng();

            checkLocation();

        }

    });

}

// CHECK INPUT LOCATION
$('#edit_input_completeAddress').change(function() {
   checkLocation();
});

function checkLocation() {
    if(
        $('#edit_input_lat').val() === ""
        && $('#edit_input_lng').val() === ""
        && $('#edit_input_placeId').val() === ""

    ) {

        $('#feedback_location').html("L'adresse n'est pas valide");
        $('#feedback_location').removeClass('green-text');
        $('#feedback_location').addClass('red-text');
        $('#edit_input_completeAddress').removeClass('green-text');
        $('#edit_input_completeAddress').addClass('red-text');

    }
    else {

        $('#feedback_location').html("L'adresse est valide");
        $('#feedback_location').removeClass('red-text');
        $('#feedback_location').addClass('green-text');
        $('#edit_input_completeAddress').addClass('green-text');
        $('#edit_input_completeAddress').removeClass('red-text');

    }
}