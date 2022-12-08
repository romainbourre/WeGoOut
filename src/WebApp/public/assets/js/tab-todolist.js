import './ajax-todolist.js';
import {addTask} from "./ajax-todolist.js";

$('#task-form-add').submit(function(e) {
    e.preventDefault();
});


$('#task-form-add a').on('click', function() {
    addTask();
});
