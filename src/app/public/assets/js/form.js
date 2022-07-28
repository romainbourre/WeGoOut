export const getFormData = ($form) => {
    const non_indexed_array = $form.serializeArray();
    const indexed_array = {};

    $.map(non_indexed_array, function(n, i){
        indexed_array[n['name']] = n['value'];
    });

    return indexed_array;
}