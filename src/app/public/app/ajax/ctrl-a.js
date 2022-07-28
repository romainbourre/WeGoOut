import {aj_request_change_registration, aj_request_update_cmd, aj_request_update_window, filterList, aj_request_list_event} from './a-listevent.js';
import {Listener} from "./a-profile.js";
import {getFormData} from "../../assets/js/form.js";

Listener();


$('#list_range_distance').change(function () {
    filterList();
});

$('#list_select_category').change(function () {
    filterList();
});

$('#list_input_date').change(function () {
    filterList();
});

$('#list_button_reset').click(function () {
    $('#list_input_lat').attr({value: ""});
    $('#list_input_lng').attr({value: ""});
    filterList();
});

$('#sheet_event_cmd_registration, #sheet_event_cmd_wait').click(function () {
    aj_request_change_registration();
});

setInterval(() => {
    const filterData = getFormData($('#list_form_filter'));
    aj_request_list_event(filterData, true)
}, 300000)