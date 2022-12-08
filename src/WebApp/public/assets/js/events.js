import {filterList} from "../../app/ajax/a-listevent.js";
import {initAutocompletePlace} from "./location.service.js";

$('#list_range_distance').on('input', function() {

    $('#list_label_distance').html($(this).val());

});

$('.datepicker').pickadate({
    monthsFull: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
    monthsShort: ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Dec'],
    weekdaysFull: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
    weekdaysShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
    today: 'aujourd\'hui',
    clear: 'effacer',
    close: '',
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

    // An integer (positive/negative) sets it relative to today.
    min: true
    // `true` sets it to today. `false` removes any limits.


});

// -------------------------- LOCATION API ---------------------------------- //

initAutocompletePlace('#list_input_city', '#search-location-result', (selected, input, _) => {
    const props = selected['properties'];
    const geo = selected['geometry'];
    const longitude = geo['coordinates'][0];
    const latitude = geo['coordinates'][1];

    input.val(props.label);
    $('#list_input_lat').val(latitude);
    $('#list_input_lng').val(longitude);

    filterList();

    const cityClearFilterButton = $('#filter_city_clear');
    if (input.length > 0) {
        cityClearFilterButton.css('display', 'block');
        cityClearFilterButton.on('click', () => {
            input.val('');
            $('#list_input_lat').attr({value : ""});
            $('#list_input_lng').attr({value : ""});
            cityClearFilterButton.css('display', 'none');
            filterList();
        });
    }
    else {
        cityClearFilterButton.style.display = 'none';
    }
});

// -------------------------- LOCATION API ---------------------------------- //

$('#list_input_date').change(function() {
    $('#filter_date_clear').css('display', 'block');
    $('#filter_date_clear').click(function() {
        $('#list_input_date').val('');
        $('#filter_date_clear').css('display', 'none');
        filterList();
    });
});

(function($) {
    "use strict";
    $.fn.openSelect = function()
    {
        return this.each(function(idx,domEl) {
            if (document.createEvent) {
                const event = document.createEvent("MouseEvents");
                event.initMouseEvent("mousedown", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
                domEl.dispatchEvent(event);
            } else if (element.fireEvent) {
                domEl.fireEvent("onmousedown");
            }
        });
    }
}(jQuery));

$('#filter_select_down').click(function() {
    $('#list_select_category').openSelect();
});

